<?php
/**
* s2Member's Post protection routines *(for specific Posts)*.
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
* @package s2Member\Posts
* @since 3.5
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit ("Do not access this file directly.");

if (!class_exists ("c_ws_plugin__s2member_posts_sp"))
	{
		/**
		* s2Member's Post protection routines *(for specific Posts)*.
		*
		* @package s2Member\Posts
		* @since 3.5
		*/
		class c_ws_plugin__s2member_posts_sp
			{
				/**
				* Handles Post Level Access *(for specific Posts)*.
				*
				* @package s2Member\Posts
				* @since 3.5
				*
				* @param int|str $post_id Numeric Post ID.
				* @param bool $check_user Test permissions against the current User? Defaults to true.
				* @return null|array Non-empty array (with details) if access is denied, else null if access is allowed.
				*/
				public static function check_specific_post_level_access ($post_id = FALSE, $check_user = TRUE)
					{
						do_action ("ws_plugin__s2member_before_check_specific_post_level_access", get_defined_vars ());

						$excluded = apply_filters ("ws_plugin__s2member_check_specific_post_level_access_excluded", false, get_defined_vars ());

						if (!$excluded && is_numeric ($post_id) && ($post_id = (int)$post_id) && $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["membership_options_page"])
							{
								$post_uri = c_ws_plugin__s2member_utils_urls::parse_uri (get_permalink ($post_id)); // Get a full valid URI for this Post now.

								if (!c_ws_plugin__s2member_systematics_sp::is_wp_systematic_use_specific_page ($post_id, $post_uri)) // Do NOT touch WordPress® Systematics.
									{
										$user = (is_user_logged_in () && is_object ($user = wp_get_current_user ()) && !empty ($user->ID)) ? $user : false; // Current User's object.

										if ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_redirection_override"] && ($login_redirection_uri = c_ws_plugin__s2member_login_redirects::login_redirection_uri ($user, "root-returns-false")) && preg_match ("/^" . preg_quote ($login_redirection_uri, "/") . "$/", $post_uri) && (!$check_user || !$user || !$user->has_cap ("access_s2member_level0")))
											return apply_filters ("ws_plugin__s2member_check_specific_post_level_access", array ("s2member_level_req" => 0), get_defined_vars ());

										else if (!c_ws_plugin__s2member_systematics_sp::is_systematic_use_specific_page ($post_id, $post_uri)) // Never restrict Systematics. However, there is 1 exception above.
											{
												for ($n = $GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["levels"]; $n >= 0; $n--) // Post Level restrictions (including Custom Post Types). Go through each Level.
													{
														if ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["level" . $n . "_posts"] === "all" && (!$check_user || !$user || !$user->has_cap ("access_s2member_level" . $n)))
															return apply_filters ("ws_plugin__s2member_check_specific_post_level_access", array ("s2member_level_req" => $n), get_defined_vars ());

														else if (strpos ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["level" . $n . "_posts"], "all-") !== false && ($post_type = get_post_type ($post_id)) && in_array ("all-" . $post_type . "s", preg_split ("/[\r\n\t\s;,]+/", $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["level" . $n . "_posts"])) && (!$check_user || !$user || !$user->has_cap ("access_s2member_level" . $n)))
															return apply_filters ("ws_plugin__s2member_check_specific_post_level_access", array ("s2member_level_req" => $n), get_defined_vars ());

														else if ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["level" . $n . "_posts"] && in_array ($post_id, preg_split ("/[\r\n\t\s;,]+/", $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["level" . $n . "_posts"])) && (!$check_user || !$user || !$user->has_cap ("access_s2member_level" . $n)))
															return apply_filters ("ws_plugin__s2member_check_specific_post_level_access", array ("s2member_level_req" => $n), get_defined_vars ());
													}

												for ($n = $GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["levels"]; $n >= 0; $n--) // Category Level Access against this Post. Go through each Level.
													{
														if ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["level" . $n . "_catgs"] === "all" && (!$check_user || !$user || !$user->has_cap ("access_s2member_level" . $n)))
															return apply_filters ("ws_plugin__s2member_check_specific_post_level_access", array ("s2member_level_req" => $n), get_defined_vars ());

														else if ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["level" . $n . "_catgs"] && (in_category (($catgs = preg_split ("/[\r\n\t\s;,]+/", $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["level" . $n . "_catgs"])), $post_id) || c_ws_plugin__s2member_utils_conds::in_descendant_category ($catgs, $post_id)) && (!$check_user || !$user || !$user->has_cap ("access_s2member_level" . $n)))
															return apply_filters ("ws_plugin__s2member_check_specific_post_level_access", array ("s2member_level_req" => $n), get_defined_vars ());
													}

												if (has_tag ("", $post_id)) // Here we take a look to see if this Post has any Tags. If so, we need to run the full set of routines against Tags also.
													{
														for ($n = $GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["levels"]; $n >= 0; $n--) // Tag Level restrictions now. Go through each Level.
															{
																if ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["level" . $n . "_ptags"] === "all" && (!$check_user || !$user || !$user->has_cap ("access_s2member_level" . $n)))
																	return apply_filters ("ws_plugin__s2member_check_specific_post_level_access", array ("s2member_level_req" => $n), get_defined_vars ());

																else if ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["level" . $n . "_ptags"] && has_tag (preg_split ("/[\r\n\t;,]+/", $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["level" . $n . "_ptags"]), $post_id) && (!$check_user || !$user || !$user->has_cap ("access_s2member_level" . $n)))
																	return apply_filters ("ws_plugin__s2member_check_specific_post_level_access", array ("s2member_level_req" => $n), get_defined_vars ());
															}
													}

												for ($n = $GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["levels"]; $n >= 0; $n--) // URIs. Go through each Level.
													{
														if ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["level" . $n . "_ruris"]) // URIs configured at this Level?

															foreach (preg_split ("/[\r\n\t]+/", c_ws_plugin__s2member_ruris::fill_ruri_level_access_rc_vars ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["level" . $n . "_ruris"], $user)) as $str)
																if ($str && preg_match ("/" . preg_quote ($str, "/") . "/", $post_uri) && (!$check_user || !$user || !$user->has_cap ("access_s2member_level" . $n)))
																	return apply_filters ("ws_plugin__s2member_check_specific_post_level_access", array ("s2member_level_req" => $n), get_defined_vars ());
													}

												if (is_array ($ccaps_req = get_post_meta ($post_id, "s2member_ccaps_req", true)) && !empty ($ccaps_req))
													{
														foreach ($ccaps_req as $ccap) // The $user MUST satisfy ALL Custom Capabilities. Serialized array.
															if (strlen ($ccap) && (!$check_user || !$user || !$user->has_cap ("access_s2member_ccap_" . $ccap)))
																return apply_filters ("ws_plugin__s2member_check_specific_post_level_access", array ("s2member_ccap_req" => $ccap), get_defined_vars ());
													}

												if ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["specific_ids"] && in_array ($post_id, preg_split ("/[\r\n\t\s;,]+/", $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["specific_ids"])) && (!$check_user || !c_ws_plugin__s2member_sp_access::sp_access ($post_id, "read-only")))
													return apply_filters ("ws_plugin__s2member_check_specific_post_level_access", array ("s2member_sp_req" => $post_id), get_defined_vars ());
											}

										do_action ("ws_plugin__s2member_during_check_specific_post_level_access", get_defined_vars ());
									}
							}

						return apply_filters ("ws_plugin__s2member_check_specific_post_level_access", null, get_defined_vars ());
					}
			}
	}
?>