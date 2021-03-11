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

if(!class_exists("c_ws_plugin__s2member_menu_page_email_options"))
{
	/**
	 * Menu page for the s2Member plugin (General Options page).
	 *
	 * @package s2Member\Menu_Pages
	 * @since 210208
	 */
	class c_ws_plugin__s2member_menu_page_email_options {
		public function __construct() {
			echo '<div class="wrap ws-menu-page">'."\n";

			echo '<div class="wp-header-end"></div>'."\n";

			echo '<div class="ws-menu-page-toolbox">'."\n";
			c_ws_plugin__s2member_menu_pages_tb::display();
			echo '</div>'."\n";

			echo '<h2>Email Options</h2>'."\n";

			echo '<table class="ws-menu-page-table">'."\n";
			echo '<tbody class="ws-menu-page-table-tbody">'."\n";
			echo '<tr class="ws-menu-page-table-tr">'."\n";
			echo '<td class="ws-menu-page-table-l">'."\n";

			echo '<form method="post" name="ws_plugin__s2member_options_form" id="ws-plugin--s2member-options-form" autocomplete="off">'."\n";
			echo '<input type="hidden" name="ws_plugin__s2member_options_save" id="ws-plugin--s2member-options-save" value="'.esc_attr(wp_create_nonce("ws-plugin--s2member-options-save")).'" />'."\n";
			echo '<input type="hidden" name="ws_plugin__s2member_configured" id="ws-plugin--s2member-configured" value="1" />'."\n";

			do_action("s2x_during_email_options_page_before_left_sections", get_defined_vars());

			if(apply_filters("s2x_during_email_options_page_during_left_sections_display_email_config", TRUE, get_defined_vars()))
			{
				do_action("s2x_during_registration_options_page_during_left_sections_before_email_config", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="Email Configuration">'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-email-section">'."\n";
				echo '<h3 style="margin:0;">Email From: '.esc_html('"Name" <address>').'</h3>'."\n";
				echo '<p style="margin:0;">This is the name/address that will appear in outgoing email notifications sent by the s2Member plugin.</p>'."\n";
				do_action("s2x_during_registration_options_page_during_left_sections_during_email_from_name_config", get_defined_vars());

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-reg-email-from-name">'."\n";
				echo 'Email From Name:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_reg_email_from_name" id="ws-plugin--s2member-reg-email-from-name" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["reg_email_from_name"]).'" /><br />'."\n";
				echo 'We recommend that you use the name of your site here.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-reg-email-from-email">'."\n";
				echo 'Email From Address:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_reg_email_from_email" id="ws-plugin--s2member-reg-email-from-email" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["reg_email_from_email"]).'" /><br />'."\n";
				echo 'Example: support@your-domain.com. <em>Please read <a href="#" onclick="alert(\'Running WordPress with an SMTP mail plugin?\\n\\nPlease be advised. If you run an SMTP mail plugin with WordPress, be sure to configure s2Member with a valid `From:` address (i.e., one matching your SMTP configuration perhaps). Most free SMTP servers, such as Gmail and Yahoo, require that your `From:` header match the email address associated with your account. Please check with your SMTP service provider before attempting to configure plugins like s2Member to use a different `From:` address when sending email messages.\'); return false;">this important note</a></em>.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-reg-email-support-link">'."\n";
				echo 'Email Support/Contact Link:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_reg_email_support_link" id="ws-plugin--s2member-reg-email-support-link" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["reg_email_support_link"]).'" /><br />'."\n";
				echo 'Ex: <code>mailto:support@your-domain.com</code> (<em>mailto link</em>)<br />'."\n";
				echo 'Or: <code>'.esc_html(home_url("/contact-us/")).'</code>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<h3 style="margin:0;">New User Email Configuration</h3>'."\n";
				echo '<input type="hidden" id="ws-plugin--s2member-pluggables-wp-new-user-notification" value="'.esc_attr((empty($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["pluggables"]["wp_new_user_notification"])) ? '0' : '1').'" />'."\n";
				echo (empty($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["pluggables"]["wp_new_user_notification"])) ? '<p class="ws-menu-page-error" style="margin:0;"><em><strong>Conflict warning:</strong> You have another theme or plugin installed that is preventing s2Member from controlling this aspect of your installation. When the pluggable function <code><a href="http://codex.wordpress.org/Function_Reference/wp_new_user_notification" target="_blank" rel="external">wp_new_user_notification()</a></code> is handled by another plugin, it\'s not possible for s2Member to allow customization of New User Emails. This is NOT a major issue. In fact, in some cases, it might be desirable. That being said, if you DO want to use s2Member\'s customization of New User Emails, you will need to deactivate one plugin at a time until this conflict warning goes away.</em></p>'."\n" : '';
				do_action("s2x_during_registration_options_page_during_left_sections_during_new_user_emails", get_defined_vars());

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<select name="ws_plugin__s2member_new_user_emails_enabled" id="ws-plugin--s2member-new-user-emails-enabled">'."\n";
				echo '<option value="0"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["new_user_emails_enabled"]) ? ' selected="selected"' : '').'>No (default, use WordPress defaults)</option>'."\n";
				echo '<option value="1"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["new_user_emails_enabled"]) ? ' selected="selected"' : '').'>Yes (customize New User Emails with s2Member)</option>'."\n";
				echo '</select>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div id="ws-plugin--s2member-new-user-emails">'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<h3 style="margin:0;">New User Email Message (<a href="#" onclick="jQuery(\'div#ws-plugin--s2member-new-user-email-details\').toggle(); return false;" class="ws-dotted-link">click to customize</a>)</h3>'."\n";
				echo '<p style="margin:0;">This email is sent to new Users/Members who did <em>not</em> set a Custom Password during registration.</p>'."\n";
				if($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_password"])
					echo '<p class="info" style="font-size:80%; margin:.5em 0 0 0;"><strong>↑ NOTE:</strong> You currently have Custom Passwords enabled in your s2Member Registration/Profile Field options. Therefore, this email is not going to be sent; i.e., it is only sent to users who need it for the purpose of obtaining their password.</p>'."\n";
				do_action("s2x_during_registration_options_page_during_left_sections_during_new_user_email", get_defined_vars());

				echo '<div id="ws-plugin--s2member-new-user-email-details" style="display:none;">'."\n";
				echo c_ws_plugin__s2member_utils_conds::bp_is_installed() ? '<p><em><strong>BuddyPress:</strong> please note that BuddyPress does NOT send this email to Users that register through the BuddyPress registration system. This is because BuddyPress sends each User an activation link; eliminating the need for this email altogether. However, you CAN still customize s2Member\'s separate email to paying Members. See: <strong>s2Member → PayPal Options → Signup Confirmation Email</strong>.</em></p>'."\n" : '';
				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-new-user-email-subject">'."\n";
				echo 'New User Email Subject:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_new_user_email_subject" id="ws-plugin--s2member-new-user-email-subject" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["new_user_email_subject"]).'" /><br />'."\n";
				echo 'Subject Line used in the email sent to new Users/Members.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-new-user-email-message">'."\n";
				echo 'New User Email Message:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<p><em>(The purpose of this email is to send the user a password-setup link; i.e., <code>%%wp_set_pass_url%%</code>)</em></small><p>'."\n";
				echo '<textarea name="ws_plugin__s2member_new_user_email_message" id="ws-plugin--s2member-new-user-email-message" rows="10">'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["new_user_email_message"]).'</textarea><br />'."\n";
				echo 'Message Body used in the email sent to new Users/Members.<br /><br />'."\n";
				echo '<strong>You can also use these special Replacement Codes if you need them:</strong>'."\n";
				echo '<ul>'."\n";
				echo '<li style="margin-bottom:1em;"><code>%%wp_set_pass_url%%</code> = The full URL where Users can set their password for the first time. Note that <code>%%wp_set_pass_url%%</code> should be used instead of the older/deprecated <code style="text-decoration:line-through;">%%user_pass%%</code> Replacement Code. Starting w/ WordPress v4.3+, a user can visit this link (i.e., <code>%%wp_set_pass_url%%</code>) to set a password of their own, and this is accomplished without sending a plain-text password via email; which improves security. It is suggested that you prefix this as follows: <em>To set your password, visit: <code>%%wp_set_pass_url%%</code></em></li>'."\n";
				echo '<li><code>%%role%%</code> = The Role ID <code>(subscriber, s2member_level[0-9]+, administrator, editor, author, contributor)</code>.</li>'."\n";
				echo '<li><code>%%label%%</code> = The Role ID Label <code>(Subscriber, s2Member Level 1, s2Member Level 2; or your own custom Labels—if configured)</code>.</li>'."\n";
				echo '<li><code>%%level%%</code> = The Level number <code>(0, 1, 2, 3, 4)</code>. (<em>deprecated, no longer recommended; use <code>%%role%%</code></em>)</li>'."\n";
				echo '<li><code>%%ccaps%%</code> = Custom Capabilities. Ex: <code>music,videos,free_gift</code> (<em>in comma-delimited format</em>).</li>'."\n";
				echo '<li><code>%%user_first_name%%</code> = The First Name of the Member who registered their Username.</li>'."\n";
				echo '<li><code>%%user_last_name%%</code> = The Last Name of the Member who registered their Username.</li>'."\n";
				echo '<li><code>%%user_full_name%%</code> = The Full Name (First &amp; Last) of the Member who registered their Username.</li>'."\n";
				echo '<li><code>%%user_email%%</code> = The Email Address of the Member who registered their Username.</li>'."\n";
				echo '<li><code>%%user_login%%</code> = The Username the Member selected during registration.</li>'."\n";
				echo '<li><code>%%user_ip%%</code> = The User\'s IP Address, detected via <code>$_SERVER["REMOTE_ADDR"]</code>.</li>'."\n";
				echo '<li><code>%%user_id%%</code> = A unique WordPress User ID generated during registration.</li>'."\n";
				echo '<li><code>%%wp_login_url%%</code> = The full URL where Users can get logged into your site.</li>'."\n";
				echo '<li style="margin-top:1em;"><code style="text-decoration:line-through;">%%user_pass%%</code> = <em>Deprecated, please stop using this. Starting w/ WordPress v4.3, this email is only sent to users who did NOT set a Custom Password during registration; i.e., the New User Notification email is only sent to the user whenever they need it to set their password. In that scenario, you should be sending the user the <code>%%wp_set_pass_url%%</code> instead of sending a plain-text password via email; which is a security no-no.</em></li>'."\n";
				echo '</ul>'."\n";

				echo '<strong>Custom Registration/Profile Fields are also supported in this email:</strong>'."\n";
				echo '<ul>'."\n";
				echo '<li><code>%%date_of_birth%%</code> would be valid; if you have a Custom Registration/Profile Field with the ID <code>date_of_birth</code>.</li>'."\n";
				echo '<li><code>%%street_address%%</code> would be valid; if you have a Custom Registration/Profile Field with the ID <code>street_address</code>.</li>'."\n";
				echo '<li><code>%%country%%</code> would be valid; if you have a Custom Registration/Profile Field with the ID <code>country</code>.</li>'."\n";
				echo '<li><em><code>%%etc, etc...%%</code> <strong>see:</strong> s2Member → General Options → Registration/Profile Fields</em>.</li>'."\n";
				echo '</ul>'."\n";

				echo '<strong>Custom Replacement Codes can also be inserted using these instructions:</strong>'."\n";
				echo '<ul>'."\n";
				echo '<li><code>%%cv0%%</code> = The domain of your site, which is passed through the `custom` attribute in your Shortcode.</li>'."\n";
				echo '<li><code>%%cv1%%</code> = If you need to track additional custom variables, you can pipe delimit them into the `custom` attribute; inside your Shortcode, like this: <code>custom="'.esc_html($_SERVER["HTTP_HOST"]).'|cv1|cv2|cv3"</code>. You can have an unlimited number of custom variables. Obviously, this is for advanced webmasters; but the functionality has been made available for those who need it.</li>'."\n";
				echo '</ul>'."\n";
				echo '<strong>This example uses cv1 to record a special marketing campaign:</strong><br />'."\n";
				echo '<em>(The campaign (i.e., christmas-promo) could be referenced using <code>%%cv1%%</code>)</em><br />'."\n";
				echo '<code>custom="'.esc_html($_SERVER["HTTP_HOST"]).'|christmas-promo"</code>'."\n";

				echo (!is_multisite() || !c_ws_plugin__s2member_utils_conds::is_multisite_farm() || is_main_site()) ?
					'<div class="ws-menu-page-hr"></div>'."\n".
					'<p style="margin:0;"><strong>PHP Code:</strong> It is also possible to use PHP tags—optional (for developers). If you use PHP tags, please run a test email with <code>&lt;?php print_r(get_defined_vars()); ?&gt;</code>. This will give you a full list of all PHP variables available to you in this email. The <code>$user</code> variable is the most important one. It\'s an instance of the <a href="https://s2member.com/r/wordpress-codex-wp_user/" target="_blank" rel="external"><code>WP_User</code></a> class (e.g., <code>$user->ID</code>, <code>$user->has_cap()</code>, etc). Please note that all Replacement Codes will be parsed first, and then any PHP tags that you\'ve included. Also, please remember that emails are sent in plain text format.</p>'."\n"
					: '';
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";
				echo '</div>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<h3 style="margin:0;">Administrative: New User Notification (<a href="#" onclick="jQuery(\'div#ws-plugin--s2member-new-user-admin-email-details\').toggle(); return false;" class="ws-dotted-link">click to customize</a>)</h3>'."\n";
				echo '<p style="margin:0;">This email notification is sent to you, each time a new User/Member registers.</p>'."\n";
				do_action("s2x_during_registration_options_page_during_left_sections_during_new_user_admin_email", get_defined_vars());

				echo '<div id="ws-plugin--s2member-new-user-admin-email-details" style="display:none;">'."\n";
				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-new-user-admin-email-recipients">'."\n";
				echo 'New User Notification Recipients:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_new_user_admin_email_recipients" id="ws-plugin--s2member-new-user-admin-email-recipients" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["new_user_admin_email_recipients"]).'" /><br />'."\n";
				echo 'This is a semicolon ( ; ) delimited list of Recipients. Here is an example:<br />'."\n";
				echo '<code>"Name" &lt;user@example.com&gt;; admin@example.com; "Webmaster" &lt;webmaster@example.com&gt;</code>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-new-user-admin-email-subject">'."\n";
				echo 'New User Notification Subject:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_new_user_admin_email_subject" id="ws-plugin--s2member-new-user-admin-email-subject" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["new_user_admin_email_subject"]).'" /><br />'."\n";
				echo 'Subject Line used in the email notification sent to Administrator.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-new-user-admin-email-message">'."\n";
				echo 'New User Notification Message:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<textarea name="ws_plugin__s2member_new_user_admin_email_message" id="ws-plugin--s2member-new-user-admin-email-message" rows="10">'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["new_user_admin_email_message"]).'</textarea><br />'."\n";
				echo 'Message Body used in the email notification sent to Administrator.<br /><br />'."\n";
				echo '<strong>You can also use these special Replacement Codes if you need them:</strong>'."\n";
				echo '<ul>'."\n";
				echo '<li><code>%%role%%</code> = The Role ID <code>(subscriber, s2member_level[0-9]+, administrator, editor, author, contributor)</code>.</li>'."\n";
				echo '<li><code>%%label%%</code> = The Role ID Label <code>(Subscriber, s2Member Level 1, s2Member Level 2; or your own custom Labels—if configured)</code>.</li>'."\n";
				echo '<li><code>%%level%%</code> = The Level number <code>(0, 1, 2, 3, 4)</code>. (<em>deprecated, no longer recommended; use <code>%%role%%</code></em>)</li>'."\n";
				echo '<li><code>%%ccaps%%</code> = Custom Capabilities. Ex: <code>music,videos,free_gift</code> (<em>in comma-delimited format</em>).</li>'."\n";
				echo '<li><code>%%user_first_name%%</code> = The First Name of the Member who registered their Username.</li>'."\n";
				echo '<li><code>%%user_last_name%%</code> = The Last Name of the Member who registered their Username.</li>'."\n";
				echo '<li><code>%%user_full_name%%</code> = The Full Name (First &amp; Last) of the Member who registered their Username.</li>'."\n";
				echo '<li><code>%%user_email%%</code> = The Email Address of the Member who registered their Username.</li>'."\n";
				echo '<li><code>%%user_login%%</code> = The Username the Member selected during registration.</li>'."\n";
				echo '<li><code>%%user_ip%%</code> = The User\'s IP Address, detected via <code>$_SERVER["REMOTE_ADDR"]</code>.</li>'."\n";
				echo '<li><code>%%user_id%%</code> = A unique WordPress User ID generated during registration.</li>'."\n";
				echo '<li><code>%%wp_login_url%%</code> = The full URL where Users can get logged into your site.</li>'."\n";
				echo '<li style="margin-top:1em;"><code style="text-decoration:line-through;">%%user_pass%%</code> = <em>Deprecated. Starting w/ WordPress v4.3, this is no longer applicable (or advised).</em></li>'."\n";
				echo '</ul>'."\n";

				echo '<strong>Custom Registration/Profile Fields are also supported in this email:</strong>'."\n";
				echo '<ul>'."\n";
				echo '<li><code>%%date_of_birth%%</code> would be valid; if you have a Custom Registration/Profile Field with the ID <code>date_of_birth</code>.</li>'."\n";
				echo '<li><code>%%street_address%%</code> would be valid; if you have a Custom Registration/Profile Field with the ID <code>street_address</code>.</li>'."\n";
				echo '<li><code>%%country%%</code> would be valid; if you have a Custom Registration/Profile Field with the ID <code>country</code>.</li>'."\n";
				echo '<li><em><code>%%etc, etc...%%</code> <strong>see:</strong> s2Member → General Options → Registration/Profile Fields</em>.</li>'."\n";
				echo '</ul>'."\n";

				echo '<strong>Custom Replacement Codes can also be inserted using these instructions:</strong>'."\n";
				echo '<ul>'."\n";
				echo '<li><code>%%cv0%%</code> = The domain of your site, which is passed through the `custom` attribute in your Shortcode.</li>'."\n";
				echo '<li><code>%%cv1%%</code> = If you need to track additional custom variables, you can pipe delimit them into the `custom` attribute; inside your Shortcode, like this: <code>custom="'.esc_html($_SERVER["HTTP_HOST"]).'|cv1|cv2|cv3"</code>. You can have an unlimited number of custom variables. Obviously, this is for advanced webmasters; but the functionality has been made available for those who need it.</li>'."\n";
				echo '</ul>'."\n";
				echo '<strong>This example uses cv1 to record a special marketing campaign:</strong><br />'."\n";
				echo '<em>(The campaign (i.e., christmas-promo) could be referenced using <code>%%cv1%%</code>)</em><br />'."\n";
				echo '<code>custom="'.esc_html($_SERVER["HTTP_HOST"]).'|christmas-promo"</code>'."\n";

				echo (!is_multisite() || !c_ws_plugin__s2member_utils_conds::is_multisite_farm() || is_main_site()) ?
					'<div class="ws-menu-page-hr"></div>'."\n".
					'<p style="margin:0;"><strong>PHP Code:</strong> It is also possible to use PHP tags—optional (for developers). If you use PHP tags, please run a test email with <code>&lt;?php print_r(get_defined_vars()); ?&gt;</code>. This will give you a full list of all PHP variables available to you in this email. The <code>$user</code> variable is the most important one. It\'s an instance of the <a href="https://s2member.com/r/wordpress-codex-wp_user/" target="_blank" rel="external"><code>WP_User</code></a> class (e.g., <code>$user->ID</code>, <code>$user->has_cap()</code>, etc). Please note that all Replacement Codes will be parsed first, and then any PHP tags that you\'ve included. Also, please remember that emails are sent in plain text format.</p>'."\n"
					: '';
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";
				echo '</div>'."\n";
				echo '</div>'."\n";

				echo '</div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_during_registration_options_page_during_left_sections_after_email_config", get_defined_vars());
			}
			if(apply_filters("s2x_during_email_options_page_during_left_sections_display_signup_confirmation_email", TRUE, get_defined_vars()))
			{
				do_action("s2x_during_email_options_page_during_left_sections_before_signup_confirmation_email", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="Signup Confirmation Email (Standard)">'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-signup-confirmation-email-section">'."\n";
				echo '<h3>Signup Confirmation Email (required, but the default works fine)</h3>'."\n";
				echo '<p>This email is sent to new Customers after they return from a successful signup at PayPal. The <strong>primary</strong> purpose of this email is to provide the Customer with instructions, along with a link to register a Username for their Membership. You may also customize this further by providing details that are specifically geared to your site.</p>'."\n";
				do_action("s2x_during_email_options_page_during_left_sections_during_signup_confirmation_email", get_defined_vars());

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-signup-email-recipients">'."\n";
				echo 'Signup Confirmation Recipients:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_signup_email_recipients" id="ws-plugin--s2member-signup-email-recipients" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["signup_email_recipients"]).'" /><br />'."\n";
				echo 'This is a semicolon ( ; ) delimited list of Recipients. Here is an example:<br />'."\n";
				echo '<code>"%%full_name%%" &lt;%%payer_email%%&gt;; admin@example.com; "Webmaster" &lt;webmaster@example.com&gt;</code>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-signup-email-subject">'."\n";
				echo 'Signup Confirmation Email Subject:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_signup_email_subject" id="ws-plugin--s2member-signup-email-subject" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["signup_email_subject"]).'" /><br />'."\n";
				echo 'Subject Line used in the email sent to a Customer after a successful signup has occurred through PayPal.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-signup-email-message">'."\n";
				echo 'Signup Confirmation Email Message:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<textarea name="ws_plugin__s2member_signup_email_message" id="ws-plugin--s2member-signup-email-message" rows="10">'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["signup_email_message"]).'</textarea><br />'."\n";
				echo 'Message Body used in the email sent to a Customer after a successful signup has occurred through PayPal.<br /><br />'."\n";
				echo '<strong>You can also use these special Replacement Codes if you need them:</strong>'."\n";
				echo '<ul class="ws-menu-page-li-margins">'."\n";
				echo '<li><code>%%registration_url%%</code> = The full URL (generated by s2Member) where the Customer can get registered.</li>'."\n";
				echo '<li><code>%%subscr_id%%</code> = The PayPal Subscription ID, which remains constant throughout any &amp; all future payments. [ <a href="#" onclick="alert(\'There is one exception. If you are selling Lifetime or Fixed-Term (non-recurring) access, using Buy Now functionality; the %%subscr_id%% is actually set to the Transaction ID for the purchase. PayPal does not provide a specific Subscription ID for Buy Now purchases. Since Lifetime &amp; Fixed-Term Subscriptions are NOT recurring (i.e., there is only ONE payment), using the Transaction ID as the Subscription ID is a graceful way to deal with this minor conflict.\'); return false;">?</a> ]</li>'."\n";
				echo '<li><code>%%currency%%</code> = Three-character currency code (uppercase); e.g., <code>USD</code></li>'."\n";
				echo '<li><code>%%currency_symbol%%</code> = Currency code symbol; e.g., <code>$</code></li>'."\n";
				echo '<li><code>%%initial%%</code> = The Initial Fee charged during signup. If you offered a 100% Free Trial, this will be <code>0</code>. [ <a href="#" onclick="alert(\'This will always represent the amount of money the Customer spent, whenever they initially signed up, no matter what. If a Customer signs up, under the terms of a 100% Free Trial Period, this will be 0.\'); return false;">?</a> ]</li>'."\n";
				echo '<li><code>%%regular%%</code> = The Regular Amount of the Subscription. If you offer something 100% free, this will be <code>0</code>. [ <a href="#" onclick="alert(\'This is how much the Subscription costs after an Initial Period expires. If you did NOT offer an Initial Period at a different price, %%initial%% and %%regular%% will be equal to the same thing.\'); return false;">?</a> ]</li>'."\n";
				echo '<li><code>%%recurring%%</code> = This is the amount that will be charged on a recurring basis, or <code>0</code> if non-recurring. [ <a href="#" onclick="alert(\'If Recurring Payments have not been required, this will be equal to 0. That being said, %%regular%% &amp; %%recurring%% are usually the same value. This variable can be used in two different ways. You can use it to determine what the Regular Recurring Rate is, or to determine whether the Subscription will recur or not. If it is going to recur, %%recurring%% will be > 0.\'); return false;">?</a> ]</li>'."\n";
				echo '<li><code>%%first_name%%</code> = The First Name of the Customer who purchased the Membership Subscription.</li>'."\n";
				echo '<li><code>%%last_name%%</code> = The Last Name of the Customer who purchased the Membership Subscription.</li>'."\n";
				echo '<li><code>%%full_name%%</code> = The Full Name (First &amp; Last) of the Customer who purchased the Membership Subscription.</li>'."\n";
				echo '<li><code>%%payer_email%%</code> = The Email Address of the Customer who purchased the Membership Subscription.</li>'."\n";
				echo '<li><code>%%user_ip%%</code> = The Customer\'s IP Address, detected during checkout via <code>$_SERVER["REMOTE_ADDR"]</code>.</li>'."\n";
				echo '<li><code>%%item_number%%</code> = The Item Number (colon separated <code><em>level:custom_capabilities:fixed term</em></code>) that the Subscription is for.</li>'."\n";
				echo '<li><code>%%item_name%%</code> = The Item Name (as provided by the <code>desc=""</code> attribute in your Shortcode, which briefly describes the Item Number).</li>'."\n";
				echo '<li><code>%%initial_term%%</code> = This is the term length of the Initial Period. This will be a numeric value, followed by a space, then a single letter. [ <a href="#" onclick="alert(\'Here are some examples:\\n\\n%%initial_term%% = 1 D (this means 1 Day)\\n%%initial_term%% = 1 W (this means 1 Week)\\n%%initial_term%% = 1 M (this means 1 Month)\\n%%initial_term%% = 1 Y (this means 1 Year)\\n\\nThe Initial Period never recurs, so this only lasts for the term length specified, then it is over.\'); return false;">?</a> ]</li>'."\n";
				echo '<li><code>%%initial_cycle%%</code> = This is the <code>%%initial_term%%</code> from above, converted to a cycle representation of: <code><em>X days/weeks/months/years</em></code>.</li>'."\n";
				echo '<li><code>%%regular_term%%</code> = This is the term length of the Regular Period. This will be a numeric value, followed by a space, then a single letter. [ <a href="#" onclick="alert(\'Here are some examples:\\n\\n%%regular_term%% = 1 D (this means 1 Day)\\n%%regular_term%% = 1 W (this means 1 Week)\\n%%regular_term%% = 1 M (this means 1 Month)\\n%%regular_term%% = 1 Y (this means 1 Year)\\n%%regular_term%% = 1 L (this means 1 Lifetime)\\n\\nThe Regular Term is usually recurring. So the Regular Term value represents the period (or duration) of each recurring period. If %%recurring%% = 0, then the Regular Term only applies once, because it is not recurring. So if it is not recurring, the value of %%regular_term%% simply represents how long their Membership privileges are going to last after the %%initial_term%% has expired, if there was an Initial Term. The value of this variable ( %%regular_term%% ) will never be empty, it will always be at least: 1 D, meaning 1 day. No exceptions.\'); return false;">?</a> ]</li>'."\n";
				echo '<li><code>%%regular_cycle%%</code> = This is the <code>%%regular_term%%</code> from above, converted to a cycle representation of: <code><em>[every] X days/weeks/months/years—OR daily, weekly, bi-weekly, monthly, bi-monthly, quarterly, yearly, or lifetime</em></code>. This is a very useful Replacment Code. Its value is dynamic; depending on term length, recurring status, and period/term lengths configured.</li>'."\n";
				echo '<li><code>%%recurring/regular_cycle%%</code> = Example (<code>14.95 / Monthly</code>), or ... (<code>0 / non-recurring</code>); depending on the value of <code>%%recurring%%</code>.</li>'."\n";
				echo '</ul>'."\n";

				echo '<strong>Custom Replacement Codes can also be inserted using these instructions:</strong>'."\n";
				echo '<ul class="ws-menu-page-li-margins">'."\n";
				echo '<li><code>%%cv0%%</code> = The domain of your site, which is passed through the `custom` attribute in your Shortcode.</li>'."\n";
				echo '<li><code>%%cv1%%</code> = If you need to track additional custom variables, you can pipe delimit them into the `custom` attribute; inside your Shortcode, like this: <code>custom="'.esc_html($_SERVER["HTTP_HOST"]).'|cv1|cv2|cv3"</code>. You can have an unlimited number of custom variables. Obviously, this is for advanced webmasters; but the functionality has been made available for those who need it.</li>'."\n";
				echo '</ul>'."\n";
				echo '<strong>This example uses cv1 to record a special marketing campaign:</strong><br />'."\n";
				echo '<em>(The campaign (i.e., christmas-promo) could be referenced using <code>%%cv1%%</code>)</em><br />'."\n";
				echo '<code>custom="'.esc_html($_SERVER["HTTP_HOST"]).'|christmas-promo"</code>'."\n";

				echo (!is_multisite() || !c_ws_plugin__s2member_utils_conds::is_multisite_farm() || is_main_site()) ?
					'<div class="ws-menu-page-hr"></div>'."\n".
					'<p style="margin:0;"><strong>PHP Code:</strong> It is also possible to use PHP tags—optional (for developers). If you use PHP tags, please run a test email with <code>&lt;?php print_r(get_defined_vars()); ?&gt;</code>. This will give you a full list of all PHP variables available to you in this email. The <code>$paypal</code> variable is the most important one. It contains all of the <code>$_POST</code> variables received from PayPal\'s IPN service—or from an s2Member Pro-Form integration (e.g., <code>$paypal["item_number"]</code>, <code>$paypal["item_name"]</code>, etc). Please note that all Replacement Codes will be parsed first, and then any PHP tags that you\'ve included. Also, please remember that emails are sent in plain text format.</p>'."\n"
					: '';
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";
				echo '</div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_during_email_options_page_during_left_sections_after_signup_confirmation_email", get_defined_vars());
			}
			if(apply_filters("s2x_during_email_options_page_during_left_sections_display_ccap_confirmation_email", c_ws_plugin__s2member_utils_conds::pro_is_installed(), get_defined_vars()))
			{
				do_action("s2x_during_email_options_page_during_left_sections_before_ccap_confirmation_email", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="Capability Confirmation Email '.((c_ws_plugin__s2member_utils_conds::pro_is_installed()) ? '(Standard/Pro-Form)' : '(Standard)').'">'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-ccap-confirmation-email-section">'."\n";
				echo '<h3>Capability Confirmation Email (required, but the default works fine)</h3>'."\n";
				echo '<p>This email is sent to existing Users after they complete a Buy Now purchase for one or more Custom Capabilities (if and when you make this possible); see: <strong>Dashboard → s2Member → PayPal Buttons/Forms → Capability (Buy Now)</strong>. The <strong>primary</strong> purpose of this email is to provide the Customer with a confirmation that their account was updated. You may also customize this further by providing details that are specifically geared to your site.</p>'."\n";
				do_action("s2x_during_email_options_page_during_left_sections_during_ccap_confirmation_email", get_defined_vars());

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-ccap-email-recipients">'."\n";
				echo 'Capability Confirmation Recipients:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_ccap_email_recipients" id="ws-plugin--s2member-ccap-email-recipients" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["ccap_email_recipients"]).'" /><br />'."\n";
				echo 'This is a semicolon ( ; ) delimited list of Recipients. Here is an example:<br />'."\n";
				echo '<code>"%%full_name%%" &lt;%%payer_email%%&gt;; admin@example.com; "Webmaster" &lt;webmaster@example.com&gt;</code>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-ccap-email-subject">'."\n";
				echo 'Capability Confirmation Email Subject:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_ccap_email_subject" id="ws-plugin--s2member-ccap-email-subject" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["ccap_email_subject"]).'" /><br />'."\n";
				echo 'Subject Line used in the email sent to a Customer after a purchase is completed through PayPal.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-ccap-email-message">'."\n";
				echo 'Capability Confirmation Email Message:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<textarea name="ws_plugin__s2member_ccap_email_message" id="ws-plugin--s2member-ccap-email-message" rows="10">'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["ccap_email_message"]).'</textarea><br />'."\n";
				echo 'Message Body used in the email sent to a Customer after a purchase is completed through PayPal.<br /><br />'."\n";
				echo '<strong>You can also use these special Replacement Codes if you need them:</strong>'."\n";
				echo '<ul class="ws-menu-page-li-margins">'."\n";
				echo '<li><code>%%txn_id%%</code> = The PayPal Transaction ID. PayPal assigns a unique identifier for every purchase.</li>'."\n";
				echo '<li><code>%%currency%%</code> = Three-character currency code (uppercase); e.g., <code>USD</code></li>'."\n";
				echo '<li><code>%%currency_symbol%%</code> = Currency code symbol; e.g., <code>$</code></li>'."\n";
				echo '<li><code>%%amount%%</code> = The full Amount that you charged for Custom Capability access.</li>'."\n";
				echo '<li><code>%%first_name%%</code> = The First Name of the Customer who completed the purchase.</li>'."\n";
				echo '<li><code>%%last_name%%</code> = The Last Name of the Customer who completed the purchase.</li>'."\n";
				echo '<li><code>%%full_name%%</code> = The Full Name (First &amp; Last) of the Customer who completed the purchase.</li>'."\n";
				echo '<li><code>%%payer_email%%</code> = The Email Address of the Customer who completed the purchase.</li>'."\n";
				echo '<li><code>%%item_number%%</code> = The Item Number (colon separated <code><em>*:custom_capabilities</em></code>); where <code>custom_capabilities</code> is a comma-delimited list of the Custom Capabilities they purchased.</li>'."\n";
				echo '<li><code>%%item_name%%</code> = The Item Name (as provided by the <code>desc=""</code> attribute in your Shortcode, which briefly describes the Item Number).</li>'."\n";
				echo '<li><code>%%user_first_name%%</code> = The First Name listed on their User account. This might be different than what is on file with PayPal.</li>'."\n";
				echo '<li><code>%%user_last_name%%</code> = The Last Name listed on their User account. This might be different than what is on file with PayPal.</li>'."\n";
				echo '<li><code>%%user_full_name%%</code> = The Full Name listed on their User account. This might be different than what is on file with PayPal.</li>'."\n";
				echo '<li><code>%%user_email%%</code> = The Email Address associated with their User account. This might be different than what is on file with PayPal.</li>'."\n";
				echo '<li><code>%%user_login%%</code> = The Username associated with their account. The Customer created this during registration.</li>'."\n";
				echo '<li><code>%%user_ip%%</code> = The Customer\'s original IP Address, during checkout/registration via <code>$_SERVER["REMOTE_ADDR"]</code>.</li>'."\n";
				echo '<li><code>%%user_id%%</code> = A unique WordPress User ID that references this account in the WordPress database.</li>'."\n";
				echo '</ul>'."\n";

				if(c_ws_plugin__s2member_utils_conds::pro_is_installed())
				{
					echo '<strong>Coupon Replacement Codes (applicable only w/ s2Member Pro-Forms):</strong>'."\n";
					echo '<ul class="ws-menu-page-li-margins">'."\n";
					echo '<li><code>%%full_coupon_code%%</code> = A full Coupon Code—if one is accepted by your configuration of s2Member. This may indicate an Affiliate Coupon Code, which will include your Affiliate Suffix Chars too (i.e., the full Coupon Code).</li>'."\n";
					echo '<li><code>%%coupon_code%%</code> = A Coupon Code—if one is accepted by your configuration of s2Member. This will NOT include any Affiliate Suffix Chars. This indicates the actual Coupon Code accepted by your configuration of s2Member (excluding any Affiliate ID).</li>'."\n";
					echo '<li><code>%%coupon_affiliate_id%%</code> = This is the end of an Affiliate Coupon Code <em>(i.e., the referring affiliate\'s ID)</em>. This is only applicable if an Affiliate Coupon Code is accepted by your configuration of s2Member.</li>'."\n";
					echo '</ul>'."\n";
				}
				echo '<strong>Custom Registration/Profile Fields are also supported in this email:</strong>'."\n";
				echo '<ul class="ws-menu-page-li-margins">'."\n";
				echo '<li><code>%%date_of_birth%%</code> would be valid; if you have a Custom Registration/Profile Field with the ID <code>date_of_birth</code>.</li>'."\n";
				echo '<li><code>%%street_address%%</code> would be valid; if you have a Custom Registration/Profile Field with the ID <code>street_address</code>.</li>'."\n";
				echo '<li><code>%%country%%</code> would be valid; if you have a Custom Registration/Profile Field with the ID <code>country</code>.</li>'."\n";
				echo '<li><em><code>%%etc, etc...%%</code> <strong>see:</strong> s2Member → General Options → Registration/Profile Fields</em>.</li>'."\n";
				echo '</ul>'."\n";

				echo '<strong>Custom Replacement Codes can also be inserted using these instructions:</strong>'."\n";
				echo '<ul class="ws-menu-page-li-margins">'."\n";
				echo '<li><code>%%cv0%%</code> = The domain of your site, which is passed through the `custom` attribute in your Shortcode.</li>'."\n";
				echo '<li><code>%%cv1%%</code> = If you need to track additional custom variables, you can pipe delimit them into the `custom` attribute; inside your Shortcode, like this: <code>custom="'.esc_html($_SERVER["HTTP_HOST"]).'|cv1|cv2|cv3"</code>. You can have an unlimited number of custom variables. Obviously, this is for advanced webmasters; but the functionality has been made available for those who need it.</li>'."\n";
				echo '</ul>'."\n";
				echo '<strong>This example uses cv1 to record a special marketing campaign:</strong><br />'."\n";
				echo '<em>(The campaign (i.e., christmas-promo) could be referenced using <code>%%cv1%%</code>)</em><br />'."\n";
				echo '<code>custom="'.esc_html($_SERVER["HTTP_HOST"]).'|christmas-promo"</code>'."\n";

				echo (!is_multisite() || !c_ws_plugin__s2member_utils_conds::is_multisite_farm() || is_main_site()) ?
					'<div class="ws-menu-page-hr"></div>'."\n".
					'<p style="margin:0;"><strong>PHP Code:</strong> It is also possible to use PHP tags—optional (for developers). If you use PHP tags, please run a test email with <code>&lt;?php print_r(get_defined_vars()); ?&gt;</code>. This will give you a full list of all PHP variables available to you in this email. The <code>$paypal</code> variable is the most important one. It contains all of the <code>$_POST</code> variables received from PayPal\'s IPN service—or from an s2Member Pro-Form integration (e.g., <code>$paypal["item_number"]</code>, <code>$paypal["item_name"]</code>, etc). Please note that all Replacement Codes will be parsed first, and then any PHP tags that you\'ve included. Also, please remember that emails are sent in plain text format.</p>'."\n"
					: '';
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";
				echo '</div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_during_email_options_page_during_left_sections_after_ccap_confirmation_email", get_defined_vars());
			}
			if(apply_filters("s2x_during_email_options_page_during_left_sections_display_sp_confirmation_email", TRUE, get_defined_vars()))
			{
				do_action("s2x_during_email_options_page_during_left_sections_before_sp_confirmation_email", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="Specific Post/Page Confirmation Email (Standard)">'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-sp-confirmation-email-section">'."\n";
				echo '<h3>Specific Post/Page Confirmation Email (required, but the default works fine)</h3>'."\n";
				echo '<p>This email is sent to new Customers after they return from a successful purchase at PayPal, for Specific Post/Page Access. (see: <strong>s2Member → Restriction Options → Specific Post/Page Access</strong>). This is NOT used for Membership sales, only for Specific Post/Page Access. The <strong>primary</strong> purpose of this email is to provide the Customer with instructions, along with a link to access the Specific Post/Page they\'ve purchased access to. If you\'ve created a Specific Post/Page Package (with multiple Posts/Pages bundled together into one transaction), this ONE link (<code>%%sp_access_url%%</code>) will automatically authenticate them for access to ALL of the Posts/Pages included in their transaction. You may customize this email further by providing details that are specifically geared to your site.</p>'."\n";
				do_action("s2x_during_email_options_page_during_left_sections_during_sp_confirmation_email", get_defined_vars());

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-sp-email-recipients">'."\n";
				echo 'Specific Post/Page Confirmation Recipients:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_sp_email_recipients" id="ws-plugin--s2member-sp-email-recipients" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["sp_email_recipients"]).'" /><br />'."\n";
				echo 'This is a semicolon ( ; ) delimited list of Recipients. Here is an example:<br />'."\n";
				echo '<code>"%%full_name%%" &lt;%%payer_email%%&gt;; admin@example.com; "Webmaster" &lt;webmaster@example.com&gt;</code>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-sp-email-subject">'."\n";
				echo 'Specific Post/Page Confirmation Email Subject:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_sp_email_subject" id="ws-plugin--s2member-sp-email-subject" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["sp_email_subject"]).'" /><br />'."\n";
				echo 'Subject Line used in the email sent to a Customer after a successful purchase has occurred through PayPal, for Specific Post/Page Access.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-sp-email-message">'."\n";
				echo 'Specific Post/Page Confirmation Email Message:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<textarea name="ws_plugin__s2member_sp_email_message" id="ws-plugin--s2member-sp-email-message" rows="10">'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["sp_email_message"]).'</textarea><br />'."\n";
				echo 'Message Body used in the email sent to a Customer after a successful purchase has occurred through PayPal, for Specific Post/Page Access.<br /><br />'."\n";
				echo '<strong>You can also use these special Replacement Codes if you need them:</strong>'."\n";
				echo '<ul class="ws-menu-page-li-margins">'."\n";
				echo '<li><code>%%sp_access_url%%</code> = The full URL (generated by s2Member) where the Customer can gain access.</li>'."\n";
				echo '<li><code>%%sp_access_exp%%</code> = Human readable expiration for <code>%%sp_access_url%%</code>. Ex: <em>(link expires in <code>%%sp_access_exp%%</code>)</em>.</li>'."\n";
				echo '<li><code>%%txn_id%%</code> = The PayPal Transaction ID. PayPal assigns a unique identifier for every purchase.</li>'."\n";
				echo '<li><code>%%currency%%</code> = Three-character currency code (uppercase); e.g., <code>USD</code></li>'."\n";
				echo '<li><code>%%currency_symbol%%</code> = Currency code symbol; e.g., <code>$</code></li>'."\n";
				echo '<li><code>%%amount%%</code> = The full Amount that you charged for Specific Post/Page Access.</li>'."\n";
				echo '<li><code>%%first_name%%</code> = The First Name of the Customer who purchased Specific Post/Page Access.</li>'."\n";
				echo '<li><code>%%last_name%%</code> = The Last Name of the Customer who purchased Specific Post/Page Access.</li>'."\n";
				echo '<li><code>%%full_name%%</code> = The Full Name (First &amp; Last) of the Customer who purchased Specific Post/Page Access.</li>'."\n";
				echo '<li><code>%%payer_email%%</code> = The Email Address of the Customer who purchased Specific Post/Page Access.</li>'."\n";
				echo '<li><code>%%user_ip%%</code> = The Customer\'s IP Address, detected during checkout via <code>$_SERVER["REMOTE_ADDR"]</code>.</li>'."\n";
				echo '<li><code>%%item_number%%</code> = The Item Number. Ex: <code><em>sp:13,24,36:72</em></code> (translates to: <code><em>sp:comma-delimited IDs:expiration hours</em></code>).</li>'."\n";
				echo '<li><code>%%item_name%%</code> = The Item Name (as provided by the <code>desc=""</code> attribute in your Shortcode, which briefly describes the Item Number).</li>'."\n";
				echo '</ul>'."\n";

				echo '<strong>Custom Replacement Codes can also be inserted using these instructions:</strong>'."\n";
				echo '<ul class="ws-menu-page-li-margins">'."\n";
				echo '<li><code>%%cv0%%</code> = The domain of your site, which is passed through the `custom` attribute in your Shortcode.</li>'."\n";
				echo '<li><code>%%cv1%%</code> = If you need to track additional custom variables, you can pipe delimit them into the `custom` attribute; inside your Shortcode, like this: <code>custom="'.esc_html($_SERVER["HTTP_HOST"]).'|cv1|cv2|cv3"</code>. You can have an unlimited number of custom variables. Obviously, this is for advanced webmasters; but the functionality has been made available for those who need it.</li>'."\n";
				echo '</ul>'."\n";
				echo '<strong>This example uses cv1 to record a special marketing campaign:</strong><br />'."\n";
				echo '<em>(The campaign (i.e., christmas-promo) could be referenced using <code>%%cv1%%</code>)</em><br />'."\n";
				echo '<code>custom="'.esc_html($_SERVER["HTTP_HOST"]).'|christmas-promo"</code>'."\n";

				echo (!is_multisite() || !c_ws_plugin__s2member_utils_conds::is_multisite_farm() || is_main_site()) ?
					'<div class="ws-menu-page-hr"></div>'."\n".
					'<p style="margin:0;"><strong>PHP Code:</strong> It is also possible to use PHP tags—optional (for developers). If you use PHP tags, please run a test email with <code>&lt;?php print_r(get_defined_vars()); ?&gt;</code>. This will give you a full list of all PHP variables available to you in this email. The <code>$paypal</code> variable is the most important one. It contains all of the <code>$_POST</code> variables received from PayPal\'s IPN service—or from an s2Member Pro-Form integration (e.g., <code>$paypal["item_number"]</code>, <code>$paypal["item_name"]</code>, etc). Please note that all Replacement Codes will be parsed first, and then any PHP tags that you\'ve included. Also, please remember that emails are sent in plain text format.</p>'."\n"
					: '';
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";
				echo '</div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_during_email_options_page_during_left_sections_after_sp_confirmation_email", get_defined_vars());
			}
			if(apply_filters("s2x_during_email_options_page_during_left_sections_display_modification_confirmation_email", c_ws_plugin__s2member_utils_conds::pro_is_installed(), get_defined_vars()))
			{
				do_action("s2x_during_email_options_page_during_left_sections_before_modification_confirmation_email", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="Modification Confirmation Email '.((c_ws_plugin__s2member_utils_conds::pro_is_installed()) ? '(Standard/Pro-Form)' : '(Standard)').'">'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-modification-confirmation-email-section">'."\n";
				echo '<h3>Modification Confirmation Email (required, but the default works fine)</h3>'."\n";
				echo '<p>This email is sent to existing Users after they complete an upgrade/downgrade (if and when you make this possible). For instance, if a Free Subscriber upgrades to a paid Membership Level, s2Member considers this a Modification (NOT a Signup; a Signup is associated only with someone completely new). The <strong>primary</strong> purpose of this email is to provide the Customer with a confirmation that their account was updated. You may also customize this further by providing details that are specifically geared to your site.</p>'."\n";
				do_action("s2x_during_email_options_page_during_left_sections_during_modification_confirmation_email", get_defined_vars());

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-modification-email-recipients">'."\n";
				echo 'Modification Confirmation Recipients:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_modification_email_recipients" id="ws-plugin--s2member-modification-email-recipients" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["modification_email_recipients"]).'" /><br />'."\n";
				echo 'This is a semicolon ( ; ) delimited list of Recipients. Here is an example:<br />'."\n";
				echo '<code>"%%full_name%%" &lt;%%payer_email%%&gt;; admin@example.com; "Webmaster" &lt;webmaster@example.com&gt;</code>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-modification-email-subject">'."\n";
				echo 'Modification Confirmation Email Subject:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_modification_email_subject" id="ws-plugin--s2member-modification-email-subject" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["modification_email_subject"]).'" /><br />'."\n";
				echo 'Subject Line used in the email sent to a Customer after a successful modification has occurred through PayPal.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-modification-email-message">'."\n";
				echo 'Modification Confirmation Email Message:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<textarea name="ws_plugin__s2member_modification_email_message" id="ws-plugin--s2member-modification-email-message" rows="10">'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["modification_email_message"]).'</textarea><br />'."\n";
				echo 'Message Body used in the email sent to a Customer after a successful modification has occurred through PayPal.<br /><br />'."\n";
				echo '<strong>You can also use these special Replacement Codes if you need them:</strong>'."\n";
				echo '<ul class="ws-menu-page-li-margins">'."\n";
				echo '<li><code>%%subscr_id%%</code> = The PayPal Subscription ID, which remains constant throughout any &amp; all future payments. [ <a href="#" onclick="alert(\'There is one exception. If you are selling Lifetime or Fixed-Term (non-recurring) access, using Buy Now functionality; the %%subscr_id%% is actually set to the Transaction ID for the purchase. PayPal does not provide a specific Subscription ID for Buy Now purchases. Since Lifetime &amp; Fixed-Term Subscriptions are NOT recurring (i.e., there is only ONE payment), using the Transaction ID as the Subscription ID is a graceful way to deal with this minor conflict.\'); return false;">?</a> ]</li>'."\n";
				echo '<li><code>%%subscr_baid%%</code> = Applicable only with PayPal Pro (Payflow Edition); and only for Express Checkout transactions that require a Billing Agreement. This is the Subscription\'s Billing Agreement ID, which remains constant throughout any &amp; all future payments. [ <a href="#" onclick="alert(\'Applicable only with PayPal Pro (Payflow Edition); and only for Express Checkout transactions that require a Billing Agreement. In all other cases, the %%subscr_baid%% is simply set to the %%subscr_id%% value; i.e., it is a duplicate of %%subscr_id%% in most cases.\'); return false;">?</a> ]</li>'."\n";
				echo '<li><code>%%currency%%</code> = Three-character currency code (uppercase); e.g., <code>USD</code></li>'."\n";
				echo '<li><code>%%currency_symbol%%</code> = Currency code symbol; e.g., <code>$</code></li>'."\n";
				echo '<li><code>%%initial%%</code> = The Initial Fee. If you offered a 100% Free Trial, this will be <code>0</code>. [ <a href="#" onclick="alert(\'This will always represent the amount of money the Customer spent when they completed checkout, no matter what. Even if that amount is 0. If a Customer upgrades/downgrades under the terms of a 100% Free Trial Period, this will be 0.\'); return false;">?</a> ]</li>'."\n";
				echo '<li><code>%%regular%%</code> = The Regular Amount of the Subscription. If you offer something 100% free, this will be <code>0</code>. [ <a href="#" onclick="alert(\'This is how much the Subscription costs after an Initial Period expires. If you did NOT offer an Initial Period at a different price, %%initial%% and %%regular%% will be equal to the same thing.\'); return false;">?</a> ]</li>'."\n";
				echo '<li><code>%%recurring%%</code> = This is the amount that will be charged on a recurring basis, or <code>0</code> if non-recurring. [ <a href="#" onclick="alert(\'If Recurring Payments have not been required, this will be equal to 0. That being said, %%regular%% &amp; %%recurring%% are usually the same value. This variable can be used in two different ways. You can use it to determine what the Regular Recurring Rate is, or to determine whether the Subscription will recur or not. If it is going to recur, %%recurring%% will be > 0.\'); return false;">?</a> ]</li>'."\n";
				echo '<li><code>%%first_name%%</code> = The First Name of the Customer who purchased the Membership Subscription.</li>'."\n";
				echo '<li><code>%%last_name%%</code> = The Last Name of the Customer who purchased the Membership Subscription.</li>'."\n";
				echo '<li><code>%%full_name%%</code> = The Full Name (First &amp; Last) of the Customer who purchased the Membership Subscription.</li>'."\n";
				echo '<li><code>%%payer_email%%</code> = The Email Address of the Customer who purchased the Membership Subscription.</li>'."\n";
				echo '<li><code>%%item_number%%</code> = The Item Number (colon separated <code><em>level:custom_capabilities:fixed term</em></code>) that the Subscription is for.</li>'."\n";
				echo '<li><code>%%item_name%%</code> = The Item Name (as provided by the <code>desc=""</code> attribute in your Shortcode, which briefly describes the Item Number).</li>'."\n";
				echo '<li><code>%%initial_term%%</code> = This is the term length of the Initial Period. This will be a numeric value, followed by a space, then a single letter. [ <a href="#" onclick="alert(\'Here are some examples:\\n\\n%%initial_term%% = 1 D (this means 1 Day)\\n%%initial_term%% = 1 W (this means 1 Week)\\n%%initial_term%% = 1 M (this means 1 Month)\\n%%initial_term%% = 1 Y (this means 1 Year)\\n\\nThe Initial Period never recurs, so this only lasts for the term length specified, then it is over.\'); return false;">?</a> ]</li>'."\n";
				echo '<li><code>%%initial_cycle%%</code> = This is the <code>%%initial_term%%</code> from above, converted to a cycle representation of: <code><em>X days/weeks/months/years</em></code>.</li>'."\n";
				echo '<li><code>%%regular_term%%</code> = This is the term length of the Regular Period. This will be a numeric value, followed by a space, then a single letter. [ <a href="#" onclick="alert(\'Here are some examples:\\n\\n%%regular_term%% = 1 D (this means 1 Day)\\n%%regular_term%% = 1 W (this means 1 Week)\\n%%regular_term%% = 1 M (this means 1 Month)\\n%%regular_term%% = 1 Y (this means 1 Year)\\n%%regular_term%% = 1 L (this means 1 Lifetime)\\n\\nThe Regular Term is usually recurring. So the Regular Term value represents the period (or duration) of each recurring period. If %%recurring%% = 0, then the Regular Term only applies once, because it is not recurring. So if it is not recurring, the value of %%regular_term%% simply represents how long their Membership privileges are going to last after the %%initial_term%% has expired, if there was an Initial Term. The value of this variable ( %%regular_term%% ) will never be empty, it will always be at least: 1 D, meaning 1 day. No exceptions.\'); return false;">?</a> ]</li>'."\n";
				echo '<li><code>%%regular_cycle%%</code> = This is the <code>%%regular_term%%</code> from above, converted to a cycle representation of: <code><em>[every] X days/weeks/months/years—OR daily, weekly, bi-weekly, monthly, bi-monthly, quarterly, yearly, or lifetime</em></code>. This is a very useful Replacment Code. Its value is dynamic; depending on term length, recurring status, and period/term lengths configured.</li>'."\n";
				echo '<li><code>%%recurring/regular_cycle%%</code> = Example (<code>14.95 / Monthly</code>), or ... (<code>0 / non-recurring</code>); depending on the value of <code>%%recurring%%</code>.</li>'."\n";
				echo '<li><code>%%user_first_name%%</code> = The First Name listed on their User account. This might be different than what is on file with PayPal.</li>'."\n";
				echo '<li><code>%%user_last_name%%</code> = The Last Name listed on their User account. This might be different than what is on file with PayPal.</li>'."\n";
				echo '<li><code>%%user_full_name%%</code> = The Full Name listed on their User account. This might be different than what is on file with PayPal.</li>'."\n";
				echo '<li><code>%%user_email%%</code> = The Email Address associated with their User account. This might be different than what is on file with PayPal.</li>'."\n";
				echo '<li><code>%%user_login%%</code> = The Username associated with their account. The Customer created this during registration.</li>'."\n";
				echo '<li><code>%%user_ip%%</code> = The Customer\'s original IP Address, during checkout/registration via <code>$_SERVER["REMOTE_ADDR"]</code>.</li>'."\n";
				echo '<li><code>%%user_id%%</code> = A unique WordPress User ID that references this account in the WordPress database.</li>'."\n";
				echo '</ul>'."\n";

				if(c_ws_plugin__s2member_utils_conds::pro_is_installed())
				{
					echo '<strong>Coupon Replacement Codes (applicable only w/ s2Member Pro-Forms):</strong>'."\n";
					echo '<ul class="ws-menu-page-li-margins">'."\n";
					echo '<li><code>%%full_coupon_code%%</code> = A full Coupon Code—if one is accepted by your configuration of s2Member. This may indicate an Affiliate Coupon Code, which will include your Affiliate Suffix Chars too (i.e., the full Coupon Code).</li>'."\n";
					echo '<li><code>%%coupon_code%%</code> = A Coupon Code—if one is accepted by your configuration of s2Member. This will NOT include any Affiliate Suffix Chars. This indicates the actual Coupon Code accepted by your configuration of s2Member (excluding any Affiliate ID).</li>'."\n";
					echo '<li><code>%%coupon_affiliate_id%%</code> = This is the end of an Affiliate Coupon Code <em>(i.e., the referring affiliate\'s ID)</em>. This is only applicable if an Affiliate Coupon Code is accepted by your configuration of s2Member.</li>'."\n";
					echo '</ul>'."\n";
				}
				echo '<strong>Custom Registration/Profile Fields are also supported in this email:</strong>'."\n";
				echo '<ul class="ws-menu-page-li-margins">'."\n";
				echo '<li><code>%%date_of_birth%%</code> would be valid; if you have a Custom Registration/Profile Field with the ID <code>date_of_birth</code>.</li>'."\n";
				echo '<li><code>%%street_address%%</code> would be valid; if you have a Custom Registration/Profile Field with the ID <code>street_address</code>.</li>'."\n";
				echo '<li><code>%%country%%</code> would be valid; if you have a Custom Registration/Profile Field with the ID <code>country</code>.</li>'."\n";
				echo '<li><em><code>%%etc, etc...%%</code> <strong>see:</strong> s2Member → General Options → Registration/Profile Fields</em>.</li>'."\n";
				echo '</ul>'."\n";

				echo '<strong>Custom Replacement Codes can also be inserted using these instructions:</strong>'."\n";
				echo '<ul class="ws-menu-page-li-margins">'."\n";
				echo '<li><code>%%cv0%%</code> = The domain of your site, which is passed through the `custom` attribute in your Shortcode.</li>'."\n";
				echo '<li><code>%%cv1%%</code> = If you need to track additional custom variables, you can pipe delimit them into the `custom` attribute; inside your Shortcode, like this: <code>custom="'.esc_html($_SERVER["HTTP_HOST"]).'|cv1|cv2|cv3"</code>. You can have an unlimited number of custom variables. Obviously, this is for advanced webmasters; but the functionality has been made available for those who need it.</li>'."\n";
				echo '</ul>'."\n";
				echo '<strong>This example uses cv1 to record a special marketing campaign:</strong><br />'."\n";
				echo '<em>(The campaign (i.e., christmas-promo) could be referenced using <code>%%cv1%%</code>)</em><br />'."\n";
				echo '<code>custom="'.esc_html($_SERVER["HTTP_HOST"]).'|christmas-promo"</code>'."\n";

				echo (!is_multisite() || !c_ws_plugin__s2member_utils_conds::is_multisite_farm() || is_main_site()) ?
					'<div class="ws-menu-page-hr"></div>'."\n".
					'<p style="margin:0;"><strong>PHP Code:</strong> It is also possible to use PHP tags—optional (for developers). If you use PHP tags, please run a test email with <code>&lt;?php print_r(get_defined_vars()); ?&gt;</code>. This will give you a full list of all PHP variables available to you in this email. The <code>$paypal</code> variable is the most important one. It contains all of the <code>$_POST</code> variables received from PayPal\'s IPN service—or from an s2Member Pro-Form integration (e.g., <code>$paypal["item_number"]</code>, <code>$paypal["item_name"]</code>, etc). Please note that all Replacement Codes will be parsed first, and then any PHP tags that you\'ve included. Also, please remember that emails are sent in plain text format.</p>'."\n"
					: '';
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";
				echo '</div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_during_email_options_page_during_left_sections_after_modification_confirmation_email", get_defined_vars());
			}
			if(apply_filters("s2x_during_email_options_page_page_during_left_sections_display_mailchimp", TRUE, get_defined_vars()))
			{
				do_action("s2x_during_email_options_page_page_during_left_sections_before_mailchimp", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="MailChimp Integration">'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-mailchimp-section">'."\n";
				echo '<a href="http://www.s2member.com/r/mailchimp/" target="_blank"><img src="'.esc_attr($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["dir_url"]).'/src/images/mailchimp-stamp.png" class="ws-menu-page-right" style="width:125px; height:125px; border:0;" alt="." /></a>'."\n";
				echo '<h3>MailChimp List Server Integration (optional)</h3>'."\n";
				echo '<p>s2Member can be integrated with MailChimp. MailChimp is an email marketing service. MailChimp makes it easy to send email newsletters to your Customers, manage your MailChimp subscriber lists, and track campaign performance. Although s2Member can be integrated with almost ANY list server, we highly recommend MailChimp; because of their <a href="http://s2member.com/r/mailchimp-api-docs/" target="_blank" rel="external">powerful API for MailChimp services</a>. In future versions of s2Member, we plan to build additional features into s2Member that work with, and extend MailChimp services.</p>'."\n";
				echo '<p>For now, we\'ve covered the basics. You can have your Members automatically subscribed to your MailChimp marketing lists <em>(i.e., newsletters / auto-responders)</em>. You\'ll need a <a href="http://www.s2member.com/r/mailchimp/" target="_blank" rel="external">MailChimp account</a>, a <a href="http://s2member.com/r/mailchimp-api-key/" target="_blank" rel="external">MailChimp API Key</a>, and your <a href="#" onclick="alert(\'To obtain your MailChimp List ID(s), log into your MailChimp account and click the Lists tab. Now click the (View) button, for the List(s) you want to integrate with s2Member. Then, click the (Settings) link. At the bottom of the (Settings) page, for each list; you\\\'ll find a Unique List ID.\'); return false;">MailChimp List IDs</a>.</p>'."\n";
				do_action("s2x_during_email_options_page_page_during_left_sections_during_mailchimp", get_defined_vars());

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-mailchimp-api-key">'."\n";
				echo 'MailChimp API Key:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="password" autocomplete="off" name="ws_plugin__s2member_mailchimp_api_key" id="ws-plugin--s2member-mailchimp-api-key" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["mailchimp_api_key"]).'" /><br />'."\n";
				echo 'Once you have a MailChimp account, you\'ll need to <a href="http://s2member.com/r/mailchimp-api-key/" target="_blank" rel="external">add an API Key</a>.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";

				for($n = 0; $n <= $GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["levels"]; $n++)
				{
					echo '<tr>'."\n";

					echo '<th>'."\n";
					echo '<label for="ws-plugin--s2member-level'.$n.'-mailchimp-list-ids">'."\n";
					echo 'List ID(s) for '.(($n === 0) ? 'Free Subscribers' : 'Level #'.$n.' Members').' (comma-delimited):'."\n";
					echo '</label>'."\n";
					echo '</th>'."\n";

					echo '</tr>'."\n";
					echo '<tr>'."\n";

					echo '<td>'."\n";
					echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_level'.$n.'_mailchimp_list_ids" id="ws-plugin--s2member-level'.$n.'-mailchimp-list-ids" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["level".$n."_mailchimp_list_ids"]).'" /><br />'."\n";
					echo 'New '.(($n === 0) ? 'Free Subscribers' : 'Level #'.$n.' Members').' will be subscribed to these List IDs.<br />'."\n";
					echo 'Ex: <code>4a44fRio5d, 434ksvviEdf, 8834jsdf923, ee9djfs4jel3</code><br />'."\n";
					echo 'Or: <code>4a44fRio5d::Group Title::Group|Another Group</code>'."\n";
					echo '</td>'."\n";

					echo '</tr>'."\n";
				}
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<div class="info" style="margin-bottom:0;">'."\n";
				echo '<p style="margin-top:0;"><span>We highly recommend that you enable logging during your initial testing phase. Logs produce lots of useful details that can help in debugging. Logs can help you find issues in your configuration and/or problems during MailChimp processing. See: <a href="'.esc_attr(admin_url("/admin.php?page=ws-plugin--s2member-logs")).'">Log Files (Debug)</a>.</span></p>'."\n";
				echo '<p style="margin-bottom:0;"><span class="ws-menu-page-error">However, it is very important to disable logging once you go live. Log files may contain personally identifiable information, credit card numbers, secret API credentials, passwords and/or other sensitive information. We strongly suggest that logging be disabled on a live site (for security reasons).</span></p>'."\n";
				echo '</div>'."\n";

				echo '</div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_during_email_options_page_page_during_left_sections_after_mailchimp", get_defined_vars());
			}
			if(apply_filters("s2x_during_email_options_page_page_during_left_sections_display_getresponse", c_ws_plugin__s2member_utils_conds::pro_is_installed(), get_defined_vars()))
			{
				do_action("s2x_during_email_options_page_page_during_left_sections_before_getresponse", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="GetResponse Integration">'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-getresponse-section">'."\n";
				echo '<a href="http://www.s2member.com/r/getresponse/" target="_blank"><img src="'.esc_attr($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["dir_url"]).'/src/images/getresponse-logo.png" class="ws-menu-page-right" style="width:256px; height:89px; border:0;" alt="." /></a>'."\n";
				echo '<h3>GetResponse List Server Integration (optional)</h3>'."\n";
				echo '<p>s2Member can be integrated with GetResponse. GetResponse is a complete email marketing solution. It provides turnkey newsletter publishing and hosting features, as well as unlimited autoresponders to deliver information to your subscribers and convert them to paying customers.</p>'."\n";
				echo '<p>You can have your Members automatically subscribed to your GetResponse marketing lists <em>(i.e., newsletters / auto-responders)</em>. You\'ll need a <a href="http://www.s2member.com/r/getresponse" target="_blank" rel="external">GetResponse account</a>, a <a href="http://www.s2member.com/r/getresponse-api-key" target="_blank" rel="external">GetResponse API Key</a>, and your <a href="http://www.s2member.com/r/getresponse-campaigns-list" target="_blank" rel="external" onclick="alert(\'To obtain your GetResponse Campaign Token(s), log into your GetResponse account and navigate to your entire list of Campaigns. In the left-hand column you\\\'ll find a list of Unique Campaign Tokens.\\n\\nPlease click OK and we\\\'ll take you there now :-)\');">GetResponse Campaign Tokens</a>.</p>'."\n";
				do_action("s2x_during_email_options_page_page_during_left_sections_during_getresponse", get_defined_vars());

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-getresponse-api-key">'."\n";
				echo 'GetResponse API Key:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="password" autocomplete="off" name="ws_plugin__s2member_getresponse_api_key" id="ws-plugin--s2member-getresponse-api-key" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["getresponse_api_key"]).'" /><br />'."\n";
				echo 'Once you have a GetResponse account, you\'ll need to login; then <a href="http://www.s2member.com/r/getresponse-api-key" target="_blank" rel="external">get your API Key</a>.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";

				for($n = 0; $n <= $GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["levels"]; $n++)
				{
					echo '<tr>'."\n";

					echo '<th>'."\n";
					echo '<label for="ws-plugin--s2member-level'.$n.'-getresponse-list-ids">'."\n";
					echo 'Campaign Token(s) for '.(($n === 0) ? 'Free Subscribers' : 'Level #'.$n.' Members').' (comma-delimited):'."\n";
					echo '</label>'."\n";
					echo '</th>'."\n";

					echo '</tr>'."\n";
					echo '<tr>'."\n";

					echo '<td>'."\n";
					echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_level'.$n.'_getresponse_list_ids" id="ws-plugin--s2member-level'.$n.'-getresponse-list-ids" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["level".$n."_getresponse_list_ids"]).'" /><br />'."\n";
					echo 'New '.(($n === 0) ? 'Free Subscribers' : 'Level #'.$n.' Members').' will be subscribed to these Campaign Tokens.<br />'."\n";
					echo 'Ex: <code>4ksdX</code> or <code>4ksdX, koeeXs, ggjXk, aakSc</code>'."\n";
					echo '</td>'."\n";

					echo '</tr>'."\n";
				}
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<div class="info" style="margin-bottom:0;">'."\n";
				echo '<p style="margin-top:0;"><span>We highly recommend that you enable logging during your initial testing phase. Logs produce lots of useful details that can help in debugging. Logs can help you find issues in your configuration and/or problems during GetResponse processing. See: <a href="'.esc_attr(admin_url("/admin.php?page=ws-plugin--s2member-logs")).'">Log Files (Debug)</a>.</span></p>'."\n";
				echo '<p style="margin-bottom:0;"><span class="ws-menu-page-error">However, it is very important to disable logging once you go live. Log files may contain personally identifiable information, credit card numbers, secret API credentials, passwords and/or other sensitive information. We strongly suggest that logging be disabled on a live site (for security reasons).</span></p>'."\n";
				echo '</div>'."\n";

				echo '</div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_during_email_options_page_page_during_left_sections_after_getresponse", get_defined_vars());
			}
			if(apply_filters("s2x_during_email_options_page_page_during_left_sections_display_aweber", TRUE, get_defined_vars()))
			{
				do_action("s2x_during_email_options_page_page_during_left_sections_before_aweber", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="AWeber Integration">'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-aweber-section">'."\n";
				echo '<a href="http://s2member.com/r/aweber/" target="_blank"><img src="'.esc_attr($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["dir_url"]).'/src/images/aweber-logo.png" class="ws-menu-page-right ws-menu-page-bordered" style="width:125px; height:125px; border:0;" alt="." /></a>'."\n";
				echo '<h3>AWeber List Server Integration (optional)</h3>'."\n";
				echo '<p>s2Member can be integrated with AWeber. AWeber is an email marketing service. Whether you\'re looking to get your first email campaign off the ground, or you\'re a seasoned veteran who wants to dig into advanced tools like detailed email web analytics, activity based segmentation, geo-targeting and broadcast split-testing, AWeber\'s got just what you need to make email marketing work for you. You can have your Members automatically subscribed to your AWeber marketing lists <em>(i.e., newsletters / auto-responders)</em>. You\'ll need an <a href="http://s2member.com/r/aweber/" target="_blank" rel="external">AWeber account</a> and your <a href="#" onclick="alert(\'To obtain your AWeber List ID(s), log into your AWeber account. Click on the Lists tab. On that page you\\\'ll find a Unique List ID associated with each of your lists. AWeber sometimes refers to this as a List Name instead of a List ID.\'); return false;">AWeber List IDs</a>. You will ALSO need either an API Authorization Code (if you choose the API option below); or a <a href="http://www.s2member.com/kb/aweber-email-parser-for-s2member/" target="_blank" rel="external">Custom Email Parser</a> for the s2Member application.</p>'."\n";
				echo '<p><em><strong>AWeber Tip:</strong> If you want your Members to be subscribed to multiple AWeber List IDs at the same time, instead of comma-delimiting those List IDs here; we suggest a single List ID in your s2Member integration; then use <a href="http://s2member.com/r/aweber-automation-rules/" target="_blank" rel="external">AWeber Automation Rules</a> for this. Automation Rules can also reduce the number of email confirmation notices that Members receive.</em></p>'."\n";
				echo '<p><em><strong>AWeber Tip:</strong> This company is known to have a policy of rejecting role-based email addresses like: <code>admin@</code> or <code>webmaster@</code>. Therefore, if you integrate AWeber it is suggested that you configure s2Member to Force Personal Emails. Please see: <strong>s2Member → General Options → Registration/Profile Fields &amp; Options</strong>.</em></p>'."\n";
				do_action("s2x_during_email_options_page_page_during_left_sections_during_aweber", get_defined_vars());

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-aweber-api-type">'."\n";
				echo 'AWeber API Method (Please Choose):'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<select name="ws_plugin__s2member_aweber_api_type" id="ws-plugin--s2member-aweber-api-type">'."\n";
				echo '<option value="api"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["aweber_api_type"] === "api") ? ' selected="selected"' : '').'>API (recommended for a more robust integration)</option>'."\n";
				echo '<option value="email"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["aweber_api_type"] === "email") ? ' selected="selected"' : '').'>Email (less reliable; requires an Email Parser)</option>'."\n";
				echo '</select><br />'."\n";
				echo 'Starting w/ s2Member™ v141007+, you can now integrate with AWeber\'s API (recommended) instead of through an Email Parser.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-aweber-api-key">'."\n";
				echo 'AWeber API Authorization Code (Required for API Integration):<br />'."\n";
				echo '<small>If you choose <code>API</code> above, you MUST fill this in please.</small>'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="password" autocomplete="off" name="ws_plugin__s2member_aweber_api_key" id="ws-plugin--s2member-aweber-api-key" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["aweber_api_key"]).'" /><br />'."\n";
				echo 'Once you have an AWeber account, <a href="http://www.s2member.com/r/aweber-api-key" target="_blank" rel="external">click here to get the Authorization Code</a> needed by s2Member.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";

				for($n = 0; $n <= $GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["levels"]; $n++)
				{
					echo '<tr>'."\n";

					echo '<th>'."\n";
					echo '<label for="ws-plugin--s2member-level'.$n.'-aweber-list-ids">'."\n";
					echo 'List ID(s) for '.(($n === 0) ? 'Free Subscribers' : 'Level #'.$n.' Members').' (comma-delimited):'."\n";
					echo '</label>'."\n";
					echo '</th>'."\n";

					echo '</tr>'."\n";
					echo '<tr>'."\n";

					echo '<td>'."\n";
					echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_level'.$n.'_aweber_list_ids" id="ws-plugin--s2member-level'.$n.'-aweber-list-ids" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["level".$n."_aweber_list_ids"]).'" /><br />'."\n";
					echo 'New '.(($n === 0) ? 'Free Subscribers' : 'Level #'.$n.' Members').' will be subscribed to these List IDs.<br />'."\n";
					echo 'Ex: <code>mylist, anotherlist</code>—See also: <a href="http://s2member.com/r/aweber-automation-rules/" target="_blank" rel="external">Automation Rules</a>'."\n";
					echo '</td>'."\n";

					echo '</tr>'."\n";
				}
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<div class="info" style="margin-bottom:0;">'."\n";
				echo '<p style="margin-top:0;"><span>We highly recommend that you enable logging during your initial testing phase. Logs produce lots of useful details that can help in debugging. Logs can help you find issues in your configuration and/or problems during AWeber processing. See: <a href="'.esc_attr(admin_url("/admin.php?page=ws-plugin--s2member-logs")).'">Log Files (Debug)</a>.</span></p>'."\n";
				echo '<p style="margin-bottom:0;"><span class="ws-menu-page-error">However, it is very important to disable logging once you go live. Log files may contain personally identifiable information, credit card numbers, secret API credentials, passwords and/or other sensitive information. We strongly suggest that logging be disabled on a live site (for security reasons).</span></p>'."\n";
				echo '</div>'."\n";

				echo '</div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_during_email_options_page_page_during_left_sections_after_aweber", get_defined_vars());
			}
			if(apply_filters("s2x_during_email_options_page_page_during_left_sections_display_opt_in", TRUE, get_defined_vars()))
			{
				do_action("s2x_during_email_options_page_page_during_left_sections_before_opt_in", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="Registration / Double Opt-In Box?">'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-opt-in-section">'."\n";
				echo '<h3>Double Opt-In Checkbox Field (optional)</h3>'."\n";
				echo '<p>A Double Opt-In Checkbox will ONLY be displayed, if you\'ve integrated one <em>or more</em> List Servers. See also: <a href="http://www.s2member.com/kb/double-opt-in-checkbox/" target="_blank" rel="external">this KB article</a>.</p>'."\n";
				do_action("s2x_during_email_options_page_page_during_left_sections_during_opt_in", get_defined_vars());

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr class="ws-plugin--s2member-custom-reg-opt-in-label-row"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_opt_in"]) ? ' style="display:none;"' : '').'>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-custom-reg-opt-in-label">'."\n";
				echo 'Double Opt-In Checkbox Label:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr class="ws-plugin--s2member-custom-reg-opt-in-label-row"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_opt_in"]) ? ' style="display:none;"' : '').'>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_custom_reg_opt_in_label" id="ws-plugin--s2member-custom-reg-opt-in-label" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_opt_in_label"]).'" /><br />'."\n";
				echo 'Example: <code><img src="'.esc_attr($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["dir_url"]).'/src/images/'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_opt_in"] == 1) ? 'checked' : 'unchecked').'.png" class="ws-plugin--s2member-custom-reg-opt-in-label-prev-img ws-menu-page-img-16" style="vertical-align:middle;" alt="" /> Your Label will appear next to a Checkbox.</code>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-custom-reg-opt-in">'."\n";
				echo 'Require Double Opt-In Checkbox?'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<select name="ws_plugin__s2member_custom_reg_opt_in" id="ws-plugin--s2member-custom-reg-opt-in">'."\n";
				echo '<option value="1"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_opt_in"] == 1) ? ' selected="selected"' : '').'>Yes (the Box MUST be checked—checked by default)</option>'."\n";
				echo '<option value="2"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_opt_in"] == 2) ? ' selected="selected"' : '').'>Yes (the Box MUST be checked—unchecked by default)</option>'."\n";
				echo '<option value="0"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_opt_in"]) ? ' selected="selected"' : '').'>No (disable—do NOT display or require the Checkbox)</option>'."\n";
				echo '</select><br />'."\n";
				echo 'An email confirmation will NOT be sent to the User, unless the Box is checked, or you\'ve disabled the Box; by choosing <code>No</code>.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";
				echo '</div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_during_email_options_page_page_during_left_sections_after_opt_in", get_defined_vars());
			}
			if(apply_filters("s2x_during_email_options_page_page_during_left_sections_display_opt_out", TRUE, get_defined_vars()))
			{
				do_action("s2x_during_email_options_page_page_during_left_sections_before_opt_out", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="Automate Un-Subscribe/Opt-Outs?">'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-opt-out-section">'."\n";
				echo '<h3>Automate Un-Subscribe/Opt-Out Removals (optional)</h3>'."\n";
				echo '<p>s2Member can automatically <em>(and silently)</em> remove Users/Members from the List Servers you\'ve configured above. s2Member is also capable of automating this, based on your own personal configuration preferences. Below, you can choose which Events you consider grounds for List Removal. It is also important to point out that s2Member will ONLY remove Users/Members from the Lists you\'ve configured at the Level the User/Member is or was at during the time of the Event. For example, if a Level #1 Member is deleted, they will ONLY be removed from the List(s) you\'ve configured at Level #1. If an account is upgraded from Level #1 to Level #2, they will ONLY be removed from the List(s) you\'ve configured at Level #1. Of course, all of this is based on the configuration below.</p>'."\n";
				echo '<p><em><strong>Regarding AWeber Email Parser Integration::</strong> these will NOT work for AWeber until you <a href="http://s2member.com/r/aweber-notification-email/" target="_blank" rel="external">add a Notification Email</a> to your AWeber account matching the "EMail From Address" configured in <strong>s2Member → General Options → EMail Configuration</strong>. Which is currently set to: <code>'.esc_html($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["reg_email_from_email"]).'</code>. This is a required step if you want s2Member to be authenticated when it emails List Removal requests to AWeber. Please note, this only applies to AWeber integration via "email". If you choose to integrate via the AWeber API instead (recommended) this is not necessary/applicable.</em></p>'."\n";
				do_action("s2x_during_email_options_page_page_during_left_sections_during_opt_out", get_defined_vars());

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-custom-reg-auto-opt-outs">'."\n";
				echo 'Process List Removals Automatically? (choose events)'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<div class="ws-menu-page-scrollbox" style="height:150px;">'."\n";
				echo '<input type="hidden" name="ws_plugin__s2member_custom_reg_auto_opt_outs[]" value="update-signal" />'."\n";
				foreach(array("removal-deletion" => "<strong>Anytime a User is deleted (including manual deletions)</strong>", "ipn-refund-reversal-deletion" => "&#9492;&#9472; Anytime s2Member deletes an account because of a Refund/Reversal.", "(ipn|auto-eot)-cancellation-expiration-deletion" => "&#9492;&#9472; Anytime s2Member deletes an account because of a Cancellation/Expiration.", "modification" => "<strong>Anytime a User's Role changes (including manual changes)</strong>", "ipn-refund-reversal-demotion" => "&#9492;&#9472; Anytime s2Member demotes an account because of a Refund/Reversal.", "(ipn|auto-eot)-cancellation-expiration-demotion" => "&#9492;&#9472; Anytime s2Member demotes an account because of a Cancellation/Expiration.", "(rtn|ipn)-upgrade-downgrade" => "&#9492;&#9472; Anytime s2Member changes a User's Role after a paid Subscr. Modification.") as $ws_plugin__s2member_temp_s_value => $ws_plugin__s2member_temp_s_label)
					echo '<input type="checkbox" name="ws_plugin__s2member_custom_reg_auto_opt_outs[]" id="ws-plugin--s2member-custom-reg-auto-opt-outs-'.esc_attr(preg_replace("/[^a-z0-9_\-]/", "-", $ws_plugin__s2member_temp_s_value)).'" value="'.esc_attr($ws_plugin__s2member_temp_s_value).'"'.((in_array($ws_plugin__s2member_temp_s_value, $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_auto_opt_outs"])) ? ' checked="checked"' : '').' /> <label for="ws-plugin--s2member-custom-reg-auto-opt-outs-'.esc_attr(preg_replace("/[^a-z0-9_\-]/", "-", $ws_plugin__s2member_temp_s_value)).'">'.$ws_plugin__s2member_temp_s_label.'</label><br />'."\n";
				echo '</div>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-custom-reg-auto-opt-out-transitions">'."\n";
				echo 'Also Process List Transitions Automatically?'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<select name="ws_plugin__s2member_custom_reg_auto_opt_out_transitions" id="ws-plugin--s2member-custom-reg-auto-opt-out-transitions">'."\n";
				echo '<option value="0"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_auto_opt_out_transitions"]) ? ' selected="selected"' : '').'>No (do NOT transition mailing list subscribers automatically)</option>'."\n";
				echo '<option value="1"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_auto_opt_out_transitions"] === "1") ? ' selected="selected"' : '').'>Yes (automatically transition, if able to remove from a previous list)</option>'."\n";
				echo '<option value="2"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_auto_opt_out_transitions"] === "2") ? ' selected="selected"' : '').'>Yes (always automatically transition, even if NOT removed from a previous list)</option>'."\n";
				echo '</select><br />'."\n";
				echo '<em><strong>Transitions:</strong> When/if s2Member automatically removes a Member from Lists at their current Level# (based on your configuration in the previous section), this setting tells s2Member that it should <strong>also</strong> transition the Member to any Lists you\'ve configured at the new Access Level# (i.e., Role) they are being changed to. For example, if a Member is demoted from Level #1 to Level #0, do you want s2Member to add them to the Level #0 List(s) after it removes them from the Level #1 List(s)?</em><br /><br />'."\n";
				echo '<em><strong>If removed from a previous list, or NOT?:</strong> You can choose your preference above. When/if s2Member automatically transitions a mailing list subscriber, it will first try to remove the subscriber from a previous mailing list. If s2Member is able to remove the subscriber from a previous list before the transition takes place, s2Member will then make an attempt (based on your configuration) to transition the subscriber to a new/different list silently (i.e., without a new confirmation email being sent out). If s2Member is NOT able to remove a subscriber from a previous list, it can (if configured to do so) still transition a subscriber to a new list, by sending the subscriber a new email confirmation letter (i.e., this is NOT silent, because you absolutely NEED the subscriber\'s permission in this case).</em><br /><br />'."\n";
				echo '<em><strong>Seamless with MailChimp:</strong> If enabled, Automatic List Transitions work seamlessly with MailChimp. Automatic List Transitions also work with GetResponse/AWeber, but GetResponse/AWeber may send the User/Member a new confirmation email, asking them to confirm changes to their mailing list subscription with you. Work is underway to improve this aspect of s2Member\'s integration with GetResponse/AWeber in a future release. Ideally, a Customer would be transitioned silently behind the scene with GetResponse/AWeber too, when appropriate.</em>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";
				echo '</div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_during_email_options_page_page_during_left_sections_after_opt_out", get_defined_vars());
			}
			if(apply_filters("s2x_during_email_options_page_page_during_left_sections_display_other_methods", TRUE, get_defined_vars()))
			{
				do_action("s2x_during_email_options_page_page_during_left_sections_before_other_methods", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="Other List Server Integration Methods">'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-other-methods-section">'."\n";
				echo '<h3>Other List Server Integrations (there\'s always a way)</h3>'."\n";
				echo '<p>Check the s2Member API Notifications panel. You\'ll find additional layers of automation available through the use of the `Signup`, `Registration`, `Payment`, `EOT/Deletion`, `Refund/Reversal`, and `Specific Post/Page` Notifications that are available to you through the s2Member API. These make it possible to integrate with 3rd party applications; like list servers, affiliate programs, and other back-office routines; in more advanced ways. You will probably need to get help from a web developer though. s2Member API Notifications require some light PHP scripting by someone familiar with web service connections.</p>'."\n";
				do_action("s2x_during_email_options_page_page_during_left_sections_during_other_methods", get_defined_vars());
				echo '</div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_during_email_options_page_page_during_left_sections_after_other_methods", get_defined_vars());
			}

			do_action("s2x_during_email_options_page_after_left_sections", get_defined_vars());

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

new c_ws_plugin__s2member_menu_page_email_options();
