<?php
// @codingStandardsIgnoreFile
/**
* HTML utilities.
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
* @package s2Member\Utilities
* @since 110720
*/
if(!defined('WPINC')) // MUST have WordPress.
	exit("Do not access this file directly.");

if (!class_exists ("c_ws_plugin__s2member_utils_html"))
	{
		/**
		* HTML utilities.
		*
		* @package s2Member\Utilities
		* @since 110720
		*/
		class c_ws_plugin__s2member_utils_html
			{
				/**
				* Returns a DOCTYPE tag along with the HEAD section and title tag.
				*
				* This method should NOT be called upon until
				* {@link s2Member\API_Constants\c_ws_plugin__s2member_constants::constants()}
				* has been processed. We need access to: ``WS_PLUGIN__S2MEMBER_API_CONSTANTS_MD5``.
				*
				* @package s2Member\Utilities
				* @since 110720
				*
				* @param string $doctype_html_head_title Optional. The title of the HTML document being generated.
				* @param string $doctype_html_head_action Optional. An action Hook to process during HEAD generation.
				* @return string A DOCTYPE tag along with the HEAD section and title tag, configured by parameters.
				*/
				public static function doctype_html_head ($doctype_html_head_title = FALSE, $doctype_html_head_action = FALSE)
					{
						$static_css = (!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['static_css'])) ? c_ws_plugin__s2member_utils_assets::ensure_static_assets('css') : array();
						$static_js = (!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['static_js']) && function_exists('wp_add_inline_script')) ? c_ws_plugin__s2member_utils_assets::ensure_static_assets('js') : array();
						$static_inline_js = (!empty($static_js['ok']) && !empty($static_js['assets']) && c_ws_plugin__s2member_utils_assets::static_js_text_delivery() === 'page') ? c_ws_plugin__s2member_utils_assets::static_js_inline_data($static_js['assets']) : '';
						if(!empty($static_js['ok']) && !empty($static_js['assets']) && c_ws_plugin__s2member_utils_assets::static_js_text_delivery() === 'page' && $static_inline_js === '')
							$static_js = array(); //260906.2049 Do not emit slot-based static JavaScript in standalone documents without its page-loaded text values.
						//260906.2219 Static disabled keeps the selected lightweight loader; requested static delivery that fails uses full WordPress so compatibility hooks are not skipped.
						$dynamic_css_url = c_ws_plugin__s2member_utils_assets::dynamic_asset_url(!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['static_css']) && (empty($static_css['ok']) || empty($static_css['assets'])));
						$dynamic_js_url = c_ws_plugin__s2member_utils_assets::dynamic_asset_url(!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['static_js']) && (empty($static_js['ok']) || empty($static_js['assets'])));

						ob_start (); // Start output buffering here so we can "return" the output from this utility.

						echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . "\n";

						echo '<html xmlns="http://www.w3.org/1999/xhtml">' . "\n";
						echo '<head>' . "\n";

						echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . "\n";

						if(!empty($static_css['ok']) && !empty($static_css['assets']))
						{
							foreach($static_css['assets'] as $asset)
								echo '<link href="'.esc_attr($asset['url']).'" type="text/css" rel="stylesheet" media="all" />'."\n";
						}
						else
							echo '<link href="' . esc_attr ($dynamic_css_url . "?ws_plugin__s2member_css=1&amp;qcABC=1&amp;ver=" . urlencode (c_ws_plugin__s2member_utilities::ver_checksum ())) . '" type="text/css" rel="stylesheet" media="all" />' . "\n";

						echo '<script type="text/javascript" src="' . esc_attr (site_url ("/wp-includes/js/jquery/jquery.js?ver=" . urlencode (c_ws_plugin__s2member_utilities::ver_checksum ()))) . '"></script>' . "\n";

						if(!empty($static_js['ok']) && !empty($static_js['assets']))
						{
							//260903.0453 This standalone frontend document cannot use WordPress's enqueue printer; emit the same page-specific globals immediately before the generated scripts.
							echo '<script type="text/javascript">'.c_ws_plugin__s2member_css_js_in::current_user_js_globals(TRUE).(($static_inline_js !== '') ? "\n".$static_inline_js : '').'</script>'."\n";
							foreach($static_js['assets'] as $asset)
								echo '<script type="text/javascript" src="'.esc_attr($asset['url']).'"></script>'."\n";
						}
						else
							echo '<script type="text/javascript" src="' . esc_attr ($dynamic_js_url . "?ws_plugin__s2member_js_w_globals=" . urlencode (WS_PLUGIN__S2MEMBER_API_CONSTANTS_MD5) . "&amp;qcABC=1&amp;ver=" . urlencode (c_ws_plugin__s2member_utilities::ver_checksum ())) . '"></script>' . "\n";

						if ($doctype_html_head_title) // Add <title></title> tag?
							echo '<title>' . $doctype_html_head_title . '</title>' . "\n";

						if ($doctype_html_head_action) // Add content from Hook?
							do_action($doctype_html_head_action, get_defined_vars ());

						echo '</head>' . "\n";

						return ob_get_clean ();
					}
			}
	}
