<?php
/**
* Membership Options Page.
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
* @package s2Member\Membership_Options_Page
* @since 3.5
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit ("Do not access this file directly.");

if (!class_exists ("c_ws_plugin__s2member_mo_page"))
	{
		/**
		* Membership Options Page.
		*
		* @package s2Member\Membership_Options_Page
		* @since 3.5
		*/
		class c_ws_plugin__s2member_mo_page
			{
				/**
				* Forces a redirection to the Membership Options Page for s2Member.
				*
				* This can be used by 3rd party apps that are not aware of which Page is currently set as the Membership Options Page.
				* Example usage: `http://example.com/?s2member_membership_options_page=1`
				*
				* @package s2Member\Membership_Options_Page
				* @since 3.5
				*
				* @attaches-to ``add_action("init");``
				*
				* @return null|inner Return-value of inner routine.
				*/
				public static function membership_options_page ()
					{
						if (!empty ($_GET["s2member_membership_options_page"]))
							{
								return c_ws_plugin__s2member_mo_page_in::membership_options_page ();
							}
					}
				/**
				* Redirects to Membership Options Page w/ MOP Vars.
				*
				* @package s2Member\Membership_Options_Page
				* @since 111101
				*
				* @param str $seeking_type Seeking content type. One of: `post|page|catg|ptag|file|ruri`.
				* @param str|int $seeking_type_value Seeking content type data. String, or a Post/Page ID.
				* @param str $req_type Access requirement type. One of these values: `level|ccap|sp`.
				* @param str|int $req_type_value Access requirement. String, or a Post/Page ID.
				* @param str $seeking_uri The full URI that access was attempted on.
				* @param str $res_type Restriction type that's preventing access.
				* 	One of: `post|page|catg|ptag|file|ruri|ccap|sp|sys`.
				* 	Defaults to ``$seeking_type``.
				* @return inner Return-value of inner routine.
				*/
				public static function wp_redirect_w_mop_vars ($seeking_type = FALSE, $seeking_type_value = FALSE, $req_type = FALSE, $req_type_value = FALSE, $seeking_uri = FALSE, $res_type = FALSE)
					{
						return c_ws_plugin__s2member_mo_page_in::wp_redirect_w_mop_vars ($seeking_type, $seeking_type_value, $req_type, $req_type_value, $seeking_uri, $res_type);
					}

				public static function back_compat_mop_vars()
					{
						if(empty($_REQUEST["_s2member_vars"])
						   || !is_string($_REQUEST["_s2member_vars"])
						) return;

						foreach(explode(";", $_REQUEST["_s2member_vars"]) as $_v)
							$v[] = explode(",", $_v);
						unset($_v);

						if(!isset($v, $v[0], $v[1])
						   || count($v[0]) !== 3 || count($v[1]) !== 3
						) return;

						/*
						 * Back compat. Deprecated since v1404xx.
						 */
						$ov["_s2member_seeking"]     = array(
							"type"   => $v[1][0],
							$v[1][0] => $v[1][1],
							"_uri"   => $v[1][2]
						);
						$ov["_s2member_req"]         = array(
							"type"   => $v[0][1],
							$v[0][1] => $v[0][2],
						);
						$ov["_s2member_res"]["type"] = $v[0][0];

						/*
						 * Back compat. Deprecated since v1104xx.
						 */
						$ov["s2member_seeking"]          = $v[1][0]."-".$v[1][1];
						$ov["s2member_".$v[0][1]."_req"] = $v[0][2];

						/*
						 * Fill both $_GET and $_REQUEST vars.
						 */
						foreach($ov as $_k => $_v)
							$_GET[$_k] = $_REQUEST[$_k] = $_v;
						unset($_k, $_v);
					}
			}
	}
?>