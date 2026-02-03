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

												if(!empty($postvars["charset"]) && function_exists("mb_convert_encoding"))
													{
														foreach($postvars as &$value) // Convert to UTF-8 encoding.
															$value = @mb_convert_encoding($value, "UTF-8", (($postvars["charset"] === "gb2312") ? "GBK" : $postvars["charset"]));
													}
												return apply_filters("ws_plugin__s2member_paypal_postvars", $postvars, get_defined_vars());
											}
										else return false;
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

										if(!empty($postvars["charset"]) && function_exists("mb_convert_encoding"))
											{
												foreach($postvars as &$value) // Convert to UTF-8 encoding.
													$value = @mb_convert_encoding($value, "UTF-8", (($postvars["charset"] === "gb2312") ? "GBK" : $postvars["charset"]));
											}
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

						$creds     = self::paypal_checkout_creds();
						$client_id = (string)$creds['client_id'];
						$secret    = (string)$creds['secret'];

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
								'ppco'    => 'oauth',
								'event'   => 'token_failed',
								'env'     => self::paypal_checkout_is_sandbox() ? 'sandbox' : 'live',
								'url'     => $url,
								'code'    => !empty($r['code']) ? (int)$r['code'] : 0,
								'message' => !empty($r['message']) ? (string)$r['message'] : '',
								'body'    => !empty($r['body']) ? $r['body'] : '',
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

						c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
							'ppco'  => 'checkout',
							'event' => $ok ? 'creds_test_ok' : 'creds_test_failed',
							'env'   => $env,
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
							'ppco'    => 'checkout',
							'event'   => 'cleared_cache',
							'env'     => $env,
							'cred_id' => $cred_id,
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
							$args['body'] = is_string($body) ? $body : wp_json_encode($body);

						$r = c_ws_plugin__s2member_utils_urls::remote($url, false, $args, true);

						if(!is_array($r))
							$r = array('code' => 0, 'message' => 'request_failed', 'headers' => array(), 'body' => '');

						c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
								'ppco'    => 'api_request',
								'method'  => $method,
								'path'    => $path,
								'code'    => !empty($r['code']) ? (int)$r['code'] : 0,
								'message' => !empty($r['message']) ? (string)$r['message'] : '',
								'body'    => !empty($r['body']) ? $r['body'] : '',
							));

						return $r;
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

						$purchase_unit = array(
							'invoice_id' => $invoice,
							'amount'     => array(
								'currency_code' => $cc,
								'value'         => $amount,
								'breakdown'     => array(
									'item_total' => array(
										'currency_code' => $cc,
										'value'         => $amount,
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
										'value'         => $amount,
									),
								),
							),
						);

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

						$r = self::paypal_checkout_api_request('POST', '/v2/checkout/orders', $body, $headers);

						$data = array();
						if(!empty($r['body']) && is_string($r['body']))
							$data = json_decode($r['body'], true);

						return is_array($data) ? $data : array();
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
							return array();

						// Idempotency: stable per order capture retries.
						$headers = array(
							'PayPal-Request-Id' => 's2m-ppco-cap-'.md5($order_id),
						);

						$r = self::paypal_checkout_api_request('POST', '/v2/checkout/orders/'.$order_id.'/capture', (object)array(), $headers);

						$data = array();
						if(!empty($r['body']) && is_string($r['body']))
							$data = json_decode($r['body'], true);

						return is_array($data) ? $data : array();
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

						$rrt = !empty($token['rrt']) ? (int)$token['rrt'] : 0;
						$rra = isset($token['rra']) ? (int)$token['rra'] : 1;

						// rrt/rra are only meaningful when rr="1" (recurring).
						if($rr !== '1')
						{
							$rrt = 0;
							$rra = 0;
						}

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

						// rrt = number of payments (limited recurring). Only applies to rr="1".
						if($rr === '1' && $rrt > 0)
							$regular_total_cycles = min(999, max(1, (int)$rrt));
						else if($rr === '0')
							$regular_total_cycles = 1;

						$payment_failure_threshold = ($rr === '1' && $rra) ? 2 : 1;

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
								'ppco'    => 'plan',
								'event'   => 'plan_create_failed',
								'env'     => $env,
								'plan_key'=> $plan_key,
								'code'    => !empty($r['code']) ? (int)$r['code'] : 0,
								'message' => !empty($r['message']) ? (string)$r['message'] : '',
								'body'    => !empty($r['body']) ? (string)$r['body'] : '',
								'request' => $body,
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
							'ppco'    => 'plan',
							'event'   => 'plan_cached',
							'env'     => $env,
							'cred_id' => $cred_id,
							'plan_key'=> $plan_key,
							'plan_id' => $plan_id,
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
								'ppco'    => 'product',
								'event'   => 'product_create_failed',
								'env'     => $env,
								'code'    => !empty($r['code']) ? (int)$r['code'] : 0,
								'message' => !empty($r['message']) ? (string)$r['message'] : '',
								'body'    => !empty($r['body']) ? (string)$r['body'] : '',
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
							'ppco'    => 'product',
							'event'   => 'product_cached',
							'env'     => $env,
							'cred_id' => $cred_id,
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
						$webhook_id = self::paypal_checkout_webhook_id();
						if(!$webhook_id)
							return false;

						$tx_id   = !empty($headers['paypal-transmission-id']) ? $headers['paypal-transmission-id'] : '';
						$tx_time = !empty($headers['paypal-transmission-time']) ? $headers['paypal-transmission-time'] : '';
						$tx_sig  = !empty($headers['paypal-transmission-sig']) ? $headers['paypal-transmission-sig'] : '';
						$cert    = !empty($headers['paypal-cert-url']) ? $headers['paypal-cert-url'] : '';
						$algo    = !empty($headers['paypal-auth-algo']) ? $headers['paypal-auth-algo'] : '';

						if(!$tx_id || !$tx_time || !$tx_sig || !$cert || !$algo)
							return false;

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
							return false;

						$data = json_decode($r['body'], true);
						return !empty($data['verification_status']) && $data['verification_status'] === 'SUCCESS';
					}

				/**
				 * Returns the PayPal Checkout webhook event names processed by s2Member.
				 *
				 * These events are used for:
				 * - Recurring payment bookkeeping (completed payments).
				 * - Subscription lifecycle changes (cancel/suspend/expire/payment failed).
				 *
				 * @since 260115
				 *
				 * @return array<string> Event type names.
				 */
				public static function paypal_checkout_webhook_event_names()
					{
						return array(
							'BILLING.SUBSCRIPTION.CANCELLED',
							'BILLING.SUBSCRIPTION.SUSPENDED',
							'BILLING.SUBSCRIPTION.EXPIRED',
							'BILLING.SUBSCRIPTION.PAYMENT.FAILED',

							'PAYMENT.SALE.COMPLETED',
							'PAYMENT.CAPTURE.COMPLETED',
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
				 * @param string $env 'live' or 'sandbox'. Defaults to 'live'.
				 *
				 * @return array Result array on success with keys:
				 *               - id (string) webhook id
				 *               - op (string) 'created'|'updated'
				 *               - env (string) 'live'|'sandbox'
				 *              Empty array on failure.
				 */
				public static function paypal_checkout_webhook_upsert($env = '')
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
									'ppco'  => 'webhook',
									'event' => 'updated_webhook',
									'env'   => $env,
									'id'    => $existing_id,
									'url'   => $url,
									'code'  => (int)$r['code'],
								));

								$GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_sandbox'] = $orig_sandbox ? '1' : '0';
								return array('id' => $existing_id, 'op' => 'updated', 'env' => $env);
							}

							c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
								'ppco'    => 'webhook',
								'event'   => 'update_webhook_failed',
								'env'     => $env,
								'id'      => $existing_id,
								'url'     => $url,
								'code'    => !empty($r['code']) ? (int)$r['code'] : 0,
								'message' => !empty($r['message']) ? (string)$r['message'] : '',
								'body'    => !empty($r['body']) ? (string)$r['body'] : '',
							));
						}

						$body = array(
							'url'         => $url,
							'event_types' => $event_types,
						);
						$r = self::paypal_checkout_api_request('POST', '/v1/notifications/webhooks', $body);

						$id = '';
						if(!empty($r['code']) && (int)$r['code'] === 201 && !empty($r['body']))
						{
							$data = json_decode($r['body'], true);
							if(!empty($data['id']))
								$id = (string)$data['id'];
						}

						if($id)
						{
							self::paypal_checkout_webhook_store_id($id);

							c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
								'ppco'  => 'webhook',
								'event' => 'created_webhook',
								'env'   => $env,
								'id'    => $id,
								'url'   => $url,
								'code'  => (int)$r['code'],
							));

							$GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_sandbox'] = $orig_sandbox ? '1' : '0';
							return array('id' => $id, 'op' => 'created', 'env' => $env);
						}

						c_ws_plugin__s2member_utils_logs::log_entry('paypal-checkout', array(
							'ppco'    => 'webhook',
							'event'   => 'create_webhook_failed',
							'env'     => $env,
							'url'     => $url,
							'code'    => !empty($r['code']) ? (int)$r['code'] : 0,
							'message' => !empty($r['message']) ? (string)$r['message'] : '',
							'body'    => !empty($r['body']) ? (string)$r['body'] : '',
						));

						$GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_checkout_sandbox'] = $orig_sandbox ? '1' : '0';
						return array();
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
						$options = get_option('ws_plugin__s2member_options');
						if(!is_array($options))
							$options = array();

						if(self::paypal_checkout_is_sandbox())
							$options['paypal_checkout_sandbox_webhook_id'] = (string)$webhook_id;
						else
							$options['paypal_checkout_webhook_id'] = (string)$webhook_id;

						$options = ws_plugin__s2member_configure_options_and_their_defaults($options);

						update_option('ws_plugin__s2member_options', $options).((is_multisite() && is_main_site()) ? update_site_option('ws_plugin__s2member_options', $options) : NULL);

						if(self::paypal_checkout_is_sandbox())
							$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_checkout_sandbox_webhook_id"] = (string)$webhook_id;
						else
							$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_checkout_webhook_id"] = (string)$webhook_id;
					}
			}
	}
