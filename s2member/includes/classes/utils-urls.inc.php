<?php
/**
* URL utilities.
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
if(realpath(__FILE__) === realpath($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");

if(!class_exists("c_ws_plugin__s2member_utils_urls"))
	{
		/**
		* URL utilities.
		*
		* @package s2Member\Utilities
		* @since 3.5
		*/
		class c_ws_plugin__s2member_utils_urls
			{
				/**
				* Builds a WordPress signup URL to `/wp-signup.php`.
				*
				* @package s2Member\Utilities
				* @since 3.5
				*
				* @return string Full URL to `/wp-signup.php`.
				*/
				public static function wp_signup_url()
					{
						return apply_filters("wp_signup_location", site_url("/wp-signup.php"));
					}
				/**
				* Builds a WordPress registration URL to `/wp-login.php?action=register`.
				*
				* @package s2Member\Utilities
				* @since 3.5
				*
				* @return string Full URL to `/wp-login.php?action=register`.
				*/
				public static function wp_register_url()
					{
						return apply_filters("wp_register_location", add_query_arg("action", urlencode("register"), wp_login_url()), get_defined_vars());
					}
				/**
				* Builds a BuddyPress registration URL to `/register`.
				*
				* @package s2Member\Utilities
				* @since 111009
				*
				* @return str|bool Full URL to `/register`, if BuddyPress is installed; else false.
				*/
				public static function bp_register_url()
					{
						if( /* If BuddyPress is installed. */c_ws_plugin__s2member_utils_conds::bp_is_installed())
							return site_url(((function_exists("bp_get_signup_slug")) ? bp_get_signup_slug()."/" : BP_REGISTER_SLUG."/"));

						return /* Default return false. */ false;
					}
				/**
				* Filters content redirection status *(uses 302s for browsers)*.
				*
				* @package s2Member\Utilities
				* @since 3.5
				*
				* @attaches-to ``add_filter("ws_plugin__s2member_content_redirect_status");``
				*
				* @param int|string $status A numeric redirection status code.
				* @return int|str A numeric status redirection code, possibly modified to a value of `302`.
				*
				* @see http://en.wikipedia.org/wiki/Web_browser_engine
				*/
				public static function redirect_browsers_using_302_status($status = FALSE)
					{
						$engines = "msie|trident|gecko|webkit|presto|konqueror|playstation";

						if( /* Default `301` status? */(int)$status === 301 && /* Have User-Agent? */ !empty($_SERVER["HTTP_USER_AGENT"]))
							if(($is_browser = preg_match("/(".$engines.")[\/ ]([0-9\.]+)/i", $_SERVER["HTTP_USER_AGENT"])))
								return /* Use 302 status. */ ($status = 302);

						return /* Else use existing status. */ $status;
					}
				/**
				* Encodes all types of amperands to `amp;`, for use in XHTML code.
				*
				* Note however, this is usually NOT necessary. Just use WordPress ``esc_html()`` or ``esc_attr()``.
				*
				* @package s2Member\Utilities
				* @since 111106
				*
				* @param string $url_uri_query A full URL, a partial URI, or just the query string.
				* @return string A full URL, a partial URI, or just the query string; after having been encoded by this routine.
				*/
				public static function e_amps($url_uri_query = FALSE)
					{
						return str_replace("&", "&amp;", c_ws_plugin__s2member_utils_urls::n_amps((string)$url_uri_query));
					}
				/**
				* Normalizes amperands to `&` when working with URLs, URIs, and/or query strings.
				*
				* @package s2Member\Utilities
				* @since 111106
				*
				* @param string $url_uri_query A full URL, a partial URI, or just the query string.
				* @return string A full URL, a partial URI, or just the query string; after having been normalized by this routine.
				*/
				public static function n_amps($url_uri_query = FALSE)
					{
						$amps = implode("|", array_keys /* Keys are regex patterns. */(c_ws_plugin__s2member_utils_strings::$ampersand_entities));

						return /* Normalizes amperands to `&`. */ preg_replace("/(?:".$amps.")/", "&", (string)$url_uri_query);
					}
				/**
				* Parses out a full valid URI, from either a full URL, or a partial URI.
				*
				* Uses {@link s2Member\Utilities\c_ws_plugin__s2member_utils_urls::parse_url()}.
				*
				* @package s2Member\Utilities
				* @since 3.5
				*
				* @param string $url_uri Either a full URL, or a partial URI.
				* @return string A valid URI, starting with `/` on success, else an empty string.
				*/
				public static function parse_uri($url_uri = FALSE)
					{
						if(is_string($url_uri) && is_array($parse = c_ws_plugin__s2member_utils_urls::parse_url($url_uri)))
							{
								$parse["path"] = (!empty($parse["path"])) ? ((strpos($parse["path"], "/") === 0) ? $parse["path"] : "/".$parse["path"]) : "/";

								return (!empty($parse["query"])) ? $parse["path"]."?".$parse["query"] : $parse["path"];
							}
						else // Force a string return value here.
							return /* Empty string. */ "";
					}
				/**
				* Parses a URL/URI with same args as PHP's ``parse_url()`` function.
				*
				* This works around issues with this PHP function in versions prior to 5.3.8.
				*
				* @package s2Member\Utilities
				* @since 111017
				*
				* @param string $url_uri Either a full URL, or a partial URI to parse.
				* @param bool|int $component Optional. See PHP documentation on ``parse_url()`` function.
				* @param bool $clean_path Defaults to true. s2Member will cleanup any return array `path`.
				* @return str|array|bool The return value from PHP's ``parse_url()`` function.
				* 	However, if ``$component`` is passed, s2Member forces a string return.
				*/
				public static function parse_url($url_uri = FALSE, $component = FALSE, $clean_path = TRUE)
					{
						$component = ($component === false || $component === -1) ? -1 : $component;

						if(is_string($url_uri) && /* And, there is a query string? */ strpos($url_uri, "?") !== false)
							{
								list($_, $query) = preg_split /* Split @ query string marker. */("/\?/", $url_uri, 2);
								$query = /* See: <https://bugs.php.net/bug.php?id=38143>. */ str_replace("://", urlencode("://"), $query);
								$url_uri = /* Put it all back together again, after the above modifications. */ $_."?".$query;
								unset /* A little housekeeping here. Unset these vars. */($_, $query);
							}
						$parse = @parse_url /* Let PHP work its magic via ``parse_url()``. */($url_uri, $component);

						if($clean_path && isset($parse["path"]) && is_string($parse["path"]) && !empty($parse["path"]))
							$parse["path"] = /* Clean up the path now. */ preg_replace("/\/+/", "/", $parse["path"]);

						return ($component !== -1) ? /* Force a string return value? */ (string)$parse : $parse;
					}
				/**
				* Responsible for all remote communications processed by s2Member.
				*
				* Uses ``wp_remote_request()`` through the `WP_Http` class.
				*
				* @package s2Member\Utilities
				* @since 3.5
				*
				* @param string $url Full URL with possible query string parameters.
				* @param string|array $post_vars Optional. Either a string of POST vars, or an array.
				* @param array $args Optional. An array of additional arguments used by ``wp_remote_request()``.
				* @param bool $return_array Optional. If true, instead of a string, we return an array with elements:
				* 	`code` *(http response code)*, `message` *(http response message)*, `headers` *(an array of lowercase headers)*, `body` *(the response body string)*, `response` *(full response array)*.
				* @return str|array|bool Requested response str|array from remote location *(see ``$return_array`` parameter )*; else (bool)`false` on failure.
				*/
				public static function remote($url = FALSE, $post_vars = FALSE, $args = FALSE, $return_array = FALSE)
					{
						if($url && /* We MUST have a valid full URL (string) before we do anything in this routine. */ is_string($url))
							{
								$args = /* Force array, and disable SSL verification. */ (!is_array($args)) ? array(): $args;

								$args["s2member"] = WS_PLUGIN__S2MEMBER_VERSION; // Indicates this is an s2Member connection.

								$args["sslverify"] = (!isset($args["sslverify"])) ? /* Off. */ false : $args["sslverify"];

								$args["httpversion"] = (!isset($args["httpversion"])) ? "1.1" : $args["httpversion"];

								if((is_array($post_vars) || is_string($post_vars)) && !empty($post_vars))
									$args = array_merge($args, array("method" => "POST", "body" => $post_vars));

								if(!empty($args["method"]) && strcasecmp((string)$args["method"], "DELETE") === 0 && version_compare(get_bloginfo("version"), "3.4", "<"))
									add_filter("use_curl_transport", "__return_false", /* ID via priority. */ 111209554);

								foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
								do_action("ws_plugin__s2member_before_wp_remote_request", get_defined_vars());
								unset($__refs, $__v);

								$response = /* Process remote request via ``wp_remote_request()``. */ wp_remote_request($url, $args);

								remove_filter /* Remove this Filter now. */("use_curl_transport", "__return_false", 111209554);

								if($return_array && !is_wp_error($response) && is_array($response))
									{
										$a = array("code" => (int)wp_remote_retrieve_response_code($response));
										$a = array_merge($a, array("message" => wp_remote_retrieve_response_message($response)));
										$a = array_merge($a, array("headers" => wp_remote_retrieve_headers($response)));
										$a = array_merge($a, array("body" => wp_remote_retrieve_body($response)));
										$a = array_merge($a, array("response" => $response));

										return /* Return array w/ ``$response`` too. */ $a;
									}
								else if(!is_wp_error($response) && is_array($response) /* Return body only. */)
									return /* Return ``$response`` body only. */ wp_remote_retrieve_body($response);

								else // Else this remote request has failed completely. Return false.
								return false; // Remote request failed, return false.
							}
						else // Else, return false.
							return false;
					}
				/**
				* Shortens a long URL, based on s2Member configuration.
				*
				* @package s2Member\Utilities
				* @since 111002
				*
				* @param string $url A full/long URL to be shortened.
				* @param string $api_sp Optional. A specific URL shortening API to use. Defaults to that which is configured in the s2Member Dashboard. Normally `tiny_url`, by default.
				* @param bool $try_backups Defaults to true. If a failure occurs with the first API, we'll try others until we have success.
				* @return str|bool The shortened URL on success, else false on failure.
				*/
				public static function shorten($url = FALSE, $api_sp = FALSE, $try_backups = TRUE)
					{
						$url = /* Force strings, else false. */ ($url && is_string($url)) ? $url : false;
						$api_sp = ($api_sp && is_string($api_sp)) ? strtolower($api_sp) : false;

						$default_url_shortener = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["default_url_shortener"];
						$default_custom_str_url_shortener = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["default_custom_str_url_shortener"];

						$apis = /* The shortening APIs currently pre-integrated in this release of s2Member. */ array("tiny_url", "goo_gl");

						if($url && ($api = /* If specific, use it. Otherwise, use the default shortening API. */ ($api_sp) ? $api_sp : $default_url_shortener))
							{
								if(!$api_sp && ($custom_url = trim(apply_filters("ws_plugin__s2member_url_shorten", false, get_defined_vars()))) && stripos($custom_url, "http") === 0)
									return /* Using whatever other shortener API you prefer, over the ones available by default with s2Member. */ ($shorter_url = $custom_url);

								else if(!$api_sp && stripos($default_custom_str_url_shortener, "http") === 0 && ($custom_url = trim(c_ws_plugin__s2member_utils_urls::remote(str_ireplace(array("%%s2_long_url%%", "%%s2_long_url_md5%%"), array(rawurlencode($url), urlencode(md5($url))), $default_custom_str_url_shortener)))) && stripos($custom_url, "http") === 0)
									return /* Using whatever other shortener API that a site owner prefers, over the ones available by default with s2Member. */ ($shorter_url = $custom_url);

								else if($api === "tiny_url" && ($tiny_url = trim(c_ws_plugin__s2member_utils_urls::remote("http://tinyurl.com/api-create.php?url=".rawurlencode($url)))) && stripos($tiny_url, "http") === 0)
									return /* The default tinyURL API: <http://tinyurl.com/api-create.php?url=http://www.example.com/>. */ ($shorter_url = $tiny_url);

								else if($api === "goo_gl" && ($goo_gl = json_decode(trim(c_ws_plugin__s2member_utils_urls::remote("https://www.googleapis.com/urlshortener/v1/url".((($goo_gl_key = apply_filters("ws_plugin__s2member_url_shorten_api_goo_gl_key", false))) ? "?key=".urlencode($goo_gl_key) : ""), json_encode(array("longUrl" => $url)), array("headers" => array("Content-Type" => "application/json")))), true)) && !empty($goo_gl["id"]) && is_string($goo_gl_url = $goo_gl["id"]) && stripos($goo_gl_url, "http") === 0)
									return /* Google API: <http://code.google.com/apis/urlshortener/v1/getting_started.html>. */ ($shorter_url = $goo_gl_url);

								else if /* Try backups? This way we can still shorten the URL with a backup. */($try_backups && count($apis) > 1)

									foreach /* Try other backup APIs now. */(array_diff($apis, array($api)) as $backup)
										if(($backup = c_ws_plugin__s2member_utils_urls::shorten($url, $backup, false)))
											return /* Success, we can return now. */ ($shorter_url = $backup);
							}
						return /* Default return value. */ false;
					}
				/**
				* Removes all s2Member-generated signatures from a full URL, a partial URI, or just a query string.
				*
				* @package s2Member\Utilities
				* @since 111106
				*
				* @param string $url_uri_query A full URL, a partial URI, or just the query string; to remove s2Member-generated signatures from.
				* @param string $sig_var Optional. The name of the s2Member-generated signature variable. Defaults to `_s2member_sig`.
				* @return string A full URL, a partial URI, or just the query string; without any s2Member-generated signatures.
				*/
				public static function remove_s2member_sigs($url_uri_query = FALSE, $sig_var = FALSE)
					{
						$url_uri_query = c_ws_plugin__s2member_utils_strings::trim((string)$url_uri_query, false, "?&=");
						$sig_var = ($sig_var && is_string($sig_var)) ? $sig_var : /* Use default. */ "_s2member_sig";
						$sigs = /* Remove all signatures. */ array_unique(array($sig_var, "_s2member_sig"));

						return trim(remove_query_arg($sigs, $url_uri_query), "?&=");
					}
				/**
				* Adds an s2Member-generated signature onto a full URL, a partial URI, or just a query string.
				*
				* @package s2Member\Utilities
				* @since 111106
				*
				* @param string $url_uri_query A full URL, a partial URI, or just a query string; to append the s2Member-generated signature onto.
				* @param string $sig_var Optional. The name of the s2Member-generated signature variable. Defaults to `_s2member_sig`.
				* @return string A full URL, a partial URI, or just a query string; with an s2Member-generated signature.
				*/
				public static function add_s2member_sig($url_uri_query = FALSE, $sig_var = FALSE)
					{
						$url_uri_query = $query = c_ws_plugin__s2member_utils_strings::trim((string)$url_uri_query, false, "?&=");
						$sig_var = ($sig_var && is_string($sig_var)) ? $sig_var : /* Use default. */ "_s2member_sig";

						$url_uri_query = $query = c_ws_plugin__s2member_utils_urls::remove_s2member_sigs($url_uri_query, $sig_var);
						if( /* Is this a full URL, or a partial URI? */preg_match("/^(?:[a-z]+\:\/\/|\/)/i", ($url_uri_query)))
							$query = trim(c_ws_plugin__s2member_utils_urls::parse_url($url_uri_query, PHP_URL_QUERY), "?&=");

						$key = /* Obtain the proper encryption/decryption key. */ c_ws_plugin__s2member_utils_encryption::key();

						if($url_uri_query && is_string /* We DO allow empty query strings. So we can sign a URL without one. */($query))
							{
								wp_parse_str /* Parse the query string into an array of ``$vars``. Then sort & serialize them into a string. */($query, $vars);
								$vars = c_ws_plugin__s2member_utils_arrays::remove_0b_strings(c_ws_plugin__s2member_utils_strings::trim_deep($vars));
								$vars = serialize(c_ws_plugin__s2member_utils_arrays::ksort_deep($vars));

								$sig = /* The s2Member-generated signature. */ ($time = time())."-".md5($key.$time.$vars);

								$url_uri_query = add_query_arg($sig_var, urlencode($sig), $url_uri_query);
							}
						return /* Possibly with a ``$sig_var`` variable. */ $url_uri_query;
					}
				/**
				* Verifies an s2Member-generated signature; in a full URL, a partial URI, or in just a query string.
				*
				* @package s2Member\Utilities
				* @since 111106
				*
				* @param string $url_uri_query A full URL, a partial URI, or just a query string. Must have an s2Member-generated signature to validate.
				* @param bool $check_time Optional. Defaults to false. If true, s2Member will also check if the signature has expired, based on ``$exp_secs``.
				* @param string|int $exp_secs Optional. Defaults to (int)10. If ``$check_time`` is true, s2Member will check if the signature has expired, based on ``$exp_secs``.
				* @param string $sig_var Optional. The name of the s2Member-generated signature variable. Defaults to `_s2member_sig`.
				* @return bool True if the s2Member-generated signature is OK, else false.
				*/
				public static function s2member_sig_ok($url_uri_query = FALSE, $check_time = FALSE, $exp_secs = FALSE, $sig_var = FALSE)
					{
						$url_uri_query = $query = c_ws_plugin__s2member_utils_strings::trim((string)$url_uri_query, false, "?&=");
						if( /* Is this a full URL, or a partial URI? */preg_match("/^(?:[a-z]+\:\/\/|\/)/i", ($url_uri_query)))
							$query = trim(c_ws_plugin__s2member_utils_urls::parse_url($url_uri_query, PHP_URL_QUERY), "?&=");

						$check_time = /* Are we checking time? Force a boolean value here. */ ($check_time) ? true : false;
						$exp_secs = (is_numeric($exp_secs)) ? (int)$exp_secs : /* Else 10 seconds by default here. */ 10;
						$sig_var = ($sig_var && is_string($sig_var)) ? $sig_var : /* Use default. */ "_s2member_sig";

						$key = /* Obtain the proper encryption/decryption key. */ c_ws_plugin__s2member_utils_encryption::key();

						if(preg_match_all /* Does ``$query`` have an s2Member-generated signature? */("/".preg_quote($sig_var, "/")."\=([0-9]+)-([^&$]+)/", $query, $sigs))
							{
								$query = /* Remove existing s2Member-generated signatures. */ c_ws_plugin__s2member_utils_urls::remove_s2member_sigs($query, $sig_var);

								wp_parse_str /* Parse the query string into an array of ``$vars``. Then sort & serialize them into a string. */($query, $vars);
								$vars = c_ws_plugin__s2member_utils_arrays::remove_0b_strings(c_ws_plugin__s2member_utils_strings::trim_deep($vars));
								$vars = serialize(c_ws_plugin__s2member_utils_arrays::ksort_deep($vars));

								($time = $sigs[1][($i = count($sigs[1]) - 1)]).($sig = $sigs[2][$i]).($valid_sig = md5($key.$time.$vars));

								if /* Checking time? This must NOT be older than ``$exp_secs`` seconds ago. */($check_time)
									return ($sig === $valid_sig && $time >= strtotime("-".$exp_secs." seconds"));

								else // Ignoring time? Just need to compare signatures in this case.
								return /* Do they match up? */ ($sig === $valid_sig);
							}
						else // Return false. No ``$query``, or no ``$sigs``.
							return /* False, it's NOT ok. */ false;
					}
			}
	}
?>