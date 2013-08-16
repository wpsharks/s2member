<?php
/**
* Login customizations.
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
* @package s2Member\Login_Customizations
* @since 3.5
*/
if(realpath(__FILE__) === realpath($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");

if(!class_exists("c_ws_plugin__s2member_login_customizations"))
	{
		/**
		* Login customizations.
		*
		* @package s2Member\Login_Customizations
		* @since 3.5
		*/
		class c_ws_plugin__s2member_login_customizations
			{
				/**
				* Filters the login/registration logo URL.
				*
				* @package s2Member\Login_Customizations
				* @since 3.5
				*
				* @attaches-to ``add_filter("login_headerurl");``
				*
				* @param str $url Expects a login header URL passed in by the Filter.
				* @return str A URL based on s2Member's UI configuration.
				*/
				public static function login_header_url($url = FALSE)
					{
						if(!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_design_enabled"])
							return $url; // Login/Registration Design disabled in this case.

						do_action("ws_plugin__s2member_before_login_header_url", get_defined_vars());

						$url = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_logo_url"];

						return apply_filters("ws_plugin__s2member_login_header_url", $url, get_defined_vars());
					}
				/**
				* Filters the login/registration logo title.
				*
				* @package s2Member\Login_Customizations
				* @since 3.5
				*
				* @attaches-to ``add_filter("login_headertitle");``
				*
				* @param str $title Expects a title passed in by the Filter.
				* @return str A title based on s2Member's UI configuration.
				*/
				public static function login_header_title($title = FALSE)
					{
						if(!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_design_enabled"])
							return $title; // Login/Registration Design disabled in this case.

						do_action("ws_plugin__s2member_before_login_header_title", get_defined_vars());

						$title = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_logo_title"];

						return apply_filters("ws_plugin__s2member_login_header_title", $title, get_defined_vars());
					}
				/**
				* Styles login/registration *( i.e. `/wp-login.php` )*.
				*
				* @package s2Member\Login_Customizations
				* @since 3.5
				*
				* @attaches-to ``add_action("login_head");``
				*
				* @return void
				*/
				public static function login_header_styles()
					{
						if(!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_design_enabled"])
							return; // Login/Registration Design disabled in this case.

						$s = /* Initialize styles string here to give Hooks a chance. */ "";
						$a = /* Initialize here to give Filters a chance. */ array();

						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_before_login_header_styles", get_defined_vars());
						unset /* Unset defined __refs, __v. */ ($__refs, $__v);

						$a[] = /* Open style tag, then give Filters a chance below. */ '<style type="text/css">';
						$i = apply_filters("ws_plugin__s2member_login_header_styles_important", " !important", get_defined_vars());
						$a = apply_filters("ws_plugin__s2member_login_header_styles_array_after_open", $a, get_defined_vars());

						$a[] = /* Clear existing. */ 'html, body { border:0'.$i.'; background:none'.$i.'; }';
						$a[] = 'html { background-color:#'.$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_background_color"].$i.'; }';
						$a[] = 'html { background-image:url('.$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_background_image"].')'.$i.'; }';
						$a[] = 'html { background-repeat:'.$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_background_image_repeat"].$i.'; }';

						$a[] = 'body, body * { font-size:'.$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_font_size"].$i.'; }';
						$a[] = 'body, body * { font-family:'.$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_font_family"].$i.'; }';

						$a[] = 'div#login { width:'.$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_logo_src_width"].'px'.$i.'; }';
						$a[] = 'div#login h1 a { background:url('.$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_logo_src"].') no-repeat top center'.$i.'; background-size:auto'.$i.'; }';
						$a[] = 'div#login h1 a { display:block'.$i.'; width:100%'.$i.'; height:'.$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_logo_src_height"].'px'.$i.'; }';

						$a[] = 'div#login form { -moz-box-shadow:1px 1px 5px #'.$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_background_box_shadow_color"].', -1px -1px 5px #'.$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_background_box_shadow_color"].$i.'; -webkit-box-shadow:1px 1px 5px #'.$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_background_box_shadow_color"].', -1px -1px 5px #'.$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_background_box_shadow_color"].$i.'; box-shadow:1px 1px 5px #'.$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_background_box_shadow_color"].', -1px -1px 5px #'.$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_background_box_shadow_color"].$i.'; }';

						$a[] = 'div#login p#nav, div#login p#nav a, div#login p#nav a:hover, div#login p#nav a:active, div#login p#nav a:focus { color:#'.$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_background_text_color"].$i.'; text-shadow:1px 1px 3px #'.$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_background_text_shadow_color"].$i.'; }';
						$a[] = 'div#login p#backtoblog, div#login p#backtoblog a, div#login p#backtoblog a:hover, div#login p#backtoblog a:active, div#login p#backtoblog a:focus { color:#'.$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_background_text_color"].$i.'; text-shadow:1px 1px 3px #'.$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_background_text_shadow_color"].$i.'; }';

						$a[] = /* Handles paragraph margins inside the form. */ 'div#login form p { margin:2px 0 16px 0'.$i.'; }';
						$a[] = 'div#login form input[type="text"], div#login form input[type="email"], div#login form input[type="password"], div#login form textarea, div#login form select { font-weight:normal'.$i.'; color:#333333'.$i.'; background:none repeat scroll 0 0 #FBFBFB'.$i.'; border:1px solid #E5E5E5'.$i.'; font-size:'.$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_font_field_size"].$i.'; margin:0'.$i.'; padding:3px'.$i.'; -moz-border-radius:3px'.$i.'; -webkit-border-radius:3px'.$i.'; border-radius:3px'.$i.'; width:100%'.$i.'; width:98%'.$i.' !ie<8; margin-right:2%'.$i.' !ie<8; box-sizing:border-box'.$i.'; -ms-box-sizing:border-box'.$i.'; -moz-box-sizing:border-box'.$i.'; -webkit-box-sizing:border-box'.$i.'; }';
						$a[] = 'div#login form select { width:99.5%'.$i.' !ie<8; } div#login form select > option { font-size:'.$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_font_field_size"].$i.'; }';
						$a[] = 'div#login form label { cursor:pointer'.$i.'; } div#login form label.ws-plugin--s2member-custom-reg-field-op-l { opacity:0.7'.$i.'; font-size:90%'.$i.'; vertical-align:middle'.$i.'; }';
						$a[] = 'div#login form input[type="checkbox"], div#login form input[type="radio"] { margin:0 3px 0 0'.$i.'; vertical-align:middle'.$i.'; }';
						$a[] = 'div#login form input#ws-plugin--s2member-custom-reg-field-user-pass2[type="password"] { margin-top:5px'.$i.'; }';

						$a[] = 'div#login form div.ws-plugin--s2member-custom-reg-field-divider-section { margin:2px 0 16px 0'.$i.'; border:0'.$i.'; height:1px'.$i.'; line-height:1px'.$i.'; background:#CCCCCC'.$i.'; }';
						$a[] = 'div#login form div.ws-plugin--s2member-custom-reg-field-divider-section-title { margin:2px 0 16px 0'.$i.'; border:0 solid #CCCCCC'.$i.'; border-width:0 0 1px 0'.$i.'; padding:0 0 10px 0'.$i.'; font-size:110%'.$i.'; }';

						$a[] = 'div#login form input[type="submit"], div#login form input[type="submit"]:hover, div#login form input[type="submit"]:active, div#login form input[type="submit"]:focus { color:#666666'.$i.'; text-shadow:2px 2px 5px #EEEEEE'.$i.'; border:1px solid #999999'.$i.'; background:#FBFBFB'.$i.';'.((version_compare(get_bloginfo("version"), "3.5", "<")) ? ' padding:5px'.$i.';' : '').' -moz-border-radius:3px'.$i.'; -webkit-border-radius:3px'.$i.'; border-radius:3px'.$i.'; }';
						$a[] = 'div#login form input[type="submit"]:hover, div#login form input[type="submit"]:active, div#login form input[type="submit"]:focus { color:#000000'.$i.'; text-shadow:2px 2px 5px #CCCCCC'.$i.'; border-color:#000000'.$i.'; }';
						$a[] = 'div#login form#registerform { padding-bottom:16px'.$i.'; } div#login form#registerform p.submit { float:none'.$i.'; margin-top:-10px'.$i.'; } div#login form#registerform input[type="submit"] { float:none'.$i.'; width:100%'.$i.'; width:98%'.$i.' !ie<8; margin-right:2%'.$i.' !ie<8; box-sizing:border-box'.$i.'; -ms-box-sizing:border-box'.$i.'; -moz-box-sizing:border-box'.$i.'; -webkit-box-sizing:border-box'.$i.'; }';
						$a[] = 'div#login form#lostpasswordform { padding-bottom:16px'.$i.'; } div#login form#lostpasswordform p.submit { float:none'.$i.'; } div#login form#lostpasswordform input[type="submit"] { float:none'.$i.'; width:100%'.$i.'; width:98%'.$i.' !ie<8; margin-right:2%'.$i.' !ie<8; box-sizing:border-box'.$i.'; -ms-box-sizing:border-box'.$i.'; -moz-box-sizing:border-box'.$i.'; -webkit-box-sizing:border-box'.$i.'; }';

						$a[] = 'div.ws-plugin--s2member-password-strength { margin-top:3px'.$i.'; font-color:#000000'.$i.'; background-color:#EEEEEE'.$i.'; padding:3px'.$i.'; -moz-border-radius:3px'.$i.'; -webkit-border-radius:3px'.$i.'; border-radius:3px'.$i.'; } div.ws-plugin--s2member-password-strength-short { background-color:#FFA0A0'.$i.'; } div.ws-plugin--s2member-password-strength-bad { background-color:#FFB78C'.$i.'; } div.ws-plugin--s2member-password-strength-good { background-color:#FFEC8B'.$i.'; } div.ws-plugin--s2member-password-strength-strong { background-color:#C3FF88'.$i.'; } div.ws-plugin--s2member-password-strength-mismatch { background-color:#D6C1AB'.$i.'; }';

						$a[] = 'div#login form#registerform p#reg_passmail { font-style:italic'.$i.'; }';

						if($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_password"])
							$a[] = 'div#login form#registerform p#reg_passmail { display:none'.$i.'; }';

						if(!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_footer_backtoblog"])
							$a[] = 'div#login p#backtoblog { display:none'.$i.'; }';

						$a = apply_filters("ws_plugin__s2member_login_header_styles_array_before_close", $a, get_defined_vars());
						$a[] = /* Now close style tag. There are other Filters below. */ '</style>';

						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_during_login_header_styles", get_defined_vars());
						unset /* Unset defined __refs, __v. */ ($__refs, $__v);

						$a = apply_filters("ws_plugin__s2member_login_header_styles_array", $a, get_defined_vars());
						$s .= /* Now put all array elements together. */ "\n".implode("\n", $a)."\n\n";

						echo apply_filters("ws_plugin__s2member_login_header_styles", $s, get_defined_vars());

						do_action("ws_plugin__s2member_after_login_header_styles", get_defined_vars());

						return /* Return for uniformity. */;
					}
				/**
				* Displays login footer design.
				*
				* @package s2Member\Login_Customizations
				* @since 3.5
				*
				* @attaches-to ``add_action("login_footer");``
				*
				* @return void
				*/
				public static function login_footer_design()
					{
						if(!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_design_enabled"])
							return; // Login/Registration Design disabled in this case.

						do_action("ws_plugin__s2member_before_login_footer_design", get_defined_vars());

						if(($code = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_footer_design"]))

							if(is_multisite() && c_ws_plugin__s2member_utils_conds::is_multisite_farm() && !is_main_site())
								{
									echo /* No PHP here. */ $code."\n";
								}
							else // Otherwise, safe to allow PHP code.
								{
									eval("?>".$code);
								}

						do_action("ws_plugin__s2member_after_login_footer_design", get_defined_vars());

						return /* Return for uniformity. */;
					}
			}
	}
?>