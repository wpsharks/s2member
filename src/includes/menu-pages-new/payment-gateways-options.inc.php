<?php
// @codingStandardsIgnoreFile
/**
 * Menu page for the s2Member plugin (PayPal Options page).
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

if(!class_exists("c_ws_plugin__s2member_menu_page_payment_gateways_options"))
{
	/**
	 * Menu page for the s2Member plugin (PayPal Options page).
	 *
	 * @package s2Member\Menu_Pages
	 * @since 110531
	 */
	class c_ws_plugin__s2member_menu_page_payment_gateways_options
	{
		public function __construct()
		{
            echo '<div class="wrap ws-menu-page">'."\n";

			echo '<div class="wp-header-end"></div>'."\n";

			echo '<div class="ws-menu-page-toolbox">'."\n";
			c_ws_plugin__s2member_menu_pages_tb::display();
			echo '</div>'."\n";

			echo '<h2>Payment Gateways Options</h2>'."\n";

			echo '<table class="ws-menu-page-table">'."\n";
			echo '<tbody class="ws-menu-page-table-tbody">'."\n";
			echo '<tr class="ws-menu-page-table-tr">'."\n";
			echo '<td class="ws-menu-page-table-l">'."\n";

			echo '<form method="post" name="ws_plugin__s2member_options_form" id="ws-plugin--s2member-options-form" autocomplete="off">'."\n";
			echo '<input type="hidden" name="ws_plugin__s2member_options_save" id="ws-plugin--s2member-options-save" value="'.esc_attr(wp_create_nonce("ws-plugin--s2member-options-save")).'" />'."\n";
			echo '<input type="hidden" name="ws_plugin__s2member_configured" id="ws-plugin--s2member-configured" value="1" />'."\n";

			do_action("s2x_during_payment_gateways_options_page_before_left_sections", get_defined_vars());

			if(apply_filters("s2x_during_payment_gateways_options_page_during_left_sections_display_paypal_account_details", TRUE, get_defined_vars()))
			{
				do_action("s2x_during_payment_gateways_options_page_during_left_sections_before_paypal_account_details", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="PayPal Account Details">'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-paypal-account-details-section">'."\n";
				echo '<a href="https://s2member.com/r/paypal/" target="_blank"><img src="'.esc_attr($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["dir_url"]).'/src/images/paypal-logo.png" class="ws-menu-page-right" style="width:125px; height:125px; border:0;" alt="." /></a>'."\n";
				echo '<h3>PayPal Account Details (required, if using PayPal)</h3>'."\n";
				echo '<p>s2Member integrates with <a href="https://s2member.com/r/paypal/" target="_blank" rel="external">PayPal Payments Standard</a>—for businesses. You do not need a PayPal Pro account. You just need to upgrade your Personal PayPal account to a Business status, which is free. A PayPal account can be <a href="http://s2member.com/r/paypal-business-upgrade/" target="_blank" rel="external">upgraded</a> from a Personal account to a Business account by clicking the "Profile" link under your "My Account" tab, selecting "Personal Business Information", and then clicking the "Upgrade Your Account" button. <strong>See also:</strong> This KB article: <a href="http://s2member.com/kb-article/supported-paypal-account-types/" target="_blank" rel="external">PayPal Compatibility (Account Types)</a>.</p>'."\n";
				echo '<p><em><strong>PayPal API Credentials:</strong> Once you have a PayPal Business account, you\'ll need access to your <a href="http://s2member.com/r/paypal-profile-api-access/" target="_blank" rel="external">PayPal API Credentials</a>. Log into your PayPal account, and navigate to <strong>Profile → API Access (or → Request API Credentials)</strong>. From the available options, please choose "Request API Signature".</em></p>'."\n";
				do_action("s2x_during_payment_gateways_options_page_during_left_sections_during_paypal_account_details", get_defined_vars());

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-paypal-merchant-id">'."\n";
				echo 'Your PayPal Merchant ID:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_paypal_merchant_id" id="ws-plugin--s2member-paypal-merchant-id" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_merchant_id"]).'" /><br />'."\n";
				echo 'At PayPal, see: <strong>Profile → Secure Merchant ID</strong>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-paypal-business">'."\n";
				echo 'Your PayPal EMail Address:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_paypal_business" id="ws-plugin--s2member-paypal-business" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_business"]).'" /><br />'."\n";
				echo 'At PayPal, see: <strong>Profile → Email Accounts</strong>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-paypal-api-username">'."\n";
				echo 'Your PayPal API Username:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_paypal_api_username" id="ws-plugin--s2member-paypal-api-username" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_api_username"]).'" /><br />'."\n";
				echo 'At PayPal, see: <strong>Profile → API Access (or → Request API Credentials)</strong>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-paypal-api-password">'."\n";
				echo 'Your PayPal API Password:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="password" autocomplete="off" name="ws_plugin__s2member_paypal_api_password" id="ws-plugin--s2member-paypal-api-password" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_api_password"]).'" /><br />'."\n";
				echo 'At PayPal, see: <strong>Profile → API Access (or → Request API Credentials)</strong>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-paypal-api-signature">'."\n";
				echo 'Your PayPal API Signature:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="password" autocomplete="off" name="ws_plugin__s2member_paypal_api_signature" id="ws-plugin--s2member-paypal-api-signature" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_api_signature"]).'" /><br />'."\n";
				echo 'At PayPal, see: <strong>Profile → API Access (or  → "Request API Credentials")</strong>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				do_action("s2x_during_payment_gateways_options_page_during_left_sections_during_paypal_account_detail_rows", get_defined_vars());

				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<table class="form-table" style="margin:0;">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th style="padding-top:0;">'."\n";
				echo '<label for="ws-plugin--s2member-paypal-sandbox">'."\n";
				echo 'Developer/Sandbox Testing?'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="radio" name="ws_plugin__s2member_paypal_sandbox" id="ws-plugin--s2member-paypal-sandbox-0" value="0"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_sandbox"]) ? ' checked="checked"' : '').' /> <label for="ws-plugin--s2member-paypal-sandbox-0">No</label> &nbsp;&nbsp;&nbsp; <input type="radio" name="ws_plugin__s2member_paypal_sandbox" id="ws-plugin--s2member-paypal-sandbox-1" value="1"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_sandbox"]) ? ' checked="checked"' : '').' /> <label for="ws-plugin--s2member-paypal-sandbox-1">Yes, enable support for Sandbox testing.</label><br />'."\n";
				echo '<em>Only enable this if you\'ve provided Sandbox credentials above.<br />This puts the API, IPN, PDT and Form/Button Generators all into Sandbox mode. See: <a href="http://s2member.com/r/paypal-developers/" target="_blank" rel="external">PayPal Developers</a></em><br />'."\n";
				echo '<em><strong>Warning:</strong> The PayPal Sandbox doesn\'t always give you an accurate view of what will happen once you go live, and in fact it is sometimes buggy at best. For this reason, our strong recommendation is that instead of using Sandbox Mode to run tests, that you go live and run tests with low-dollar amounts; i.e., $0.01 transactions are possible with PayPal in live mode, and that is a better way to test your installation of s2Member.</em>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-paypal-btn-encryption">'."\n";
				echo 'Enable Button Encryption?'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="radio" name="ws_plugin__s2member_paypal_btn_encryption" id="ws-plugin--s2member-paypal-btn-encryption-0" value="0"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_btn_encryption"]) ? ' checked="checked"' : '').' /> <label for="ws-plugin--s2member-paypal-btn-encryption-0">No</label> &nbsp;&nbsp;&nbsp; <input type="radio" name="ws_plugin__s2member_paypal_btn_encryption" id="ws-plugin--s2member-paypal-btn-encryption-1" value="1"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_btn_encryption"]) ? ' checked="checked"' : '').' /> <label for="ws-plugin--s2member-paypal-btn-encryption-1">Yes, enable PayPal Button encryption.</label><br />'."\n";
				echo '<em>If enabled, all of your PayPal Button Shortcodes will produce <em>encrypted</em> PayPal Buttons. This improves security against fraudulent transactions. For extra security, you should update your PayPal account too, under: <strong>My Profile → Website Payment Preferences</strong>. You\'ll want to block all non-encrypted payments. <strong>Note:</strong> this will NOT work until you\'ve supplied s2Member with your PayPal Email Address, and also with your API Username/Password/Signature.</em>'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";

				if(!is_multisite() || !c_ws_plugin__s2member_utils_conds::is_multisite_farm() || is_main_site())
				{
					echo '<tr>'."\n";

					echo '<th>'."\n";
					echo '<label for="ws-plugin--s2member-gateway-debug-logs">'."\n";
					echo 'Enable Logging Routines?'."\n";
					echo '</label>'."\n";
					echo '</th>'."\n";

					echo '</tr>'."\n";
					echo '<tr>'."\n";

					echo '<td>'."\n";
					echo '<input type="radio" name="ws_plugin__s2member_gateway_debug_logs" id="ws-plugin--s2member-gateway-debug-logs-0" value="0"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["gateway_debug_logs"]) ? ' checked="checked"' : '').' /> <label for="ws-plugin--s2member-gateway-debug-logs-0">No</label> &nbsp;&nbsp;&nbsp; <input type="radio" name="ws_plugin__s2member_gateway_debug_logs" id="ws-plugin--s2member-gateway-debug-logs-1" value="1"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["gateway_debug_logs"]) ? ' checked="checked"' : '').' /> <label for="ws-plugin--s2member-gateway-debug-logs-1">Yes, enable debugging, with API, IPN &amp; Return Page logging.</label><br />'."\n";
					echo '<em>This enables API, IPN and Return Page logging. The log files are stored here: <code>'.esc_html(c_ws_plugin__s2member_utils_dirs::doc_root_path($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["logs_dir"])).'</code></em><br />'."\n";
					echo '<em class="ws-menu-page-hilite">If you have any trouble, please review your s2Member log files for problems. See: <a href="'.esc_attr(admin_url("/admin.php?page=ws-plugin--s2member-logs")).'">Log Viewer</a></em>'."\n";
					echo '</td>'."\n";

					echo '</tr>'."\n";
					echo '<tr>'."\n";

					echo '<td>'."\n";
					echo '<div class="info" style="margin-bottom:0;">'."\n";
					echo '<p style="margin-top:0;"><span>We highly recommend that you enable logging during your initial testing phase. Logs produce lots of useful details that can help in debugging. Logs can help you find issues in your configuration and/or problems during payment processing. See: <a href="'.esc_attr(admin_url("/admin.php?page=ws-plugin--s2member-logs")).'">Log Files (Debug)</a>.</span></p>'."\n";
					echo '<p style="margin-bottom:0;"><span class="ws-menu-page-error">However, it is very important to disable logging once you go live. Log files may contain personally identifiable information, credit card numbers, secret API credentials, passwords and/or other sensitive information. We strongly suggest that logging be disabled on a live site (for security reasons).</span></p>'."\n";
					echo '</div>'."\n";
					echo '</td>'."\n";

					echo '</tr>'."\n";
				}
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<p><em><strong>Sandbox Tip:</strong> If you\'re testing your site through a PayPal Sandbox account, please remember that Email Confirmations from s2Member will not be received after a test purchase. s2Member sends its Confirmation Emails to the PayPal Email Address of the Customer. Since PayPal Sandbox addresses are usually bogus (for testing), you will have to run live transactions before Email Confirmations from s2Member are received. That being said, all other s2Member functionality can be tested through a PayPal Sandbox account. Email Confirmations are the only hang-up.</em></p>'."\n";
				do_action("s2x_during_payment_gateways_options_page_during_left_sections_during_paypal_account_details_after_sandbox_tip", get_defined_vars());
				echo '</div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_during_payment_gateways_options_page_during_left_sections_after_paypal_account_details", get_defined_vars());
			}
			if(apply_filters("s2x_during_payment_gateways_options_page_during_left_sections_display_paypal_ipn", TRUE, get_defined_vars()))
			{
				do_action("s2x_during_payment_gateways_options_page_during_left_sections_before_paypal_ipn", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="PayPal IPN Integration">'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-paypal-ipn-section">'."\n";
				echo '<h3>PayPal IPN / Instant Payment Notifications (required, please enable)</h3>'."\n";
				echo '<p>Log into your PayPal account and navigate to this section:<br /><strong>Account Profile → <a href="http://s2member.com/r/paypal-com-ipn-configuration-page/" target="_blank" rel="external">Instant Payment Notification Preferences</a></strong></p>'."\n";
				echo '<p>Edit your IPN settings &amp; turn IPN Notifications: <strong><code>On</code></strong></p>'."\n";
				echo '<p>You\'ll need your IPN URL, which is:<br /><code>'.esc_html(home_url("/?s2member_paypal_notify=1", "https")).'</code></p>'."\n";
				do_action("s2x_during_payment_gateways_options_page_during_left_sections_during_paypal_ipn", get_defined_vars());

				echo '<h4 style="margin-bottom:0;"><strong class="ws-menu-page-hilite">Note: SSL is required by PayPal</strong></h4>'."\n";
				echo '<p style="margin-top:0;">If you configure your PayPal.com account using the URL above, your site <strong><em>must</em> support SSL</strong> (i.e., the <code>https://</code> protocol). In other words, PayPal\'s system will refuse to accept any URL that does not begin with <code>https://</code>. The IPN URL that s2Member provides (see above) <em>does</em> start with <code>https://</code>. However, that doesn\'t necessarily mean that the URL actually works. Please be sure that your hosting account is configured with a valid SSL certificate before giving this URL to PayPal.</p>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<h3 style="margin:0;">More Information (<a href="#" onclick="jQuery(\'div#ws-plugin--s2member-paypal-ipn-details\').toggle(); return false;" class="ws-dotted-link">click here</a>)</h3>'."\n";
				echo '<div id="ws-plugin--s2member-paypal-ipn-details" style="margin-top:10px; display:none;">'."\n";
				echo '<p><em><strong>Quick Tip:</strong> In addition to the <a href="http://s2member.com/r/paypal-ipn-setup/" target="_blank" rel="external">default IPN settings inside your PayPal account</a>, the IPN URL is also set on a per-transaction basis by the special PayPal Button Code that s2Member provides you with. In other words, if you have multiple sites operating on one PayPal account, that\'s OK. s2Member dynamically sets the IPN URL for each transaction. The result is that the IPN URL configured from within your PayPal account, becomes the default, which is then overwritten on a per-transaction basis. In fact, PayPal recently updated their system to support IPN URL preservation. One PayPal account can handle multiple sites, all using different IPN URLs.</em></p>'."\n";
				do_action("s2x_during_payment_gateways_options_page_during_left_sections_during_paypal_ipn_after_quick_tip", get_defined_vars());
				echo '<p><em><strong>IPN Communications:</strong> You\'ll be happy to know that s2Member handles cancellations, expirations, failed payments, terminations (e.g., refunds &amp; chargebacks) for you automatically. If you log into your PayPal account and cancel a Member\'s Subscription, or, if the Member logs into their PayPal account and cancels their own Subscription, s2Member will be notified of these important changes and react accordingly through the PayPal IPN service that runs silently behind-the-scene. The PayPal IPN service will notify s2Member whenever a Member\'s payments have been failing, and/or whenever a Member\'s Subscription has expired for any reason. Even refunds &amp; chargeback reversals are supported through the IPN service. If you issue a refund to an unhappy Customer through PayPal, s2Member will be notified, and the account for that Customer will either be demoted to a Free Subscriber, or deleted automatically (based on your configuration). The communication from PayPal → s2Member is seamless.</em></p>'."\n";
				echo '</div>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<h3 style="margin:0;">IPN w/ Proxy Key (<a href="#" onclick="jQuery(\'div#ws-plugin--s2member-paypal-ipn-proxy-details\').toggle(); return false;" class="ws-dotted-link">optional, for 3rd-party integrations</a>)</h3>'."\n";
				echo '<div id="ws-plugin--s2member-paypal-ipn-proxy-details" style="margin-top:10px; display:none;">'."\n";
				echo '<p>If you\'re using a 3rd-party application that needs to POST simulated IPN transactions to your s2Member installation, you can use this alternate IPN URL, which includes a Proxy Key. This encrypted Proxy Key verifies incoming data being received by s2Member\'s IPN processor. You can change <em>[proxy-gateway]</em> to whatever you like. The <em>[proxy-gateway]</em> value is required. It will be stored by s2Member as the Customer\'s Paid Subscr. Gateway. Your [proxy-gateway] value will also be reflected in s2Member\'s IPN log.</p>'."\n";
				echo '<input type="text" autocomplete="off" value="'.format_to_edit(home_url("/?s2member_paypal_notify=1&s2member_paypal_proxy=[proxy-gateway]&s2member_paypal_proxy_verification=".urlencode(c_ws_plugin__s2member_paypal_utilities::paypal_proxy_key_gen()), "https")).'" style="width:99%;" />'."\n";
				echo '<p><em>Any 3rd-party application that is sending IPN transactions to your s2Member installation must include the <code>custom</code> POST variable, and that variable must always start with your installation domain (i.e., custom=<code>'.esc_html($_SERVER["HTTP_HOST"]).'</code>). In addition, the <code>item_number</code> variable must always match a format that s2Member looks for. Generally speaking, the <code>item_number</code> should be <code>1, 2, 3, or 4</code>, indicating a specific s2Member Level #. However, s2Member also uses some advanced formats in this field. Just to be sure, we suggest creating a PayPal Button with the s2Member Button Generator, and then taking a look at the Full Button Code to see how s2Member expects <code>item_number</code> to be formatted. Other than the aforementioned exceptions, all other POST variables should follow PayPal standards. Please see: <a href="https://s2member.com/r/paypal/-ipn-pdt-vars" target="_blank" rel="external">PayPal\'s IPN/PDT reference guide</a> for full documentation.</em></p>'."\n";
				do_action("s2x_during_payment_gateways_options_page_during_left_sections_during_paypal_ipn_after_proxy", get_defined_vars());
				echo '</div>'."\n";
				echo '</div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_during_payment_gateways_options_page_during_left_sections_after_paypal_ipn", get_defined_vars());
			}
			if(apply_filters("s2x_during_payment_gateways_options_page_during_left_sections_display_paypal_pdt", TRUE, get_defined_vars()))
			{
				do_action("s2x_during_payment_gateways_options_page_during_left_sections_before_paypal_pdt", get_defined_vars());

				echo '<div class="ws-menu-page-group" title="PayPal PDT/Auto-Return Integration">'."\n";

				echo '<div class="ws-menu-page-section ws-plugin--s2member-paypal-pdt-section">'."\n";
				echo '<h3>PayPal PDT Identity Token (required, please enable)</h3>'."\n";
				echo '<p>Log into your PayPal account and navigate to this section:<br /><strong>Account Profile → Website Payment Preferences</strong></p>'."\n";
				echo '<p>Turn the Auto-Return feature: <strong><code>On</code></strong></p>'."\n";
				echo '<p>You\'ll need your <a href="'.esc_attr(home_url("/?s2member_paypal_return=1&s2member_paypal_proxy=paypal&s2member_paypal_proxy_use=x-preview")).'" target="_blank" rel="external">Auto-Return URL</a>, which is:<br /><code>'.esc_html(home_url("/?s2member_paypal_return=1")).'</code></p>'."\n";
				echo '<p>You must also enable PDT (Payment Data Transfer): <strong><code>On</code></strong><br /><em>You\'ll be issued an Identity Token that you can enter below.</em></p>'."\n";
				do_action("s2x_during_payment_gateways_options_page_during_left_sections_during_paypal_pdt", get_defined_vars());

				echo '<table class="form-table">'."\n";
				echo '<tbody>'."\n";
				echo '<tr>'."\n";

				echo '<th>'."\n";
				echo '<label for="ws-plugin--s2member-paypal-identity-token">'."\n";
				echo 'PayPal PDT Identity Token:'."\n";
				echo '</label>'."\n";
				echo '</th>'."\n";

				echo '</tr>'."\n";
				echo '<tr>'."\n";

				echo '<td>'."\n";
				echo '<input type="password" autocomplete="off" name="ws_plugin__s2member_paypal_identity_token" id="ws-plugin--s2member-paypal-identity-token" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_identity_token"]).'" /><br />'."\n";
				echo 'Your PDT Identity Token will appear under <strong>Profile → Website Payment Preferences</strong> in your PayPal account.'."\n";
				echo '</td>'."\n";

				echo '</tr>'."\n";
				echo '</tbody>'."\n";
				echo '</table>'."\n";

				echo '<div class="ws-menu-page-hr"></div>'."\n";

				echo '<h3 style="margin:0;">More Information (<a href="#" onclick="jQuery(\'div#ws-plugin--s2member-paypal-pdt-details\').toggle(); return false;" class="ws-dotted-link">click here</a>)</h3>'."\n";
				echo '<div id="ws-plugin--s2member-paypal-pdt-details" style="margin-top:10px; display:none;">'."\n";
				echo '<p><em><strong>Quick Tip:</strong> In addition to the <a href="http://s2member.com/r/paypal-pdt-setup/" target="_blank" rel="external">default Auto-Return/PDT configuration inside your PayPal account</a>, the Auto-Return URL is also set on a per-transaction basis from within the special PayPal Button Code that s2Member provides you with. In other words, if you have multiple sites operating on one PayPal account, that\'s OK. s2Member sets the Auto-Return URL (dynamically) for each transaction. The result is that the Auto-Return URL configured from within your PayPal account becomes the default, which is then overwritten on a per-transaction basis by the s2Member software.</em></p>'."\n";
				do_action("s2x_during_payment_gateways_options_page_during_left_sections_during_paypal_pdt_after_quick_tip", get_defined_vars());
				echo '</div>'."\n";

				do_action("s2x_during_payment_gateways_options_page_during_left_sections_during_paypal_pdt_after_more_info", get_defined_vars());

				echo '</div>'."\n";

				echo '</div>'."\n";

				do_action("s2x_during_payment_gateways_options_page_during_left_sections_after_paypal_pdt", get_defined_vars());
			}

			do_action('s2x_during_payment_gateways_options_page_gateways_settings');

			self::render_button_generators();

			do_action('s2x_during_payment_gateways_options_page_taxes');
			do_action('s2x_during_payment_gateways_options_page_captcha');
			do_action('s2x_during_payment_gateways_options_page_pro_forms');
			do_action('s2x_during_payment_gateways_options_page_buttons');
			do_action('s2x_during_payment_gateways_options_page_coupon_codes');

			do_action("s2x_during_payment_gateways_options_page_after_left_sections", get_defined_vars());

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

		private static function render_button_generators() {
			echo '<div class="ws-menu-page-group" title="Buttons Generator">'."\n";

			echo '<div class="ws-menu-page-section ws-plugin--s2member-buttons-generator-section">'."\n";

			echo '<div id="s2x-shortcode-generator"></div>';

			echo '</div>'."\n";

			echo '</div>'."\n";
		}
	}
}

new c_ws_plugin__s2member_menu_page_payment_gateways_options();
