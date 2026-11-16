<?php
// @codingStandardsIgnoreFile
/**
 * Privileged PayPal preflight acknowledgment and read-only payment diagnostics.
 *
 * @package s2Member\PayPal
 * @since 261114.0253
 */
if(!defined('WPINC'))
    exit('Do not access this file directly.');
if(!current_user_can('manage_options') || (is_multisite() && !is_super_admin()))
    return;
global $wpdb;

$qa_is_post = isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST';
$qa_preflight_error = '';
$qa_preflight_success = FALSE;
//261116.1810 An explicit, privileged POST records operator acknowledgment only; it does not verify provider history or enable cutover.
if($qa_is_post && isset($_POST['s2m_pay_preflight_submit']))
{
    $qa_nonce = isset($_POST['s2m_pay_preflight_nonce']) && is_string($_POST['s2m_pay_preflight_nonce']) ? wp_unslash($_POST['s2m_pay_preflight_nonce']) : '';
    $qa_epoch = isset($_POST['s2m_pay_preflight_epoch']) && is_string($_POST['s2m_pay_preflight_epoch']) ? wp_unslash($_POST['s2m_pay_preflight_epoch']) : '';
    $qa_reference = isset($_POST['s2m_pay_preflight_reference']) && is_string($_POST['s2m_pay_preflight_reference']) ? wp_unslash($_POST['s2m_pay_preflight_reference']) : '';
    $qa_ack = isset($_POST['s2m_pay_preflight_ack']) && $_POST['s2m_pay_preflight_ack'] === '1';
    if(!$qa_ack)
        $qa_preflight_error = 'Confirm that external provider and membership reconciliation evidence has been reviewed.';
    else
    {
        //261116.1810 The core API rechecks nonce, epoch, merchant, privilege and immutable database readback; never trust form fields alone.
        $qa_saved = c_ws_plugin__s2member_paypal_notify_in_subscr_or_rp_payment_w_level::prepare_authoritative_cutover($qa_epoch, $qa_reference, $qa_nonce);
        if(is_wp_error($qa_saved))
            $qa_preflight_error = $qa_saved->get_error_message();
        else
            $qa_preflight_success = TRUE;
    }
}
//261116.1810 Compute readiness AFTER POST so successful approval and immutable conflicts are shown accurately.
$qa_cutover_readiness = c_ws_plugin__s2member_paypal_notify_in_subscr_or_rp_payment_w_level::cutover_readiness();
$qa_cutover_status = $qa_cutover_readiness['epoch'];

//261116.2220 Record only coarse usage dates. A missing marker is not proof no external integration uses the legacy credential.
$qa_legacy_first_seen = get_option('ws_plugin__s2member_paypal_proxy_legacy_first_used', false);
$qa_legacy_last_seen = get_option('ws_plugin__s2member_paypal_proxy_legacy_last_used', false);
$qa_legacy_status = is_scalar($qa_legacy_first_seen) && ctype_digit((string)$qa_legacy_first_seen) && (int)$qa_legacy_first_seen > 0
    ? 'First observed '.gmdate('Y-m-d H:i', (int)$qa_legacy_first_seen).' UTC; last sampled '.(is_scalar($qa_legacy_last_seen) && ctype_digit((string)$qa_legacy_last_seen) && (int)$qa_legacy_last_seen > 0 ? gmdate('Y-m-d H:i', (int)$qa_legacy_last_seen).' UTC' : 'unknown').'.'
    : 'No authenticated legacy-key use recorded since this monitoring was installed (not evidence of zero usage).';

$qa_proxy_error = '';
$qa_proxy_result = NULL;
if($qa_is_post && isset($_POST['s2m_pay_proxy_lookup_submit']))
{
    //261116.2220 A dedicated nonce and POST keep message IDs out of URLs, referers, and ordinary server access logs.
    $qa_nonce = isset($_POST['s2m_pay_proxy_lookup_nonce']) && is_string($_POST['s2m_pay_proxy_lookup_nonce']) ? wp_unslash($_POST['s2m_pay_proxy_lookup_nonce']) : '';
    if(!wp_verify_nonce($qa_nonce, 's2m_pay_proxy_claim_inspect'))
        $qa_proxy_error = 'The signed-delivery lookup could not be verified.';
    else
    {
        $qa_message_id = isset($_POST['s2m_pay_proxy_message_id']) && is_string($_POST['s2m_pay_proxy_message_id']) ? wp_unslash($_POST['s2m_pay_proxy_message_id']) : '';
        $qa_inspected = c_ws_plugin__s2member_paypal_utilities::paypal_proxy_v2_claim_inspect($qa_message_id);
        if(is_wp_error($qa_inspected))
            $qa_proxy_error = 'The claim database lookup failed; no changes were made.';
        else if($qa_inspected === FALSE)
            $qa_proxy_error = 'Enter a canonical 32-character lowercase hexadecimal message ID, and verify the v2 signing key exists.';
        else
            $qa_proxy_result = $qa_inspected;
    }
}

//261114.0253 Require POST + a dedicated nonce: raw PayPal identifiers never enter an audit URL.
$qa_result = NULL;
$qa_error = '';
$qa_sub_status = 'Not supplied';
$qa_marker_status = 'Not checked';
$qa_queue_status = 'Unknown';
$qa_lease_status = 'Not processing';
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
    <h1>PayPal Cutover &amp; Payment Audit</h1>
    <p>Payment receipts and legacy markers do not prove that membership fulfillment, external mail, or hooks completed. The receipt lookup is read-only; neither it nor the preflight form replays a payment or changes the authoritative routing filter.</p>
    <h2>1. Review cutover readiness</h2>
    <p><strong>Persisted cutover epoch:</strong> <?php echo esc_html($qa_cutover_status); ?></p>
    <p><strong>Immutable epoch fence:</strong> <?php echo esc_html($qa_cutover_readiness['fence']); ?></p>
    <p><strong>Activation preflight:</strong> <?php echo esc_html($qa_cutover_readiness['preflight']); ?></p>
    <p><strong>Rollback rule:</strong> Disable the per-payment authoritative opt-in if needed, but retain the original cutover epoch and every receipt. Historical shadows without a current legacy acceptance marker require reconciliation, not replay.</p>
    <p><strong>Activation status:</strong> This page cannot determine whether the application-supplied, per-payment opt-in filter is enabled. No control here enables it.</p>
    <h2>2. Record external reconciliation review</h2>
    <p><strong>This is an operator acknowledgment, not provider verification.</strong> Independently reconcile PayPal payment history against memberships, notification hooks, and external effects. Retain supporting evidence outside WordPress before recording its non-secret ticket reference. This cannot repair an uncertain payment.</p>
    <?php if($qa_preflight_error !== ''): ?>
        <div class="notice notice-error" role="alert"><p><?php echo esc_html($qa_preflight_error); ?></p></div>
    <?php elseif($qa_preflight_success): ?>
        <div class="notice notice-success" role="status"><p>Operator preflight recorded for the original epoch and configured PayPal merchant. This form did not change the application's authoritative opt-in; verify that setting independently.</p></div>
    <?php endif; ?>
    <?php if($qa_cutover_readiness['preflight_recorded']): ?>
        <p><strong>Preflight already recorded.</strong> The original evidence reference cannot be replaced here. Preserve the epoch and approval during rollback.</p>
    <?php elseif($qa_cutover_readiness['epoch_value'] > 0): ?>
        <p><strong>Current configured epoch:</strong> <code><?php echo esc_html($qa_cutover_readiness['epoch_value']); ?></code></p>
        <form method="post" autocomplete="off">
            <?php wp_nonce_field('s2m_pay_cutover_preflight', 's2m_pay_preflight_nonce'); ?>
            <p><label for="s2m-pay-preflight-epoch">Confirm the original Unix UTC cutover epoch (must match the configured value exactly)</label><br />
            <input type="text" id="s2m-pay-preflight-epoch" name="s2m_pay_preflight_epoch" size="24" maxlength="20" inputmode="numeric" autocomplete="off" required="required" /></p>
            <p><label for="s2m-pay-preflight-reference">External reconciliation evidence ticket (12&ndash;192 characters; no customer identifiers or secrets)</label><br />
            <input type="text" id="s2m-pay-preflight-reference" name="s2m_pay_preflight_reference" size="55" maxlength="192" autocomplete="off" required="required" /></p>
            <p><label><input type="checkbox" name="s2m_pay_preflight_ack" value="1" required="required" /> I have reviewed the independent evidence, unresolved payments, and rollback procedure.</label></p>
            <p><button class="button button-secondary" type="submit" name="s2m_pay_preflight_submit" value="1">Record one-time operator acknowledgment</button></p>
        </form>
    <?php else: ?>
        <p>Configure a canonical, positive cutover epoch using the documented operator procedure before recording preflight. Invalid or absent epochs cannot be approved.</p>
    <?php endif; ?>
    <h2>3. Inspect one payment receipt (read only)</h2>
    <p>Lookup is scoped to the site's currently configured PayPal merchant; an earlier merchant account may require separate records. Raw identifiers are never placed in the URL.</p>
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
    <h2>4. Inspect a signed proxy delivery (read only)</h2>
    <p>A v2 message ID identifies its replay claim, not the underlying payment or fulfillment. Processing and historic claims must be reconciled against provider and external side-effect evidence before any manual recovery. This page does not release claims, replay messages, or expose the HMAC key.</p>
    <form method="post" autocomplete="off">
        <?php wp_nonce_field('s2m_pay_proxy_claim_inspect', 's2m_pay_proxy_lookup_nonce'); ?>
        <p><label for="s2m-pay-proxy-message-id">Signed proxy message ID (32 lowercase hexadecimal characters)</label><br />
        <input type="password" id="s2m-pay-proxy-message-id" name="s2m_pay_proxy_message_id" size="42" maxlength="32" pattern="[a-f0-9]{32}" autocomplete="off" required="required" /></p>
        <p><button class="button button-secondary" type="submit" name="s2m_pay_proxy_lookup_submit" value="1">Inspect delivery claim (no changes)</button></p>
    </form>
    <?php if($qa_proxy_error !== ''): ?>
        <div class="notice notice-error" role="alert"><p><?php echo esc_html($qa_proxy_error); ?></p></div>
    <?php elseif(is_array($qa_proxy_result)): ?>
        <p><strong>Claim state:</strong> <?php echo esc_html($qa_proxy_result['state']); ?>;
            <strong>Signed replay window expired:</strong> <?php echo $qa_proxy_result['expired'] ? 'Yes' : 'No'; ?>;
            <strong>Expiry (UTC):</strong> <?php echo $qa_proxy_result['expires'] ? esc_html(gmdate('Y-m-d H:i:s', $qa_proxy_result['expires'])) : 'Not available'; ?>.</p>
        <p>Neither an absent nor a completed claim proves provider fulfillment or external mail/hook delivery. Do not clear uncertain claims based on this result alone.</p>
    <?php endif; ?>
    <h2>5. Legacy proxy-key migration</h2>
    <p><strong>Observed legacy-key authentication:</strong> <?php echo esc_html($qa_legacy_status); ?></p>
    <p>This records only verified use of the old static key, without storing sender addresses, payloads, or credentials. Usage may be from internal or external senders, and timestamps are sampled at most hourly. Keep the legacy compatibility filter unchanged until external senders have been identified and migrated to HMAC-v2.</p>
</div>
