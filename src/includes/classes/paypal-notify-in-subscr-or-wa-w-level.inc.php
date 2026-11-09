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

if(!class_exists('c_ws_plugin__s2member_paypal_notify_in_subscr_or_wa_w_level'))
{
	/**
	 * s2Member's PayPal IPN handler (inner processing routine).
	 *
	 * @package s2Member\PayPal
	 * @since 110720
	 */
	class c_ws_plugin__s2member_paypal_notify_in_subscr_or_wa_w_level
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
		public static function cp($vars = array() /* Conditional phase for ``c_ws_plugin__s2member_paypal_notify_in::paypal_notify()``. */)
		{
			extract($vars, EXTR_OVERWRITE | EXTR_REFS); // Extract all vars passed in from: ``c_ws_plugin__s2member_paypal_notify_in::paypal_notify()``.

			if((!empty($paypal['txn_type']) && preg_match('/^(web_accept|subscr_signup)$/i', $paypal['txn_type']))
			   && (!empty($paypal['item_number']) && preg_match($GLOBALS['WS_PLUGIN__']['s2member']['c']['membership_item_number_w_level_regex'], $paypal['item_number']))
			   && (!empty($paypal['subscr_id']) || (!empty($paypal['txn_id']) && ($paypal['subscr_id'] = $paypal['txn_id'])))
			   && (empty($paypal['payment_status']) || empty($payment_status_issues) || !preg_match($payment_status_issues, $paypal['payment_status']))
			   && (!empty($paypal['subscr_baid']) || ($paypal['subscr_baid'] = $paypal['subscr_id']))
			   && (!empty($paypal['subscr_cid']) || ($paypal['subscr_cid'] = $paypal['subscr_id']))
			   && (!empty($paypal['payer_email']))
			)
			{
				// Fix some IDs for Stripe...
				if ($paypal['subscr_gateway'] == 'stripe' && $paypal['txn_type'] == 'web_accept') {
					// Above, if subscr_id was empty, it got the value of txn_id.
					// Only save the subscr_id if it's a Subscription ID (subscr_signup), not a Payment Intent ID (web_accept).
					// If subscr_id has a value, s2Member will query Stripe for a subscription that doesn't exist and get errors.
					// If it was empty, then this is not a subscr_signup, don't give it the wrong value, better empty it again.
					// If it is needed to have a web_accept Payment Intent ID, then that may need its own field.
					// !!! Seems s2 searches for customer in usermeta using the subscr_id instead of subscr_cid:
					//  c_ws_plugin__s2member_utils_users::get_user_id_with($paypal['subscr_id']...
					//  Seems to be only in paypal-notify-in- and paypayl-return-in- files.
					//  Will need to come back to this...
					//  Leave subscr_id with txn_id for now, although it may cause the Stripe exception:
					//  "Customer cus_xxxx does not have a subscription with ID pi_xxxx",
					//  but it's not visible in the UI, only in the API log.
					// $paypal['subscr_id'] = '';

					// Above, if subscr_cid was empty (it's not a subscr_signup), it got the value of txn_id (Payment Intent ID).
					// The cid is the Customer ID, should not be a Payment Intent or Subscription ID.
					// !!! Maybe should rename this field to Stripe Customer ID (cus_id).
					$paypal['subscr_cid'] = $paypal['txn_cid'];
				}

				foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
				do_action('ws_plugin__s2member_during_paypal_notify_before_subscr_signup', get_defined_vars());
				unset($__refs, $__v);

				if(!get_transient($transient_ipn = 's2m_ipn_'.md5('s2member_transient_'.$_paypal_s)) && set_transient($transient_ipn, time(), 31556926 * 10))
				{
					$processing = $modifying = $during = FALSE; // Initialize these flags.

					$paypal['s2member_log'][] = 's2Member `txn_type` identified as ( `web_accept|subscr_signup` ).';

					//260814 Parse and pad optional CCaps/EOT components instead of relying on undefined list() offsets.
					list ($paypal['level'], $paypal['ccaps'], $paypal['eotper']) = array_pad(explode(':', $paypal['item_number'], 3), 3, '');
					//260814 Set optional gateway elements used below so sparse/null payload fields do not trigger PHP diagnostics.
					$paypal = c_ws_plugin__s2member_utils_arrays::set_unset_elements($paypal, array('option_name1', 'option_selection1', 'option_name2', 'option_selection2', 'invoice', 'first_name', 'last_name'));

					$paypal['ip'] = (preg_match('/ip address/i', $paypal['option_name2']) && $paypal['option_selection2']) ? $paypal['option_selection2'] : '';
					$paypal['ip'] = (!$paypal['ip'] && preg_match('/^[a-z0-9]+~[0-9\.]+$/i', $paypal['invoice'])) ? preg_replace('/^[a-z0-9]+~/i', '', $paypal['invoice']) : $paypal['ip'];

					$paypal['period1']    = (isset($paypal['period1']) && preg_match('/^[1-9]/', $paypal['period1'])) ? $paypal['period1'] : '0 D';
					$paypal['mc_amount1'] = (isset($paypal['mc_amount1']) && $paypal['mc_amount1'] > 0) ? $paypal['mc_amount1'] : '0.00';

					if(preg_match('/^web_accept$/i', $paypal['txn_type']) /* Conversions for lifetime & fixed-term sales. */)
					{
						$paypal['period3']    = ($paypal['eotper']) ? $paypal['eotper'] : '1 L'; // 1 lifetime.
						$paypal['mc_amount3'] = $paypal['mc_gross']; // The 'Buy Now' amount is the full gross.
					}
					$paypal['initial_term']    = (preg_match('/^[1-9]/', $paypal['period1'])) ? $paypal['period1'] : '0 D';
					$paypal['initial']         = isset($paypal['mc_amount1'][0]) && preg_match('/^[1-9]/', $paypal['period1']) ? $paypal['mc_amount1'] : $paypal['mc_amount3'];
					$paypal['regular']         = $paypal['mc_amount3']; // This is the regular payment amount that is charged to the customer. always required by PayPal.
					$paypal['regular_term']    = $paypal['period3']; // This is just set to keep a standard; this way both initial_term & regular_term are available.
					$paypal['recurring']       = !empty($paypal['recurring']) ? $paypal['mc_amount3'] : '0'; // If non-recurring, this should be zero, otherwise regular.
					$paypal['currency']        = strtoupper($paypal['mc_currency']); // Normalize input currency.
					$paypal['currency_symbol'] = c_ws_plugin__s2member_utils_cur::symbol($paypal['currency']);

					if(!empty($coupon['coupon_code']) && c_ws_plugin__s2member_utils_conds::pro_is_installed())
						{
							$coupon_class = new c_ws_plugin__s2member_pro_coupons();
							$coupon_class->update_uses($coupon['coupon_code']);
						}
					$ipn_signup_vars = $paypal; // Create array of IPN signup vars w/o one-shot processing markers/logs.
					unset($ipn_signup_vars['s2member_log'], $ipn_signup_vars['pending_new_user_signup']);
					/*
					New Subscription with advanced update vars (option_name1, option_selection1)? These variables are used in Subscr. Modifications.
					*/
					if(preg_match('/(referenc|associat|updat|upgrad)/i', $paypal['option_name1']) && $paypal['option_selection1'])
					{
						foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
						do_action('ws_plugin__s2member_during_paypal_notify_before_subscr_signup_w_update_vars', get_defined_vars());
						unset($__refs, $__v);

						$paypal['s2member_log'][] = 's2Member `txn_type` identified as ( `web_accept|subscr_signup` ) w/ update vars.';

						//261002.0228 A pending-new-user-signup is internally fulfilled through this existing-user update branch, but outwardly it must remain the original Stripe signup.
						$pending_new_user_signup = !empty($paypal['pending_new_user_signup']) && $paypal['subscr_gateway'] === 'stripe'
							&& !empty($_REQUEST['s2member_paypal_proxy']) && (string)$_REQUEST['s2member_paypal_proxy'] === 'stripe';

						if(($user_id = c_ws_plugin__s2member_utils_users::get_user_id_with($paypal['subscr_id'], $paypal['option_selection1'])) && is_object($user = new WP_User ($user_id)) && $user->ID)
						{
							if(!$user->has_cap('administrator')) // Do NOT process this routine on Administrators.
							{
								$processing = $during = TRUE; // Yes, we ARE processing this.
								$modifying = !$pending_new_user_signup; //261002.0228 Delayed activation completes the original signup; it is not a later membership modification.

								foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
								do_action('ws_plugin__s2member_during_paypal_notify_during_before_subscr_signup_w_update_vars', get_defined_vars());
								do_action('ws_plugin__s2member_during_collective_mods', $user_id, get_defined_vars(), 'ipn-upgrade-downgrade', 'modification', 's2member_level'.$paypal['level']);
								unset($__refs, $__v);

								$fields      = get_user_option('s2member_custom_fields', $user_id);
								$user_reg_ip = get_user_option('s2member_registration_ip', $user_id);
								$user_reg_ip = $paypal['ip'] = ($user_reg_ip) ? $user_reg_ip : $paypal['ip'];

								if(is_multisite() && !is_user_member_of_blog($user_id))
								{
									add_existing_user_to_blog(array('user_id' => $user_id, 'role' => 's2member_level'.$paypal['level']));
									$user = new WP_User ($user_id);
								}
								$current_role = c_ws_plugin__s2member_user_access::user_access_role($user);

								if($current_role !== 's2member_level'.$paypal['level'])
									$user->set_role('s2member_level'.$paypal['level']);

								if($paypal['ccaps'] && preg_match('/^-all/', str_replace('+', '', $paypal['ccaps'])))
									foreach($user->allcaps as $cap => $cap_enabled)
										if(preg_match('/^access_s2member_ccap_/', $cap))
											$user->remove_cap($ccap = $cap);

								if($paypal['ccaps'] && preg_replace('/^-all['."\r\n\t".'\s;,]*/', '', str_replace('+', '', $paypal['ccaps'])))
									foreach(preg_split('/['."\r\n\t".'\s;,]+/', preg_replace('/^-all['."\r\n\t".'\s;,]*/', '', str_replace('+', '', $paypal['ccaps']))) as $ccap)
										if(strlen($ccap = trim(strtolower(preg_replace('/[^a-z_0-9]/i', '', $ccap)))))
											$user->add_cap('access_s2member_ccap_'.$ccap);

								update_user_option($user_id, 's2member_subscr_gateway', $paypal['subscr_gateway']);
								update_user_option($user_id, 's2member_subscr_id', $paypal['subscr_id']);
								update_user_option($user_id, 's2member_subscr_baid', $paypal['subscr_baid']);
								update_user_option($user_id, 's2member_subscr_cid', $paypal['subscr_cid']);

								update_user_option($user_id, 's2member_custom', $paypal['custom']);

								if(!get_user_option('s2member_registration_ip', $user_id))
									update_user_option($user_id, 's2member_registration_ip', $paypal['ip']);

								update_user_option($user_id, 's2member_ipn_signup_vars', $ipn_signup_vars);

								delete_user_option($user_id, 's2member_file_download_access_log');

								if((preg_match('/^web_accept$/i', $paypal['txn_type']) || ($paypal['initial'] <= 0 && $paypal['regular'] <= 0)) && $paypal['eotper'])
								{
									update_user_option($user_id, 's2member_auto_eot_time', // Set exclusively by the IPN handler; to avoid duplicate extensions.
										($eot_time = c_ws_plugin__s2member_utils_time::auto_eot_time('', '', '', $paypal['eotper'], '', get_user_option('s2member_auto_eot_time', $user_id))));
									$paypal['s2member_log'][] = 'Automatic EOT (End Of Term) Time set to: '.date('D M j, Y g:i:s a T', $eot_time).'.';
								}
								else delete_user_option($user_id, 's2member_auto_eot_time');

								$pr_times                           = get_user_option('s2member_paid_registration_times', $user_id);
								$pr_times['level']                  = (!$pr_times['level']) ? time() : $pr_times['level']; // Preserve existing.
								$pr_times['level'.$paypal['level']] = (!$pr_times['level'.$paypal['level']]) ? time() : $pr_times['level'.$paypal['level']];
								update_user_option($user_id, 's2member_paid_registration_times', $pr_times);

								if(!empty($coupon['full_coupon_code']) && c_ws_plugin__s2member_utils_conds::pro_is_installed())
								{
									//250214 Improved coupon use logging.
									$processed_coupons = c_ws_plugin__s2member_pro_coupons::log_user_coupons_use($user_id, $coupon); // Flag for routines below.
								}
								c_ws_plugin__s2member_user_notes::clear_user_note_lines($user_id, '/^Demoted by s2Member\:/');
								c_ws_plugin__s2member_user_notes::clear_user_note_lines($user_id, '/^Paid Subscr\. ID @ time of demotion\:/');

								$paypal['s2member_log'][] = 's2Member Level/Capabilities updated w/ advanced update routines.';

								if($pending_new_user_signup)
								{
									//261002.0228 The New User/credentials email was already sent when the Level-0 holding account was created; successful delayed activation now gets the normal Pro Signup Confirmation.
									$sbj = $GLOBALS['WS_PLUGIN__']['s2member']['o']['pro_signup_email_subject'];
									$msg = $GLOBALS['WS_PLUGIN__']['s2member']['o']['pro_signup_email_message'];
									$rec = $GLOBALS['WS_PLUGIN__']['s2member']['o']['pro_signup_email_recipients'];
									$notification_urls       = $GLOBALS['WS_PLUGIN__']['s2member']['o']['signup_notification_urls'];
									$notification_recipients = $GLOBALS['WS_PLUGIN__']['s2member']['o']['signup_notification_recipients'];
									$tracking_codes          = $GLOBALS['WS_PLUGIN__']['s2member']['o']['signup_tracking_codes'];
									$notification_type       = 'signup';
								}
								else
								{
									$sbj = $GLOBALS['WS_PLUGIN__']['s2member']['o']['modification_email_subject']; // The same for standard and w/ Pro-Forms.
									$msg = $GLOBALS['WS_PLUGIN__']['s2member']['o']['modification_email_message']; // The same for standard and w/ Pro-Forms.
									$rec = $GLOBALS['WS_PLUGIN__']['s2member']['o']['modification_email_recipients']; // The same for standard and w/ Pro-Forms.
									$notification_urls       = $GLOBALS['WS_PLUGIN__']['s2member']['o']['modification_notification_urls'];
									$notification_recipients = $GLOBALS['WS_PLUGIN__']['s2member']['o']['modification_notification_recipients'];
									$tracking_codes          = $GLOBALS['WS_PLUGIN__']['s2member']['o']['modification_tracking_codes'];
									$notification_type       = 'modification';
								}

								//261109.1514 Retain the unrendered templates for get_defined_vars() consumers after rendering overwrites $rec/$sbj/$msg.
								$original_rec = $rec;
								$original_sbj = $sbj;
								$original_msg = $msg;
								$paypal = c_ws_plugin__s2member_utils_strings::strip_php_tags_deep($paypal); //251226
								//261109.1531 Member confirmation: resolve the three output-specific templates before sending; an empty required part suppresses the email.
								if(($rec = c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($rec, c_ws_plugin__s2member_utils_strings::paypal_ipn_template_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'subscr-or-wa-w-level-1-rec'), 'recipient')) &&
									($sbj = c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($sbj, c_ws_plugin__s2member_utils_strings::paypal_ipn_template_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'subscr-or-wa-w-level-2-sbj'), 'subject')) &&
									($msg = c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($msg, c_ws_plugin__s2member_utils_strings::paypal_ipn_template_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'subscr-or-wa-w-level-3-msg'), !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['html_emails_enabled']) ? 'html' : 'text')) &&
									($rec = trim($rec)) && ($sbj = trim($sbj)) && ($msg = trim($msg)))
								{
									foreach(c_ws_plugin__s2member_utils_strings::parse_emails($rec) as $recipient /* Go through a possible list of recipients. */)
									{
										//250617 HTML email support.
										if (empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['html_emails_enabled'])) {
											//261109.1726 Scope temporary WordPress sender filters to this plaintext notification; other emails must not inherit them.
											c_ws_plugin__s2member_email_configs::email_config();
											wp_mail($recipient, apply_filters('ws_plugin__s2member_'.$notification_type.'_email_sbj', $sbj, get_defined_vars()), apply_filters('ws_plugin__s2member_'.$notification_type.'_email_msg', $msg, get_defined_vars()), 'From: "'.preg_replace('/"/', "'", $GLOBALS['WS_PLUGIN__']['s2member']['o']['reg_email_from_name']).'" <'.$GLOBALS['WS_PLUGIN__']['s2member']['o']['reg_email_from_email'].'>'."\r\n".'Content-Type: text/plain; charset=UTF-8');
											c_ws_plugin__s2member_email_configs::email_config_release();
										} else {
											c_ws_plugin__s2member_utilities::mail($recipient, apply_filters('ws_plugin__s2member_'.$notification_type.'_email_sbj', $sbj, get_defined_vars()), apply_filters('ws_plugin__s2member_'.$notification_type.'_email_msg', $msg, get_defined_vars()));
										}
									}

									$paypal['s2member_log'][] = ucfirst($notification_type).' Confirmation Email sent to: '.$rec.'.';
								}

								//261109.1531 Webhook/API URL notifications: send each configured URL independently; an empty rendered URL must not skip later entries.
								if($processing && $notification_urls)
								{
									foreach(preg_split('/['."\r\n\t".']+/', $notification_urls) as $url)
									{
										if(($url = c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($url, c_ws_plugin__s2member_utils_strings::paypal_ipn_template_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'subscr-or-wa-w-level-4-url'), 'url')) && ($url = trim($url)))
										{
											c_ws_plugin__s2member_utils_urls::remote($url);
										}
									}
									$paypal['s2member_log'][] = ucfirst($notification_type).' Notification URLs have been processed.';
								}

								//261109.1531 API notification email: assemble a plaintext event summary for configured recipients.
								if($processing && $notification_recipients)
								{
									$msg = $sbj = '(s2Member / API Notification Email) - '.ucfirst($notification_type);
									$msg .= "\n\n"; // Spacing in the message body.

									$msg .= 'subscr_id: %%subscr_id%%'."\n";
									$msg .= 'subscr_baid: %%subscr_baid%%'."\n";
									$msg .= 'subscr_cid: %%subscr_cid%%'."\n";
									$msg .= 'currency: %%currency%%'."\n";
									$msg .= 'currency_symbol: %%currency_symbol%%'."\n";
									$msg .= 'initial: %%initial%%'."\n";
									$msg .= 'regular: %%regular%%'."\n";
									$msg .= 'recurring: %%recurring%%'."\n";
									$msg .= 'initial_term: %%initial_term%%'."\n";
									$msg .= 'regular_term: %%regular_term%%'."\n";
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
									$original_msg = $msg; //261109.1514 Preserve the raw message for get_defined_vars() consumers after rendering mutates $msg.

									//261109.1531 Only render after appending every event/custom-field row; replacement values are not rescanned.
									if(($msg = c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($msg, c_ws_plugin__s2member_utils_strings::paypal_ipn_template_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'subscr-or-wa-w-level-5-msg'), 'text')) && $sbj && ($msg = trim($msg)))
									{
										foreach(c_ws_plugin__s2member_utils_strings::parse_emails($notification_recipients) as $recipient)
										{
											wp_mail($recipient, apply_filters('ws_plugin__s2member_'.$notification_type.'_notification_email_sbj', $sbj, get_defined_vars()), apply_filters('ws_plugin__s2member_'.$notification_type.'_notification_email_msg', $msg, get_defined_vars()), 'Content-Type: text/plain; charset=UTF-8');
										}
									}
									$paypal['s2member_log'][] = ucfirst($notification_type).' Notification Emails have been processed.';
								}

								//261109.1531 On-site tracking: queue the rendered code for the next visitor request instead of running it during this IPN.
								if($processing && ($code = $tracking_codes))
								{
									if(($code = trim(c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($code, c_ws_plugin__s2member_utils_strings::paypal_ipn_tracking_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'subscr-or-wa-w-level-6-code'), 'tracking'))))
									{
										$paypal['s2member_log'][] = 'Storing '.ucfirst($notification_type).' Tracking Codes into a Transient Queue. These will be processed on-site.';
										$code = str_replace('<?', '-', $code);
										set_transient('s2m_'.md5('s2member_transient_'.$notification_type.'_tracking_codes_'.$paypal['subscr_id']), $code, 43200);
									}
								}

								foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
								do_action('ws_plugin__s2member_during_paypal_notify_during_subscr_signup_w_update_vars', get_defined_vars());
								unset($__refs, $__v);
							}
							else $paypal['s2member_log'][] = 'Unable to modify Subscription. The existing User ID is associated with an Administrator. Stopping here. Otherwise, an Administrator could lose access.';
						}
						else $paypal['s2member_log'][] = 'Unable to modify Subscription. Could not get the existing User ID from the DB. Please check the `on0` and `os0` variables in your Button Code.';

						foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
						do_action('ws_plugin__s2member_during_paypal_notify_after_subscr_signup_w_update_vars', get_defined_vars());
						unset($__refs, $__v);
					}
					/*
					New Subscription. Normal Subscription signup, we are not updating anything for a past Subscription.
					*/
					else // Else this is a normal Subscription signup, we are not updating anything.
					{
						foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
						do_action('ws_plugin__s2member_during_paypal_notify_before_subscr_signup_wo_update_vars', get_defined_vars());
						unset($__refs, $__v);

						$paypal['s2member_log'][] = 's2Member `txn_type` identified as ( `web_accept|subscr_signup` ) w/o update vars.';

						if(($registration_url = c_ws_plugin__s2member_register_access::register_link_gen($paypal['subscr_gateway'], $paypal['subscr_id'], $paypal['custom'], $paypal['item_number'])))
						{
							$processing = $during = TRUE; // Yes, we ARE processing this.

							//261109.1514 Keep configured originals available to get_defined_vars() consumers after the copies are rendered.
							$original_sbj = $GLOBALS['WS_PLUGIN__']['s2member']['o'][(($_REQUEST['s2member_paypal_proxy'] && preg_match('/pro-emails/', $_REQUEST['s2member_paypal_proxy_use'])) ? 'pro_' : '').'signup_email_subject'];
							$sbj = $GLOBALS['WS_PLUGIN__']['s2member']['o'][(($_REQUEST['s2member_paypal_proxy'] && preg_match('/pro-emails/', $_REQUEST['s2member_paypal_proxy_use'])) ? 'pro_' : '').'signup_email_subject'];
							$original_msg = $GLOBALS['WS_PLUGIN__']['s2member']['o'][(($_REQUEST['s2member_paypal_proxy'] && preg_match('/pro-emails/', $_REQUEST['s2member_paypal_proxy_use'])) ? 'pro_' : '').'signup_email_message'];
							$msg = $GLOBALS['WS_PLUGIN__']['s2member']['o'][(($_REQUEST['s2member_paypal_proxy'] && preg_match('/pro-emails/', $_REQUEST['s2member_paypal_proxy_use'])) ? 'pro_' : '').'signup_email_message'];
							$original_rec = $GLOBALS['WS_PLUGIN__']['s2member']['o'][(($_REQUEST['s2member_paypal_proxy'] && preg_match('/pro-emails/', $_REQUEST['s2member_paypal_proxy_use'])) ? 'pro_' : '').'signup_email_recipients'];
							$rec = $GLOBALS['WS_PLUGIN__']['s2member']['o'][(($_REQUEST['s2member_paypal_proxy'] && preg_match('/pro-emails/', $_REQUEST['s2member_paypal_proxy_use'])) ? 'pro_' : '').'signup_email_recipients'];

							$paypal = c_ws_plugin__s2member_utils_strings::strip_php_tags_deep($paypal); //251226
							//261109.1531 Member confirmation: resolve the three output-specific templates before sending; an empty required part suppresses the email.
							if(($rec = c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($rec, c_ws_plugin__s2member_utils_strings::paypal_ipn_template_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'subscr-or-wa-w-level-7-rec'), 'recipient')) &&
								($sbj = c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($sbj, c_ws_plugin__s2member_utils_strings::paypal_ipn_template_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'subscr-or-wa-w-level-8-sbj'), 'subject')) &&
								($msg = c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($msg, c_ws_plugin__s2member_utils_strings::paypal_ipn_template_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'subscr-or-wa-w-level-9-msg'), !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['html_emails_enabled']) ? 'html' : 'text')) &&
								($rec = trim($rec)) && ($sbj = trim($sbj)) && ($msg = trim($msg)))
							{
								foreach(c_ws_plugin__s2member_utils_strings::parse_emails($rec) as $recipient) // Go through a possible list of recipients.
								{
									//250617 HTML email support.
									if (empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['html_emails_enabled'])) {
										//261109.1726 Scope temporary WordPress sender filters to this plaintext notification; other emails must not inherit them.
										c_ws_plugin__s2member_email_configs::email_config();
										wp_mail($recipient, apply_filters('ws_plugin__s2member_signup_email_sbj', $sbj, get_defined_vars()), apply_filters('ws_plugin__s2member_signup_email_msg', $msg, get_defined_vars()), 'From: "'.preg_replace('/"/', "'", $GLOBALS['WS_PLUGIN__']['s2member']['o']['reg_email_from_name']).'" <'.$GLOBALS['WS_PLUGIN__']['s2member']['o']['reg_email_from_email'].'>'."\r\n".'Content-Type: text/plain; charset=UTF-8');
										c_ws_plugin__s2member_email_configs::email_config_release();
									} else {
										c_ws_plugin__s2member_utilities::mail($recipient, apply_filters('ws_plugin__s2member_signup_email_sbj', $sbj, get_defined_vars()), apply_filters('ws_plugin__s2member_signup_email_msg', $msg, get_defined_vars()));
									}
								}
								$paypal['s2member_log'][] = 'Signup Confirmation Email sent to: '.$rec.'.';
							}

							//261109.1531 Webhook/API URL notifications: send each configured URL independently; an empty rendered URL must not skip later entries.
							if($processing && $GLOBALS['WS_PLUGIN__']['s2member']['o']['signup_notification_urls'])
							{
								foreach(preg_split('/['."\r\n\t".']+/', $GLOBALS['WS_PLUGIN__']['s2member']['o']['signup_notification_urls']) as $url)
								{
									if(($url = c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($url, c_ws_plugin__s2member_utils_strings::paypal_ipn_template_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'subscr-or-wa-w-level-10-url'), 'url')) && ($url = trim($url)))
									{
										c_ws_plugin__s2member_utils_urls::remote($url);
									}
								}

								$paypal['s2member_log'][] = 'Signup Notification URLs have been processed.';
							}

							//261109.1531 API notification email: assemble a plaintext event summary for configured recipients.
							if($processing && $GLOBALS['WS_PLUGIN__']['s2member']['o']['signup_notification_recipients'])
							{
								$msg = $sbj = '(s2Member / API Notification Email) - Signup';
								$msg .= "\n\n"; // Spacing in the message body.

								$msg .= 'subscr_id: %%subscr_id%%'."\n";
								$msg .= 'subscr_baid: %%subscr_baid%%'."\n";
								$msg .= 'subscr_cid: %%subscr_cid%%'."\n";
								$msg .= 'currency: %%currency%%'."\n";
								$msg .= 'currency_symbol: %%currency_symbol%%'."\n";
								$msg .= 'initial: %%initial%%'."\n";
								$msg .= 'regular: %%regular%%'."\n";
								$msg .= 'recurring: %%recurring%%'."\n";
								$msg .= 'initial_term: %%initial_term%%'."\n";
								$msg .= 'regular_term: %%regular_term%%'."\n";
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
								if(($msg = c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($msg, c_ws_plugin__s2member_utils_strings::paypal_ipn_template_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'subscr-or-wa-w-level-11-msg'), 'text')) && $sbj && ($msg = trim($msg)))
								{
									foreach(c_ws_plugin__s2member_utils_strings::parse_emails($GLOBALS['WS_PLUGIN__']['s2member']['o']['signup_notification_recipients']) as $recipient)
									{
										wp_mail($recipient, apply_filters('ws_plugin__s2member_signup_notification_email_sbj', $sbj, get_defined_vars()), apply_filters('ws_plugin__s2member_signup_notification_email_msg', $msg, get_defined_vars()), 'Content-Type: text/plain; charset=UTF-8');
									}
								}

								$paypal['s2member_log'][] = 'Signup Notification Emails have been processed.';
							}

							//261109.1531 On-site tracking: queue the rendered code for the next visitor request instead of running it during this IPN.
							if($processing && ($code = $GLOBALS['WS_PLUGIN__']['s2member']['o']['signup_tracking_codes']))
							{
								if(($code = trim(c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($code, c_ws_plugin__s2member_utils_strings::paypal_ipn_tracking_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'subscr-or-wa-w-level-12-code'), 'tracking'))))
								{
									$paypal['s2member_log'][] = 'Storing Signup Tracking Codes into a Transient Queue. These will be processed on-site.';
									$code = str_replace('<?', '-', $code);
									set_transient('s2m_'.md5('s2member_transient_signup_tracking_codes_'.$paypal['subscr_id']), $code, 43200);
								}
							}

							foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
							do_action('ws_plugin__s2member_during_paypal_notify_during_subscr_signup_wo_update_vars', get_defined_vars());
							unset($__refs, $__v);
						}
						else $paypal['s2member_log'][] = 'Unable to generate Registration URL for Membership Access. Possible data corruption within the IPN response.';

						foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
						do_action('ws_plugin__s2member_during_paypal_notify_after_subscr_signup_wo_update_vars', get_defined_vars());
						unset($__refs, $__v);
					}
					//260827.0637 Proxy return URLs are optional; avoid PHP warnings when a valid proxy request does not include one.
					if($processing && !empty($_REQUEST['s2member_paypal_proxy']) && !empty($_REQUEST['s2member_paypal_proxy_return_url']) && ($url = $_REQUEST['s2member_paypal_proxy_return_url'])) // A Proxy is requesting a Return URL?
					{
						if((!empty($user_id) && !empty($user) && is_object($user) && $user->ID) 
							|| (($user_id = c_ws_plugin__s2member_utils_users::get_user_id_with($paypal['subscr_id'], $paypal['option_selection1'])) && is_object($user = new WP_User ($user_id)) && $user->ID)
							|| (($user_id = c_ws_plugin__s2member_utils_users::get_user_id_with($paypal['subscr_cid'], $paypal['option_selection1'])) && is_object($user = new WP_User ($user_id)) && $user->ID)
						)
						{
							$fields      = get_user_option('s2member_custom_fields', $user_id); // These will be needed in the routines below.
							$user_reg_ip = get_user_option('s2member_registration_ip', $user_id); // Original IP during Registration.
							//261109.1512 Proxy return templates can contain placeholders this handler cannot resolve; url-preserve keeps them intact for the proxy consumer.
							if(($url = c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($url, c_ws_plugin__s2member_utils_strings::paypal_ipn_template_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'subscr-or-wa-w-level-13-url'), 'url-preserve')) && ($url = trim($url)))
							{
								$paypal['s2member_paypal_proxy_return_url'] = $url;
							}
						}
						$paypal['s2member_log'][] = 'Subscr. Return ( `modification='.(int)$modifying.'` ), a Proxy Return URL is ready.';
					}
					if($processing // Process a payment now? Special cases for web_accept and/or Proxy requests with `subscr-signup-as-subscr-payment`.
					   && (preg_match('/^web_accept$/i', $paypal['txn_type']) || ($_REQUEST['s2member_paypal_proxy'] && preg_match('/subscr-signup-as-subscr-payment/', $_REQUEST['s2member_paypal_proxy_use']) && $paypal['txn_id'] && $paypal['mc_gross'] > 0))
					   && ((!empty($user_id) && !empty($user) && is_object($user) && $user->ID) || (($user_id = c_ws_plugin__s2member_utils_users::get_user_id_with($paypal['subscr_id'], $paypal['option_selection1'])) && is_object($user = new WP_User ($user_id)) && $user->ID))
					)
					{
						$paypal['s2member_log'][] = 'User exists. Handling `payment` for Subscription via ( `'.((preg_match('/^web_accept$/i', $paypal['txn_type'])) ? 'web_accept' : 'subscr-signup-as-subscr-payment').'` ).';

						$pr_times                           = get_user_option('s2member_paid_registration_times', $user_id);
						$pr_times['level']                  = (!$pr_times['level']) ? time() : $pr_times['level']; // Preserve existing.
						$pr_times['level'.$paypal['level']] = (!$pr_times['level'.$paypal['level']]) ? time() : $pr_times['level'.$paypal['level']];
						update_user_option($user_id, 's2member_paid_registration_times', $pr_times);

						if(!get_user_option('s2member_first_payment_txn_id', $user_id) /* 1st payment? */)
							update_user_option($user_id, 's2member_first_payment_txn_id', $paypal['txn_id']);

						update_user_option($user_id, 's2member_last_payment_time', time()); // Update the last payment time.

						$fields      = get_user_option('s2member_custom_fields', $user_id); // These will be needed in the routines below.
						$user_reg_ip = get_user_option('s2member_registration_ip', $user_id); // Original IP during Registration.

						if(empty($processed_coupons) && !empty($coupon['full_coupon_code']) && c_ws_plugin__s2member_utils_conds::pro_is_installed())
							{
								//250214 Improved coupon use logging.
								$processed_coupons = c_ws_plugin__s2member_pro_coupons::log_user_coupons_use($user_id, $coupon); // Flag for routines below.
							}

						//261109.1531 Webhook/API URL notifications: send each configured URL independently; an empty rendered URL must not skip later entries.
						if($GLOBALS['WS_PLUGIN__']['s2member']['o']['payment_notification_urls'])
						{
							foreach(preg_split('/['."\r\n\t".']+/', $GLOBALS['WS_PLUGIN__']['s2member']['o']['payment_notification_urls']) as $url)
							{
								if(($url = c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($url, c_ws_plugin__s2member_utils_strings::paypal_ipn_template_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'subscr-or-wa-w-level-14-url'), 'url')) && ($url = trim($url)))
								{
									c_ws_plugin__s2member_utils_urls::remote($url);
								}
							}
							$paypal['s2member_log'][] = 'Payment Notification URLs have been processed.';
						}

						//261109.1531 API notification email: assemble a plaintext event summary for configured recipients.
						if($GLOBALS['WS_PLUGIN__']['s2member']['o']['payment_notification_recipients'])
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
							$original_msg = $msg; //261109.1514 Preserve the raw message for get_defined_vars() consumers after rendering mutates $msg.

							//261109.1531 Only render after appending every event/custom-field row; replacement values are not rescanned.
							if(($msg = c_ws_plugin__s2member_utils_strings::fill_paypal_template_once($msg, c_ws_plugin__s2member_utils_strings::paypal_ipn_template_values($paypal, (isset($coupon) ? $coupon : array()), get_defined_vars(), 'subscr-or-wa-w-level-15-msg'), 'text')) && $sbj && ($msg = trim($msg)))
							{
								foreach(c_ws_plugin__s2member_utils_strings::parse_emails($GLOBALS['WS_PLUGIN__']['s2member']['o']['payment_notification_recipients']) as $recipient)
								{
									wp_mail($recipient, apply_filters('ws_plugin__s2member_payment_notification_email_sbj', $sbj, get_defined_vars()), apply_filters('ws_plugin__s2member_payment_notification_email_msg', $msg, get_defined_vars()), 'Content-Type: text/plain; charset=UTF-8');
								}
							}
							$paypal['s2member_log'][] = 'Payment Notification Emails have been processed.';
						}

						foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
						do_action('ws_plugin__s2member_during_paypal_notify_during_subscr_or_wa_payment', get_defined_vars());
						unset($__refs, $__v);
					}
					else if($processing // Process a payment now? Special cases for web_accept and/or Proxy requests with `subscr-signup-as-subscr-payment`.
					        && (preg_match('/^web_accept$/i', $paypal['txn_type']) || ($_REQUEST['s2member_paypal_proxy'] && preg_match('/subscr-signup-as-subscr-payment/', $_REQUEST['s2member_paypal_proxy_use']) && $paypal['txn_id'] && $paypal['mc_gross'] > 0))
					)
					{
						$paypal['s2member_log'][] = 'Storing `payment` for Subscription via ( `'.((preg_match('/^web_accept$/i', $paypal['txn_type'])) ? 'web_accept' : 'subscr-signup-as-subscr-payment').'` ).';

						$ipn = array('txn_type' => 'subscr_payment'); // Create a simulated IPN response for txn_type=subscr_payment.

						foreach($paypal as $var => $val)
							if(in_array($var, array('subscr_gateway', 'subscr_id', 'subscr_baid', 'subscr_cid', 'txn_id', 'custom', 'invoice', 'mc_gross', 'mc_currency', 'tax', 'payer_email', 'first_name', 'last_name', 'item_name', 'item_number', 'option_name1', 'option_selection1', 'option_name2', 'option_selection2')))
								$ipn[$var] = $val;

						$paypal['s2member_log'][] = 'Creating an IPN response for `subscr_payment`. This will go into a Transient Queue; and be processed during registration.';

						set_transient('s2m_'.md5('s2member_transient_ipn_subscr_payment_'.$paypal['subscr_id']), $ipn, 43200);
					}
					if($processing // Store signup vars now? If the User already exists in the database, we can go ahead and store these right now.
					   && ((!empty($user_id) && !empty($user) && is_object($user) && $user->ID) || (($user_id = c_ws_plugin__s2member_utils_users::get_user_id_with($paypal['subscr_id'], $paypal['option_selection1'])) && is_object($user = new WP_User ($user_id)) && $user->ID))
					)
					{
						$paypal['s2member_log'][] = 'Storing IPN signup vars now. These are associated with a User\'s account record; for future reference.';

						update_user_option($user_id, 's2member_ipn_signup_vars', $ipn_signup_vars);
					}
					else if($processing) // Otherwise, we can store these into a Transient Queue for registration processing.
					{
						$paypal['s2member_log'][] = 'Storing IPN signup vars into a Transient Queue. These will be processed on registration.';

						set_transient('s2m_'.md5('s2member_transient_ipn_signup_vars_'.$paypal['subscr_id']), $ipn_signup_vars, 43200);
					}
				}
				else // Else, this is a duplicate IPN. Must stop here.
				{
					$paypal['s2member_log'][] = 'Not processing. Duplicate IPN.';
					$paypal['s2member_log'][] = 's2Member `txn_type` identified as ( `web_accept|subscr_signup` ).';
					$paypal['s2member_log'][] = 'Duplicate IPN. Already processed. This IPN will be ignored.';
				}
				foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
				do_action('ws_plugin__s2member_during_paypal_notify_after_subscr_signup', get_defined_vars());
				unset($__refs, $__v);

				return apply_filters('c_ws_plugin__s2member_paypal_notify_in_subscr_or_wa_w_level', $paypal, get_defined_vars());
			}
			return apply_filters('c_ws_plugin__s2member_paypal_notify_in_subscr_or_wa_w_level', FALSE, get_defined_vars());
		}
	}
}
