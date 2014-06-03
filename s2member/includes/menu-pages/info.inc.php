<?php
/**
* Menu page for the s2Member plugin (s2Member Info page).
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
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit ("Do not access this file directly.");

if (!class_exists ("c_ws_plugin__s2member_menu_page_info"))
	{
		/**
		* Menu page for the s2Member plugin (s2Member Info page).
		*
		* @package s2Member\Menu_Pages
		* @since 110531
		*/
		class c_ws_plugin__s2member_menu_page_info
			{
				public function __construct ()
					{
						echo '<div class="wrap ws-menu-page">' . "\n";

						echo '<div class="ws-menu-page-toolbox">'."\n";
						c_ws_plugin__s2member_menu_pages_tb::display ();
						echo '</div>'."\n";

						echo '<h2>Specs / Info</h2>' . "\n";

						echo '<table class="ws-menu-page-table">' . "\n";
						echo '<tbody class="ws-menu-page-table-tbody">' . "\n";
						echo '<tr class="ws-menu-page-table-tr">' . "\n";
						echo '<td class="ws-menu-page-table-l">' . "\n";

						echo '<img src="' . esc_attr ($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["dir_url"]) . '/images/brand-icon.png" class="ws-menu-page-brand-icon" alt="." />' . "\n";

						echo '<a href="' . esc_attr (add_query_arg ("c_check_ver", urlencode (c_ws_plugin__s2member_readmes::parse_readme_value ("Version")), c_ws_plugin__s2member_readmes::parse_readme_value ("Plugin URI"))) . '" target="_blank"><img src="' . esc_attr ($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["dir_url"]) . '/images/brand-updates.png" class="ws-menu-page-brand-updates" alt="." /></a>' . "\n";

						do_action("ws_plugin__s2member_during_info_page_before_left_sections", get_defined_vars ());

						if (apply_filters("ws_plugin__s2member_during_info_page_during_left_sections_display_readme", true, get_defined_vars ()))
							{
								do_action("ws_plugin__s2member_during_info_page_during_left_sections_before_readme", get_defined_vars ());

								echo '<div class="ws-menu-page-readme">' . "\n";
								do_action("ws_plugin__s2member_during_info_page_during_left_sections_during_readme", get_defined_vars ());
								echo c_ws_plugin__s2member_readmes::parse_readme () . "\n";
								echo '</div>' . "\n";

								do_action("ws_plugin__s2member_during_info_page_during_left_sections_after_readme", get_defined_vars ());
							}

						do_action("ws_plugin__s2member_during_info_page_after_left_sections", get_defined_vars ());

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

new c_ws_plugin__s2member_menu_page_info ();
?>