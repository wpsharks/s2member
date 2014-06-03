<?php
/**
* Systematics *(for a specific page)*.
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
* @package s2Member\Systematics
* @since 3.5
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");

if (!class_exists ("c_ws_plugin__s2member_systematics_sp"))
	{
		/**
		* Systematics *(for a specific page)*.
		*
		* @package s2Member\Systematics
		* @since 3.5
		*/
		class c_ws_plugin__s2member_systematics_sp
			{
				/**
				* Determines if a specific Post/Page ID, or URI, is s2Member Systematic.
				*
				* @package s2Member\Systematics
				* @since 111115
				*
				* @param int|string $singular_id Optional. A numeric Post/Page ID in WordPress.
				* @param string $uri Optional. A request URI to test against.
				* @return bool True if s2Member Systematic, else false.
				*/
				public static function is_s2_systematic_use_specific_page ($singular_id = FALSE, $uri = FALSE)
					{
						$singular_id = ($singular_id && is_numeric ($singular_id)) ? (int)$singular_id : false; // Force types.
						$uri = ($uri && is_string ($uri) && ($uri = c_ws_plugin__s2member_utils_urls::parse_uri ($uri))) ? $uri : false;

						if ($uri && ($_q = c_ws_plugin__s2member_utils_urls::parse_url ($uri, PHP_URL_QUERY)) && preg_match ("/[\?&]s2member/", $_q) && c_ws_plugin__s2member_utils_conds::is_site_root ($uri))
							{
								return ($is_s2_systematic = apply_filters("ws_plugin__s2member_is_s2_systematic_use_specific_page", true, get_defined_vars ()));
							}
						else // Otherwise, we return false (i.e. it's NOT an s2Member Systematic Use Page).
							return ($is_s2_systematic = apply_filters("ws_plugin__s2member_is_s2_systematic_use_specific_page", false, get_defined_vars ()));
					}
				/**
				* Determines if a specific Post/Page ID, or URI, is WordPress Systematic.
				*
				* @package s2Member\Systematics
				* @since 111002
				*
				* @param int|string $singular_id Optional. A numeric Post/Page ID in WordPress.
				* @param string $uri Optional. A request URI to test against.
				* @return bool True if WordPress Systematic, else false.
				*/
				public static function is_wp_systematic_use_specific_page ($singular_id = FALSE, $uri = FALSE)
					{
						$singular_id = ($singular_id && is_numeric ($singular_id)) ? (int)$singular_id : false; // Force types.
						$uri = ($uri && is_string ($uri) && ($uri = c_ws_plugin__s2member_utils_urls::parse_uri ($uri))) ? $uri : false;

						if ($uri && preg_match ("/\/wp-admin(?:\/|\?|$)/", $uri)) // Inside a WordPress administrative area?
							{
								return ($is_wp_systematic = apply_filters("ws_plugin__s2member_is_wp_systematic_use_specific_page", true, get_defined_vars ()));
							}
						else if ($uri && preg_match ("/^\/(?:wp-.+?|xmlrpc)\.php$/", c_ws_plugin__s2member_utils_urls::parse_url ($uri, PHP_URL_PATH)))
							{
								return ($is_wp_systematic = apply_filters("ws_plugin__s2member_is_wp_systematic_use_specific_page", true, get_defined_vars ()));
							}
						else // Otherwise, we return false (i.e. it's NOT a WordPress Systematic Use Page).
							return ($is_wp_systematic = apply_filters("ws_plugin__s2member_is_wp_systematic_use_specific_page", false, get_defined_vars ()));
					}
				/**
				* Determines if a specific Post/Page ID, or URI, is Systematic in any way.
				*
				* @package s2Member\Systematics
				* @since 3.5
				*
				* @param int|string $singular_id Optional. A numeric Post/Page ID in WordPress.
				* @param string $uri Optional. A request URI to test against.
				* @return bool True if Systematic, else false.
				*
				* @todo Test URIs against formulated links for Systematic Pages like the Membership Options Page?
				* 	Don't think this is required though; as it's already handled in other areas, correct?
				*/
				public static function is_systematic_use_specific_page ($singular_id = FALSE, $uri = FALSE)
					{
						global $bp; // If BuddyPress is installed, we'll need this global reference.

						$singular_id = ($singular_id && is_numeric ($singular_id)) ? (int)$singular_id : false; // Force types.
						$uri = ($uri && is_string ($uri) && ($uri = c_ws_plugin__s2member_utils_urls::parse_uri ($uri))) ? $uri : false;

						if (c_ws_plugin__s2member_systematics_sp::is_s2_systematic_use_specific_page /* An s2Member Systematic Use Page? */ ($singular_id, $uri))
							{
								return ($is_systematic = apply_filters("ws_plugin__s2member_is_systematic_use_specific_page", true, get_defined_vars ()));
							}
						else if (c_ws_plugin__s2member_systematics_sp::is_wp_systematic_use_specific_page /* A WordPress Systematic Use Page? */ ($singular_id, $uri))
							{
								return ($is_systematic = apply_filters("ws_plugin__s2member_is_systematic_use_specific_page", true, get_defined_vars ()));
							}
						else if ($uri && c_ws_plugin__s2member_utils_conds::bp_is_installed () && preg_match ("/\/(?:" . preg_quote (trim (((function_exists ("bp_get_signup_slug")) ? bp_get_signup_slug () : BP_REGISTER_SLUG), "/"), "/") . "|" . preg_quote (trim (((function_exists ("bp_get_activate_slug")) ? bp_get_activate_slug () : BP_ACTIVATION_SLUG), "/"), "/") . ")(?:\/|\?|$)/", $uri))
							{
								return ($is_systematic = apply_filters("ws_plugin__s2member_is_systematic_use_specific_page", true, get_defined_vars ()));
							}
						else if ($singular_id && c_ws_plugin__s2member_utils_conds::bp_is_installed () && ((!empty($bp->pages->register->id) && $singular_id === (int)$bp->pages->register->id) || (!empty($bp->pages->activate->id) && $singular_id === (int)$bp->pages->activate->id)))
							{
								return ($is_systematic = apply_filters("ws_plugin__s2member_is_systematic_use_specific_page", true, get_defined_vars ()));
							}
						else if ($singular_id && $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_welcome_page"] && $singular_id === (int)$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_welcome_page"])
							{
								return ($is_systematic = apply_filters("ws_plugin__s2member_is_systematic_use_specific_page", true, get_defined_vars ()));
							}
						else if ($singular_id && $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["membership_options_page"] && $singular_id === (int)$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["membership_options_page"])
							{
								return ($is_systematic = apply_filters("ws_plugin__s2member_is_systematic_use_specific_page", true, get_defined_vars ()));
							}
						else if ($singular_id && $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["file_download_limit_exceeded_page"] && $singular_id === (int)$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["file_download_limit_exceeded_page"])
							{
								return ($is_systematic = apply_filters("ws_plugin__s2member_is_systematic_use_specific_page", true, get_defined_vars ()));
							}
						else if ($uri && $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_redirection_override"] && ($_lro = c_ws_plugin__s2member_login_redirects::login_redirection_uri (false, "root-returns-false")) && preg_match ("/^" . preg_quote ($_lro, "/") . "$/", $uri))
							{
								return ($is_systematic = apply_filters("ws_plugin__s2member_is_systematic_use_specific_page", true, get_defined_vars ()));
							}
						else // Otherwise, we return false (i.e. it's NOT a Systematic Use Page in any way).
							return ($is_systematic = apply_filters("ws_plugin__s2member_is_systematic_use_specific_page", false, get_defined_vars ()));
					}
			}
	}
?>