<?php
/**
* Menu page for the s2Member plugin (Logs page).
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
* @since 130210
*/
if(realpath(__FILE__) === realpath($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");

if(!class_exists("c_ws_plugin__s2member_menu_page_logs"))
	{
		/**
		* Menu page for the s2Member plugin (Integrations page).
		*
		* @package s2Member\Menu_Pages
		* @since 110531
		*/
		class c_ws_plugin__s2member_menu_page_logs
			{
				public function __construct()
					{
						echo '<div class="wrap ws-menu-page">'."\n";

						echo '<div id="icon-plugins" class="icon32"><br /></div>'."\n";
						echo '<h2>s2Member® Log Viewer</h2>'."\n";

						echo '<table class="ws-menu-page-table">'."\n";
						echo '<tbody class="ws-menu-page-table-tbody">'."\n";
						echo '<tr class="ws-menu-page-table-tr">'."\n";
						echo '<td class="ws-menu-page-table-l">'."\n";

						echo '<form method="post" name="ws_plugin__s2member_log_viewer" id="ws-plugin--s2member-log-viewer">' . "\n";

						do_action("ws_plugin__s2member_during_logs_page_before_left_sections", get_defined_vars());

						if(apply_filters("ws_plugin__s2member_during_logs_page_during_left_sections_display_logs", true, get_defined_vars()))
							{
								do_action("ws_plugin__s2member_during_logs_page_during_left_sections_before_logs", get_defined_vars());

								echo '<div class="ws-menu-page-group" title="s2Member® Log Files (for Debugging Purposes)" default-state="open">'."\n";

								echo '<div class="ws-menu-page-section ws-plugin--s2member-logs-section">'."\n";
								echo '<h3>s2Member® Log Files (for Debugging Purposes)</h3>'."\n";
								echo '<input type="button" value="Archive All Log Files (Start Fresh)" class="ws-menu-page-right ws-plugin--s2member-archive-logs-start-fresh-button" style="min-width:200px;" onclick="if(!confirm(\'Archive all existing log files and start fresh?\n\nPlease click OK to confirm.\')) return false; location.href = \''.esc_attr(add_query_arg(array('ws_plugin__s2member_logs_archive_start_fresh' => 'yes'))).'\';" />'."\n";
								echo '<input type="button" value="Download All Log Files (Zip File)" class="ws-menu-page-right ws-plugin--s2member-logs-download-zip-button" style="clear:right; min-width:200px;" onclick="location.href = \''.esc_attr(add_query_arg(array('ws_plugin__s2member_logs_download_zip' => 'yes'))).'\';" />'."\n";
								echo '<p><span class="ws-menu-page-hilite">s2Member® keeps a log of ALL of its communication with Payment Gateways. If you are having trouble, please review your log files below.</span></p>'."\n";
								echo '<p><strong>Debugging Tips:</strong> &nbsp;&nbsp; It is normal to see a few errors in your log files. This is because s2Member® logs ALL of its communication with Payment Gateways. Everything — not just successes. With that in mind, there will be some failures that s2Member® expects (to a certain extent); and s2Member® deals with these gracefully. What you\'re looking for here, are things that jump right out at you as being a major issue (e.g. when s2Member® makes a point of providing details to you in a log entry about problems that should be corrected).</p>'."\n";
								echo '<p><strong>Test Transaction Tips:</strong> &nbsp;&nbsp; Generally speaking, it is best to run test transactions for yourself. Then review the log file entries pertaining to your transaction. Does s2Member® report any major issues? If so, please read through any details that s2Member® provides in the log file. If you need assistance, please <a href="http://www.s2member.com/quick-s.php" target="_blank" rel="external">search s2Member.com</a> for answers to common questions.</p>'."\n";
								echo '<p><strong>Important Note:</strong> &nbsp;&nbsp; It is normal to have a <code>paypay-ipn.log</code> and/or a <code>paypay-rtn.log</code> file at all times. Ultimately, all Payment Gateway integrations supported by s2Member® pass through it\'s core PayPal® processors; even if you\'ve integrated with another Payment Gateway. If you are having trouble, and you don\'t find any errors in your Payment Gateway log files, please check the <code>paypay-ipn.log</code> and <code>paypay-rtn.log</code> files too. Regarding s2Member® Pro Forms... If you\'ve integrated s2Member® Pro Forms, you will NOT have a <code>paypay-rtn.log</code> file, because that particular processor is not used with Pro Form integrations. However, you will have a <code>paypay-ipn.log</code> file, and you will need to make a point of inspecting this file to ensure there were no post-processing issues.</p>'."\n";
								echo '<p style="font-style:italic;"><strong>Archived Log Files:</strong> &nbsp;&nbsp; All s2Member® log files are stored here: <code>'.esc_html(c_ws_plugin__s2member_utils_dirs::doc_root_path($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["logs_dir"])).'</code>. Any log files that contain the word <code>ARCHIVED</code> in their name, are files that reached a size of more than 2MB, so s2Member® archived them automatically to prevent any single log file from becoming too large. Archived log file names will also contain the date/time they were archived by s2Member®. These archived log files typically contain much older (and possibly outdated) log entries.</p>'."\n";

								$log_file_options = ""; // Initialize to an empty string.
								$view_log_file = (!empty($_POST["ws_plugin__s2member_log_file"])) ? esc_html($_POST["ws_plugin__s2member_log_file"]) : "";
								$logs_dir = $GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["logs_dir"];

								if(is_dir($logs_dir)) // Do we have a logs directory on this installation?
								{
									$log_files = scandir($logs_dir); sort($log_files, SORT_STRING);

									$log_file_options .= '<optgroup label="Current Log Files">';
									foreach($log_files as $log_file) // Build options for each current log file.
									{
										if(preg_match("/\.log$/", $log_file) && stripos($log_file, "-ARCHIVED-") === FALSE)
											$log_file_options .= '<option data-type="current" value="'.esc_attr($log_file).'"'.(($view_log_file === $log_file) ? ' style="font-weight:bold;" selected="selected"' : '').'>'.esc_html($log_file).'</option>';
									}
									$log_file_options .= '</optgroup>';

									if(stripos($log_file_options, '<option data-type="current"') === FALSE)
										$log_file_options .= '<option value="" disabled="disabled">— No current log files yet. —</option>';

									$log_file_options .= '<option value="" disabled="disabled"></option>';

									$log_file_options .= '<optgroup label="Archived Log Files">';
									foreach($log_files as $log_file) // Build options for each ARCHIVED log file.
									{
										if(preg_match("/\.log$/", $log_file) && stripos($log_file, "-ARCHIVED-") !== FALSE)
											$log_file_options .= '<option data-type="archived" value="'.esc_attr($log_file).'"'.(($view_log_file === $log_file) ? ' style="font-weight:bold;" selected="selected"' : '').'>'.esc_html($log_file).'</option>';
									}
									$log_file_options .= '</optgroup>';

									if(stripos($log_file_options, '<option data-type="archived"') === FALSE)
										$log_file_options .= '<option value="" disabled="disabled">— No log files archived yet. —</option>';
								}
								$log_file_options = '<option value="">— Choose a Log File to View —</option>'.
															'<option value="" disabled="disabled"></option>'.
								                     $log_file_options;

								echo '<table class="form-table">' . "\n";
								echo '<tbody>' . "\n";
								echo '<tr>' . "\n";

								echo '<td style="width:80%;">' . "\n";
								echo '<select name="ws_plugin__s2member_log_file" id="ws-plugin--s2member-log-file">' . "\n";
								echo $log_file_options."\n";
								echo '</select>' . "\n";
								echo '</td>' . "\n";

								echo '<td style="width:20%;">' . "\n";
								echo '<input type="submit" value="View" class="button-primary" />'."\n";
								echo '</td>' . "\n";

								echo '</tr>' . "\n";
								echo '</tbody>' . "\n";
								echo '</table>' . "\n";

								echo '<table class="form-table">' . "\n";
								echo '<tbody>' . "\n";
								echo '<tr>' . "\n";

								echo '<td>' . "\n";

								if($view_log_file && file_exists($logs_dir."/".$view_log_file) && filesize($logs_dir."/".$view_log_file))
									{
										echo '<p style="float:left; text-align:left;"><strong>Currently viewing log file:</strong> <a href="'.esc_attr(add_query_arg(array('ws_plugin__s2member_download_log_file' => $view_log_file))).'">'.esc_html($view_log_file).'</a></p>'."\n";
										echo '<p style="float:right; text-align:right;">[ <a href="'.esc_attr(add_query_arg(array('ws_plugin__s2member_download_log_file' => $view_log_file))).'"><strong>download this log file</strong></a> ]</p>'."\n";

										echo '<textarea id="ws-plugin--s2member-log-file-viewer" rows="100" wrap="on" spellcheck="false" style="font-family:monospace;">'.esc_html(file_get_contents($logs_dir."/".$view_log_file)).'</textarea>' . "\n";

										echo '<p style="float:left; text-align:left;"><strong>Currently viewing log file:</strong> <a href="'.esc_attr(add_query_arg(array('ws_plugin__s2member_download_log_file' => $view_log_file))).'">'.esc_html($view_log_file).'</a></p>'."\n";
										echo '<p style="float:right; text-align:right;">[ <a href="'.esc_attr(add_query_arg(array('ws_plugin__s2member_download_log_file' => $view_log_file))).'"><strong>download this log file</strong></a> ]</p>'."\n";
									}
								else if($view_log_file && file_exists($logs_dir."/".$view_log_file))
									echo '<textarea id="ws-plugin--s2member-log-file-viewer" rows="100" wrap="on" spellcheck="false" style="font-family:monospace; font-style:italic;">— Empty at this time —</textarea>' . "\n";

								else if($view_log_file && !file_exists($logs_dir."/".$view_log_file))
									echo '<textarea id="ws-plugin--s2member-log-file-viewer" rows="100" wrap="on" spellcheck="false" style="font-family:monospace; font-style:italic;">— File no longer exists —</textarea>' . "\n";

								else // Display an empty textarea in this default scenario.
									echo '<textarea id="ws-plugin--s2member-log-file-viewer" rows="100" wrap="on" spellcheck="false" style="font-family:monospace; font-style:italic;"></textarea>' . "\n";

								echo '</td>' . "\n";

								echo '</tr>' . "\n";
								echo '</tbody>' . "\n";
								echo '</table>' . "\n";

								do_action("ws_plugin__s2member_during_logs_page_during_left_sections_during_logs", get_defined_vars());
								echo '</div>'."\n";

								echo '</div>'."\n";

								do_action("ws_plugin__s2member_during_logs_page_during_left_sections_after_logs", get_defined_vars());
							}

						do_action("ws_plugin__s2member_during_logs_page_after_left_sections", get_defined_vars());

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

new c_ws_plugin__s2member_menu_page_logs();
?>