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
 * @since 110815
 */
if(!defined('WPINC')) // MUST have WordPress.
	exit ('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_paypal_notify_in_wa_ccaps_wo_level'))
{
	/**
	 * s2Member's PayPal IPN handler (inner processing routine).
	 *
	 * @package s2Member\PayPal
	 * @since 110815
	 */
	class c_ws_plugin__s2member_paypal_notify_in_wa_ccaps_wo_level
	{
		/**
		 * s2Member's PayPal IPN handler (inner processing routine).
		 *
		 * @package s2Member\PayPal
		 * @since 110815
		 *
		 * @param array $vars Required. An array of defined variables passed by {@link s2Member\PayPal\c_ws_plugin__s2member_paypal_notify_in::paypal_notify()}.
		 *
		 * @return array|bool The original ``$paypal`` array passed in (extracted) from ``$vars``, or false when conditions do NOT apply.
		 */
		public static function cp($vars = array()) // Conditional phase for ``c_ws_plugin__s2member_paypal_notify_in::paypal_notify()``.
		{
			extract($vars, EXTR_OVERWRITE | EXTR_REFS); // Extract all vars passed in from: ``c_ws_plugin__s2member_paypal_notify_in::paypal_notify()``.

			if((!empty($paypal['txn_type']) && preg_match('/^web_accept$/i', $paypal['txn_type']))
			   && (!empty($paypal['item_number']) && preg_match($GLOBALS['WS_PLUGIN__']['s2member']['c']['membership_item_number_wo_level_regex'], $paypal['item_number']))
			   && (empty($paypal['payment_status']) || empty($payment_status_issues) || !preg_match($payment_status_issues, $paypal['payment_status']))
			   && (!empty($paypal['txn_id'])) && (!empty($paypal['payer_email']))
			   && (!empty($paypal['txn_baid']) || ($paypal['txn_baid'] = $paypal['txn_id']))
			   && (!empty($paypal['txn_cid']) || ($paypal['txn_cid'] = $paypal['txn_id']))
			)
			{
				foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
				do_action('ws_plugin__s2member_during_paypal_notify_before_new_ccaps', get_defined_vars());
				unset($__refs, $__v);

				if(!get_transient($transient_ipn = 's2m_ipn_'.md5('s2member_transient_'.$_paypal_s)) && set_transient($transient_ipn, time(), 31556926 * 10))
				{
					$paypal['s2member_log'][] = 's2Member `txn_type` identified as ( `web_accept` ) w/ update vars for Capabilities w/o Level.';

					//260814 Parse and pad optional CCaps/EOT components instead of relying on undefined list() offsets.
					list ($paypal['level'], $paypal['ccaps'], $paypal['eotper']) = array_pad(explode(':', $paypal['item_number'], 3), 3, '');
					//260814 Set optional gateway elements used below so sparse/null payload fields do not trigger PHP diagnostics.
					$paypal = c_ws_plugin__s2member_utils_arrays::set_unset_elements($paypal, array('option_name1', 'option_selection1', 'option_name2', 'option_selection2', 'invoice', 'first_name', 'last_name'));

					$paypal['ip'] = (preg_match('/ip address/i', $paypal['option_name2']) && $paypal['option_selection2']) ? $paypal['option_selection2'] : '';
					$paypal['ip'] = (!$paypal['ip'] && preg_match('/^[a-z0-9]+~[0-9\.]+$/i', $paypal['invoice'])) ? preg_replace('/^[a-z0-9]+~/i', '', $paypal['invoice']) : $paypal['ip'];

					$paypal['currency']        = strtoupper($paypal['mc_currency']); // Normalize input currency.
					$paypal['currency_symbol'] = c_ws_plugin__s2member_utils_cur::symbol($paypal['currency']);

					if(!empty($coupon['coupon_code']) && c_ws_plugin__s2member_utils_conds::pro_is_installed())
						{
							$coupon_class = new c_ws_plugin__s2member_pro_coupons();
							$coupon_class->update_uses($coupon['coupon_code']);
						}
					if(preg_match('/(referenc|associat|updat|upgrad)/i', $paypal['option_name1']) && $paypal['option_selection1']) // Must have this information for Capability additions.
					{
						if(($user_id = c_ws_plugin__s2member_utils_users::get_user_id_with($paypal['txn_id'], $paypal['option_selection1'])) && is_object($user = new WP_User ($user_id)) && $user->ID)
						{
							if(!$user->has_cap('administrator')) // Do NOT process this routine on Administrators.
							{
								$processing = $during = TRUE; // Yes, we ARE processing this.

								foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
								do_action('ws_plugin__s2member_during_paypal_notify_during_before_new_ccaps', get_defined_vars());
								unset($__refs, $__v);

								$fields      = get_user_option('s2member_custom_fields', $user_id); // These will be needed in the routines below.
								$user_reg_ip = get_user_option('s2member_registration_ip', $user_id); // Original IP during Registration.
								$user_reg_ip = $paypal['ip'] = ($user_reg_ip) ? $user_reg_ip : $paypal['ip']; // Now merge conditionally.

								if(is_multisite() && !is_user_member_of_blog($user_id)) // Must have a Role on this Blog.
								{
									add_existing_user_to_blog(array('user_id' => $user_id, 'role' => get_option('default_role')));
									$user = new WP_User ($user_id);
								}
								if($paypal['ccaps'] && preg_match('/^-all/', str_replace('+', '', $paypal['ccaps'])))
									foreach($user->allcaps as $cap => $cap_enabled)
										if(preg_match('/^access_s2member_ccap_/', $cap))
											$user->remove_cap($ccap = $cap);

								if($paypal['ccaps'] && preg_replace('/^-all['."\r\n\t".'\s;,]*/', '', str_replace('+', '', $paypal['ccaps'])))
									foreach(preg_split('/['."\r\n\t".'\s;,]+/', preg_replace('/^-all['."\r\n\t".'\s;,]*/', '', str_replace('+', '', $paypal['ccaps']))) as $ccap)
										if(strlen($ccap = trim(strtolower(preg_replace('/[^a-z_0-9]/i', '', $ccap)))))
											$user->add_cap('access_s2member_ccap_'.$ccap);

								if(!get_user_option('s2member_registration_ip', $user_id))
									update_user_option($user_id, 's2member_registration_ip', $paypal['ip']);

								if(!empty($coupon['full_coupon_code']) && c_ws_plugin__s2member_utils_conds::pro_is_installed())
								{
									//250214 Improved coupon use logging.
									$processed_coupons = c_ws_plugin__s2member_pro_coupons::log_user_coupons_use($user_id, $coupon);
								}
								$paypal['s2member_log'][] = 's2Member Custom Capabilities updated w/ advanced update routines.';

								$sbj = $GLOBALS['WS_PLUGIN__']['s2member']['o']['ccap_email_subject']; // The same for standard and w/ Pro-Forms.
								$msg = $GLOBALS['WS_PLUGIN__']['s2member']['o']['ccap_email_message']; // The same for standard and w/ Pro-Forms.
								$rec = $GLOBALS['WS_PLUGIN__']['s2member']['o']['ccap_email_recipients']; // The same for standard and w/ Pro-Forms.

								$paypal = c_ws_plugin__s2member_utils_strings::strip_php_tags_deep($paypal); //251226
								//261109.0511 Render IPN template with the supported fields.
								if(($rec = c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($rec, c_ws_plugin__s2member_utils_strings::paypal_ipn_template_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'wa-ccaps-wo-level-1-rec'), 'recipient')))

																							//261109.0511 Render IPN template with the supported fields.
																							if(($sbj = c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($sbj, c_ws_plugin__s2member_utils_strings::paypal_ipn_template_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'wa-ccaps-wo-level-2-sbj'), 'subject')))

																																						//261109.0511 Render IPN template with the supported fields.
																																						if(($msg = c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($msg, c_ws_plugin__s2member_utils_strings::paypal_ipn_template_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'wa-ccaps-wo-level-3-msg'), !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['html_emails_enabled']) ? 'html' : 'text')))


																																																				{
																																																					if(($rec = trim($rec)) && ($sbj = trim($sbj)) && ($msg = trim($msg)))
																																																					{
																																																						foreach(c_ws_plugin__s2member_utils_strings::parse_emails($rec) as $recipient  /* Go through a possible list of recipients. */)
																																																						{
																																																							//250617 HTML email support.
																																																							if (empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['html_emails_enabled'])) {
																																																								c_ws_plugin__s2member_email_configs::email_config().wp_mail($recipient, apply_filters('ws_plugin__s2member_capabilities_email_sbj', $sbj, get_defined_vars()), apply_filters('ws_plugin__s2member_capabilities_email_msg', $msg, get_defined_vars()), 'From: "'.preg_replace('/"/', '"', $GLOBALS['WS_PLUGIN__']['s2member']['o']['reg_email_from_name']).'" <'.$GLOBALS['WS_PLUGIN__']['s2member']['o']['reg_email_from_email'].'>'."\r\n".'Content-Type: text/plain; charset=UTF-8').c_ws_plugin__s2member_email_configs::email_config_release();;
																																																							} else {
																																																								c_ws_plugin__s2member_utilities::mail($recipient, apply_filters('ws_plugin__s2member_capabilities_email_sbj', $sbj, get_defined_vars()), apply_filters('ws_plugin__s2member_capabilities_email_msg', $msg, get_defined_vars()));
																																																							}
																																																						}

																																																						$paypal['s2member_log'][] = 'Capability Confirmation Email sent to: '.$rec.'.';
																																																					}
																																																				}
								//260827.0637 Proxy return URLs are optional; avoid PHP warnings when a valid proxy request does not include one.
								if($processing && !empty($_REQUEST['s2member_paypal_proxy']) && !empty($_REQUEST['s2member_paypal_proxy_return_url']) && ($url = $_REQUEST['s2member_paypal_proxy_return_url'])) // A Proxy is requesting a Return URL?
								{
									//261109.0511 Render IPN template with the supported fields.
									if(($url = c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($url, c_ws_plugin__s2member_utils_strings::paypal_ipn_template_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'wa-ccaps-wo-level-4-url'), 'url-preserve')))


																							{

																								if(($url = trim($url))) // Preserve remaining replacements.
																									// Because the parent routine may perform replacements too.
																									$paypal['s2member_paypal_proxy_return_url'] = $url;
																							}
									$paypal['s2member_log'][] = 'Capability Return, a Proxy Return URL is ready.';
								}
								if($processing && $GLOBALS['WS_PLUGIN__']['s2member']['o']['payment_notification_urls'])
								{
									foreach(preg_split('/['."\r\n\t".']+/', $GLOBALS['WS_PLUGIN__']['s2member']['o']['payment_notification_urls']) as $url)

										//261109.0511 Render IPN template with the supported fields.
										if(($url = c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($url, c_ws_plugin__s2member_utils_strings::paypal_ipn_template_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'wa-ccaps-wo-level-5-url'), 'url')))


																								{

																									if(($url = trim($url)))
																										c_ws_plugin__s2member_utils_urls::remote($url);
																								}
									$paypal['s2member_log'][] = 'Payment Notification URLs have been processed.';
								}
								if($processing && $GLOBALS['WS_PLUGIN__']['s2member']['o']['payment_notification_recipients'])
								{
									$msg = $sbj = '(s2Member / API Notification Email) - Payment';
									$msg .= "\n\n"; // Spacing in the message body.

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

									$msg .= 'full_coupon_code: %%full_coupon_code%%'."\n";
									$msg .= 'coupon_code: %%coupon_code%%'."\n";
									$msg .= 'coupon_affiliate_id: %%coupon_affiliate_id%%'."\n";

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

									//261109.0511 Render IPN template with the supported fields.
									if(($msg = c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($msg, c_ws_plugin__s2member_utils_strings::paypal_ipn_template_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'wa-ccaps-wo-level-6-msg'), 'text')))


																							{

																								if($sbj && ($msg = trim($msg))) // Still have a ``$sbj`` and a ``$msg``?

																									foreach(c_ws_plugin__s2member_utils_strings::parse_emails($GLOBALS['WS_PLUGIN__']['s2member']['o']['payment_notification_recipients']) as $recipient)
																										wp_mail($recipient, apply_filters('ws_plugin__s2member_payment_notification_email_sbj', $sbj, get_defined_vars()), apply_filters('ws_plugin__s2member_payment_notification_email_msg', $msg, get_defined_vars()), 'Content-Type: text/plain; charset=UTF-8');
																							}
									$paypal['s2member_log'][] = 'Payment Notification Emails have been processed.';
								}
								if($processing && ($code = $GLOBALS['WS_PLUGIN__']['s2member']['o']['ccap_tracking_codes']))
								{
									//261109.0511 Render tracking data in a single pass.
									if(($code = trim(c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($code, c_ws_plugin__s2member_utils_strings::paypal_ipn_tracking_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'wa-ccaps-wo-level-7-code'), 'tracking'))))
									{
										$paypal['s2member_log'][] = 'Storing Payment Tracking Codes into a Transient Queue. These will be processed on-site.';
										$code = str_replace('<?', '-', $code);
										set_transient('s2m_'.md5('s2member_transient_ccap_tracking_codes_'.$paypal['txn_id']), $code, 43200);
									}
								}
								foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
								do_action('ws_plugin__s2member_during_paypal_notify_during_new_ccaps', get_defined_vars());
								unset($__refs, $__v);
							}
							else $paypal['s2member_log'][] = 'Unable to add new Capabilities. The existing User ID is associated with an Administrator. Stopping here. Otherwise, an Administrator could lose access.';
						}
						else $paypal['s2member_log'][] = 'Unable to add new Capabilities. Could not get the existing User ID from the DB. Please check the `on0` and `os0` variables in your Button Code.';
					}
					else $paypal['s2member_log'][] = 'Unable to add new Capabilities. Missing User/Member details. Please check the `on0` and `os0` variables in your Button Code.';
				}
				else // Else, this is a duplicate IPN. Must stop here.
				{
					$paypal['s2member_log'][] = 'Not processing. Duplicate IPN.';
					$paypal['s2member_log'][] = 's2Member `txn_type` identified as ( `web_accept` ) w/ update vars for Capabilities w/o Level.';
					$paypal['s2member_log'][] = 'Duplicate IPN. Already processed. This IPN will be ignored.';
				}
				foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
				do_action('ws_plugin__s2member_during_paypal_notify_after_new_ccaps', get_defined_vars());
				unset($__refs, $__v);

				return apply_filters('c_ws_plugin__s2member_paypal_notify_in_wa_ccaps_wo_level', $paypal, get_defined_vars());
			}
			else return apply_filters('c_ws_plugin__s2member_paypal_notify_in_wa_ccaps_wo_level', FALSE, get_defined_vars());
		}
	}
}
