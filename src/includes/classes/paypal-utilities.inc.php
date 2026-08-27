<?php
// @codingStandardsIgnoreFile
/**
* PayPal utilities.
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
* @package s2Member\PayPal
* @since 3.5
*/
if(!defined('WPINC')) // MUST have WordPress.
	exit("Do not access this file directly.");

if(!class_exists("c_ws_plugin__s2member_paypal_utilities"))
	{
		/**
		* PayPal utilities.
		*
		* @package s2Member\PayPal
		* @since 3.5
		*/
		class c_ws_plugin__s2member_paypal_utilities
			{
				/**
				* Get ``$_POST`` or ``$_REQUEST`` vars from PayPal.
				*
				* @package s2Member\PayPal
				* @since 3.5
				*
				* @return array|bool An array of verified ``$_POST`` or ``$_REQUEST`` variables, else false.
				*/
				public static function paypal_postvars()
					{
						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_before_paypal_postvars", get_defined_vars());
						unset($__refs, $__v); // Housekeeping.
						/*
						 * Custom conditionals can be applied by filters.
						 */
						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v; // Vars by reference.
						if(!($postvars = apply_filters("ws_plugin__s2member_during_paypal_postvars_conditionals", array(), get_defined_vars())))
							{
								unset($__refs, $__v); // Housekeeping.

								if(!empty($_GET["tx"]) && empty($_GET["s2member_paypal_proxy"]))
									{
										$postback["tx"] = $_GET["tx"];
										$postback["cmd"] = "_notify-synch";
										$postback["at"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_identity_token"];

										$endpoint = ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_sandbox"]) ? "www.sandbox.paypal.com" : "www.paypal.com";

										if(preg_match("/^SUCCESS/i", ($response = trim(c_ws_plugin__s2member_utils_urls::remote("https://".$endpoint."/cgi-bin/webscr", $postback, array("timeout" => 20))))))
											{
												foreach(preg_split("/[\r\n]+/", preg_replace("/^SUCCESS/i", "", $response)) as $varline)
													{
														if (!empty($varline)) {
															list($key, $value) = preg_split("/\=/", $varline, 2);
															if (strlen($key = trim($key)) && strlen($value = trim($value)))
																$postvars[$key] = trim(stripslashes(urldecode($value)));
														}
													}
												$postvars = self::paypal_postvars_back_compat($postvars); // From verified data.

												$postvars = self::paypal_postvars_utf8($postvars);
												return apply_filters("ws_plugin__s2member_paypal_postvars", $postvars, get_defined_vars());
											}
										else return false;
									}
								//260817 Allow signed Checkout data through Return or custom handlers, but never use a browser handoff to authenticate the PayPal Notify endpoint.
								else if(empty($_GET["s2member_paypal_notify"]) && !empty($_GET["s2member_paypal_proxy"]) && $_GET["s2member_paypal_proxy"] === "paypal"
								&& array_key_exists("s2member_paypal_checkout_handoff", $_POST) && is_array($postvars = stripslashes_deep($_POST)))
									{
										if(!is_string($postvars["s2member_paypal_checkout_handoff"]) || $postvars["s2member_paypal_checkout_handoff"] === '')
											return false;

										$handoff = $postvars["s2member_paypal_checkout_handoff"];
										unset($postvars["s2member_paypal_checkout_handoff"]);

										//260817 Verify the complete PayPal Checkout browser-return payload before trusting any transaction or proxy metadata.
										if(!self::paypal_checkout_return_handoff_verify($handoff, $postvars))
											return false;

										if(empty($postvars["s2member_paypal_proxy"]) || $postvars["s2member_paypal_proxy"] !== "paypal"
										|| (string)$_GET["s2member_paypal_proxy"] !== (string)$postvars["s2member_paypal_proxy"])
											return false;

										//260817 If proxy-use routing is supplied in the URL, it must be scalar and match the signed browser-return metadata.
										if(!empty($_GET["s2member_paypal_proxy_use"]) && (!is_string($_GET["s2member_paypal_proxy_use"]) || empty($postvars["s2member_paypal_proxy_use"]) || $_GET["s2member_paypal_proxy_use"] !== (string)$postvars["s2member_paypal_proxy_use"]))
											return false;

										foreach($postvars as $key => $value)
											if(preg_match("/^s2member_/", $key))
												unset($postvars[$key]);

										$postvars = self::paypal_postvars_back_compat($postvars);
										$postvars = c_ws_plugin__s2member_utils_strings::trim_deep($postvars);
										$postvars = self::paypal_postvars_utf8($postvars);

										return apply_filters("ws_plugin__s2member_paypal_postvars", array_merge($postvars, array("proxy_verified" => "paypal")), get_defined_vars());
									}
								else if(!empty($_REQUEST) && is_array($postvars = stripslashes_deep($_REQUEST)))
									{
										foreach($postvars as $key => $value)
											if(preg_match("/^s2member_/", $key))
												unset($postvars[$key]);

										$postback = $postvars; // Copy.
										$postback["cmd"] = "_notify-validate";

										$postvars = self::paypal_postvars_back_compat($postvars);
										$postvars = c_ws_plugin__s2member_utils_strings::trim_deep($postvars);

										$postvars = self::paypal_postvars_utf8($postvars);
										$endpoint = ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_sandbox"]) ? "www.sandbox.paypal.com" : "www.paypal.com";

										if(!empty($_REQUEST["s2member_paypal_proxy"]) && !empty($_REQUEST["s2member_paypal_proxy_verification"]) && $_REQUEST["s2member_paypal_proxy_verification"] === c_ws_plugin__s2member_paypal_utilities::paypal_proxy_key_gen())
											return apply_filters("ws_plugin__s2member_paypal_postvars", array_merge($postvars, array("proxy_verified" => $_REQUEST["s2member_paypal_proxy"])), get_defined_vars());

										else if(empty($_POST) && !empty($_GET["s2member_paypal_proxy"]) && !empty($_GET["s2member_paypal_proxy_verification"]) && c_ws_plugin__s2member_utils_urls::s2member_sig_ok($_SERVER["REQUEST_URI"], false, false, "s2member_paypal_proxy_verification"))
											return apply_filters("ws_plugin__s2member_paypal_postvars", array_merge($postvars, array("proxy_verified" => $_GET["s2member_paypal_proxy"])), get_defined_vars());

										else if(trim(strtolower(c_ws_plugin__s2member_utils_urls::remote("https://".$endpoint."/cgi-bin/webscr", $postback, array("timeout" => 20)))) === "verified")
											return apply_filters("ws_plugin__s2member_paypal_postvars", $postvars, get_defined_vars());

										else return false;
									}
								else return false;
							}
						else // Else a custom conditional has been applied by Filters.
							{
								unset($__refs, $__v); // Housekeeping.
								$postvars = self::paypal_postvars_back_compat($postvars);
								return apply_filters("ws_plugin__s2member_paypal_postvars", $postvars, get_defined_vars());
							}
					}
				/**
				 * Convert PayPal post vars to UTF-8 when PayPal reports a usable charset.
				 *
				 * @since 260603
				 *
				 * @return array PayPal post vars.
				 */
				public static function paypal_postvars_utf8($postvars)
					{
						$postvars = (array) $postvars;

						if(empty($postvars["charset"]))
							return $postvars;

						$charset = trim((string) $postvars["charset"]);
						$charset = (strtolower($charset) === "gb2312") ? "GBK" : $charset;

						foreach($postvars as &$value)
							if(is_string($value))
								{
									$converted = false;

									if(function_exists("mb_convert_encoding"))
										{
											try
												{
													$converted = @mb_convert_encoding($value, "UTF-8", $charset);
												}
											catch(ValueError $exception)
												{
												}
										}

									if($converted === false && function_exists("iconv"))
										$converted = @iconv($charset, "UTF-8//IGNORE", $value);

									if($converted !== false)
										$value = $converted;
								}
						unset($value);

						return $postvars;
					}
				/**
				 * Back compat. PayPal post vars.
				 *
				 * @since 170722 PayPal IPN variable change.
				 *
				 * @return array Updated PayPal IPN data.
				 *
				 * @see https://github.com/websharks/s2member/issues/1112
				 */
				public static function paypal_postvars_back_compat($postvars)
					{
						$postvars = (array) $postvars;

						foreach ($postvars as $_key => $_value) {
							if (is_string($_key) && preg_match('/_?[0-9]+$/u', $_key)) {
								$_old_key = preg_replace('/_?[0-9]+$/u', '', $_key);
								if (!isset($postvars[$_old_key])) $postvars[$_old_key] = $_value;
							}
						} // unset($_key, $_old_key, $_value); // Housekeeping.

						return $postvars; // w/ back. compat keys.
					}
				/**
				 * Normalizes PayPal Checkout browser-return variables for handoff signing.
				 *
				 * @package s2Member\PayPal
				 * @since 260817
				 *
				 * @param array $postvars Browser-return variables.
				 *
				 * @return string|bool Canonical payload string, else false.
				 */
				public static function paypal_checkout_return_handoff_payload($postvars)
					{
						if(!is_array($postvars) || !$postvars)
							return false;

						$normalized = array();
						foreach($postvars as $key => $value)
							{
								$key = (string)$key;

								if($key === 's2member_paypal_checkout_handoff')
									continue;
								if(!is_scalar($value) && $value !== null)
									return false;

								$key = preg_replace('/\r\n|\r|\n/', "\r\n", $key);
								$value = preg_replace('/\r\n|\r|\n/', "\r\n", (string)$value);
								$normalized[$key] = $value;
							}
						if(!$normalized)
							return false;

						ksort($normalized, SORT_STRING);
						return http_build_query($normalized, '', '&', PHP_QUERY_RFC3986);
					}
				/**
				 * Generates the private signing key for PayPal Checkout browser-return handoffs.
				 *
				 * @package s2Member\PayPal
				 * @since 260817
				 *
				 * @return string Private signing key.
				 */
				public static function paypal_checkout_return_handoff_key()
					{
						return hash_hmac('sha256', 's2member_paypal_checkout_return_handoff|'.self::paypal_proxy_key_gen(), c_ws_plugin__s2member_utils_encryption::key());
					}
				/**
				 * Creates a short-lived PayPal Checkout browser-return handoff.
				 *
				 * @package s2Member\PayPal
				 * @since 260817
				 *
				 * @param array $postvars Verified browser-return variables.
				 *
				 * @return string Signed handoff token, else an empty string on failure.
				 */
				public static function paypal_checkout_return_handoff_create($postvars)
					{
						$payload = self::paypal_checkout_return_handoff_payload($postvars);

						if($payload === false)
							return '';

						$expires = time() + HOUR_IN_SECONDS;
						$signature = hash_hmac('sha256', $expires.'|'.$payload, self::paypal_checkout_return_handoff_key());

						// The browser gets only a transaction-scoped signature; reusable server-side secrets remain private.
						return $expires.'.'.$signature;
					}
				/**
				 * Verifies a PayPal Checkout browser-return handoff.
				 *
				 * @package s2Member\PayPal
				 * @since 260817
				 *
				 * @param string $handoff Signed handoff token.
				 * @param array  $postvars Browser-return variables received by POST.
				 *
				 * @return bool TRUE if valid; else FALSE.
				 */
				public static function paypal_checkout_return_handoff_verify($handoff, $postvars)
					{
						$handoff = trim((string)$handoff);

						if(!preg_match('/^([0-9]{10,12})\.([a-f0-9]{64})$/D', $handoff, $matches))
							return false;

						$expires = (int)$matches[1];
						$signature = (string)$matches[2];
						$payload = self::paypal_checkout_return_handoff_payload($postvars);

						if($payload === false || time() > $expires)
							return false;

						$expected = hash_hmac('sha256', $expires.'|'.$payload, self::paypal_checkout_return_handoff_key());
						return hash_equals($expected, $signature);
					}
				/**
				* Generates a PayPal Proxy Key, for simulated IPN responses.
				*
				* @package s2Member\PayPal
				* @since 3.5
				*
				* @return string A Proxy Key. It's an MD5 Hash, 32 chars, URL-safe.
				*/
				public static function paypal_proxy_key_gen()
					{
						global /* Multisite Networking. */ $current_site, $current_blog;

						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_before_paypal_proxy_key_gen", get_defined_vars());
						unset($__refs, $__v);

						if(is_multisite() && !is_main_site())
							$key = md5(c_ws_plugin__s2member_utils_encryption::xencrypt(strtolower($current_blog->domain.$current_blog->path), false, false));

						else {
							$host = ($GLOBALS['WS_PLUGIN__']['s2member']['o']['skip_ipn_domain_validation']) ? parse_url(home_url('/'), PHP_URL_HOST) : $_SERVER["HTTP_HOST"]; //250917
							$key  = md5(c_ws_plugin__s2member_utils_encryption::xencrypt(preg_replace("/\:[0-9]+$/", "", strtolower((string) $host)), false, false));
						}

						return apply_filters("ws_plugin__s2member_paypal_proxy_key_gen", $key, get_defined_vars());
					}
				/**
				* Acquires a short-lived dedupe lock.
				*
				* @package s2Member\PayPal
				* @since 260406
				*
				* @param string  $lock_option  Dedupe lock option name.
				* @param integer $lock_timeout Optional. Lock timeout in seconds.
				*
				* @return bool TRUE if lock acquired; else FALSE.
				*/
				public static function dedupe_lock_acquire($lock_option, $lock_timeout = 900)
					{
						if(!$lock_option || !is_string($lock_option))
							return FALSE;

						if(add_option($lock_option, time(), '', 'no'))
							return TRUE;

						$lock_time = (int)get_option($lock_option, 0);

						if($lock_time > 0 && (time() - $lock_time) >= abs($lock_timeout))
							{
								delete_option($lock_option);

								if(add_option($lock_option, time(), '', 'no'))
									return TRUE;
							}
						return FALSE;
					}
				/**
				* Releases a short-lived dedupe lock.
				*
				* @package s2Member\PayPal
				* @since 260406
				*
				* @param string $lock_option Dedupe lock option name.
				*
				* @return void
				*/
				public static function dedupe_lock_release($lock_option)
					{
						if($lock_option && is_string($lock_option))
							delete_option($lock_option);
					}
				/**
				* Gets a dedupe done-marker time and expires it lazily when needed.
				*
				* @package s2Member\PayPal
				* @since 260406
				*
				* @param string  $done_option Dedupe done-marker option name.
				* @param integer $done_ttl    Optional. Marker TTL in seconds.
				*
				* @return integer UNIX timestamp if still valid; else 0.
				*/
				public static function dedupe_done_time_get($done_option, $done_ttl = 0)
					{
						if(!$done_option || !is_string($done_option))
							return 0;

						$done_time = (int)get_option($done_option, 0);

						if($done_time > 0 && $done_ttl > 0 && (time() - $done_time) >= abs($done_ttl))
							{
								delete_option($done_option);
								return 0;
							}
						return $done_time;
					}
				/**
				* Marks a dedupe done-marker as done.
				*
				* @package s2Member\PayPal
				* @since 260406
				*
				* @param string $done_option Dedupe done-marker option name.
				*
				* @return void
				*/
				public static function dedupe_done_mark($done_option)
					{
						if($done_option && is_string($done_option))
							{
								if(!add_option($done_option, time(), '', 'no'))
									update_option($done_option, time(), false);
							}
					}
				/**
				* Occasionally cleans up expired dedupe markers.
				*
				* @package s2Member\PayPal
				* @since 260406
				*
				* @param string  $cleanup_transient Cleanup throttle transient name.
				* @param array   $markers           Array of arrays, each with `prefix` and `ttl` keys.
				* @param integer $throttle_ttl      Optional. Cleanup throttle TTL in seconds.
				*
				* @return void
				*/
				public static function dedupe_markers_cleanup($cleanup_transient, $markers = array(), $throttle_ttl = 21600)
					{
						if(!$cleanup_transient || !is_string($cleanup_transient) || !is_array($markers) || empty($markers))
							return;

						if(get_transient($cleanup_transient))
							return;

						global $wpdb;

						foreach($markers as $marker)
							if(!empty($marker['prefix']) && isset($marker['ttl']) && is_string($marker['prefix']))
								{
									$cutoff = (string)(time() - abs((int)$marker['ttl']));

									$wpdb->query("DELETE FROM `".$wpdb->options."` WHERE `option_name` LIKE '".esc_sql($marker['prefix'])."%' AND CAST(`option_value` AS UNSIGNED) > 0 AND CAST(`option_value` AS UNSIGNED) < '".$cutoff."'");
								}

						set_transient($cleanup_transient, time(), abs((int)$throttle_ttl));
					}
				/**
				* Calls upon the PayPal API, and returns the response.
				*
				* @package s2Member\PayPal
				* @since 3.5
				*
				* @param array $post_vars An array of variables to send through the PayPal API call.
				* @return array An array of variables returned by the PayPal API.
				*
				* @todo Optimize this routine with ``empty()`` and ``isset()``.
				* @todo Possibly integrate this API: {@link http://msdn.microsoft.com/en-us/library/ff512417.aspx}.
				*/
				public static function paypal_api_response($post_vars = FALSE)
					{
						global /* For Multisite support. */ $current_site, $current_blog;

						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_before_paypal_api_response", get_defined_vars());
						unset($__refs, $__v);

						$url = "https://".(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_sandbox"]) ? "api-3t.sandbox.paypal.com" : "api-3t.paypal.com")."/nvp";

						$post_vars = apply_filters("ws_plugin__s2member_paypal_api_post_vars", $post_vars, get_defined_vars());
						$post_vars = (is_array($post_vars)) ? $post_vars : array();

						$post_vars["VERSION"] = /* Configure the PayPal API version. */ "71.0";
						$post_vars["USER"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_api_username"];
						$post_vars["PWD"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_api_password"];
						$post_vars["SIGNATURE"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_api_signature"];

						foreach($post_vars as $_key => &$_value /* We need to clean these up. */)
							$_value = c_ws_plugin__s2member_paypal_utilities::paypal_api_nv_cleanup($_key, $_value);
						unset($_key, $_value);

						$input_time = /* Record input/nvp for logging. */ date("D M j, Y g:i:s a T");

						$nvp = trim(c_ws_plugin__s2member_utils_urls::remote($url, $post_vars, array("timeout" => 20)));

						$output_time = /* Now record after output time. */ date("D M j, Y g:i:s a T");

						wp_parse_str /* Parse NVP response. */($nvp, $response);
						$response = c_ws_plugin__s2member_utils_strings::trim_deep($response);

						if(!$response["ACK"] || !preg_match("/^(Success|SuccessWithWarning)$/i", $response["ACK"]))
							{
								if(strlen($response["L_ERRORCODE0"]) || $response["L_SHORTMESSAGE0"] || $response["L_LONGMESSAGE0"])
									/* translators: Exclude `%2$s` and `%3$s`. These are English details returned by PayPal. Replace `%2$s` and `%3$s` with: `Unable to process, please try again`, or something to that affect. Or, if you prefer, you could Filter ``$response["__error"]`` with `ws_plugin__s2member_paypal_api_response`. */
									$response["__error"] = sprintf(_x('Error #%1$s. %2$s. %3$s.', "s2member-front", "s2member"), $response["L_ERRORCODE0"], rtrim($response["L_SHORTMESSAGE0"], "."), rtrim($response["L_LONGMESSAGE0"], "."));

								else // Else, generate an error messsage - so something is reported back to the Customer.
									$response["__error"] = _x("Error. Please contact Support for assistance.", "s2member-front", "s2member");
							}
						$logt = c_ws_plugin__s2member_utilities::time_details ();
						$logv = c_ws_plugin__s2member_utilities::ver_details();
						$logm = c_ws_plugin__s2member_utilities::mem_details();
						$log4 = $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]."\nUser-Agent: ".@$_SERVER["HTTP_USER_AGENT"];
						$log4 = (is_multisite() && !is_main_site()) ? ($_log4 = $current_blog->domain.$current_blog->path)."\n".$log4 : $log4;
						$log2 = (is_multisite() && !is_main_site()) ? "paypal-api-4-".trim(preg_replace("/[^a-z0-9]/i", "-", $_log4), "-").".log" : "paypal-api.log";

						if(isset($post_vars["ACCT"]) && strlen($post_vars["ACCT"]) > 4)
							$post_vars["ACCT"] = str_repeat("*", strlen($post_vars["ACCT"]) - 4).substr($post_vars["ACCT"], -4);

						if($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["gateway_debug_logs"])
							if(is_dir($logs_dir = $GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["logs_dir"]))
								if(is_writable($logs_dir) && c_ws_plugin__s2member_utils_logs::archive_oversize_log_files())
									if(($log = "-------- Input vars: ( ".$input_time." ) --------\n".var_export($post_vars, true)."\n"))
										if(($log .= "-------- Output string/vars: ( ".$output_time." ) --------\n".$nvp."\n".var_export($response, true)))
											file_put_contents($logs_dir."/".$log2,
											                  "LOG ENTRY: ".$logt . "\n" . $logv."\n".$logm."\n".$log4."\n".
											                                       c_ws_plugin__s2member_utils_logs::conceal_private_info($log)."\n\n",
											                  FILE_APPEND);

						return apply_filters("ws_plugin__s2member_paypal_api_response", c_ws_plugin__s2member_paypal_utilities::_paypal_api_response_filters($response), get_defined_vars());
					}
				/**
				* A sort of callback function that Filters PayPal responses.
				*
				* Provides alternative explanations in some cases that require special attention.
				*
				* @package s2Member\PayPal
				* @since 3.5
				*
				* @param array $response Expects an array of response variables returned by the PayPal API.
				* @return array An array of variables returned by the PayPal API, after ``$response["__error"]`` is Filtered.
				*/
				public static function _paypal_api_response_filters($response = FALSE)
					{
						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("_ws_plugin__s2member_before_paypal_api_response_filters", get_defined_vars());
						unset($__refs, $__v);

						if(!empty($response["__error"]) && !empty($response["L_ERRORCODE0"]))
							{
								if((int)$response["L_ERRORCODE0"] === 10422)
									$response["__error"] = sprintf(_x("Error #%s. Transaction declined. Please use an alternate funding source.", "s2member-front", "s2member"), $response["L_ERRORCODE0"]);

								else if((int)$response["L_ERRORCODE0"] === 10435)
									$response["__error"] = sprintf(_x("Error #%s. Transaction declined. Express Checkout was NOT confirmed.", "s2member-front", "s2member"), $response["L_ERRORCODE0"]);

								else if((int)$response["L_ERRORCODE0"] === 10417)
									$response["__error"] = sprintf(_x("Error #%s. Transaction declined. Please use an alternate funding source.", "s2member-front", "s2member"), $response["L_ERRORCODE0"]);
							}
						return /* Filters already applied with: ``ws_plugin__s2member_paypal_api_response``. */ $response;
					}
				/**
				* Cleans up values passed through PayPal NVP strings.
				*
				* @package s2Member\PayPal
				* @since 121202
				*
				* @param string $key Expects a string value.
				* @param string $value Expects a string value.
				* @return string Cleaned string value.
				*/
				public static function paypal_api_nv_cleanup($key = FALSE, $value = FALSE)
					{
						$value = (string)$value;
						$value = preg_replace('/"/', "'", $value);

						if(($key === "DESC" || $key === "BA_DESC" #
						|| preg_match("/^L_NAME[0-9]+$/", $key) || preg_match("/^PAYMENTREQUEST_[0-9]+_DESC$/", $key) || preg_match("/^PAYMENTREQUEST_[0-9]+_NAME[0-9]+$/", $key) #
						|| preg_match("/^L_BILLINGAGREEMENTDESCRIPTION[0-9]+$/", $key)) && strlen($value) > 60)
							$value = substr($value, 0, 57)."...";

						return apply_filters("ws_plugin__s2member_paypal_api_nv_cleanup", $value, get_defined_vars());
					}
				/**
				* Calls upon the PayPal PayFlow API, and returns the response.
				*
				* @package s2Member\PayPal
				* @since 120514
				*
				* @param array $post_vars An array of variables to send through the PayPal PayFlow API call.
				* @return array An array of variables returned by the PayPal PayFlow API.
				*/
				public static function paypal_payflow_api_response($post_vars = FALSE)
					{
						global /* For Multisite support. */ $current_site, $current_blog;

						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_before_paypal_payflow_api_response", get_defined_vars());
						unset($__refs, $__v);

						$url = "https://".(($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_sandbox"]) ? "pilot-payflowpro.paypal.com" : "payflowpro.paypal.com");

						$post_vars = apply_filters("ws_plugin__s2member_paypal_payflow_api_post_vars", $post_vars, get_defined_vars());
						$post_vars = (is_array($post_vars)) ? $post_vars : array();

						$post_vars["VERBOSITY"] = "HIGH";
						$post_vars["USER"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_payflow_api_username"];
						$post_vars["PARTNER"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_payflow_api_partner"];
						$post_vars["VENDOR"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_payflow_api_vendor"];
						$post_vars["PWD"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_payflow_api_password"];

						foreach($post_vars as $_key => &$_value /* We need to clean these up. */)
							$_value = c_ws_plugin__s2member_paypal_utilities::paypal_payflow_api_nv_cleanup($_key, $_value);
						unset($_key, $_value);

						$input_time = /* Record input/nvp for logging. */ date("D M j, Y g:i:s a T");

						$nvp_post_vars = /* Initialize this to an empty string. */ "";
						foreach($post_vars as $_key => $_value /* A ridiculous `text/namevalue` format. */)
							$nvp_post_vars .= (($nvp_post_vars) ? "&" : "").$_key."[".strlen($_value)."]=".$_value;
						unset($_key, $_value);

						$nvp = trim(c_ws_plugin__s2member_utils_urls::remote($url, $nvp_post_vars, array("timeout" => 20, "headers" => array("Content-Type" => "text/namevalue"))));

						$output_time = /* Now record after output time. */ date("D M j, Y g:i:s a T");

						wp_parse_str /* Parse NVP response. */($nvp, $response);
						$response = c_ws_plugin__s2member_utils_strings::trim_deep($response);

						if($response["RESULT"] !== "0")
							{
								if(strlen($response["RESPMSG"]))
									/* translators: Exclude `%2$s`. These are English details returned by PayPal. Replace `%2$s` with: `Unable to process, please try again`, or something to that affect. Or, if you prefer, you could Filter ``$response["__error"]`` with `ws_plugin__s2member_paypal_payflow_api_response`. */
									$response["__error"] = sprintf(_x('Error #%1$s. %2$s.', "s2member-front", "s2member"), $response["RESULT"], rtrim($response["RESPMSG"], "."));

								else $response["__error"] = _x("Error. Please contact Support for assistance.", "s2member-front", "s2member");
							}
						else if(isset($response["TRXRESULT"]) && $response["TRXRESULT"] !== "0")
							{
								if(strlen($response["TRXRESPMSG"]))
									/* translators: Exclude `%2$s`. These are English details returned by PayPal. Replace `%2$s` with: `Unable to process, please try again`, or something to that affect. Or, if you prefer, you could Filter ``$response["__error"]`` with `ws_plugin__s2member_paypal_payflow_api_response`. */
									$response["__error"] = sprintf(_x('Error #%1$s. %2$s.', "s2member-front", "s2member"), $response["TRXRESULT"], rtrim($response["TRXRESPMSG"], "."));

								else $response["__error"] = _x("Error. Please contact Support for assistance.", "s2member-front", "s2member");
							}

						$logt = c_ws_plugin__s2member_utilities::time_details ();
						$logv = c_ws_plugin__s2member_utilities::ver_details();
						$logm = c_ws_plugin__s2member_utilities::mem_details();
						$log4 = $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]."\nUser-Agent: ".@$_SERVER["HTTP_USER_AGENT"];
						$log4 = (is_multisite() && !is_main_site()) ? ($_log4 = $current_blog->domain.$current_blog->path)."\n".$log4 : $log4;
						$log2 = (is_multisite() && !is_main_site()) ? "paypal-payflow-api-4-".trim(preg_replace("/[^a-z0-9]/i", "-", $_log4), "-").".log" : "paypal-payflow-api.log";

						if(isset($post_vars["ACCT"]) && strlen($post_vars["ACCT"]) > 4)
							$post_vars["ACCT"] = str_repeat("*", strlen($post_vars["ACCT"]) - 4).substr($post_vars["ACCT"], -4);

						if($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["gateway_debug_logs"])
							if(is_dir($logs_dir = $GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["logs_dir"]))
								if(is_writable($logs_dir) && c_ws_plugin__s2member_utils_logs::archive_oversize_log_files())
									if(($log = "-------- Input vars: ( ".$input_time." ) --------\n".$nvp_post_vars."\n".var_export($post_vars, true)."\n"))
										if(($log .= "-------- Output string/vars: ( ".$output_time." ) --------\n".$nvp."\n".var_export($response, true)))
											file_put_contents($logs_dir."/".$log2,
											                  "LOG ENTRY: ".$logt . "\n" . $logv."\n".$logm."\n".$log4."\n".
											                                       c_ws_plugin__s2member_utils_logs::conceal_private_info($log)."\n\n",
											                  FILE_APPEND);

						return apply_filters("ws_plugin__s2member_paypal_payflow_api_response", c_ws_plugin__s2member_paypal_utilities::_paypal_payflow_api_response_filters($response), get_defined_vars());
					}
				/**
				* A sort of callback function that Filters Payflow responses.
				*
				* Provides alternative explanations in some cases that require special attention.
				*
				* @package s2Member\PayPal
				* @since 120514
				*
				* @param array $response Expects an array of response variables returned by the Payflow API.
				* @return array An array of variables returned by the Payflow API, after ``$response["__error"]`` is Filtered.
				*/
				public static function _paypal_payflow_api_response_filters($response = FALSE)
					{
						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("_ws_plugin__s2member_before_paypal_payflow_api_response_filters", get_defined_vars());
						unset($__refs, $__v);

						if(!empty($response["__error"]) && !empty($response["HOSTCODE"]))
							{
								if((int)$response["HOSTCODE"] === 11452)
									$response["__error"] .= _x(" Please contact PayPal Merchant Technical Support (www.paypal.com/mts) and request `Recurring Billing` service, and also ask to have `Reference Transactions` enabled for Recurring Billing via Express Checkout.", "s2member-front", "s2member");
							}

						return /* Filters already applied with: ``ws_plugin__s2member_paypal_payflow_api_response``. */ $response;
					}
				/**
				* Cleans up values passed through PayPal text/namevalue strings.
				*
				* @package s2Member\PayPal
				* @since 121202
				*
				* @param string $key Expects a string value.
				* @param string $value Expects a string value.
				* @return string Cleaned string value.
				*/
				public static function paypal_payflow_api_nv_cleanup($key = FALSE, $value = FALSE)
					{
						$value = (string)$value;
						$value = preg_replace('/"/', "'", $value);

						if(($key === "DESC" || $key === "ORDERDESC" || $key === "BA_DESC" || $key === "BA_CUSTOM" #
						|| preg_match("/^L_NAME[0-9]+$/", $key) || preg_match("/^PAYMENTREQUEST_[0-9]+_DESC$/", $key) || preg_match("/^PAYMENTREQUEST_[0-9]+_NAME[0-9]+$/", $key) #
						|| preg_match("/^L_BILLINGAGREEMENTDESCRIPTION[0-9]+$/", $key)) && strlen($value) > 60)
							$value = substr($value, 0, 57)."...";

						return apply_filters("ws_plugin__s2member_paypal_payflow_api_nv_cleanup", $value, get_defined_vars());
					}
				/**
				* Converts a term `D|W|M|Y` into PayPal Pro format.
				*
				* @package s2Member\PayPal
				* @since 3.5
				*
				* @param string $term Expects one of `D|W|M|Y`.
				* @return bool|str A full singular description of the term *( i.e., `Day|Week|Month|Year` )*, else false.
				*/
				public static function paypal_pro_term($term = FALSE)
					{
						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_before_paypal_pro_term", get_defined_vars());
						unset($__refs, $__v);

						$paypal_pro_terms = array("D" => "Day", "W" => "Week", "M" => "Month", "Y" => "Year");

						$pro_term = (!empty($paypal_pro_terms[strtoupper($term)])) ? $paypal_pro_terms[strtoupper($term)] : false;

						return apply_filters("ws_plugin__s2member_paypal_pro_term", $pro_term, get_defined_vars());
					}
				/**
				* Converts a term `D|W|M|Y` into Payflow format.
				*
				* @package s2Member\PayPal
				* @since 120514
				*
				* @param string $term Expects one of `D|W|M|Y`.
				* @param string $period Expects a numeric value.
				* @return bool|str A full singular description of the term *( i.e., `DAY|WEEK|BIWK|MONT|QTER|SMYR|YEAR` )*, else false.
				*
				* @note Payflow unfortunately does NOT support daily and/or bi-monthly billing.
				*/
				public static function paypal_payflow_term($term = FALSE, $period = FALSE)
					{
						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_before_paypal_payflow_term", get_defined_vars());
						unset($__refs, $__v);

						$paypal_payflow_terms = array("D" => "DAY", "W" => "WEEK", "M" => "MONT", "Y" => "YEAR");

						$payflow_term = (!empty($paypal_payflow_terms[strtoupper($term)])) ? $paypal_payflow_terms[strtoupper($term)] : false;

						if($payflow_term === "WEEK" && $period === "2")
							$payflow_term = "BIWK";

						else if($payflow_term === "MONT" && $period === "3")
							$payflow_term = "QTER";

						else if($payflow_term === "MONT" && $period === "6")
							$payflow_term = "SMYR";

						return apply_filters("ws_plugin__s2member_paypal_payflow_term", $payflow_term, get_defined_vars());
					}
				/**
				* Converts a term `Day|Week|Month|Year` into PayPal Standard format.
				*
				* @package s2Member\PayPal
				* @since 3.5
				*
				* @param string $term Expects one of `Day|Week|Month|Year`.
				* @return bool|str A term code *( i.e., `D|W|M|Y` )*, else false.
				*/
				public static function paypal_std_term($term = FALSE)
					{
						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_before_paypal_std_term", get_defined_vars());
						unset($__refs, $__v);

						$paypal_std_terms = array("DAY" => "D", "WEEK" => "W", "MONTH" => "M", "YEAR" => "Y");

						$std_term = (!empty($paypal_std_terms[strtoupper($term)])) ? $paypal_std_terms[strtoupper($term)] : false;

						return apply_filters("ws_plugin__s2member_paypal_std_term", $std_term, get_defined_vars());
					}
				/**
				* Get `subscr_id` from either an array with `recurring_payment_id|subscr_id`, or use an existing string.
				*
				* @package s2Member\PayPal
				* @since 3.5
				*
				* @param string|array $array_or_string Either an array of PayPal post vars, or a string.
				* @return str|bool A `subscr_id` string if non-empty, else false.
				*/
				public static function paypal_pro_subscr_id($array_or_string = FALSE)
					{
						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_before_paypal_pro_subscr_id", get_defined_vars());
						unset($__refs, $__v);

						if(is_array($array = $array_or_string) && !empty($array["subscr_id"]))
							$subscr_id = trim($array["subscr_id"]);

						else if(is_array($array = $array_or_string) && !empty($array["recurring_payment_id"]))
							$subscr_id = trim($array["recurring_payment_id"]);

						else if(is_array($array = $array_or_string) && !empty($array["mp_id"])
							&& ($ipn_signup_var_subscr_id = c_ws_plugin__s2member_utils_users::get_user_ipn_signup_var("subscr_id", FALSE, $array["mp_id"])))
							$subscr_id = trim($ipn_signup_var_subscr_id); // Found w/ a Billing Agreement ID.

						else if(is_string($string = $array_or_string) && !empty($string)) $subscr_id = trim($string);

						return apply_filters("ws_plugin__s2member_paypal_pro_subscr_id", ((!empty($subscr_id)) ? $subscr_id : false), get_defined_vars());
					}
				/**
				* Get `item_number` from either an array with `PROFILEREFERENCE|rp_invoice_id|item_number1|item_number`, or use an existing string.
				*
				* @package s2Member\PayPal
				* @since 3.5
				*
				* @param string|array $array_or_string Either an array of PayPal post vars, or a string.
				* 	If it's a string, we make sure it is a valid `level:ccaps:eotper` or `sp:ids:expiration` combination.
				* @return str|bool An `item_number` string if non-empty, else false.
				*/
				public static function paypal_pro_item_number($array_or_string = FALSE)
					{
						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_before_paypal_pro_item_number", get_defined_vars());
						unset($__refs, $__v);

						if(is_array($array_or_string) && isset($array_or_string["PROFILENAME"]) /* Payflow. */)
							$array_or_string["PROFILEREFERENCE"] = $array_or_string["PROFILENAME"];

						if(is_array($array = $array_or_string) && !empty($array["item_number"]))
							$_item_number = trim($array["item_number"]);

						else if(is_array($array = $array_or_string) && !empty($array["item_number1"]))
							$_item_number = trim($array["item_number1"]);

						else if(is_array($array = $array_or_string) && (!empty($array["PROFILEREFERENCE"]) || !empty($array["rp_invoice_id"])))
							list($_reference, $_domain, $_item_number) = array_map("trim", preg_split("/~/", ((!empty($array["PROFILEREFERENCE"])) ? $array["PROFILEREFERENCE"] : $array["rp_invoice_id"]), 3));

						else if(is_array($array = $array_or_string) && !empty($array["mp_id"])
							&& ($ipn_signup_var_item_number = c_ws_plugin__s2member_utils_users::get_user_ipn_signup_var("item_number", FALSE, $array["mp_id"])))
							$_item_number = trim($ipn_signup_var_item_number); // Found w/ a Billing Agreement ID.

						//260213 Backfill from stored IPN Signup Vars using recurring_payment_id/subscr_id (PayPal may omit item_number on cancellations).
						else if(is_array($array = $array_or_string) && (!empty($array["recurring_payment_id"]) || !empty($array["subscr_id"]))
							&& ($ipn_signup_var_item_number = c_ws_plugin__s2member_utils_users::get_user_ipn_signup_var("item_number", FALSE, ((!empty($array["recurring_payment_id"])) ? $array["recurring_payment_id"] : $array["subscr_id"]))))
							$_item_number = trim($ipn_signup_var_item_number); // Found w/ a Subscription ID.

						else if(is_string($string = $array_or_string) && !empty($string)) $_item_number = trim($string);

						if(!empty($_item_number) && preg_match($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["membership_item_number_w_or_wo_level_regex"], $_item_number))
							$item_number = $_item_number;

						else if(!empty($_item_number) && preg_match($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["sp_access_item_number_regex"], $_item_number))
							$item_number = $_item_number;

						return apply_filters("ws_plugin__s2member_paypal_pro_item_number", ((!empty($item_number)) ? $item_number : false), get_defined_vars());
					}
				/**
				* Get `item_name` from either an array with `product_name|item_name1|item_name`, or use an existing string.
				*
				* @package s2Member\PayPal
				* @since 3.5
				*
				* @param string|array $array_or_string Either an array of PayPal post vars, or a string.
				* @return str|bool An `item_name` string if non-empty, else false.
				*/
				public static function paypal_pro_item_name($array_or_string = FALSE)
					{
						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_before_paypal_pro_item_name", get_defined_vars());
						unset($__refs, $__v);

						if(is_array($array = $array_or_string) && !empty($array["item_name"]))
							$item_name = trim($array["item_name"]);

						else if(is_array($array = $array_or_string) && !empty($array["item_name1"]))
							$item_name = trim($array["item_name1"]);

						else if(is_array($array = $array_or_string) && !empty($array["product_name"]))
							$item_name = trim($array["product_name"]);

						else if(is_array($array = $array_or_string) && !empty($array["mp_id"])
							&& ($ipn_signup_var_item_name = c_ws_plugin__s2member_utils_users::get_user_ipn_signup_var("item_name", FALSE, $array["mp_id"])))
							$item_name = trim($ipn_signup_var_item_name); // Found w/ a Billing Agreement ID.

						//260213 Backfill from stored IPN Signup Vars using recurring_payment_id/subscr_id (PayPal may omit item_name on cancellations).
						else if(is_array($array = $array_or_string) && (!empty($array["recurring_payment_id"]) || !empty($array["subscr_id"]))
							&& ($ipn_signup_var_item_name = c_ws_plugin__s2member_utils_users::get_user_ipn_signup_var("item_name", FALSE, ((!empty($array["recurring_payment_id"])) ? $array["recurring_payment_id"] : $array["subscr_id"]))))
							$item_name = trim($ipn_signup_var_item_name); // Found w/ a Subscription ID.

						else if(is_string($string = $array_or_string) && !empty($string)) $item_name = trim($string);

						return apply_filters("ws_plugin__s2member_paypal_pro_item_name", ((!empty($item_name)) ? $item_name : false), get_defined_vars());
					}
				/**
				* Get `period1` from either an array with `PROFILEREFERENCE|rp_invoice_id|period1`, or use an existing string.
				*
				* This will also convert `1 Day`, into `1 D`, and so on.
				* This will also convert `1 SemiMonth`, into `2 W`, and so on.
				*
				* @package s2Member\PayPal
				* @since 3.5
				*
				* @param string|array $array_or_string Either an array of PayPal post vars, or a string.
				* 	If it's a string, we make sure it is a valid `period term` combination.
				* @param string $default Optional. Value if unavailable. Defaults to `0 D`.
				* @return string A `period1` string if possible, or defaults to `0 D`.
				*/
				public static function paypal_pro_period1($array_or_string = FALSE, $default = "0 D")
					{
						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_before_paypal_pro_period1", get_defined_vars());
						unset($__refs, $__v);

						if(is_array($array_or_string) && isset($array_or_string["PROFILENAME"]) /* Payflow. */)
							$array_or_string["PROFILEREFERENCE"] = $array_or_string["PROFILENAME"];

						if(is_array($array = $array_or_string) && !empty($array["period1"])) $_period1 = trim($array["period1"]);

						else if(is_array($array = $array_or_string) && (!empty($array["PROFILEREFERENCE"]) || !empty($array["rp_invoice_id"])))
							{
								list($_reference, $_domain, $_item_number) = array_map("trim", preg_split("/~/", ((!empty($array["PROFILEREFERENCE"])) ? $array["PROFILEREFERENCE"] : $array["rp_invoice_id"]), 3));
								list($_start_time, $_period1, $_period3) = array_map("trim", preg_split("/\:/", $_reference, 3));
							}
						else if(is_array($array = $array_or_string) && !empty($array["mp_id"])
							&& ($ipn_signup_var_period1 = c_ws_plugin__s2member_utils_users::get_user_ipn_signup_var("period1", FALSE, $array["mp_id"])))
							$_period1 = trim($ipn_signup_var_period1); // Found w/ a Billing Agreement ID.

						//260213 Backfill from stored IPN Signup Vars using recurring_payment_id/subscr_id (PayPal may omit period1 on cancellations).
						else if(is_array($array = $array_or_string) && (!empty($array["recurring_payment_id"]) || !empty($array["subscr_id"]))
							&& ($ipn_signup_var_period1 = c_ws_plugin__s2member_utils_users::get_user_ipn_signup_var("period1", FALSE, ((!empty($array["recurring_payment_id"])) ? $array["recurring_payment_id"] : $array["subscr_id"]))))
							$_period1 = trim($ipn_signup_var_period1); // Found w/ a Subscription ID.

						else if(is_string($string = $array_or_string) && !empty($string)) $_period1 = trim($string);

						if /* Were we able to get a `period1` string? */(!empty($_period1))
							{
								list($num, $span) = array_map("trim", preg_split("/ /", $_period1, 2));

								if(strtoupper($span) === "SEMIMONTH" && is_numeric($num) && $num >= 1)
									{ $num = "2"; $span = "W"; }

								if /* To Standard format. */(strlen($span) !== 1)
									$span = c_ws_plugin__s2member_paypal_utilities::paypal_std_term($span);

								$span = (preg_match("/^[DWMY]$/i", $span)) ? $span : "";
								$num = ($span && is_numeric($num) && $num >= 0) ? $num : "";

								$period1 = ($num && $span) ? $num." ".strtoupper($span) : $default;

								return apply_filters("ws_plugin__s2member_paypal_pro_period1", $period1, get_defined_vars());
							}
						else return apply_filters("ws_plugin__s2member_paypal_pro_period1", $default, get_defined_vars());
					}
				/**
				* Get `period3` from either an array with `PROFILEREFERENCE|rp_invoice_id|period3`, or use an existing string.
				*
				* This will also convert `1 Day`, into `1 D`, and so on.
				* This will also convert `1 SemiMonth`, into `2 W`, and so on.
				* The Regular Period can never be less than 1 day ( `1 D` ).
				*
				* @package s2Member\PayPal
				* @since 3.5
				*
				* @param string|array $array_or_string Either an array of PayPal post vars, or a string.
				* 	If it's a string, we make sure it is a valid `period term` combination.
				* @param string $default Optional. Value if unavailable. Defaults to `1 D`.
				* @return string A `period3` string if possible, or defaults to `1 D`.
				*/
				public static function paypal_pro_period3($array_or_string = FALSE, $default = "1 D")
					{
						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_before_paypal_pro_period3", get_defined_vars());
						unset($__refs, $__v);

						if(is_array($array_or_string) && isset($array_or_string["PROFILENAME"]) /* Payflow. */)
							$array_or_string["PROFILEREFERENCE"] = $array_or_string["PROFILENAME"];

						if(is_array($array = $array_or_string) && !empty($array["period3"])) $_period3 = trim($array["period3"]);

						else if(is_array($array = $array_or_string) && (!empty($array["PROFILEREFERENCE"]) || !empty($array["rp_invoice_id"])))
							{
								list($_reference, $_domain, $_item_number) = array_map("trim", preg_split("/~/", ((!empty($array["PROFILEREFERENCE"])) ? $array["PROFILEREFERENCE"] : $array["rp_invoice_id"]), 3));
								list($_start_time, $_period1, $_period3) = array_map("trim", preg_split("/\:/", $_reference, 3));
							}
						else if(is_array($array = $array_or_string) && !empty($array["mp_id"])
							&& ($ipn_signup_var_period3 = c_ws_plugin__s2member_utils_users::get_user_ipn_signup_var("period3", FALSE, $array["mp_id"])))
							$_period3 = trim($ipn_signup_var_period3); // Found w/ a Billing Agreement ID.

						//260213 Backfill from stored IPN Signup Vars using recurring_payment_id/subscr_id (PayPal may omit period3 on cancellations).
						else if(is_array($array = $array_or_string) && (!empty($array["recurring_payment_id"]) || !empty($array["subscr_id"]))
							&& ($ipn_signup_var_period3 = c_ws_plugin__s2member_utils_users::get_user_ipn_signup_var("period3", FALSE, ((!empty($array["recurring_payment_id"])) ? $array["recurring_payment_id"] : $array["subscr_id"]))))
							$_period3 = trim($ipn_signup_var_period3); // Found w/ a Subscription ID.

						else if(is_string($string = $array_or_string) && !empty($string)) $_period3 = trim($string);

						if /* Were we able to get a `period3` string? */(!empty($_period3))
							{
								list($num, $span) = array_map("trim", preg_split("/ /", $_period3, 2));

								if(strtoupper($span) === "SEMIMONTH" && is_numeric($num) && $num >= 1)
									{ $num = "2"; $span = "W"; }

								if /* To Standard format. */(strlen($span) !== 1)
									$span = c_ws_plugin__s2member_paypal_utilities::paypal_std_term($span);

								$span = (preg_match("/^[DWMY]$/i", $span)) ? $span : "";
								$num = ($span && is_numeric($num) && $num >= 0) ? $num : "";

								$period3 = ($num && $span) ? $num." ".strtoupper($span) : $default;

								return apply_filters("ws_plugin__s2member_paypal_pro_period3", $period3, get_defined_vars());
							}
						else return apply_filters("ws_plugin__s2member_paypal_pro_period3", $default, get_defined_vars());
					}

				//260106 PayPal Checkout
				/**
				 * Returns true when PayPal Checkout is enabled and required credentials exist.
				 *
				 * @since 260106
				 *
				 * @return bool
				 */
				public static function paypal_checkout_is_enabled()
					{
						if(empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_enable']))
							return false;

						if(self::paypal_checkout_is_sandbox())
							return (!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_sandbox_client_id'])
								&& !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_sandbox_client_secret']));

						return (!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_client_id'])
							&& !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_client_secret']));
					}

				/**
				 * Returns true when PayPal Checkout webhook processing can operate.
				 *
				 * This is intentionally decoupled from `paypal_checkout_enable` so that:
				 * - sites can switch new sales back to PayPal Standard
				 * - while still processing webhooks for existing Checkout subscriptions
				 *
				 * @since 260218
				 *
				 * @return bool
				 */
				public static function paypal_checkout_webhook_processing_is_enabled()
					{
						// Full Checkout enabled? Then yes.
						if(self::paypal_checkout_is_enabled())
							return true;

						// Otherwise: allow webhook processing when creds + webhook id exist (either env).
						$live_ready = (!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_client_id'])
							&& !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_client_secret'])
							&& !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_webhook_id']));

						$sandbox_ready = (!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_sandbox_client_id'])
							&& !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_sandbox_client_secret'])
							&& !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_sandbox_webhook_id']));

						return ($live_ready || $sandbox_ready);
					}

				/**
				 * Returns true when PayPal Checkout is in sandbox mode.
				 *
				 * @since 260101
				 *
				 * @return bool
				 */
				public static function paypal_checkout_is_sandbox()
					{
						return !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_sandbox']);
					}

				/**
				 * Returns the PayPal REST API base URL for the active environment.
				 *
				 * @since 260101
				 *
				 * @return string
				 */
				public static function paypal_checkout_api_base()
					{
						return (self::paypal_checkout_is_sandbox())
							? 'https://api-m.sandbox.paypal.com'
							: 'https://api-m.paypal.com';
					}

				/**
				 * Returns PayPal Checkout REST credentials for the active environment.
				 *
				 * @since 260101
				 *
				 * @return array{client_id:string,secret:string}
				 */
				public static function paypal_checkout_creds()
					{
						if(self::paypal_checkout_is_sandbox())
							return array(
								'client_id' => (string)$GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_sandbox_client_id'],
								'secret'    => (string)$GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_sandbox_client_secret'],
							);

						return array(
							'client_id' => (string)$GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_client_id'],
							'secret'    => (string)$GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_client_secret'],
						);
					}

				/**
				 * Returns a stable short id derived from the PayPal Client ID (per env).
				 *
				 * Used to bucket caches in:
				 * - $options['paypal_checkout_cache'][$cred_id][...]
				 *
				 * @since 260127
				 *
				 * @param string $env 'live' or 'sandbox'. Defaults to 'live'.
				 *
				 * @return string 12-char hash prefix or empty string.
				 */
				public static function paypal_checkout_cred_id($env = '')
					{
						$env = ($env === 'sandbox') ? 'sandbox' : 'live';

						$client_id = ($env === 'sandbox')
							? (string)$GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_sandbox_client_id']
							: (string)$GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_client_id'];

						$client_id = trim($client_id);
						if(!$client_id)
							return '';

						return substr(md5(strtolower($client_id)), 0, 12);
					}

				/**
				 * Returns a cached PayPal REST access token (fetches a new one when needed).
				 *
				 * Stored in a transient keyed by environment.
				 *
				 * @since 260101
				 *
				 * @return string Access token or empty string on failure.
				 */
				public static function paypal_checkout_access_token()
					{
						$transient = self::paypal_checkout_is_sandbox() ? 's2m_ppco_at_sandbox' : 's2m_ppco_at_live';

						if(($cached = get_transient($transient)) && is_array($cached) && !empty($cached['access_token']))
							return $cached['access_token'];

						$creds            = self::paypal_checkout_creds();
						$client_id        = (string)$creds['client_id'];
						$secret           = (string)$creds['secret'];
						$client_len_hash  = strlen($client_id).'_'.substr(hash('sha256', $client_id), 0, 16);
						$secret_len_hash  = strlen($secret).'_'.substr(hash('sha256', $secret), 0, 16);

						if(!$client_id || !$secret)
							return '';

						$url  = self::paypal_checkout_api_base().'/v1/oauth2/token';
						$body = 'grant_type=client_credentials';

						$args = array(
							'timeout' => 20,
							'headers' => array(
								'Authorization'   => 'Basic '.base64_encode($client_id.':'.$secret),
								'Content-Type'    => 'application/x-www-form-urlencoded',
								'Accept'          => 'application/json',
								'Accept-Language' => 'en_US',
							),
						);

						$r = c_ws_plugin__s2member_utils_urls::remote($url, $body, $args, true);

						if(!is_array($r))
							$r = array('code' => 0, 'message' => 'request_failed', 'headers' => array(), 'body' => '');

						if(!isset($r['code']) || (int)$r['code'] !== 200)
							c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
								'ppco'            => 'oauth',
								'event'           => 'token_failed',
								'env_setting'     => self::paypal_checkout_is_sandbox() ? 'sandbox' : 'live',
								'client_len_hash' => $client_len_hash,
								'secret_len_hash' => $secret_len_hash,
								'url'             => $url,
								'code'            => !empty($r['code']) ? (int)$r['code'] : 0,
								'message'         => !empty($r['message']) ? (string)$r['message'] : '',
								'body'            => !empty($r['body']) ? $r['body'] : '',
							));

						$data = array();
						if(!empty($r['body']) && is_string($r['body']))
							$data = json_decode($r['body'], true);

						if(!empty($data['access_token']) && !empty($data['expires_in']))
						{
							$ttl = max(60, (int)$data['expires_in'] - 60);
							set_transient($transient, array('access_token' => $data['access_token']), $ttl);

							return $data['access_token'];
						}
						return '';
					}

				/**
				 * Tests PayPal Checkout REST credentials for the selected environment.
				 *
				 * Forces a real access token request (clears cached token transient first).
				 * Intended for admin UI diagnostics during beta/QA.
				 *
				 * @since 260115
				 *
				 * @param string $env 'live' or 'sandbox'. Defaults to 'live'.
				 *
				 * @return bool True if an access token was obtained; otherwise false.
				 */
				public static function paypal_checkout_creds_test($env = '')
					{
						$env = ($env === 'sandbox') ? 'sandbox' : 'live';

						$orig_sandbox = self::paypal_checkout_is_sandbox();
						$GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_sandbox'] = ($env === 'sandbox') ? '1' : '0';

						// Force a real token request (ignore cached transient).
						$transient = self::paypal_checkout_is_sandbox() ? 's2m_ppco_at_sandbox' : 's2m_ppco_at_live';
						delete_transient($transient);

						$token = self::paypal_checkout_access_token();
						$ok    = ($token) ? true : false;

						$creds            = self::paypal_checkout_creds();
						$client_len_hash  = strlen((string)$creds['client_id']).'_'.substr(hash('sha256', (string)$creds['client_id']), 0, 16);
						$secret_len_hash  = strlen((string)$creds['secret']).'_'.substr(hash('sha256', (string)$creds['secret']), 0, 16);

						c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
							'ppco'            => 'checkout',
							'event'           => $ok ? 'creds_test_ok' : 'creds_test_failed',
							'env_setting'     => $env,
							'client_len_hash' => $client_len_hash,
							'secret_len_hash' => $secret_len_hash,
						));

						$GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_sandbox'] = $orig_sandbox ? '1' : '0';
						return $ok;
					}

				/**
				 * Clears PayPal Checkout plan/product caches (per environment) and the cached access token.
				 *
				 * Cache storage:
				 * - $options['paypal_checkout_cache'][$cred_id][$env]['plan_ids']
				 * - $options['paypal_checkout_cache'][$cred_id][$env]['product_ids']
				 *
				 * Intended for QA and for situations where a cached plan/product id becomes stale
				 * due to changes in billing attributes.
				 *
				 * @since 260127
				 *
				 * @param string $env 'live' or 'sandbox'. Defaults to 'live'.
				 *
				 * @return bool
				 */
				public static function paypal_checkout_clear_cache($env = '')
					{
						$env = ($env === 'sandbox') ? 'sandbox' : 'live';

						$orig_sandbox = self::paypal_checkout_is_sandbox();
						$GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_sandbox'] = ($env === 'sandbox') ? '1' : '0';

						$cred_id = self::paypal_checkout_cred_id($env);

						$options = get_option('ws_plugin__s2member_options');
						if(!is_array($options))
							$options = array();

						// New cache format: $options['paypal_checkout_cache'][$cred_id][$env]['plan_ids'|'product_ids'].
						if($cred_id && !empty($options['paypal_checkout_cache']) && is_array($options['paypal_checkout_cache'])
							&& !empty($options['paypal_checkout_cache'][$cred_id]) && is_array($options['paypal_checkout_cache'][$cred_id])
							&& !empty($options['paypal_checkout_cache'][$cred_id][$env]) && is_array($options['paypal_checkout_cache'][$cred_id][$env]))
						{
							if(isset($options['paypal_checkout_cache'][$cred_id][$env]['plan_ids']))
								unset($options['paypal_checkout_cache'][$cred_id][$env]['plan_ids']);

							if(isset($options['paypal_checkout_cache'][$cred_id][$env]['product_ids']))
								unset($options['paypal_checkout_cache'][$cred_id][$env]['product_ids']);

							if(empty($options['paypal_checkout_cache'][$cred_id][$env]))
								unset($options['paypal_checkout_cache'][$cred_id][$env]);

							if(empty($options['paypal_checkout_cache'][$cred_id]))
								unset($options['paypal_checkout_cache'][$cred_id]);
						}

						// Delete legacy cache keys (no migration; just remove).
						if(isset($options['paypal_checkout_plan_ids']))
							unset($options['paypal_checkout_plan_ids']);

						if(isset($options['paypal_checkout_product_ids']))
							unset($options['paypal_checkout_product_ids']);

						$options = ws_plugin__s2member_configure_options_and_their_defaults($options);

						update_option('ws_plugin__s2member_options', $options).((is_multisite() && is_main_site()) ? update_site_option('ws_plugin__s2member_options', $options) : NULL);

						$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]['paypal_checkout_cache'] = (!empty($options['paypal_checkout_cache']) && is_array($options['paypal_checkout_cache'])) ? $options['paypal_checkout_cache'] : array();

						// Clear cached access token for this env too.
						$transient = self::paypal_checkout_is_sandbox() ? 's2m_ppco_at_sandbox' : 's2m_ppco_at_live';
						delete_transient($transient);

						c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
							'ppco'     => 'checkout',
							'event'    => 'cleared_cache',
							'env_setting' => $env,
							'cred_id'  => $cred_id,
						));

						$GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_sandbox'] = $orig_sandbox ? '1' : '0';
						return true;
					}

				/**
				 * Performs a PayPal REST API request using the current environment access token.
				 *
				 * @since 260101
				 *
				 * @param string $method  HTTP method.
				 * @param string $path    API path beginning with '/'.
				 * @param mixed  $body    Array/object body or raw string; null for no body.
				 * @param array  $headers Additional headers.
				 *
				 * @return array Response array from c_ws_plugin__s2member_utils_urls::remote().
				 */
				public static function paypal_checkout_api_request($method = 'GET', $path = '/', $body = null, $headers = array())
					{
						$method = strtoupper((string)$method);
						$url    = self::paypal_checkout_api_base().$path;

						$args = array(
							'timeout' => 20,
							'method'  => $method,
							'headers' => array_merge(array(
								'Authorization' => 'Bearer '.self::paypal_checkout_access_token(),
								'Content-Type'  => 'application/json',
								'Accept'        => 'application/json',
							), (array)$headers),
						);

						if($body !== null)
						{
							$encoded = is_string($body) ? $body : wp_json_encode($body);
							$args['body'] = ($encoded !== false) ? $encoded : '{}';
						}

						$r = c_ws_plugin__s2member_utils_urls::remote($url, false, $args, true);

						if(!is_array($r))
							$r = array('code' => 0, 'message' => 'request_failed', 'headers' => array(), 'body' => '');

						c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
								'ppco'     => 'api_request',
								'env_setting' => self::paypal_checkout_is_sandbox() ? 'sandbox' : 'live',
								'method'   => $method,
								'path'     => $path,
								'code'     => !empty($r['code']) ? (int)$r['code'] : 0,
								'message'  => !empty($r['message']) ? (string)$r['message'] : '',
								'body'     => !empty($r['body']) ? $r['body'] : '',
							));

						return $r;
					}

				/**
				 * Retrieves a PayPal Checkout order for validation or capture recovery.
				 *
				 * @since 260817
				 *
				 * @param string $order_id PayPal Checkout order id.
				 *
				 * @return array Decoded order response, with __code/__body added; __error on failure.
				 */
				public static function paypal_checkout_order_details($order_id = '')
					{
						$order_id = trim((string)$order_id);

						if(!$order_id)
							return array('__error' => 'missing_order_id', '__code' => 0, '__body' => '');

						$r = self::paypal_checkout_api_request('GET', '/v2/checkout/orders/'.rawurlencode($order_id));

						$code = !empty($r['code']) ? (int)$r['code'] : 0;
						$body = !empty($r['body']) ? (string)$r['body'] : '';
						$data = ($body) ? json_decode($body, true) : array();
						$data = is_array($data) ? $data : array();

						$data['__code'] = $code;
						$data['__body'] = $body;

						if(!($code >= 200 && $code <= 299) || empty($data['id']))
							$data['__error'] = 'order_details_failed';

						return $data;
					}
				/**
				 * Validates a PayPal Checkout order against the server-side purchase token.
				 *
				 * @since 260817
				 *
				 * @param array  $order    PayPal order representation.
				 * @param string $order_id Expected PayPal order id.
				 * @param array  $token    Signed/validated purchase token.
				 *
				 * @return string Empty string if valid; otherwise a stable error code.
				 */
				public static function paypal_checkout_order_validation_error($order = array(), $order_id = '', $token = array())
					{
						if(!is_array($order) || empty($order['id']))
							return 'order_missing';
						if($order_id && (string)$order['id'] !== (string)$order_id)
							return 'order_id_mismatch';
						if(empty($order['intent']) || strtoupper((string)$order['intent']) !== 'CAPTURE')
							return 'order_intent_mismatch';
						if(empty($order['purchase_units'][0]) || !is_array($order['purchase_units'][0]))
							return 'order_purchase_unit_missing';

						$pu = $order['purchase_units'][0];
						$invoice = isset($pu['invoice_id']) ? (string)$pu['invoice_id'] : '';
						$amount = isset($pu['amount']['value']) ? (string)$pu['amount']['value'] : '';
						$cc = isset($pu['amount']['currency_code']) ? strtoupper((string)$pu['amount']['currency_code']) : '';

						if(!empty($token['invoice']) && $invoice !== (string)$token['invoice'])
							return 'order_invoice_mismatch';
						if(!empty($token['amount']) && (!$amount || number_format((float)$amount, 2, '.', '') !== number_format((float)$token['amount'], 2, '.', '')))
							return 'order_amount_mismatch';
						if(!empty($token['cc']) && $cc !== strtoupper((string)$token['cc']))
							return 'order_currency_mismatch';

						$custom = !empty($token['custom']) ? (string)$token['custom'] : '';
						if($custom && strlen($custom) <= 127 && (!isset($pu['custom_id']) || (string)$pu['custom_id'] !== $custom))
							return 'order_custom_mismatch';

						return '';
					}
				/**
				 * Validates that a PayPal Checkout order contains a completed capture for the purchase token.
				 *
				 * @since 260817
				 *
				 * @param array  $order    PayPal order representation.
				 * @param string $order_id Expected PayPal order id.
				 * @param array  $token    Signed/validated purchase token.
				 *
				 * @return string Empty string if complete and valid; otherwise a stable error code.
				 */
				public static function paypal_checkout_order_completion_error($order = array(), $order_id = '', $token = array())
					{
						if(($error = self::paypal_checkout_order_validation_error($order, $order_id, $token)))
							return $error;
						if(empty($order['status']) || strtoupper((string)$order['status']) !== 'COMPLETED')
							return 'order_not_completed';

						$capture = (!empty($order['purchase_units'][0]['payments']['captures'][0]) && is_array($order['purchase_units'][0]['payments']['captures'][0])) ? $order['purchase_units'][0]['payments']['captures'][0] : array();
						if(empty($capture['id']) || empty($capture['status']) || strtoupper((string)$capture['status']) !== 'COMPLETED')
							return 'capture_missing_fields';

						$amount = !empty($capture['amount']['value']) ? (string)$capture['amount']['value'] : '';
						$cc = !empty($capture['amount']['currency_code']) ? strtoupper((string)$capture['amount']['currency_code']) : '';

						if(!empty($token['amount']) && (!$amount || number_format((float)$amount, 2, '.', '') !== number_format((float)$token['amount'], 2, '.', '')))
							return 'capture_amount_mismatch';
						if(!empty($token['cc']) && $cc !== strtoupper((string)$token['cc']))
							return 'capture_currency_mismatch';
						if(empty($order['payer']['email_address']))
							return 'capture_missing_fields';

						return '';
					}

				/**
				 * Creates a PayPal Checkout order for one-time (Buy Now) purchases.
				 *
				 * This must be server-side to prevent client-side manipulation of amount, item_number,
				 * custom fields, etc. The resulting order id is returned to the JS SDK or used for
				 * redirect-mode approval.
				 *
				 * @since 260101
				 *
				 * @param array $token Signed/validated purchase token.
				 *
				 * @return array API request result array from paypal_checkout_api_request().
				 */
				public static function paypal_checkout_order_create($token = array())
					{
						// token: invoice, custom, item_name, item_number, amount, cc, ns, return, cancel.
						$invoice = (string)$token['invoice'];
						$custom  = (string)$token['custom'];
						$amount  = (string)$token['amount'];
						$cc      = strtoupper((string)$token['cc']);

						$item_name = trim((string)$token['item_name']);
						if(!$item_name)
							$item_name = 's2Member Purchase';

						// PayPal limits various fields; keep item name within common limits.
						if(strlen($item_name) > 127)
							$item_name = substr($item_name, 0, 127);

						$item_sku = trim((string)$token['item_number']);
						if(strlen($item_sku) > 127)
							$item_sku = substr($item_sku, 0, 127);

						//260817.2119 Keep normal Checkout pricing unchanged; only split subtotal/tax when a Pro-Form token supplies a breakdown that reconciles exactly to the charged total.
						$item_amount = $amount;
						$tax_amount  = '';
						if(isset($token['sub_total'], $token['tax']) && is_numeric($token['sub_total']) && is_numeric($token['tax'])
						&& number_format((float)$token['sub_total'] + (float)$token['tax'], 2, '.', '') === number_format((float)$amount, 2, '.', ''))
						{
							$item_amount = (string)$token['sub_total'];
							$tax_amount  = (string)$token['tax'];
						}

						$purchase_unit = array(
							'invoice_id' => $invoice,
							'amount'     => array(
								'currency_code' => $cc,
								'value'         => $amount,
								'breakdown'     => array(
									'item_total' => array(
										'currency_code' => $cc,
										'value'         => $item_amount,
									),
								),
							),
							'description' => $item_name,
							'items'       => array(
								array(
									'name'        => $item_name,
									'quantity'    => '1',
									'unit_amount' => array(
										'currency_code' => $cc,
										'value'         => $item_amount,
									),
								),
							),
						);

						if($tax_amount !== '' && (float)$tax_amount > 0)
						{
							$purchase_unit['amount']['breakdown']['tax_total'] = array(
								'currency_code' => $cc,
								'value'         => $tax_amount,
							);
							$purchase_unit['items'][0]['tax'] = array(
								'currency_code' => $cc,
								'value'         => $tax_amount,
							);
						}

						if($item_sku)
							$purchase_unit['items'][0]['sku'] = $item_sku;

						// PayPal limits custom_id length; keep it short/consistent.
						if($custom && strlen($custom) <= 127)
							$purchase_unit['custom_id'] = $custom;

						$body = array(
							'intent'         => 'CAPTURE',
							'purchase_units' => array($purchase_unit),
							'application_context' => array(
								'user_action'          => 'PAY_NOW',
								'shipping_preference'  => (!empty($token['ns']) && (string)$token['ns'] === '1') ? 'NO_SHIPPING' : 'GET_FROM_FILE',
								'return_url'           => (string)$token['return'],
								'cancel_url'           => (string)$token['cancel'],
							),
						);

						// Idempotency: stable per invoice for create-order retries.
						$headers = array(
							'PayPal-Request-Id' => 's2m-ppco-order-'.md5($invoice),
						);

						$data = array();
						for($attempt = 0; $attempt < 2; $attempt++)
							{
								$r = self::paypal_checkout_api_request('POST', '/v2/checkout/orders', $body, $headers);
								$code = !empty($r['code']) ? (int)$r['code'] : 0;
								$response_body = !empty($r['body']) ? (string)$r['body'] : '';
								$data = ($response_body) ? json_decode($response_body, true) : array();
								$data = is_array($data) ? $data : array();

								if($code >= 200 && $code <= 299 && !empty($data['id']))
									break;

								$ambiguous = ($code === 0 || $code === 408 || $code >= 500 || ($code >= 200 && $code <= 299));
								if(!$ambiguous)
									break;
							}

						if($code >= 200 && $code <= 299 && !empty($data['id']))
							{
								//260817 Bind the invoice and expected payment data to the PayPal order before the browser can request capture.
								set_transient('s2m_ppco_order_bind_'.md5($invoice), array(
									'order_id' => (string)$data['id'],
									'invoice'  => $invoice,
									'amount'   => $amount,
									'cc'       => $cc,
									'custom'   => $custom,
								), 3 * HOUR_IN_SECONDS);
							}

						return $data;
					}

				/**
				 * Retrieves PayPal Checkout subscription details via the Subscriptions REST API.
				 *
				 * @since 260517
				 *
				 * @param string $subscription_id PayPal subscription id (I-...).
				 *
				 * @return array Decoded subscription response, with __code/__body added; __error on failure.
				 */
				public static function paypal_checkout_subscription_details($subscription_id = '')
					{
						$subscription_id = trim((string)$subscription_id);

						if(!$subscription_id)
							return array('__error' => 'missing_subscription_id', '__code' => 0, '__body' => '');

						$r = self::paypal_checkout_api_request('GET', '/v1/billing/subscriptions/'.rawurlencode($subscription_id));

						$code = !empty($r['code']) ? (int)$r['code'] : 0;
						$body = !empty($r['body']) ? (string)$r['body'] : '';
						$data = ($body) ? json_decode($body, true) : array();
						$data = is_array($data) ? $data : array();

						$data['__code'] = $code;
						$data['__body'] = $body;

						if(!($code >= 200 && $code <= 299) || empty($data['id']))
							$data['__error'] = 'subscription_details_failed';

						return $data;
					}

				/**
				 * Cancels a PayPal Checkout subscription via the Subscriptions REST API.
				 *
				 * Used by the optional on-site cancellation flow (logged-in users).
				 *
				 * @since 260114
				 *
				 * @param string $subscription_id PayPal subscription id (I-...).
				 * @param string $reason          Short human readable reason (PayPal limit applies).
				 *
				 * @return array API request result array from paypal_checkout_api_request().
				 */
				public static function paypal_checkout_subscription_cancel($subscription_id = '', $reason = '')
					{
						$subscription_id = trim((string)$subscription_id);
						$reason          = trim((string)$reason);

						if(!$subscription_id)
							return array('code' => 0, 'message' => 'missing_subscription_id', 'body' => '');

						// PayPal docs: reason 1..128 chars.
						$reason = substr(preg_replace('/\s+/', ' ', strip_tags($reason)), 0, 128);
						if(!$reason)
							$reason = 'Cancelled by subscriber.';

						$body = array('reason' => $reason);

						return self::paypal_checkout_api_request('POST', '/v1/billing/subscriptions/'.rawurlencode($subscription_id).'/cancel', $body);
					}

				/**
				 * Cancels a PayPal Standard/legacy recurring profile via the classic NVP API.
				 *
				 * This is used by cross-gateway replacement flows when the old subscription appears
				 * to be a PayPal Standard recurring profile. //260407
				 *
				 * @since 260407
				 *
				 * @param string $profile_id PayPal recurring profile id.
				 * @param string $action Optional status action. Defaults to `Cancel`.
				 *
				 * @return array API response array from paypal_api_response().
				 */
				public static function paypal_standard_subscription_cancel($profile_id = '', $action = 'Cancel')
					{
						$profile_id = trim((string)$profile_id);
						$action     = trim((string)$action);

						if(!$profile_id)
							return array('__error' => 'missing_profile_id');

						if(!$action)
							$action = 'Cancel';

						//260407 This still goes through the existing authenticated NVP helper, so current PayPal API credentials are required.
						return self::paypal_api_response(array(
							'METHOD'    => 'ManageRecurringPaymentsProfileStatus',
							'ACTION'    => $action,
							'PROFILEID' => $profile_id,
						));
					}

				/**
				 * Captures a PayPal Checkout order (server-side) after buyer approval.
				 *
				 * Used by the JS SDK onApprove callback (capture_order op) and by redirect-mode
				 * return handling. On success, the capture details are proxied into the legacy
				 * s2Member PayPal notify/return handlers.
				 *
				 * @since 260101
				 *
				 * @param string $order_id PayPal Checkout order id.
				 * @param array  $token    Signed/validated purchase token.
				 *
				 * @return array API request result array from paypal_checkout_api_request().
				 */
				public static function paypal_checkout_order_capture($order_id = '', $token = array())
					{
						$order_id = trim((string)$order_id);
						if(!$order_id)
							return array('__error' => 'missing_order_id');

						$invoice = !empty($token['invoice']) ? (string)$token['invoice'] : '';
						$binding_name = $invoice ? 's2m_ppco_order_bind_'.md5($invoice) : '';
						$binding = $binding_name ? get_transient($binding_name) : false;

						if(is_array($binding))
							{
								$binding_matches = (!empty($binding['order_id']) && (string)$binding['order_id'] === $order_id
								&& isset($binding['invoice']) && (string)$binding['invoice'] === $invoice
								&& isset($binding['amount']) && number_format((float)$binding['amount'], 2, '.', '') === number_format((float)$token['amount'], 2, '.', '')
								&& isset($binding['cc']) && strtoupper((string)$binding['cc']) === strtoupper((string)$token['cc'])
								&& isset($binding['custom']) && (string)$binding['custom'] === (string)$token['custom']);

								if(!$binding_matches)
									return array('__error' => 'order_binding_mismatch');
							}

						$capture_lock = 's2m_ppco_capture_lock_'.md5($order_id);
						if(!self::dedupe_lock_acquire($capture_lock, 300))
							return array('__error' => 'capture_in_progress');

						try
							{
								//260817 If the short-lived local binding is gone, verify PayPal's order before attempting capture.
								if(!is_array($binding))
									{
										$details = self::paypal_checkout_order_details($order_id);
										if(!empty($details['__error']))
											return $details;

										if(($validation_error = self::paypal_checkout_order_validation_error($details, $order_id, $token)))
											return array('__error' => $validation_error);

										if(!empty($details['status']) && strtoupper((string)$details['status']) === 'COMPLETED')
											{
												if(($completion_error = self::paypal_checkout_order_completion_error($details, $order_id, $token)))
													return array('__error' => $completion_error);

												return $details;
											}
										if(empty($details['status']) || strtoupper((string)$details['status']) !== 'APPROVED')
											return array('__error' => 'order_not_approved');
									}

								// Idempotency: stable per order capture retries.
								$headers = array(
									'PayPal-Request-Id' => 's2m-ppco-cap-'.md5($order_id),
									'Prefer'            => 'return=representation',
								);

								$r = array();
								$data = array();
								for($attempt = 0; $attempt < 2; $attempt++)
									{
										$r = self::paypal_checkout_api_request('POST', '/v2/checkout/orders/'.$order_id.'/capture', (object)array(), $headers);
										$code = !empty($r['code']) ? (int)$r['code'] : 0;
										$body = !empty($r['body']) ? (string)$r['body'] : '';
										$data = ($body) ? json_decode($body, true) : array();
										$data = is_array($data) ? $data : array();

										if($code >= 200 && $code <= 299)
											break;

										$ambiguous = ($code === 0 || $code === 408 || $code >= 500);
										if(!$ambiguous)
											break;
									}

								$code = !empty($r['code']) ? (int)$r['code'] : 0;
								if($code >= 200 && $code <= 299 && !($completion_error = self::paypal_checkout_order_completion_error($data, $order_id, $token)))
									{
										if($binding_name)
											delete_transient($binding_name);
										return $data;
									}

								//260817 Recover from an ambiguous or incomplete capture response by reading PayPal's final order state.
								$details = self::paypal_checkout_order_details($order_id);
								if(empty($details['__error']) && !($completion_error = self::paypal_checkout_order_completion_error($details, $order_id, $token)))
									{
										if($binding_name)
											delete_transient($binding_name);
										return $details;
									}

								if($code >= 200 && $code <= 299 && !empty($completion_error))
									return array('__error' => $completion_error);
								if(!empty($details['__error']))
									return $details;
								return array('__error' => 'order_capture_failed', '__code' => $code, '__body' => !empty($r['body']) ? (string)$r['body'] : '');
							}
						finally
							{
								self::dedupe_lock_release($capture_lock);
							}
					}

				/**
				 * Sends PayPal Checkout fulfillment through s2Member's existing PayPal Notify handler once.
				 *
				 * @since 260817
				 *
				 * @param array  $paypal      PayPal-style transaction variables.
				 * @param string $done_option Local fulfillment done-marker option name.
				 * @param string $proxy_use   Optional proxy-use routing value.
				 * @param array  $extra       Optional additional server-side Notify variables.
				 *
				 * @return array Result with ok/processed/duplicate/error and response details.
				 */
				public static function paypal_checkout_notify_once($paypal = array(), $done_option = '', $proxy_use = 'paypal_checkout', $extra = array())
					{
						if(!is_array($paypal) || !$paypal || !$done_option || !is_string($done_option))
							return array('ok' => false, 'processed' => false, 'duplicate' => false, 'error' => 'notify_invalid_args');

						//260818.0603 This helper now coordinates one-time and subscription fulfillment markers.
						self::dedupe_markers_cleanup('s2m_ppco_notify_cleanup_throttle', array(
							array('prefix' => 's2m_ppco_capture_done_', 'ttl' => DAY_IN_SECONDS),
							array('prefix' => 's2m_ppco_subscr_done_', 'ttl' => DAY_IN_SECONDS),
							array('prefix' => 's2m_ppco_notify_lock_', 'ttl' => HOUR_IN_SECONDS),
							array('prefix' => 's2m_ppco_capture_lock_', 'ttl' => HOUR_IN_SECONDS),
						));

						$result_transient = 's2m_ppco_notify_result_'.md5($done_option);
						if(self::dedupe_done_time_get($done_option, DAY_IN_SECONDS))
							{
								$cached_result = get_transient($result_transient);
								return array_merge(array('ok' => true, 'processed' => false, 'duplicate' => true, 'error' => ''), is_array($cached_result) ? $cached_result : array());
							}

						$lock_option = 's2m_ppco_notify_lock_'.md5($done_option);
						if(!self::dedupe_lock_acquire($lock_option, 900))
							{
								if(self::dedupe_done_time_get($done_option, DAY_IN_SECONDS))
									{
										$cached_result = get_transient($result_transient);
										return array_merge(array('ok' => true, 'processed' => false, 'duplicate' => true, 'error' => ''), is_array($cached_result) ? $cached_result : array());
									}

								return array('ok' => false, 'processed' => false, 'duplicate' => false, 'error' => 'notify_in_progress');
							}

						try
							{
								if(self::dedupe_done_time_get($done_option, DAY_IN_SECONDS))
									{
										$cached_result = get_transient($result_transient);
										return array_merge(array('ok' => true, 'processed' => false, 'duplicate' => true, 'error' => ''), is_array($cached_result) ? $cached_result : array());
									}

								//260818.0617 Allow Pro to prepare account-specific fulfillment inside the shared Notify lock and enrich fallback context.
								$notify_context = apply_filters('ws_plugin__s2member_paypal_checkout_notify_context', array(
									'paypal'    => $paypal,
									'proxy_use' => (string)$proxy_use,
									'extra'     => is_array($extra) ? $extra : array(),
								), $done_option);

								if(is_wp_error($notify_context))
									return array('ok' => false, 'processed' => false, 'duplicate' => false, 'error' => 'notify_context_failed', 'context_error' => (string)$notify_context->get_error_code());

								if(!is_array($notify_context) || empty($notify_context['paypal']) || !is_array($notify_context['paypal']))
									return array('ok' => false, 'processed' => false, 'duplicate' => false, 'error' => 'notify_context_invalid');

								$paypal    = $notify_context['paypal'];
								$proxy_use = isset($notify_context['proxy_use']) ? (string)$notify_context['proxy_use'] : (string)$proxy_use;
								$extra     = !empty($notify_context['extra']) && is_array($notify_context['extra']) ? $notify_context['extra'] : array();

								$notify_url = home_url('/?s2member_paypal_notify=1');
								$notify_post = array_merge($paypal, $extra, array(
									's2member_paypal_proxy'              => 'paypal',
									's2member_paypal_proxy_use'          => $proxy_use,
									's2member_paypal_proxy_verification' => self::paypal_proxy_key_gen(),
								));
								$notify_r = c_ws_plugin__s2member_utils_urls::remote($notify_url, $notify_post, array('timeout' => 20), true);

								if(!is_array($notify_r))
									$notify_r = array('code' => 0, 'message' => 'request_failed', 'body' => '');

								$code = !empty($notify_r['code']) ? (int)$notify_r['code'] : 0;
								$message = !empty($notify_r['message']) ? (string)$notify_r['message'] : '';
								$body = !empty($notify_r['body']) ? (string)$notify_r['body'] : '';

								if($code >= 200 && $code <= 299)
									{
										$result = array('code' => $code, 'message' => $message, 'body' => $body);
										set_transient($result_transient, $result, DAY_IN_SECONDS); // Preserve the Notify result for safe duplicate/retry returns, including future Pro success URLs.
										self::dedupe_done_mark($done_option);

										//260818.1752 Run account-specific post-Notify work only after fulfillment is durably marked complete.
										do_action('ws_plugin__s2member_paypal_checkout_notify_processed', $notify_context, $done_option, $result);

										return array_merge(array('ok' => true, 'processed' => true, 'duplicate' => false, 'error' => ''), $result);
									}

								return array('ok' => false, 'processed' => false, 'duplicate' => false, 'error' => 'notify_proxy_failed', 'code' => $code, 'message' => $message, 'body' => $body);
							}
						finally
							{
								self::dedupe_lock_release($lock_option);
							}
					}

				/**
				 * Creates a PayPal Checkout subscription (server-side) when using redirect-mode approval.
				 *
				 * In JS SDK button mode, subscriptions are created client-side using plan_id and
				 * then confirmed server-side. Redirect-mode requires server-side creation.
				 *
				 * @since 260114
				 *
				 * @param array $token Signed/validated purchase token.
				 *
				 * @return array API request result array from paypal_checkout_api_request().
				 */
				public static function paypal_checkout_subscription_create($token = array())
					{
						if(!is_array($token))
							return array();

						$invoice = (string)$token['invoice'];

						$plan_id = self::paypal_checkout_plan_get_id($token);
						if(!$plan_id)
							return array();

						$brand_name = get_bloginfo('name');
						$brand_name = substr(preg_replace('/\s+/', ' ', trim(strip_tags($brand_name))), 0, 127);

						$body = array(
							'plan_id'              => $plan_id,
							'custom_id'            => $invoice,
							'application_context'  => array(
								'brand_name'          => $brand_name,
								'return_url'          => (string)$token['return'],
								'cancel_url'          => (string)$token['cancel'],
								'user_action'         => 'SUBSCRIBE_NOW',
								'shipping_preference' => 'NO_SHIPPING',
							),
						);

						// Idempotency: stable per invoice for create-subscription retries.
						$headers = array(
							'PayPal-Request-Id' => 's2m-ppco-sub-'.md5($invoice),
						);

						$r = self::paypal_checkout_api_request('POST', '/v1/billing/subscriptions', $body, $headers);

						$data = array();
						if(!empty($r['body']) && is_string($r['body']))
							$data = json_decode($r['body'], true);

						return is_array($data) ? $data : array();
					}

				/**
				 * Returns a PayPal Checkout Plan ID for a subscription token (creates product/plan if needed).
				 *
				 * Plan/product creation is cached in ws_plugin__s2member_options to avoid duplicates.
				 * Cache key is derived from plan-affecting attributes (currency, billing cycles, trial).
				 *
				 * @since 260101
				 *
				 * @param array $token Signed/validated purchase token from shortcode flow.
				 *
				 * @return string PayPal plan id (P-...) or empty string on failure.
				 */
				public static function paypal_checkout_plan_get_id($token = array())
					{
						if(!is_array($token))
							return '';

						$cc = !empty($token['cc']) ? strtoupper(trim((string)$token['cc'])) : '';
						$rr = isset($token['rr']) ? strtoupper(trim((string)$token['rr'])) : '';
						$ra = isset($token['amount']) ? (string)$token['amount'] : '';
						$rp = !empty($token['rp']) ? (int)$token['rp'] : 0;
						$rt = !empty($token['rt']) ? strtoupper(trim((string)$token['rt'])) : '';

						$is_pro_form = !empty($token['s2member_paypal_proxy_use']) && strpos((string)$token['s2member_paypal_proxy_use'], 'pro-emails') !== false;
						$rrt = !empty($token['rrt']) ? (int)$token['rrt'] : 0;
						$rra = isset($token['rra']) ? (int)$token['rra'] : ($is_pro_form ? 2 : 1);

						//260827.1950 Pro-Forms define rra as the exact Max Failed Payments value for any recurring profile;
						// Framework buttons retain their legacy PayPal Standard retry semantics. rrt remains rr="1" only.
						if($rr !== '1')
							$rrt = 0;

						$ta = isset($token['ta']) ? (string)$token['ta'] : '';
						$tp = !empty($token['tp']) ? (int)$token['tp'] : 0;
						$tt = !empty($token['tt']) ? strtoupper(trim((string)$token['tt'])) : '';

						if(!$cc || $rr === '' || $rr === 'BN' || $rp < 1 || !$rt)
							return '';

						$env     = self::paypal_checkout_is_sandbox() ? 'sandbox' : 'live';
						$cred_id = self::paypal_checkout_cred_id($env);
						if(!$cred_id)
							return '';

						$plan_key = md5(serialize(array(
							'env'         => $env,
							'cc'          => $cc,
							'rr'          => $rr,
							'ra'          => (string)$ra,
							'rp'          => (int)$rp,
							'rt'          => (string)$rt,

							'rrt'         => (int)$rrt,
							'rra'         => (int)$rra,
							//260827.2129 !!! TO-DO: Standardize Pro-Form and Framework rrt/rra semantics in a future gateway abstraction; keep Plan caches separate until both contracts match.
							'pro_form'    => (int)$is_pro_form,

							'ta'          => (string)$ta,
							'tp'          => (int)$tp,
							'tt'          => (string)$tt,
							'item_number' => !empty($token['item_number']) ? (string)$token['item_number'] : '',
							'item_name'   => !empty($token['item_name']) ? (string)$token['item_name'] : '',
						)));

						$ppco_opt = !empty($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_checkout_cache"]) ? $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_checkout_cache"] : array();
						if(!is_array($ppco_opt))
							$ppco_opt = array();

						$plan_ids = (!empty($ppco_opt[$cred_id][$env]['plan_ids']) && is_array($ppco_opt[$cred_id][$env]['plan_ids'])) ? $ppco_opt[$cred_id][$env]['plan_ids'] : array();

						if(!empty($plan_ids[$plan_key]) && is_string($plan_ids[$plan_key]))
							return $plan_ids[$plan_key];

						$product_id = self::paypal_checkout_product_get_id();
						if(!$product_id)
							return '';

						$unit_map = array('D' => 'DAY', 'W' => 'WEEK', 'M' => 'MONTH', 'Y' => 'YEAR');
						$rt_unit  = !empty($unit_map[$rt]) ? $unit_map[$rt] : 'MONTH';
						$tt_unit  = !empty($unit_map[$tt]) ? $unit_map[$tt] : $rt_unit;

						$rp = max(1, (int)$rp);
						$tp = max(0, (int)$tp);

						$ra_v = number_format((float)$ra, 2, '.', '');
						$ta_v = number_format((float)$ta, 2, '.', '');

						$regular_total_cycles = 0; // 0 = infinite.

						//260827.2129 Legacy Pro-Forms without an initial term charge once at checkout and define rrt as additional payments.
						// PPCO regular cycles include the checkout payment, while Framework buttons retain total-installment rrt semantics.
						if($rr === '1' && $rrt > 0)
						{
							$regular_total_cycles = (int)$rrt + (($is_pro_form && $tp === 0) ? 1 : 0);
							if($regular_total_cycles > 999) // PayPal cannot represent the legacy Pro-Form result; fail instead of silently reducing the number of charges.
								return '';
						}
						else if($rr === '0')
							$regular_total_cycles = 1;

						//260827.1950 Preserve the Pro-Form's documented exact rra value; Framework buttons keep legacy Standard boolean retry behavior.
						$payment_failure_threshold = $is_pro_form ? max(0, (int)$rra) : (($rr === '1' && $rra) ? 2 : 1);

						$billing_cycles = array();
						$seq = 1;

						if($tp > 0)
						{
							$billing_cycles[] = array(
								'frequency' => array(
									'interval_unit'  => $tt_unit,
									'interval_count' => $tp,
								),
								'tenure_type'    => 'TRIAL',
								'sequence'       => $seq++,
								'total_cycles'   => 1,
								'pricing_scheme' => array(
									'fixed_price' => array(
										'value'         => $ta_v,
										'currency_code' => $cc,
									),
								),
							);
						}

						$billing_cycles[] = array(
							'frequency' => array(
								'interval_unit'  => $rt_unit,
								'interval_count' => $rp,
							),
							'tenure_type'    => 'REGULAR',
							'sequence'       => $seq++,
							'total_cycles'   => $regular_total_cycles,
							'pricing_scheme' => array(
								'fixed_price' => array(
									'value'         => $ra_v,
									'currency_code' => $cc,
								),
							),
						);

						$plan_name = !empty($token['item_name']) ? (string)$token['item_name'] : 's2Member Plan';
						$plan_name = substr(preg_replace('/\s+/', ' ', trim(strip_tags($plan_name))), 0, 127);

						$plan_desc = $plan_name;
						if(!empty($token['rr']) && $token['rr'] !== 'BN' && !empty($token['rp']) && !empty($token['rt']))
						{
							$plan_desc .= ' (recurring)';
						}
						$plan_desc = substr(preg_replace('/\s+/', ' ', trim(strip_tags($plan_desc))), 0, 127);

						$body = array(
							'product_id'          => $product_id,
							'name'                => $plan_name,
							'description'         => $plan_desc,
							'status'              => 'ACTIVE',
							'billing_cycles'      => $billing_cycles,
							'payment_preferences' => array(
								'auto_bill_outstanding'      => true,
								'setup_fee'                  => array('value' => '0.00', 'currency_code' => $cc),
								'setup_fee_failure_action'   => 'CONTINUE',
								'payment_failure_threshold'  => $payment_failure_threshold,
							),
						);

						$headers = array(
							'PayPal-Request-Id' => 's2m-ppco-plan-'.md5($env.'|'.$plan_key.'|'.md5((string)wp_json_encode($body))),
						);

						$r = self::paypal_checkout_api_request('POST', '/v1/billing/plans', $body, $headers);

						$data = array();
						if(!empty($r['body']) && is_string($r['body']))
							$data = json_decode($r['body'], true);

						$plan_id = !empty($data['id']) ? (string)$data['id'] : '';
						if(!$plan_id)
						{
							c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
								'ppco'     => 'plan',
								'event'    => 'plan_create_failed',
								'env_setting' => $env,
								'plan_key' => $plan_key,
								'code'     => !empty($r['code']) ? (int)$r['code'] : 0,
								'message'  => !empty($r['message']) ? (string)$r['message'] : '',
								'body'     => !empty($r['body']) ? (string)$r['body'] : '',
								'request'  => $body,
							));
							return '';
						}

						$plan_ids[$plan_key] = $plan_id;

						$options = get_option('ws_plugin__s2member_options');
						if(!is_array($options))
							$options = array();

						if(empty($options['paypal_checkout_cache']) || !is_array($options['paypal_checkout_cache']))
							$options['paypal_checkout_cache'] = array();

						if(empty($options['paypal_checkout_cache'][$cred_id]) || !is_array($options['paypal_checkout_cache'][$cred_id]))
							$options['paypal_checkout_cache'][$cred_id] = array();

						if(empty($options['paypal_checkout_cache'][$cred_id][$env]) || !is_array($options['paypal_checkout_cache'][$cred_id][$env]))
							$options['paypal_checkout_cache'][$cred_id][$env] = array();

						if(empty($options['paypal_checkout_cache'][$cred_id][$env]['plan_ids']) || !is_array($options['paypal_checkout_cache'][$cred_id][$env]['plan_ids']))
							$options['paypal_checkout_cache'][$cred_id][$env]['plan_ids'] = array();

						$options['paypal_checkout_cache'][$cred_id][$env]['plan_ids'] = $plan_ids;

						// Delete legacy cache keys (no migration; just remove).
						if(isset($options['paypal_checkout_plan_ids']))
							unset($options['paypal_checkout_plan_ids']);

						$options = ws_plugin__s2member_configure_options_and_their_defaults($options);

						update_option('ws_plugin__s2member_options', $options).((is_multisite() && is_main_site()) ? update_site_option('ws_plugin__s2member_options', $options) : NULL);
						$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_checkout_cache"] = (!empty($options['paypal_checkout_cache']) && is_array($options['paypal_checkout_cache'])) ? $options['paypal_checkout_cache'] : array();

						c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
							'ppco'     => 'plan',
							'event'    => 'plan_cached',
							'env_setting' => $env,
							'cred_id'  => $cred_id,
							'plan_key' => $plan_key,
							'plan_id'  => $plan_id,
						));

						return $plan_id;
					}

				/**
				 * Returns a PayPal Catalog Product ID (creates and caches if needed).
				 *
				 * Cached under:
				 * - $options['paypal_checkout_cache'][$cred_id][$env]['product_ids'][$product_key]
				 *
				 * @since 260101
				 *
				 * @return string PayPal product id (PROD-...) or empty string on failure.
				 */
				public static function paypal_checkout_product_get_id()
					{
						$env     = self::paypal_checkout_is_sandbox() ? 'sandbox' : 'live';
						$cred_id = self::paypal_checkout_cred_id($env);
						if(!$cred_id)
							return '';

						$product_key = 'default';

						$ppco_opt = !empty($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_checkout_cache"]) ? $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_checkout_cache"] : array();
						if(!is_array($ppco_opt))
							$ppco_opt = array();

						$product_ids = (!empty($ppco_opt[$cred_id][$env]['product_ids']) && is_array($ppco_opt[$cred_id][$env]['product_ids'])) ? $ppco_opt[$cred_id][$env]['product_ids'] : array();

						if(!empty($product_ids[$product_key]) && is_string($product_ids[$product_key]))
							return $product_ids[$product_key];

						$name = get_bloginfo('name');
						$url  = home_url('/');

						$name = substr(preg_replace('/\s+/', ' ', trim(strip_tags((string)$name))), 0, 127);
						if(!$name)
							$name = 's2Member';

						$body = array(
							'name'        => $name.' Membership',
							'description' => 'Membership billing product (created by s2Member).',
							'type'        => 'SERVICE',
							'category'    => 'SOFTWARE',
							'home_url'    => $url,
						);

						$headers = array(
							'PayPal-Request-Id' => 's2m-ppco-prod-'.md5($env),
						);

						$r = self::paypal_checkout_api_request('POST', '/v1/catalogs/products', $body, $headers);

						$data = array();
						if(!empty($r['body']) && is_string($r['body']))
							$data = json_decode($r['body'], true);

						$product_id = !empty($data['id']) ? (string)$data['id'] : '';
						if(!$product_id)
						{
							c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
								'ppco'     => 'product',
								'event'    => 'product_create_failed',
								'env_setting' => $env,
								'code'     => !empty($r['code']) ? (int)$r['code'] : 0,
								'message'  => !empty($r['message']) ? (string)$r['message'] : '',
								'body'     => !empty($r['body']) ? (string)$r['body'] : '',
							));
							return '';
						}

						$product_ids[$product_key] = $product_id;

						$options = get_option('ws_plugin__s2member_options');
						if(!is_array($options))
							$options = array();

						if(empty($options['paypal_checkout_cache']) || !is_array($options['paypal_checkout_cache']))
							$options['paypal_checkout_cache'] = array();

						if(empty($options['paypal_checkout_cache'][$cred_id]) || !is_array($options['paypal_checkout_cache'][$cred_id]))
							$options['paypal_checkout_cache'][$cred_id] = array();

						if(empty($options['paypal_checkout_cache'][$cred_id][$env]) || !is_array($options['paypal_checkout_cache'][$cred_id][$env]))
							$options['paypal_checkout_cache'][$cred_id][$env] = array();

						if(empty($options['paypal_checkout_cache'][$cred_id][$env]['product_ids']) || !is_array($options['paypal_checkout_cache'][$cred_id][$env]['product_ids']))
							$options['paypal_checkout_cache'][$cred_id][$env]['product_ids'] = array();

						$options['paypal_checkout_cache'][$cred_id][$env]['product_ids'] = $product_ids;

						// Delete legacy cache keys (no migration; just remove).
						if(isset($options['paypal_checkout_product_ids']))
							unset($options['paypal_checkout_product_ids']);

						$options = ws_plugin__s2member_configure_options_and_their_defaults($options);

						update_option('ws_plugin__s2member_options', $options).((is_multisite() && is_main_site()) ? update_site_option('ws_plugin__s2member_options', $options) : NULL);
						$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_checkout_cache"] = (!empty($options['paypal_checkout_cache']) && is_array($options['paypal_checkout_cache'])) ? $options['paypal_checkout_cache'] : array();

						c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
							'ppco'     => 'product',
							'event'    => 'product_cached',
							'env_setting' => $env,
							'cred_id'  => $cred_id,
							'product_key' => $product_key,
							'product_id' => $product_id,
						));

						return $product_id;
					}

				/**
				 * Returns the stored PayPal webhook id for the active environment.
				 *
				 * @since 260101
				 *
				 * @return string Webhook id or empty string.
				 */
				public static function paypal_checkout_webhook_id()
					{
						return self::paypal_checkout_is_sandbox()
							? (string)$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_checkout_sandbox_webhook_id"]
							: (string)$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_checkout_webhook_id"];
					}

				/**
				 * Verifies a PayPal webhook signature via PayPal's verify-webhook-signature API.
				 *
				 * @since 260115
				 *
				 * @param mixed  $event    Decoded event array (or raw JSON string in $raw_body).
				 * @param string $raw_body Raw webhook body.
				 * @param array  $headers  Request headers (lowercase keys expected).
				 *
				 * @return bool True if signature verifies; otherwise false.
				 */
				public static function paypal_checkout_verify_webhook_signature($event, $raw_body, $headers = array())
					{
						$tx_id   = !empty($headers['paypal-transmission-id']) ? $headers['paypal-transmission-id'] : '';
						$tx_time = !empty($headers['paypal-transmission-time']) ? $headers['paypal-transmission-time'] : '';
						$tx_sig  = !empty($headers['paypal-transmission-sig']) ? $headers['paypal-transmission-sig'] : '';
						$cert    = !empty($headers['paypal-cert-url']) ? $headers['paypal-cert-url'] : '';
						$algo    = !empty($headers['paypal-auth-algo']) ? $headers['paypal-auth-algo'] : '';

						if(!$tx_id || !$tx_time || !$tx_sig || !$cert || !$algo)
							return false;

						//260205 Detect sandbox vs live from the cert URL.
						$orig_sandbox = self::paypal_checkout_is_sandbox();
						$cert_is_sandbox = (strpos((string)$cert, 'sandbox') !== false);

						$GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_sandbox'] = $cert_is_sandbox ? '1' : '0';

						$webhook_id = self::paypal_checkout_webhook_id();
						if(!$webhook_id)
						{
							$GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_sandbox'] = $orig_sandbox ? '1' : '0';
							return false;
						}

						$body = array(
							'transmission_id'   => $tx_id,
							'transmission_time' => $tx_time,
							'cert_url'          => $cert,
							'auth_algo'         => $algo,
							'transmission_sig'  => $tx_sig,
							'webhook_id'        => $webhook_id,
							'webhook_event'     => is_array($event) ? $event : json_decode((string)$raw_body, true),
						);

						$r = self::paypal_checkout_api_request('POST', '/v1/notifications/verify-webhook-signature', $body);
						if(empty($r['code']) || (int)$r['code'] !== 200 || empty($r['body']))
						{
							$GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_sandbox'] = $orig_sandbox ? '1' : '0';
							return false;
						}

						if(!is_string($r['body']))
						{
							$GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_sandbox'] = $orig_sandbox ? '1' : '0';
							return false;
						}

						$data = json_decode($r['body'], true);

						$GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_sandbox'] = $orig_sandbox ? '1' : '0';
						return !empty($data['verification_status']) && $data['verification_status'] === 'SUCCESS';
					}

				/**
				 * Returns the PayPal Checkout webhook event names processed by s2Member.
				 *
				 * These events are used for:
				 * - Subscription activation fallback and lifecycle changes.
				 * - Recurring payment bookkeeping, refunds, and reversals.
				 *
				 * @since 260115
				 *
				 * @return array<string> Event type names.
				 */
				public static function paypal_checkout_webhook_event_names()
					{
						//260820.0218 Keep automatic webhook registration aligned with the events handled by s2Member and listed in PayPal Checkout setup help.
						return array(
							'PAYMENT.SALE.COMPLETED',
							'PAYMENT.CAPTURE.COMPLETED',
							'PAYMENT.SALE.REFUNDED',
							'PAYMENT.CAPTURE.REFUNDED',
							'PAYMENT.SALE.REVERSED',
							'PAYMENT.CAPTURE.REVERSED',

							//260824.1727 Treat a newly opened PayPal dispute as a chargeback/reversal through s2Member's existing EOT policy.
							'CUSTOMER.DISPUTE.CREATED',

							'BILLING.SUBSCRIPTION.CREATED',
							'BILLING.SUBSCRIPTION.ACTIVATED',
							'BILLING.SUBSCRIPTION.RE-ACTIVATED',
							'BILLING.SUBSCRIPTION.UPDATED',
							'BILLING.SUBSCRIPTION.CANCELLED',
							'BILLING.SUBSCRIPTION.SUSPENDED',
							'BILLING.SUBSCRIPTION.EXPIRED',
							'BILLING.SUBSCRIPTION.PAYMENT.FAILED',
						);
					}

				/**
				 * Creates or updates a PayPal Checkout webhook for the current site URL and required events.
				 *
				 * Used by the admin "Create/Update Webhook Automatically" buttons.
				 * Persists the webhook id into ws_plugin__s2member_options for the selected environment.
				 *
				 * @since 260115
				 *
				 * @param string $env           'live' or 'sandbox'. Defaults to 'live'.
				 * @param bool   $existing_only If true, update only a webhook whose ID is already stored; never create/adopt one.
				 *
				 * @return array Result array on success with keys:
				 *               - id (string) webhook id
				 *               - op (string) 'created'|'updated'|'adopted'
				 *               - env (string) 'live'|'sandbox'
				 *              Empty array on failure.
				 */
				public static function paypal_checkout_webhook_upsert($env = '', $existing_only = false)
					{
						$env = ($env === 'sandbox') ? 'sandbox' : 'live';

						$orig_sandbox = self::paypal_checkout_is_sandbox();
						$GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_sandbox'] = ($env === 'sandbox') ? '1' : '0';

						$url = add_query_arg('s2member_paypal_webhook', '1', home_url('/', 'https'));

						$event_types = array();
						foreach(self::paypal_checkout_webhook_event_names() as $name)
							$event_types[] = array('name' => $name);

						$existing_id = self::paypal_checkout_webhook_id();

						if($existing_id)
						{
							$patch = array(
								array('op' => 'replace', 'path' => '/url', 'value' => $url),
								array('op' => 'replace', 'path' => '/event_types', 'value' => $event_types),
							);
							$r = self::paypal_checkout_api_request('PATCH', '/v1/notifications/webhooks/'.rawurlencode($existing_id), $patch);

							if(!empty($r['code']) && (int)$r['code'] === 200)
							{
								self::paypal_checkout_webhook_store_id($existing_id);

								c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
									'ppco'     => 'webhook',
									'event'    => 'updated_webhook',
									'env_setting' => $env,
									'id'       => $existing_id,
									'url'      => $url,
									'code'     => (int)$r['code'],
								));

								$GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_sandbox'] = $orig_sandbox ? '1' : '0';
								return array('id' => $existing_id, 'op' => 'updated', 'env' => $env);
							}

							//260205 PayPal may return 400 when there is no change; treat as success.
							$no_change = false;
							if(!empty($r['body']) && is_string($r['body']))
							{
								$d = json_decode($r['body'], true);
								$no_change = !empty($d['name']) && $d['name'] === 'WEBHOOK_PATCH_REQUEST_NO_CHANGE';
							}
							if($no_change)
							{
								self::paypal_checkout_webhook_store_id($existing_id);

								c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
									'ppco'     => 'webhook',
									'event'    => 'updated_webhook_no_change',
									'env_setting' => $env,
									'id'       => $existing_id,
									'url'      => $url,
									'code'     => !empty($r['code']) ? (int)$r['code'] : 0,
								));

								$GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_sandbox'] = $orig_sandbox ? '1' : '0';
								return array('id' => $existing_id, 'op' => 'updated', 'env' => $env);
							}

							c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
								'ppco'     => 'webhook',
								'event'    => 'update_webhook_failed',
								'env_setting' => $env,
								'id'       => $existing_id,
								'url'      => $url,
								'code'     => !empty($r['code']) ? (int)$r['code'] : 0,
								'message'  => !empty($r['message']) ? (string)$r['message'] : '',
								'body'     => !empty($r['body']) ? (string)$r['body'] : '',
							));
						}

						//260820.0313 Upgrade reconciliation must never create or adopt a webhook the site owner did not already store.
						if($existing_only)
						{
							$GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_sandbox'] = $orig_sandbox ? '1' : '0';
							return array();
						}

						$body = array(
							'url'         => $url,
							'event_types' => $event_types,
						);
						$r = self::paypal_checkout_api_request('POST', '/v1/notifications/webhooks', $body);

						$id = '';
						if(!empty($r['code']) && (int)$r['code'] === 201 && !empty($r['body']) && is_string($r['body']))
						{
							$data = json_decode($r['body'], true);
							if(!empty($data['id']))
								$id = (string)$data['id'];
						}

						$adopted_existing = false;

						//260205 If URL already exists, lookup existing webhook by URL and adopt its ID.
						if(!$id && !empty($r['code']) && (int)$r['code'] === 400 && !empty($r['body']) && is_string($r['body']))
						{
							$d = json_decode($r['body'], true);
							if(!empty($d['name']) && $d['name'] === 'WEBHOOK_URL_ALREADY_EXISTS')
							{
								$lr = self::paypal_checkout_api_request('GET', '/v1/notifications/webhooks');
								if(!empty($lr['code']) && (int)$lr['code'] === 200 && !empty($lr['body']) && is_string($lr['body']))
								{
									$ld = json_decode($lr['body'], true);
									if(!empty($ld['webhooks']) && is_array($ld['webhooks']))
									{
										foreach($ld['webhooks'] as $_wh)
											if(!empty($_wh['url']) && (string)$_wh['url'] === $url && !empty($_wh['id']))
											{
												$id = (string)$_wh['id'];
												$adopted_existing = true;
												break;
											}
									}
								}
							}
						}

						//260820.0313 A same-app webhook found by this exact s2Member URL is safe to adopt, but first reconcile its required events.
						if($id && $adopted_existing)
						{
							$patch = array(
								array('op' => 'replace', 'path' => '/url', 'value' => $url),
								array('op' => 'replace', 'path' => '/event_types', 'value' => $event_types),
							);
							$ur = self::paypal_checkout_api_request('PATCH', '/v1/notifications/webhooks/'.rawurlencode($id), $patch);
							$adopt_update_ok = (!empty($ur['code']) && (int)$ur['code'] === 200);

							if(!$adopt_update_ok && !empty($ur['body']) && is_string($ur['body']))
							{
								$ud = json_decode($ur['body'], true);
								$adopt_update_ok = !empty($ud['name']) && $ud['name'] === 'WEBHOOK_PATCH_REQUEST_NO_CHANGE';
							}
							if(!$adopt_update_ok)
							{
								c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
									'ppco'     => 'webhook',
									'event'    => 'update_adopted_webhook_failed',
									'env_setting' => $env,
									'id'       => $id,
									'url'      => $url,
									'code'     => !empty($ur['code']) ? (int)$ur['code'] : 0,
									'message'  => !empty($ur['message']) ? (string)$ur['message'] : '',
									'body'     => !empty($ur['body']) ? (string)$ur['body'] : '',
								));
								$id = '';
							}
						}

						if($id)
						{
							self::paypal_checkout_webhook_store_id($id);

							c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
								'ppco'     => 'webhook',
								'event'    => $adopted_existing ? 'adopted_webhook' : 'created_webhook',
								'env_setting' => $env,
								'id'       => $id,
								'url'      => $url,
								'code'     => $adopted_existing ? 200 : (int)$r['code'],
							));

							$GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_sandbox'] = $orig_sandbox ? '1' : '0';
							return array('id' => $id, 'op' => $adopted_existing ? 'adopted' : 'created', 'env' => $env);
						}

						c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
							'ppco'     => 'webhook',
							'event'    => 'create_webhook_failed',
							'env_setting' => $env,
							'url'      => $url,
							'code'     => !empty($r['code']) ? (int)$r['code'] : 0,
							'message'  => !empty($r['message']) ? (string)$r['message'] : '',
							'body'     => !empty($r['body']) ? (string)$r['body'] : '',
						));

						$GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_sandbox'] = $orig_sandbox ? '1' : '0';
						return array();
					}

				/**
				 * Clears a resolved PayPal Checkout webhook upgrade notice.
				 *
				 * @since 260824.0507
				 *
				 * @param string $env 'live' or 'sandbox'.
				 *
				 * @return void
				 */
				protected static function paypal_checkout_webhook_upgrade_notice_clear($env = '')
					{
						$env = ($env === 'sandbox') ? 'sandbox' : 'live';
						$env_label = ($env === 'sandbox') ? 'Sandbox' : 'Live';
						$marker = 's2member-ppco-webhook-upgrade-notice-'.$env;
						$legacy_message = 'Your '.$env_label.' webhook could not be updated automatically with the latest required events.';

						$notices = (array)get_option('ws_plugin__s2member_notices');
						$changed = FALSE;

						foreach($notices as $notice_key => $notice)
							if(is_array($notice) && !empty($notice['notice']) && (strpos((string)$notice['notice'], $marker) !== FALSE || strpos((string)$notice['notice'], $legacy_message) !== FALSE))
							{
								unset($notices[$notice_key]);
								$changed = TRUE;
							}

						if($changed)
							update_option('ws_plugin__s2member_notices', array_values($notices));
					}

				/**
				 * Stores a PayPal Checkout webhook id into ws_plugin__s2member_options for the current env.
				 *
				 * @since 260115
				 *
				 * @param string $webhook_id Webhook id returned by PayPal.
				 *
				 * @return void
				 */
				protected static function paypal_checkout_webhook_store_id($webhook_id)
					{
						//260820.0427 Preserve the selected environment before option normalization resets the global Checkout environment.
						$is_sandbox = self::paypal_checkout_is_sandbox();

						$options = get_option('ws_plugin__s2member_options');
						if(!is_array($options))
							$options = array();

						if($is_sandbox)
							$options['paypal_checkout_sandbox_webhook_id'] = (string)$webhook_id;
						else
							$options['paypal_checkout_webhook_id'] = (string)$webhook_id;

						$options = ws_plugin__s2member_configure_options_and_their_defaults($options);

						update_option('ws_plugin__s2member_options', $options).((is_multisite() && is_main_site()) ? update_site_option('ws_plugin__s2member_options', $options) : NULL);

						if($is_sandbox)
							$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_checkout_sandbox_webhook_id"] = (string)$webhook_id;
						else
							$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_checkout_webhook_id"] = (string)$webhook_id;

						//260824.0507 A successful create/update or no-change verification resolves any queued upgrade warning for this environment.
						self::paypal_checkout_webhook_upgrade_notice_clear($is_sandbox ? 'sandbox' : 'live');
					}
			}
	}
