<?php
/**
* Login redirect removals.
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
* @package s2Member\Login_Redirects
* @since 3.5
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");

if (!class_exists ("c_ws_plugin__s2member_login_redirects_r"))
	{
		/**
		* Login redirect removals.
		*
		* @package s2Member\Login_Redirects
		* @since 3.5
		*/
		class c_ws_plugin__s2member_login_redirects_r
			{
				/**
				* Handles completely empty ``login_redirect`` values.
				*
				* Some plugins (most notably BuddyPress v1.5) have a nasty habit of sending an empty ``$_REQUEST["redirect_to"]`` input field with login widgets.
				* In the case of BuddyPress, it's own Filter against `login_redirect` will deal with empty values. However, since s2Member removes all Filters
				* against `login_redirect` (for compatibility), we NEED this simple routine to check empty values, and default them to ``admin_url()``.
				*
				* @attaches-to ``add_filter("login_redirect");``
				*
				* @package s2Member\Login_Redirects
				* @since 110926
				*
				* @param str $redirect_to Expects the current ``$redirect_to`` value, passed in by the Filter.
				* @return str A non-empty string value. s2Member will NEVER allow this to be completely empty.
				*/
				public static function _empty_login_redirect_filter ($redirect_to = FALSE)
					{
						return (empty ($redirect_to)) ? admin_url () : $redirect_to;
					}
				/**
				* Removes all other ``login_redirect`` Filters to prevent conflicts with s2Member.
				*
				* @attaches-to ``add_action("init");``
				*
				* @package s2Member\Login_Redirects
				* @since 3.5
				*
				* @return null
				*/
				public static function remove_login_redirect_filters ()
					{
						do_action ("ws_plugin__s2member_before_remove_login_redirect_filters", get_defined_vars ());

						if (!apply_filters ("ws_plugin__s2member_allow_other_login_redirect_filters", false, get_defined_vars ()))
							{
								remove_all_filters /* Removes all `login_redirect` Filters. */("login_redirect");
								add_filter ("login_redirect", "c_ws_plugin__s2member_login_redirects_r::_empty_login_redirect_filter");

								do_action ("ws_plugin__s2member_during_remove_login_redirect_filters", get_defined_vars ());
							}

						do_action ("ws_plugin__s2member_after_remove_login_redirect_filters", get_defined_vars ());

						return /* Return for uniformity. */;
					}
			}
	}
?>