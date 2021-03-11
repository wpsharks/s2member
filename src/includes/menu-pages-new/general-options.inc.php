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

if(!class_exists("c_ws_plugin__s2member_menu_page_general_options"))
{
	/**
	 * Menu page for the s2Member plugin (General Options page).
	 *
	 * @package s2Member\Menu_Pages
	 * @since 210208
	 */
	class c_ws_plugin__s2member_menu_page_general_options {
		static public function render() {
			echo '<div class="wrap ws-menu-page">'."\n";

			echo '<div class="wp-header-end"></div>'."\n";

			echo '<div class="ws-menu-page-toolbox">'."\n";
			c_ws_plugin__s2member_menu_pages_tb::display();
			echo '</div>'."\n";

			echo '<h2>General Options</h2>'."\n";

			echo '<table class="ws-menu-page-table">'."\n";
			echo '<tbody class="ws-menu-page-table-tbody">'."\n";
			echo '<tr class="ws-menu-page-table-tr">'."\n";
			echo '<td class="ws-menu-page-table-l">'."\n";

			echo '<form method="post" name="ws_plugin__s2member_options_form" id="ws-plugin--s2member-options-form" autocomplete="off">'."\n";
			echo '<input type="hidden" name="ws_plugin__s2member_options_save" id="ws-plugin--s2member-options-save" value="'.esc_attr(wp_create_nonce("ws-plugin--s2member-options-save")).'" />'."\n";
			echo '<input type="hidden" name="ws_plugin__s2member_configured" id="ws-plugin--s2member-configured" value="1" />'."\n";

			do_action("s2x_during_general_options_page_before_left_sections", get_defined_vars());

			c_ws_plugin__s2member_menu_page_general_options::render_membership_options_page_panel();
			if(apply_filters("s2x_during_general_options_page_during_left_sections_display_login_welcome_page", TRUE, get_defined_vars()))
			{
				do_action("s2x_during_general_options_page_during_left_sections_before_login_welcome_page", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="Login Welcome Page (configuration required)">'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-login-welcome-page-section">'."\n";
				echo '<h3>Login Welcome Page (required, please customize this)</h3>'."\n";
				echo '<p>Please create and/or choose an existing Page to use as the first page Members will see after logging in.</p>'."\n";
				echo (c_ws_plugin__s2member_utils_conds::bp_is_installed()) ? '<p><em><strong>BuddyPress:</strong> s2Member integrates with BuddyPress. Your Login Welcome Page affects BuddyPress too.</em></p>'."\n" : '';
				echo '<p><em><strong>Always Private:</strong> This Page will always require a logged-in User/Member. In fact, this Page will be protected from public access by s2Member automatically. <strong>Note:</strong> for technical reasons, your Login Welcome Page <strong>cannot</strong> be set to your Front Page (i.e., your Home Page); or your Posts Page (i.e., your main Blog page). Please create a separate dedicated Page in WordPress, and then designate it as your Login Welcome Page below.</em></p>'."\n";
				echo '<p><strong>See also:</strong> This KB article: <a href="http://www.s2member.com/kb/customizing-your-lwp/" target="_blank" rel="external">Customizing Your Login Welcome Page</a>.</p>'."\n";
				do_action("s2x_during_general_options_page_during_left_sections_during_login_welcome_page", get_defined_vars());

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-login-welcome-page">'."\n";
				echo 'Login Welcome Page:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<select name="ws_plugin__s2member_login_welcome_page" id="ws-plugin--s2member-login-welcome-page">'."\n";
				echo '<option value="">&mdash; Select &mdash;</option>'."\n";
				foreach(($ws_plugin__s2member_temp_a = array_merge((array)get_pages())) as $ws_plugin__s2member_temp_o)
					echo '<option value="'.esc_attr($ws_plugin__s2member_temp_o->ID).'"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_redirection_override"] && $ws_plugin__s2member_temp_o->ID == $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_welcome_page"]) ? ' selected="selected"' : '').'>'.esc_html($ws_plugin__s2member_temp_o->post_title).'</option>'."\n";
				echo '</select><br />'."\n";
				echo 'Please choose a Page to be used as the first page Members will see after logging in. This Page can contain anything you like. We recommend the following title: <code>Welcome To Our Members Area</code>.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-login-redirection-override">'."\n";
				echo 'Or, a Special Redirection URL?'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_login_redirection_override" id="ws-plugin--s2member-login-redirection-override" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["login_redirection_override"]).'" /><br />'."\n";
				echo 'Or, you may configure a Special Redirection URL, if you prefer. You\'ll need to type in the full URL, starting with: <code>http://</code> or <code>https://</code>. <em>A few <a href="#" onclick="alert(\'Replacement Codes:\\n\\n%%current_user_login%% = The current User\\\'s Username, lowercase (deprecated, please use %%current_user_nicename%%).\\n\\n%%current_user_nicename%% = The current User\\\'s Nicename in lowercase format (i.e., a cleaner version of the username for URLs; recommended for best compatibility).\\n\\n%%current_user_id%% = The current User\\\'s ID.\\n\\n%%current_user_level%% = The current User\\\'s s2Member Level.\\n\\n%%current_user_role%% = The current User\\\'s WordPress Role.'.((!is_multisite() || !c_ws_plugin__s2member_utils_conds::is_multisite_farm() || is_main_site()) ? '\\n\\n%%current_user_ccaps%% = The current User\\\'s Custom Capabilities.' : '').'\\n\\n%%current_user_logins%% = Number of times the current User has logged in.\\n\\nFor example, if you\\\'re using BuddyPress, and you want to redirect Members to their BuddyPress Profile page after logging in, you would setup a Special Redirection URL, like this: '.home_url("/members/%%current_user_nicename%%/profile/").'\\n\\nOr ... using %%current_user_level%%, you could have a separate Login Welcome Page for each Membership Level that you plan to offer. BuddyPress not required.\'); return false;">Replacement Codes</a> are also supported here.</em>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";
				echo '</div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_during_general_options_page_during_left_sections_after_login_welcome_page", get_defined_vars());
			}
			if(apply_filters("s2x_during_general_options_page_during_left_sections_display_eot_behavior", TRUE, get_defined_vars()))
			{
				do_action("s2x_during_general_options_page_during_left_sections_before_eot_behavior", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="Automatic EOT Behavior">'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-eot-behavior-section">'."\n";
				echo '<h3>PayPal EOT Behavior (required, please choose)</h3>'."\n";
				echo '<p>EOT = End Of Term. By default, s2Member will demote a paid Member to a Free Subscriber whenever their Subscription term has ended (i.e., expired), been cancelled, refunded, charged back to you, etc. s2Member demotes them to a Free Subscriber, so they will no longer have Member Level Access to your site. However, in some cases, you may prefer to have Customer accounts deleted completely, instead of just being demoted. This is where you choose which method works best for your site. If you don\'t want s2Member to take ANY action at all, you can disable s2Member\'s EOT System temporarily, or even completely. There are also a few other configurable options here, so please read carefully. These options are all very important.</p>'."\n";
				echo '<p><strong>PayPal IPNs:</strong> The PayPal IPN service will notify s2Member whenever a Member\'s payments have been failing, and/or whenever a Member\'s Subscription has expired for any reason. Even refunds &amp; chargeback reversals are supported through the IPN service. For example, if you issue a refund to an unhappy Customer through PayPal, s2Member will eventually be notified, and the account for that Customer will either be demoted to a Free Subscriber, or deleted automatically (based on your configuration). The communication from PayPal → s2Member is seamless.</p>'."\n";
				echo '<p><em><strong>Some Hairy Details:</strong> There might be times whenever you notice that a Member\'s Subscription has been cancelled through PayPal... but, s2Member continues allowing the User  access to your site as a paid Member. Please don\'t be confused by this... in 99.9% of these cases, the reason for this is legitimate. s2Member will only remove the User\'s Membership privileges when an EOT (End Of Term) is processed, a refund occurs, a chargeback occurs, or when a cancellation occurs - which would later result in a delayed Auto-EOT by s2Member.</em></p>'."\n";
				echo '<p><em>s2Member will not process an EOT until the User has completely used up the time they paid for. In other words, if a User signs up for a monthly Subscription on Jan 1st, and then cancels their Subscription on Jan 15th; technically, they should still be allowed to access the site for another 15 days, and then on Feb 1st, the time they paid for has completely elapsed. At that time, s2Member will remove their Membership privileges; by either demoting them to a Free Subscriber, or deleting their account from the system (based on your configuration). s2Member also calculates one extra day (24 hours) into its equation, just to make sure access is not removed sooner than a Customer might expect.</em></p>'."\n";
				do_action("s2x_during_general_options_page_during_left_sections_during_eot_behavior", get_defined_vars());

				echo '<p id="ws-plugin--s2member-auto-eot-system-enabled-via-cron"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["auto_eot_system_enabled"] == 2 && (!function_exists("wp_cron") || !wp_get_schedule("ws_plugin__s2member_auto_eot_system__schedule"))) ? '' : ' style="display:none;"').'>If you\'d like to run s2Member\'s Auto-EOT System through a more traditional Cron Job; instead of through <code>WP-Cron</code>, you will need to configure a Cron Job through your server control panel; provided by your hosting company. Set the Cron Job to run <code>once about every 10 minutes to an hour</code>. You\'ll want to configure an HTTP Cron Job that loads this URL:<br /><code>'.esc_html(home_url("/?s2member_auto_eot_system_via_cron=1")).'</code></p>'."\n";

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-auto-eot-system-enabled">'."\n";
				echo 'Enable s2Member\'s Auto-EOT System?'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<select name="ws_plugin__s2member_auto_eot_system_enabled" id="ws-plugin--s2member-auto-eot-system-enabled">'."\n";
				// Very advanced conditionals here. If the Auto-EOT System is NOT running, or NOT fully configured, this will indicate that no option is set - as sort of a built-in acknowledgment/warning in the UI panel.
				echo (($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["auto_eot_system_enabled"] == 1 && (!function_exists("wp_cron") || !wp_get_schedule("ws_plugin__s2member_auto_eot_system__schedule"))) || ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["auto_eot_system_enabled"] == 2 && (function_exists("wp_cron") && wp_get_schedule("ws_plugin__s2member_auto_eot_system__schedule"))) || (!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["auto_eot_system_enabled"] && (function_exists("wp_cron") && wp_get_schedule("ws_plugin__s2member_auto_eot_system__schedule")))) ? '<option value=""></option>'."\n" : '';
				echo '<option value="1"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["auto_eot_system_enabled"] == 1 && function_exists("wp_cron") && wp_get_schedule("ws_plugin__s2member_auto_eot_system__schedule")) ? ' selected="selected"' : '').'>Yes (enable the Auto-EOT System through WP-Cron)</option>'."\n";
				echo (!is_multisite() || !c_ws_plugin__s2member_utils_conds::is_multisite_farm() || is_main_site()) ? '<option value="2"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["auto_eot_system_enabled"] == 2 && (!function_exists("wp_cron") || !wp_get_schedule("ws_plugin__s2member_auto_eot_system__schedule"))) ? ' selected="selected"' : '').'>Yes (but, I\'ll run it with my own Cron Job)</option>'."\n" : '';
				echo '<option value="0"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["auto_eot_system_enabled"] && (!function_exists("wp_cron") || !wp_get_schedule("ws_plugin__s2member_auto_eot_system__schedule"))) ? ' selected="selected"' : '').'>No (disable the Auto-EOT System)</option>'."\n";
				echo '</select><br />'."\n";
				echo 'Recommended setting: (<code>Yes / enable via WP-Cron</code>)'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-membership-eot-behavior">'."\n";
				echo 'Membership EOT Behavior (Demote or Delete)?'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<select name="ws_plugin__s2member_membership_eot_behavior" id="ws-plugin--s2member-membership-eot-behavior">'."\n";
				echo '<option value="demote"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["membership_eot_behavior"] === "demote") ? ' selected="selected"' : '').'>Demote (convert them to a Free Subscriber)</option>'."\n";
				echo '<option value="delete"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["membership_eot_behavior"] === "delete") ? ' selected="selected"' : '').'>Delete (erase their account completely)</option>'."\n";
				echo '</select>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-eots-remove-ccaps">'."\n";
				echo 'Membership EOTs also Remove all Custom Capabilities?'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<select name="ws_plugin__s2member_eots_remove_ccaps" id="ws-plugin--s2member-eots-remove-ccaps">'."\n";
				echo '<option value="1"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["eots_remove_ccaps"]) ? ' selected="selected"' : '').'>Yes (an EOT also results in the loss of any Custom Capabilities a User/Member may have)</option>'."\n";
				echo '<option value="0"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["eots_remove_ccaps"]) ? ' selected="selected"' : '').'>No (an EOT has no impact on any Custom Capabilities a User/Member may have)</option>'."\n";
				echo '</select><br />'."\n";
				echo '<em>NOTE: If Refunds/Reversals trigger an Immediate EOT (see setting below); Custom Capabilities will always be removed when/if a Refund or Reversal occurs. In other words, this setting is ignored for Refunds/Reversals (IF they trigger an Immediate EOT—based on your configuration below). If you prefer to review all Refunds/Reversals for yourself, please choose that option below.</em>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-eot-grace-time">'."\n";
				echo 'EOT Grace Time (in seconds):'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_eot_grace_time" id="ws-plugin--s2member-eot-grace-time" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["eot_grace_time"]).'" /><br />'."\n";
				echo '<em>This is represented in seconds. For example, a value of: <code>86400</code> = 1 day. Your EOT Grace Time; is the amount of time you will offer as a grace period (if any). Most site owners will give customers an additional 24 hours of access; just to help avoid any negativity that may result from a customer losing access sooner than they might expect. You can disable EOT Grace Time by setting this to: <code>0</code>. Note: there is NO Grace Time applied when/if a Refund or Reversal occurs. If Refunds/Reversals trigger an Immediate EOT (see setting below); there is never any Grace Time applied in that scenario.</em>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-triggers-immediate-eot">'."\n";
				echo 'Refunds/Partial Refunds/Reversals (trigger Immediate EOT)?'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<select name="ws_plugin__s2member_triggers_immediate_eot" id="ws-plugin--s2member-triggers-immediate-eot">'."\n";
				echo '<option value="none"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["triggers_immediate_eot"] === "none") ? ' selected="selected"' : '').'>None (I\'ll review these events manually)</option>'."\n";
				echo '<option value="refunds"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["triggers_immediate_eot"] === "refunds") ? ' selected="selected"' : '').'>Full Refunds (full refunds only; ALWAYS trigger an Immediate EOT action)</option>'."\n";
				echo '<option value="reversals"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["triggers_immediate_eot"] === "reversals") ? ' selected="selected"' : '').'>Reversals (chargebacks only; ALWAYS trigger an Immediate EOT action)</option>'."\n";
				echo '<option value="refunds,reversals"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["triggers_immediate_eot"] === "refunds,reversals") ? ' selected="selected"' : '').'>Full Refunds, Reversals (these ALWAYS trigger an Immediate EOT action)</option>'."\n";
				echo '<option value="refunds,partial_refunds,reversals"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["triggers_immediate_eot"] === "refunds,partial_refunds,reversals") ? ' selected="selected"' : '').'>Full Refunds, Partial Refunds, Reversals (these ALWAYS trigger an Immediate EOT action)</option>'."\n";
				echo '</select><br />'."\n";
				echo '<em><strong>Note:</strong> s2Member is not equipped to detect partial refunds against multi-payment Subscriptions reliably. Therefore, all refunds processed against Subscriptions (of any kind) are considered <strong>Partial</strong> Refunds. Full refunds (in the eyes of s2Member) occur only against Buy Now transactions where it is easy for s2Member to see that the refund amount is &gt;= the original Buy Now purchase price (i.e., a Full Refund). <strong>Also Note:</strong> This setting (no matter what you choose) will NOT impact s2Member\'s internal API Notifications for Refund/Reversal events. <a href="#" onclick="alert(\'A Full or Partial Refund; and/or a Reversal Notification will ALWAYS be processed internally by s2Member, even if no action is taken by s2Member in accordance with your configuration here.\\n\\nIn this way, you\\\'ll have the full ability to listen for these events on your own (via API Notifications); if you prefer (optional). For more information, check your Dashboard under: `s2Member → API Notifications → Refunds/Reversals`.\'); return false;">Click here for details</a>.</em>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-eot-time-ext-behavior">'."\n";
				echo 'Fixed-Term Extensions (Auto-Extend)?'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<select name="ws_plugin__s2member_eot_time_ext_behavior" id="ws-plugin--s2member-eot-time-ext-behavior">'."\n";
				echo '<option value="extend"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["eot_time_ext_behavior"] === "extend") ? ' selected="selected"' : '').'>Yes (default, automatically extend any existing EOT Time)</option>'."\n";
				echo '<option value="reset"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["eot_time_ext_behavior"] === "reset") ? ' selected="selected"' : '').'>No (do NOT extend; s2Member should reset EOT Time completely)</option>'."\n";
				echo '</select><br />'."\n";
				echo '<em>This setting will only affect Buy Now transactions for fixed-term lengths. By default, s2Member will automatically extend any existing EOT Time that a Customer may have. For example, if I buy one year of access, and then I buy another year of access (before my first year is totally used up); I end up with everything I paid you for (now over 1 year of access) if this is set to <code>Yes</code>. If this was set to <code>No</code>, the EOT Time would be reset when I make the second purchase; leaving me with only 1 year of access, starting the date of my second purchase.</em>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";
				echo '</div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_during_general_options_page_during_left_sections_after_eot_behavior", get_defined_vars());
			}
			if(apply_filters("s2x_during_general_options_page_during_left_sections_display_uninstall", (!is_multisite() || !c_ws_plugin__s2member_utils_conds::is_multisite_farm() || is_main_site() || is_super_admin()), get_defined_vars()))
			{
				do_action("s2x_during_general_options_page_during_left_sections_before_uninstall", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="Plugin Deletion Safeguards"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["run_uninstall_routines"]) ? ' default-state="open"' : '').'>'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-uninstall-section">'."\n";
				echo '<h3>Plugin Deletion Safeguards (highly recommended)</h3>'."\n";
				echo '<p>By default, s2Member will retain all of it\'s Roles, Capabilities, and your Configuration Options when/if you delete s2Member from the Plugins Menu in WordPress. However, if you would like for s2Member to erase itself completely, please choose: <code>No (upon deletion, erase all data/options)</code>. See also: <a href="http://s2member.com/kb-article/how-do-i-uninstall-s2member/" target="_blank" rel="external">s2Member Uninstall Instructions</a></p>';
				do_action("s2x_during_general_options_page_during_left_sections_during_uninstall", get_defined_vars());

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-run-uninstall-routines">'."\n";
				echo 'Safeguard s2Member Data/Options?'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<select name="ws_plugin__s2member_run_uninstall_routines" id="ws-plugin--s2member-run-uninstall-routines">'."\n";
				echo '<option value="0"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["run_uninstall_routines"]) ? ' selected="selected"' : '').'>Yes (safeguard all data/options)</option>'."\n";
				echo '<option value="1"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["run_uninstall_routines"]) ? ' selected="selected"' : '').'>No (upon deletion, erase all data/options)</option>'."\n";
				echo '</select><br />'."\n";
				echo 'Recommended setting: (<code>Yes, safeguard all data/options</code>)'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";
				echo '</div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_during_general_options_page_during_left_sections_after_uninstall", get_defined_vars());
			}
			if(apply_filters("s2x_during_general_options_page_during_left_sections_display_security", TRUE, get_defined_vars()))
			{
				do_action("s2x_during_general_options_page_during_left_sections_before_security", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="Security Encryption Key">'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-security-section">'."\n";
				echo '<img src="'.esc_attr($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["dir_url"]).'/src/images/large-icon.png" title="s2Member (a Membership management system for WordPress)" alt="" style="float:right; margin:0 0 0 25px; border:0;" />'."\n";
				echo '<h3>Security Encryption Key (optional, for tighter security)</h3>'."\n";
				echo '<p>Just like WordPress, s2Member is open-source software. Which is wonderful. However, this also makes it possible for anyone to grab a copy of the software, and try to learn their way around its security measures. In order to keep your installation of s2Member unique/secure, you should configure a Security Encryption Key. s2Member will use your Security Encryption Key to protect itself against hackers. It does this by encrypting all sensitive information with your Key. A Security Encryption Key is unique to your installation.</p>'."\n";
				echo '<p>Once you configure this, you <em>do not</em> want to change it—ever! In fact, it is a <em>very</em> good idea to keep this backed up in a safe place, just in case you need to move your site, or re-install s2Member in the future. Some of the sensitive data that s2Member stores will be encrypted with this Key. If you change it, that data can no longer be read, even by s2Member itself. In other words, don\'t use s2Member for six months, then decide to change your Key. That would break your installation.</p>'."\n";
				do_action("s2x_during_general_options_page_during_left_sections_during_security", get_defined_vars());

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-sec-encryption-key">'."\n";
				echo 'Security Encryption Key (at least 60 chars)'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["sec_encryption_key"]) ? ' <a href="#" onclick="ws_plugin__s2member_enableSecurityKey(); return false;" title="(not recommended)">edit key</a>' : ' <a href="#" onclick="ws_plugin__s2member_generateSecurityKey(); return false;" title="Insert an auto-generated Key. (recommended)">auto-generate</a>')."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" maxlength="256" autocomplete="off" name="ws_plugin__s2member_sec_encryption_key" id="ws-plugin--s2member-sec-encryption-key" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["sec_encryption_key"]).'"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["sec_encryption_key"]) ? ' disabled="disabled"' : '').' />'."\n";
				echo (!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["sec_encryption_key"]) ? '<br />This may contain letters, numbers, spaces; even punctuation. Up to 256 characters.<br /><em>Ex: <code>'.esc_html(strtoupper(c_ws_plugin__s2member_utils_strings::random_str_gen(64))).'</code></em>'."\n" : '';
				echo (count($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["sec_encryption_key_history"]) > 1) ? '<br /><a href="#" onclick="ws_plugin__s2member_securityKeyHistory(); return false;">Click here</a> for a history of your last 10 Encryption Keys.<div id="ws-plugin--s2member-sec-encryption-key-history" style="display:none;"><code>'.implode('</code><br /><code>', $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["sec_encryption_key_history"]).'</code></div>'."\n" : '';
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<h3>Additional Details Regarding this Key:</h3>'."\n";
				echo '<p>Your Security Encryption Key is used throughout s2Member\'s source code for many different things. However, most (not all, but most) uses of this Key are related to transactional processing within a particular session; so changing the Key won\'t really impact these scenarios in any significant way. Your Security Encryption Key is simply there to enhance security of data that is being transmitted in these cases.</p>'."\n";
				echo '<p>That said, there are a few scenarios where use of your Security Encryption Key is more long-term. These include: Specific Post/Page Access Links, Registration Access Links, and it can also have a long-term impact on IPN communication because some data analyzed by s2Member includes a checksum that depends on your Key. If the Key changes, it could cause future IPN data (i.e., data from your payment gateway) to fail validation.</p>'."\n";

				echo '</div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_during_general_options_page_during_left_sections_after_security", get_defined_vars());
			}
			if(apply_filters("s2x_during_general_options_page_during_left_sections_display_lazy_load", (!is_multisite() || !c_ws_plugin__s2member_utils_conds::is_multisite_farm() || is_main_site() || is_super_admin()), get_defined_vars()))
			{
				do_action("s2x_during_general_options_page_during_left_sections_before_lazy_load", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="CSS/JS Lazy Loading">'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-lazy-load-section">'."\n";
				echo '<h3>CSS/JS Lazy Loading (Client-Side Libraries)</h3>'."\n";
				echo '<p>By default, s2Member will load it\'s CSS/JS libraries on every page of your site. However, you may wish to enable lazy-loading here (i.e., only load when absolutely necessary).</p>'."\n";
				echo '<p><em><strong>Tip:</strong> Do you need s2Member\'s CSS/JS on every page? If not, you can turn lazy-loading on. If you need s2Member\'s CSS/JS on a given Post/Page, you can insert an HTML comment into the Post/Page content like this: <code>&lt;!--s2member--&gt;</code>. If a Post/Page contains the word <code>s2member</code> or an <code>[s2*</code> Shortcode, this will automatically trigger s2Member\'s lazy-load routine (no matter what you configure here). Thus, it\'s an easy way to force s2Member to load it\'s CSS/JS on specific Posts/Pages where you deem this necessary. There is also a WordPress filter available: <code>add_filter("ws_plugin__s2member_lazy_load_css_js", "__return_true");</code> for developers. This could be incorporated into more dynamic scenarios.</em></p>'."\n";
				do_action("s2x_during_general_options_page_during_left_sections_during_lazy_load", get_defined_vars());

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-lazy-load-css-js">'."\n";
				echo 'Lazy-Load s2Member\'s CSS/JS Libraries?'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<select name="ws_plugin__s2member_lazy_load_css_js" id="ws-plugin--s2member-lazy-load-css-js">'."\n";
				echo '<option value="0"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["lazy_load_css_js"]) ? ' selected="selected"' : '').'>No (always load the CSS/JS libraries; i.e., on every page of the site)</option>'."\n";
				echo '<option value="1"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["lazy_load_css_js"]) ? ' selected="selected"' : '').'>Yes (lazy-load CSS/JS libraries; i.e., load only when absolutely necessary)</option>'."\n";
				echo '</select>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";
				echo '</div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_during_general_options_page_during_left_sections_after_lazy_load", get_defined_vars());
			}
			if(apply_filters("s2x_during_general_options_page_during_left_sections_display_url_shortening", TRUE, get_defined_vars()))
			{
				do_action("s2x_during_general_options_page_during_left_sections_before_url_shortening", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="URL Shortening Service Preference">'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-url-shortening-section">'."\n";
				echo '<h3>URL Shortening Service API (Preference)</h3>'."\n";
				echo '<p>In a few special cases, long URLs generated by s2Member (i.e., those containing encrypted authentication details) will be shortened. URLs are shortened automatically, using one of the URL Shortening APIs <em>(listed below)</em>. A shortened URL prevents issues with very long links becoming corrupted by a Customer\'s email application. For instance, the Signup Confirmation Email that s2Member sends out to a new paying Customer may contain a link which is shortened to prevent corruption by email applications. By default, s2Member uses the tinyURL API. tinyURL has proven itself to be the most reliable. However, in cases where an API service call fails, s2Member will automatically use one or more of its other APIs as a backup. The option below allows you to configure which URL Shortening API s2Member should try first <em>(i.e., the one you prefer)</em>.</p>'."\n";
				do_action("s2x_during_general_options_page_during_left_sections_during_url_shortening", get_defined_vars());

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-default-url-shortener">'."\n";
				echo 'URL Shortening Service API (Default):'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<select name="ws_plugin__s2member_default_url_shortener" id="ws-plugin--s2member-default-url-shortener">'."\n";
				echo '<option value="none"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["default_url_shortener"] === "none") ? ' selected="selected"' : '').'>None (Don\'t shorten the URL)</option>'."\n";
				echo '<option value="tiny_url"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["default_url_shortener"] === "tiny_url") ? ' selected="selected"' : '').'>tinyurl.com (free; no API key)</option>'."\n";
				echo '<option value="bitly"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["default_url_shortener"] === "bitly") ? ' selected="selected"' : '').'>Bitly (free; API key required)</option>'."\n";
				echo '</select>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="password" autocomplete="off" name="ws_plugin__s2member_default_url_shortener_key" id="ws-plugin--s2member-default-url-shortener-key" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["default_url_shortener_key"]).'" /><br />'."\n";
				echo '<em>If you choose Bitly, enter your <a href="http://s2member.com/r/bitly-oauth-access-token/" target="_blank" rel="external">Bitly oAuth Access Token</a>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				if (c_ws_plugin__s2member_utils_conds::pro_is_installed()) {
					echo '<tr>'."\n";

					echo '<th>'."\n";
					echo '<label for="ws-plugin--s2member-default-custom-str-url-shortener">'."\n";
					echo 'Custom URL Shortening Service API (Optional/Advanced):'."\n";
					echo '</label>'."\n";
					echo '</th>'."\n";

					echo '</tr>'."\n";
					echo '<tr>'."\n";

					echo '<td>'."\n";
					echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_default_custom_str_url_shortener" id="ws-plugin--s2member-default-custom-str-url-shortener" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["default_custom_str_url_shortener"]).'" /><br />'."\n";
					echo 'Your own custom URL <code>(i.e., GET request)</code>, with <code>%%s2_long_url%%</code> Replacement Code. [<a href="#" onclick="alert(\'Advanced site owners can use a custom URL shortening service they prefer.\\n\\nIn order for this to work, your URL shortening service must support simple GET requests through its API (sometimes referred to as a REST or NVP API).\\n\\nIn addition, your URL shortening service must return a plain-text URL in the response. See example below.\\n\\nYOURLS example GET request with format=simple:\\n\\nhttp://yoursite.com/yourls/yourls-api.php?signature=1234567890&action=shorturl&format=simple&url=%%s2_long_url%%\\n\\ns2Member expects a shortened URL in the response from YOURLS.\\n\\nIf you configure s2Member to use your own custom URL shortening service, s2Member will try your configuration first, and if anything fails, it will fall back on its own pre-integrated backups.\\n\\nWhen configuring your URL for the GET request, s2Member makes two Replacement Codes available:\\n\\n%%s2_long_url%% = The full URL that needs to be shortened (raw URL-encoded).\\n\\n%%s2_long_url_md5%% = An MD5 hash of the full URL (might be useful in some APIs).\'); return false;" tabindex="-1">details</a>]<br />'."\n";
					echo '</td>'."\n";

					echo '</tr>'."\n";
				}
				echo '</tbody>'."\n";
				echo '</table>'."\n";
				echo '</div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_during_general_options_page_during_left_sections_after_url_shortening", get_defined_vars());
			}
			if(apply_filters("s2x_during_general_options_page_during_left_sections_display_log_settings", TRUE, get_defined_vars()))
			{
				do_action("s2x_during_general_options_page_during_left_sections_before_log_settings", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="Logging Configuration">'."\n";
				echo '<div class="ws-menu-page-section ws-plugin--s2member-log-settings-section">'."\n";

				echo '<h3>Logging Configuration</h3>'."\n";

				echo '<div class="info">'."\n";
				echo '<p style="margin-top:0;"><span>We highly recommend that you enable logging during your initial testing phase. Logs produce lots of useful details that can help in debugging. Logs can help you find issues in your configuration and/or problems that occur during processing. Enable logging here, and then view your log files below, in the s2Member Log Viewer.</span></p>'."\n";
				echo '<p style="margin-bottom:0;"><span class="ws-menu-page-error">However, it is very important to disable logging once you go live. Log files may contain personally identifiable information, credit card numbers, secret API credentials, passwords and/or other sensitive information. We strongly suggest that logging be disabled on a live site (for security reasons).</span></p>'."\n";
				echo '</div>'."\n";

				echo '<div class="warning" style="margin-bottom:0;">'."\n";
				echo '<p style="margin:0;"><span>Regarding s2Member Security Badges. If debug logging is enabled, your site will not qualify for an s2Member Security Badge until you disable logging (and you must also download, and then delete any existing log files). For further details, please see KB Article: <a href="http://www.s2member.com/kb/security-badges/" target="_blank" rel="external">s2Member Security Badges</a>.</span></p>'."\n";
				echo '</div>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				do_action("s2x_during_general_options_page_during_left_sections_during_log_settings", get_defined_vars());

				echo '<form method="post" name="ws_plugin__s2member_options_form" id="ws-plugin--s2member-options-form" autocomplete="off">'."\n";
				echo '<input type="hidden" name="ws_plugin__s2member_options_save" id="ws-plugin--s2member-options-save" value="'.esc_attr(wp_create_nonce("ws-plugin--s2member-options-save")).'" />'."\n";

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-gateway-debug-logs">'."\n";
				echo 'Enable Logging Routines?'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="radio" name="ws_plugin__s2member_gateway_debug_logs" id="ws-plugin--s2member-gateway-debug-logs-0" value="0"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["gateway_debug_logs"]) ? ' checked="checked"' : '').' /> <label for="ws-plugin--s2member-gateway-debug-logs-0">No</label> &nbsp;&nbsp;&nbsp; <input type="radio" name="ws_plugin__s2member_gateway_debug_logs" id="ws-plugin--s2member-gateway-debug-logs-1" value="1"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["gateway_debug_logs"]) ? ' checked="checked"' : '').' /> <label for="ws-plugin--s2member-gateway-debug-logs-1">Yes, enable debugging w/ HTTP, API, IPN &amp; Return Page logging (and List Server API logs too).</label><br />'."\n";
				echo '<em>This enables logging overall. Includes s2Member HTTP, API, IPN and Return Page logging. Also logs any List Server integrations.</em><br />'."\n";
				echo '<em>* Use only for debugging. This should NEVER be enabled on a live site.<br />* The log files are stored here: <code>'.esc_html(c_ws_plugin__s2member_utils_dirs::doc_root_path($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["logs_dir"])).'</code></em>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-gateway-debug-logs-extensive">'."\n";
				echo 'Enable Additional Logging Routines?'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="radio" name="ws_plugin__s2member_gateway_debug_logs_extensive" id="ws-plugin--s2member-gateway-debug-logs-extensive-0" value="0"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["gateway_debug_logs_extensive"]) ? ' checked="checked"' : '').' /> <label for="ws-plugin--s2member-gateway-debug-logs-extensive-0">No</label> &nbsp;&nbsp;&nbsp; <input type="radio" name="ws_plugin__s2member_gateway_debug_logs_extensive" id="ws-plugin--s2member-gateway-debug-logs-extensive-1" value="1"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["gateway_debug_logs_extensive"]) ? ' checked="checked"' : '').' /> <label for="ws-plugin--s2member-gateway-debug-logs-extensive-1">Yes, enable debugging w/ HTTP connection logging for ALL of WordPress.</label><br />'."\n";
				echo '<em>This enables HTTP connection logging for ALL of WordPress (quite extensive).<br />* Use only for debugging. This should NEVER be enabled on a live site.<br />* Creates the additional log file: <code>wp-http-api-debug.log</code></em>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<p class="submit">'."\n";
				echo '<input type="submit" value="Update Logging Configuration" />'."\n";
				echo '</p>'."\n";

				echo '</form>'."\n";

				echo '</div>'."\n";
				echo '</div>'."\n";

				do_action("s2x_during_general_options_page_during_left_sections_after_log_settings", get_defined_vars());
			}

			do_action("s2x_during_general_options_page_after_left_sections", get_defined_vars());

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

		/**
		 * @attaches-to ``add_action('s2x_during_res_ops_page_during_left_sections_before_post_level_access');``
		 */
		static public function render_membership_options_page_panel() {
			if(apply_filters("s2x_display_membership_options_page_panel", TRUE, get_defined_vars()))
			{
				do_action("s2x_before_membership_options_page_panel", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="Membership Options Page (configuration required)">'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-membership-options-page-section">'."\n";
				echo '<h3>Membership Options Page (required, please customize this)</h3>'."\n";
				echo '<p>Please create and/or choose an existing Page that showcases your Membership Options. This special Page is where you\'ll insert the Payment Button(s) generated for you by s2Member (or Pro-Forms; if you\'re running s2Member Pro). This Page serves as your lead-in signup page <em>(i.e., you\'ll give visitors one or more registration options here, and they\'ll pay for the option they choose)</em>.</p>'."\n";
				echo '<p>Your Membership Options Page should detail all of the features that come with Membership to your site, and provide a Payment Button (or Pro-Form; if you\'re running s2Member Pro) for each Level of access you plan to offer. This is also the Page that anyone could be redirected to <em>(by s2Member)</em>, should they attempt to access an area of your site that requires access to something they\'re currenty not allowed to view.</p>'."\n";
				echo '<p><em><strong>Tip:</strong> If you allow Open Registration (i.e., Free Subscribers), you might want to place a link on your Membership Options Page, which points directly to your free Registration Form, instead of routing a Customer through your Payment Gateway first. It\'s a matter of preference though. For further details, please check the section above: <strong>s2Member → General Options → Open Registration</strong>.</em></p>'."\n";
				echo c_ws_plugin__s2member_utils_conds::bp_is_installed() ? '<p><em><strong>BuddyPress:</strong> Even with BuddyPress, s2Member still needs a Membership Options Page. This is where your Payment Buttons (or Pro-Forms; if you\'re running s2Member Pro) will go, giving people the ability to pay you. And again, this is also the Page that anyone could be redirected to <em>(by s2Member)</em>, should they attempt to access an area of your site that requires access to something they\'re currenty not allowed to view.</em></p>'."\n" : '';
				echo '<p><em><strong>Always Public:</strong> This Page must be public at all times. In fact, s2Member will not allow this Page to be restricted in any way. <strong>Note:</strong> for technical reasons, your Membership Options Page <strong>cannot</strong> be set to your Front Page (i.e., your Home Page); or your Posts Page (i.e., your main Blog page). Please create a separate (dedicated) Page in WordPress, and then designate it as your Membership Options Page below.</em></p>'."\n";
				do_action("s2x_during_general_options_page_during_left_sections_during_membership_options_page", get_defined_vars());

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-membership-options-page">'."\n";
				echo 'Membership Options Page:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<select name="ws_plugin__s2member_membership_options_page" id="ws-plugin--s2member-membership-options-page">'."\n";
				echo '<option value="">&mdash; Select &mdash;</option>'."\n";
				foreach(($ws_plugin__s2member_temp_a = array_merge((array)get_pages())) as $ws_plugin__s2member_temp_o)
					echo '<option value="'.esc_attr($ws_plugin__s2member_temp_o->ID).'"'.(($ws_plugin__s2member_temp_o->ID == $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["membership_options_page"]) ? ' selected="selected"' : '').'>'.esc_html($ws_plugin__s2member_temp_o->post_title).'</option>'."\n";
				echo '</select><br />'."\n";
				echo 'Please choose a Page that provides Users with a way to sign up. We recommend the following title: <code>Membership Signup</code>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-membership-options-page-vars-enable">'."\n";
				echo 'Enable MOP Vars (i.e., Membership Options Page Variables)?'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<select name="ws_plugin__s2member_membership_options_page_vars_enable" id="ws-plugin--s2member-membership-options-page-vars-enable">'."\n";
				echo '<option value="1"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["membership_options_page_vars_enable"]) ? ' selected="selected"' : '').'>Yes (enable MOP Vars in all redirections; recommended behavior)</option>'."\n";
				echo '<option value="0"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["membership_options_page_vars_enable"]) ? ' selected="selected"' : '').'>No (don\'t include the additional details provided by MOP Vars)</option>'."\n";
				echo '</select><br />'."\n";
				echo 'See also: <strong>Dashboard → s2Member → API / Scripting → Membership Options Page / Variables</strong><br />'."\n";
				echo 'Recommended setting: (<code>Yes, enable MOP Vars</code>)'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";
				echo '</div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_after_membership_options_page_panel", get_defined_vars());
			}
		}
	}
}
