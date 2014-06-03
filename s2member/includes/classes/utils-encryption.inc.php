<?php
/**
* Encryption utilities.
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
* @package s2Member\Utilities
* @since 3.5
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit ("Do not access this file directly.");

if (!class_exists ("c_ws_plugin__s2member_utils_encryption"))
	{
		/**
		* Encryption utilities.
		*
		* @package s2Member\Utilities
		* @since 3.5
		*/
		class c_ws_plugin__s2member_utils_encryption
			{
				/**
				* Determines the proper encryption/decryption Key to use.
				*
				* @package s2Member\Utilities
				* @since 111106
				*
				* @param string $key Optional. Attempt to force a specific Key. Defaults to the one configured for s2Member. Short of that, defaults to: ``wp_salt()``.
				* @return string Proper encryption/decryption Key. If ``$key`` is passed in, and it validates, we'll return that. Otherwise use a default Key.
				*/
				public static function key ($key = FALSE)
					{
						$key = (!is_string ($key) || !strlen ($key)) ? $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["sec_encryption_key"] : $key;
						$key = (!is_string ($key) || !strlen ($key)) ? /* Use the installed WordPress salt. */ wp_salt () : $key;
						$key = (!is_string ($key) || !strlen ($key)) ? /* Default/backup. */ md5 ($_SERVER["HTTP_HOST"]) : $key;
						return /* Proper encryption/decryption key. */ $key;
					}
				/**
				* RIJNDAEL 256: two-way encryption/decryption, with a URL-safe base64 wrapper.
				*
				* Falls back on XOR encryption/decryption when/if mcrypt is not possible.
				*
				* @package s2Member\Utilities
				* @since 3.5
				*
				* @param string $string A string of data to encrypt.
				* @param string $key Optional. Key used for encryption. Defaults to the one configured for s2Member. Short of that, defaults to: ``wp_salt()``.
				* @param bool $w_md5_cs Optional. Defaults to true. When true, an MD5 checksum is used in the encrypted string *(recommended)*.
				* @return string Encrypted string.
				*/
				public static function encrypt ($string = FALSE, $key = FALSE, $w_md5_cs = TRUE)
					{
						if (function_exists ("mcrypt_encrypt") && in_array("rijndael-256", mcrypt_list_algorithms ()) && in_array("cbc", mcrypt_list_modes ()))
							{
								$string = /* Force a valid string value here. */ (is_string ($string)) ? $string : "";
								$string = /* Indicating this is an RIJNDAEL 256 encrypted string. */ (strlen ($string)) ? "~r2|" . $string : "";

								$key = /* Obtain encryption/decryption key. */ c_ws_plugin__s2member_utils_encryption::key ($key);
								$key = /* Proper key length. */ substr ($key, 0, mcrypt_get_key_size (MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC));

								$iv = c_ws_plugin__s2member_utils_strings::random_str_gen (mcrypt_get_iv_size (MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), false);

								if (strlen ($string) && is_string ($e = mcrypt_encrypt /* Encrypt the string. */ (MCRYPT_RIJNDAEL_256, $key, $string, MCRYPT_MODE_CBC, $iv)) && strlen ($e))
									$e = /* RIJNDAEL 256 encrypted string with IV and checksum built into itself. */ "~r2:" . $iv . (($w_md5_cs) ? ":" . md5 ($e) : "") . "|" . $e;

								return (isset ($e) && is_string ($e) && strlen ($e)) ? ($base64 = c_ws_plugin__s2member_utils_strings::base64_url_safe_encode ($e)) : "";
							}
						else // Fallback on XOR encryption.
							return c_ws_plugin__s2member_utils_encryption::xencrypt ($string, $key, $w_md5_cs);
					}
				/**
				* RIJNDAEL 256: two-way encryption/decryption, with a URL-safe base64 wrapper.
				*
				* Falls back on XOR encryption/decryption when mcrypt is not available.
				*
				* @package s2Member\Utilities
				* @since 3.5
				*
				* @param string $base64 A string of data to decrypt. Should still be base64 encoded.
				* @param string $key Optional. Key used originally for encryption. Defaults to the one configured for s2Member. Short of that, defaults to: ``wp_salt()``.
				* @return string Decrypted string.
				*/
				public static function decrypt ($base64 = FALSE, $key = FALSE)
					{
						$base64 = /* Force a valid string value here. */ (is_string ($base64)) ? $base64 : "";
						$e = (strlen ($base64)) ? c_ws_plugin__s2member_utils_strings::base64_url_safe_decode ($base64) : "";

						if (function_exists ("mcrypt_decrypt") && in_array("rijndael-256", mcrypt_list_algorithms ()) && in_array("cbc", mcrypt_list_modes ()) #
						&& strlen ($e) /* And, is this an RIJNDAEL 256 encrypted string? */ && preg_match ("/^~r2\:([a-zA-Z0-9]+)(?:\:([a-zA-Z0-9]+))?\|(.*?)$/s", $e, $iv_md5_e))
							{
								$key = /* Obtain encryption/decryption key. */ c_ws_plugin__s2member_utils_encryption::key ($key);
								$key = /* Proper key length. */ substr ($key, 0, mcrypt_get_key_size (MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC));

								if (strlen ($iv_md5_e[3]) && ( /* No checksum? */!$iv_md5_e[2] || /* Or, a matching checksum? */ $iv_md5_e[2] === md5 ($iv_md5_e[3])))
									$d = /* Decrypt the string. */ mcrypt_decrypt (MCRYPT_RIJNDAEL_256, $key, $iv_md5_e[3], MCRYPT_MODE_CBC, $iv_md5_e[1]);

								if (isset ($d) && /* Was ``$iv_md5_e[3]`` decrypted successfully? */ is_string ($d) && strlen ($d))

									if (strlen ($d = preg_replace ("/^~r2\|/", "", $d, 1, $r2)) && $r2)
										$d = rtrim /* Right-trim NULLS and EOTs. */ ($d, "\0\4");
									else // Else we need to empty this out.
										$d = /* Empty string. Invalid. */ "";

								return (isset ($d) && is_string ($d) && strlen ($d)) ? ($string = $d) : "";
							}
						else // Fallback on XOR decryption.
							return c_ws_plugin__s2member_utils_encryption::xdecrypt ($base64, $key);
					}
				/**
				* XOR two-way encryption/decryption, with a base64 wrapper.
				*
				* @package s2Member\Utilities
				* @since 3.5
				*
				* @param string $string A string of data to encrypt.
				* @param string $key Optional. Key used for encryption. Defaults to the one configured for s2Member. Short of that, defaults to: ``wp_salt()``.
				* @param bool $w_md5_cs Optional. Defaults to true. When true, an MD5 checksum is used in the encrypted string *(recommended)*.
				* @return string Encrypted string.
				*/
				public static function xencrypt ($string = FALSE, $key = FALSE, $w_md5_cs = TRUE)
					{
						$string = /* Force a valid string value here. */ (is_string ($string)) ? $string : "";
						$string = /* Indicating this is an XOR encrypted string. */ (strlen ($string)) ? "~xe|" . $string : "";

						$key = /* Obtain encryption/decryption key. */ c_ws_plugin__s2member_utils_encryption::key ($key);

						for ($i = 1, $e = ""; $i <= /* Will NOT run if ``$string`` has no length. */ strlen ($string); $i++)
							{
								$char = substr ($string, $i - 1, 1);
								$keychar = substr ($key, ($i % strlen ($key)) - 1, 1);
								$e .= chr (ord ($char) + ord ($keychar));
							}
						$e = /* XOR encrypted? */ (strlen ($e)) ? "~xe" . (($w_md5_cs) ? ":" . md5 ($e) : "") . "|" . $e : "";

						return (strlen ($e)) ? ($base64 = c_ws_plugin__s2member_utils_strings::base64_url_safe_encode ($e)) : "";
					}
				/**
				* XOR two-way encryption/decryption, with a base64 wrapper.
				*
				* @package s2Member\Utilities
				* @since 3.5
				*
				* @param string $base64 A string of data to decrypt. Should still be base64 encoded.
				* @param string $key Optional. Key used originally for encryption. Defaults to the one configured for s2Member. Short of that, defaults to: ``wp_salt()``.
				* @return string Decrypted string.
				*/
				public static function xdecrypt ($base64 = FALSE, $key = FALSE)
					{
						$base64 = /* Force a valid string value here. */ (is_string ($base64)) ? $base64 : "";
						$e = (strlen ($base64)) ? c_ws_plugin__s2member_utils_strings::base64_url_safe_decode ($base64) : "";

						if (strlen ($e) /* And, is this an XOR encrypted string? */ && preg_match ("/^~xe(?:\:([a-zA-Z0-9]+))?\|(.*?)$/s", $e, $md5_e))
							{
								$key = /* Obtain encryption/decryption key. */ c_ws_plugin__s2member_utils_encryption::key ($key);

								if (strlen ($md5_e[2]) && ( /* No checksum? */!$md5_e[1] || /* Or a matching checksum? */ $md5_e[1] === md5 ($md5_e[2])))

									for ($i = 1, $d = ""; $i <= /* Will NOT run if ``$md5_e[2]`` has no length. */ strlen ($md5_e[2]); $i++)
										{
											$char = substr ($md5_e[2], $i - 1, 1);
											$keychar = substr ($key, ($i % strlen ($key)) - 1, 1);
											$d .= chr (ord ($char) - ord ($keychar));
										}
								if (isset ($d) && /* Was ``$md5_e[2]`` decrypted successfully? */ is_string ($d) && strlen ($d))

									if (strlen ($d = preg_replace ("/^~xe\|/", "", $d, 1, $xe)) && $xe)
										$d = /* Just re-assign this here. Nothing more to do. */ $d;
									else // Else we need to empty this out.
										$d = /* Empty string. Invalid. */ "";

								return (isset ($d) && is_string ($d) && strlen ($d)) ? ($string = $d) : "";
							}
						else // Otherwise we must fail here with an empty string value.
							return /* Just return an empty string in this case. */ "";
					}
			}
	}
?>