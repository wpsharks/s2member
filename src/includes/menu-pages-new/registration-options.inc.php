<?php
// @codingStandardsIgnoreFile
/**
 * Menu page for the s2Member plugin (General Options page).
 *
 * Copyright: © 2009-2011
 * {@link http://websharks-inc.com/ WebSharks, Inc.}
 * (coded in the USA)
 *
 * Released under the terms of the GNU General Public License.
 * You should have received a copy of the GNU General Public License,
 * along with this software. In the main directory, see: /licensing/
 * If not, see: {@link http://www.gnu.org/licenses/}.
 *
 * @package s2Member\Menu_Pages
 * @since 3.0
 */
if(!defined('WPINC')) // MUST have WordPress.
	exit("Do not access this file directly.");

if(!class_exists("c_ws_plugin__s2member_menu_page_registration_options"))
{
	/**
	 * Menu page for the s2Member plugin (General Options page).
	 *
	 * @package s2Member\Menu_Pages
	 * @since 210208
	 */
	class c_ws_plugin__s2member_menu_page_registration_options {
		public function __construct() {
			echo '<div class="wrap ws-menu-page">'."\n";

			echo '<div class="wp-header-end"></div>'."\n";

			echo '<div class="ws-menu-page-toolbox">'."\n";
			c_ws_plugin__s2member_menu_pages_tb::display();
			echo '</div>'."\n";

			echo '<h2>Registration Options</h2>'."\n";

			echo '<table class="ws-menu-page-table">'."\n";
			echo '<tbody class="ws-menu-page-table-tbody">'."\n";
			echo '<tr class="ws-menu-page-table-tr">'."\n";
			echo '<td class="ws-menu-page-table-l">'."\n";

			echo '<form method="post" name="ws_plugin__s2member_options_form" id="ws-plugin--s2member-options-form" autocomplete="off">'."\n";
			echo '<input type="hidden" name="ws_plugin__s2member_options_save" id="ws-plugin--s2member-options-save" value="'.esc_attr(wp_create_nonce("ws-plugin--s2member-options-save")).'" />'."\n";
			echo '<input type="hidden" name="ws_plugin__s2member_configured" id="ws-plugin--s2member-configured" value="1" />'."\n";

			do_action("s2x_during_registration_options_page_before_left_sections", get_defined_vars());

			if(apply_filters("s2x_during_registration_options_page_during_left_sections_display_open_registration", TRUE, get_defined_vars()))
			{
				do_action("s2x_during_registration_options_page_during_left_sections_before_open_registration", get_defined_vars());

				if(is_multisite() && is_main_site()) // A Multisite Network, and we're on the Main Site?
				{
					echo '<div class="ws-menu-page-group" title="Open Registration">'."\n";

					echo '<div class="ws-menu-page-section ws-plugin--s2member-open-registration-section">'."\n";
					echo '<h3>Open Registration / Free Subscribers (optional)</h3>'."\n";
					echo '<p>On the Main Site of a Multisite Network, the settings for Open Registration are consolidated into the <strong>s2Member → Multisite (Config)</strong> panel.</p>'."\n";
					do_action("s2x_during_registration_options_page_during_left_sections_during_open_registration", get_defined_vars());
					echo '</div>'."\n";

					echo '</div>'."\n";
				}
				else // Else we display this section normally. No special considerations are required in this case.
				{
					echo '<div class="ws-menu-page-group" title="Open Registration">'."\n";

					echo '<div class="ws-menu-page-section ws-plugin--s2member-open-registration-section">'."\n";
					echo '<h3>Open Registration / Free Subscribers (optional)</h3>'."\n";
					echo '<p>s2Member supports Free Subscribers (at Level #0), along with four Primary Levels [1-4] of paid Membership. If you want your visitors to be capable of registering absolutely free, you will want to "allow" Open Registration. Whenever a visitor registers without paying, they\'ll automatically become a Free Subscriber at Level #0.</p>'."\n";
					do_action("s2x_during_registration_options_page_during_left_sections_during_open_registration", get_defined_vars());

					echo '<table class="form-table">'."\n";
					echo '<tbody>'."\n";
					echo '<tr>'."\n";

					echo '<th>'."\n";
					echo '<label for="ws-plugin--s2member-allow-subscribers-in">'."\n";
					echo 'Allow Open Registration? (Free Subscribers)'."\n";
					echo '</label>'."\n";
					echo '</th>'."\n";

					echo '</tr>'."\n";
					echo '<tr>'."\n";

					echo '<td>'."\n";
					echo '<select name="ws_plugin__s2member_allow_subscribers_in" id="ws-plugin--s2member-allow-subscribers-in">'."\n";
					echo '<option value="0"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["allow_subscribers_in"]) ? ' selected="selected"' : '').'>No (do NOT allow Open Registration)</option>'."\n";
					echo '<option value="1"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["allow_subscribers_in"]) ? ' selected="selected"' : '').'>Yes (allow Open Registration; Free Subscribers at Level #0)</option>'."\n";
					echo '</select><br />'."\n";
					echo 'If you set this to <code>Yes</code>, you\'re unlocking <a href="'.esc_attr(c_ws_plugin__s2member_utils_urls::wp_register_url()).'" target="_blank" rel="external" onclick="alert(\'s2Member will now open your Standard Registration Form.\\n* s2Member makes this form available to logged-in Administrators, at all times (for testing purposes), regardless of configuration.'.((c_ws_plugin__s2member_utils_conds::bp_is_installed()) ? '\\n\\nBuddyPress: BuddyPress will use its own Registration Form. Please note, you will probably be redirected away from the BuddyPress Registration Form ( '.c_ws_plugin__s2member_utils_strings::esc_js_sq(c_ws_plugin__s2member_utils_urls::bp_register_url()).' ), because you\\\'re already logged-in. Please log out before testing BuddyPress registration.' : '').'\');">/wp-login.php?action=register</a>. When a visitor registers without paying, they\'ll automatically become a Free Subscriber at Level #0. The s2Member software reserves Level #0; to be used only for Free Subscribers. All other Membership Levels [1-4] require payment.'."\n";
					echo (c_ws_plugin__s2member_utils_conds::bp_is_installed()) ? '<br /><br /><em><strong>BuddyPress:</strong> BuddyPress will use its own Registration Form <a href="'.esc_attr(c_ws_plugin__s2member_utils_urls::bp_register_url()).'" target="_blank" rel="external" onclick="alert(\'s2Member will now open your BuddyPress Registration Form.\\n* However, you will probably be redirected away from this BuddyPress Registration Form ( '.c_ws_plugin__s2member_utils_strings::esc_js_sq(c_ws_plugin__s2member_utils_urls::bp_register_url()).' ), because you\\\'re already logged-in. Please log out before testing BuddyPress registration.\');">here</a>.<br />s2Member integrates with BuddyPress, and the above setting will control Open Regisration for BuddyPress too.</em>'."\n" : '';
					echo '</td>'."\n";

					echo '</tr>'."\n";
					echo '</tbody>'."\n";
					echo '</table>'."\n";
					echo '</div>'."\n";

					echo '</div>'."\n";
				}
				do_action("s2x_during_registration_options_page_during_left_sections_after_open_registration", get_defined_vars());
			}
			if(apply_filters("s2x_during_registration_options_page_during_left_sections_display_membership_levels", TRUE, get_defined_vars()))
			{
				do_action("s2x_during_registration_options_page_during_left_sections_before_membership_levels", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="Membership Levels/Labels">'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-membership-levels-section">'."\n";
				echo '<h3>Membership Levels (required, please customize these)</h3>'."\n";
				echo '<p>The default Membership Levels are labeled generically; feel free to modify them as needed. s2Member supports Free Subscribers <em>(at Level #0)</em>, along with several Primary Roles for paid Membership <em>(i.e., Levels 1-4)</em>, created by the s2Member plugin.'.((!is_multisite() || !c_ws_plugin__s2member_utils_conds::is_multisite_farm() || is_main_site()) ? ' s2Member also supports unlimited Custom Capability Packages <em>(see <strong>s2Member → API Scripting → Custom Capabilities</strong>)</em>' : '').'. That being said, you don\'t have to use all of the Membership Levels if you don\'t want to. To use only 1 or 2 of these Levels, just design your Membership Options Page, so it only includes Payment Buttons for the Levels being used.</p>'."\n";
				echo (!is_multisite() || !c_ws_plugin__s2member_utils_conds::is_multisite_farm() || is_main_site()) ? '<p><em><strong>TIP:</strong> <strong>Unlimited Membership Levels</strong> are only possible with <a href="http://s2member.com/prices/" target="_blank" rel="external">s2Member Pro</a>. However, Custom Capabilities are possible in all versions of s2Member, including the free version. Custom Capabilities are a great way to extend s2Member in creative ways. If you\'re an advanced site owner, a theme designer, or a web developer integrating s2Member for a client, please check your Dashboard, under: <strong>s2Member → API Scripting → Custom Capabilities</strong>. We also recommend <a href="https://s2member.com/r/s2member-video-custom-capabilities-for-wordpress/" target="_blank" rel="external">this video tutorial</a>.</em></p>'."\n" : '';
				echo (!is_multisite() || !c_ws_plugin__s2member_utils_conds::is_multisite_farm() || is_main_site()) ? '<p><strong>See also:</strong> These KB articles: <a href="http://www.s2member.com/kb/roles-caps/" target="_blank" rel="external">s2Member Roles/Capabilities</a> and <a href="http://www.s2member.com/kb/simple-shortcode-conditionals/" target="_blank" rel="external">Simple Shortcode Conditionals</a>.</p>'."\n" : '';
				do_action("s2x_during_registration_options_page_during_left_sections_during_membership_levels", get_defined_vars());

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";

				for($n = 0; $n <= $GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["levels"]; $n++)
				{
					echo '<tr>'."\n";

					echo '<th>'."\n";
					echo '<label for="ws-plugin--s2member-level'.$n.'-label">'."\n";
					echo ($n === 0) ? 'Level #'.$n.' <em>(Free Subscribers)</em>:'."\n" : 'Level #'.$n.' Members:'."\n";
					echo '</label>'."\n";
					echo '</th>'."\n";

					echo '</tr>'."\n";
					echo '<tr>'."\n";

					echo '<td>'."\n";
					echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_level'.$n.'_label" id="ws-plugin--s2member-level'.$n.'-label" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["level".$n."_label"]).'" /><br />'."\n";
					echo 'This is the Label for Level #'.$n.(($n === 0) ? ' (Free Subscribers)' : ' Members').'.<br />'."\n";
					echo '</td>'."\n";

					echo '</tr>'."\n";
				}
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<table class="form-table" style="margin-top:0;">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th style="padding-top:0;">'."\n";
				echo '<label for="ws-plugin--s2member-apply-label-translations">'."\n";
				echo 'Force WordPress to use your Labels?'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="radio" name="ws_plugin__s2member_apply_label_translations" id="ws-plugin--s2member-apply-label-translations-0" value="0"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["apply_label_translations"]) ? ' checked="checked"' : '').' /> <label for="ws-plugin--s2member-apply-label-translations-0">No</label> &nbsp;&nbsp;&nbsp; <input type="radio" name="ws_plugin__s2member_apply_label_translations" id="ws-plugin--s2member-apply-label-translations-1" value="1"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["apply_label_translations"]) ? ' checked="checked"' : '').' /> <label for="ws-plugin--s2member-apply-label-translations-1">Yes, force WordPress to use my Labels.</label><br />'."\n";
				echo 'This affects your administrative Dashboard only <em>(i.e., your list of Users)</em>.<br />s2Member can force WordPress to use your Labels instead of referencing Roles by `s2Member Level #`. If this is your first installation of s2Member, we suggest leaving this set to <code>no</code> until you\'ve had a chance to get acclimated with s2Member\'s functionality. In fact, many site owners choose to leave this off, because they find it less confusing when Roles are referred to by their s2Member Level #.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<input type="button" value="Reset Roles/Capabilities" class="ws-menu-page-right ws-plugin--s2member-reset-roles-button" style="min-width:175px;" />'."\n";
				echo '<p>The button to the right, is a nifty tool, which allows you to reset s2Member\'s internal Roles and Capabilities that integrate with WordPress. If you, or a developer working with you, has made attempts to alter the default <em>internal</em> Role/Capability sets that come with s2Member, and you need to reset them back to the way s2Member expects them to be, please use this tool. <em>Attn Developers: it is also possible lock-in your modified Roles/Capabilities with an s2Member Filter. Please see <a href="http://s2member.com/kb-article/locking-s2member-rolescapabilities/" target="_blank" rel="external">this KB article for details</a>.</em></p>'."\n";

				echo '</div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_during_registration_options_page_during_left_sections_after_membership_levels", get_defined_vars());
			}
			if(apply_filters("s2x_during_registration_options_page_during_left_sections_display_login_registration", TRUE, get_defined_vars()))
			{
				do_action("s2x_during_registration_options_page_during_left_sections_before_login_registration", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="Login/Registration Design">'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-login-registration-section">'."\n";
				echo '<h3>Login/Registration Page Customization (optional)</h3>'."\n";
				echo '<p>These settings customize your Standard Login/Registration Pages:<br />(<a href="'.esc_attr(c_ws_plugin__s2member_utils_urls::wp_register_url()).'" target="_blank" rel="external" onclick="alert(\'s2Member will now open your Standard Registration Form.\\n* s2Member makes this form available to logged-in Administrators, at all times (for testing purposes), regardless of configuration.'.((c_ws_plugin__s2member_utils_conds::bp_is_installed()) ? '\\n\\nBuddyPress: * BuddyPress will use its own Registration Form. Please note, you will probably be redirected away from the BuddyPress Registration Form ( '.c_ws_plugin__s2member_utils_strings::esc_js_sq(c_ws_plugin__s2member_utils_urls::bp_register_url()).' ), because you\\\'re ALREADY logged-in. Please log out before testing BuddyPress registration.' : '').'\');">'.esc_html(c_ws_plugin__s2member_utils_urls::wp_register_url()).'</a>)</p>'."\n";
				echo (is_multisite() && c_ws_plugin__s2member_utils_conds::is_multisite_farm() && is_main_site()) ? '<p><em>The Main Site of a Multisite Blog Farm uses this Form instead, powered by your theme.<br />(<a href="'.esc_attr(c_ws_plugin__s2member_utils_urls::wp_signup_url()).'" target="_blank" rel="external" onclick="alert(\'s2Member will now open your Multisite Registration Form.\\n* s2Member makes this form available to logged-in Super Administrators, at all times (for testing purposes), regardless of configuration.'.((c_ws_plugin__s2member_utils_conds::bp_is_installed()) ? '\\n\\nBuddyPress: * BuddyPress will use its own Registration Form. Please note, you will probably be redirected away from the BuddyPress Registration Form ( '.c_ws_plugin__s2member_utils_strings::esc_js_sq(c_ws_plugin__s2member_utils_urls::bp_register_url()).' ), because you\\\'re ALREADY logged-in. Please log out before testing BuddyPress registration.' : '').'\');">'.esc_html(c_ws_plugin__s2member_utils_urls::wp_signup_url()).'</a>)</em></p>'."\n" : '';
				echo (c_ws_plugin__s2member_utils_conds::bp_is_installed()) ? '<p><em><strong>BuddyPress:</strong> BuddyPress will use its own Registration Form, powered by your theme.<br />(<a href="'.esc_attr(c_ws_plugin__s2member_utils_urls::bp_register_url()).'" target="_blank" rel="external" onclick="alert(\'s2Member will now open your BuddyPress Registration Form.\\n* However, you will probably be redirected away from this BuddyPress Registration Form ( '.c_ws_plugin__s2member_utils_strings::esc_js_sq(c_ws_plugin__s2member_utils_urls::bp_register_url()).' ), because you\\\'re ALREADY logged-in. Please log out before testing BuddyPress registration.\');">'.esc_html(c_ws_plugin__s2member_utils_urls::bp_register_url()).'</a>)</em></p>'."\n" : '';
				do_action("s2x_during_registration_options_page_during_left_sections_during_login_registration", get_defined_vars());

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<h3 style="margin:0;">Enable This Functionality?</h3>'."\n";
				echo '<select name="ws_plugin__s2member_login_reg_design_enabled" id="ws-plugin--s2member-login-reg-design-enabled">'."\n";
				echo '<option value="0"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_design_enabled"]) ? ' selected="selected"' : '').'>No (default, use WordPress defaults)</option>'."\n";
				echo '<option value="1"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_design_enabled"]) ? ' selected="selected"' : '').'>Yes (customize Login/Registration with s2Member)</option>'."\n";
				echo '</select>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div id="ws-plugin--s2member-login-reg-design"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_design_enabled"]) ? ' style="display:none;"' : '').'>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<h3 style="margin:0;">Overall Font/Size Configuration</h3>'."\n";
				echo '<p style="margin:0;">These settings are all focused on your Login/Registration Fonts.</p>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-login-reg-font-size">'."\n";
				echo 'Overall Font Size:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_login_reg_font_size" id="ws-plugin--s2member-login-reg-font-size" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_font_size"]).'" /><br />'."\n";
				echo 'Set this to a numeric value, calculated in pixels.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-login-reg-font-family">'."\n";
				echo 'Overall Font Family:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_login_reg_font_family" id="ws-plugin--s2member-login-reg-font-family" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_font_family"]).'" /><br />'."\n";
				echo 'Set this to a web-safe font family.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-login-reg-font-field-size">'."\n";
				echo 'Form Field Font Size:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_login_reg_font_field_size" id="ws-plugin--s2member-login-reg-font-field-size" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_font_field_size"]).'" /><br />'."\n";
				echo 'Set this to a numeric value, calculated in pixels.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<table class="form-table" style="margin-top:0;">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<h3 style="margin:0;">Background Configuration</h3>'."\n";
				echo '<p style="margin:0;">These settings are all focused on your Login/Registration Background.</p>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-login-reg-background-color">'."\n";
				echo 'Background Color:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_login_reg_background_color" id="ws-plugin--s2member-login-reg-background-color" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_background_color"]).'" /><br />'."\n";
				echo 'Set this to a 6-digit hex color code.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-login-reg-background-image">'."\n";
				echo 'Background Image:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_login_reg_background_image" id="ws-plugin--s2member-login-reg-background-image" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_background_image"]).'" /><br />'."\n";
				echo '<input type="button" id="ws-plugin--s2member-login-reg-background-image-media-btn" value="Open Media Library" class="ws-menu-page-media-btn" rel="ws-plugin--s2member-login-reg-background-image" />'."\n";
				echo 'Set this to the URL of your Background Image. (this is optional)<br />';
				echo 'If supplied, your Background Image will be tiled.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-login-reg-background-image-repeat">'."\n";
				echo 'Background Image Tile:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<select name="ws_plugin__s2member_login_reg_background_image_repeat" id="ws-plugin--s2member-login-reg-background-image-repeat">'."\n";
				echo '<option value="repeat"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_background_image_repeat"] === "repeat") ? ' selected="selected"' : '').'>Seamless Tile ( background-repeat: repeat; )</option>'."\n";
				echo '<option value="repeat-x"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_background_image_repeat"] === "repeat-x") ? ' selected="selected"' : '').'>Tile Horizontally ( background-repeat: repeat-x; )</option>'."\n";
				echo '<option value="repeat-y"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_background_image_repeat"] === "repeat-y") ? ' selected="selected"' : '').'>Tile Vertically ( background-repeat: repeat-y; )</option>'."\n";
				echo '<option value="no-repeat"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_background_image_repeat"] === "no-repeat") ? ' selected="selected"' : '').'>No Tiles ( background-repeat: no-repeat; )</option>'."\n";
				echo '</select><br />'."\n";
				echo 'This controls the way your Background Image is styled with CSS. [ <a href="http://s2member.com/r/css-background-repeat/" target="_blank" rel="external">learn more</a> ]'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-login-reg-background-text-color">'."\n";
				echo 'Color of Text on top of your Background:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_login_reg_background_text_color" id="ws-plugin--s2member-login-reg-background-text-color" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_background_text_color"]).'" /><br />'."\n";
				echo 'Set this to a 6-digit hex color code.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-login-reg-background-text-shadow-color">'."\n";
				echo 'Shadow Color for Text on top of your Background:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_login_reg_background_text_shadow_color" id="ws-plugin--s2member-login-reg-background-text-shadow-color" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_background_text_shadow_color"]).'" /><br />'."\n";
				echo 'Set this to a 6-digit hex color code.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-login-reg-background-box-shadow-color">'."\n";
				echo 'Shadow Color for Boxes on top of your Background:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_login_reg_background_box_shadow_color" id="ws-plugin--s2member-login-reg-background-box-shadow-color" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_background_box_shadow_color"]).'" /><br />'."\n";
				echo 'Set this to a 6-digit hex color code.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<table class="form-table" style="margin-top:0;">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<h3 style="margin:0;">Logo Image Configuration</h3>'."\n";
				echo '<p style="margin:0;">These settings are all focused on your Login/Registration Logo.</p>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-login-reg-logo-src">'."\n";
				echo 'Logo Image Location:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_login_reg_logo_src" id="ws-plugin--s2member-login-reg-logo-src" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_logo_src"]).'" /><br />'."\n";
				echo '<input type="button" id="ws-plugin--s2member-login-reg-logo-src-media-btn" value="Open Media Library" class="ws-menu-page-media-btn" rel="ws-plugin--s2member-login-reg-logo-src" />'."\n";
				echo 'Set this to the URL of your Logo Image.<br />'."\n";
				echo 'Suggested size is around 500 x 100.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-login-reg-logo-src-width">'."\n";
				echo 'Logo Image Width:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_login_reg_logo_src_width" id="ws-plugin--s2member-login-reg-logo-src-width" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_logo_src_width"]).'" /><br />'."\n";
				echo 'The pixel Width of your Logo Image. <em>* This ALSO affects the overall width of your Login/Registration forms. If you want wider form fields, use a wider Logo.</em>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-login-reg-logo-src-height">'."\n";
				echo 'Logo Image Height:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_login_reg_logo_src_height" id="ws-plugin--s2member-login-reg-logo-src-height" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_logo_src_height"]).'" /><br />'."\n";
				echo 'The pixel Height of your Logo Image.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-login-reg-logo-url">'."\n";
				echo 'Logo Image Click URL:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_login_reg_logo_url" id="ws-plugin--s2member-login-reg-logo-url" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_logo_url"]).'" /><br />'."\n";
				echo 'Set this to the Click URL for your Logo Image.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-login-reg-logo-title">'."\n";
				echo 'Logo Image Title Attribute:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_login_reg_logo_title" id="ws-plugin--s2member-login-reg-logo-title" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_logo_title"]).'" /><br />'."\n";
				echo 'Used as the <code>title=""</code> attribute for your Logo Image.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<table class="form-table" style="margin-top:0;">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-login-reg-footer-backtoblog">'."\n";
				echo 'Display [Back to Home Page] Link At Bottom?'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<select name="ws_plugin__s2member_login_reg_footer_backtoblog" id="ws-plugin--s2member-login-reg-footer-backtoblog">'."\n";
				echo '<option value="1"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_footer_backtoblog"]) ? ' selected="selected"' : '').'>Yes, display link at bottom pointing visitors back to the home page</option>'."\n";
				echo '<option value="0"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_footer_backtoblog"]) ? ' selected="selected"' : '').'>No, hide this link (I\'ll create my own custom footer w/ the details I prefer)</option>'."\n";
				echo '</select>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<h3 style="margin:0;">Footer Design (i.e., Bottom)</h3>'."\n";
				echo '<p style="margin:0;">This field accepts raw HTML'.((!is_multisite() || !c_ws_plugin__s2member_utils_conds::is_multisite_farm() || is_main_site()) ? ' (and/or PHP)' : '').' code.</p>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-login-reg-footer-design">'."\n";
				echo 'Login/Registration Footer Design (optional):'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<textarea name="ws_plugin__s2member_login_reg_footer_design" id="ws-plugin--s2member-login-reg-footer-design" rows="3" wrap="off" spellcheck="false">'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_reg_footer_design"]).'</textarea><br />'."\n";
				echo 'This optional HTML'.((!is_multisite() || !c_ws_plugin__s2member_utils_conds::is_multisite_farm() || is_main_site()) ? ' (and/or PHP)' : '').' code will appear at the very bottom of your Login/Registration Forms.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";
				echo '</div>'."\n";

				echo '</div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_during_registration_options_page_during_left_sections_after_login_registration", get_defined_vars());
			}
			if(apply_filters("s2x_during_registration_options_page_during_left_sections_display_custom_reg_fields", TRUE, get_defined_vars()))
			{
				do_action("s2x_during_registration_options_page_during_left_sections_before_custom_reg_fields", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="Registration/Profile Fields &amp; Options">'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-custom-reg-fields-section">'."\n";
				echo '<h3>Custom Registration/Profile Fields (optional, for further customization)</h3>'."\n";
				echo '<p>Some fields are already built-in by default. The defaults are: <code>*Username*, *Email*, *First Name*, *Last Name*</code>.</p>'."\n";

				echo '<p>Custom Fields will appear in your Standard Registration Form, and in User/Member Profiles:<br />(<a href="'.esc_attr(c_ws_plugin__s2member_utils_urls::wp_register_url()).'" target="_blank" rel="external" onclick="alert(\'s2Member will now open your Standard Registration Form.\\n* s2Member makes this form available to logged-in Administrators, at all times (for testing purposes), regardless of configuration.'.((c_ws_plugin__s2member_utils_conds::bp_is_installed()) ? '\\n\\nBuddyPress: * BuddyPress will use its own Registration Form. Please note, you will probably be redirected away from the BuddyPress Registration Form ( '.c_ws_plugin__s2member_utils_strings::esc_js_sq(c_ws_plugin__s2member_utils_urls::bp_register_url()).' ), because you\\\'re ALREADY logged-in. Please log out before testing BuddyPress registration.' : '').'\');">'.esc_html(c_ws_plugin__s2member_utils_urls::wp_register_url()).'</a>)</p>'."\n";
				echo (is_multisite() && c_ws_plugin__s2member_utils_conds::is_multisite_farm() && is_main_site()) ? '<p><em>The Main Site of a Multisite Blog Farm uses this Form. s2Member supports Custom Fields here too.<br />(<a href="'.esc_attr(c_ws_plugin__s2member_utils_urls::wp_signup_url()).'" target="_blank" rel="external" onclick="alert(\'s2Member will now open your Multisite Registration Form.\\n* s2Member makes this form available to logged-in Super Administrators, at all times (for testing purposes), regardless of configuration.'.((c_ws_plugin__s2member_utils_conds::bp_is_installed()) ? '\\n\\nBuddyPress: * BuddyPress will use its own Registration Form. Please note, you will probably be redirected away from the BuddyPress Registration Form ( '.c_ws_plugin__s2member_utils_strings::esc_js_sq(c_ws_plugin__s2member_utils_urls::bp_register_url()).' ), because you\\\'re ALREADY logged-in. Please log out before testing BuddyPress registration.' : '').'\');">'.esc_html(c_ws_plugin__s2member_utils_urls::wp_signup_url()).'</a>)</em></p>'."\n" : '';
				echo (c_ws_plugin__s2member_utils_conds::bp_is_installed()) ? '<p><em><strong>BuddyPress:</strong> BuddyPress will use its own Registration Form <a href="'.esc_attr(c_ws_plugin__s2member_utils_urls::bp_register_url()).'" target="_blank" rel="external" onclick="alert(\'s2Member will now open your BuddyPress Registration Form.\\n* However, you will probably be redirected away from this BuddyPress Registration Form ( '.c_ws_plugin__s2member_utils_strings::esc_js_sq(c_ws_plugin__s2member_utils_urls::bp_register_url()).' ), because you\\\'re ALREADY logged-in. Please log out before testing BuddyPress registration.\');">here</a>.<br />s2Member can integrate your Custom Fields with BuddyPress too, please see options below.</em></p>'."\n" : '';
				echo '<p><strong>Regarding Registration:</strong> Custom Fields do not appear during repeat registration and/or checkout attempts (i.e., they do not appear for any user that is currently logged into the site). Please make sure that you test registration and/or checkout forms while not logged in (i.e., please test as a first-time customer). Existing users, members, customers may update Custom Fields by editing their Profile.</p>'."\n";

				do_action("s2x_during_registration_options_page_during_left_sections_during_custom_reg_fields", get_defined_vars());

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label>'."\n";
				echo 'Custom Registration/Profile Fields:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="hidden" name="ws_plugin__s2member_custom_reg_fields" id="ws-plugin--s2member-custom-reg-fields" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_fields"]).'" />'."\n";
				echo '<div id="ws-plugin--s2member-custom-reg-field-configuration"></div>'."\n"; // This is filled by JavaScript routines.
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-custom-reg-names">'."\n";
				echo 'Collect First/Last Names During Registration?'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<select name="ws_plugin__s2member_custom_reg_names" id="ws-plugin--s2member-custom-reg-names">'."\n";
				echo '<option value="1"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_names"]) ? ' selected="selected"' : '').'>Yes (always collect First/Last Names during registration)</option>'."\n";
				echo '<option value="0"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_names"]) ? ' selected="selected"' : '').'>No (do NOT collect First/Last Names during registration)</option>'."\n";
				echo '</select><br />'."\n";
				echo 'Recommended setting (<code>Yes</code>). It\'s usually a good idea to leave this on.'."\n";
				echo (c_ws_plugin__s2member_utils_conds::bp_is_installed()) ? '<br /><em>* Has no affect on BuddyPress registration form (BuddyPress always collects a full <code>Name</code> field).</em>'."\n" : '';
				echo (c_ws_plugin__s2member_utils_conds::pro_is_installed()) ? '<br /><em>* s2Member Pro (Checkout) Forms always require a First/Last Name for billing.</em>'."\n" : '';
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-custom-reg-display-name">'."\n";
				echo 'Set "Display Name" During Registration?'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<select name="ws_plugin__s2member_custom_reg_display_name" id="ws-plugin--s2member-custom-reg-display-name">'."\n";
				echo '<option value="full"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_display_name"] === "full") ? ' selected="selected"' : '').'>Yes (set Display Name to User\'s Full Name)</option>'."\n";
				echo '<option value="first"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_display_name"] === "first") ? ' selected="selected"' : '').'>Yes (set Display Name to User\'s First Name)</option>'."\n";
				echo '<option value="last"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_display_name"] === "last") ? ' selected="selected"' : '').'>Yes (set Display Name to User\'s Last Name)</option>'."\n";
				echo '<option value="login"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_display_name"] === "login") ? ' selected="selected"' : '').'>Yes (set Display Name to User\'s Username)</option>'."\n";
				echo '<option value="0"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_display_name"]) ? ' selected="selected"' : '').'>No (leave Display Name at default WordPress value)</option>'."\n";
				echo '</select>'."\n";
				echo (c_ws_plugin__s2member_utils_conds::bp_is_installed()) ? '<br /><em>* Has no affect on BuddyPress registration form (BuddyPress always uses its full <code>Name</code> field).</em>'."\n" : '';
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-custom-reg-password">'."\n";
				echo 'Allow Custom Passwords During Registration?'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<p style="margin-bottom:4px;"><em><strong>Note:</strong> Custom Passwords are easier for users. However, this has an impact on site security. Enabling Custom Passwords disables the New User Notification email that contains a password-setup link for each user. In other words, enabling Custom Passwords effectively disables any sort of email verification procedure, because the user is allowed to set their password during registration, instead of doing that via email confirmation—which is the default WordPress behavior.</em></p>'."\n";
				echo (is_multisite() && c_ws_plugin__s2member_utils_conds::is_multisite_farm() && is_main_site()) ? '<p style="margin-bottom:4px;"><em>* For security purposes, Custom Passwords are not possible on the Main Site of a Blog Farm. <a href="#" onclick="alert(\'For security purposes, Custom Passwords are not possible on the Main Site of a Blog Farm. A User must wait for the activation/confirmation email; where a randomly generated Password will be assigned. Please note, this limitation only affects your Main Site, via `/wp-signup.php`. In other words, your Customers (i.e., other Blog Owners) will still have the ability to allow Custom Passwords with s2Member. YOU are affected by this limitation, NOT them.\\n\\n* NOTE: s2Member (Pro) removes this limitation.\\nIf you install the s2Member Pro Add-on, you WILL be able to allow Custom Passwords through s2Member Pro-Forms; even on a Multisite Blog Farm.\'); return false;" tabindex="-1">[?]</a></em></p>'."\n" : '';
				echo (c_ws_plugin__s2member_utils_conds::bp_is_installed()) ? '<p style="margin-bottom:4px;"><em>* Does not affect BuddyPress registration form (always <code>yes</code> with BuddyPress registration).</em></p>'."\n" : '';
				echo '<select name="ws_plugin__s2member_custom_reg_password" id="ws-plugin--s2member-custom-reg-password"'.((is_multisite() && c_ws_plugin__s2member_utils_conds::is_multisite_farm() && is_main_site() && !c_ws_plugin__s2member_utils_conds::pro_is_installed()) ? ' disabled="disabled"' : '').'>'."\n";
				echo '<option value="0"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_password"]) ? ' selected="selected"' : '').'>No (send each user a password-setup link after registration; recommended for best security)</option>'."\n";
				echo '<option value="1"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_password"]) ? ' selected="selected"' : '').'>Yes (allow members to create their own password during registration)</option>'."\n";
				echo '</select>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-custom-reg-password-min-length">'."\n";
				echo 'Minimum Length/Strength for Custom Passwords:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo 'Minimum length: <input type="text" autocomplete="off" name="ws_plugin__s2member_custom_reg_password_min_length" id="ws-plugin--s2member-custom-reg-password-min-length" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_password_min_length"]).'" maxlength="2" size="2" style="width:auto;" /> characters.'."\n";
				echo '&nbsp;&nbsp;&nbsp; Minimum strength: <select name="ws_plugin__s2member_custom_reg_password_min_strength" id="ws-plugin--s2member-custom-reg-password-min-strength" style="width:auto;">'."\n";
				echo '<option value="n/a"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_password_min_strength"] === 'n/a') ? ' selected="selected"' : '').'>N/A (do not enforce a password strength requirement)</option>'."\n";
				echo '<option value="weak"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_password_min_strength"] === 'weak') ? ' selected="selected"' : '').'>Weak (only needs to meet minimum length requirement)</option>'."\n";
				echo '<option value="good"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_password_min_strength"] === 'good') ? ' selected="selected"' : '').'>Good (must have numbers, letters, and mixed caSe)</option>'."\n";
				echo '<option value="strong"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_password_min_strength"] === 'strong') ? ' selected="selected"' : '').'>Strong (must have numbers, letters, mixed caSe, and punctuation)</option>'."\n";
				echo '</select>'."\n";
				echo '<p><em><strong>Tip:</strong> Minimum length and password strength also impact profile updates, so it\'s a good idea to configure these even if you\'re not using Custom Passwords during registration.</em></p>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-custom-reg-force-personal-emails">'."\n";
				echo 'Force Personal Emails During Registration?'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_custom_reg_force_personal_emails" id="ws-plugin--s2member-custom-reg-force-personal-emails" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_force_personal_emails"]).'" /><br />'."\n";
				echo 'To force personal email addresses, provide a comma-delimited list of email users to reject. <a href="#" onclick="alert(\'s2Member will reject [user]@ (based on your configuration here). A JavaScript alert message will be issued, asking the User to, `please use a personal email address`.\'); return false;" tabindex="-1">[?]</a><br />'."\n";
				echo 'Ex: <code>info,help,admin,webmaster,hostmaster,sales,support,spam</code><br />'."\n";
				echo 'See: <a href="http://s2member.com/r/mailchimp-role-based-emails/" target="_blank" rel="external">this article</a> for a more complete list.'."\n";
				echo (c_ws_plugin__s2member_utils_conds::bp_is_installed()) ? '<br /><em>* Affects BuddyPress registration form too.</em>'."\n" : '';
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-custom-reg-fields-4bp">'."\n";
				echo 'Integrate Custom Registration/Profile Fields with BuddyPress?'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<div class="ws-menu-page-scrollbox" style="height:65px;">'."\n";
				echo '<input type="hidden" name="ws_plugin__s2member_custom_reg_fields_4bp[]" value="update-signal"'.((!c_ws_plugin__s2member_utils_conds::bp_is_installed()) ? ' disabled="disabled"' : '').' />'."\n";
				foreach(array("profile-view" => "Yes, integrate with BuddyPress Public Profiles.", "registration" => "Yes, integrate with BuddyPress Registration Form.", "profile" => "Yes, integrate with BuddyPress Profile Editing Panel.") as $ws_plugin__s2member_temp_s_value => $ws_plugin__s2member_temp_s_label)
					echo '<input type="checkbox" name="ws_plugin__s2member_custom_reg_fields_4bp[]" id="ws-plugin--s2member-custom-reg-fields-4bp-'.esc_attr(preg_replace("/[^a-z0-9_\-]/", "-", $ws_plugin__s2member_temp_s_value)).'" value="'.esc_attr($ws_plugin__s2member_temp_s_value).'"'.((in_array($ws_plugin__s2member_temp_s_value, $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_fields_4bp"])) ? ' checked="checked"' : '').((!c_ws_plugin__s2member_utils_conds::bp_is_installed()) ? ' disabled="disabled"' : '').' /> <label for="ws-plugin--s2member-custom-reg-fields-4bp-'.esc_attr(preg_replace("/[^a-z0-9_\-]/", "-", $ws_plugin__s2member_temp_s_value)).'">'.$ws_plugin__s2member_temp_s_label.'</label><br />'."\n";
				echo '</div>'."\n";
				echo (!c_ws_plugin__s2member_utils_conds::bp_is_installed()) ? '<em>* BuddyPress is not installed; which is perfectly OK. BuddyPress is not a requirement.</em>'."\n" : '<em>* The options above, make it possible to integrate Custom Registration/Profile Fields (i.e., those configured with s2Member) into BuddyPress as well. However, if you configure Profile Fields with BuddyPress, those will NOT be integrated with s2Member. Therefore, if you need Custom Registration/Profile Fields to work with both s2Member and with BuddyPress, please configure them with s2Member.</em>';
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '</div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_during_registration_options_page_during_left_sections_after_custom_reg_fields", get_defined_vars());
			}
			if(apply_filters("s2x_during_registration_options_page_during_left_sections_display_profile_modifications", TRUE, get_defined_vars()))
			{
				do_action("s2x_during_registration_options_page_during_left_sections_before_profile_modifications", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="Member Profile Modifications">'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-profile-modifications-section">'."\n";
				echo '<h3>Giving Members The Ability To Modify Their Profile</h3>'."\n";
				echo '<p>s2Member can be configured to redirect Members away from the <a href="'.esc_attr(admin_url("/profile.php")).'" target="_blank" rel="external">default Profile Editing Panel</a> that is built into WordPress. When/if a Member attempts to access the default Profile Editing Panel, they\'ll instead be redirected to the Login Welcome Page that you\'ve configured with s2Member. <strong>Why would I redirect away from the default Profile Editing Panel?</strong> Unless you\'ve made some drastic modifications to your WordPress installation, the default Profile Editing Panel that comes with WordPress is <em>not</em> suited for any sort of public access.</p>'."\n";
				echo '<p>So instead of using this default Profile Editing Panel, s2Member provides you <em>(the site owner)</em> with a special Shortcode: <code>[s2Member-Profile /]</code>. You can insert this into your Login Welcome Page, or any Post/Page for that matter <em>(even into a Text Widget)</em>. This Shortcode produces an Inline Profile Editing Form that supports all aspects of s2Member, including Password changes; and any Custom Registration/Profile Fields that you\'ve configured with s2Member. Alternatively, you can send your Members to a <a href="'.esc_attr(home_url("/?s2member_profile=1")).'" target="_blank" rel="external">special Stand-Alone version</a>. The stand-alone version makes it possible for you to <a href="#" onclick="if(!window.open(\''.home_url("/?s2member_profile=1").'\', \'_popup\', \'width=600,height=400,left=100,screenX=100,top=100,screenY=100,location=0,menubar=0,toolbar=0,status=0,scrollbars=1,resizable=1\')) alert(\'Please disable popup blockers and try again!\'); return false;" rel="external">open it up in a popup window</a>, or embed it into your Login Welcome Page using an IFRAME. Code samples below.</p>'."\n";
				echo (c_ws_plugin__s2member_utils_conds::bp_is_installed()) ? '<p><em><strong>BuddyPress:</strong> BuddyPress already provides Users/Members with a Profile Editing Panel, powered by your theme. If you\'ve configured Custom Registration/Profile Fields with s2Member, you can also enable s2Member\'s Profile Field integration with BuddyPress (recommended). For further details, see: <strong>s2Member → General Options → Registration/Profile Fields</strong>.</em></p>'."\n" : '';
				do_action("s2x_during_registration_options_page_during_left_sections_during_profile_modifications", get_defined_vars());

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-force-admin-lockouts">'."\n";
				echo 'Redirect Members away from the Default Profile Panel?'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<select name="ws_plugin__s2member_force_admin_lockouts" id="ws-plugin--s2member-force-admin-lockouts">'."\n";
				echo '<option value="0"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["force_admin_lockouts"]) ? ' selected="selected"' : '').'>No (I want to use the WordPress default methodologies)</option>'."\n";
				echo '<option value="1"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["force_admin_lockouts"]) ? ' selected="selected"' : '').'>Yes (redirect to Login Welcome Page; locking all /wp-admin/ areas)</option>'."\n";
				echo '</select><br />'."\n";
				echo 'Recommended setting (<code>Yes</code>). <em><strong>Note:</strong> When this is set to (<code>Yes</code>), s2Member will take an initiative to further safeguard ALL <code>/wp-admin/</code> areas of your installation; not just the Default Profile Panel.</em>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<p style="margin-bottom:0;"><strong>Shortcode (copy/paste)</strong>, for an Inline Profile Modification Form:<br />'."\n";
				echo '<p style="margin-top:0;"><input type="text" autocomplete="off" value="'.format_to_edit('[s2Member-Profile /]').'" onclick="this.select ();" /></p>'."\n";

				echo '<p style="margin-top:25px; margin-bottom:0;"><strong>Stand-Alone (copy/paste)</strong>, for popup window:</p>'."\n";
				echo '<p style="margin-top:0;"><input type="text" autocomplete="off" value="'.format_to_edit(preg_replace("/\<\?php echo S2MEMBER_CURRENT_USER_PROFILE_MODIFICATION_PAGE_URL; \?\>/", c_ws_plugin__s2member_utils_strings::esc_refs(home_url("/?s2member_profile=1")), file_get_contents(dirname(__FILE__)."/code-samples/current-user-profile-modification-page-url-2-ops.x-php"))).'" onclick="this.select ();" /></p>'."\n";
				echo '</div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_during_registration_options_page_during_left_sections_after_profile_modifications", get_defined_vars());
			}

			do_action("s2x_during_registration_options_page_after_left_sections", get_defined_vars());

			echo '<p class="submit"><input type="submit" value="Save All Changes" /></p>'."\n";

			echo '</form>'."\n";

			echo '</td>'."\n";

			echo '<td class="ws-menu-page-table-r">'."\n";
			c_ws_plugin__s2member_menu_pages_rs::display();
			echo '</td>'."\n";

			echo '</tr>'."\n";
			echo '</tbody>'."\n";
			echo '</table>'."\n";

			echo '</div>'."\n";
		}
	}
}

new c_ws_plugin__s2member_menu_page_registration_options();
