<?php
// @codingStandardsIgnoreFile
/**
 * Read-only, administrator-only PayPal payment reconciliation diagnostics.
 *
 * @package s2Member\PayPal
 * @since 261114.0253
 */
if(!defined('WPINC'))
    exit('Do not access this file directly.');
if(!current_user_can('manage_options'))
    return;
global $wpdb;

//261116.1633 Use the router's canonical rules and direct fence/preflight readback; this dormant page never changes activation state.
$qa_cutover_readiness = c_ws_plugin__s2member_paypal_notify_in_subscr_or_rp_payment_w_level::cutover_readiness();
$qa_cutover_status = $qa_cutover_readiness['epoch'];

//261114.0253 Require POST + a dedicated nonce: raw PayPal identifiers never enter an audit URL.
$qa_result = NULL;
$qa_error = '';
$qa_sub_status = 'Not supplied';
$qa_marker_status = 'Not checked';
$qa_queue_status = 'Unknown';
$qa_lease_status = 'Not processing';
$qa_is_post = isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST';
if($qa_is_post && isset($_POST['s2m_pay_audit_submit']))
{
    $qa_nonce = isset($_POST['s2m_pay_audit_nonce']) && is_string($_POST['s2m_pay_audit_nonce']) ? wp_unslash($_POST['s2m_pay_audit_nonce']) : '';
    if(!wp_verify_nonce($qa_nonce, 's2m_pay_reconciliation_audit'))
        $qa_error = 'The audit request could not be verified.';
    else
    {
        $qa_txn = isset($_POST['s2m_pay_audit_txn']) && is_string($_POST['s2m_pay_audit_txn']) ? wp_unslash($_POST['s2m_pay_audit_txn']) : '';
        $qa_sub = isset($_POST['s2m_pay_audit_subscription']) && is_string($_POST['s2m_pay_audit_subscription']) ? wp_unslash($_POST['s2m_pay_audit_subscription']) : '';
        $qa_merchant = c_ws_plugin__s2member_payment_events::trusted_merchant('paypal');
        $qa_name = !is_wp_error($qa_merchant) ? c_ws_plugin__s2member_payment_events::option_name('paypal', $qa_merchant, $qa_txn, 'payment') : FALSE;
        if(is_wp_error($qa_merchant))
            $qa_error = 'A trusted PayPal merchant identity is not configured.';
        else if($qa_name === FALSE || strlen($qa_sub) > 255 || preg_match('/[\x00-\x1f\x7f]/', $qa_sub))
            $qa_error = 'Supply a valid PayPal transaction ID and, optionally, a valid subscription ID.';
        else
        {
            $qa_receipt = c_ws_plugin__s2member_payment_events::read($qa_name);
            if(is_wp_error($qa_receipt))
                $qa_error = 'The durable receipt could not be read. No changes were made.';
            else if($qa_receipt === FALSE)
                $qa_result = array('category' => 'no_durable_receipt', 'status' => 'Absent', 'origin' => 'Unknown', 'checkpoint' => 'Unknown');
            else if($qa_receipt['gateway'] !== 'paypal' || $qa_receipt['event'] !== 'payment')
                $qa_error = 'The receipt identity or event type did not match the trusted lookup.';
            else
            {
                $qa_authoritative = isset($qa_receipt['replay_origin']);
                $qa_authorized = isset($qa_receipt['replay_authorized']) && $qa_receipt['replay_authorized'] === TRUE;
                $qa_checkpoint = isset($qa_receipt['fulfillment_started']);
                $qa_bound = isset($qa_receipt['subscription_hash']);
                $qa_queue_found = FALSE;
                if($qa_bound)
                {
                    //261114.0253 Direct, bounded SQL reads; presence is NOT proof of a valid replay payload.
                    $qa_queue_name = 's2m_pay_pend_'.$qa_receipt['subscription_hash'].'_'.substr($qa_name, -64);
                    $qa_queue_read = $wpdb->get_var($wpdb->prepare("SELECT `option_name` FROM `{$wpdb->options}` WHERE `option_name` = %s LIMIT 1", $qa_queue_name));
                    if($wpdb->last_error !== '')
                        $qa_error = 'The pending-queue lookup failed. No changes were made.';
                    else
                    {
                        $qa_queue_found = is_string($qa_queue_read);
                        $qa_queue_status = $qa_queue_found ? 'Row exists (payload unverified)' : 'Row absent';
                    }
                }
                if($qa_sub !== '')
                {
                    $qa_sub_status = $qa_bound && hash_equals($qa_receipt['subscription_hash'], hash('sha256', $qa_sub)) ? 'Matches receipt binding' : 'Does not match a receipt binding';
                    //261114.0253 Never call dedupe_done_time_get() in an audit: that helper deletes expired legacy markers.
                    $qa_done_name = 's2m_paypal_txn_done_'.md5(strtolower($qa_sub).'|'.strtolower($qa_txn));
                    $qa_done_raw = $wpdb->get_var($wpdb->prepare("SELECT `option_value` FROM `{$wpdb->options}` WHERE `option_name` = %s LIMIT 1", $qa_done_name));
                    if($wpdb->last_error !== '')
                        $qa_error = 'The legacy-marker lookup failed. No changes were made.';
                    else
                    {
                        $qa_done_time = is_scalar($qa_done_raw) && ctype_digit((string)$qa_done_raw) ? (int)$qa_done_raw : 0;
                        $qa_marker_status = $qa_done_time > 0 && $qa_done_time <= time() && time() - $qa_done_time < DAY_IN_SECONDS ? 'Current legacy acceptance marker (NOT fulfillment proof)' : 'No current legacy acceptance marker';
                    }
                }
                $qa_lease_active = FALSE;
                if($qa_receipt['status'] === 'processing' && isset($qa_receipt['owner']))
                {
                    $qa_lease_active = c_ws_plugin__s2member_database::lock_is_owned('payment-event/'.$qa_name, $qa_receipt['owner']);
                    if(is_wp_error($qa_lease_active))
                        $qa_error = 'The active-worker lookup failed. No changes were made.';
                    else
                        $qa_lease_status = $qa_lease_active ? 'Current worker may be active' : 'No current owner';
                }
                if($qa_error === '')
                {
                    if(!$qa_authoritative)
                        $qa_category = 'legacy_shadow_unverified';
                    else if($qa_receipt['status'] === 'completed')
                        $qa_category = $qa_authorized && $qa_checkpoint && $qa_bound ? 'authoritative_completed_evidence' : 'completion_missing_proof';
                    else if($qa_checkpoint)
                        $qa_category = $qa_receipt['status'] === 'processing' && $qa_lease_active === TRUE ? 'checkpointed_worker_may_be_active' : 'checkpointed_ambiguous';
                    else if($qa_receipt['status'] === 'pending_registration')
                        $qa_category = $qa_authorized && $qa_bound && $qa_queue_found && ($qa_sub === '' || $qa_sub_status === 'Matches receipt binding') ? 'pending_queue_present_unverified' : 'pending_requires_review';
                    else
                        $qa_category = 'before_effects_checkpoint_no_fulfillment_proof';
                    $qa_result = array('category' => $qa_category, 'status' => $qa_receipt['status'],
                                       'origin' => $qa_authoritative ? 'Authoritative first receipt' : 'Historical legacy shadow',
                                       'checkpoint' => $qa_checkpoint ? 'Recorded (irreversible-work boundary)' : 'Not recorded');
                }
            }
        }
    }
}
?>
<div class="wrap">
    <h1>PayPal Payment Audit (Read Only)</h1>
    <p>Diagnostic lookup only. A receipt, legacy acceptance marker, or queue row does not prove membership fulfillment, external email, or third-party hook delivery. This page cannot replay, mark complete, or alter a payment. Lookup is scoped to the site's currently configured PayPal merchant; a prior merchant account may require separate records.</p>
    <h2>Recurring-payment cutover safety</h2>
    <p><strong>Persisted cutover epoch:</strong> <?php echo esc_html($qa_cutover_status); ?></p>
    <p><strong>Immutable epoch fence:</strong> <?php echo esc_html($qa_cutover_readiness['fence']); ?></p>
    <p><strong>Activation preflight:</strong> <?php echo esc_html($qa_cutover_readiness['preflight']); ?></p>
    <p><strong>Rollback rule:</strong> Disable the per-payment authoritative opt-in if needed, but retain the original cutover epoch and all durable receipts. Never reset, delete, or rewind the epoch to restore legacy processing. Historical shadows without a current legacy acceptance marker require reconciliation, not replay.</p>
    <p><strong>Activation status:</strong> This page cannot determine whether the application-supplied, per-payment opt-in filter is enabled. An epoch alone does not activate the authoritative route. No settings are changed here.</p>
    <form method="post" autocomplete="off">
        <?php wp_nonce_field('s2m_pay_reconciliation_audit', 's2m_pay_audit_nonce'); ?>
        <p><label for="s2m-pay-audit-txn">PayPal transaction ID (required)</label><br />
        <input type="password" id="s2m-pay-audit-txn" name="s2m_pay_audit_txn" autocomplete="off" size="50" maxlength="255" required="required" /></p>
        <p><label for="s2m-pay-audit-sub">PayPal subscription ID (optional; verifies binding and recent legacy marker)</label><br />
        <input type="password" id="s2m-pay-audit-sub" name="s2m_pay_audit_subscription" autocomplete="off" size="50" maxlength="255" /></p>
        <p><button class="button button-primary" type="submit" name="s2m_pay_audit_submit" value="1">Inspect receipt (no changes)</button></p>
    </form>
    <?php if($qa_error !== ''): ?>
        <div class="notice notice-error"><p><?php echo esc_html($qa_error); ?></p></div>
    <?php elseif(is_array($qa_result)): ?>
        <h2>Diagnostic result</h2>
        <table class="widefat striped" style="max-width:760px"><tbody>
        <?php foreach(array('Category' => $qa_result['category'], 'Durable status' => $qa_result['status'],
                            'First-receipt origin' => $qa_result['origin'], 'Fulfillment checkpoint' => $qa_result['checkpoint'],
                            'Pending queue' => $qa_queue_status, 'Current worker' => $qa_lease_status,
                            'Subscription binding' => $qa_sub_status, 'Recent legacy marker' => $qa_marker_status) as $qa_label => $qa_value): ?>
            <tr><th scope="row"><?php echo esc_html($qa_label); ?></th><td><?php echo esc_html($qa_value); ?></td></tr>
        <?php endforeach; ?>
        </tbody></table>
        <p><strong>Not a resolution or replay authorization.</strong> Verify the authenticated PayPal transaction, existing membership, notifications, and hook effects independently. If evidence is insufficient, leave the payment unresolved.</p>
    <?php endif; ?>
</div>
