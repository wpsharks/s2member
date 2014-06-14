<?php
/**
* s2Member's Category protection routines *(for current page)*.
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
* @package s2Member\Categories
* @since 3.5
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");

if (!class_exists ("c_ws_plugin__s2member_catgs"))
	{
		/**
		* s2Member's Category protection routines *(for current page)*.
		*
		* @package s2Member\Categories
		* @since 3.5
		*/
		class c_ws_plugin__s2member_catgs
			{
				/**
				* Handles Category Level Access *(for current page)*.
				*
				* @package s2Member\Categories
				* @since 3.5
				*
				* @return null Or exits script execution after redirection.
				*/
				public static function check_catg_level_access ()
					{
						global /* ``get_the_ID()`` is NOT available outside The Loop. */ $post;

						do_action("ws_plugin__s2member_before_check_catg_level_access", get_defined_vars ());

						$excluded = apply_filters("ws_plugin__s2member_check_catg_level_access_excluded", false, get_defined_vars ());

						if (!$excluded && is_category () && ($cat_id = get_query_var ("cat")) && ($cat_id = (int)$cat_id) && $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["membership_options_page"])
							{
								if /* Do NOT touch WordPress Systematics. This excludes all WordPress Systematics. */ (!c_ws_plugin__s2member_systematics::is_wp_systematic_use_page ())
									{
										$user = /* Current User's object. */ (is_user_logged_in () && is_object ($user = wp_get_current_user ()) && !empty($user->ID)) ? $user : false;

										if ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_redirection_override"] && ($login_redirection_uri = c_ws_plugin__s2member_login_redirects::login_redirection_uri ($user, "root-returns-false")) && preg_match ("/^" . preg_quote ($login_redirection_uri, "/") . "$/", $_SERVER["REQUEST_URI"]) && c_ws_plugin__s2member_no_cache::no_cache_constants ('restricted') && (!$user || !$user->has_cap ("access_s2member_level0")))
											c_ws_plugin__s2member_mo_page::wp_redirect_w_mop_vars /* Configure MOP Vars here. */ ("catg", $cat_id, "level", 0, $_SERVER["REQUEST_URI"], "sys") . exit ();

										else if /* Do NOT protect Systematics. However, there is 1 exception above. */ (!c_ws_plugin__s2member_systematics::is_systematic_use_page ())
											{
												for /* Category Level restrictions. Go through each Level. We also check nested Categories. */ ($n = $GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["levels"]; $n >= 0; $n--)
													{
														if ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["level" . $n . "_catgs"] === "all" && c_ws_plugin__s2member_no_cache::no_cache_constants ('restricted') && (!$user || !$user->has_cap ("access_s2member_level" . $n)))
															c_ws_plugin__s2member_mo_page::wp_redirect_w_mop_vars /* Configure MOP Vars here. */ ("catg", $cat_id, "level", $n, $_SERVER["REQUEST_URI"]) . exit ();

														else if ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["level" . $n . "_catgs"] && in_array($cat_id, ($catgs = preg_split ("/[\r\n\t\s;,]+/", $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["level" . $n . "_catgs"]))) && c_ws_plugin__s2member_no_cache::no_cache_constants ('restricted') && (!$user || !$user->has_cap ("access_s2member_level" . $n)))
															c_ws_plugin__s2member_mo_page::wp_redirect_w_mop_vars /* Configure MOP Vars here. */ ("catg", $cat_id, "level", $n, $_SERVER["REQUEST_URI"]) . exit ();

														else if ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["level" . $n . "_catgs"] /* Check Category ancestry. */)
															foreach (preg_split ("/[\r\n\t\s;,]+/", $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["level" . $n . "_catgs"]) as $catg)
																if ($catg && cat_is_ancestor_of ($catg, $cat_id) && c_ws_plugin__s2member_no_cache::no_cache_constants ('restricted') && (!$user || !$user->has_cap ("access_s2member_level" . $n)))
																	c_ws_plugin__s2member_mo_page::wp_redirect_w_mop_vars /* Configure MOP Vars here. */ ("catg", $cat_id, "level", $n, $_SERVER["REQUEST_URI"]) . exit ();
													}
												for /* URIs. Go through each Level. */ ($n = $GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["levels"]; $n >= 0; $n--)
													{
														if /* URIs configured at this Level? */ ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["level" . $n . "_ruris"])

															foreach (preg_split ("/[\r\n\t]+/", c_ws_plugin__s2member_ruris::fill_ruri_level_access_rc_vars ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["level" . $n . "_ruris"], $user)) as $str)
																if ($str && preg_match ("/" . preg_quote ($str, "/") . "/", $_SERVER["REQUEST_URI"]) && c_ws_plugin__s2member_no_cache::no_cache_constants ('restricted') && (!$user || !$user->has_cap ("access_s2member_level" . $n)))
																	c_ws_plugin__s2member_mo_page::wp_redirect_w_mop_vars /* Configure MOP Vars here. */ ("catg", $cat_id, "level", $n, $_SERVER["REQUEST_URI"], "ruri") . exit ();
													}
											}
										do_action("ws_plugin__s2member_during_check_catg_level_access", get_defined_vars ());
									}
							}
						do_action("ws_plugin__s2member_after_check_catg_level_access", get_defined_vars ());

						return /* For uniformity. */;
					}
			}
	}
?>