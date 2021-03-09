<?php
// @codingStandardsIgnoreFile
/**
 * Menu page for the s2Member plugin (Tools page).
 *
 * Copyright: © 2009-2021
 * {@link http://websharks-inc.com/ WebSharks, Inc.}
 *
 * Released under the terms of the GNU General Public License.
 * You should have received a copy of the GNU General Public License,
 * along with this software. In the main directory, see: /licensing/
 * If not, see: {@link http://www.gnu.org/licenses/}.
 *
 * @package s2Member\Menu_Pages
 * @since 210208
 */
if(!defined('WPINC')) // MUST have WordPress.
	exit("Do not access this file directly.");

if(!class_exists("c_ws_plugin__s2member_menu_page_tools"))
{
	/**
	 * Menu page for the s2Member plugin (General Options page).
	 *
	 * @package s2Member\Menu_Pages
	 * @since 210208
	 */
	class c_ws_plugin__s2member_menu_page_tools {
		public function __construct() {
			echo '<div class="wrap ws-menu-page">'."\n";

			echo '<div class="wp-header-end"></div>'."\n";

			echo '<div class="ws-menu-page-toolbox">'."\n";
			c_ws_plugin__s2member_menu_pages_tb::display();
			echo '</div>'."\n";

			echo '<h2>Tools</h2>'."\n";

			echo '<table class="ws-menu-page-table">'."\n";
			echo '<tbody class="ws-menu-page-table-tbody">'."\n";
			echo '<tr class="ws-menu-page-table-tr">'."\n";
			echo '<td class="ws-menu-page-table-l">'."\n";

			echo '<form method="post" name="ws_plugin__s2member_options_form" id="ws-plugin--s2member-options-form" autocomplete="off">'."\n";
			echo '<input type="hidden" name="ws_plugin__s2member_options_save" id="ws-plugin--s2member-options-save" value="'.esc_attr(wp_create_nonce("ws-plugin--s2member-options-save")).'" />'."\n";
			echo '<input type="hidden" name="ws_plugin__s2member_configured" id="ws-plugin--s2member-configured" value="1" />'."\n";

			do_action("s2x_during_tools_page_before_left_sections", get_defined_vars());

			if(apply_filters("s2x_during_tools_page_during_left_sections_display_s_badge_wp_footer_code", TRUE, get_defined_vars()))
			{
				do_action("s2x_during_tools_page_during_left_sections_before_s_badge_wp_footer_code", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="s2Member Security Badge">'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-s-badge-wp-footer-code-section">'."\n";
				echo '<h3>Security Badge &amp; Footer Configuration (optional)</h3>'."\n";
				echo '<div class="ws-menu-page-right">'.c_ws_plugin__s2member_utilities::s_badge_gen("1", TRUE, TRUE).'</div>'."\n";
				echo '<p>An s2Member Security Badge can be used to express your site\'s concern for security. To qualify your site, you must enable the Badge Status API (see below), and you must <a href="http://www.s2member.com/kb/security-badges/" target="_blank" rel="external">properly configure all security features in WordPress &amp; s2Member</a>. If you enable the Badge Status API, s2Member will make a connection to your site <strong>once per day</strong>, to test your status. Once your status is <code>1</code> (secure), <strong>it can then take up to 12 hours</strong> for your s2Member Security Badge image to show a green status for the first time.</p>'."\n";
				echo '<p><strong>How does s2Member know when my site is secure?</strong><br />If enabled below, an API call for "Security Badge Status" will allow web service connections to determine your status. For example, clicking <a href="'.esc_attr(home_url("/?s2member_s_badge_status=1")).'" target="_blank" rel="external">this link</a> will report <code>1</code> (secure), <code>0</code> (at risk), or <code>-</code> (API disabled). Once you have <a href="http://www.s2member.com/kb/security-badges/" target="_blank" rel="external">properly configured all security features in WordPress &amp; s2Member</a>, the s2Member Badge Status API will report <code>1</code> (secure) for your installation. <strong>Note:</strong> this simple API will not, and should not, report any other information. It will only report the current status of your Security Badge, as determined by your installation of s2Member.</p>'."\n";
				do_action("s2x_during_tools_page_during_left_sections_during_s_badge_wp_footer_code", get_defined_vars());

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-s-badge-status-enabled">'."\n";
				echo 'Enable Security Badge Status API?'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<select name="ws_plugin__s2member_s_badge_status_enabled" id="ws-plugin--s2member-s-badge-status-enabled">'."\n";
				echo '<option value="0"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["s_badge_status_enabled"]) ? ' selected="selected"' : '').'>No (default, Badge Status API is disabled)</option>'."\n";
				echo '<option value="1"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["s_badge_status_enabled"]) ? ' selected="selected"' : '').'>Yes (enable Badge Status API for verification)</option>'."\n";
				echo '</select><br />'."\n";
				echo '<em>This must be enabled if you want s2Member to verify your Security Badge each day.</em>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-wp-footer-code">'."\n";
				echo 'Customize WordPress Footer:<br />'."\n";
				echo '<small>[ <a href="#" onclick="this.$code = jQuery(\'textarea#ws-plugin--s2member-wp-footer-code\'); this.$code.val(jQuery.trim(unescape(\''.rawurlencode('[s2Member-Security-Badge v="1" /]').'\')+\'\n\'+this.$code.val())); return false;">Click HERE to insert your Security Badge</a> ],<br />or use Shortcode <code>[s2Member-Security-Badge v="1" /]</code> in a Post/Page/Widget.<br />The <code>v="1"</code> attribute is a Security Badge style/variation. Try variations <code>1|2|3</code>.</small>'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<textarea name="ws_plugin__s2member_wp_footer_code" id="ws-plugin--s2member-wp-footer-code" rows="8" wrap="off" spellcheck="false">'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["wp_footer_code"]).'</textarea><br />'."\n";
				echo 'Any valid XHTML / JavaScript'.((is_multisite() && c_ws_plugin__s2member_utils_conds::is_multisite_farm() && !is_main_site()) ? '' : ' (or even PHP)').' code will work just fine here.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";
				echo '</div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_during_tools_page_during_left_sections_after_s_badge_wp_footer_code", get_defined_vars());
			}

			if(apply_filters("s2x_during_tools_page_during_left_sections_display_bbpress", TRUE, get_defined_vars()))
			{
				do_action("s2x_during_tools_page_during_left_sections_before_bbpress", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="bbPress Plugin Integration (2.0+ plugin version)">'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-bbpress-section">'."\n";
				echo '<h3>bbPress Plugin Integration (easy peasy)</h3>'."\n";
				echo '<input type="button" value="Update Roles/Capabilities" class="ws-menu-page-right ws-plugin--s2member-update-roles-button" style="min-width:175px;" />'."\n";
				echo '<p>The plugin version of <a href="http://s2member.com/r/bbpress/" target="_blank" rel="external">bbPress 2.0+</a> integrates seamlessly with WordPress. If bbPress was already installed when you activated s2Member, your s2Member Roles/Capabilities are already configured to work in harmony with bbPress. If you didn\'t, you can simply click the "Update Roles/Capabilities" button here. That\'s all it takes. Once your Roles/Capbilities are updated, s2Member and bbPress are fully integrated with each other.</p>'."\n";
				echo '<p><strong>See also:</strong> This KB article: <a href="http://www.s2member.com/kb/roles-caps/#s2-roles-caps" target="_blank" rel="external">s2Member Roles/Capabilities (Including <strong>bbPress</strong> Support)</a>.</p>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<h3>bbPress Forums and s2Member Roles/Capabilities</h3>'."\n";
				echo '<p>s2Member configures your Membership Roles (by default, these include: <em>s2Member Level 1</em>, <em>s2Member Level 2</em>, <em>s2Member Level 3</em>, <em>s2Member Level 4</em>), with a default set of bbPress permissions that allow all Members to both spectate and particpate in your forums, just as if they were a WordPress Subscriber Role (or a bbPress Participant Role).</p>'."\n";
				echo '<p>bbPress also adds some new Roles (dynamic Roles in bbPress 2.2+) to your WordPress installation. These include but are not limited to: <em>Keymaster</em> and <em>Moderator</em>. s2Member allows Forum Keymasters &amp; Moderators full access to the highest Membership Level you offer; just like it does with <em>Administrators</em>, <em>Editors</em>, <em>Authors</em>, and <em>Contributors</em>.</p>'."\n";
				echo '<p><strong>Membership Levels provide incremental access:</strong></p>'."\n";
				echo '<p>* A Member with Level 4 access, will also be able to access Levels 0, 1, 2 &amp; 3 <em>(plus spectate/participate in bbPress Forums)</em>.<br />* A Member with Level 3 access, will also be able to access Levels 0, 1 &amp; 2 <em>(plus spectate/participate in bbPress Forums)</em>.<br />* A Member with Level 2 access, will also be able to access Levels 0 &amp; 1 <em>(plus spectate/participate in bbPress Forums)</em>.<br />* A Member with Level 1 access, will also be able to access Level 0 <em>(plus spectate/participate in bbPress Forums)</em>.<br />* A Subscriber with Level 0 access, will ONLY be able to access Level 0 <em>(plus spectate/participate in bbPress Forums)</em>.<br />* A public Visitor will have NO access to protected content <em>(and no special access to bbPress Forums)</em>.</p>'."\n";
				echo '<p><em>* WordPress Subscribers <strong class="ws-menu-page-hilite">and bbPress Spectators/Participants</strong> are at Membership Level 0. If you\'re allowing Open Registration via s2Member, Subscribers will be at Level 0 (a Free Subscriber).</em></p>'."\n";
				echo '<p><em>* WordPress Administrators, Editors, Authors, Contributors, <strong class="ws-menu-page-hilite">and bbPress Keymasters/Moderators</strong> have Level 4 access, with respect to s2Member. All of their other Roles/Capabilities are left untouched.</em></p>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<h3>Protecting Content Introduced by bbPress</h3>'."\n";
				echo '<p>You can protect individual Forum Topics/Posts/Replies at different Levels with s2Member, or even with Custom Capabilities. Forum Topics/Posts/Replies are integrated by bbPress internally as "Custom Post Types", which can be protected by s2Member either through Post Level Access Restrictions, or through URI Level Access Restrictions. If you choose to use Post Level Access Restrictions, please remember that s2Member will provide you with drop-down menus whenever you add or edit Forum Topics/Posts/Replies to make things easier for you.</p>'."\n";
				echo '<p>You\'ll be happy to know that protecting a bbPress Forum will also (automatically) protect all Topics within that Forum. In other words, if you require a certain Membership Level to access a particular bbPress Forum (or if you require a certain Custom Capability to access a particular Forum), all Topics in that Forum will also require the same.</p>'."\n";

				do_action("s2x_during_tools_page_during_left_sections_during_bbpress", get_defined_vars());
				echo '</div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_during_tools_page_during_left_sections_after_bbpress", get_defined_vars());
			}

			// Member Registration Access Links

			echo '<div class="ws-menu-page-group" title="Member Registration Access Links">'."\n";

			echo '<div class="ws-menu-page-section ws-plugin--s2member-pro-reg-links-section">'."\n";
			echo '<h3>Registration Access Link Generator (for Customer Service)</h3>'."\n";
			echo '<p>s2Member Pro-Forms consolidate the Registration/Checkout process into a single-step solution, so it is unlikely that you will ever need this tool. That being said, if you DO need to deal with a Customer Service issue that requires a simple paid Registration Access Link to be created manually, you can use this tool for that. Alternatively, you can create their account yourself/manually by going to <strong>Users → Add New</strong>. Either of these methods will work fine.</p>'."\n";

			echo '<table class="form-table">'."\n";
			echo '<tbody>'."\n";
			echo '<tr>'."\n";

			echo '<td>'."\n";
			echo '<form onsubmit="return false;" autocomplete="off">'."\n";
			echo '<p>Paid Membership Level#: <select id="ws-plugin--s2member-pro-reg-link-level">'."\n";
			for($n = 1; $n <= $GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["levels"]; $n++)
				echo '<option value="'.$n.'">s2Member Level #'.$n.'</option>'."\n";
			echo '</select></p>'."\n";
			echo '<p>Paid Subscr. ID: <input type="text" autocomplete="off" id="ws-plugin--s2member-pro-reg-link-subscr-id" value="" size="50" /> <a href="#" onclick="alert(\'The Customer\\\'s Paid Subscr. ID (aka: Recurring Profile ID, Transaction ID) must be unique. This value can be obtained from inside your PayPal account in the History tab. Each paying Customer MUST be associated with a unique Paid Subscr. ID. If the Customer is NOT associated with a Paid Subscr. ID, you will need to generate a unique value for this field on your own. But keep in mind, s2Member will be unable to maintain future communication with the PayPal IPN (i.e., Notification) service if this value does not reflect a real Paid Subscr. ID that exists in your PayPal History log.\'); return false;" tabindex="-1">[?]</a></p>'."\n";
			echo '<p>Custom String Value: <input type="text" autocomplete="off" id="ws-plugin--s2member-pro-reg-link-custom" value="'.esc_attr($_SERVER["HTTP_HOST"]).'" size="30" /> <a href="#" onclick="alert(\'A Paid Subscription is always associated with a Custom String that is passed through the custom=\\\'\\\''.c_ws_plugin__s2member_utils_strings::esc_js_sq(esc_attr($_SERVER["HTTP_HOST"]), 3).'\\\'\\\' attribute of your Shortcode. This Custom Value, MUST always start with your domain name. However, you can also pipe delimit additional values after your domain, if you need to.\\n\\nFor example:\n'.c_ws_plugin__s2member_utils_strings::esc_js_sq(esc_attr($_SERVER["HTTP_HOST"]), 3).'|cv1|cv2|cv3\'); return false;" tabindex="-1">[?]</a> <input type="button" value="Generate Access Link" onclick="ws_plugin__s2member_pro_paypalRegLinkGenerate();" /> <img id="ws-plugin--s2member-pro-reg-link-loading" src="'.esc_attr($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["dir_url"]).'/src/images/ajax-loader.gif" alt="" style="display:none;" /></p>'."\n";
			echo '<p'.((is_multisite() && c_ws_plugin__s2member_utils_conds::is_multisite_farm() && !is_main_site()) ? ' style="display:none;"' : '').'>Custom Capabilities (comma-delimited) <a href="#" onclick="alert(\'Optional. This is VERY advanced.\\nSee: s2Member → API Scripting → Custom Capabilities.\'); return false;" tabindex="-1">[?]</a> <input type="text" maxlength="125" autocomplete="off" id="ws-plugin--s2member-pro-reg-link-ccaps" size="40" onkeyup="if(this.value.match(/[^a-z_0-9,]/)) this.value = jQuery.trim (jQuery.trim (this.value).replace (/[ \-]/g, \'_\').replace (/[^a-z_0-9,]/gi, \'\').toLowerCase ());" /></p>'."\n";
			echo '<p>Fixed Term Length (for Buy Now transactions): <input type="text" autocomplete="off" id="ws-plugin--s2member-pro-reg-link-fixed-term" value="" size="10" /> <a href="#" onclick="alert(\'If the Customer purchased Membership through a Buy Now transaction (i.e., there is no Initial/Trial Period and no recurring charges for ongoing access), you may configure a Fixed Term Length in this field. This way the Customer\\\'s Membership Access is revoked by s2Member at the appropriate time. This will be a numeric value, followed by a space, then a single letter.\\n\\nHere are some examples:\\n\\n1 D (this means 1 Day)\\n1 W (this means 1 Week)\\n1 M (this means 1 Month)\\n1 Y (this means 1 Year)\\n1 L (this means 1 Lifetime)\'); return false;">[?]</a></p>'."\n";
			echo '<p id="ws-plugin--s2member-pro-reg-link" class="monospace" style="display:none;"></p>'."\n";
			echo '</form>'."\n";
			echo '</td>'."\n";

			echo '</tr>'."\n";
			echo '</tbody>'."\n";
			echo '</table>'."\n";
			echo '</div>'."\n";

			echo '</div>'."\n";

			// Specific Post/Page Access Links

			echo '<div class="ws-menu-page-group" title="Specific Post/Page Access Links">'."\n";

			echo '<div class="ws-menu-page-section ws-plugin--s2member-pro-sp-links-section">'."\n";
			echo '<h3>Specific Post/Page Access Link Generator (for Customer Service)</h3>'."\n";
			echo '<p>s2Member automatically generates Specific Post/Page Access Links for your Customers after checkout, and also sends them a link in a Confirmation Email. However, if you ever need to deal with a Customer Service issue that requires a new Specific Post/Page Access Link to be created manually, you can use this tool for that.</p>'."\n";

			echo '<table class="form-table">'."\n";
			echo '<tbody>'."\n";
			echo '<tr>'."\n";

			echo '<td>'."\n";
			echo '<form onsubmit="return false;" autocomplete="off">'."\n";

			echo '<p><select id="ws-plugin--s2member-pro-sp-link-leading-id">'."\n";
			echo '<option value="">&mdash; Select a Leading Post/Page that you\'ve protected &mdash;</option>'."\n";

			$ws_plugin__s2member_pro_temp_a_singulars = c_ws_plugin__s2member_utils_gets::get_all_singulars_with_sp("exclude-conflicts");

			foreach($ws_plugin__s2member_pro_temp_a_singulars as $ws_plugin__s2member_pro_temp_o)
				echo '<option value="'.esc_attr($ws_plugin__s2member_pro_temp_o->ID).'">'.esc_html($ws_plugin__s2member_pro_temp_o->post_title).'</option>'."\n";

			echo '</select> <a href="#" onclick="alert(\'Required. The Leading Post/Page, is what your Customers will land on after checkout.\n\n*Tip* If there are no Posts/Pages in the menu, it\\\'s because you\\\'ve not configured s2Member for Specific Post/Page Access yet. See: s2Member → Restriction Options → Specific Post/Page Access.\'); return false;" tabindex="-1">[?]</a></p>'."\n";

			echo '<p><select id="ws-plugin--s2member-pro-sp-link-additional-ids" multiple="multiple" style="height:100px; min-width:450px;">'."\n";
			echo '<optgroup label="&mdash; Package Additional Posts/Pages that you\'ve protected &mdash;">'."\n";

			foreach($ws_plugin__s2member_pro_temp_a_singulars as $ws_plugin__s2member_pro_temp_o)
				echo '<option value="'.esc_attr($ws_plugin__s2member_pro_temp_o->ID).'">'.esc_html($ws_plugin__s2member_pro_temp_o->post_title).'</option>'."\n";

			echo '</optgroup></select> <a href="#" onclick="alert(\'Hold down your `Ctrl` key to select multiples.\\n\\nOptional. If you include Additional Posts/Pages, Customers will still land on your Leading Post/Page; BUT, they\\\'ll ALSO have access to some Additional Posts/Pages that you\\\'ve protected. This gives you the ability to create Post/Page Packages.\\n\\nIn other words, a Customer is sold a Specific Post/Page (they\\\'ll land on your Leading Post/Page after checkout), which might contain links to some other Posts/Pages that you\\\'ve packaged together under one transaction.\\n\\nBundling Additional Posts/Pages into one Package, authenticates the Customer for access to the Additional Posts/Pages automatically (i.e., only one Access Link is needed, and s2Member generates this automatically). However, you will STILL need to design your Leading Post/Page (which is what a Customer will actually land on), with links pointing to the other Posts/Pages. This way your Customers will have clickable links to everything they\\\'ve paid for.\\n\\n*Quick Summary* s2Member sends Customers to your Leading Post/Page, and also authenticates them for access to any Additional Posts/Pages automatically. You handle it from there.\\n\\n*Tip* If there are no Posts/Pages in this menu, it\\\'s because you\\\'ve not configured s2Member for Specific Post/Page Access yet. See: s2Member → Restriction Options → Specific Post/Page Access.\'); return false;" tabindex="-1">[?]</a></p>'."\n";

			echo '<p><select id="ws-plugin--s2member-pro-sp-link-hours">'.trim(c_ws_plugin__s2member_utilities::evl(file_get_contents(dirname(dirname(__FILE__))."/templates/options/paypal-sp-hours.php"))).'</select> <input type="button" value="Generate Access Link" onclick="ws_plugin__s2member_pro_paypalSpLinkGenerate();" /> <img id="ws-plugin--s2member-pro-sp-link-loading" src="'.esc_attr($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["dir_url"]).'/src/images/ajax-loader.gif" alt="" style="display:none;" /></p>'."\n";
			echo '<p id="ws-plugin--s2member-pro-sp-link" class="monospace" style="display:none;"></p>'."\n";
			echo '</form>'."\n";
			echo '</td>'."\n";

			echo '</tr>'."\n";
			echo '</tbody>'."\n";
			echo '</table>'."\n";
			echo '</div>'."\n";

			echo '</div>'."\n";

			if(apply_filters("s2x_during_tools_page_during_left_sections_display_logs", TRUE, get_defined_vars()))
			{
				do_action("s2x_during_tools_page_during_left_sections_before_logs", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="Logs Viewer"' . (!empty($_POST['ws_plugin__s2member_log_file']) ? ' default-state="open"' : '') . '>'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-logs-section">'."\n";
				echo '<h3>Debugging Tools/Tips &amp; Other Important Details (<a href="#" onclick="jQuery(\'div#ws-plugin--s2member-debugging-tips-details\').toggle(); return false;" class="ws-dotted-link">click here to toggle</a>)</h3>'."\n";

				echo '<div id="ws-plugin--s2member-debugging-tips-details" style="display:none;">'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<form method="post" onsubmit="if(!confirm(\'Archive all existing log files?\n\nAll of your current log files will be archived (i.e., they will simply be renamed with an ARCHIVED tag &amp; date in their file name); and new log files will be created automatically the next time s2Member logs something on your installation.\n\nPlease click OK to confirm this action.\')) return false;" autocomplete="off">'."\n";
				echo '<input type="hidden" name="ws_plugin__s2member_logs_archive_start_fresh" value="'.esc_attr(wp_create_nonce("ws-plugin--s2member-logs-archive-start-fresh")).'" />'."\n";
				echo '<input type="submit" value="Archive All Current Log Files" class="ws-menu-page-right ws-plugin--s2member-archive-logs-start-fresh-button" style="font-size:110%; font-weight:normal; clear:right; min-width:300px;" />'."\n";
				echo '</form>'."\n";

				echo '<form method="post" onsubmit="if(!confirm(\'Delete all existing log files?\n\nThis will permanently delete ALL of your existing log files (including any archived log files).\n\nPlease click OK to confirm this action.\')) return false;" autocomplete="off">'."\n";
				echo '<input type="hidden" name="ws_plugin__s2member_logs_delete_start_fresh" value="'.esc_attr(wp_create_nonce("ws-plugin--s2member-logs-delete-start-fresh")).'" />'."\n";
				echo '<input type="submit" value="Permanently Delete All Log Files" class="ws-menu-page-right ws-plugin--s2member-delete-logs-start-fresh-button" style="font-size:110%; font-weight:normal; clear:right; min-width:300px;" />'."\n";
				echo '</form>'."\n";

				echo '<form method="post" autocomplete="off">'."\n";
				echo '<input type="hidden" name="ws_plugin__s2member_logs_download_zip" value="'.esc_attr(wp_create_nonce("ws-plugin--s2member-logs-download-zip")).'" />'."\n";
				echo '<input type="submit" value="Download All Log Files (Zip File)" class="ws-menu-page-right ws-plugin--s2member-logs-download-zip-button" style="font-size:110%; font-weight:normal; clear:right; min-width:300px;" />'."\n";
				echo '</form>'."\n";

				echo '<p><strong>Debugging Tips:</strong> &nbsp;&nbsp; It is normal to see a few errors in your log files. This is because s2Member logs <em>all</em> of its communication with Payment Gateways. Everything—not just successes. With that in mind, there will be some failures that s2Member expects (to a certain extent); and s2Member deals with these gracefully. What you\'re looking for here, are things that jump right out at you as being a major issue (e.g., when s2Member makes a point of providing details to you in a log entry about problems that should be corrected on your installation). Please read carefully.</p>'."\n";
				echo '<p><strong>Test Transaction Tips:</strong> &nbsp;&nbsp; Generally speaking, it is best to run test transactions for yourself. Be sure to run your final test transactions against a live Payment Gateway that is <em>not</em> in Sandbox/Test Mode (<a href="#" onclick="alert(\'While some Payment Gateways make it possible for you to run test transactions in Sandbox/Test Mode, these are not a reliable way to test s2Member.\n\nOften times (particularly with PayPal) Sandbox/Test mode behaves somewhat differently—often with buggy behavior. This can really create frustration for site owners. Therefore, it is always a good idea to run low-dollar test transactions against a live Payment Gateway.\n\nAlso, please be sure that you are not logged in as an Administrator when running test transactions. For most test transactions, you will want to be completely logged-out of your site before completing checkout (just like a new Customer would be). If you are testing an upgrade or downgrade (where you do need to be logged-in), please do not attempt this under an Administrative account. s2Member will not upgrade/downgrade Administrative accounts—for security purposes.\'); return false;">click here for details</a>). After running test transactions, please review the log file entries pertaining to your transaction. Does s2Member report any major issues? If so, please read through any details that s2Member provides in the log file. If you need assistance, please <a href="https://s2member.com/r/s2member-kb/" target="_blank" rel="external">search s2Member.com</a> for answers to common questions.</p>'."\n";
				echo '<p><strong>s2 Core Processors:</strong> &nbsp;&nbsp; It is normal to have a <code>gateway-core-ipn.log</code> and/or a <code>gateway-core-rtn.log</code> file at all times. Ultimately, all Payment Gateway integrations supported by s2Member pass through it\'s core post-processing handlers. If you\'re having trouble, and you don\'t find any errors in your Payment Gateway log files, please check the <code>gateway-core-ipn.log</code> and <code>gateway-core-rtn.log</code> files too. Regarding s2Member Pro-Forms... If you\'ve integrated s2Member Pro-Forms, you will not have a <code>gateway-core-rtn.log</code> file, because that particular processor is not used with Pro-Form integrations. However, you will have a <code>gateway-core-ipn.log</code> file, and you will need to make a point of inspecting this file to ensure there were no post-processing issues.</p>'."\n";
				echo '<p><strong>s2 HTTP API Logs:</strong> &nbsp;&nbsp; If s2Member is not behaving as expected, and you cannot find errors anywhere in your Payment Gateway log files (or with any core processors), please review your <code>s2-http-api-debug.log</code> file too. Look for any HTTP connections where s2Member is getting <code>403</code>, <code>404</code>, <code>503</code> errors from your server. This can sometimes happen due to <a href="http://www.s2member.com/kb/mod-security-random-503-403-errors/" target="_blank" rel="external">paranoid Mod Security configurations</a>, and it may require you to contact your hosting company for assistance.</p>'."\n";
				echo '<p style="font-style:italic;"><strong>Archived Log Files:</strong> &nbsp;&nbsp; All s2Member log files are stored here: <code>'.esc_html(c_ws_plugin__s2member_utils_dirs::doc_root_path($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["logs_dir"])).'</code>. Any log files that contain the word <code>ARCHIVED</code> in their name, are files that reached a size of more than 2MB; so s2Member archived them automatically to prevent any single log file from becoming too large. Archived log file names will also contain the date/time they were archived by s2Member. These archived log files typically contain much older (and possibly outdated) log entries.</p>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<h3>s2Member Log File Descriptions (for <em>all</em> possible log file names)</h3>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<ul class="ws-menu-page-li-margins">'."\n";
				foreach(c_ws_plugin__s2member_utils_logs::$log_file_descriptions as $_k => $_v)
					echo '<li><code><strong>'.esc_html(preg_replace(array('/^\/|\/$/', '/\\\\+/'), '', $_k)).'.log</strong></code> &nbsp;&nbsp; '.esc_html($_v["long"]).'</li>'."\n";
				unset($_k, $_v); // Housekeeping.
				echo '</ul>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_during_tools_page_during_left_sections_during_logs", get_defined_vars());

				$log_file_options = ""; // Initialize to an empty string.
				$logs_dir         = $GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["logs_dir"];

				if(is_dir($logs_dir)) // Do we have a logs directory on this installation?
				{
					$log_files = scandir($logs_dir);
					sort($log_files, SORT_STRING);

					$log_file_options .= '<optgroup label="Current Log Files">';
					foreach($log_files as $_log_file) // Build options for each current log file.
					{
						$_log_file_description = array("short" => "No description available.", "long" => "No description available.");

						foreach(c_ws_plugin__s2member_utils_logs::$log_file_descriptions as $_k => $_v)
							if(preg_match($_k, $_log_file))
							{
								$_log_file_description = $_v;
								break; // Stop here.
							}
						unset($_k, $_v); // Housekeeping.

						if(preg_match("/\.log$/", $_log_file) && stripos($_log_file, "-ARCHIVED-") === FALSE)
							$log_file_options .= '<option data-type="current" title="'.esc_attr($_log_file_description["long"]).'" value="'.esc_attr($_log_file).'"'.(($view_log_file === $_log_file) ? ' style="font-weight:bold;" selected="selected"' : '').'>'.esc_html($_log_file).'—'.esc_html($_log_file_description["short"]).'</option>';
					}
					unset($_log_file_description, $_log_file); // Housekeeping.
					$log_file_options .= '</optgroup>';

					if(stripos($log_file_options, '<option data-type="current"') === FALSE)
						$log_file_options .= '<option value="" disabled="disabled">— No current log files yet. —</option>';

					$log_file_options .= '<option value="" disabled="disabled"></option>';

					$log_file_options .= '<optgroup label="Archived Log Files">';
					foreach($log_files as $_log_file) // Build options for each ARCHIVED log file.
					{
						if(preg_match("/\.log$/", $_log_file) && stripos($_log_file, "-ARCHIVED-") !== FALSE)
							$log_file_options .= '<option data-type="archived" value="'.esc_attr($_log_file).'"'.(($view_log_file === $_log_file) ? ' style="font-weight:bold;" selected="selected"' : '').'>'.esc_html($_log_file).'</option>';
					}
					$log_file_options .= '</optgroup>';

					if(stripos($log_file_options, '<option data-type="archived"') === FALSE)
						$log_file_options .= '<option value="" disabled="disabled">— No log files archived yet. —</option>';
				}
				$log_file_options = '<option value="">— Choose a Log File to View —</option>'.
					'<option value="" disabled="disabled"></option>'.
					$log_file_options;

				echo '<form method="post" name="ws_plugin__s2member_log_viewer" id="ws-plugin--s2member-log-viewer" autocomplete="off">'."\n";

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<td style="width:80%;">'."\n";
				echo '<select name="ws_plugin__s2member_log_file" id="ws-plugin--s2member-log-file">'."\n";
				echo $log_file_options."\n";
				echo '</select>'."\n";
				echo '</td>'."\n";

				echo '<td style="width:20%; padding-left:5px;">'."\n";
				echo '<input type="submit" value="View" style="font-size:120%; font-weight:normal;" />'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";

				if($view_log_file && file_exists($logs_dir."/".$view_log_file) && filesize($logs_dir."/".$view_log_file))
				{
					$_log_file_description = array("short" => "", "long" => "");

					foreach(c_ws_plugin__s2member_utils_logs::$log_file_descriptions as $_k => $_v)
						if(preg_match($_k, $view_log_file))
						{
							$_log_file_description = $_v;
							break; // Stop here.
						}
					unset($_k, $_v); // Housekeeping.

					if(!empty($_log_file_description["long"])) // Do we have a description that we can display here?
						echo '<p style="clear:both; width:80%; font-family:\'Georgia\', serif; font-style:italic;"><strong>Description for <a href="'.esc_attr(add_query_arg(array("ws_plugin__s2member_download_log_file" => $view_log_file, "ws_plugin__s2member_download_log_file_v" => wp_create_nonce("ws-plugin--s2member-download-log-file-v")))).'">'.esc_html($view_log_file).'</a></strong>: '.esc_html($_log_file_description["long"]).'</p>'."\n";
					unset($_log_file_description); // Just a little housekeeping here.

					echo '<p style="float:left; text-align:left;"><strong>Viewing:</strong> <a href="'.esc_attr(add_query_arg(array("ws_plugin__s2member_download_log_file" => $view_log_file, "ws_plugin__s2member_download_log_file_v" => wp_create_nonce("ws-plugin--s2member-download-log-file-v")))).'">'.esc_html($view_log_file).'</a> (log entries oldest to newest)</p>'."\n";
					echo '<p style="float:right; text-align:right;">[ <a href="'.esc_attr(add_query_arg(array("ws_plugin__s2member_download_log_file" => $view_log_file, "ws_plugin__s2member_download_log_file_v" => wp_create_nonce("ws-plugin--s2member-download-log-file-v")))).'"><strong>download file</strong></a> ]</p>'."\n";
					echo '<p style="margin-right:10px; float:right; text-align:right;"><a href="#" class="ws-plugin--s2member-log-file-viewport-toggle" style="text-decoration:none;">&#8659; expand viewport &#8659;</a></p>'."\n";

					echo '<textarea id="ws-plugin--s2member-log-file-viewer" rows="20" wrap="on" spellcheck="false" style="box-shadow:inset 0 0 5px rgba(0,0,0,0.5); background:#EEEEEE; color:#000000; overflow-y:scroll;">'.htmlspecialchars(file_get_contents($logs_dir."/".$view_log_file)).'</textarea>'."\n";

					echo '<p style="float:left; text-align:left;"><strong>Viewing:</strong> <a href="'.esc_attr(add_query_arg(array("ws_plugin__s2member_download_log_file" => $view_log_file, "ws_plugin__s2member_download_log_file_v" => wp_create_nonce("ws-plugin--s2member-download-log-file-v")))).'">'.esc_html($view_log_file).'</a> (log entries oldest to newest)</p>'."\n";
					echo '<p style="float:right; text-align:right;">[ <a href="'.esc_attr(add_query_arg(array("ws_plugin__s2member_download_log_file" => $view_log_file, "ws_plugin__s2member_download_log_file_v" => wp_create_nonce("ws-plugin--s2member-download-log-file-v")))).'"><strong>download file</strong></a> ]</p>'."\n";
					echo '<p style="margin-right:10px; float:right; text-align:right;"><a href="#" class="ws-plugin--s2member-log-file-viewport-toggle" style="text-decoration:none;">&#8659; expand viewport &#8659;</a></p>'."\n";
				}
				else if($view_log_file && file_exists($logs_dir."/".$view_log_file))
					echo '<textarea id="ws-plugin--s2member-log-file-viewer" rows="20" wrap="on" spellcheck="false" style="box-shadow:inset 0 0 5px rgba(0,0,0,0.5); background:#EEEEEE; color:#000000; overflow-y:scroll; font-style:italic;">— Empty at this time —</textarea>'."\n";

				else if($view_log_file && !file_exists($logs_dir."/".$view_log_file))
					echo '<textarea id="ws-plugin--s2member-log-file-viewer" rows="20" wrap="on" spellcheck="false" style="box-shadow:inset 0 0 5px rgba(0,0,0,0.5); background:#EEEEEE; color:#000000; overflow-y:scroll; font-style:italic;">— File no longer exists —</textarea>'."\n";

				else // Display an empty textarea in this default scenario.
					echo '<textarea id="ws-plugin--s2member-log-file-viewer" rows="20" wrap="on" spellcheck="false" style="box-shadow:inset 0 0 5px rgba(0,0,0,0.5); background:#EEEEEE; color:#000000; overflow-y:scroll; font-style:italic;"></textarea>'."\n";

				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '</form>'."\n";

				echo '</div>'."\n";
				echo '</div>'."\n";

				do_action("s2x_during_tools_page_during_left_sections_after_logs", get_defined_vars());
			}

			do_action("s2x_during_tools_page_after_left_sections", get_defined_vars());

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

new c_ws_plugin__s2member_menu_page_tools();
