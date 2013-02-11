<?php
/**
* User deletion routines.
*
* Copyright: © 2009-2011
* {@link http://www.websharks-inc.com/ WebSharks, Inc.}
* (coded in the USA)
*
* Released under the terms of the GNU General Public License.
* You should have received a copy of the GNU General Public License,
* along with this software. In the main directory, see: /licensing/
* If not, see: {@link http://www.gnu.org/licenses/}.
*
* @package s2Member\User_Deletions
* @since 3.5
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit ("Do not access this file directly.");

if (!class_exists ("c_ws_plugin__s2member_user_deletions"))
	{
		/**
		* User deletion routines.
		*
		* @package s2Member\User_Deletions
		* @since 3.5
		*/
		class c_ws_plugin__s2member_user_deletions
			{
				/**
				* Handles Multisite User removal deletions.
				*
				* @package s2Member\User_Deletions
				* @since 3.5
				*
				* @attaches-to ``add_action("remove_user_from_blog");``
				*
				* @param int|str $user_id Numeric WordPress® User ID.
				* @param int|str $blog_id Numeric WordPress® Blog ID.
				* @param bool $s2says Optional. Defaults to false. If true, it's definitely OK to process this deletion?
				* 	The ``$s2says`` flag can be used when/if the routine is called directly for whatever reason.
				* @return null
				*/
				public static function handle_ms_user_deletions ($user_id = FALSE, $blog_id = FALSE, $s2says = FALSE)
					{
						static $processed = array (); // No duplicate processing.
						global $pagenow; // Need this to detect the current admin page.

						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action ("ws_plugin__s2member_before_handle_ms_user_deletions", get_defined_vars ());
						unset /* Unset defined __refs, __v. */ ($__refs, $__v);

						if (is_multisite () && empty ($processed[$user_id])) // Safeguard this routine against duplicate processing.
							{
								if (($s2says || (is_blog_admin () && $pagenow === "users.php")) && ($processed[$user_id] = true))
									// Do NOT react on this globally. There are many routines that remove Users for different/minor reasons.
									{
										foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
										do_action ("ws_plugin__s2member_during_handle_ms_user_deletions_before", get_defined_vars ());
										unset /* Unset defined __refs, __v. */ ($__refs, $__v);

										c_ws_plugin__s2member_user_deletions::handle_user_deletions ($user_id); // Hand this over.

										foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
										do_action ("ws_plugin__s2member_during_handle_ms_user_deletions_after", get_defined_vars ());
										unset /* Unset defined __refs, __v. */ ($__refs, $__v);
									}
							}

						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action ("ws_plugin__s2member_after_handle_ms_user_deletions", get_defined_vars ());
						unset /* Unset defined __refs, __v. */ ($__refs, $__v);

						return /* Return for uniformity. */;
					}
				/**
				* Handles User removals/deletions.
				*
				* @package s2Member\User_Deletions
				* @since 3.5
				*
				* @attaches-to ``add_action("delete_user");``
				* @attaches-to ``add_action("wpmu_delete_user");``
				*
				* @param int|str $user_id Numeric WordPress® User ID.
				* @return null
				*/
				public static function handle_user_deletions ($user_id = FALSE)
					{
						static $processed = array (); // No duplicate processing.

						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action ("ws_plugin__s2member_before_handle_user_deletions", get_defined_vars ());
						unset /* Unset defined __refs, __v. */ ($__refs, $__v);

						if (empty ($processed[$user_id]) && ($processed[$user_id] = true)) // Safeguard this routine against duplicate processing.
							{
								$eot_del_type = // Configure EOT/Del type (possibly through a global variable made available in other routines).
								(!empty ($GLOBALS["ws_plugin__s2member_eot_del_type"])) ? // Is the global available for use?
								$GLOBALS["ws_plugin__s2member_eot_del_type"] : "user-removal-deletion"; // Else use default.

								$custom = get_user_option ("s2member_custom", $user_id); // An EOT Notification is triggered, EVEN if empty.
								$subscr_id = get_user_option ("s2member_subscr_id", $user_id); // And also, EVEN if this is empty.
								$fields = get_user_option ("s2member_custom_fields", $user_id); // Used in API Notifications.
								$user_reg_ip = get_user_option ("s2member_registration_ip", $user_id); // In API Notifications.

								foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
								do_action ("ws_plugin__s2member_during_handle_user_before_deletions", get_defined_vars ());
								do_action ("ws_plugin__s2member_during_collective_eots", $user_id, get_defined_vars (), $eot_del_type, "removal-deletion");
								unset /* Unset defined __refs, __v. */ ($__refs, $__v);

								delete_user_option ($user_id, "s2member_custom"); // Remove User options (for this Blog).
								delete_user_option ($user_id, "s2member_subscr_id");
								delete_user_option ($user_id, "s2member_subscr_gateway");

								delete_user_option ($user_id, "s2member_custom_fields");
								delete_user_option ($user_id, "s2member_registration_ip");

								delete_user_option ($user_id, "s2member_ipn_signup_vars");
								delete_user_option ($user_id, "s2member_paid_registration_times");
								delete_user_option ($user_id, "s2member_sp_references");

								delete_user_option ($user_id, "s2member_last_status_scan");
								delete_user_option ($user_id, "s2member_first_payment_txn_id");
								delete_user_option ($user_id, "s2member_last_payment_time");
								delete_user_option ($user_id, "s2member_auto_eot_time");

								delete_user_option ($user_id, "s2member_file_download_access_arc");
								delete_user_option ($user_id, "s2member_file_download_access_log");

								delete_user_option ($user_id, "s2member_login_counter");
								delete_user_option ($user_id, "s2member_notes");

								if (is_object ($user = new WP_User ($user_id)) && $user->ID && $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["eot_del_notification_urls"] && is_array ($cv = preg_split ("/\|/", $custom)))
									{
										foreach (preg_split ("/[\r\n\t]+/", $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["eot_del_notification_urls"]) as $url) // Handle EOT Notifications on user deletion.

											if (($url = preg_replace ("/%%cv([0-9]+)%%/ei", 'urlencode(trim($cv[$1]))', $url)) && ($url = preg_replace ("/%%eot_del_type%%/i", c_ws_plugin__s2member_utils_strings::esc_ds (urlencode ($eot_del_type)), $url)) && ($url = preg_replace ("/%%subscr_id%%/i", c_ws_plugin__s2member_utils_strings::esc_ds (urlencode ($subscr_id)), $url)))
												if (($url = preg_replace ("/%%user_first_name%%/i", c_ws_plugin__s2member_utils_strings::esc_ds (urlencode ($user->first_name)), $url)) && ($url = preg_replace ("/%%user_last_name%%/i", c_ws_plugin__s2member_utils_strings::esc_ds (urlencode ($user->last_name)), $url)))
													if (($url = preg_replace ("/%%user_full_name%%/i", c_ws_plugin__s2member_utils_strings::esc_ds (urlencode (trim ($user->first_name . " " . $user->last_name))), $url)))
														if (($url = preg_replace ("/%%user_email%%/i", c_ws_plugin__s2member_utils_strings::esc_ds (urlencode ($user->user_email)), $url)))
															if (($url = preg_replace ("/%%user_login%%/i", c_ws_plugin__s2member_utils_strings::esc_ds (urlencode ($user->user_login)), $url)))
																if (($url = preg_replace ("/%%user_ip%%/i", c_ws_plugin__s2member_utils_strings::esc_ds (urlencode ($user_reg_ip)), $url)))
																	if (($url = preg_replace ("/%%user_id%%/i", c_ws_plugin__s2member_utils_strings::esc_ds (urlencode ($user_id)), $url)))
																		{
																			if (is_array ($fields) && !empty ($fields))
																				foreach /* Custom Registration/Profile Fields. */ ($fields as $var => $val)
																					if (!($url = preg_replace ("/%%" . preg_quote ($var, "/") . "%%/i", c_ws_plugin__s2member_utils_strings::esc_ds (urlencode (maybe_serialize ($val))), $url)))
																						break;

																			if (($url = trim (preg_replace ("/%%(.+?)%%/i", "", $url))))
																				c_ws_plugin__s2member_utils_urls::remote ($url);
																		}
									}

								if (is_object ($user = new WP_User ($user_id)) && $user->ID && $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["eot_del_notification_recipients"] && is_array ($cv = preg_split ("/\|/", $custom)))
									{
										$email_configs_were_on = c_ws_plugin__s2member_email_configs::email_config_status ();
										c_ws_plugin__s2member_email_configs::email_config_release ();

										$msg = $sbj = "(s2Member / API Notification Email) - EOT/Deletion";
										$msg .= "\n\n"; // Spacing in the message body.

										$msg .= "eot_del_type: %%eot_del_type%%\n";
										$msg .= "subscr_id: %%subscr_id%%\n";
										$msg .= "user_first_name: %%user_first_name%%\n";
										$msg .= "user_last_name: %%user_last_name%%\n";
										$msg .= "user_full_name: %%user_full_name%%\n";
										$msg .= "user_email: %%user_email%%\n";
										$msg .= "user_login: %%user_login%%\n";
										$msg .= "user_ip: %%user_ip%%\n";
										$msg .= "user_id: %%user_id%%\n";

										if (is_array ($fields) && !empty ($fields))
											foreach ($fields as $var => $val)
												$msg .= $var . ": %%" . $var . "%%\n";

										$msg .= "cv0: %%cv0%%\n";
										$msg .= "cv1: %%cv1%%\n";
										$msg .= "cv2: %%cv2%%\n";
										$msg .= "cv3: %%cv3%%\n";
										$msg .= "cv4: %%cv4%%\n";
										$msg .= "cv5: %%cv5%%\n";
										$msg .= "cv6: %%cv6%%\n";
										$msg .= "cv7: %%cv7%%\n";
										$msg .= "cv8: %%cv8%%\n";
										$msg .= "cv9: %%cv9%%";

										if (($msg = preg_replace ("/%%cv([0-9]+)%%/ei", 'trim($cv[$1])', $msg)) && ($msg = preg_replace ("/%%eot_del_type%%/i", c_ws_plugin__s2member_utils_strings::esc_ds ($eot_del_type), $msg)) && ($msg = preg_replace ("/%%subscr_id%%/i", c_ws_plugin__s2member_utils_strings::esc_ds ($subscr_id), $msg)))
											if (($msg = preg_replace ("/%%user_first_name%%/i", c_ws_plugin__s2member_utils_strings::esc_ds ($user->first_name), $msg)) && ($msg = preg_replace ("/%%user_last_name%%/i", c_ws_plugin__s2member_utils_strings::esc_ds ($user->last_name), $msg)))
												if (($msg = preg_replace ("/%%user_full_name%%/i", c_ws_plugin__s2member_utils_strings::esc_ds (trim ($user->first_name . " " . $user->last_name)), $msg)))
													if (($msg = preg_replace ("/%%user_email%%/i", c_ws_plugin__s2member_utils_strings::esc_ds ($user->user_email), $msg)))
														if (($msg = preg_replace ("/%%user_login%%/i", c_ws_plugin__s2member_utils_strings::esc_ds ($user->user_login), $msg)))
															if (($msg = preg_replace ("/%%user_ip%%/i", c_ws_plugin__s2member_utils_strings::esc_ds ($user_reg_ip), $msg)))
																if (($msg = preg_replace ("/%%user_id%%/i", c_ws_plugin__s2member_utils_strings::esc_ds ($user_id), $msg)))
																	{
																		if (is_array ($fields) && !empty ($fields))
																			foreach /* Custom Registration/Profile Fields. */ ($fields as $var => $val)
																				if (!($msg = preg_replace ("/%%" . preg_quote ($var, "/") . "%%/i", c_ws_plugin__s2member_utils_strings::esc_ds (maybe_serialize ($val)), $msg)))
																					break;

																		if ($sbj && ($msg = trim (preg_replace ("/%%(.+?)%%/i", "", $msg)))) // Still have a ``$sbj`` and a ``$msg``?

																			foreach (c_ws_plugin__s2member_utils_strings::parse_emails ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["eot_del_notification_recipients"]) as $recipient)
																				wp_mail ($recipient, apply_filters ("ws_plugin__s2member_eot_del_notification_email_sbj", $sbj, get_defined_vars ()), apply_filters ("ws_plugin__s2member_eot_del_notification_email_msg", $msg, get_defined_vars ()), "Content-Type: text/plain; charset=UTF-8");
																	}

										if ($email_configs_were_on) // Back on?
											c_ws_plugin__s2member_email_configs::email_config ();
									}

								foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
								do_action ("ws_plugin__s2member_during_handle_user_deletions", get_defined_vars ());
								unset /* Unset defined __refs, __v. */ ($__refs, $__v);
							}

						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action ("ws_plugin__s2member_after_handle_user_deletions", get_defined_vars ());
						unset /* Unset defined __refs, __v. */ ($__refs, $__v);

						return /* Return for uniformity. */;
					}
			}
	}
?>