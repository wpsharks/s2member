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

if(!class_exists('c_ws_plugin__s2member_paypal_notify_in_web_accept_sp'))
{
	/**
	 * s2Member's PayPal IPN handler (inner processing routine).
	 *
	 * @package s2Member\PayPal
	 * @since 110720
	 */
	class c_ws_plugin__s2member_paypal_notify_in_web_accept_sp
	{
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

			if((!empty($paypal['txn_type']) && preg_match('/^web_accept$/i', $paypal['txn_type']))
			   && (!empty($paypal['item_number']) && preg_match($GLOBALS['WS_PLUGIN__']['s2member']['c']['sp_access_item_number_regex'], $paypal['item_number']))
			   && c_ws_plugin__s2member_paypal_utilities::paypal_payment_status_allows_access($paypal, $payment_status_issues)
			   && (!empty($paypal['payer_email'])) && (!empty($paypal['txn_id'])) && (!empty($paypal['txn_baid']) || ($paypal['txn_baid'] = $paypal['txn_id']))
			   && (!empty($paypal['txn_cid']) || ($paypal['txn_cid'] = $paypal['txn_id']))
			)
			{
				foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
				do_action('ws_plugin__s2member_during_paypal_notify_before_sp_access', get_defined_vars());
				unset($__refs, $__v);

				//261111.0100 Lock by authenticated gateway + transaction, not mutable full-IPN serialization; pending and completed payments were gated above.
				$purchase_key = c_ws_plugin__s2member_paypal_utilities::paypal_purchase_event_key($paypal, 'sp');
				$purchase_lock = 's2m_purchase_lock_'.$purchase_key;
				$purchase_done = 's2m_purchase_done_'.$purchase_key;
				$purchase_done_ttl = 31556926 * 10; // Preserve the earlier IPN transient's ten-year duplicate horizon.
				$purchase_completed = FALSE;
				$purchase_started = FALSE;
				//261111.0104 Existing ten-year transients are read for upgrade compatibility, but new messages are only written to stable transaction markers.
				$legacy_ipn_seen = !empty($_paypal_s) && is_string($_paypal_s) && get_transient('s2m_ipn_'.md5('s2member_transient_'.$_paypal_s));
				if($purchase_key !== false)
					c_ws_plugin__s2member_paypal_utilities::dedupe_markers_cleanup('s2m_purchase_gc', array(
						array('prefix' => 's2m_purchase_lock_', 'ttl' => 900),
						array('prefix' => 's2m_purchase_done_', 'ttl' => $purchase_done_ttl),
					), DAY_IN_SECONDS);

				if($purchase_key !== false && !$legacy_ipn_seen
				&& !c_ws_plugin__s2member_paypal_utilities::paypal_purchase_done_confirmed($purchase_done, $purchase_done_ttl)
				&& ($purchase_lock_token = c_ws_plugin__s2member_paypal_utilities::paypal_purchase_lock_claim($purchase_lock, $purchase_done, 900, $purchase_done_ttl)))
				{
					$paypal['s2member_log'][] = 's2Member `txn_type` identified as ( `web_accept` ) for Specific Post/Page Access.';

					list (, $paypal['sp_ids'], $paypal['hours']) = preg_split('/\:/', $paypal['item_number'], 3);

					//260814 Set optional gateway elements used below so sparse/null payload fields do not trigger PHP diagnostics.
					$paypal = c_ws_plugin__s2member_utils_arrays::set_unset_elements($paypal, array('option_name1', 'option_selection1', 'option_name2', 'option_selection2', 'invoice', 'first_name', 'last_name'));

					$paypal['ip'] = (preg_match('/ip address/i', $paypal['option_name2']) && $paypal['option_selection2']) ? $paypal['option_selection2'] : '';
					$paypal['ip'] = (!$paypal['ip'] && preg_match('/^[a-z0-9]+~[0-9\.]+$/i', $paypal['invoice'])) ? preg_replace('/^[a-z0-9]+~/i', '', $paypal['invoice']) : $paypal['ip'];

					$paypal['currency']        = strtoupper($paypal['mc_currency']); // Normalize input currency.
					$paypal['currency_symbol'] = c_ws_plugin__s2member_utils_cur::symbol($paypal['currency']);

					if(($sp_access_url = c_ws_plugin__s2member_sp_access::sp_access_link_gen($paypal['sp_ids'], $paypal['hours'])))
					{
						//261116.0439 Establish an owned checkpoint before fulfillment side effects in this handler.
						if(!c_ws_plugin__s2member_paypal_utilities::paypal_purchase_lock_mark_started($purchase_lock, $purchase_lock_token))
						{
							$paypal['s2member_notification_retry_error'] = 's2m_purchase_checkpoint_unavailable';
							$paypal['s2member_log'][] = 'Purchase checkpoint could not be persisted; payment is not acknowledged.';
							c_ws_plugin__s2member_paypal_utilities::paypal_purchase_lock_release($purchase_lock, $purchase_lock_token);
							return apply_filters('c_ws_plugin__s2member_paypal_notify_in_web_accept_sp', $paypal, get_defined_vars());
						}
						$purchase_started = TRUE;
						$processing = $during = TRUE; // Yes, we ARE processing this.

						if(!empty($coupon['coupon_code']) && c_ws_plugin__s2member_utils_conds::pro_is_installed())
							{
								$coupon_class = new c_ws_plugin__s2member_pro_coupons();
								$coupon_class->update_uses($coupon['coupon_code']);
							}

						if(preg_match('/(referenc|associat)/i', $paypal['option_name1']) && $paypal['option_selection1']) // Associating this purchase with a Member?
							if(($user_id = c_ws_plugin__s2member_utils_users::get_user_id_with($paypal['option_selection1'], $paypal['option_selection1'])) && is_object($user = new WP_User ($user_id)) && $user->ID)
							{
								$sp_references = (array)get_user_option('s2member_sp_references', $user_id);
								$_sp_reference = array('time' => time(), 'ids' => $paypal['sp_ids'], 'hours' => $paypal['hours'], 'url' => $sp_access_url);
								$sp_references = c_ws_plugin__s2member_utils_arrays::array_unique(array_merge($sp_references, $_sp_reference));
								update_user_option($user_id, 's2member_sp_references', $sp_references);

								if(!empty($coupon['full_coupon_code']) && c_ws_plugin__s2member_utils_conds::pro_is_installed())
								{
									//250214 Improved coupon use logging.
									c_ws_plugin__s2member_pro_coupons::log_user_coupons_use($user_id, $coupon);
								}
								$paypal['s2member_log'][] = 'Specific Post/Page ~ Sale associated with User ID: '.$user_id.'.';
							}
						//261109.1514 Keep configured originals available to get_defined_vars() consumers after the copies are rendered.
						$original_sbj = $GLOBALS['WS_PLUGIN__']['s2member']['o'][(($_REQUEST['s2member_paypal_proxy'] && preg_match('/pro-emails/', $_REQUEST['s2member_paypal_proxy_use'])) ? 'pro_' : '').'sp_email_subject'];
						$sbj = $GLOBALS['WS_PLUGIN__']['s2member']['o'][(($_REQUEST['s2member_paypal_proxy'] && preg_match('/pro-emails/', $_REQUEST['s2member_paypal_proxy_use'])) ? 'pro_' : '').'sp_email_subject'];

						$original_msg = $GLOBALS['WS_PLUGIN__']['s2member']['o'][(($_REQUEST['s2member_paypal_proxy'] && preg_match('/pro-emails/', $_REQUEST['s2member_paypal_proxy_use'])) ? 'pro_' : '').'sp_email_message'];
						$msg = $GLOBALS['WS_PLUGIN__']['s2member']['o'][(($_REQUEST['s2member_paypal_proxy'] && preg_match('/pro-emails/', $_REQUEST['s2member_paypal_proxy_use'])) ? 'pro_' : '').'sp_email_message'];

						$original_rec = $GLOBALS['WS_PLUGIN__']['s2member']['o'][(($_REQUEST['s2member_paypal_proxy'] && preg_match('/pro-emails/', $_REQUEST['s2member_paypal_proxy_use'])) ? 'pro_' : '').'sp_email_recipients'];
						$rec = $GLOBALS['WS_PLUGIN__']['s2member']['o'][(($_REQUEST['s2member_paypal_proxy'] && preg_match('/pro-emails/', $_REQUEST['s2member_paypal_proxy_use'])) ? 'pro_' : '').'sp_email_recipients'];

						$paypal = c_ws_plugin__s2member_utils_strings::strip_php_tags_deep($paypal); //251226
						//261109.1531 Member confirmation: resolve the three output-specific templates before sending; an empty required part suppresses the email.
						if(($rec = c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($rec, c_ws_plugin__s2member_utils_strings::paypal_ipn_template_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'web-accept-sp-1-rec'), 'recipient')) &&
							($sbj = c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($sbj, c_ws_plugin__s2member_utils_strings::paypal_ipn_template_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'web-accept-sp-2-sbj'), 'subject')) &&
							($msg = c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($msg, c_ws_plugin__s2member_utils_strings::paypal_ipn_template_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'web-accept-sp-3-msg'), !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['html_emails_enabled']) ? 'html' : 'text')) &&
							($rec = trim($rec)) && ($sbj = trim($sbj)) && ($msg = trim($msg)))
						{
							foreach(c_ws_plugin__s2member_utils_strings::parse_emails($rec) as $recipient) // Go through a possible list of recipients.
							{
								//250617 HTML email support.
								if (empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['html_emails_enabled'])) {
									//261109.1726 Scope temporary WordPress sender filters to this plaintext notification; other emails must not inherit them.
									c_ws_plugin__s2member_email_configs::email_config();
									wp_mail($recipient, apply_filters('ws_plugin__s2member_sp_email_sbj', $sbj, get_defined_vars()), apply_filters('ws_plugin__s2member_sp_email_msg', $msg, get_defined_vars()), 'From: "'.preg_replace('/"/', '"', $GLOBALS['WS_PLUGIN__']['s2member']['o']['reg_email_from_name']).'" <'.$GLOBALS['WS_PLUGIN__']['s2member']['o']['reg_email_from_email'].'>'."\r\n".'Content-Type: text/plain; charset=UTF-8');
									c_ws_plugin__s2member_email_configs::email_config_release();
								} else {
									c_ws_plugin__s2member_utilities::mail($recipient, apply_filters('ws_plugin__s2member_sp_email_sbj', $sbj, get_defined_vars()), apply_filters('ws_plugin__s2member_sp_email_msg', $msg, get_defined_vars()));
								}
							}

							$paypal['s2member_log'][] = 'Specific Post/Page Confirmation Email sent to: '.$rec.'.';
						}

						//261109.1531 Webhook/API URL notifications: send each configured URL independently; an empty rendered URL must not skip later entries.
						if($processing && $GLOBALS['WS_PLUGIN__']['s2member']['o']['sp_sale_notification_urls'])
						{
							foreach(preg_split('/['."\r\n\t".']+/', $GLOBALS['WS_PLUGIN__']['s2member']['o']['sp_sale_notification_urls']) as $url)
							{
								if(($url = c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($url, c_ws_plugin__s2member_utils_strings::paypal_ipn_template_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'web-accept-sp-4-url'), 'url')) && ($url = trim($url)))
								{
									c_ws_plugin__s2member_utils_urls::remote($url);
								}
							}

							$paypal['s2member_log'][] = 'Specific Post/Page ~ Sale Notification URLs have been processed.';
						}

						//261109.1531 API notification email: assemble a plaintext event summary for configured recipients.
						if($processing && $GLOBALS['WS_PLUGIN__']['s2member']['o']['sp_sale_notification_recipients'])
						{
							$msg = $sbj = '(s2Member / API Notification Email) - Specific Post/Page ~ Sale';
							$msg .= "\n\n"; // Spacing in the message body.

							$msg .= 'sp_access_url: %%sp_access_url%%'."\n";
							$msg .= 'sp_access_exp: %%sp_access_exp%%'."\n";

							$msg .= 'currency: %%currency%%'."\n";
							$msg .= 'currency_symbol: %%currency_symbol%%'."\n";
							$msg .= 'amount: %%amount%%'."\n";
							$msg .= 'txn_id: %%txn_id%%'."\n";
							$msg .= 'txn_baid: %%txn_baid%%'."\n";
							$msg .= 'txn_cid: %%txn_cid%%'."\n";
							$msg .= 'item_number: %%item_number%%'."\n";
							$msg .= 'item_name: %%item_name%%'."\n";
							$msg .= 'first_name: %%first_name%%'."\n";
							$msg .= 'last_name: %%last_name%%'."\n";
							$msg .= 'full_name: %%full_name%%'."\n";
							$msg .= 'payer_email: %%payer_email%%'."\n";
							$msg .= 'user_ip: %%user_ip%%'."\n";

							$msg .= 'full_coupon_code: %%full_coupon_code%%'."\n";
							$msg .= 'coupon_code: %%coupon_code%%'."\n";
							$msg .= 'coupon_affiliate_id: %%coupon_affiliate_id%%'."\n";

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
							$original_msg = $msg; //261109.1514 Preserve the raw message for get_defined_vars() consumers after rendering mutates $msg.

							//261109.1531 Only render after appending every event/custom-field row; replacement values are not rescanned.
							if(($msg = c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($msg, c_ws_plugin__s2member_utils_strings::paypal_ipn_template_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'web-accept-sp-5-msg'), 'text')) && $sbj && ($msg = trim($msg)))
							{
								foreach(c_ws_plugin__s2member_utils_strings::parse_emails($GLOBALS['WS_PLUGIN__']['s2member']['o']['sp_sale_notification_recipients']) as $recipient)
								{
									wp_mail($recipient, apply_filters('ws_plugin__s2member_sp_sale_notification_email_sbj', $sbj, get_defined_vars()), apply_filters('ws_plugin__s2member_sp_sale_notification_email_msg', $msg, get_defined_vars()), 'Content-Type: text/plain; charset=UTF-8');
								}
							}

							$paypal['s2member_log'][] = 'Specific Post/Page ~ Sale Notification Emails have been processed.';
						}
						//260827.0637 Proxy return URLs are optional; avoid PHP warnings when a valid proxy request does not include one.
						if($processing && !empty($_REQUEST['s2member_paypal_proxy']) && !empty($_REQUEST['s2member_paypal_proxy_return_url']) && ($url = $_REQUEST['s2member_paypal_proxy_return_url'])) // A Proxy is requesting a Return URL?
						{
							//261109.1512 Proxy return templates can contain placeholders this handler cannot resolve; url-preserve keeps them intact for the proxy consumer.
							if(($url = c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($url, c_ws_plugin__s2member_utils_strings::paypal_ipn_template_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'web-accept-sp-6-url'), 'url-preserve')) && ($url = trim($url)))
							{
								$paypal['s2member_paypal_proxy_return_url'] = $url;
							}

							$paypal['s2member_log'][] = 'Specific Post/Page Return, a Proxy Return URL is ready.';
						}

						//261109.1531 On-site tracking: queue the rendered code for the next visitor request instead of running it during this IPN.
						if($processing && ($code = $GLOBALS['WS_PLUGIN__']['s2member']['o']['sp_tracking_codes']))
						{
							if(($code = trim(c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($code, c_ws_plugin__s2member_utils_strings::paypal_ipn_tracking_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'web-accept-sp-7-code'), 'tracking'))))
							{
								$paypal['s2member_log'][] = 'Storing Specific Post/Page Tracking Codes into a Transient Queue. These will be processed on-site.';
								$code = str_replace('<?', '-', $code);
								set_transient('s2m_'.md5('s2member_transient_sp_tracking_codes_'.$paypal['txn_id']), $code, 43200);
							}
						}

						foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
						do_action('ws_plugin__s2member_during_paypal_notify_during_sp_access', get_defined_vars());
						unset($__refs, $__v);
						//261111.0100 Record completion only after all Specific Post/Page work has returned; a missing link may be retried.
						$purchase_completed = TRUE;
					}
					else $paypal['s2member_log'][] = 'Unable to generate Access Link for Specific Post/Page Access. Does your Leading Post/Page still exist?';
					//261116.0439 Retain the started lock on uncertain completion; only verified completion permits release.
					if($purchase_completed && c_ws_plugin__s2member_paypal_utilities::paypal_purchase_complete($purchase_done, $purchase_done_ttl))
						c_ws_plugin__s2member_paypal_utilities::paypal_purchase_lock_release($purchase_lock, $purchase_lock_token);
					else
					{
						$paypal['s2member_notification_retry_error'] = 's2m_purchase_not_confirmed';
						$paypal['s2member_log'][] = 'Purchase outcome remains unconfirmed; do not replay side effects.';
						if(!$purchase_started)
							c_ws_plugin__s2member_paypal_utilities::paypal_purchase_lock_release($purchase_lock, $purchase_lock_token);
					}
				}
				else // A historical/completed duplicate, or a payment whose outcome is not yet known.
				{
					if(!$legacy_ipn_seen && ($purchase_key === false || !c_ws_plugin__s2member_paypal_utilities::paypal_purchase_done_confirmed($purchase_done, $purchase_done_ttl)))
					{
						//261116.0439 A live or uncertain lock is not proof of fulfillment; request a provider retry.
						$paypal['s2member_notification_retry_error'] = 's2m_purchase_in_flight_or_uncertain';
						$paypal['s2member_log'][] = 'Purchase still processing or uncertain; no completion acknowledged.';
					}
					else
					{
						$paypal['s2member_log'][] = 'Not processing. Duplicate IPN.';
						$paypal['s2member_log'][] = 'Duplicate IPN. Already processed. This IPN will be ignored.';
					}
					$paypal['s2member_log'][] = 's2Member `txn_type` identified as ( `web_accept` ) for Specific Post/Page Access.';

				}
				foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
				do_action('ws_plugin__s2member_during_paypal_notify_after_sp_access', get_defined_vars());
				unset($__refs, $__v);

				return apply_filters('c_ws_plugin__s2member_paypal_notify_in_web_accept_sp', $paypal, get_defined_vars());
			}
			else return apply_filters('c_ws_plugin__s2member_paypal_notify_in_web_accept_sp', FALSE, get_defined_vars());
		}
	}
}
