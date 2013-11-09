<?php
/**
* Menu page for the s2Member plugin (Main Multisite Options page).
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
* @package s2Member\Menu_Pages
* @since 3.0
*/
if(realpath(__FILE__) === realpath($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");

if(!class_exists("c_ws_plugin__s2member_menu_page_mms_ops"))
	{
		/**
		* Menu page for the s2Member plugin (Main Multisite Options page).
		*
		* @package s2Member\Menu_Pages
		* @since 110531
		*/
		class c_ws_plugin__s2member_menu_page_mms_ops
			{
				public function __construct()
					{
						echo '<div class="wrap ws-menu-page">'."\n";

						echo '<div class="ws-menu-page-toolbox">'."\n";
						c_ws_plugin__s2member_menu_pages_tb::display ();
						echo '</div>'."\n";

						echo '<h2>Multisite Config</h2>'."\n";

						echo '<table class="ws-menu-page-table">'."\n";
						echo '<tbody class="ws-menu-page-table-tbody">'."\n";
						echo '<tr class="ws-menu-page-table-tr">'."\n";
						echo '<td class="ws-menu-page-table-l">'."\n";

						if(is_multisite() && is_main_site()) // These panels will ONLY be available on the Main Site; with Multisite Networking.
							{
								echo '<form method="post" name="ws_plugin__s2member_options_form" id="ws-plugin--s2member-options-form">'."\n";
								echo '<input type="hidden" name="ws_plugin__s2member_options_save" id="ws-plugin--s2member-options-save" value="'.esc_attr(wp_create_nonce("ws-plugin--s2member-options-save")).'" />'."\n";
								echo '<input type="hidden" name="ws_plugin__s2member_configured" id="ws-plugin--s2member-configured" value="1" />'."\n";

								do_action("ws_plugin__s2member_during_mms_ops_page_before_left_sections", get_defined_vars());

								if(apply_filters("ws_plugin__s2member_during_mms_ops_page_during_left_sections_display_mms_patches", true, get_defined_vars()))
									{
										do_action("ws_plugin__s2member_during_mms_ops_page_during_left_sections_before_mms_patches", get_defined_vars());

										echo '<div class="ws-menu-page-group" title="Multisite WordPress Patches" default-state="open">'."\n";

										echo '<div class="ws-menu-page-section ws-plugin--s2member-mms-patches-section">'."\n";
										echo '<img src="'.esc_attr($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["dir_url"]).'/images/small-icon.png" title="s2Member (a Membership management system for WordPress)" alt="" style="float:right; margin:0 0 0 25px; border:0;" />'."\n";
										echo '<h3>Multisite WordPress Patches (required for compatiblity)</h3>'."\n";
										echo '<p>In order for s2Member to function properly in a Multisite environment, you MUST implement four patches. One goes into your <code>/wp-login.php</code> file, one into <code>/wp-includes/load.php</code>, one into <code>/wp-includes/ms-functions.php</code>, and another into <code>/wp-admin/user-new.php</code>. Please use the automatic patcher below. All you do is check the box &amp; click Save.</p>'."\n";
										do_action("ws_plugin__s2member_during_mms_ops_page_during_left_sections_during_mms_patches", get_defined_vars());

										echo '<table class="form-table">'."\n";
										echo '<tbody>'."\n";
										echo '<tr>'."\n";

										echo '<th>'."\n";
										echo '<label for="ws-plugin--s2member-mms-auto-patch">'."\n";
										echo 'Patch Automatically? (the easiest way)'."\n";
										echo '</label>'."\n";
										echo '</th>'."\n";

										echo '</tr>'."\n";
										echo '<tr>'."\n";

										echo '<td>'."\n";

										if(defined("DISALLOW_FILE_MODS") && DISALLOW_FILE_MODS)
											{
												echo '<select name="ws_plugin__s2member_mms_auto_patch" id="ws-plugin--s2member-mms-auto-patch" disabled="disabled">'."\n";
												echo '<option value="0" selected="selected">No (I\'ll patch WordPress myself)</option>'."\n";
												echo '</select><br />'."\n";
												echo '<em class="ws-menu-page-hilite">This is now locked. Your <code>/wp-config.php</code> file says: <code>DISALLOW_FILE_MODS = true</code></em>.'."\n";
											}
										else // Otherwise we can display these options.
											{
												echo '<select name="ws_plugin__s2member_mms_auto_patch" id="ws-plugin--s2member-mms-auto-patch">'."\n";
												echo '<option value="1"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["mms_auto_patch"]) ? ' selected="selected"' : '').'>Yes (automatically patch WordPress)</option>'."\n";
												echo '<option value="0"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["mms_auto_patch"]) ? ' selected="selected"' : '').'>No (I\'ll patch WordPress myself)</option>'."\n";
												echo '</select><br />'."\n";
												echo '<em class="ws-menu-page-hilite">These files MUST be patched, each time you upgrade the WordPress core. If you set this option to <code>Yes (Patch Automatically)</code>, s2Member will patch your installation now, and also in the future, should you upgrade to newer version. That way, you won\'t need to patch manually each time WordPress is upgraded.</em>'."\n";
											}

										echo '</td>'."\n";

										echo '</tr>'."\n";
										echo '</tbody>'."\n";
										echo '</table>'."\n";

										echo '<div class="ws-menu-page-hr"></div>'."\n";

										echo '<div id="ws-plugin--s2member-mms-patches-details-wrapper">'."\n";
										echo '<h3>Rather Do It Yourself? (<a href="#" onclick="jQuery(\'div#ws-plugin--s2member-mms-patches-details\').toggle(); return false;" class="ws-dotted-link">manual instructions</a>)</h3>'."\n";
										echo '<div id="ws-plugin--s2member-mms-patches-details" style="display:none;">'."\n";
										echo '<p><strong>Patch #1</strong> ( /wp-login.php )</p>'."\n";
										echo '<p>'.c_ws_plugin__s2member_utils_strings::highlight_php(file_get_contents(dirname(__FILE__)."/code-samples/mms-patch-wp-login.x-php")).'</p>'."\n";
										echo '<p><strong>Patch #2</strong> ( /wp-includes/load.php )</p>'."\n";
										echo '<p>'.c_ws_plugin__s2member_utils_strings::highlight_php(file_get_contents(dirname(__FILE__)."/code-samples/mms-patch-load.x-php")).'</p>'."\n";
										echo '<p><strong>Patch #3</strong> ( /wp-admin/user-new.php )</p>'."\n";
										echo '<p>'.c_ws_plugin__s2member_utils_strings::highlight_php(file_get_contents(dirname(__FILE__)."/code-samples/mms-patch-user-new.x-php")).'</p>'."\n";
										echo '<p><strong>Patch #4</strong> ( /wp-includes/ms-functions.php )</p>'."\n";
										echo '<p>'.c_ws_plugin__s2member_utils_strings::highlight_php(file_get_contents(dirname(__FILE__)."/code-samples/mms-patch-ms-functions.x-php")).'</p>'."\n";
										echo '<p><em class="ws-menu-page-hilite">Don\'t forget to patch these files again, each time you upgrade the WordPress core.</em></p>'."\n";
										echo '</div>'."\n";
										echo '</div>'."\n";
										echo '</div>'."\n";

										echo '</div>'."\n";

										do_action("ws_plugin__s2member_during_mms_ops_page_during_left_sections_after_mms_patches", get_defined_vars());
									}

								if(apply_filters("ws_plugin__s2member_during_mms_ops_page_during_left_sections_display_mms_registration", true, get_defined_vars()))
									{
										do_action("ws_plugin__s2member_during_mms_ops_page_during_left_sections_before_mms_registration", get_defined_vars());

										echo '<div class="ws-menu-page-group" title="Multisite Registration Configuration" default-state="open">'."\n";

										echo '<div class="ws-menu-page-section ws-plugin--s2member-mms-registration-section">'."\n";
										echo '<img src="'.esc_attr($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["dir_url"]).'/images/large-icon.png" title="s2Member (a Membership management system for WordPress)" alt="" style="float:right; margin:0 0 0 25px; border:0;" />'."\n";
										echo '<h3>Multisite Registration (Main Site Configuration)</h3>'."\n";
										echo '<p>s2Member supports Free Subscribers <em>(at Level #0)</em>, and several Primary Roles created by the s2Member plugin (<em> i.e. s2Member Levels 1-4, or up to the number of configured Levels )</em>. If you want your visitors to be capable of registering absolutely free, you will want to "allow" Open Registration. Whenever a visitor registers without paying, they\'ll automatically become a Free Subscriber, at Level #0.</p>'."\n";
										echo '<p><strong>Running A Multisite Blog Farm?</strong> With Multisite Networking enabled, your Main Site could ALSO offer a Customer access to create a Blog of their own <em>(optional)</em>, where a Customer becomes a "Member" of your Main Site, and also a Blog Owner/Administrator of at least one other Blog on your Network. With s2Member installed <em>(Network wide)</em>, each of your Blog Owners could offer Membership too, using a single copy of the s2Member plugin, which is a great selling point<em>!</em> We refer to this type of installation as a Multisite Blog Farm.</p>'."\n";
										echo '<p>Multisite Networking makes a new Registration Form available <em>(driven by your theme)</em>; which we refer to as: <code>/wp-signup.php</code>. If, and only if, you\'re planning to offer Blogs, you MUST use <a href="'.esc_attr(c_ws_plugin__s2member_utils_urls::wp_signup_url()).'" target="_blank" rel="external" onclick="alert(\'s2Member will now open your Multisite Registration Form.\\n* s2Member makes this form available to logged-in Super Administrators, at all times (for testing purposes), regardless of configuration.'.((c_ws_plugin__s2member_utils_conds::bp_is_installed()) ? '\\n\\nBuddyPress: * BuddyPress will use its own Registration Form. Please note, you will probably be redirected away from the BuddyPress Registration Form ( '.c_ws_plugin__s2member_utils_strings::esc_js_sq(c_ws_plugin__s2member_utils_urls::bp_register_url()).' ), because you\\\'re ALREADY logged-in. Please log out before testing BuddyPress registration.' : '').'\');">/wp-signup.php</a>, instead of using the Standard Login/Registration Form. In a Multisite installation, we refer to the Standard Login/Registration Form, as: <a href="'.esc_attr(c_ws_plugin__s2member_utils_urls::wp_register_url()).'" target="_blank" rel="external" onclick="alert(\'s2Member will now open your Standard Registration Form.\\n* s2Member makes this form available to logged-in Administrators, at all times (for testing purposes), regardless of configuration.'.((c_ws_plugin__s2member_utils_conds::bp_is_installed()) ? '\\n\\nBuddyPress: * BuddyPress will use its own Registration Form. Please note, you will probably be redirected away from the BuddyPress Registration Form ( '.c_ws_plugin__s2member_utils_strings::esc_js_sq(c_ws_plugin__s2member_utils_urls::bp_register_url()).' ), because you\\\'re ALREADY logged-in. Please log out before testing BuddyPress registration.' : '').'\');">/wp-login.php?action=register</a>. If you\'re planning to offer Membership Access only, and NOT Blogs, you can simply use the <a href="'.esc_attr(c_ws_plugin__s2member_utils_urls::wp_register_url()).'" target="_blank" rel="external" onclick="alert(\'s2Member will now open your Standard Registration Form.\\n* s2Member makes this form available to logged-in Administrators, at all times (for testing purposes), regardless of configuration.'.((c_ws_plugin__s2member_utils_conds::bp_is_installed()) ? '\\n\\nBuddyPress: * BuddyPress will use its own Registration Form. Please note, you will probably be redirected away from the BuddyPress Registration Form ( '.c_ws_plugin__s2member_utils_strings::esc_js_sq(c_ws_plugin__s2member_utils_urls::bp_register_url()).' ), because you\\\'re ALREADY logged-in. Please log out before testing BuddyPress registration.' : '').'\');">Standard Login/Registration Form</a>, which is easily customized through <code>s2Member -› General Options -› Login/Registration Design</code>.</p>'."\n";
										echo '<p>In either case, s2Member Pro Forms are possible too. If you\'ve purchased s2Member Pro, you could use Pro Forms instead of these WordPress defaults. That being said, even with s2Member Pro Forms, if you are offering Blogs, you will still need to facilitate the actual creation of each Blog through <code>/wp-signup.php</code>. In other words, Customers can register through s2Member Pro Forms, and even checkout. But when it comes time to setup a new Blog, you will need to redirect your Customer to <code>/wp-signup.php</code>, while they are logged-in. This will allow them to create a new Blog on your Network. That is, if they are allowed to <em>(based on your configuration below)</em>.</p>'."\n";
										echo (c_ws_plugin__s2member_utils_conds::bp_is_installed()) ? '<p><em><strong>BuddyPress:</strong> BuddyPress will use its own Registration Form, powered by your theme.<br />(BuddyPress can handle both Membership and Blog creation in its integration)<br />(<a href="'.esc_attr(c_ws_plugin__s2member_utils_urls::bp_register_url()).'" target="_blank" rel="external" onclick="alert(\'s2Member will now open your BuddyPress Registration Form.\\n* However, you will probably be redirected away from this BuddyPress Registration Form ( '.c_ws_plugin__s2member_utils_strings::esc_js_sq(c_ws_plugin__s2member_utils_urls::bp_register_url()).' ), because you\\\'re ALREADY logged-in. Please log out before testing BuddyPress registration.\');">'.esc_html(c_ws_plugin__s2member_utils_urls::bp_register_url()).'</a>)</em></p>'."\n" : '';
										do_action("ws_plugin__s2member_during_mms_ops_page_during_left_sections_during_mms_registration", get_defined_vars());

										echo '<div id="ws-plugin--s2member-mms-registration-support-package-details-wrapper">'."\n";
										echo '<h4 style="margin-bottom:0;">Running a Multisite Blog Farm? (<a href="#" onclick="jQuery(\'div#ws-plugin--s2member-mms-registration-support-package-details\').toggle(); return false;" class="ws-dotted-link">click here / please read</a>)</h4>'."\n";
										echo '<div id="ws-plugin--s2member-mms-registration-support-package-details" style="display:none;">'."\n";
										echo '<p>The most important thing to do when setting up a Blog Farm with s2Member, is to add this line to your <code>/wp-config.php</code> file: <code><span style="color:#0000BB;">define</span><span style="color:#007700;">(</span><span style="color:#DD0000;">"MULTISITE_FARM"</span>, <span style="color:#0000BB;">true</span><span style="color:#007700;">);</span></code>. This will add a default layer of security, to all Blogs within your Network, with respect to s2Member. <strong>But, before you go live</strong>, please contact <a href="'.esc_attr(c_ws_plugin__s2member_readmes::parse_readme_value("Pro Module / Prices")).'" target="_blank" rel="external">s2Member.com</a> for full documentation. There is some additional functionality that can be enabled for security on a Blog Farm installation; and also some menus/documentation/functionality that can be disabled. You will be asked to purchase our <a href="'.esc_attr(c_ws_plugin__s2member_readmes::parse_readme_value("Pro Module / Prices")).'" target="_blank" rel="external">Network Support Package</a> when you need assistance in this regard.</p>'."\n";
										echo '<p>Multisite Blog Farms require a site owner that fully understands the potential security risks associated with Blog Farming. s2Member\'s <a href="'.esc_attr(c_ws_plugin__s2member_readmes::parse_readme_value("Pro Module / Prices")).'" target="_blank" rel="external">Network Support Package</a> provides you with the information you need, and priority support for anything about s2Member that you don\'t understand. In addition, our Network Support Package includes a lengthy PDF file that details a list of things affected by <code><span style="color:#0000BB;">define</span><span style="color:#007700;">(</span><span style="color:#DD0000;">"MULTISITE_FARM"</span>, <span style="color:#0000BB;">true</span><span style="color:#007700;">);</span></code>, best practices, and other supplemental documentation focused on Blog Farms.</p>'."\n";
										echo '<p><em><strong>Definition of a Multisite Blog Farm:</strong> If your Network is making it possible for "Members" of your Main Site, to create and/or manage Blogs (in any way), s2Member will consider your installation to be a Multisite Blog Farm. That being said, some site owners run a Multisite Network for the purpose of maintaining their own sites. The term Multisite Blog Farm does NOT apply to a Network that hosts multiple Child Blogs, all of which are operated by a single site owner and/or a single company. Again, a Multisite Blog Farm (in the eyes of s2Member), is any Network that is making it possible for "Members" of its Main Site, to create and/or manage Blogs; where one or more of these Child Blogs is being administered by a Customer (e.g. if you offer both Membership and Blog creation, as configured below).</em></p>'."\n";
										echo '<p><em><strong>When NOT to run a Multisite Blog Farm:</strong> If you run a Multisite Network for the purpose of maintaining your own sites. You should NOT run a Multisite Blog Farm. You can still activate s2Member Network-wide, if you like (optional), but the advanced security considerations offered through s2Member\'s Multisite Blog Farm functionality are NOT needed in this case; because all of the Child Blogs in your Network belong to trusted Administrators (i.e. your Customers are NOT going to run Child Blogs on your Network in this case).</em></p>'."\n";
										echo '</div>'."\n";
										echo '</div>'."\n";

										echo '<div class="ws-menu-page-hr"></div>'."\n";

										echo '<table class="form-table">'."\n";
										echo '<tbody>'."\n";
										echo '<tr>'."\n";

										echo '<th>'."\n";
										echo '<label for="ws-plugin--s2member-mms-registration-file">'."\n";
										echo 'What Do You Plan To Offer? (please choose one)'."\n";
										echo '</label>'."\n";
										echo '</th>'."\n";

										echo '</tr>'."\n";
										echo '<tr>'."\n";

										echo '<td>'."\n";

										if(defined("MULTISITE_FARM") && MULTISITE_FARM) // Lock this down if a config option is set in /wp-config.php.
											{
												echo '<select name="ws_plugin__s2member_mms_registration_file" id="ws-plugin--s2member-mms-registration-file" disabled="disabled">'."\n";
												echo '<option value="wp-signup" selected="selected">Blog Farm (I plan to offer both Membership &amp; Blog creation)</option>'."\n";
												echo '</select><br />'."\n";
												echo '<em class="ws-menu-page-hilite">This is now locked. Your <code>/wp-config.php</code> file says: <code>MULTISITE_FARM = true</code></em>.'."\n";
											}
										else // Otherwise we can display these options normally.
											{
												echo '<select name="ws_plugin__s2member_mms_registration_file" id="ws-plugin--s2member-mms-registration-file">'."\n";
												echo '<option value="wp-login"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["mms_registration_file"] === "wp-login") ? ' selected="selected"' : '').'>Membership Only (I\'m NOT offering Blogs)</option>'."\n";
												echo '<option value="wp-signup"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["mms_registration_file"] === "wp-signup") ? ' selected="selected"' : '').'>Blog Farm (I plan to offer both Membership &amp; Blog creation)</option>'."\n";
												echo '</select><br />'."\n";
												echo 'Depending on your selection, the options below may change.'."\n";
											}

										echo '</td>'."\n";

										echo '</tr>'."\n";
										echo '</tbody>'."\n";
										echo '</table>'."\n";

										echo '<div class="ws-menu-page-hr"></div>'."\n";

										echo '<table class="form-table ws-plugin--s2member-mms-registration-wp-login" style="margin:0;">'."\n";
										echo '<tbody>'."\n";
										echo '<tr>'."\n";

										echo '<th style="padding-top:0;">'."\n";
										echo '<label for="ws-plugin--s2member-allow-subscribers-in">'."\n";
										echo 'Your Main Site / Allow Open Registration? (via <code>wp-login.php?action=register</code>)'."\n";
										echo '</label>'."\n";
										echo '</th>'."\n";

										echo '</tr>'."\n";
										echo '<tr>'."\n";

										echo '<td>'."\n";
										echo '<select name="ws_plugin__s2member_allow_subscribers_in" id="ws-plugin--s2member-allow-subscribers-in">'."\n";
										echo '<option value="0"'.((!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["allow_subscribers_in"]) ? ' selected="selected"' : '').'>No (do NOT allow Open Registration)</option>'."\n";
										echo '<option value="1"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["allow_subscribers_in"]) ? ' selected="selected"' : '').'>Yes (allow Open Registration; Free Subscribers at Level #0)</option>'."\n";
										echo '</select><br />'."\n";
										echo 'If you set this to <code>Yes</code>, you\'re unlocking <a href="'.esc_attr(c_ws_plugin__s2member_utils_urls::wp_register_url()).'" target="_blank" rel="external" onclick="alert(\'s2Member will now open your Standard Registration Form.\\n* s2Member makes this form available to logged-in Administrators, at all times (for testing purposes), regardless of configuration.'.((c_ws_plugin__s2member_utils_conds::bp_is_installed()) ? '\\n\\nBuddyPress: * BuddyPress will use its own Registration Form. Please note, you will probably be redirected away from the BuddyPress Registration Form ( '.c_ws_plugin__s2member_utils_strings::esc_js_sq(c_ws_plugin__s2member_utils_urls::bp_register_url()).' ), because you\\\'re ALREADY logged-in. Please log out before testing BuddyPress registration.' : '').'\');">wp-login.php?action=register</a> (on your Main Site). When a visitor registers without paying, they\'ll automatically become a Free Subscriber, at Level #0. The s2Member software reserves Level #0; to be used ONLY for Free Subscribers. All other Membership Levels [1-4] require payment.'."\n";
										echo '</td>'."\n";

										echo '</tr>'."\n";
										echo '</tbody>'."\n";
										echo '</table>'."\n";

										echo '<table class="form-table ws-plugin--s2member-mms-registration-wp-signup" style="margin:0;">'."\n";
										echo '<tbody>'."\n";
										echo '<tr>'."\n";

										echo '<th style="padding-top:0;">'."\n";
										echo '<label for="ws-plugin--s2member-mms-registration-grants">'."\n";
										echo 'Your Main Site / Allow Open Registration? (via <code>wp-signup.php</code>)'."\n";
										echo '</label>'."\n";
										echo '</th>'."\n";

										echo '</tr>'."\n";
										echo '<tr>'."\n";

										echo '<td style="padding-bottom:0;">'."\n";
										echo '<select name="ws_plugin__s2member_mms_registration_grants" id="ws-plugin--s2member-mms-registration-grants">'."\n";
										echo '<option value="none"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["mms_registration_grants"] === "none") ? ' selected="selected"' : '').'>No (do NOT allow Open Registration)</option>'."\n";
										echo '<option value="user"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["mms_registration_grants"] === "user") ? ' selected="selected"' : '').'>Yes (allow Open Registration; Free Subscribers at Level #0)</option>'."\n";
										echo '<option value="all"'.(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["mms_registration_grants"] === "all") ? ' selected="selected"' : '').'>Yes (allow Open Registration; Free Subscribers, with a free Blog too)</option>'."\n";
										echo '</select><br />'."\n";
										echo 'If you set this to <code>Yes</code>, you\'re unlocking <a href="'.esc_attr(c_ws_plugin__s2member_utils_urls::wp_signup_url()).'" target="_blank" rel="external" onclick="alert(\'s2Member will now open your Multisite Registration Form.\\n* s2Member makes this form available to logged-in Super Administrators, at all times (for testing purposes), regardless of configuration.'.((c_ws_plugin__s2member_utils_conds::bp_is_installed()) ? '\\n\\nBuddyPress: * BuddyPress will use its own Registration Form. Please note, you will probably be redirected away from the BuddyPress Registration Form ( '.c_ws_plugin__s2member_utils_strings::esc_js_sq(c_ws_plugin__s2member_utils_urls::bp_register_url()).' ), because you\\\'re ALREADY logged-in. Please log out before testing BuddyPress registration.' : '').'\');">wp-signup.php</a> (on your Main Site).'."\n";
										echo '</td>'."\n";

										echo '</tr>'."\n";
										echo '</tbody>'."\n";
										echo '</table>'."\n";

										echo '<table class="form-table ws-plugin--s2member-mms-registration-wp-signup ws-plugin--s2member-mms-registration-wp-signup-blogs-level0">'."\n";
										echo '<tbody>'."\n";
										echo '<tr>'."\n";

										echo '<th>'."\n";
										echo '<label for="ws-plugin--s2member-mms-registration-blogs-level0">'."\n";
										echo 'Level #0 (Free Subscribers):'."\n";
										echo '</label>'."\n";
										echo '</th>'."\n";

										echo '</tr>'."\n";
										echo '<tr>'."\n";

										echo '<td style="padding-bottom:0;">'."\n";
										echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_mms_registration_blogs_level0" id="ws-plugin--s2member-mms-registration-blogs-level0" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["mms_registration_blogs_level0"]).'" /><br />'."\n";
										echo 'How many blogs can a Free Subscriber create?'."\n";
										echo '</td>'."\n";

										echo '</tr>'."\n";
										echo '</tbody>'."\n";
										echo '</table>'."\n";

										echo '<div class="ws-menu-page-hr ws-plugin--s2member-mms-registration-wp-signup"></div>'."\n";

										echo '<table class="form-table ws-plugin--s2member-mms-registration-wp-signup" style="margin:0;">'."\n";
										echo '<tbody>'."\n";

										for($n = 1; $n <= $GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["levels"]; $n++)
											{
												echo '<tr>'."\n";

												echo '<th style="padding-top:0;">'."\n";
												echo '<label for="ws-plugin--s2member-mms-registration-blogs-level'.$n.'">'."\n";
												echo 'Membership Level #'.$n.' / Maximum Blogs Allowed:'."\n";
												echo '</label>'."\n";
												echo '</th>'."\n";

												echo '</tr>'."\n";
												echo '<tr>'."\n";

												echo '<td>'."\n";
												echo '<input type="text" autocomplete="off" name="ws_plugin__s2member_mms_registration_blogs_level'.$n.'" id="ws-plugin--s2member-mms-registration-blogs-level'.$n.'" value="'.format_to_edit($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["mms_registration_blogs_level".$n]).'" /><br />'."\n";
												echo 'How many blogs can a Member ( at Level #'.$n.' ) create?'."\n";
												echo '</td>'."\n";

												echo '</tr>'."\n";
											}

										echo '</tbody>'."\n";
										echo '</table>'."\n";
										echo '</div>'."\n";

										echo '</div>'."\n";

										do_action("ws_plugin__s2member_during_mms_ops_page_during_left_sections_after_mms_registration", get_defined_vars());
									}
								do_action("ws_plugin__s2member_during_mms_ops_page_after_left_sections", get_defined_vars());

								echo '<div class="ws-menu-page-hr"></div>'."\n";

								echo '<p class="submit"><input type="submit" value="Save All Changes" /></p>'."\n";

								echo '</form>'."\n";
							}
						else // Otherwise, we can display a simple notation; leading into Multisite Networking.
							{
								echo '<p style="margin-top:0;"><span class="ws-menu-page-hilite">Your WordPress installation does not have Multisite Networking enabled.<br />Which is perfectly OK :-) Multisite Networking is 100% completely optional.</span></p>'."\n";
								echo '<img src="'.esc_attr($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["dir_url"]).'/images/large-icon.png" title="s2Member (a Membership management system for WordPress)" alt="" style="float:right; margin:0 0 0 25px; border:0;" />'."\n";

								if(file_exists($ws_plugin__s2member_temp = dirname(dirname(dirname(__FILE__)))."/readme-ms.txt"))
									{
										echo '<div class="ws-menu-page-hr"></div>'."\n";

										if(!function_exists("NC_Markdown"))
											include_once dirname(dirname(__FILE__))."/externals/markdown/nc-markdown.inc.php";

										$ws_plugin__s2member_temp = file_get_contents($ws_plugin__s2member_temp);
										$ws_plugin__s2member_temp = preg_replace("/(\=)( )(.+?)( )(\=)/", "<h3>$3</h3>", $ws_plugin__s2member_temp);
										$ws_plugin__s2member_temp = NC_Markdown($ws_plugin__s2member_temp);

										echo preg_replace("/(\<a)( href)/i", "$1".' target="_blank" rel="nofollow external"'."$2", $ws_plugin__s2member_temp);
									}
							}

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

new c_ws_plugin__s2member_menu_page_mms_ops();
?>