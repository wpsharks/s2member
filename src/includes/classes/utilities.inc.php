<?php
// @codingStandardsIgnoreFile
/**
 * General utilities.
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
 * @since 3.5
 */
if(!defined('WPINC')) // MUST have WordPress.
	exit ('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_utilities'))
{
	/**
	 * General utilities.
	 *
	 * @package s2Member\Utilities
	 * @since 3.5
	 */
	class c_ws_plugin__s2member_utilities
	{
		/**
		 * Evaluates PHP code, and 'returns' output.
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @param string $code A string of data, possibly with embedded PHP code.
		 * @param array  $vars Variables to put into the scope of `eval()`.
		 *
		 * @return string Output after PHP evaluation.
		 */
		public static function evl($code = '', $vars = array())
		{
			if(is_array($vars) && !empty($vars))
				extract($vars, EXTR_PREFIX_SAME, '_extract_');

			ob_start(); // Output buffer.

			eval ('?>'.trim($code));

			return ob_get_clean();
		}

		/**
		 * Buffers (gets) function output.
		 *
		 * A variable length of additional arguments are possible.
		 * Additional parameters get passed into the ``$function``.
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @param string $function Name of a function to call upon.
		 *
		 * @return string Output after call to function.
		 *   Any output is buffered and returned.
		 */
		public static function get($function = '')
		{
			$args     = func_get_args();
			$function = array_shift($args);

			if(is_string($function) && $function)
			{
				ob_start(); // Open output buffer.

				if(is_array($args) && !empty($args))
					$return = call_user_func_array($function, $args);

				else // There are no additional arguments to pass.
					$return = call_user_func($function);

				$echo = ob_get_clean(); // Close buffer.

				return !strlen($echo) && strlen($return) ? $return : $echo;
			}
			return NULL;
		}

		/**
		 * Builds a version checksum for this installation.
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @return string String with `[version]-[pro version]-[consolidated checksum]`.
		 */
		public static function ver_checksum()
		{
			$checksum = WS_PLUGIN__S2MEMBER_VERSION; // Software version string.
			$checksum .= (c_ws_plugin__s2member_utils_conds::pro_is_installed()) ? '-'.WS_PLUGIN__S2MEMBER_PRO_VERSION : ''; // Pro version string?
			$checksum .= '-'.abs(crc32($GLOBALS['WS_PLUGIN__']['s2member']['c']['checksum'].$GLOBALS['WS_PLUGIN__']['s2member']['o']['options_checksum'].$GLOBALS['WS_PLUGIN__']['s2member']['o']['options_version']));

			return $checksum; // (i.e., version-pro version-checksum)
		}

		/**
		 * String with current time details.
		 *
		 * @package s2Member\Utilities
		 * @since 130210
		 *
		 * @return string String with time representation (in UTC time).
		 */
		public static function time_details()
		{
			$time    = time(); // The time at this very moment.
			$details = date('D M jS, Y', $time).' @ precisely '.date('g:i a e', $time);

			return $details; // Return all details.
		}

		/**
		 * String with all version details *(for PHP, WordPress, s2Member, and Pro)*.
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @return string String with `PHP vX.XX :: WordPress vX.XX :: s2Member vX.XX :: s2Member Pro vX.XX`.
		 */
		public static function ver_details()
		{
			$details = 'PHP v'.PHP_VERSION.' :: WordPress v'.get_bloginfo('version').' :: s2Member v'.WS_PLUGIN__S2MEMBER_VERSION;
			$details .= (c_ws_plugin__s2member_utils_conds::pro_is_installed()) ? ' :: s2Member Pro v'.WS_PLUGIN__S2MEMBER_PRO_VERSION : '';

			return $details; // Return all details.
		}

		/**
		 * Generates s2Member Security Badge.
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @param string $v A variation number to display. Defaults to `1`.
		 * @param bool   $no_cache Defaults to false. If true, the HTML markup will contain query string params that prevent caching.
		 * @param bool   $display_on_failure . Defaults to false. True if we need to display the 'NOT yet verified' version inside admin panels.
		 *
		 * @return string HTML markup for display of s2Member Security Badge.
		 */
		public static function s_badge_gen($v = '1', $no_cache = FALSE, $display_on_failure = FALSE)
		{
			if($v && file_exists(($template = dirname(dirname(__FILE__)).'/templates/badges/s-badge.php')))
			{
				switch((integer)$v) // Width/height based on variation.
				{
					case 1: // Variation number 1.

						$width_height_attrs  = 'width="200" height="55"';
						$width_height_styles = 'width:200px; height:55px;';

						break; // Break switch loop.

					case 2: // Variation number 2.

						$width_height_attrs  = 'width="180" height="58"';
						$width_height_styles = 'width:180px; height:58px;';

						break; // Break switch loop.

					case 3: // Variation number 3.

						$width_height_attrs  = 'width="80" height="15"';
						$width_height_styles = 'width:80px; height:15px;';

						break; // Break switch loop.

					default: // Default case handler.

						$width_height_attrs = $width_height_styles = '';

						break; // Break switch loop.
				}
				$badge = trim(c_ws_plugin__s2member_utilities::evl(file_get_contents($template)));

				$badge = preg_replace('/%%v%%/i', (string)(integer)$v, $badge);
				$badge = preg_replace('/%%site_url%%/i', urlencode(home_url()), $badge);
				$badge = preg_replace('/%%no_cache%%/i', $no_cache ? '&amp;no_cache='.urlencode(mt_rand()) : '', $badge);
				$badge = preg_replace('/%%display_on_failure%%/i', $display_on_failure ? '&amp;display_on_failure=1' : '', $badge);
				$badge = preg_replace(array('/%%width_height_attrs%%/i', '/%%width_height_styles%%/i'), array($width_height_attrs, $width_height_styles), $badge);
			}
			return !empty($badge) ? $badge : ''; // Return Security Badge.
		}

		/**
		 * Acquires information about memory usage.
		 *
		 * @package s2Member\Utilities
		 * @since 110815
		 *
		 * @return string String with `Memory x MB :: Real Memory x MB :: Peak Memory x MB :: Real Peak Memory x MB`.
		 */
		public static function mem_details()
		{
			$memory           = number_format(memory_get_usage() / 1048576, 2, '.', '');
			$real_memory      = number_format(memory_get_usage(TRUE) / 1048576, 2, '.', '');
			$peak_memory      = number_format(memory_get_peak_usage() / 1048576, 2, '.', '');
			$real_peak_memory = number_format(memory_get_peak_usage(TRUE) / 1048576, 2, '.', '');

			$details = 'Memory '.$memory.' MB :: Real Memory '.$real_memory.' MB :: Peak Memory '.$peak_memory.' MB :: Real Peak Memory '.$real_peak_memory.' MB';

			return $details; // Return all details.
		}

		/**
		 * Acquires s2Member options for the Main Site of a Multisite Network.
		 *
		 * @package s2Member\Utilities
		 * @since 110912
		 *
		 * @return array Array of s2Member options for the Main Site.
		 */
		public static function mms_options()
		{
			return (is_multisite()) ? (array)get_site_option('ws_plugin__s2member_options') : array();
		}

		/**
		 * Builds an array of backtrace callers.
		 *
		 * @package s2Member\Utilities
		 * @since 110912
		 *
		 * @param array $debug_backtrace Optional. Defaults to ``debug_backtrace()``.
		 *
		 * @return array Array of backtrace callers (lowercase).
		 */
		public static function callers($debug_backtrace = NULL)
		{
			$callers = array(); // Initialize array.
			foreach(($debug_backtrace = is_array($debug_backtrace) ? $debug_backtrace : debug_backtrace()) as $caller)
				if(isset($caller['class'], $caller['function']) || (!isset($caller['class']) && isset($caller['function'])))
					$callers[] = isset($caller['class']) ? $caller['class'].'::'.$caller['function'] : $caller['function'];

			return array_map('strtolower', array_unique($callers));
		}

		/**
		 * Sends an email using WordPress' `wp_mail()`, supporting HTML formatting and auto From header.
		 *
		 * Automatically sets Content-Type to `text/html` if HTML markers are found.
		 * If no `From:` header is provided, adds one using s2Member options via `email_config()` filters.
		 *
		 * @since 250605
		 *
		 * @param string|string[] $to          Recipient email address(es).
		 * @param string          $subject     Email subject line.
		 * @param string          $message     Email body. May contain HTML.
		 * @param string|string[] $headers     Optional. Additional headers.
		 * @param string|string[] $attachments Optional. File paths to attach.
		 *
		 * @return bool True if the email was sent successfully, false otherwise.
		 */
		public static function mail($to, $subject, $message, $headers = '', $attachments = [])
		{
			// If From: not already in headers, add it using configured options.
			if (stripos($headers, 'From:') === false) {
				c_ws_plugin__s2member_email_configs::email_config(); // Setup From filters
				$from_email = isset($GLOBALS['WS_PLUGIN__']['s2member']['o']['reg_email_from_email']) ? $GLOBALS['WS_PLUGIN__']['s2member']['o']['reg_email_from_email'] : '';
				$from_name  = isset($GLOBALS['WS_PLUGIN__']['s2member']['o']['reg_email_from_name']) ? str_replace('"', "'", $GLOBALS['WS_PLUGIN__']['s2member']['o']['reg_email_from_name']) : '';
				$headers   .= (empty($headers) ? '' : "\r\n") . 'From: "' . $from_name . '" <' . $from_email . '>';
				c_ws_plugin__s2member_email_configs::email_config_release(); // Remove filters.
			}

			// Check HTML tags.
			$is_html = false;
			foreach (['</', '/>', '<i', '<b', '<h'] as $tag) {
				if (strpos($message, $tag) !== false) {
					$is_html = true;
					break;
				}
			}
			if ($is_html && strpos($message, '<p') === false && strpos($message, '<br') === false) {
				$message = wpautop($message);
			}

			// Add correct content type.
			$headers .= (empty($headers) ? '' : "\r\n");
			$headers .= 'Content-Type: ' . ($is_html ? 'text/html' : 'text/plain') . '; charset=UTF-8';

			return wp_mail($to, $subject, $message, $headers, $attachments);
		}

		/**
		 * Renders an s2Member email template editor (TinyMCE if HTML emails are enabled).
		 * Falls back to a plain <textarea> if HTML emails are disabled.
		 * Displays a soft warning if the database charset may strip emojis.
		 *
		 * @since 250610
		 *
		 * @param string $option_key  The s2Member option key (w/o prefix).
		 * @param string $editor_id   Optional. The DOM ID for the editor. Defaults to derived from option_key.
		 * @param array  $editor_args Optional. Extra arguments for `wp_editor()`.
		 */
		public static function editor($option_key, $editor_id = '', array $editor_args = [])
		{
			$value     = isset($GLOBALS['WS_PLUGIN__']['s2member']['o'][$option_key]) ? $GLOBALS['WS_PLUGIN__']['s2member']['o'][$option_key] : '';
			$editor_id = $editor_id ? $editor_id : strtr('ws_plugin__s2member_' . $option_key, ['_' => '-']);

			// If HTML emails are enabled, use wp_editor()
			$html_enabled = !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['html_emails_enabled']);
			if ($html_enabled) {
				$args = wp_parse_args($editor_args, [
					'textarea_name'     => 'ws_plugin__s2member_' . $option_key,
					'textarea_rows'     => 20,
					'editor_height'     => null,
					'media_buttons'     => false,
					'teeny'             => false,
					'quicktags'         => true,
					'tinymce'           => [
						'height'   => 400,
						'resize'   => true,
						'wp_autoresize_on' => false,
						'toolbar1' => 'formatselect | bold italic underline strikethrough | bullist numlist blockquote | alignleft aligncenter alignright | link unlink | undo redo | removeformat',
						'toolbar2' => '',
					],
				]);
				wp_editor($value, $editor_id, $args);
			} else {
				echo '<textarea name="ws_plugin__s2member_' . esc_attr($option_key) . '" id="' . esc_attr($editor_id) . '" rows="20">'
					. format_to_edit($value) . '</textarea><br />' . "\n";
			}

			// Check charset and warn about emojis.
			global $wpdb;
			$collation = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COLLATION_NAME
					FROM information_schema.COLUMNS
					WHERE TABLE_SCHEMA = %s
						AND TABLE_NAME = %s
						AND COLUMN_NAME = 'option_value'",
					$wpdb->dbname,
					$wpdb->options
				)
			);
			$charset = $collation ? strtolower(preg_replace('/_.+$/', '', $collation)) : '';
			if ($charset && $charset !== 'utf8mb4') {
				echo '<div class="ws-menu-page-hilite" style="margin-bottom:.2em;">';
				echo '⚠️ <strong>Note:</strong> Your database uses the <code>' . esc_html($charset) . '</code> charset for WordPress option values. Changes to your email will be lost if you use emojis, as they require the <code>utf8mb4</code> charset.';
				echo '</div>';
			}
		}
	}
}
