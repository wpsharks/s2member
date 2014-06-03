<?php
/**
* Pluggable functions within WordPress.
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
* @package s2Member
* @since 110707
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");

if (!function_exists ("wp_new_user_notification"))
	{
		/**
		* New User notifications.
		*
		* The arguments to this function are passed through the class method.
		*
		* @package s2Member
		* @since 110707
		*
		* @return class Return-value of class method.
		*/
		if ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["new_user_emails_enabled"])
			{
				function /* Accepts any number of arguments. */ wp_new_user_notification ()
					{
						$args = /* Pulls the arguments passed in to this function. */ func_get_args ();

						return call_user_func_array("c_ws_plugin__s2member_email_configs::new_user_notification", $args);
					}
				add_filter /* Combine. */ ("wpmu_welcome_user_notification", "wp_new_user_notification", 10, 2);
			}
		$GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["pluggables"]["wp_new_user_notification"] = true;
	}
?>