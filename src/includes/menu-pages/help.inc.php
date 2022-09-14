<?php
// @codingStandardsIgnoreFile
/**
* Getting Help.
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
* @since 151218
*/
if(!defined('WPINC')) // MUST have WordPress.
	exit ("Do not access this file directly.");

if (!class_exists ("c_ws_plugin__s2member_menu_page_help"))
	{
		/**
		* Getting Help.
		*
		* @package s2Member\Menu_Pages
		* @since 151218
		*/
		class c_ws_plugin__s2member_menu_page_help
			{
				public function __construct ()
					{
						echo '<div class="wrap ws-menu-page">' . "\n";
						
						echo '<div class="wp-header-end"></div>'."\n";

						echo '<div class="ws-menu-page-toolbox">'."\n";
						c_ws_plugin__s2member_menu_pages_tb::display ();
						echo '</div>'."\n";

						echo '<h2>Getting Help with s2Member</h2>' . "\n";

						echo '<table class="ws-menu-page-table">' . "\n";
						echo '<tbody class="ws-menu-page-table-tbody">' . "\n";
						echo '<tr class="ws-menu-page-table-tr">' . "\n";
						echo '<td class="ws-menu-page-table-l">' . "\n";

						do_action("ws_plugin__s2member_during_help_page_before_left_sections", get_defined_vars ());
						do_action("ws_plugin__s2member_during_help_page_during_left_sections_before_help", get_defined_vars ());

						echo '<div class="ws-menu-page-group" title="Getting Help with s2Member" default-state="open">' . "\n";

						echo '<div class="ws-menu-page-section ws-plugin--s2member-help">' . "\n";
						echo '<p>s2Member is pretty easy to setup and install initially. Most of the official documentation is right here in your Dashboard (there is a lot of inline documentation built into the software). If you need assistance with s2Member, please try searching <a href="https://google.com" target="_blank" rel="external">the web</a>, our <a href="http://s2member.com/kb/" target="_blank" rel="external">knowledge base</a>, <a href="http://s2member.com/r/videos/" target="_blank" rel="external">videos</a>, <a href="http://s2member.com/r/forum/" target="_blank" rel="external">forums</a>, or <a href="http://s2member.com/r/codex/" target="_blank" rel="external">codex</a>.' . "\n";

						echo '<div class="ws-menu-page-hr"></div>' . "\n";

						echo '<h3 style="margin-bottom:0;">Troubleshooting Tips</h3>'."\n";
						echo '<p>Follow this <a href="http://s2member.com/r/common-troubleshooting-tips/" target="_blank" rel="external">Troubleshooting Guide</a>. Please read this first if you\'re having trouble, it has great information on how to troubleshoot.</p>'."\n";

						echo '<div class="ws-menu-page-hr"></div>' . "\n";

						echo '<h3 style="margin-bottom:0;">Enable Logging</h3>'."\n";
						echo '<p>Please use <a href="'.esc_attr(admin_url("/admin.php?page=ws-plugin--s2member-logs")).'">s2Member\'s logs</a>. These log files can be very helpful while troubleshooting.</p>'."\n";

						echo '<div class="ws-menu-page-hr"></div>' . "\n";

						echo '<h3 style="margin-bottom:0;">Search s2Member KB Articles</h3>'."\n";
						echo '<form method="get" action="http://s2member.com/kb/" target="_blank" onsubmit="if(this.q.value === \'enter search terms...\') this.q.value = \'\';" autocomplete="off">'."\n";
						echo '<p><input type="text" name="kb_q" value="enter search terms..." style="width:60%;" onfocus="if(this.value === \'enter search terms...\') this.value = \'\';" onblur="if(this.value === \'\') this.value = \'enter search terms...\';" /> <input type="submit" value="Search" style="font-size:120%; font-weight:normal;" /></p>'."\n";
						echo '</form>'."\n";

						echo '<div class="ws-menu-page-hr"></div>' . "\n";

						echo '<h3 style="margin-bottom:0;">Do a Web Search</h3>'."\n";

						echo '<p></p><p>A <a href="https://google.com" target="_blank" rel="external">Google search</a> for s2member and some keywords for what you\'re trying to solve/answer, is usually very helpful. You\'ll often get links to threads in our old forums, which are packed with helpful information, sometimes not easily found by other means.</p>'."\n";

						echo '<form method="get" action="https://google.com/search" target="_blank" onsubmit="this.q.value = \'s2member \' + this.q.value;" autocomplete="off">'."\n";
						echo '<p><input type="text" name="q" value="enter search terms..." style="width:60%;" onfocus="if(this.value === \'enter search terms...\') this.value = \'\';" onblur="if(this.value === \'\') this.value = \'enter search terms...\';" /> <input type="submit" value="Search" style="font-size:120%; font-weight:normal;" /></p>'."\n";
						echo '</form>'."\n";
	
						do_action("ws_plugin__s2member_during_start_page_during_left_sections_during_help", get_defined_vars ());

						echo '</div>' . "\n";

						echo '</div>' . "\n";

						do_action("ws_plugin__s2member_during_help_page_during_left_sections_after_help", get_defined_vars ());

						do_action("ws_plugin__s2member_during_help_page_during_left_sections_before_support", get_defined_vars ());

						echo '<div class="ws-menu-page-group" title="s2Member Support" default-state="open">' . "\n";

						echo '<div class="ws-menu-page-section ws-plugin--s2member-support">' . "\n";
						echo '<p style="max-width:900px;">Support for s2Member is provided by WP Sharks. Join us in our <a href="http://s2member.com/r/forums/" target="_blank" rel="external">community forum</a>.</p>'."\n";

						echo '<ul>'."\n";
						echo '<li>For pre-sale questions, please see: <a href="http://s2member.com/kb/kb-tag/pre-sale-faqs/" target="_blank" rel="external">Pre-Sale FAQs</a>.</li>'."\n";
						echo '<li>If you have other questions, please <a href="http://s2member.com/r/new-pre-sale-inquiry/" target="_blank" rel="external">contact our sales dept</a>.</li>'."\n";
						echo '</ul>'."\n";

						do_action("ws_plugin__s2member_during_start_page_during_left_sections_during_support", get_defined_vars ());

						echo '</div>' . "\n";

						echo '</div>' . "\n";

						do_action("ws_plugin__s2member_during_help_page_during_left_sections_after_support", get_defined_vars ());

						if (apply_filters("ws_plugin__s2member_during_help_page_during_left_sections_display_pro", !c_ws_plugin__s2member_utils_conds::pro_is_installed(), get_defined_vars ()))
							{
								do_action("ws_plugin__s2member_during_help_page_during_left_sections_before_pro", get_defined_vars ());

								echo '<div class="ws-menu-page-group" title="Upgrading to s2Member Pro<em>!</em>" default-state="open">' . "\n";

								echo '<div class="ws-menu-page-section ws-plugin--s2member-pro">' . "\n";
								echo '<p>Among many other features/enhancements, <a href="http://s2member.com/" target="_blank" rel="external">s2Member Pro</a> comes pre-integrated with additional payment gateways that work with s2Member Pro-Forms (a powerful s2Member Pro feature). For instance, Stripe (most popular), PayPal Payments Pro, and Authorize.Net. Each of these payment gateways allow you to accept most major credit cards on-site; i.e., customers never leave your site! s2Member Pro-Forms also support PayPal Express Checkout (if you integrate with PayPal Pro); for customers who actually prefer to pay with PayPal.</p>' . "\n";
								echo '<p><strong>Learn more here:</strong> <a href="http://s2member.com/features/" target="_blank" rel="external">s2Member Pro Features</a></p>'."\n";
								do_action("ws_plugin__s2member_during_help_page_during_left_sections_during_pro", get_defined_vars ());
								echo '</div>' . "\n";

								echo '</div>' . "\n";

								do_action("ws_plugin__s2member_during_start_page_during_left_sections_after_pro", get_defined_vars ());
							}
						do_action("ws_plugin__s2member_during_help_page_after_left_sections", get_defined_vars ());

						echo '</td>' . "\n";

						echo '<td class="ws-menu-page-table-r">' . "\n";
						c_ws_plugin__s2member_menu_pages_rs::display ();
						echo '</td>' . "\n";

						echo '</tr>' . "\n";
						echo '</tbody>' . "\n";
						echo '</table>' . "\n";

						echo '</div>' . "\n";
					}
			}
	}

new c_ws_plugin__s2member_menu_page_help ();
