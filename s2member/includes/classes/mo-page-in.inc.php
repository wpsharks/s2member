<?php
/**
* Membership Options Page ( inner processing routines ).
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
* @package s2Member\Membership_Options_Page
* @since 3.5
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit ("Do not access this file directly.");
/**/
if (!class_exists ("c_ws_plugin__s2member_mo_page_in"))
	{
		/**
		* Membership Options Page ( inner processing routines ).
		*
		* @package s2Member\Membership_Options_Page
		* @since 3.5
		*/
		class c_ws_plugin__s2member_mo_page_in
			{
				/**
				* Forces a redirection to the Membership Options Page for s2Member.
				*
				* This can be used by 3rd party apps that are not aware of which Page is currently set as the Membership Options Page.
				* Example usage: `http://example.com/?s2member_membership_options_page=1`
				*
				* Redirection URLs containing array brackets MUST be URL encoded to get through: ``wp_sanitize_redirect()``.
				* 	So we pass everything to ``urlencode_deep()``, as an array. It handles this via ``_http_build_query()``.
				* 	See bug report here: {@link http://core.trac.wordpress.org/ticket/17052}
				*
				* @package s2Member\Membership_Options_Page
				* @since 3.5
				*
				* @attaches-to ``add_action("init");``
				*
				* @return null Or exits script execution after redirection w/ `301` status.
				*/
				public static function membership_options_page /* Real Membership Options Page. */ ()
					{
						do_action ("ws_plugin__s2member_before_membership_options_page", get_defined_vars ());
						/**/
						if (!empty ($_GET["s2member_membership_options_page"]) && is_array ($_g = c_ws_plugin__s2member_utils_strings::trim_deep (stripslashes_deep ($_GET))))
							{
								$args = /* Initialize this to an empty array value. */ array ();
								/**/
								foreach ($_g as $var => $value) /* Include all of the `_?s2member_` variables. */
									/* Do NOT include `s2member_membership_options_page`; creates a redirection loop. */
									if (preg_match ("/^_?s2member_/", $var) && $var !== "s2member_membership_options_page")
										$args[$var] = /* Supports nested arrays. */ $value;
								/**/
								wp_redirect (add_query_arg (urlencode_deep ($args), get_page_link ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["membership_options_page"])), 301) . exit ();
							}
						/**/
						do_action ("ws_plugin__s2member_after_membership_options_page", get_defined_vars ());
					}
				/**
				* Redirects to Membership Options Page w/ MOP Vars.
				*
				* Redirection URLs containing array brackets MUST be URL encoded to get through: ``wp_sanitize_redirect()``.
				* 	So we pass everything to ``urlencode_deep()``, as an array. It handles this via ``_http_build_query()``.
				* 	See bug report here: {@link http://core.trac.wordpress.org/ticket/17052}
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
				* @return bool This function always returns true.
				*/
				public static function wp_redirect_w_mop_vars ($seeking_type = FALSE, $seeking_type_value = FALSE, $req_type = FALSE, $req_type_value = FALSE, $seeking_uri = FALSE, $res_type = FALSE)
					{
						do_action ("ws_plugin__s2member_before_wp_redirect_w_mop_vars", get_defined_vars ());
						/**/
						$status = /* Allow Filters. Defaults to `301`. */ apply_filters ("ws_plugin__s2member_content_redirect_status", 301, get_defined_vars ());
						$status = /* Allow Filters. Defaults to `301`. */ apply_filters ("ws_plugin__s2member_wp_redirect_w_mop_vars_status", $status, get_defined_vars ());
						/**/
						$seeking_uri = (strlen ((string)$seeking_uri) /* URIs are base64 encoded. */) ? base64_encode ((string)$seeking_uri) : (string)$seeking_uri;
						$seeking_type_value = ((string)$seeking_type /* URIs are base64 encoded. */ === "ruri") ? base64_encode ((string)$seeking_type_value) : (string)$seeking_type_value;
						/**/
						$res_type = (!(string)$res_type) ? /* Restriction type preventing access. Defaults to ``$seeking_type`` if NOT passed in explicitly. */ (string)$seeking_type : (string)$res_type;
						/**/
						wp_redirect (add_query_arg (urlencode_deep (array ("_s2member_seeking" => array ("type" => (string)$seeking_type, urlencode ((string)$seeking_type)=> (string)$seeking_type_value, "_uri" => (string)$seeking_uri), "_s2member_req" => array ("type" => (string)$req_type, urlencode ((string)$req_type)=> (string)$req_type_value), "_s2member_res" => array ("type" => (string)$res_type), "s2member_seeking" => (string)$seeking_type . "-" . (string)$seeking_type_value, "s2member_" . urlencode ((string)$req_type) . "_req" => (string)$req_type_value)), get_page_link ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["membership_options_page"])), $status);
						/**/
						do_action ("ws_plugin__s2member_after_wp_redirect_w_mop_vars", get_defined_vars ());
						/**/
						return true; /* Always returns true here. */
					}
			}
	}
?>