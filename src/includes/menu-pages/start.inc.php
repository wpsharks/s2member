<?php
// @codingStandardsIgnoreFile
/**
* Getting Started.
*
* Copyright: © 2009-2022
* {@link http://wpsharks.com WP Sharks}
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
	exit ("Do not access this file directly.");

if (!class_exists ("c_ws_plugin__s2member_menu_page_start"))
{
	/**
	* Getting Started.
	*
	* @package s2Member\Menu_Pages
	* @since 110531
	*/
	class c_ws_plugin__s2member_menu_page_start
	{
		public function __construct ()
		{
			echo '<div class="wrap ws-menu-page">' . "\n";

			echo '<div class="wp-header-end"></div>'."\n";

			echo '<div class="ws-menu-page-toolbox">'."\n";
			c_ws_plugin__s2member_menu_pages_tb::display ();
			echo '</div>'."\n";

			echo '<h2>Quick Start</h2>' . "\n";

			echo '<table class="ws-menu-page-table">' . "\n";
			echo '<tbody class="ws-menu-page-table-tbody">' . "\n";
			echo '<tr class="ws-menu-page-table-tr">' . "\n";
			echo '<td class="ws-menu-page-table-l">' . "\n";

			do_action("ws_plugin__s2member_during_start_page_before_left_sections", get_defined_vars());

			echo
			'<div class="ws-menu-page-group" title="Getting Started Quick and Easy" default-state="open">',"\n",
				'<div class="ws-menu-page-section ws-plugin--s2member-quick-start-section">',"\n",
					'<iframe width="560" height="315" src="https://www.youtube.com/embed/videoseries?list=PL8gPolqFnYqtBVz0nVeN2sJgRVednq0jw" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',"\n",
				'</div>',"\n",
			'</div>',"\n";

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

new c_ws_plugin__s2member_menu_page_start ();
