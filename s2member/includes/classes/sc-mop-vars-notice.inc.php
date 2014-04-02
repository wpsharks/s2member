<?php
/**
 * Shortcode for `[s2MOP /]`.
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
 * @package s2Member\Shortcodes
 * @since 140331
 */
if(realpath(__FILE__) === realpath($_SERVER["SCRIPT_FILENAME"]))
	exit ("Do not access this file directly.");

if(!class_exists("c_ws_plugin__s2member_sc_mop_vars_notice"))
	{
		/**
		 * Shortcode for `[s2MOP /]`.
		 *
		 * @package s2Member\Shortcodes
		 * @since 140331
		 */
		class c_ws_plugin__s2member_sc_mop_vars_notice
		{
			public static function shortcode($attr = array(), $content = "", $shortcode = "")
				{
					return c_ws_plugin__s2member_sc_mop_vars_notice_in::shortcode($attr, $content, $shortcode);
				}
		}
	}
?>