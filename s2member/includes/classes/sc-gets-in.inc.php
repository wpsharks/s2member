<?php
/**
* Shortcode `[s2Get /]` (inner processing routines).
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
* @package s2Member\s2Get
* @since 3.5
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");

if (!class_exists ("c_ws_plugin__s2member_sc_gets_in"))
	{
		/**
		* Shortcode `[s2Get /]` (inner processing routines).
		*
		* @package s2Member\s2Get
		* @since 3.5
		*/
		class c_ws_plugin__s2member_sc_gets_in
			{
				/**
				* Handles the Shortcode for: `[s2Get /]`.
				*
				* @package s2Member\s2Get
				* @since 3.5
				*
				* @attaches-to ``add_shortcode("s2Get");``
				*
				* @param array $attr An array of Attributes.
				* @param string $content Content inside the Shortcode.
				* @param string $shortcode The actual Shortcode name itself.
				* @return mixed Value of the requested data, or null on failure.
				*
				* @todo Prevent this routine from potentially returning objects/arrays?
				*/
				public static function sc_get_details ($attr = FALSE, $content = FALSE, $shortcode = FALSE)
					{
						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_before_sc_get_details", get_defined_vars ());
						unset($__refs, $__v);

						$attr = c_ws_plugin__s2member_utils_strings::trim_qts_deep ((array)$attr); // Force array; trim quote entities.

						$attr = shortcode_atts (array("constant" => "", "user_field" => "", "user_option" => "", "user_id" => ""), $attr);

						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_before_sc_get_details_after_shortcode_atts", get_defined_vars ());
						unset($__refs, $__v);

						if ($attr["constant"] && defined ($attr["constant"])) // Security check here. It must start with S2MEMBER_ on a Blog Farm.
							{
								if (!is_multisite () || !c_ws_plugin__s2member_utils_conds::is_multisite_farm () || is_main_site () || preg_match ("/^S2MEMBER_/i", $attr["constant"]))
									$get = constant ($attr["constant"]);
							}
						else if ($attr["user_field"] && (is_user_logged_in () || $attr["user_id"]))
							$get = c_ws_plugin__s2member_utils_users::get_user_field ($attr["user_field"], (int)$attr["user_id"]);

						else if ($attr["user_option"] && (is_user_logged_in () || $attr["user_id"]))
							$get = get_user_option ($attr["user_option"], (int)$attr["user_id"]);

						return apply_filters("ws_plugin__s2member_sc_get_details", ((isset ($get)) ? $get : null), get_defined_vars ());
					}
			}
	}
?>