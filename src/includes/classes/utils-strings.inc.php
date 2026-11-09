<?php
// @codingStandardsIgnoreFile
/**
 * String utilities.
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
	exit('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_utils_strings'))
{
	/**
	 * String utilities.
	 *
	 * @package s2Member\Utilities
	 * @since 3.5
	 */
	class c_ws_plugin__s2member_utils_strings
	{
		/**
		 * Array of all ampersand entities.
		 *
		 * Array keys are actually regex patterns *(very useful)*.
		 *
		 * @package s2Member\Utilities
		 * @since 111106
		 *
		 * @var array
		 */
		public static $ampersand_entities = array(
			'&amp;'       => '&amp;',
			'&#0*38;'     => '&#38;',
			'&#[xX]0*26;' => '&#x26;'
		);

		/**
		 * Array of all quote entities *(and entities for quote variations)*.
		 *
		 * Array keys are actually regex patterns *(very useful)*.
		 *
		 * @package s2Member\Utilities
		 * @since 111106
		 *
		 * @var array
		 */
		public static $quote_entities_w_variations = array(
			'&apos;'           => '&apos;',
			'&#0*39;'          => '&#39;',
			'&#[xX]0*27;'      => '&#x27;',
			'&lsquo;'          => '&lsquo;',
			'&#0*8216;'        => '&#8216;',
			'&#[xX]0*2018;'    => '&#x2018;',
			'&rsquo;'          => '&rsquo;',
			'&#0*8217;'        => '&#8217;',
			'&#[xX]0*2019;'    => '&#x2019;',
			'&quot;'           => '&quot;',
			'&#0*34;'          => '&#34;',
			'&#[xX]0*22;'      => '&#x22;',
			'&ldquo;'          => '&ldquo;',
			'&#0*8220;'        => '&#8220;',
			'&#[xX]0*201[cC];' => '&#x201C;',
			'&rdquo;'          => '&rdquo;',
			'&#0*8221;'        => '&#8221;',
			'&#[xX]0*201[dD];' => '&#x201D;',
			//260829.0025 Literal smart quotes can reach shortcode attributes unchanged; normalize them like their HTML entity forms.
			'‘'                => '‘',
			'’'                => '’',
			'“'                => '“',
			'”'                => '”'
		);

		/**
		 * Escapes double quotes.
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @param string $string Input string.
		 * @param int    $times Number of escapes. Defaults to 1.
		 * @param string $escape_char The character to be used in escapes.
		 *
		 * @return string Output string after double quotes are escaped.
		 */
		public static function esc_dq($string = '', $times = NULL, $escape_char = '\\')
		{
			$times = (is_numeric($times) && $times >= 0) ? (int)$times : 1;

			return str_replace('"', str_repeat($escape_char, $times).'"', (string)$string);
		}

		/**
		 * Escapes single quotes.
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @param string $string Input string.
		 * @param int    $times Number of escapes. Defaults to 1.
		 *
		 * @return string Output string after single quotes are escaped.
		 */
		public static function esc_sq($string = '', $times = NULL)
		{
			$times = (is_numeric($times) && $times >= 0) ? (int)$times : 1;

			return str_replace("'", str_repeat('\\', $times)."'", (string)$string);
		}

		/**
		 * Escapes JavaScript and single quotes.
		 *
		 * @package s2Member\Utilities
		 * @since 110901
		 *
		 * @param string $string Input string.
		 * @param int    $times Number of escapes. Defaults to 1.
		 *
		 * @return string Output string after JavaScript and single quotes are escaped.
		 */
		public static function esc_js_sq($string = '', $times = NULL)
		{
			$times = (is_numeric($times) && $times >= 0) ? (int)$times : 1;

			return str_replace("'", str_repeat('\\', $times)."'", str_replace(array("\r", "\n"), array('', '\\n'), str_replace("\\'", "'", (string)$string)));
		}

		/**
		 * Escapes dollars signs (for regex patterns).
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @param string $string Input string.
		 * @param int    $times Number of escapes. Defaults to 1.
		 *
		 * @return string Output string after dollar signs are escaped.
		 *
		 * @deprecated Starting with s2Member v120103, please use:
		 * ``c_ws_plugin__s2member_utils_strings::esc_refs()``.
		 */
		public static function esc_ds($string = '', $times = NULL)
		{
			$times = (is_numeric($times) && $times >= 0) ? (int)$times : 1;

			return str_replace('$', str_repeat('\\', $times).'$', (string)$string);
		}

		/**
		 * Escapes backreferences (for regex patterns).
		 *
		 * @package s2Member\Utilities
		 * @since 120103
		 *
		 * @param string $string Input string.
		 * @param int    $times Number of escapes. Defaults to 1.
		 *
		 * @return string Output string after backreferences are escaped.
		 */
		public static function esc_refs($string = NULL, $times = NULL)
		{
			$times = (is_numeric($times) && $times >= 0) ? (int)$times : 1;

			return str_replace(array('\\', '$'), array(str_repeat('\\', $times).'\\', str_repeat('\\', $times).'$'), (string)$string);
		}

		/**
		 * Sanitizes a string; by stripping characters NOT on a standard U.S. keyboard.
		 *
		 * @package s2Member\Utilities
		 * @since 111106
		 *
		 * @param string $string Input string.
		 *
		 * @return string Output string, after characters NOT on a standard U.S. keyboard have been stripped.
		 */
		public static function strip_2_kb_chars($string = '')
		{
			return preg_replace('/[^0-9A-Z'."\r\n\t".'\s`\=\[\]\\\;\',\.\/~\!@#\$%\^&\*\(\)_\+\|\}\{\:"\?\>\<\-]/i', '', remove_accents((string)$string));
		}

		/**
		 * Rejects values containing PHP opening tags (including encoded/mixed/double-encoded forms).
		 *
		 * @package s2Member\Utilities
		 * @since 241207
		 *
		 * @param string $input Input string to sanitize.
		 * @return string Original input when safe; a dash when PHP opening tags are detected.
		 */
		public static function strip_php_tags($input = '') {
				$copy = (string) $input;

				//251002 Collapse mixed/double encodings (HTML entities + URL encodings).
				$flags = ENT_QUOTES | (defined('ENT_HTML5') ? ENT_HTML5 : 0);
				for ($i = 0; $i < 3; $i++) {
						$copy = html_entity_decode($copy, $flags, 'UTF-8'); // &lt;, &#60;, &#x3C;, etc.
						$copy = rawurldecode($copy);                        // %3C, %3F, %3E, etc.
				}

				//261109.1512 Decode only for PHP-tag detection; return the original safe value so its final output context controls encoding.
				if (strpos($copy, '<?') !== false) {
					return '-';
				}

				return $input;
		}

		/**
		 * Recursively replaces suspicious values in arrays/objects using strip_php_tags().
		 *
		 * @package s2Member\Utilities
		 * @since 251002
		 *
		 * @param mixed $input A scalar, array, or object to sanitize.
		 * @return mixed Original structure with suspicious scalar values replaced by a dash.
		 */
		public static function strip_php_tags_deep($input) {
				if (is_array($input)) {
						foreach ($input as $k => $v)
								$input[$k] = self::strip_php_tags_deep($v);
						return $input;
				}
				if (is_object($input)) {
						foreach (get_object_vars($input) as $k => $v)
								$input->$k = self::strip_php_tags_deep($v);
						return $input;
				}
				return self::strip_php_tags($input);
		}

		/**
		 * Trims deeply; alias of ``trim_deep``.
		 *
		 * @package s2Member\Utilities
		 * @since 111106
		 *
		 * @see s2Member\Utilities\c_ws_plugin__s2member_utils_strings::trim_deep()
		 * @see http://php.net/manual/en/function.trim.php
		 *
		 * @param string|array $value Either a string, an array, or a multi-dimensional array, filled with integer and/or string values.
		 * @param string|bool  $chars Optional. Defaults to false, indicating the default trim chars ` \t\n\r\0\x0B`. Or, set to a specific string of chars.
		 * @param string|bool  $extra_chars Optional. This is NOT possible with PHP alone, but here you can specify extra chars; in addition to ``$chars``.
		 *
		 * @return string|array Either the input string, or the input array; after all data is trimmed up according to arguments passed in.
		 */
		public static function trim($value = '', $chars = FALSE, $extra_chars = FALSE)
		{
			return c_ws_plugin__s2member_utils_strings::trim_deep($value, $chars, $extra_chars);
		}

		/**
		 * Trims deeply; or use {@link s2Member\Utilities\c_ws_plugin__s2member_utils_strings::trim()}.
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @see s2Member\Utilities\c_ws_plugin__s2member_utils_strings::trim()
		 * @see http://php.net/manual/en/function.trim.php
		 *
		 * @param string|array $value Either a string, an array, or a multi-dimensional array, filled with integer and/or string values.
		 * @param string|bool  $chars Optional. Defaults to false, indicating the default trim chars ` \t\n\r\0\x0B`. Or, set to a specific string of chars.
		 * @param string|bool  $extra_chars Optional. This is NOT possible with PHP alone, but here you can specify extra chars; in addition to ``$chars``.
		 *
		 * @return string|array Either the input string, or the input array; after all data is trimmed up according to arguments passed in.
		 */
		public static function trim_deep($value = '', $chars = FALSE, $extra_chars = FALSE)
		{
			$chars = (is_string($chars)) ? $chars : " \t\n\r\0\x0B";
			$chars = (is_string($extra_chars)) ? $chars.$extra_chars : $chars;

			if(is_array($value)) /* Handles all types of arrays.
				Note, we do NOT use ``array_map()`` here, because multiple args to ``array_map()`` causes a loss of string keys.
				For further details, see: <http://php.net/manual/en/function.array-map.php>. */
			{
				foreach($value as &$r) // Reference.
					$r = c_ws_plugin__s2member_utils_strings::trim_deep($r, $chars);
				return $value; // Return modified array.
			}
			return trim((string)$value, $chars);
		}

		/**
		 * Trims double quotes deeply.
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @param string|array $value Either a string, an array, or a multi-dimensional array, filled with integer and/or string values.
		 *
		 * @return string|array Either the input string, or the input array; after all data is trimmed up.
		 */
		public static function trim_dq_deep($value = '')
		{
			return c_ws_plugin__s2member_utils_strings::trim_deep($value, FALSE, '"');
		}

		/**
		 * Trims single quotes deeply.
		 *
		 * @package s2Member\Utilities
		 * @since 111106
		 *
		 * @param string|array $value Either a string, an array, or a multi-dimensional array, filled with integer and/or string values.
		 *
		 * @return string|array Either the input string, or the input array; after all data is trimmed up.
		 */
		public static function trim_sq_deep($value = '')
		{
			return c_ws_plugin__s2member_utils_strings::trim_deep($value, FALSE, "'");
		}

		/**
		 * Trims double and single quotes deeply.
		 *
		 * @package s2Member\Utilities
		 * @since 111106
		 *
		 * @param string|array $value Either a string, an array, or a multi-dimensional array, filled with integer and/or string values.
		 *
		 * @return string|array Either the input string, or the input array; after all data is trimmed up.
		 */
		public static function trim_dsq_deep($value = '')
		{
			return c_ws_plugin__s2member_utils_strings::trim_deep($value, FALSE, "'".'"');
		}

		/**
		 * Trims all single/double quote entity variations deeply.
		 *
		 * This is useful on Shortcode attributes mangled by a Visual Editor.
		 *
		 * @package s2Member\Utilities
		 * @since 111011
		 *
		 * @param string|array $value Either a string, an array, or a multi-dimensional array, filled with integer and/or string values.
		 *
		 * @return string|array Either the input string, or the input array; after all data is trimmed up.
		 */
		public static function trim_qts_deep($value = '')
		{
			$qts = implode('|', array_keys(c_ws_plugin__s2member_utils_strings::$quote_entities_w_variations));

			return is_array($value) ? array_map('c_ws_plugin__s2member_utils_strings::trim_qts_deep', $value) : preg_replace('/^(?:'.$qts.')+|(?:'.$qts.')+$/', '', (string)$value);
		}

		/**
		 * Trims HTML whitespace.
		 *
		 * This is useful on Shortcode content.
		 *
		 * @package s2Member\Utilities
		 * @since 140124
		 *
		 * @param string $string Input string to trim.
		 *
		 * @return string Output string with all HTML whitespace trimmed away.
		 */
		public static function trim_html($string = '')
		{
			$whitespace = '&nbsp;|\<br\>|\<br\s*\/\>|\<p\>(?:&nbsp;)*\<\/p\>';
			return preg_replace('/^(?:'.$whitespace.')+|(?:'.$whitespace.')+$/', '', (string)$string);
		}

		/**
		 * Wraps a string with the characters provided.
		 *
		 * This is useful when preparing an input array for ``c_ws_plugin__s2member_utils_arrays::in_regex_array()``.
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @param string|array $value Either a string, an array, or a multi-dimensional array, filled with integer and/or string values.
		 * @param string       $beg Optional. A string value to wrap at the beginning of each value.
		 * @param string       $end Optional. A string value to wrap at the ending of each value.
		 * @param bool         $wrap_e Optional. Defaults to false. Should empty strings be wrapped too?
		 *
		 * @return string|array Either the input string, or the input array; after all data is wrapped up.
		 */
		public static function wrap_deep($value = '', $beg = '', $end = '', $wrap_e = FALSE)
		{
			if(is_array($value)) /* Handles all types of arrays.
				Note, we do NOT use ``array_map()`` here, because multiple args to ``array_map()`` causes a loss of string keys.
				For further details, see: <http://php.net/manual/en/function.array-map.php>. */
			{
				foreach($value as &$r) // Reference.
					$r = c_ws_plugin__s2member_utils_strings::wrap_deep($r, $beg, $end, $wrap_e);
				return $value; // Return modified array.
			}
			return (strlen((string)$value) || $wrap_e) ? (string)$beg.(string)$value.(string)$end : (string)$value;
		}

		/**
		 * Escapes meta characters with ``preg_quote()`` deeply.
		 *
		 * @package s2Member\Utilities
		 * @since 110926
		 *
		 * @param string|array $value Either a string, an array, or a multi-dimensional array, filled with integer and/or string values.
		 * @param string       $delimiter Optional. If a delimiting character is specified, it will also be escaped via ``preg_quote()``.
		 *
		 * @return string|array Either the input string, or the input array; after all data is escaped with ``preg_quote()``.
		 */
		public static function preg_quote_deep($value = '', $delimiter = '')
		{
			if(is_array($value)) /* Handles all types of arrays.
				Note, we do NOT use ``array_map()`` here, because multiple args to ``array_map()`` causes a loss of string keys.
				For further details, see: <http://php.net/manual/en/function.array-map.php>. */
			{
				foreach($value as &$r) // Reference.
					$r = c_ws_plugin__s2member_utils_strings::preg_quote_deep($r, $delimiter);
				return $value; // Return modified array.
			}
			return preg_quote((string)$value, (string)$delimiter);
		}

		/**
		 * Generates a random string with letters/numbers/symbols.
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @param int  $length Optional. Defaults to `12`. Length of the random string.
		 * @param bool $special_chars Defaults to true. If false, special chars are NOT included.
		 * @param bool $extra_special_chars Defaults to false. If true, extra special chars are included.
		 *
		 * @return string A randomly generated string, based on parameter configuration.
		 */
		public static function random_str_gen($length = 0, $special_chars = TRUE, $extra_special_chars = FALSE)
		{
			$length = (is_numeric($length) && $length >= 0) ? (int)$length : 12;

			$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
			$chars .= ($extra_special_chars) ? '-_ []{}<>~`+=,.;:/?|' : '';
			$chars .= ($special_chars) ? '!@#$%^&*()' : '';

			for($i = 0, $random_str = ''; $i < $length; $i++)
				$random_str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);

			return $random_str;
		}

		/**
		 * Highlights PHP, and also Shortcodes.
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @param string $string Input string to be highlighted.
		 *
		 * @return string The highlighted string.
		 */
		public static function highlight_php($string = '')
		{
			$string = highlight_string(trim((string)$string), TRUE); // Start with PHP syntax, then Shortcodes.
			$string = preg_replace('/\[\/?_*s2[a-z0-9_\-]+.*?\]/i', '<span style="color:#164A61;">$0</span>', $string);
			return str_replace('<code>', '<code class="highlight-php">', $string);
		}

		/**
		 * Parses email addresses from a string or array.
		 *
		 * @package s2Member\Utilities
		 * @since 111009
		 *
		 * @param string|array $value Input string or an array is also fine.
		 *
		 * @return array Array of parsed email addresses.
		 */
		public static function parse_emails($value = '')
		{
			if(is_array($value)) /* Handles all types of arrays.
				Note, we do NOT use ``array_map()`` here, because multiple args to ``array_map()`` causes a loss of string keys.
				For further details, see: <http://php.net/manual/en/function.array-map.php>. */
			{
				$emails = array(); // Initialize array.
				foreach($value as $v) // Loop through array.
					$emails = array_merge($emails, c_ws_plugin__s2member_utils_strings::parse_emails($v));
				return $emails; // Return array.
			}
			$delimiter = (strpos((string)$value, ';') !== FALSE) ? ';' : ',';
			foreach(c_ws_plugin__s2member_utils_strings::trim_deep(preg_split('/'.preg_quote($delimiter, '/').'+/', (string)$value)) as $section)
			{
				if(preg_match('/\<(.+?)\>/', $section, $m) && strpos($m[1], '@') !== FALSE)
					$emails[] = $m[1]; // Email inside <brackets>.

				else if(strpos($section, '@') !== FALSE)
					$emails[] = $section;
			}
			return (!empty($emails)) ? $emails : array();
		}

		/**
		 * Base64 URL-safe encoding.
		 *
		 * @package s2Member\Utilities
		 * @since 110913
		 *
		 * @param string $string Input string to be base64 encoded.
		 * @param array  $url_unsafe_chars Optional. An array of un-safe characters. Defaults to: ``array('+', '/')``.
		 * @param array  $url_safe_chars Optional. An array of safe character replacements. Defaults to: ``array('-', '_')``.
		 * @param string $trim_padding_chars Optional. A string of padding chars to rtrim. Defaults to: `=~.`.
		 *
		 * @return string The base64 URL-safe encoded string.
		 */
		public static function base64_url_safe_encode($string = '', $url_unsafe_chars = array('+', '/'), $url_safe_chars = array('-', '_'), $trim_padding_chars = '=~.')
		{
			$string             = (string)$string; // Force string values here. String MUST be a string.
			$trim_padding_chars = (string)$trim_padding_chars; // And force this one too.

			$base64_url_safe = str_replace((array)$url_unsafe_chars, (array)$url_safe_chars, (string)base64_encode($string));
			$base64_url_safe = (strlen($trim_padding_chars)) ? rtrim($base64_url_safe, $trim_padding_chars) : $base64_url_safe;

			return $base64_url_safe; // Base64 encoded, with URL-safe replacements.
		}

		/**
		 * Base64 URL-safe decoding.
		 *
		 * Note, this function is backward compatible with routines supplied by s2Member in the past;
		 * where padding characters were replaced with `~` or `.`, instead of being stripped completely.
		 *
		 * @package s2Member\Utilities
		 * @since 110913
		 *
		 * @param string $base64_url_safe Input string to be base64 decoded.
		 * @param array  $url_unsafe_chars Optional. An array of un-safe character replacements. Defaults to: ``array('+', '/')``.
		 * @param array  $url_safe_chars Optional. An array of safe characters. Defaults to: ``array('-', '_')``.
		 * @param string $trim_padding_chars Optional. A string of padding chars to rtrim. Defaults to: `=~.`.
		 *
		 * @return string The decoded string.
		 */
		public static function base64_url_safe_decode($base64_url_safe = '', $url_unsafe_chars = array('+', '/'), $url_safe_chars = array('-', '_'), $trim_padding_chars = '=~.')
		{
			$base64_url_safe    = (string)$base64_url_safe; // Force string values here. This MUST be a string.
			$trim_padding_chars = (string)$trim_padding_chars; // And force this one too.

			$string = (strlen($trim_padding_chars)) ? rtrim($base64_url_safe, $trim_padding_chars) : $base64_url_safe;
			$string = (strlen($trim_padding_chars)) ? str_pad($string, strlen($string) % 4, '=', STR_PAD_RIGHT) : $string;
			$string = (string)base64_decode(str_replace((array)$url_safe_chars, (array)$url_unsafe_chars, $string));

			return $string; // Base64 decoded, with URL-safe replacements.
		}

		/**
		 * Generates an RSA-SHA1 signature.
		 *
		 * @package s2Member\Utilities
		 * @since 111017
		 *
		 * @param string $string Input string/data, to be signed by this routine.
		 * @param string $key The secret key that will be used in this signature.
		 *
		 * @return string|bool An RSA-SHA1 signature string, or false on failure.
		 */
		public static function rsa_sha1_sign($string = '', $key = '')
		{
			$key = c_ws_plugin__s2member_utils_strings::_rsa_sha1_key_fix_wrappers((string)$key);

			$signature = c_ws_plugin__s2member_utils_strings::_rsa_sha1_shell_sign((string)$string, (string)$key);

			if(empty($signature) && stripos(PHP_OS, 'win') === 0 && file_exists(($openssl = 'c:\\openssl-win32\\bin\\openssl.exe')))
				$signature = c_ws_plugin__s2member_utils_strings::_rsa_sha1_shell_sign((string)$string, (string)$key, $openssl);

			if(empty($signature) && stripos(PHP_OS, 'win') === 0 && file_exists(($openssl = 'c:\\openssl-win64\\bin\\openssl.exe')))
				$signature = c_ws_plugin__s2member_utils_strings::_rsa_sha1_shell_sign((string)$string, (string)$key, $openssl);

			//260816 PHP 8 returns OpenSSLAsymmetricKey objects instead of resources; false indicates key-loading failure on all supported PHP versions.
			if(empty($signature) && function_exists('openssl_get_privatekey') && function_exists('openssl_sign') && ($private_key = openssl_get_privatekey((string)$key)) !== FALSE)
			{
				openssl_sign((string)$string, $signature, $private_key, OPENSSL_ALGO_SHA1);
				//260816 openssl_free_key() is unnecessary on PHP 8+ and deprecated there; PHP 5-7 still use resource cleanup.
				if(PHP_VERSION_ID < 80000 && is_resource($private_key))
					openssl_free_key($private_key);
			}

			if(empty($signature)) // Now, if we're still empty, trigger an error here.
			{
				$error = 's2Member was unable to generate an RSA-SHA1 signature.'.
				         ' Please make sure your installation of PHP is compiled with OpenSSL: `openssl_sign()`.'.
				         ' See: http://php.net/manual/en/function.openssl-sign.php';

				//260816 PHP 8.4 deprecates trigger_error(..., E_USER_ERROR); preserve the previous fatal-style path on older PHP.
				if(PHP_VERSION_ID >= 80400)
					throw new \RuntimeException($error);
				else
					trigger_error($error, E_USER_ERROR);
			}

			return (!empty($signature)) ? $signature : FALSE;
		}

		/**
		 * Generates an RSA-SHA1 signature from the command line.
		 *
		 * Used by {@link s2Member\Utilities\c_ws_plugin__s2member_utils_strings::rsa_sha1_sign()}.
		 *
		 * @package s2Member\Utilities
		 * @since 111017
		 *
		 * @param string $string Input string/data, to be signed by this routine.
		 * @param string $key The secret key that will be used in this signature.
		 * @param string $openssl Optional. Defaults to `openssl`. Path to OpenSSL executable.
		 *
		 * @return string|bool An RSA-SHA1 signature string, or false on failure.
		 */
		public static function _rsa_sha1_shell_sign($string = '', $key = '', $openssl = '')
		{
			if(function_exists('shell_exec') && ($esa = 'escapeshellarg') && ($openssl = (($openssl && is_string($openssl)) ? $openssl : 'openssl')) && ($temp_dir = c_ws_plugin__s2member_utils_dirs::get_temp_dir()))
			{
				file_put_contents(($string_file = $temp_dir.'/'.md5(uniqid('', TRUE).'rsa-sha1-string').'.tmp'), (string)$string);
				file_put_contents(($private_key_file = $temp_dir.'/'.md5(uniqid('', TRUE).'rsa-sha1-private-key').'.tmp'), (string)$key);
				file_put_contents(($rsa_sha1_sig_file = $temp_dir.'/'.md5(uniqid('', TRUE).'rsa-sha1-sig').'.tmp'), '');

				@shell_exec($esa($openssl).' sha1 -sign '.$esa($private_key_file).' -out '.$esa($rsa_sha1_sig_file).' '.$esa($string_file));
				$signature = file_get_contents($rsa_sha1_sig_file); // Do NOT trim here. Was the signature was written?
				unlink($rsa_sha1_sig_file).unlink($private_key_file).unlink($string_file); // Cleanup.
			}
			return (!empty($signature)) ? $signature : FALSE;
		}

		/**
		 * Fixes incomplete private key wrappers for RSA-SHA1 signing.
		 *
		 * Used by {@link s2Member\Utilities\c_ws_plugin__s2member_utils_strings::rsa_sha1_sign()}.
		 *
		 * @package s2Member\Utilities
		 * @since 111017
		 *
		 * @param string $key The secret key to be used in an RSA-SHA1 signature.
		 *
		 * @return string Key with incomplete wrappers corrected, when/if possible.
		 *
		 * @see http://www.faqs.org/qa/qa-14736.html
		 */
		public static function _rsa_sha1_key_fix_wrappers($key = '')
		{
			if(($key = trim((string)$key)) && (strpos($key, '-----BEGIN RSA PRIVATE KEY-----') === FALSE || strpos($key, '-----END RSA PRIVATE KEY-----') === FALSE))
			{
				foreach(($lines = c_ws_plugin__s2member_utils_strings::trim_deep(preg_split('/['."\r\n".']+/', $key))) as $line => $value)
					if(strpos($value, '-') === 0) // Begins with a boundary identifying character ( a hyphen `-` )?
					{
						$boundaries = (empty($boundaries)) ? 1 : $boundaries + 1; // Counter.
						unset($lines[$line]); // Remove this boundary line. We'll fix these below.
					}
				if(empty($boundaries) || $boundaries <= 2) // Do NOT modify keys with more than 2 boundaries.
					$key = '-----BEGIN RSA PRIVATE KEY-----'."\n".implode("\n", $lines)."\n".'-----END RSA PRIVATE KEY-----';
			}
			return $key; // Always a trimmed string here.
		}

		/**
		 * Generates an HMAC-SHA1 signature.
		 *
		 * @package s2Member\Utilities
		 * @since 111017
		 *
		 * @param string $string Input string/data, to be signed by this routine.
		 * @param string $key The secret key that will be used in this signature.
		 *
		 * @return string An HMAC-SHA1 signature string.
		 */
		public static function hmac_sha1_sign($string = '', $key = '')
		{
			$key_64 = str_pad(((strlen((string)$key) > 64) ? pack('H*', sha1((string)$key)) : (string)$key), 64, chr(0x00));

			return pack('H*', sha1(($key_64 ^ str_repeat(chr(0x5c), 64)).pack('H*', sha1(($key_64 ^ str_repeat(chr(0x36), 64)).(string)$string))));
		}

		/**
		 * Generates an HMAC-SHA256 signature.
		 *
		 * @package s2Member\Utilities
		 * @since 111017
		 *
		 * @param string  $string Input string/data, to be signed by this routine.
		 * @param string  $key The secret key that will be used in this signature.
		 * @param boolean $binary Return binary format?
		 *
		 * @return string An HMAC-SHA256 signature string.
		 */
		public static function hmac_sha256_sign($string = '', $key = '', $binary = FALSE)
		{
			return hash_hmac('sha256', $string, $key, $binary);
		}

		/**
		 * Decodes unreserved chars encoded by PHP's ``urlencode()``, deeply.
		 *
		 * For further details regarding unreserved chars, see: {@link http://www.faqs.org/rfcs/rfc3986.html}.
		 *
		 * @package s2Member\Utilities
		 * @since 111017
		 *
		 * @see http://www.faqs.org/rfcs/rfc3986.html
		 *
		 * @param string|array $value Either a string, an array, or a multi-dimensional array, filled with integer and/or string values.
		 *
		 * @return string|array Either the input string, or the input array; after all unreserved chars are decoded properly.
		 */
		public static function urldecode_ur_chars_deep($value = array())
		{
			if(is_array($value)) /* Handles all types of arrays.
				Note, we do NOT use ``array_map()`` here, because multiple args to ``array_map()`` causes a loss of string keys.
				For further details, see: <http://php.net/manual/en/function.array-map.php>. */
			{
				foreach($value as &$r) // Reference.
					$r = c_ws_plugin__s2member_utils_strings::urldecode_ur_chars_deep($r);
				return $value; // Return modified array.
			}
			return str_replace(array('%2D', '%2E', '%5F', '%7E'), array('-', '.', '_', '~'), (string)$value);
		}

		public static function like_escape($string)
		{
			global $wpdb; // Global DB object reference.

			if(method_exists($wpdb, 'esc_like'))
				return $wpdb->esc_like($string);

			return like_escape($string); // Deprecated in WP v4.0.
		}

		/**
		 * Builds recognized IPN template values without modifying the original payment data.
		 *
		 * @since 261015
		 */
		public static function paypal_template_values($paypal, $coupon = array(), $locals = array())
		{
			//261109.1531 Collect PayPal and local context independently: IPN variants may omit values that later templates still reference.
			$values = array();
			foreach(array('txn_id', 'txn_baid', 'txn_cid', 'subscr_id', 'subscr_baid', 'subscr_cid', 'currency', 'currency_symbol', 'item_number', 'item_name', 'first_name', 'last_name', 'payer_email', 'initial', 'regular', 'initial_term', 'regular_term', 'recurring') as $key)
				$values[$key] = (isset($paypal[$key]) && is_scalar($paypal[$key])) ? (string)$paypal[$key] : '';

			$values['amount'] = isset($paypal['mc_gross']) ? (string)$paypal['mc_gross'] : '';
			$values['full_name'] = trim($values['first_name'].' '.$values['last_name']);
			$values['user_ip'] = isset($locals['user_reg_ip']) ? (string)$locals['user_reg_ip'] : (isset($paypal['ip']) ? (string)$paypal['ip'] : '');
			//261016.0346 Do not parse missing or malformed periods in sparse payment notifications.
			$initial_term_is_valid = (bool)preg_match('/^[0-9]+ [DWMYL]$/i', $values['initial_term']);
			$regular_term_is_valid = (bool)preg_match('/^[0-9]+ [DWMYL]$/i', $values['regular_term']);
			$values['initial_cycle'] = $initial_term_is_valid ? c_ws_plugin__s2member_utils_time::period_term($values['initial_term']) : '';
			$values['regular_cycle'] = $regular_term_is_valid ? c_ws_plugin__s2member_utils_time::period_term($values['regular_term'], $values['recurring']) : '';
			$values['recurring/regular_cycle'] = $values['recurring'] ? ($regular_term_is_valid ? $values['recurring'].' / '.c_ws_plugin__s2member_utils_time::period_term($values['regular_term'], TRUE) : '') : '0 / non-recurring';

			//261109.1531 Coupon placeholders come from parsed coupon details rather than the basic PayPal IPN fields.
			foreach(array('full_coupon_code' => 'full_coupon_code', 'coupon_code' => 'coupon_code', 'coupon_affiliate_id' => 'affiliate_id') as $key => $coupon_key)
				$values[$key] = isset($coupon[$coupon_key]) && is_scalar($coupon[$coupon_key]) ? (string)$coupon[$coupon_key] : '';

			//261109.1531 Access and registration links are generated locally and are absent from many IPN events.
			foreach(array('sp_access_url', 'registration_url') as $key)
				if(isset($locals[$key])) $values[$key] = (string)$locals[$key];
			if(isset($paypal['hours']))
				$values['sp_access_exp'] = c_ws_plugin__s2member_utils_time::approx_time_difference(time(), strtotime('+'.$paypal['hours'].' hours'));

			//261109.1531 Use the resolved WordPress member for profile placeholders; the PayPal payload alone cannot supply them.
			if(isset($locals['user']) && is_object($locals['user']))
			{
				$user = $locals['user'];
				//261109.1512 WP_User has first_name/last_name, but user_email/user_login already include the prefix; avoid user_user_email/login keys.
				foreach(array('first_name', 'last_name') as $key)
				{
					if(isset($user->$key))
					{
						$values['user_'.$key] = (string)$user->$key;
					}
				}
				foreach(array('user_email', 'user_login') as $key)
				{
					if(isset($user->$key))
					{
						$values[$key] = (string)$user->$key;
					}
				}
				$values['user_full_name'] = trim((isset($user->first_name) ? $user->first_name : '').' '.(isset($user->last_name) ? $user->last_name : ''));
			}

			if(isset($locals['user_id'])) $values['user_id'] = (string)$locals['user_id'];
			if(isset($locals['modifying'])) $values['modification'] = (string)(int)$locals['modifying'];

			//261109.1531 Make custom profile fields available to eligible flows; the per-flow allowlist below decides which may render.
			if(isset($locals['fields']) && is_array($locals['fields']))
				foreach($locals['fields'] as $key => $value)
					if(is_string($key) && !array_key_exists(strtolower($key), $values)) $values[strtolower($key)] = maybe_serialize($value);

			//261109.1531 PayPal custom segments are positional cvN values; preserve their original order.
			foreach(explode('|', isset($paypal['custom']) ? (string)$paypal['custom'] : '') as $i => $value)
				$values['cv'.$i] = trim($value);

			return $values;
		}

		/**
		 * Returns the supported tracking fields for a PayPal return flow.
		 *
		 * @since 261107
		 */
		public static function paypal_return_template_values($paypal, $locals, $flow)
		{
			$values = self::paypal_template_values($paypal, array(), $locals);

			//261109.1512 Each return flow historically exposed different tracking placeholders; avoid exposing all collected PayPal/user fields.
			$common = array('currency', 'currency_symbol', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email');
			$subscriptions = array('subscr_id', 'subscr_baid', 'subscr_cid', 'initial', 'regular', 'recurring', 'initial_term', 'regular_term');
			$user = array('user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id');
			$fields = FALSE;

			switch($flow)
			{
				case 'sp':
					$allowed = array_merge($common, array('amount', 'txn_id', 'txn_baid', 'txn_cid', 'user_ip'));
					break;
				case 'member_signup':
					$allowed = array_merge($common, $subscriptions, array('user_ip'));
					break;
				case 'member_modify':
				case 'subscr_modify':
					$allowed = array_merge($common, $subscriptions, $user);
					$fields = TRUE;
					break;
				case 'ccap':
					$allowed = array_merge($common, array('amount', 'txn_id', 'txn_baid', 'txn_cid', 'subscr_id', 'subscr_baid', 'subscr_cid'), $user);
					$fields = TRUE;
					//261109.1512 Capability purchases also support legacy subscr_* placeholders, populated from the transaction IDs.
					foreach(array('id', 'baid', 'cid') as $suffix)
						$values['subscr_'.$suffix] = $values['txn_'.$suffix];
					break;
				default:
					return array();
			}

			//261109.1512 Custom profile fields were available in modification/capability tracking, not signup or specific-post tracking.
			if($fields && isset($locals['fields']) && is_array($locals['fields']))
			{
				foreach($locals['fields'] as $key => $value)
				{
					if(is_string($key) && !in_array(strtolower($key), $allowed, TRUE))
					{
						$values[strtolower($key)] = maybe_serialize($value);
						$allowed[] = strtolower($key);
					}
				}
			}

			//261109.1531 Specific-post return tracking uses the IPN address, not the saved member registration IP.
			if($flow === 'sp') $values['user_ip'] = isset($paypal['ip']) ? (string)$paypal['ip'] : '';

			//261109.1512 Limit cvN placeholders to positions supplied by the PayPal custom field; do not expose arbitrary indices.
			foreach(explode('|', isset($paypal['custom']) ? (string)$paypal['custom'] : '') as $i => $unused)
				$allowed[] = 'cv'.$i;
			return array_intersect_key($values, array_fill_keys($allowed, TRUE));
		}


		/**
		 * Returns supported tracking fields for each PayPal IPN flow.
		 *
		 * @since 261109
		 */
		public static function paypal_ipn_tracking_values($paypal, $coupon, $locals, $flow)
		{
			//261109.1512 Preserve the legacy placeholders per tracking flow; a shared broad list would expose fields absent from some events.
			static $allowed_lists = array(
				'subscr-modify-w-level-6-code' => array('subscr_id', 'subscr_baid', 'subscr_cid', 'currency', 'currency_symbol', 'initial', 'regular', 'recurring', 'initial_term', 'regular_term', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),
				'subscr-or-wa-w-level-6-code' => array('subscr_id', 'subscr_baid', 'subscr_cid', 'currency', 'currency_symbol', 'initial', 'regular', 'recurring', 'initial_term', 'regular_term', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),
				'subscr-or-wa-w-level-12-code' => array('subscr_id', 'subscr_baid', 'subscr_cid', 'currency', 'currency_symbol', 'initial', 'regular', 'recurring', 'initial_term', 'regular_term', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'user_ip', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id'),
				'wa-ccaps-wo-level-7-code' => array('subscr_id', 'txn_id', 'subscr_baid', 'txn_baid', 'subscr_cid', 'txn_cid', 'currency', 'currency_symbol', 'amount', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),
				'web-accept-sp-7-code' => array('amount', 'txn_id', 'currency', 'currency_symbol', 'txn_baid', 'txn_cid', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'user_ip', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id'),
			);

			if(!isset($allowed_lists[$flow])) return array();
			$allowed = $allowed_lists[$flow];
			$values = self::paypal_template_values($paypal, $coupon, $locals);

			//261109.1531 Legacy capability-purchase templates accept subscription IDs as aliases of transaction IDs.
			if(strpos($flow, 'wa-ccaps-wo-level-') === 0)
			{
				foreach(array('id', 'baid', 'cid') as $suffix)
				{
					$values['subscr_'.$suffix] = $values['txn_'.$suffix];
				}
			}

			//261109.1531 Some IPN tracking events use the gateway request IP rather than the saved registration IP.
			if($flow === 'subscr-or-wa-w-level-12-code' || $flow === 'web-accept-sp-7-code')
				$values['user_ip'] = isset($paypal['ip']) ? (string)$paypal['ip'] : '';

			//261109.1531 Only these tracking contexts historically expose arbitrary custom profile fields.
			$profile_flows = array('subscr-modify-w-level-6-code', 'subscr-or-wa-w-level-6-code', 'wa-ccaps-wo-level-7-code');
			if(in_array($flow, $profile_flows, TRUE) && isset($locals['fields']) && is_array($locals['fields']))
			{
				foreach($locals['fields'] as $key => $value)
				{
					if(is_string($key) && !array_key_exists(strtolower($key), $values))
					{
						$values[strtolower($key)] = maybe_serialize($value);
					}
				}
			}
			if(in_array($flow, $profile_flows, TRUE) && isset($locals['fields']) && is_array($locals['fields']))
			{
				foreach($locals['fields'] as $key => $unused)
				{
					if(is_string($key))
					{
						$allowed[] = strtolower($key);
					}
				}
			}

			//261109.1531 The PayPal custom field defines the allowed cvN positions, even when later templates name higher indices.
			foreach(explode('|', isset($paypal['custom']) ? (string)$paypal['custom'] : '') as $i => $unused)
				$allowed[] = 'cv'.$i;
			return array_intersect_key($values, array_fill_keys($allowed, TRUE));
		}


		/**
		 * Returns supported values for each PayPal notification template.
		 *
		 * @since 261109
		 */
		public static function paypal_ipn_template_values($paypal, $coupon, $locals, $group)
		{
			//261109.1512 Numeric suffixes (e.g., -1-rec/-2-sbj/-3-msg) identify template call sites, not membership levels.
			//261109.1512 Each call site has its own placeholder allowlist; fill_paypal_template_once() handles destination-specific escaping.
			static $allowed_lists = array(
				'sp-refund-reversal-1-url' => array('parent_txn_id', 'parent_txn_baid', 'parent_txn_cid', 'item_number', 'item_name', 'currency', 'currency_symbol', '-amount', '-fee', 'first_name', 'last_name', 'full_name', 'payer_email', 'user_ip'),
				'sp-refund-reversal-2-msg' => array('parent_txn_id', 'parent_txn_baid', 'parent_txn_cid', 'item_number', 'item_name', 'currency', 'currency_symbol', '-amount', '-fee', 'first_name', 'last_name', 'full_name', 'payer_email', 'user_ip'),

				'subscr-modify-w-level-1-rec' => array('subscr_id', 'subscr_baid', 'subscr_cid', 'currency', 'currency_symbol', 'initial', 'regular', 'initial_term', 'regular_term', 'initial_cycle', 'regular_cycle', 'recurring', 'recurring/regular_cycle', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),
				'subscr-modify-w-level-2-sbj' => array('subscr_id', 'subscr_baid', 'subscr_cid', 'currency', 'currency_symbol', 'initial', 'regular', 'initial_term', 'regular_term', 'initial_cycle', 'regular_cycle', 'recurring', 'recurring/regular_cycle', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),
				'subscr-modify-w-level-3-msg' => array('subscr_id', 'subscr_baid', 'subscr_cid', 'currency', 'currency_symbol', 'initial', 'regular', 'initial_term', 'regular_term', 'initial_cycle', 'regular_cycle', 'recurring', 'recurring/regular_cycle', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),
				'subscr-modify-w-level-4-url' => array('subscr_id', 'subscr_baid', 'subscr_cid', 'currency', 'currency_symbol', 'initial', 'regular', 'recurring', 'initial_term', 'regular_term', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),
				'subscr-modify-w-level-5-msg' => array('subscr_id', 'subscr_baid', 'subscr_cid', 'currency', 'currency_symbol', 'initial', 'regular', 'recurring', 'initial_term', 'regular_term', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),

				'subscr-or-rp-cancellation-w-level-1-url' => array('subscr_id', 'subscr_baid', 'subscr_cid', 'item_number', 'item_name', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),
				'subscr-or-rp-cancellation-w-level-2-msg' => array('subscr_id', 'subscr_baid', 'subscr_cid', 'item_number', 'item_name', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),

				'subscr-or-rp-eots-w-level-1-url' => array('eot_del_type', 'subscr_id', 'subscr_baid', 'subscr_cid', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),
				'subscr-or-rp-eots-w-level-2-msg' => array('eot_del_type', 'subscr_id', 'subscr_baid', 'subscr_cid', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),
				'subscr-or-rp-eots-w-level-3-url' => array('subscr_id', 'parent_txn_id', 'subscr_baid', 'subscr_cid', 'item_number', 'item_name', 'currency', 'currency_symbol', '-amount', '-fee', 'first_name', 'last_name', 'full_name', 'payer_email', 'user_ip', 'user_id'),
				'subscr-or-rp-eots-w-level-4-msg' => array('subscr_id', 'parent_txn_id', 'subscr_baid', 'subscr_cid', 'item_number', 'item_name', 'currency', 'currency_symbol', '-amount', '-fee', 'first_name', 'last_name', 'full_name', 'payer_email', 'user_ip', 'user_id'),

				'subscr-or-rp-payment-w-level-1-url' => array('subscr_id', 'subscr_baid', 'subscr_cid', 'currency', 'currency_symbol', 'amount', 'txn_id', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),
				'subscr-or-rp-payment-w-level-2-msg' => array('subscr_id', 'subscr_baid', 'subscr_cid', 'currency', 'currency_symbol', 'amount', 'txn_id', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),

				'subscr-or-wa-w-level-1-rec' => array('subscr_id', 'subscr_baid', 'subscr_cid', 'currency', 'currency_symbol', 'initial', 'regular', 'initial_term', 'regular_term', 'initial_cycle', 'regular_cycle', 'recurring', 'recurring/regular_cycle', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),
				'subscr-or-wa-w-level-2-sbj' => array('subscr_id', 'subscr_baid', 'subscr_cid', 'currency', 'currency_symbol', 'initial', 'regular', 'initial_term', 'regular_term', 'initial_cycle', 'regular_cycle', 'recurring', 'recurring/regular_cycle', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),
				'subscr-or-wa-w-level-3-msg' => array('subscr_id', 'subscr_baid', 'subscr_cid', 'currency', 'currency_symbol', 'initial', 'regular', 'initial_term', 'regular_term', 'initial_cycle', 'regular_cycle', 'recurring', 'recurring/regular_cycle', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),
				'subscr-or-wa-w-level-4-url' => array('subscr_id', 'subscr_baid', 'subscr_cid', 'currency', 'currency_symbol', 'initial', 'regular', 'recurring', 'initial_term', 'regular_term', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),
				'subscr-or-wa-w-level-5-msg' => array('subscr_id', 'subscr_baid', 'subscr_cid', 'currency', 'currency_symbol', 'initial', 'regular', 'recurring', 'initial_term', 'regular_term', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),
				'subscr-or-wa-w-level-7-rec' => array('registration_url', 'subscr_id', 'subscr_baid', 'subscr_cid', 'currency', 'currency_symbol', 'initial', 'regular', 'initial_term', 'regular_term', 'initial_cycle', 'regular_cycle', 'recurring', 'recurring/regular_cycle', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'user_ip', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id'),
				'subscr-or-wa-w-level-8-sbj' => array('registration_url', 'subscr_id', 'subscr_baid', 'subscr_cid', 'currency', 'currency_symbol', 'initial', 'regular', 'initial_term', 'regular_term', 'initial_cycle', 'regular_cycle', 'recurring', 'recurring/regular_cycle', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'user_ip', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id'),
				'subscr-or-wa-w-level-9-msg' => array('registration_url', 'subscr_id', 'subscr_baid', 'subscr_cid', 'currency', 'currency_symbol', 'initial', 'regular', 'initial_term', 'regular_term', 'initial_cycle', 'regular_cycle', 'recurring', 'recurring/regular_cycle', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'user_ip', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id'),
				'subscr-or-wa-w-level-10-url' => array('subscr_id', 'subscr_baid', 'subscr_cid', 'currency', 'currency_symbol', 'initial', 'regular', 'recurring', 'initial_term', 'regular_term', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'user_ip', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id'),
				'subscr-or-wa-w-level-11-msg' => array('subscr_id', 'subscr_baid', 'subscr_cid', 'currency', 'currency_symbol', 'initial', 'regular', 'recurring', 'initial_term', 'regular_term', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'user_ip', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id'),
				'subscr-or-wa-w-level-13-url' => array('subscr_id', 'subscr_baid', 'subscr_cid', 'currency', 'currency_symbol', 'initial', 'regular', 'recurring', 'initial_term', 'regular_term', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'modification', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),
				'subscr-or-wa-w-level-14-url' => array('subscr_id', 'subscr_baid', 'subscr_cid', 'currency', 'currency_symbol', 'amount', 'txn_id', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),
				'subscr-or-wa-w-level-15-msg' => array('subscr_id', 'subscr_baid', 'subscr_cid', 'currency', 'currency_symbol', 'amount', 'txn_id', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),

				'wa-ccaps-wo-level-1-rec' => array('subscr_id', 'txn_id', 'subscr_baid', 'txn_baid', 'subscr_cid', 'txn_cid', 'currency', 'currency_symbol', 'amount', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),
				'wa-ccaps-wo-level-2-sbj' => array('subscr_id', 'txn_id', 'subscr_baid', 'txn_baid', 'subscr_cid', 'txn_cid', 'currency', 'currency_symbol', 'amount', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),
				'wa-ccaps-wo-level-3-msg' => array('subscr_id', 'txn_id', 'subscr_baid', 'txn_baid', 'subscr_cid', 'txn_cid', 'currency', 'currency_symbol', 'amount', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),
				'wa-ccaps-wo-level-4-url' => array('subscr_id', 'txn_id', 'subscr_baid', 'txn_baid', 'subscr_cid', 'txn_cid', 'currency', 'currency_symbol', 'amount', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),
				'wa-ccaps-wo-level-5-url' => array('subscr_id', 'txn_id', 'subscr_baid', 'txn_baid', 'subscr_cid', 'txn_cid', 'currency', 'currency_symbol', 'amount', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),
				'wa-ccaps-wo-level-6-msg' => array('subscr_id', 'txn_id', 'subscr_baid', 'txn_baid', 'subscr_cid', 'txn_cid', 'currency', 'currency_symbol', 'amount', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id', 'user_first_name', 'user_last_name', 'user_full_name', 'user_email', 'user_login', 'user_ip', 'user_id'),

				'web-accept-sp-1-rec' => array('txn_id', 'amount', 'currency', 'currency_symbol', 'txn_baid', 'txn_cid', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'user_ip', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id', 'sp_access_url', 'sp_access_exp'),
				'web-accept-sp-2-sbj' => array('txn_id', 'amount', 'currency', 'currency_symbol', 'txn_baid', 'txn_cid', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'user_ip', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id', 'sp_access_url', 'sp_access_exp'),
				'web-accept-sp-3-msg' => array('txn_id', 'amount', 'currency', 'currency_symbol', 'txn_baid', 'txn_cid', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'user_ip', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id', 'sp_access_url', 'sp_access_exp'),
				'web-accept-sp-4-url' => array('sp_access_url', 'sp_access_exp', 'currency', 'currency_symbol', 'amount', 'txn_id', 'txn_baid', 'txn_cid', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'user_ip', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id'),
				'web-accept-sp-5-msg' => array('sp_access_url', 'sp_access_exp', 'currency', 'currency_symbol', 'amount', 'txn_id', 'txn_baid', 'txn_cid', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'user_ip', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id'),
				'web-accept-sp-6-url' => array('sp_access_url', 'sp_access_exp', 'currency', 'currency_symbol', 'amount', 'txn_id', 'txn_baid', 'txn_cid', 'item_number', 'item_name', 'first_name', 'last_name', 'full_name', 'payer_email', 'user_ip', 'full_coupon_code', 'coupon_code', 'coupon_affiliate_id'),
			);

			if(!isset($allowed_lists[$group])) return array();
			$allowed = $allowed_lists[$group];
			$values = self::paypal_template_values($paypal, $coupon, $locals);

			//261109.1531 Refund/reversal templates use parent transaction IDs plus their separate -amount/-fee placeholders.
			foreach(array('parent_txn_id','parent_txn_baid','parent_txn_cid') as $key)
				$values[$key] = isset($paypal[$key]) && is_scalar($paypal[$key]) ? (string)$paypal[$key] : '';
			$values['-amount'] = isset($paypal['mc_gross']) ? (string)$paypal['mc_gross'] : '';
			$values['-fee'] = isset($paypal['mc_fee']) ? (string)$paypal['mc_fee'] : '';
			$values['eot_del_type'] = isset($locals['eot_del_type']) ? (string)$locals['eot_del_type'] : '';

			//261109.1531 Capability notification templates support subscription-ID aliases for their transaction identifiers.
			if(strpos($group, 'wa-ccaps-wo-level-') === 0)
			{
				foreach(array('id','baid','cid') as $suffix)
				{
					$values['subscr_'.$suffix] = $values['txn_'.$suffix];
				}
			}

			//261109.1531 These notification contexts expose the gateway IP, while other contexts may use the saved registration IP.
			if(preg_match('/^(subscr-or-wa-w-level-(7|8|9|10|11)-|web-accept-sp-)/', $group))
				$values['user_ip'] = isset($paypal['ip']) ? (string)$paypal['ip'] : '';

			//261109.1531 Custom profile fields belong only to these notification templates; other events must keep their historical field lists.
			$profile_groups = array(
				'subscr-modify-w-level-5-msg',
				'subscr-modify-w-level-4-url',
				'subscr-or-rp-cancellation-w-level-2-msg',
				'subscr-or-rp-cancellation-w-level-1-url',
				'subscr-or-rp-eots-w-level-4-msg',
				'subscr-or-rp-eots-w-level-3-url',
				'subscr-or-rp-eots-w-level-2-msg',
				'subscr-or-rp-eots-w-level-1-url',
				'subscr-or-rp-payment-w-level-2-msg',
				'subscr-or-rp-payment-w-level-1-url',
				'subscr-or-wa-w-level-15-msg',
				'subscr-or-wa-w-level-14-url',
				'subscr-or-wa-w-level-13-url',
				'subscr-or-wa-w-level-5-msg',
				'subscr-or-wa-w-level-4-url',
				'wa-ccaps-wo-level-6-msg',
				'wa-ccaps-wo-level-5-url',
				'wa-ccaps-wo-level-4-url',
			);
			if(in_array($group, $profile_groups, TRUE) && isset($locals['fields']) && is_array($locals['fields']))
			{
				foreach($locals['fields'] as $key => $value)
				{
					if(is_string($key) && !array_key_exists(strtolower($key), $values))
					{
						$values[strtolower($key)] = maybe_serialize($value);
					}
				}
			}

			if(in_array($group, $profile_groups, TRUE) && isset($locals['fields']) && is_array($locals['fields']))
			{
				foreach($locals['fields'] as $key => $unused)
				{
					if(is_string($key))
					{
						$allowed[] = strtolower($key);
					}
				}
			}

			//261109.1531 The PayPal custom field defines the allowed cvN positions, even when later templates name higher indices.
			foreach(explode('|', isset($paypal['custom']) ? (string)$paypal['custom'] : '') as $i => $unused)
				$allowed[] = 'cv'.$i;
			return array_intersect_key($values, array_fill_keys($allowed, TRUE));
		}

		/**
		 * Replaces only codes in the original template; inserted values are never scanned again.
		 *
		 * @since 261015
		 */
		public static function fill_paypal_template_once($template, $values, $destination = 'text')
		{
			$values = array_change_key_case((array)$values, CASE_LOWER);

			return preg_replace_callback('/%%(.+?)%%/i', function($match) use($values, $destination)
			{
				$key = strtolower($match[1]);

				//261109.1531 Unknown placeholders are removed except for proxy return URLs, where a later consumer must resolve them.
				if(!array_key_exists($key, $values)) return ($destination === 'url-preserve') ? $match[0] : '';
				$value = self::strip_php_tags((string)$values[$key]);

				//261109.1531 Apply URL parameter encoding here; sp_access_url deliberately uses rawurlencode() instead of urlencode().
				if($destination === 'url' || $destination === 'url-preserve') return ($key === 'sp_access_url') ? rawurlencode($value) : urlencode($value);
				if($destination === 'html') return esc_html($value);

				//261109.1531 Mail headers must not contain CR/LF/NUL, even if the source is a configured template.
				if($destination === 'recipient' || $destination === 'subject')
				{
					$value = str_replace(array("\r", "\n", "\0"), '', $value);
					return ($destination === 'recipient' && in_array($key, array('first_name', 'last_name', 'full_name'), TRUE)) ? self::esc_dq($value) : $value;
				}

				if($destination === 'tracking')
				{
					//261109.1820 Keep '+' in email aliases and similar identifiers; replacing it breaks real tracking values.
					//261109.1820 Tracking templates can contain HTML or JavaScript, so other syntax characters still need context-aware handling before being allowed.
					return preg_replace('/[^A-Za-z0-9 @._+-]+/', '-', remove_accents($value));
				}

				return $value;
			}, (string)$template);
		}

		/**
		 * Generates a placeholder-only template preview without applying payment values.
		 * 261109.1514 Core IPN handlers now render templates directly; keep this public helper for compatibility.
		 * @since 261015
		 */
		public static function paypal_template_preview($template)
		{
			return preg_replace('/%%(.+?)%%/i', 'S2M_VALUE', (string)$template);
		}

		public static function fill_cvs($string, $custom, $urlencode = false)
		{
			$string = (string)$string;
			$custom = (string)$custom;

			foreach (preg_split('/\|/', $custom) as $_key => $_value) {
                $string = str_ireplace('%%cv'.$_key.'%%', $urlencode ? urlencode(trim($_value)) : trim($_value), $string);
            } // unset($_key, $_value); // Housekeeping.

			return $string;
		}
	}
}
