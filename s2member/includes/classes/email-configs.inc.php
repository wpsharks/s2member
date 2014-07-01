<?php
/**
* Email configurations for s2Member.
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
* @package s2Member\Email_Configs
* @since 3.5
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit ("Do not access this file directly.");

if (!class_exists ("c_ws_plugin__s2member_email_configs"))
	{
		/**
		* Email configurations for s2Member.
		*
		* @package s2Member\Email_Configs
		* @since 3.5
		*/
		class c_ws_plugin__s2member_email_configs
			{
				/**
				* Modifies email From: `"Name" <address>`.
				*
				* These Filters are only needed during registration.
				*
				* @package s2Member\Email_Configs
				* @since 3.5
				*
				* @return null
				*/
				public static function email_config ()
					{
						do_action("ws_plugin__s2member_before_email_config", get_defined_vars ());

						c_ws_plugin__s2member_email_configs::email_config_release ();

						add_filter ("wp_mail_from", "c_ws_plugin__s2member_email_configs::_email_config_email");
						add_filter ("wp_mail_from_name", "c_ws_plugin__s2member_email_configs::_email_config_name");

						do_action("ws_plugin__s2member_after_email_config", get_defined_vars ());

						return /* Return for uniformity. */;
					}
				/**
				* A sort of callback function that applies the email Filter.
				*
				* @package s2Member\Email_Configs
				* @since 3.5
				*
				* @attaches-to ``add_filter("wp_mail_from");``
				*
				* @param string $email Expects the email address to be passed in by the Filter.
				* @return string s2Member-configured email address.
				*/
				public static function _email_config_email ($email = FALSE)
					{
						do_action("_ws_plugin__s2member_before_email_config_email", get_defined_vars ());

						return apply_filters("_ws_plugin__s2member_email_config_email", $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["reg_email_from_email"], get_defined_vars ());
					}
				/**
				* A sort of callback function that applies the name Filter.
				*
				* @package s2Member\Email_Configs
				* @since 3.5
				*
				* @attaches-to ``add_filter("wp_mail_from_name");``
				*
				* @param string $name Expects the name to be passed in by the Filter.
				* @return string s2Member-configured name.
				*/
				public static function _email_config_name ($name = FALSE)
					{
						do_action("_ws_plugin__s2member_before_email_config_name", get_defined_vars ());

						return apply_filters("_ws_plugin__s2member_email_config_name", $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["reg_email_from_name"], get_defined_vars ());
					}
				/**
				* Checks the status of Filters being applied to the email From: "Name" <address>.
				*
				* @package s2Member\Email_Configs
				* @since 3.5
				*
				* @param bool $any Optional. Defaults to false. If true, return true if ANY Filters are being applied, not just those applied by s2Member.
				* @return bool True if Filters are being applied, else false.
				*/
				public static function email_config_status ($any = FALSE)
					{
						do_action("ws_plugin__s2member_before_email_config_status", get_defined_vars ());

						if (has_filter ("wp_mail_from", "c_ws_plugin__s2member_email_configs::_email_config_email") || has_filter ("wp_mail_from_name", "c_ws_plugin__s2member_email_configs::_email_config_name"))
							return apply_filters("ws_plugin__s2member_email_config_status", true, get_defined_vars ());

						else if ($any && (has_filter ("wp_mail_from") || has_filter ("wp_mail_from_name")))
							return apply_filters("ws_plugin__s2member_email_config_status", true, get_defined_vars ());

						return apply_filters("ws_plugin__s2member_email_config_status", false, get_defined_vars ());
					}
				/**
				* Releases Filters that modify the email From: "Name" <address>.
				*
				* @package s2Member\Email_Configs
				* @since 3.5
				*
				* @param bool $all Optional. Defaults to false. If true, remove ALL Filters, not just those applied by s2Member.
				* @return null
				*/
				public static function email_config_release ($all = FALSE)
					{
						do_action("ws_plugin__s2member_before_email_config_release", get_defined_vars ());

						remove_filter ("wp_mail_from", "c_ws_plugin__s2member_email_configs::_email_config_email");
						remove_filter ("wp_mail_from_name", "c_ws_plugin__s2member_email_configs::_email_config_name");

						if /* If ``$all`` is true, remove ALL attached WordPress Filters. */ ($all)
							remove_all_filters ("wp_mail_from") . remove_all_filters ("wp_mail_from_name");

						do_action("ws_plugin__s2member_after_email_config_release", get_defined_vars ());

						return /* Return for uniformity. */;
					}
				/**
				* Converts primitive Role names in emails sent by WordPress.
				*
				* Only necessary with this particular email: `wpmu_signup_user_notification_email`.
				*
				* @package s2Member\Email_Configs
				* @since 3.5
				*
				* @attaches-to ``add_filter("wpmu_signup_user_notification_email");``
				*
				* @param string $message Expects the message string to be passed in by the Filter.
				* @return string Message after having been Filtered by s2Member.
				*/
				public static function ms_nice_email_roles ($message = FALSE)
					{
						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_before_ms_nice_email_roles", get_defined_vars ());
						unset($__refs, $__v);

						$message = preg_replace ("/ as a (subscriber|s2member_level[0-9]+)/i", " " . _x ("as a Member", "s2member-front", "s2member"), $message);

						return apply_filters("ws_plugin__s2member_ms_nice_email_roles", $message, get_defined_vars ());
					}
				/**
				* Filters email addresses passed to ``wp_mail()``.
				*
				* @package s2Member\Email_Configs
				* @since 3.5
				*
				* @attaches-to ``add_filter("wp_mail");``
				* @uses {@link s2Member\Utilities\c_ws_plugin__s2member_utils_strings::parse_emails()}
				*
				* @param array $array Expects an array passed through by the Filter.
				* @return array Returns the array passed through by the Filter.
				*/
				public static function email_filter ($array = FALSE)
					{
						if (isset ($array["to"]) && !empty($array["to"])) // Filter list of recipients?
							// Reduces `"Name" <email>`, to just an email address *(for best cross-platform compatibility across various MTAs)*.
							// Also works around bug in PHP versions prior to fix in 5.2.11. See bug report: <https://bugs.php.net/bug.php?id=28038>.
							// Also supplements WordPress. WordPress currently does NOT support semicolon `;` delimitation, s2Member does.
							$array["to"] = implode (",", c_ws_plugin__s2member_utils_strings::parse_emails ($array["to"]));

						return apply_filters("ws_plugin__s2member_after_email_filter", $array, get_defined_vars ());
					}
				/**
				* Resets a User/Member password and resends the New User Notification email message (to the User/Member only).
				*
				* @package s2Member\Email_Configs
				* @since 110707
				*
				* @param string|int $user_id A numeric WordPress User ID.
				* @param string $user_pass Optional. A plain text version of the User's password.
				* 	If omitted, a new password will be generated automatically.
				* @param array $notify An array of directives. Must be non-empty, with at least one of these values `user,admin`.
				*  This defaults to a value of `array('user')`. We notify the User/Member only (and NOT the administrator).
				* @param string $user_email Optional. This defaults to the user's currently configured email address.
				* @return bool True if all required parameters are supplied, else false.
				*/
				public static function reset_pass_resend_new_user_notification ($user_id = FALSE, $user_pass = FALSE, $notify = array("user"), $user_email = FALSE)
					{
						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_before_reset_pass_resend_new_user_notification", get_defined_vars ());
						unset($__refs, $__v);

						if ($user_id && ($user = new WP_User ($user_id)) && !empty($user->ID) && ($user_id = $user->ID) && is_array($notify) && !empty($notify))
							{
								$user_pass = (is_string($user_pass) && $user_pass) ? $user_pass : wp_generate_password();

								wp_set_password($user_pass, $user_id);

								$return = c_ws_plugin__s2member_email_configs::new_user_notification($user_id, $user_pass, $notify, $user_email);
							}
						return apply_filters("ws_plugin__s2member_reset_pass_resend_new_user_notification", ((!empty($return)) ? true : false), get_defined_vars ());
					}
				/**
				* Handles new User/Member notifications.
				*
				* @package s2Member\Email_Configs
				* @since 110707
				*
				* @param string|int $user_id A numeric WordPress User ID.
				* @param string $user_pass Optional. A plain text version of the User's password.
				* 	If omitted, only the administrative notification will be sent.
				* @param array $notify An array of directives. Must be non-empty, with at least one of these values `user,admin`.
				* @param string $user_email Optional. This defaults to the user's currently configured email address.
				* @return bool True if all required parameters are supplied, else false.
				*/
				public static function new_user_notification ($user_id = FALSE, $user_pass = FALSE, $notify = array("user", "admin"), $user_email = FALSE)
					{
						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_before_new_user_notification", get_defined_vars ());
						unset($__refs, $__v);

						if ($user_id && ($user = new WP_User ($user_id)) && !empty($user->ID) && ($user_id = $user->ID) && is_array($notify) && !empty($notify))
							{
								$email_configs_were_on = c_ws_plugin__s2member_email_configs::email_config_status ();
								c_ws_plugin__s2member_email_configs::email_config_release ();

								if /* Send User a notification? */ (in_array("user", $notify) && $user_pass)
									{
										$fields = get_user_option ("s2member_custom_fields", $user_id);
										$cv = preg_split ("/\|/", get_user_option ("s2member_custom", $user_id));

										$role = c_ws_plugin__s2member_user_access::user_access_role($user);
										$label = c_ws_plugin__s2member_user_access::user_access_label($user);
										$level = c_ws_plugin__s2member_user_access::user_access_level($user);
										$ccaps = implode(",", c_ws_plugin__s2member_user_access::user_access_ccaps($user));

										$user->user_email = ($user_email) ? $user_email : $user->user_email;
										$user_full_name = trim ($user->first_name . " " . $user->last_name);
										$user_ip = $_SERVER["REMOTE_ADDR"];

										if (($sbj = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["new_user_email_subject"]))
											if (($sbj = preg_replace ("/%%cv([0-9]+)%%/ei", 'trim(@$cv[$1])', $sbj)))
												if (($sbj = preg_replace ("/%%wp_login_url%%/i", c_ws_plugin__s2member_utils_strings::esc_refs (wp_login_url ()), $sbj)))
													if (($sbj = preg_replace ("/%%role%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($role), $sbj)))
														if (($sbj = preg_replace ("/%%label%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($label), $sbj)))
															if (($sbj = preg_replace ("/%%level%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($level), $sbj)))
																if (($sbj = preg_replace ("/%%ccaps%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($ccaps), $sbj)))
																	if (($sbj = preg_replace ("/%%user_first_name%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user->first_name), $sbj)))
																		if (($sbj = preg_replace ("/%%user_last_name%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user->last_name), $sbj)))
																			if (($sbj = preg_replace ("/%%user_full_name%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user_full_name), $sbj)))
																				if (($sbj = preg_replace ("/%%user_email%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user->user_email), $sbj)))
																					if (($sbj = preg_replace ("/%%user_login%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user->user_login), $sbj)))
																						if (($sbj = preg_replace ("/%%user_pass%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user_pass), $sbj)))
																							if (($sbj = preg_replace ("/%%user_ip%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user_ip), $sbj)))
																								if (($sbj = preg_replace ("/%%user_id%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user_id), $sbj)))
																									{
																										if (is_array($fields) && !empty($fields))
																											foreach /* Custom Registration/Profile Fields. */ ($fields as $var => $val)
																												if (!($sbj = preg_replace ("/%%" . preg_quote ($var, "/") . "%%/i", c_ws_plugin__s2member_utils_strings::esc_refs (maybe_serialize ($val)), $sbj)))
																													break;

																										if (($msg = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["new_user_email_message"]))
																											if (($msg = preg_replace ("/%%cv([0-9]+)%%/ei", 'trim(@$cv[$1])', $msg)))
																												if (($msg = preg_replace ("/%%wp_login_url%%/i", c_ws_plugin__s2member_utils_strings::esc_refs (wp_login_url ()), $msg)))
																													if (($msg = preg_replace ("/%%role%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($role), $msg)))
																														if (($msg = preg_replace ("/%%label%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($label), $msg)))
																															if (($msg = preg_replace ("/%%level%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($level), $msg)))
																																if (($msg = preg_replace ("/%%ccaps%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($ccaps), $msg)))
																																	if (($msg = preg_replace ("/%%user_first_name%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user->first_name), $msg)))
																																		if (($msg = preg_replace ("/%%user_last_name%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user->last_name), $msg)))
																																			if (($msg = preg_replace ("/%%user_full_name%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user_full_name), $msg)))
																																				if (($msg = preg_replace ("/%%user_email%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user->user_email), $msg)))
																																					if (($msg = preg_replace ("/%%user_login%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user->user_login), $msg)))
																																						if (($msg = preg_replace ("/%%user_pass%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user_pass), $msg)))
																																							if (($msg = preg_replace ("/%%user_ip%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user_ip), $msg)))
																																								if (($msg = preg_replace ("/%%user_id%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user_id), $msg)))
																																									{
																																										if (is_array($fields) && !empty($fields))
																																											foreach /* Custom Registration/Profile Fields. */ ($fields as $var => $val)
																																												if (!($msg = preg_replace ("/%%" . preg_quote ($var, "/") . "%%/i", c_ws_plugin__s2member_utils_strings::esc_refs (maybe_serialize ($val)), $msg)))
																																													break;

																																										if (($sbj = trim (preg_replace ("/%%(.+?)%%/i", "", $sbj))) && ($msg = trim (preg_replace ("/%%(.+?)%%/i", "", $msg)))) // Still have a ``$sbj`` and a ``$msg``?
																																											{
																																												if (!is_multisite () || !c_ws_plugin__s2member_utils_conds::is_multisite_farm () || is_main_site ())
																																													{
																																														$sbj = c_ws_plugin__s2member_utilities::evl($sbj, get_defined_vars());
																																														$msg = c_ws_plugin__s2member_utilities::evl($msg, get_defined_vars());
																																													}
																																												c_ws_plugin__s2member_email_configs::email_config () . wp_mail ($user->user_email, apply_filters("ws_plugin__s2member_welcome_email_sbj", $sbj, get_defined_vars()), apply_filters("ws_plugin__s2member_welcome_email_msg", $msg, get_defined_vars()), "From: \"" . preg_replace ('/"/', "'", $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["reg_email_from_name"]) . "\" <" . $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["reg_email_from_email"] . ">\r\nContent-Type: text/plain; charset=UTF-8") . c_ws_plugin__s2member_email_configs::email_config_release ();
																																											}
																																									}
																													}
									}

								if /* Send Admin(s) a notification? */ (in_array("admin", $notify))
									if ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["new_user_admin_email_recipients"])
										{
											$fields = get_user_option ("s2member_custom_fields", $user_id);
											$cv = preg_split ("/\|/", get_user_option ("s2member_custom", $user_id));

											$role = c_ws_plugin__s2member_user_access::user_access_role($user);
											$label = c_ws_plugin__s2member_user_access::user_access_label($user);
											$level = c_ws_plugin__s2member_user_access::user_access_level($user);
											$ccaps = implode(",", c_ws_plugin__s2member_user_access::user_access_ccaps($user));

											$user->user_email = ($user_email) ? $user_email : $user->user_email;
											$user_full_name = trim ($user->first_name . " " . $user->last_name);
											$user_ip = $_SERVER["REMOTE_ADDR"];

											if (($rec = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["new_user_admin_email_recipients"]))
												if (($rec = preg_replace ("/%%cv([0-9]+)%%/ei", 'trim(@$cv[$1])', $rec)))
													if (($rec = preg_replace ("/%%wp_login_url%%/i", c_ws_plugin__s2member_utils_strings::esc_refs (wp_login_url ()), $rec)))
														if (($rec = preg_replace ("/%%role%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($role), $rec)))
															if (($rec = preg_replace ("/%%label%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($label), $rec)))
																if (($rec = preg_replace ("/%%level%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($level), $rec)))
																	if (($rec = preg_replace ("/%%ccaps%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($ccaps), $rec)))
																		if (($rec = preg_replace ("/%%user_first_name%%/i", c_ws_plugin__s2member_utils_strings::esc_dq (c_ws_plugin__s2member_utils_strings::esc_refs ($user->first_name)), $rec)))
																			if (($rec = preg_replace ("/%%user_last_name%%/i", c_ws_plugin__s2member_utils_strings::esc_dq (c_ws_plugin__s2member_utils_strings::esc_refs ($user->last_name)), $rec)))
																				if (($rec = preg_replace ("/%%user_full_name%%/i", c_ws_plugin__s2member_utils_strings::esc_dq (c_ws_plugin__s2member_utils_strings::esc_refs ($user_full_name)), $rec)))
																					if (($rec = preg_replace ("/%%user_email%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user->user_email), $rec)))
																						if (($rec = preg_replace ("/%%user_login%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user->user_login), $rec)))
																							if (($rec = preg_replace ("/%%user_pass%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user_pass), $rec)))
																								if (($rec = preg_replace ("/%%user_ip%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user_ip), $rec)))
																									if (($rec = preg_replace ("/%%user_id%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user_id), $rec)))
																										{
																											if (is_array($fields) && !empty($fields))
																												foreach /* Custom Registration/Profile Fields. */ ($fields as $var => $val)
																													if (!($rec = preg_replace ("/%%" . preg_quote ($var, "/") . "%%/i", c_ws_plugin__s2member_utils_strings::esc_refs (maybe_serialize ($val)), $rec)))
																														break;

																											if (($sbj = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["new_user_admin_email_subject"]))
																												if (($sbj = preg_replace ("/%%cv([0-9]+)%%/ei", 'trim(@$cv[$1])', $sbj)))
																													if (($sbj = preg_replace ("/%%wp_login_url%%/i", c_ws_plugin__s2member_utils_strings::esc_refs (wp_login_url ()), $sbj)))
																														if (($sbj = preg_replace ("/%%role%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($role), $sbj)))
																															if (($sbj = preg_replace ("/%%label%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($label), $sbj)))
																																if (($sbj = preg_replace ("/%%level%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($level), $sbj)))
																																	if (($sbj = preg_replace ("/%%ccaps%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($ccaps), $sbj)))
																																		if (($sbj = preg_replace ("/%%user_first_name%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user->first_name), $sbj)))
																																			if (($sbj = preg_replace ("/%%user_last_name%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user->last_name), $sbj)))
																																				if (($sbj = preg_replace ("/%%user_full_name%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user_full_name), $sbj)))
																																					if (($sbj = preg_replace ("/%%user_email%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user->user_email), $sbj)))
																																						if (($sbj = preg_replace ("/%%user_login%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user->user_login), $sbj)))
																																							if (($sbj = preg_replace ("/%%user_pass%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user_pass), $sbj)))
																																								if (($sbj = preg_replace ("/%%user_ip%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user_ip), $sbj)))
																																									if (($sbj = preg_replace ("/%%user_id%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user_id), $sbj)))
																																										{
																																											if (is_array($fields) && !empty($fields))
																																												foreach /* Custom Registration/Profile Fields. */ ($fields as $var => $val)
																																													if (!($sbj = preg_replace ("/%%" . preg_quote ($var, "/") . "%%/i", c_ws_plugin__s2member_utils_strings::esc_refs (maybe_serialize ($val)), $sbj)))
																																														break;

																																											if (($msg = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["new_user_admin_email_message"]))
																																												if (($msg = preg_replace ("/%%cv([0-9]+)%%/ei", 'trim(@$cv[$1])', $msg)))
																																													if (($msg = preg_replace ("/%%wp_login_url%%/i", c_ws_plugin__s2member_utils_strings::esc_refs (wp_login_url ()), $msg)))
																																														if (($msg = preg_replace ("/%%role%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($role), $msg)))
																																															if (($msg = preg_replace ("/%%label%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($label), $msg)))
																																																if (($msg = preg_replace ("/%%level%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($level), $msg)))
																																																	if (($msg = preg_replace ("/%%ccaps%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($ccaps), $msg)))
																																																		if (($msg = preg_replace ("/%%user_first_name%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user->first_name), $msg)))
																																																			if (($msg = preg_replace ("/%%user_last_name%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user->last_name), $msg)))
																																																				if (($msg = preg_replace ("/%%user_full_name%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user_full_name), $msg)))
																																																					if (($msg = preg_replace ("/%%user_email%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user->user_email), $msg)))
																																																						if (($msg = preg_replace ("/%%user_login%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user->user_login), $msg)))
																																																							if (($msg = preg_replace ("/%%user_pass%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user_pass), $msg)))
																																																								if (($msg = preg_replace ("/%%user_ip%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user_ip), $msg)))
																																																									if (($msg = preg_replace ("/%%user_id%%/i", c_ws_plugin__s2member_utils_strings::esc_refs ($user_id), $msg)))
																																																										{
																																																											if (is_array($fields) && !empty($fields))
																																																												foreach /* Custom Registration/Profile Fields. */ ($fields as $var => $val)
																																																													if (!($msg = preg_replace ("/%%" . preg_quote ($var, "/") . "%%/i", c_ws_plugin__s2member_utils_strings::esc_refs (maybe_serialize ($val)), $msg)))
																																																														break;

																																																											if (($rec = trim (preg_replace ("/%%(.+?)%%/i", "", $rec))) && ($sbj = trim (preg_replace ("/%%(.+?)%%/i", "", $sbj))) && ($msg = trim (preg_replace ("/%%(.+?)%%/i", "", $msg))))
																																																												{
																																																													if (!is_multisite () || !c_ws_plugin__s2member_utils_conds::is_multisite_farm () || is_main_site ())
																																																														{
																																																															$rec = c_ws_plugin__s2member_utilities::evl($rec, get_defined_vars());
																																																															$sbj = c_ws_plugin__s2member_utilities::evl($sbj, get_defined_vars());
																																																															$msg = c_ws_plugin__s2member_utilities::evl($msg, get_defined_vars());
																																																														}
																																																													foreach /* A possible list of recipients. */ (c_ws_plugin__s2member_utils_strings::parse_emails ($rec) as $recipient)
																																																															wp_mail ($recipient, apply_filters("ws_plugin__s2member_admin_new_user_email_sbj", $sbj, get_defined_vars()), apply_filters("ws_plugin__s2member_admin_new_user_email_msg", $msg, get_defined_vars()), "Content-Type: text/plain; charset=UTF-8");
																																																												}
																																																										}
																																														}
																																		}
										}
								if /* Back on? */ ($email_configs_were_on)
									c_ws_plugin__s2member_email_configs::email_config ();

								return apply_filters("ws_plugin__s2member_new_user_notification", true, get_defined_vars ());
							}
						else return apply_filters("ws_plugin__s2member_new_user_notification", false, get_defined_vars ());
					}
			}
	}
?>