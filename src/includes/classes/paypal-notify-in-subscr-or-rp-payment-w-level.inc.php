<?php
// @codingStandardsIgnoreFile
/**
 * s2Member's PayPal IPN handler (inner processing routine).
 *
 * Copyright: © 2009-2011
 * {@link http://websharks-inc.com/ WebSharks, Inc.}
 * (coded in the USA)
 *
 * Released under the terms of the GNU General Public License.
 * You should have received a copy of the GNU General Public License,
 * along with this software. In the main directory, see: /licensing/
 * If not, see: {@link http://www.gnu.org/licenses/}.
 *
 * @package s2Member\PayPal
 * @since 110720
 */
if(!defined('WPINC')) // MUST have WordPress.
	exit ('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_paypal_notify_in_subscr_or_rp_payment_w_level'))
{
	/**
	 * s2Member's PayPal IPN handler (inner processing routine).
	 *
	 * @package s2Member\PayPal
	 * @since 110720
	 */
	class c_ws_plugin__s2member_paypal_notify_in_subscr_or_rp_payment_w_level
	{
		/**
		 * Route a verified PayPal recurring payment to its first-receipt owner.
		 *
		 * Called ONLY from the authenticated native/PayPal-proxy handler after its
		 * ordinary payment, item, and membership checks. An old shadow receipt
		 * stays with legacy processing; a prior authoritative receipt can never
		 * fall back to legacy, even if the opt-in is later disabled. No direct
		 * enrollment, notifications, or transient replay happen in this method.
		 *
		 * @since 261113.0039
		 *
		 * @param array $paypal Verified and normalized incoming PayPal payment.
		 *
		 * @return array|bool|WP_Error FALSE for legacy, queued outcome, or retryable delivery error.
		 */
		public static function maybe_queue_authoritative_payment($paypal)
		{
			if(!is_array($paypal) || !isset($paypal['subscr_gateway'], $paypal['txn_type'], $paypal['txn_id'], $paypal['subscr_id'])
			   || $paypal['subscr_gateway'] !== 'paypal' || !is_string($paypal['txn_type'])
			   || !in_array(strtolower($paypal['txn_type']), array('subscr_payment', 'recurring_payment', 'merch_pmt'), TRUE)
			   || !is_string($paypal['txn_id']) || $paypal['txn_id'] === '' || !is_string($paypal['subscr_id']) || $paypal['subscr_id'] === '')
				return FALSE;

			$merchant = c_ws_plugin__s2member_payment_events::trusted_merchant('paypal');
			if(is_wp_error($merchant))
				return $merchant;
			$event = c_ws_plugin__s2member_payment_events::option_name('paypal', $merchant, $paypal['txn_id'], 'payment');
			if($event === FALSE)
				return new WP_Error('s2m_pay_cutover_invalid_identity', 'Cannot derive a durable PayPal transaction identity.');
			$prior = c_ws_plugin__s2member_payment_events::read($event);
			if(is_wp_error($prior))
				return $prior;
			//261113.2225 Use one cutover-epoch snapshot for every first-receipt decision in this request.
			$epoch = get_option('s2m_paypal_authoritative_cutover_epoch', 0);
			$epoch = (is_scalar($epoch) && ctype_digit((string)$epoch)) ? (int)$epoch : 0;
			//261113.2224 A shadow receipt does not prove whether legacy fulfillment finished. After cutover,
			//261113.2224 acknowledge only a live legacy done marker; otherwise require reconciliation.
			if(is_array($prior) && !isset($prior['replay_origin']))
			{
				if($epoch < 1)
					return FALSE; //261113.2224 Before cutover, preserve the original legacy route.
				$done = 's2m_paypal_txn_done_'.md5(strtolower($paypal['subscr_id']).'|'.strtolower($paypal['txn_id']));
				if(c_ws_plugin__s2member_paypal_utilities::dedupe_done_time_get($done, DAY_IN_SECONDS))
					return array('event' => $event, 'status' => 'legacy_acknowledged', 'created' => FALSE);
				return new WP_Error('s2m_pay_cutover_shadow_unreconciled', 'An earlier legacy payment may already have produced effects; manual reconciliation is required.');
			}

			$enabled = apply_filters('ws_plugin__s2member_paypal_authoritative_recurring_enabled', FALSE, $paypal) === TRUE;
			if($prior === FALSE && (!$enabled || strtolower($paypal['txn_type']) === 'merch_pmt'))
			{
				//261113.0834 Once a cutover epoch exists, even an opted-out legacy delivery must reserve the first receipt before member effects. Otherwise a concurrent native IPN can create an authoritative receipt while this request continues down the legacy path.
				if($epoch < 1)
					return FALSE;
				$shadow = c_ws_plugin__s2member_payment_events::receive('paypal', $merchant, $paypal['txn_id'], 'payment', FALSE);
				if(is_wp_error($shadow))
					return $shadow;
				if(!isset($shadow['record']['replay_origin']))
					return FALSE;
				//261113.0834 A concurrent authoritative first receipt won; follow its durable path even though this delivery opted out.
			}
			if($prior === FALSE && $enabled)
			{
				//261113.0039 No historical registered payment ledger exists: require a persisted cutover epoch and a verified payment date before authorizing a first receipt.
				//261113.0047 Only native PayPal validation currently proves the provider payment-date semantics; a verified Checkout proxy may follow an existing authoritative receipt but cannot create one.
				$paid = empty($paypal['proxy_verified']) && isset($paypal['payment_date']) && is_string($paypal['payment_date']) ? strtotime($paypal['payment_date']) : FALSE;
				if($epoch < 1 || $paid === FALSE || $paid < $epoch || $paid > time() + 300)
				{
					//261113.0039 Also fence the legacy-first path before effects; a racing channel must observe its shadow rather than create an authoritative receipt.
					$shadow = c_ws_plugin__s2member_payment_events::receive('paypal', $merchant, $paypal['txn_id'], 'payment', FALSE);
					if(is_wp_error($shadow))
						return $shadow;
					if(!isset($shadow['record']['replay_origin']))
						return FALSE;
				}
			}

			$ipn = array('txn_type' => 'subscr_payment');
			foreach($paypal as $name => $value)
				if(in_array($name, array('subscr_gateway', 'subscr_id', 'subscr_baid', 'subscr_cid', 'txn_id', 'custom', 'invoice', 'mc_gross', 'mc_currency', 'tax', 'payer_email', 'first_name', 'last_name', 'item_name', 'item_number', 'option_name1', 'option_selection1', 'option_name2', 'option_selection2'), TRUE))
					$ipn[$name] = $value;
			$queued = c_ws_plugin__s2member_payment_events::queue_verified_payment('paypal', $merchant, $ipn, TRUE);
			//261113.0039 A simultaneous legacy-first receipt wins the migration race; preserve its old deduplication path instead of promoting it.
			if(is_wp_error($queued) && $queued->get_error_code() === 's2m_pay_pending_legacy_shadow')
				return FALSE;
			if(is_wp_error($queued))
				return $queued;
			if($queued['status'] === 'completed')
				return $queued;
			if($queued['status'] !== 'pending_registration')
				return new WP_Error('s2m_pay_cutover_receipt_not_ready', 'The durable PayPal payment is not safely queued yet.');

			$user_id = c_ws_plugin__s2member_utils_users::get_user_id_with($ipn['subscr_id']);
			if($user_id)
			{
				//261113.0039 Existing members need a Cron wakeup; enqueue success without a worker wakeup must ask PayPal to redeliver.
				$scheduled = c_ws_plugin__s2member_registrations::schedule_pending_registration_payments($user_id, $ipn['subscr_id']);
				if($scheduled !== TRUE)
					return is_wp_error($scheduled) ? $scheduled : new WP_Error('s2m_pay_cutover_cron_failed', 'Could not schedule durable PayPal fulfillment.');
			}
			return $queued;
		}

		/**
		 * Fulfill one authenticated PayPal registration replay in-process.
		 *
		 * Only the owned registration consumer may call this with its checkpointed
		 * token. The queued IPN was authenticated at receipt time; it is NOT a new
		 * native IPN, and must not be sent through the public proxy endpoint or
		 * rejected by legacy cross-channel duplicate markers set during enqueue.
		 * Other gateway proxy integrations are deliberately unsupported until audited.
		 *
		 * @since 261112.2104
		 *
		 * @param array $ipn Original durable, verified PayPal payment payload.
		 * @param string $event Expected durable event name.
		 * @param string $token Event lease owner established by the registration consumer.
		 *
		 * @return bool TRUE only after the existing member/payment/hook path reached its verified end.
		 */
		public static function fulfill_authorized_registration_payment($ipn, $event, $token)
		{
			if(!is_array($ipn) || !isset($ipn['subscr_gateway']) || $ipn['subscr_gateway'] !== 'paypal')
				return FALSE;
			$authorized = c_ws_plugin__s2member_payment_events::fulfillment_claim_is_authorized('paypal', $ipn, $event, $token);
			if($authorized !== TRUE)
				return FALSE;
			//261112.2104 No registration-to-user binding means no fulfillment; never create a new legacy transient from an authoritative replay.
			$user_id = c_ws_plugin__s2member_utils_users::get_user_id_with($ipn['subscr_id']);
			if(!$user_id || !is_object($user = new WP_User($user_id)) || !$user->ID
			   || get_user_option('s2member_subscr_id', $user_id) !== $ipn['subscr_id'])
				return FALSE;
			//261112.2104 This internal provenance skips only the legacy duplicate gate; the caller must already own the durable payment lease.
			$paypal = $ipn;
			$paypal['proxy_verified'] = 'paypal';
			$paypal['s2member_log'] = array();
			$_paypal_s = serialize($paypal);
			$payment_status_issues = '/^(failed|denied|expired|refunded|partially_refunded|reversed|reversal|canceled_reversal|voided)$/i';
			$coupon = array('full_coupon_code' => '', 'coupon_code' => '', 'affiliate_id' => '');
			$handled = self::cp(array('paypal' => &$paypal, '_paypal_s' => $_paypal_s, 'coupon' => $coupon,
			                           'payment_status_issues' => $payment_status_issues,
			                           's2m_authoritative_event' => $event, 's2m_authoritative_token' => $token));
			return is_array($handled) && !empty($handled['s2member_authoritative_completion_verified']) && $handled['s2member_authoritative_completion_verified'] === TRUE;
		}

		/**
		 * s2Member's PayPal IPN handler (inner processing routine).
		 *
		 * @package s2Member\PayPal
		 * @since 110720
		 *
		 * @param array $vars Required. An array of defined variables passed by {@link s2Member\PayPal\c_ws_plugin__s2member_paypal_notify_in::paypal_notify()}.
		 *
		 * @return array|bool The original ``$paypal`` array passed in (extracted) from ``$vars``, or false when conditions do NOT apply.
		 */
		public static function cp($vars = array()) // Conditional phase for ``c_ws_plugin__s2member_paypal_notify_in::paypal_notify()``.
		{
			extract($vars, EXTR_OVERWRITE | EXTR_REFS); // Extract all vars passed in from: ``c_ws_plugin__s2member_paypal_notify_in::paypal_notify()``.

			if((!empty($paypal['txn_type']) && preg_match('/^(subscr_payment|recurring_payment|merch_pmt)$/i', $paypal['txn_type']))
			   && ((!empty($paypal['item_number']) || ($paypal['item_number'] = c_ws_plugin__s2member_paypal_utilities::paypal_pro_item_number($paypal))) && preg_match($GLOBALS['WS_PLUGIN__']['s2member']['c']['membership_item_number_w_level_regex'], $paypal['item_number']))
			   && (!empty($paypal['subscr_id']) || ($paypal['subscr_id'] = c_ws_plugin__s2member_paypal_utilities::paypal_pro_subscr_id($paypal)))
			   && c_ws_plugin__s2member_paypal_utilities::paypal_payment_status_allows_access($paypal, $payment_status_issues)
			   && (!empty($paypal['item_name']) || ($paypal['item_name'] = c_ws_plugin__s2member_paypal_utilities::paypal_pro_item_name($paypal)))
			   && (!empty($paypal['payer_email']) || ($paypal['payer_email'] = c_ws_plugin__s2member_utils_users::get_user_email_with($paypal['subscr_id'])))
			   && (!empty($paypal['subscr_baid']) || ($paypal['subscr_baid'] = $paypal['subscr_id']))
			   && (!empty($paypal['subscr_cid']) || ($paypal['subscr_cid'] = $paypal['subscr_id']))
			   && (!empty($paypal['txn_id'])) && (!empty($paypal['mc_gross']))
			)
			{
				//261113.0039 Bypass legacy deduplication and member effects only for the verified, durably owned payment path; internal checkpointed replay must reach the original handler.
				if(!isset($s2m_authoritative_event) && !isset($s2m_authoritative_token))
				{
					$routed = self::maybe_queue_authoritative_payment($paypal);
					if($routed !== FALSE)
					{
						if(is_wp_error($routed))
						{
							//261113.1000 One request-local retry outcome covers both durable-intake failures and legacy transaction contention.
							$paypal['s2member_notification_retry_error'] = $routed->get_error_code();
							$paypal['s2member_log'][] = 'Durable payment intake needs redelivery ('.$routed->get_error_code().').';
						}
						else
							$paypal['s2member_log'][] = 'Durable PayPal payment intake: '.$routed['status'].'.';
						return $paypal;
					}
				}

				foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
				do_action('ws_plugin__s2member_during_paypal_notify_before_subscr_payment', get_defined_vars());
				unset($__refs, $__v);

				//261112.2104 A replay must prove its active checkpoint again after pre-payment hooks, which may modify the IPN by reference.
				$authorized_replay = isset($s2m_authoritative_event, $s2m_authoritative_token)
				                     && c_ws_plugin__s2member_payment_events::fulfillment_claim_is_authorized('paypal', $paypal, $s2m_authoritative_event, $s2m_authoritative_token) === TRUE;
				//261112.2104 An expired or changed internal claim must fail closed, never drop into the ordinary legacy-IPN path.
				if((isset($s2m_authoritative_event) || isset($s2m_authoritative_token)) && !$authorized_replay)
					return FALSE;

				//260406 Cross-channel payment dedupe for PayPal recurring/subscription payments; first arrival wins across webhook proxy and native IPN.
				$txn_dedupe_key = md5(strtolower((string)$paypal['subscr_id']).'|'.strtolower((string)$paypal['txn_id']));
				$txn_lock_option = 's2m_paypal_txn_lock_'.$txn_dedupe_key;
				$txn_done_option = 's2m_paypal_txn_done_'.$txn_dedupe_key;
				$txn_lock_ttl = 900;
				$txn_done_ttl = DAY_IN_SECONDS;

				c_ws_plugin__s2member_paypal_utilities::dedupe_markers_cleanup('s2m_paypal_txn_dedupe_cleanup_throttle', array(
					array('prefix' => 's2m_paypal_txn_lock_', 'ttl' => $txn_lock_ttl),
					array('prefix' => 's2m_paypal_txn_done_', 'ttl' => $txn_done_ttl),
				), 6 * HOUR_IN_SECONDS);

				if(!$authorized_replay && c_ws_plugin__s2member_paypal_utilities::dedupe_done_time_get($txn_done_option, $txn_done_ttl))
				{
					$paypal['s2member_log'][] = 'Not processing. Duplicate notification.';
					$paypal['s2member_log'][] = 's2Member `txn_type` identified as ( `subscr_payment|recurring_payment` ).';
					$paypal['s2member_log'][] = 'Duplicate notification. This transaction was already processed via another PayPal notification path. This notification will be ignored.';
				}
				//261111.1750 Recurring payments use the shared owned lease across native IPN and Checkout webhook paths; retain the original transaction identity and busy result.
				else if(!$authorized_replay && !is_string($txn_lock_token = c_ws_plugin__s2member_paypal_utilities::dedupe_lock_acquire_owned($txn_lock_option, $txn_lock_ttl)))
				{
					//261113.1000 Another worker's lease is not proof of fulfillment. Ask the gateway to retry unless a completed marker was already observed above.
					$paypal['s2member_notification_retry_error'] = 's2m_paypal_txn_in_flight';
					$paypal['s2member_log'][] = 'Not processing. Duplicate notification.';
					$paypal['s2member_log'][] = 's2Member `txn_type` identified as ( `subscr_payment|recurring_payment` ).';
					$paypal['s2member_log'][] = 'Duplicate notification. Another PayPal notification path is already processing this transaction. This notification will be ignored.';
				}
				else if($authorized_replay || (!get_transient($transient_ipn = 's2m_ipn_'.md5('s2member_transient_'.$_paypal_s)) && set_transient($transient_ipn, time(), 31556926 * 10)))
				{
					$paypal['s2member_log'][] = 's2Member `txn_type` identified as '.($identified_as = '( `subscr_payment|recurring_payment` )').'.';

					if(empty($paypal['proxy_verified'])) //261110.1954 Only native PayPal IPNs need this delay; use authenticated origin.
					{
						$paypal['s2member_log'][] = 'Sleeping for 15 seconds. Waiting for a possible ( `subscr_signup|subscr_modify|recurring_payment_profile_created` ).';
						sleep(15); // Sleep here for a moment. PayPal sometimes sends a subscr_payment before the subscr_signup, subscr_modify.
						$paypal['s2member_log'][] = 'Awake. It\'s '.date('D M j, Y g:i:s a T').'. s2Member `txn_type` identified as '.$identified_as.'.';
					}
					//260814 Split against the full three-part membership grammar; pad missing CCaps before assignment.
					list($paypal['level'], $paypal['ccaps']) = array_pad(explode(':', $paypal['item_number'], 3), 2, '');
					//260814 Set optional gateway elements used below so sparse/null payload fields do not trigger PHP diagnostics.
					$paypal = c_ws_plugin__s2member_utils_arrays::set_unset_elements($paypal, array('option_name2', 'option_selection2', 'invoice', 'first_name', 'last_name'));

					$paypal['ip'] = (preg_match('/ip address/i', $paypal['option_name2']) && $paypal['option_selection2']) ? $paypal['option_selection2'] : '';
					$paypal['ip'] = (!$paypal['ip'] && preg_match('/^[a-z0-9]+~[0-9\.]+$/i', $paypal['invoice'])) ? preg_replace('/^[a-z0-9]+~/i', '', $paypal['invoice']) : $paypal['ip'];

					$paypal['currency']        = strtoupper($paypal['mc_currency']); // Normalize input currency.
					$paypal['currency_symbol'] = c_ws_plugin__s2member_utils_cur::symbol($paypal['currency']);

					if(($user_id = c_ws_plugin__s2member_utils_users::get_user_id_with($paypal['subscr_id'])) && is_object($user = new WP_User ($user_id)) && $user->ID)
					{
						$processing = $during = TRUE; // Yes, we ARE processing this.
						//261112.2112 Capture the local start of this actual payment update; a pre-existing user timestamp is not proof of this replay.
						$payment_update_started = time();

						$pr_times                           = get_user_option('s2member_paid_registration_times', $user_id);
						$pr_times['level']                  = (!$pr_times['level']) ? time() : $pr_times['level']; // Preserves existing.
						$pr_times['level'.$paypal['level']] = (!$pr_times['level'.$paypal['level']]) ? time() : $pr_times['level'.$paypal['level']];
						update_user_option($user_id, 's2member_paid_registration_times', $pr_times); // Update now.

						if(!get_user_option('s2member_first_payment_txn_id', $user_id)) // 1st payment?
							update_user_option($user_id, 's2member_first_payment_txn_id', $paypal['txn_id']);

						update_user_option($user_id, 's2member_last_payment_time', time()); // Also update last payment time.

						$paypal['s2member_log'][] = 'Updated Payment Times for this Member.'; // Flag this action in the log.

						$fields      = get_user_option('s2member_custom_fields', $user_id); // These will be needed in the routines below.
						$user_reg_ip = get_user_option('s2member_registration_ip', $user_id); // Original IP during Registration.
						$user_reg_ip = $paypal['ip'] = ($user_reg_ip) ? $user_reg_ip : $paypal['ip']; // Now merge conditionally.

						//261109.1531 Webhook/API URL notifications: send each configured URL independently; an empty rendered URL must not skip later entries.
						if($processing && $GLOBALS['WS_PLUGIN__']['s2member']['o']['payment_notification_urls'])
						{
							foreach(preg_split('/['."\r\n\t".']+/', $GLOBALS['WS_PLUGIN__']['s2member']['o']['payment_notification_urls']) as $url)
							{
								if(($url = c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($url, c_ws_plugin__s2member_utils_strings::paypal_ipn_template_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'subscr-or-rp-payment-w-level-1-url'), 'url')) && ($url = trim($url)))
								{
									c_ws_plugin__s2member_utils_urls::remote($url);
								}
							}
							$paypal['s2member_log'][] = 'Payment Notification URLs have been processed.';
						}

						//261109.1531 API notification email: assemble a plaintext event summary for configured recipients.
						if($processing && $GLOBALS['WS_PLUGIN__']['s2member']['o']['payment_notification_recipients'])
						{
							$msg = $sbj = '(s2Member / API Notification Email) - Payment';
							$msg .= "\n\n"; // Spacing in the message body.

							$msg .= 'subscr_id: %%subscr_id%%'."\n";
							$msg .= 'subscr_baid: %%subscr_baid%%'."\n";
							$msg .= 'subscr_cid: %%subscr_cid%%'."\n";
							$msg .= 'currency: %%currency%%'."\n";
							$msg .= 'currency_symbol: %%currency_symbol%%'."\n";
							$msg .= 'amount: %%amount%%'."\n";
							$msg .= 'txn_id: %%txn_id%%'."\n";
							$msg .= 'item_number: %%item_number%%'."\n";
							$msg .= 'item_name: %%item_name%%'."\n";
							$msg .= 'first_name: %%first_name%%'."\n";
							$msg .= 'last_name: %%last_name%%'."\n";
							$msg .= 'full_name: %%full_name%%'."\n";
							$msg .= 'payer_email: %%payer_email%%'."\n";

							$msg .= 'user_first_name: %%user_first_name%%'."\n";
							$msg .= 'user_last_name: %%user_last_name%%'."\n";
							$msg .= 'user_full_name: %%user_full_name%%'."\n";
							$msg .= 'user_email: %%user_email%%'."\n";
							$msg .= 'user_login: %%user_login%%'."\n";
							$msg .= 'user_ip: %%user_ip%%'."\n";
							$msg .= 'user_id: %%user_id%%'."\n";

							if(is_array($fields) && !empty($fields))
								foreach($fields as $var => $val)
									$msg .= $var.': %%'.$var.'%%'."\n";

							$msg .= 'cv0: %%cv0%%'."\n";
							$msg .= 'cv1: %%cv1%%'."\n";
							$msg .= 'cv2: %%cv2%%'."\n";
							$msg .= 'cv3: %%cv3%%'."\n";
							$msg .= 'cv4: %%cv4%%'."\n";
							$msg .= 'cv5: %%cv5%%'."\n";
							$msg .= 'cv6: %%cv6%%'."\n";
							$msg .= 'cv7: %%cv7%%'."\n";
							$msg .= 'cv8: %%cv8%%'."\n";
							$msg .= 'cv9: %%cv9%%';

							//261109.1531 Only render after appending every event/custom-field row; replacement values are not rescanned.
							if(($msg = c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($msg, c_ws_plugin__s2member_utils_strings::paypal_ipn_template_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'subscr-or-rp-payment-w-level-2-msg'), 'text')) && $sbj && ($msg = trim($msg)))
							{
								foreach(c_ws_plugin__s2member_utils_strings::parse_emails($GLOBALS['WS_PLUGIN__']['s2member']['o']['payment_notification_recipients']) as $recipient)
								{
									wp_mail($recipient, apply_filters('ws_plugin__s2member_payment_notification_email_sbj', $sbj, get_defined_vars()), apply_filters('ws_plugin__s2member_payment_notification_email_msg', $msg, get_defined_vars()), 'Content-Type: text/plain; charset=UTF-8');
								}
							}
							$paypal['s2member_log'][] = 'Payment Notification Emails have been processed.';
						}

						foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
						do_action('ws_plugin__s2member_during_paypal_notify_during_subscr_payment', get_defined_vars());
						unset($__refs, $__v);
						//261112.2104 Re-read durable member updates only after notification URLs, emails, and hooks have been attempted; return a completion signal only for the verified internal replay.
						if($authorized_replay)
						{
							$confirmed_times = get_user_option('s2member_paid_registration_times', $user_id);
							if(is_array($confirmed_times) && !empty($confirmed_times['level'.$paypal['level']])
							   && get_user_option('s2member_first_payment_txn_id', $user_id)
							   && (int)get_user_option('s2member_last_payment_time', $user_id) >= $payment_update_started)
								$paypal['s2member_authoritative_completion_verified'] = TRUE;
						}
					}
					else if(!$authorized_replay) // Otherwise, we need to re-generate/store this IPN into a Transient Queue. Then re-process it on registration.
					{
						$paypal['s2member_log'][] = 'Skipping this IPN response, for now. The Subscr. ID is not associated with a registered Member.';

						$ipn = array('txn_type' => 'subscr_payment'); // Create a simulated IPN response for txn_type=subscr_payment.

						foreach($paypal as $var => $val)
							if(in_array($var, array('subscr_gateway', 'subscr_id', 'subscr_baid', 'subscr_cid', 'txn_id', 'custom', 'invoice', 'mc_gross', 'mc_currency', 'tax', 'payer_email', 'first_name', 'last_name', 'item_name', 'item_number', 'option_name1', 'option_selection1', 'option_name2', 'option_selection2')))
								$ipn[$var] = $val;

						$paypal['s2member_log'][] = 'Re-generating. This IPN will go into a Transient Queue; and be re-processed during registration.';

						//261112.1205 Store each verified payment independently before the legacy single-slot compatibility replay.
						$merchant = c_ws_plugin__s2member_payment_events::trusted_merchant($paypal['subscr_gateway']);
						$queued_payment = is_wp_error($merchant) ? $merchant : c_ws_plugin__s2member_payment_events::queue_verified_payment($paypal['subscr_gateway'], $merchant, $ipn);
						if(is_wp_error($queued_payment))
							$paypal['s2member_log'][] = 'Durable pending payment queue unavailable ('.$queued_payment->get_error_code().'); retaining legacy transient fallback.';
						else
							$paypal['s2member_log'][] = 'Durable pending payment queue status: '.$queued_payment['status'].'.';

						set_transient('s2m_'.md5('s2member_transient_ipn_subscr_payment_'.$paypal['subscr_id']), $ipn, 43200);
					}

					//261112.2104 The internal replay was already deduplicated by the durable event lease. Never manufacture or release legacy marker ownership it did not acquire.
					if(!$authorized_replay)
					{
						//260406 Mark this payment transaction done after the winning PayPal notification path has been accepted or queued.
						c_ws_plugin__s2member_paypal_utilities::dedupe_done_mark($txn_done_option);
						//261111.1750 Release only this worker's token after the legacy completion marker is written.
						c_ws_plugin__s2member_paypal_utilities::dedupe_lock_release_owned($txn_lock_option, $txn_lock_token);
					}
				}
				else // Else, this is a duplicate IPN. Must stop here.
				{
					$paypal['s2member_log'][] = 'Not processing. Duplicate IPN.';
					$paypal['s2member_log'][] = 's2Member `txn_type` identified as ( `subscr_payment|recurring_payment` ).';
					$paypal['s2member_log'][] = 'Duplicate IPN. Already processed. This IPN will be ignored.';

					//260406 Release the shared transaction lock when the older exact duplicate-IPN transient check blocks this request.
					//261111.1750 A duplicate-IPN rejection must not release another worker's replacement lease.
					c_ws_plugin__s2member_paypal_utilities::dedupe_lock_release_owned($txn_lock_option, $txn_lock_token);
				}
				foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
				do_action('ws_plugin__s2member_during_paypal_notify_after_subscr_payment', get_defined_vars());
				unset($__refs, $__v);

				return apply_filters('c_ws_plugin__s2member_paypal_notify_in_subscr_or_rp_payment_w_level', $paypal, get_defined_vars());
			}
			else return apply_filters('c_ws_plugin__s2member_paypal_notify_in_subscr_or_rp_payment_w_level', FALSE, get_defined_vars());
		}
	}
}
