<?php
/**
* IP Restrictions.
*
* Copyright: © 2009-2011
* {@link http://www.websharks-inc.com/ WebSharks, Inc.}
* ( coded in the USA )
*
* Released under the terms of the GNU General Public License.
* You should have received a copy of the GNU General Public License,
* along with this software. In the main directory, see: /licensing/
* If not, see: {@link http://www.gnu.org/licenses/}.
*
* @package s2Member\IP_Restrictions
* @since 3.5
*/
if(realpath(__FILE__) === realpath($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");
/**/
if(!class_exists("c_ws_plugin__s2member_ip_restrictions"))
	{
		/**
		* IP Restrictions.
		*
		* @package s2Member\IP_Restrictions
		* @since 3.5
		*/
		class c_ws_plugin__s2member_ip_restrictions
			{
				/**
				* Handles IP Restrictions.
				*
				* IP address details are stored as Transients.
				*
				* @package s2Member\IP_Restrictions
				* @since 3.5
				*
				* @param str $ip IP Address.
				* @param str $restriction Unique IP Restriction name/identifier. Such as a Username, or a unique access code.
				* @return bool True if IP Restrictions are OK, otherwise this function will exit script execution after issuing a warning.
				*/
				public static function ip_restrictions_ok($ip = FALSE, $restriction = FALSE)
					{
						eval('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action("ws_plugin__s2member_before_ip_restrictions_ok", get_defined_vars());
						unset($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						if(!apply_filters("ws_plugin__s2member_disable_all_ip_restrictions", false, get_defined_vars())
						/* Also allow specific exclusions here. */ && !apply_filters("ws_plugin__s2member_disable_specific_ip_restriction", false, get_defined_vars())
						/* And enabled by site owner? */ && $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["max_ip_restriction"] /* And a valid ``$restriction``? */ && $restriction && is_string($restriction)
						/* Flag indicating that we ARE processing this IP Restriction. Useful in Hooks/Filters. */ && ($processing = true))
							{
								$msg_503 = _x('<strong>503: Service Temporarily Unavailable</strong><br />Too many IP addresses accessing one secure area<em>!</em><br />Please contact Support if you need assistance.', "s2member-front", "s2member");
								/**/
								$prefix = /* s2Member Transient prefix for all IP Restrictions. Allows s2Member to find these easily. */ "s2m_ipr_";
								/**/
								$transient_entries = $prefix.md5("s2member_ip_restrictions_".$restriction."_entries");
								$transient_security_breach = $prefix.md5("s2member_ip_restrictions_".$restriction."_security_breach");
								/**/
								/* If you add Filters, use a string compatible with PHP's strtotime() function. */
								$concurrency = apply_filters("ws_plugin__s2member_ip_restrictions__concurrency_time_per_ip", "30 days");
								/**/
								$entries = (is_array($entries = get_transient($transient_entries))) ? $entries : array();
								/**/
								foreach($entries as $_entry => $_time) /* Auto-expire entries, based on time. */
									if($_time < strtotime("-".$concurrency)) /* Based on time. */
										unset($entries[$_entry]); /* Unset this entry value. */
								/**/
								$ip = ($ip && is_string($ip)) ? $ip : "empty"; /* Allow empty IPs. */
								$entries[$ip] = strtotime("now"); /* Log entry. Add IP with entry time. */
								set_transient($transient_entries, $entries, 2 * (strtotime("+".$concurrency) - strtotime("now")));
								/**/
								if(get_transient($transient_security_breach)) /* Already breached security? */
									{
										c_ws_plugin__s2member_no_cache::no_cache_constants(true);
										/**/
										status_header(503); /* Send a 503 error status header; temporarily unavailable. */
										wp_clear_auth_cookie(); /* Clear authorization cookies; we need to log them out now. */
										header("Content-Type: text/html; charset=utf-8"); /* Content-Type text/html with UTF-8. */
										eval('while (@ob_end_clean ());'); /* End/clean any output buffers that may exist. */
										/**/
										eval('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
										do_action("ws_plugin__s2member_during_ip_restrictions_ok_no", get_defined_vars());
										unset($__refs, $__v); /* Unset defined __refs, __v. */
										/**/
										exit($msg_503); /* Clean exit with 503 error message. */
									}
								/**/
								else if(count($entries) > $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["max_ip_restriction"])
									{
										c_ws_plugin__s2member_no_cache::no_cache_constants(true);
										/**/
										set_transient /* A security breach has just occurred. We need to set this Transient now. */
										($transient_security_breach, 1, $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["max_ip_restriction_time"]);
										/**/
										status_header(503); /* Send a 503 error status header; temporarily unavailable. */
										wp_clear_auth_cookie(); /* Clear authorization cookies; we need to log them out now. */
										header("Content-Type: text/html; charset=utf-8"); /* Content-Type text/html with UTF-8. */
										eval('while (@ob_end_clean ());'); /* End/clean any output buffers that may exist. */
										/**/
										eval('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
										do_action("ws_plugin__s2member_during_ip_restrictions_ok_no", get_defined_vars());
										unset($__refs, $__v); /* Unset defined __refs, __v. */
										/**/
										exit($msg_503); /* Clean exit with 503 error message. */
									}
								/**/
								else /* OK, this looks legitimate. Apply Filters here and return true. */
									{
										eval('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
										do_action("ws_plugin__s2member_during_ip_restrictions_ok_yes", get_defined_vars());
										unset($__refs, $__v); /* Unset defined __refs, __v. */
										/**/
										return apply_filters("ws_plugin__s2member_ip_restrictions_ok", true, get_defined_vars());
									}
							}
						/**/
						eval('foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;');
						do_action("ws_plugin__s2member_during_ip_restrictions_ok_yes", get_defined_vars());
						unset($__refs, $__v); /* Unset defined __refs, __v. */
						/**/
						return apply_filters("ws_plugin__s2member_ip_restrictions_ok", true, get_defined_vars());
					}
				/**
				* Queries Transients for specific IP Restrictions associated with a security breach.
				*
				* @package s2Member\IP_Restrictions
				* @since 3.5
				*
				* @param str $restriction Unique IP Restriction name/identifier. Such as a Username, or a unique access code.
				* @return bool True if the specific IP Restriction is associated with a security breach, else false.
				*/
				public static function specific_ip_restriction_breached_security($restriction = FALSE)
					{
						do_action("ws_plugin__s2member_before_specific_ip_restriction_breached_security", get_defined_vars());
						/**/
						$prefix = "s2m_ipr_"; /* s2Member Transient prefix for all IP Restrictions. */
						$transient_security_breach = $prefix.md5("s2member_ip_restrictions_".(string)$restriction."_security_breach");
						$breached_security = $associated_with_security_breach = (get_transient($transient_security_breach)) ? true : false;
						/**/
						return apply_filters("ws_plugin__s2member_before_specific_ip_restriction_breached_security", $breached_security, get_defined_vars());
					}
				/**
				* Resets/deletes specific IP Restrictions.
				*
				* @package s2Member\IP_Restrictions
				* @since 3.5
				*
				* @param str $restriction Unique IP Restriction name/identifier. Such as a Username, or a unique access code.
				* @return bool Always returns a `true` value.
				*
				* @todo Make return value conditional, based on success.
				*/
				public static function delete_reset_specific_ip_restrictions($restriction = FALSE)
					{
						global $wpdb; /* Need global database object. */
						/**/
						do_action("ws_plugin__s2member_before_delete_reset_specific_ip_restrictions", get_defined_vars());
						/**/
						$prefix = "s2m_ipr_"; /* s2Member Transient prefix for all IP Restrictions. */
						$transient_entries = $prefix.md5("s2member_ip_restrictions_".(string)$restriction."_entries");
						$transient_security_breach = $prefix.md5("s2member_ip_restrictions_".(string)$restriction."_security_breach");
						/**/
						$wpdb->query("DELETE FROM `".$wpdb->options."` WHERE `option_name` LIKE '%".esc_sql(like_escape($transient_entries))."'");
						$wpdb->query("DELETE FROM `".$wpdb->options."` WHERE `option_name` LIKE '%".esc_sql(like_escape($transient_security_breach))."'");
						/**/
						do_action("ws_plugin__s2member_after_delete_reset_specific_ip_restrictions", get_defined_vars());
						/**/
						return apply_filters("ws_plugin__s2member_delete_reset_specific_ip_restrictions", true, get_defined_vars());
					}
				/**
				* Resets/deletes specific IP Restrictions via AJAX.
				*
				* @package s2Member\IP_Restrictions
				* @since 3.5
				*
				* @attaches-to ``add_action("wp_ajax_ws_plugin__s2member_delete_reset_specific_ip_restrictions_via_ajax");``
				*
				* @return null Exits script execution after returning data for AJAX caller.
				*/
				public static function delete_reset_specific_ip_restrictions_via_ajax()
					{
						do_action("ws_plugin__s2member_before_delete_reset_specific_ip_restrictions_via_ajax", get_defined_vars());
						/**/
						status_header(200); /* Send a 200 OK status header. */
						header("Content-Type: text/plain; charset=utf-8"); /* Content-Type with UTF-8. */
						eval('while (@ob_end_clean ());'); /* End/clean all output buffers that may exist. */
						/**/
						if(current_user_can("create_users")) /* Check priveledges. Ability to create Users? */
							/**/
							if(!empty($_POST["ws_plugin__s2member_delete_reset_specific_ip_restrictions_via_ajax"]))
								if(($nonce = $_POST["ws_plugin__s2member_delete_reset_specific_ip_restrictions_via_ajax"]))
									if(wp_verify_nonce($nonce, "ws-plugin--s2member-delete-reset-specific-ip-restrictions-via-ajax"))
										/**/
										if(!empty($_POST["ws_plugin__s2member_delete_reset_specific_ip_restriction"]))
											if(is_string /* Must be a string here. */($_POST["ws_plugin__s2member_delete_reset_specific_ip_restriction"]))
												if(($restriction = trim(stripslashes($_POST["ws_plugin__s2member_delete_reset_specific_ip_restriction"]))))
													/**/
													if(c_ws_plugin__s2member_ip_restrictions::delete_reset_specific_ip_restrictions($restriction))
														$success = true; /* Yes, this IP Restriction was deleted/reset. */
						/**/
						exit(apply_filters("ws_plugin__s2member_delete_reset_specific_ip_restrictions_via_ajax", ((isset($success) && $success) ? "1" : "0"), get_defined_vars()));
					}
				/**
				* Resets/deletes all IP Restrictions.
				*
				* @package s2Member\IP_Restrictions
				* @since 3.5
				*
				* @return bool Always returns a `true` value.
				*
				* @todo Make return value conditional, based on success.
				*/
				public static function delete_reset_all_ip_restrictions()
					{
						global $wpdb; /* Need global database object. */
						/**/
						do_action("ws_plugin__s2member_before_delete_reset_all_ip_restrictions", get_defined_vars());
						/**/
						$wpdb->query("DELETE FROM `".$wpdb->options."` WHERE `option_name` LIKE '".esc_sql(like_escape("_transient_s2m_ipr_"))."%'");
						$wpdb->query("DELETE FROM `".$wpdb->options."` WHERE `option_name` LIKE '".esc_sql(like_escape("_transient_timeout_s2m_ipr_"))."%'");
						/**/
						do_action("ws_plugin__s2member_after_delete_reset_all_ip_restrictions", get_defined_vars());
						/**/
						return apply_filters("ws_plugin__s2member_delete_reset_all_ip_restrictions", true, get_defined_vars());
					}
				/**
				* Resets/deletes all IP Restrictions via AJAX.
				*
				* @package s2Member\IP_Restrictions
				* @since 3.5
				*
				* @attaches-to ``add_action("wp_ajax_ws_plugin__s2member_delete_reset_all_ip_restrictions_via_ajax");``
				*
				* @return null Exits script execution after returning data for AJAX caller.
				*/
				public static function delete_reset_all_ip_restrictions_via_ajax()
					{
						do_action("ws_plugin__s2member_before_delete_reset_all_ip_restrictions_via_ajax", get_defined_vars());
						/**/
						status_header(200); /* Send a 200 OK status header. */
						header("Content-Type: text/plain; charset=utf-8"); /* Content-Type with UTF-8. */
						eval('while (@ob_end_clean ());'); /* End/clean all output buffers that may exist. */
						/**/
						if(current_user_can("create_users")) /* Check priveledges. Ability to create Users? */
							/**/
							if(!empty($_POST["ws_plugin__s2member_delete_reset_all_ip_restrictions_via_ajax"]))
								if(($nonce = $_POST["ws_plugin__s2member_delete_reset_all_ip_restrictions_via_ajax"]))
									if(wp_verify_nonce($nonce, "ws-plugin--s2member-delete-reset-all-ip-restrictions-via-ajax"))
										/**/
										if(c_ws_plugin__s2member_ip_restrictions::delete_reset_all_ip_restrictions())
											$success = true; /* Yes, all IP Restrictions were deleted/reset. */
						/**/
						exit(apply_filters("ws_plugin__s2member_delete_reset_all_ip_restrictions_via_ajax", ((isset($success) && $success) ? "1" : "0"), get_defined_vars()));
					}
			}
	}
?>