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
				//261114.2134 Preserve the HMAC v2 rejection reason for the HTTP Notify wrapper; never infer fulfillment from an unaccepted message.
				private static $paypal_proxy_v2_rejection_status = 0;
				private static $paypal_proxy_v2_active_claim = null;
				private static $paypal_proxy_v2_completed_replay = false;
				//261117.0357 Only a trusted in-process Notify invocation may bypass external proxy credentials.
				private static $paypal_proxy_internal_dispatch = false;

				/**
				 * Return the last v2 request's rejection status (403 invalid, 409 replay, 0 otherwise).
				 * A status is not a fulfillment result; it must not be used to reopen replay claims.
				 *
				 * @since 261114.2134
				 * @return int
				 */
				public static function paypal_proxy_v2_rejection_status()
					{
						return self::$paypal_proxy_v2_rejection_status;
					}
				/**
				 * Identify an identical signed message already completed by a previous local Notify request.
				 *
				 * @since 261116.0327
				 * @return bool
				 */
				public static function paypal_proxy_v2_completed_replay()
					{
						return self::$paypal_proxy_v2_completed_replay;
					}

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
						//261116.0327 Never inherit a previous parsing attempt's replay or completion state.
						self::$paypal_proxy_v2_rejection_status = 0;
						self::$paypal_proxy_v2_active_claim = null;
						self::$paypal_proxy_v2_completed_replay = false;
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

										//261110.1954 `proxy_verified` is an internal authentication result, never a PayPal-supplied field.
										unset($postvars["proxy_verified"]);
										$postback = $postvars; // Copy.
										$postback["cmd"] = "_notify-validate";

										$postvars = self::paypal_postvars_back_compat($postvars);
										$postvars = c_ws_plugin__s2member_utils_strings::trim_deep($postvars);

										$postvars = self::paypal_postvars_utf8($postvars);
										$endpoint = ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_sandbox"]) ? "www.sandbox.paypal.com" : "www.paypal.com";

										//260927.2250 Browser PayPal Returns must use the transaction-bound Checkout handoff above; never let the reusable server-to-server proxy credential authenticate them.
										if(!empty($_GET["s2member_paypal_return"]) && !empty($_REQUEST["s2member_paypal_proxy"]) && is_string($_REQUEST["s2member_paypal_proxy"]) && $_REQUEST["s2member_paypal_proxy"] === "paypal")
											return false;

										//261117.0357 Trusted in-process Notify has no external bearer credential; this private guard is never set by HTTP input.
										else if(self::$paypal_proxy_internal_dispatch && count($_GET) === 1 && isset($_GET['s2member_paypal_notify'])
										&& isset($_POST['s2member_paypal_proxy']) && is_string($_POST['s2member_paypal_proxy']) && $_POST['s2member_paypal_proxy'] !== ''
										&& isset($_POST['s2member_paypal_proxy_verification']) && $_POST['s2member_paypal_proxy_verification'] === '')
											return apply_filters('ws_plugin__s2member_paypal_postvars', array_merge($postvars, array('proxy_verified' => $_POST['s2member_paypal_proxy'])), get_defined_vars());

										//261110.2320 v2 credentials authenticate every POST field, including the gateway, and are never downgraded to the legacy static key on failure.
										else if(!empty($_POST['s2member_paypal_proxy_verification']) && is_string($_POST['s2member_paypal_proxy_verification']) && strpos($_POST['s2member_paypal_proxy_verification'], 'v2.') === 0)
											{
												//261114.2134 Mark rejected signed-proxy attempts explicitly; failed signature and unsigned GET overrides remain forbidden.
												self::$paypal_proxy_v2_rejection_status = 403;
												// Only the Notify entrypoint may be in the query: unsigned GET fields must not override signed POST fields.
												if(count($_GET) !== 1 || !isset($_GET['s2member_paypal_notify']))
														return false;

												// WordPress slash-escapes incoming POST values; verify the exact unslashed data that downstream handlers will process.
												$verified_postvars = stripslashes_deep($_POST);
												if(!self::paypal_proxy_v2_verify($verified_postvars))
													return false;
												//261117.0315 Retain authenticated sender metadata for the replay namespace before stripping internal fields from fulfillment.
												$signed_postvars = $verified_postvars;
												$verified_signature = $verified_postvars['s2member_paypal_proxy_verification'];
												foreach($verified_postvars as $key => $value)
													if(preg_match('/^s2member_/', $key))
														unset($verified_postvars[$key]);
												unset($verified_postvars['proxy_verified']);
												$verified_postvars = self::paypal_postvars_utf8(c_ws_plugin__s2member_utils_strings::trim_deep(self::paypal_postvars_back_compat($verified_postvars)));
												if($verified_postvars !== $postvars)
													return false;

												//261111.0003 Claim the signed message ID only after both HMAC and normalized dispatch payload match; duplicate requests must stop before membership side effects.
													if(!self::paypal_proxy_v2_claim($verified_signature, $signed_postvars))
													{
														//261116.0327 Only an identical, durably completed replay is safe to acknowledge without redispatch.
														self::$paypal_proxy_v2_rejection_status = self::$paypal_proxy_v2_completed_replay ? 0 : 409;
														return false;
													}

												//261114.2134 Only a freshly authenticated, atomically claimed request can clear the rejection status.
												self::$paypal_proxy_v2_rejection_status = 0;
												return apply_filters('ws_plugin__s2member_paypal_postvars', array_merge($postvars, array('proxy_verified' => $_POST['s2member_paypal_proxy'])), get_defined_vars());
											}

										//261110.2320 TO-DO: Disable the published static key by default once built-in Pro senders and external integrations can migrate; administrators can use the allow-legacy filter in the interim.
										else if(!empty($_REQUEST["s2member_paypal_proxy"]) && is_string($_REQUEST["s2member_paypal_proxy"]) && !empty($_REQUEST["s2member_paypal_proxy_verification"]) && is_string($_REQUEST["s2member_paypal_proxy_verification"]) && is_string($proxy_verification_key = c_ws_plugin__s2member_paypal_utilities::paypal_proxy_key_gen()) && hash_equals($proxy_verification_key, $_REQUEST["s2member_paypal_proxy_verification"]))
										{
											//261117.0256 A disabled or retired static key must get an explicit HTTP 403; never fall through to PayPal verification and falsely acknowledge it.
											if(!apply_filters('ws_plugin__s2member_paypal_proxy_allow_legacy_key', true, get_defined_vars()) || !self::paypal_proxy_legacy_key_not_retired())
												{
												self::$paypal_proxy_v2_rejection_status = 403; //261117.0256 Reuse Notify's existing fail-closed HTTP status without introducing a new response channel.
												return false;
											}
											//261116.2220 Observe only authenticated legacy-key use; never record credentials, payloads, IPs, or user identifiers.
											self::paypal_proxy_legacy_note_use();
											return apply_filters("ws_plugin__s2member_paypal_postvars", array_merge($postvars, array("proxy_verified" => $_REQUEST["s2member_paypal_proxy"])), get_defined_vars());
										}

										//261117.0240 Signed GET returns are distinct from HMAC-v2 POSTs. A site may opt into a bounded lifetime after reviewing legacy return-link usage.
										else if(empty($_POST) && !empty($_GET["s2member_paypal_proxy"]) && !empty($_GET["s2member_paypal_proxy_verification"]) && self::paypal_proxy_signed_get_valid($_SERVER["REQUEST_URI"]))
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
				 * Verify the account receiving a native PayPal IPN against this site's merchant settings.
				 *
				 * @since 261110.2142
				 *
				 * @param array $postvars Verified PayPal notification fields.
				 *
				 * @return bool Whether the recipient is the configured PayPal merchant.
				 */
				public static function paypal_ipn_recipient_matches($postvars)
					{
						if(!is_array($postvars))
							return false;

						$merchant_id = (isset($GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_merchant_id']) && is_string($GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_merchant_id'])) ? trim($GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_merchant_id']) : '';
						$merchant_email = (isset($GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_business']) && is_string($GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_business'])) ? trim($GLOBALS['WS_PLUGIN__']['s2member']['o']['paypal_business']) : '';

						//261110.2142 Prefer the PayPal account ID when both sides provide one; email aliases may differ on the same merchant account.
						if(isset($postvars['receiver_id']) && !is_string($postvars['receiver_id']))
							return false;
						$receiver_id = isset($postvars['receiver_id']) ? trim($postvars['receiver_id']) : '';
						if($merchant_id !== '' && $receiver_id !== '')
							return hash_equals($merchant_id, $receiver_id);

						//261117.0357 Older VERIFIED PayPal IPNs may omit receiver_id and report the merchant's primary email instead of the configured secondary alias.
						// Accept only explicitly site-provisioned additional recipient emails; never derive allowed aliases from the IPN's business or payer fields.
						if(isset($postvars['receiver_email']) && !is_string($postvars['receiver_email']))
							return false;
						$receiver_email = isset($postvars['receiver_email']) ? trim($postvars['receiver_email']) : '';
						if($receiver_email === '')
							return false;
						if($merchant_email !== '' && strcasecmp($merchant_email, $receiver_email) === 0)
							return true;
						$aliases = apply_filters('ws_plugin__s2member_paypal_ipn_recipient_email_aliases', array());
						if(!is_array($aliases))
							return false;
						foreach($aliases as $alias)
							if(is_string($alias) && is_email(trim($alias)) && strcasecmp(trim($alias), $receiver_email) === 0)
								return true;
						return false;
					}
				/**
				 * Validate native PayPal payment completion before granting access; preserve nonpayment and proxy event semantics.
				 *
				 * @since 261110.2228
				 *
				 * @param array $postvars Authenticated gateway payment or subscription fields.
				 * @param string $payment_status_issues Legacy rejected statuses for other gateway events.
				 *
				 * @return bool Whether the event may proceed to the matching handler.
				 */
				public static function paypal_payment_status_allows_access($postvars, $payment_status_issues = '')
					{
						if(!is_array($postvars))
							return false;

						//261110.2228 PayPal's VERIFIED response authenticates an event, not receipt of a completed payment.
						if(empty($postvars['proxy_verified']) && !empty($postvars['txn_type']) && is_string($postvars['txn_type'])
						&& preg_match('/^(web_accept|subscr_payment|recurring_payment|merch_pmt)$/i', $postvars['txn_type']))
							return isset($postvars['payment_status']) && is_string($postvars['payment_status'])
								&& strcasecmp(trim($postvars['payment_status']), 'Completed') === 0;

						//261110.2228 Subscription signups may omit payment_status; existing authenticated proxy integrations use their own status conventions.
						if(empty($postvars['payment_status']))
							return true;
						return is_string($postvars['payment_status'])
							&& ($payment_status_issues === '' || !preg_match($payment_status_issues, $postvars['payment_status']));
					}
				/**
				 * Verify the complete host portion of s2Member's PayPal custom field, not just a matching prefix.
				 *
				 * @since 261110.2250
				 *
				 * @param string $custom Verified payment's custom value (host, optionally followed by pipe-delimited data).
				 * @param string $host Expected installation host, without a numeric port.
				 *
				 * @return bool Whether the custom value identifies the exact installation host.
				 */
				public static function paypal_custom_origin_matches($custom, $host)
					{
						if(!is_string($custom) || !is_string($host) || $host === '')
							return false;

						//261110.2250 Preserve the documented host|custom-fields format and custom values created with HTTP_HOST including a numeric port.
						$custom_parts = explode('|', $custom, 2);
						$custom_host = preg_replace('/:[0-9]+$/', '', $custom_parts[0]);
						return strcasecmp($custom_host, $host) === 0;
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
				 * Get an independent, persistent secret for signing external v2 proxy messages.
				 *
				 * @since 261111.0003
				 *
				 * @return string|bool Hex-encoded secret, or false when it cannot be securely loaded or stored.
				 */
				public static function paypal_proxy_v2_signing_key()
					{
						$option = 'ws_plugin__s2member_paypal_proxy_v2_signing_key';
						$fence_option = 'ws_plugin__s2member_paypal_proxy_v2_signing_key_fingerprint';
						$fence = get_option($fence_option, null);
						$key = get_option($option, null);
						if($key === null)
							{
								//261117.0019 Do not silently rotate a lost key once its durable fingerprint exists: old external signers and replay claims depend on it.
								if($fence !== null)
									return false;
								//261111.0003 Generate only on the server, once per site; add_option uses the database unique key to resolve concurrent first requests.
								try { $generated = bin2hex(random_bytes(32)); }
								catch(Exception $exception) { return false; }
								$key = add_option($option, $generated, '', 'no') ? $generated : get_option($option, null);
							}
						//261117.0019 Bind an existing or newly created signing key before first signed use; never treat a corrupt or conflicting marker as recoverable.
						if(!is_string($key) || !preg_match('/^[a-f0-9]{64}$/D', $key))
							return false;
						$expected = hash('sha256', $key);
						if($fence === null)
							$fence = add_option($fence_option, $expected, '', 'no') ? $expected : get_option($fence_option, null);
						return is_string($fence) && preg_match('/^[a-f0-9]{64}$/D', $fence) && hash_equals($expected, $fence) ? $key : false;
					}
				/**
				 * Read an existing HMAC signing key without initializing or changing the replay namespace.
				 * An older key without a marker remains inspectable; a changed or damaged marker must fail closed.
				 *
				 * @since 261117.0019
				 * @return string|bool Existing key, or false when missing, corrupt, or inconsistent.
				 */
				private static function paypal_proxy_v2_existing_signing_key()
					{
						$key = get_option('ws_plugin__s2member_paypal_proxy_v2_signing_key', null);
						if(!is_string($key) || !preg_match('/^[a-f0-9]{64}$/D', $key))
							return false;
						$fence = get_option('ws_plugin__s2member_paypal_proxy_v2_signing_key_fingerprint', null);
						if($fence === null)
							return $key; //261117.0019 Older valid signing keys predate the continuity marker; read-only audits must not create it.
						return is_string($fence) && preg_match('/^[a-f0-9]{64}$/D', $fence) && hash_equals(hash('sha256', $key), $fence) ? $key : false;
					}
				/**
				 * Resolve a gateway-scoped external sender's signing key, or the historical site-wide key.
				 * Sender credentials are provisioned server-side through a filter, not through signed requests.
				 * Each sender has a durable key fingerprint so changing one cannot silently reopen replay history.
				 *
				 * @since 261117.0315
				 * @param array $postvars Original unslashed signed fields, including gateway and optional sender.
				 * @param bool $read_only True for archived correlation, which must not initialize options.
				 * @return string|bool Signing key or false on invalid scope/configuration/continuity.
				 */
				private static function paypal_proxy_v2_key_for_postvars($postvars, $read_only = false)
					{
						if(!is_array($postvars))
							return false;
						if(!array_key_exists('s2member_paypal_proxy_sender', $postvars))
							return $read_only ? self::paypal_proxy_v2_existing_signing_key() : self::paypal_proxy_v2_signing_key();

						$sender = $postvars['s2member_paypal_proxy_sender'];
						$gateway = isset($postvars['s2member_paypal_proxy']) ? $postvars['s2member_paypal_proxy'] : null;
						if(!is_string($sender) || !preg_match('/^[a-z0-9][a-z0-9_-]{0,31}$/D', $sender) || !is_string($gateway) || $gateway === '')
							return false;
						$senders = apply_filters('ws_plugin__s2member_paypal_proxy_v2_sender_credentials', array());
						if(!is_array($senders) || !isset($senders[$sender]) || !is_array($senders[$sender]))
							return false;
						$credentials = $senders[$sender];
						if(!isset($credentials['key'], $credentials['gateways']) || !is_string($credentials['key'])
						|| !preg_match('/^[a-f0-9]{64}$/D', $credentials['key']) || !is_array($credentials['gateways'])
						|| !in_array($gateway, $credentials['gateways'], true))
							return false;

						//261117.0319 Refuse reused secrets: a compromised adapter must not be able to impersonate another sender or the site's unscoped signer.
						$site_key = self::paypal_proxy_v2_existing_signing_key();
						if(is_string($site_key) && hash_equals($site_key, $credentials['key']))
							return false;
						foreach($senders as $other_id => $other)
							if($other_id !== $sender && is_array($other) && isset($other['key']) && is_string($other['key']) && hash_equals($credentials['key'], $other['key']))
								return false;

						//261117.0315 Never grant one sender another gateway's authority; the sender ID and gateway are both bound into the signed body.
						$marker = 'ws_plugin__s2member_paypal_proxy_v2_sender_fingerprint_'.$sender;
						$fingerprint = hash('sha256', $credentials['key']);
						$stored = get_option($marker, null);
						if($stored === null && !$read_only)
							$stored = add_option($marker, $fingerprint, '', 'no') ? $fingerprint : get_option($marker, null);
						//261117.0315 A missing fingerprint on read-only inspection or a mismatched one on any path is unresolved key history, not permission to rotate.
						return is_string($stored) && preg_match('/^[a-f0-9]{64}$/D', $stored) && hash_equals($fingerprint, $stored) ? $credentials['key'] : false;
					}
				/**
				 * Bind a replay claim to its signer namespace without changing old unscoped claim identifiers.
				 *
				 * @since 261117.0315
				 * @param string $message_id Signed v2 message identifier.
				 * @param array $postvars Authenticated original POST fields.
				 * @param bool $read_only Whether claim correlation must avoid writes.
				 * @return string|bool Option name or false if sender credentials are unavailable.
				 */
				private static function paypal_proxy_v2_claim_name($message_id, $postvars, $read_only = false)
					{
						if(($key = self::paypal_proxy_v2_key_for_postvars($postvars, $read_only)) === false)
							return false;
						//261117.0315 Preserve existing unscoped claim names; never allow identical IDs from distinct scoped senders to share a receipt.
						$scope = array_key_exists('s2member_paypal_proxy_sender', $postvars) ? 'sender:'.$postvars['s2member_paypal_proxy_sender']."\n" : '';
						return 'ws_plugin__s2member_proxy_v2_seen_'.hash_hmac('sha256', $scope.$message_id, $key);
					}
				/**
				 * Build domain-separated HMAC input; sender identity cannot be detached from a verified payload.
				 *
				 * @since 261117.0315
				 * @return string Signed data, independent of the signing key.
				 */
				private static function paypal_proxy_v2_signed_bytes($timestamp, $message_id, $payload, $postvars)
					{
						if(array_key_exists('s2member_paypal_proxy_sender', $postvars))
							return "s2member_paypal_proxy_v2_sender\n".$postvars['s2member_paypal_proxy_sender']."\n".$timestamp."\n".$message_id."\n".$payload;
						return "s2member_paypal_proxy_v2\n".$timestamp."\n".$message_id."\n".$payload;
					}
				/**
				 * Validate a legacy signed GET proxy return, optionally enforcing a bounded link lifetime.
				 * Existing signed browser returns remain compatible until a site explicitly enables expiry.
				 * This is not an HMAC-v2 authentication or a replay/fulfillment guarantee.
				 *
				 * @since 261117.0240
				 * @param string $request_uri Original request URI including its signed query string.
				 * @return bool Whether the signed legacy return is acceptable under the configured policy.
				 */
				public static function paypal_proxy_signed_get_valid($request_uri)
					{
						if(!is_string($request_uri) || !c_ws_plugin__s2member_utils_urls::s2member_sig_ok($request_uri, false, false, 's2member_paypal_proxy_verification'))
							return false;

						//261117.0240 Zero retains previously issued long-lived return links; positive integer seconds opt into expiry. Invalid policies fail closed.
						$max_age = apply_filters('ws_plugin__s2member_paypal_proxy_signed_get_max_age', 0);
						if($max_age === 0)
							return true;
						if(!is_int($max_age) || $max_age < 1)
							return false;

						//261117.0240 Require exactly one canonical timestamp/signature pair in bounded mode; duplicates and ambiguous encodings cannot select a different lifetime.
						$query = parse_url($request_uri, PHP_URL_QUERY);
						if(!is_string($query) || preg_match_all('/(?:^|&)s2member_paypal_proxy_verification=([^&]*)/', $query, $tokens) !== 1
						|| !preg_match('/^([0-9]{10,12})-[a-f0-9]{32}$/D', $tokens[1][0], $matches))
							return false;
						$issued = (int)$matches[1];
						$now = time();
						//261117.0240 Permit one minute of clock skew for different web nodes, but never an unlimited future or past-dated return.
						return $issued <= $now + 60 && $now - $issued <= $max_age;
					}
				/**
				 * Canonicalize a v2 proxy POST body for a transaction-bound HMAC.
				 *
				 * @since 261110.2320
				 *
				 * @param array $postvars POST fields, including the gateway and optionally the verification field.
				 *
				 * @return string|bool Deterministic RFC3986 query encoding, else false for unsupported values.
				 */
				public static function paypal_proxy_v2_payload($postvars)
					{
						if(!is_array($postvars) || empty($postvars['s2member_paypal_proxy']) || !is_string($postvars['s2member_paypal_proxy']))
							return false;

						unset($postvars['s2member_paypal_proxy_verification']);
						$normalize = function($values) use (&$normalize)
							{
								if(!is_array($values))
									return false;
								$result = array();
								foreach($values as $key => $value)
									{
										if(is_array($value))
											{
												if(!$value || ($value = $normalize($value)) === false)
													return false;
											}
										else if(!is_scalar($value))
											return false;
										$result[$key] = is_array($value) ? $value : (string)$value;
									}
								ksort($result, SORT_STRING);
								return $result;
							};

						if(($postvars = $normalize($postvars)) === false)
							return false;
						return http_build_query($postvars, '', '&', PHP_QUERY_RFC3986);
					}
				/**
				 * Sign a complete v2 proxy POST using a unique message ID and independent site secret.
				 *
				 * @since 261110.2320
				 *
				 * @param array $postvars All unslashed POST fields except the signature (including s2member_paypal_proxy).
				 * @param int|null $timestamp Optional Unix timestamp; defaults to the current time.
				 * @param string|null $message_id Optional 128-bit lowercase hex message ID; defaults to a new cryptographic random value.
				 *
				 * @return string|bool v2 timestamped, message-bound HMAC, or false on invalid input.
				 */
				public static function paypal_proxy_v2_sign($postvars, $timestamp = null, $message_id = null)
					{
						if(($payload = self::paypal_proxy_v2_payload($postvars)) === false)
							return false;
						$timestamp = $timestamp === null ? time() : $timestamp;
						if(!is_int($timestamp) || $timestamp < 1000000000)
							return false;
						if($message_id === null)
							{
								try { $message_id = bin2hex(random_bytes(16)); }
								catch(Exception $exception) { return false; }
							}
						if(!is_string($message_id) || !preg_match('/^[a-f0-9]{32}$/D', $message_id) || ($key = self::paypal_proxy_v2_key_for_postvars($postvars)) === false)
							return false;

						//261117.0315 Scoped senders use a distinct HMAC domain; unscoped site signatures retain their original bytes for compatibility.
						$signature = hash_hmac('sha256', self::paypal_proxy_v2_signed_bytes($timestamp, $message_id, $payload, $postvars), $key);
						return 'v2.'.$timestamp.'.'.$message_id.'.'.$signature;
					}
				/**
				 * Validate the signature and age of a v2 proxy POST without consuming its message ID.
				 *
				 * @since 261110.2320
				 *
				 * @param array $postvars Received unslashed POST fields, including the signature and gateway.
				 *
				 * @return bool Whether the signed transaction fields are intact and timely; replay is checked separately at the HTTP boundary.
				 */
				public static function paypal_proxy_v2_verify($postvars)
					{
						if(!is_array($postvars) || !isset($postvars['s2member_paypal_proxy_verification']) || !is_string($postvars['s2member_paypal_proxy_verification'])
						|| !preg_match('/^v2\.([0-9]{10,12})\.([a-f0-9]{32})\.([a-f0-9]{64})$/D', $postvars['s2member_paypal_proxy_verification'], $matches))
							return false;

						//261117.0315 Default to old unscoped compatibility, but allow a site to reject site-wide v2 credentials after scoped senders migrate.
						if(!array_key_exists('s2member_paypal_proxy_sender', $postvars)
						&& !apply_filters('ws_plugin__s2member_paypal_proxy_v2_allow_unscoped', true))
							return false;
						$timestamp = (int)$matches[1];
						//261110.2320 Reject expired signatures and future clock skew; signing a payload is insufficient if an old envelope can be reused indefinitely.
						if($timestamp < time() - 300 || $timestamp > time() + 60)
							return false;
						$expected = self::paypal_proxy_v2_sign($postvars, $timestamp, $matches[2]);
						return is_string($expected) && hash_equals($expected, $postvars['s2member_paypal_proxy_verification']);
					}
				/**
				 * Atomically claim a signed v2 delivery, preserving unknown outcomes and recognizing completed duplicates.
				 *
				 * @since 261111.0003
				 * @param string $token Verified v2 token; the stored digest binds the original payload and gateway.
				 * @return bool True only for the first delivery of this message ID.
				 */
				private static function paypal_proxy_v2_claim($token, $postvars)
					{
						if(!is_string($token) || !preg_match('/^v2\.([0-9]{10,12})\.([a-f0-9]{32})\.([a-f0-9]{64})$/D', $token, $matches)
						|| ($option = self::paypal_proxy_v2_claim_name($matches[2], $postvars, true)) === false)
							return false;

						$expiry = (int)$matches[1] + 361;
						$digest = hash('sha256', $token);
						//261116.0327 Keep the numeric expiry prefix for the existing SQL garbage collector and old numeric claims.
						$pending = $expiry.'|processing|'.$digest;
						if(!add_option($option, $pending, '', 'no'))
							{
								$stored = get_option($option, false);
								self::$paypal_proxy_v2_completed_replay = is_string($stored)
									&& hash_equals($expiry.'|completed|'.$digest, $stored);
								return false;
							}

						self::$paypal_proxy_v2_active_claim = array($option, $pending, $expiry.'|completed|'.$digest);
						self::paypal_proxy_v2_prune_replay();
						return true;
					}
				/**
				 * Mark a locally accepted signed delivery complete without reopening interrupted claims.
				 *
				 * @since 261116.0327
				 * @return bool True when no signed claim exists or its exact record was durably completed.
				 */
				public static function paypal_proxy_v2_complete_claim()
					{
						if(self::$paypal_proxy_v2_active_claim === null)
							return true;
						list($option, $pending, $completed) = self::$paypal_proxy_v2_active_claim;
						global $wpdb;
						//261116.0327 Compare-and-swap prevents a stale worker from completing a replaced or changed receipt.
						$changed = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s AND option_value = %s", $completed, $option, $pending));
						wp_cache_delete($option, 'options');
						if($changed !== 1 || get_option($option, false) !== $completed)
							return false;
						self::$paypal_proxy_v2_active_claim = null;
						return true;
					}
				/**
				 * Remove expired replay records without depending on WP-Cron or persistent object-cache atomicity.
				 *
				 * @since 261111.0003
				 *
				 * @return void
				 */
				private static function paypal_proxy_v2_prune_replay()
					{
						//261111.0003 A transient only throttles cleanup. Replay rejection is solely the durable, uniquely indexed add_option record above.
						$throttle = 'ws_plugin__s2member_proxy_v2_gc_throttle';
						if(get_transient($throttle))
							return;
						set_transient($throttle, 1, 30);

						global $wpdb;
						$prefix = 'ws_plugin__s2member_proxy_v2_seen_';
						//261116.2126 Preserve interrupted processing and historical numeric claims for explicit reconciliation, even after the signing window expires.
						//261116.2126 Select only expired completed receipts so unresolved claims cannot starve the bounded cleanup batch.
						$expired = $wpdb->get_results($wpdb->prepare("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value LIKE %s AND CAST(option_value AS UNSIGNED) < %d LIMIT 200", $wpdb->esc_like($prefix).'%', '%|completed|%', time()), ARRAY_A);
						foreach((array)$expired as $record)
							{
							if(!is_array($record) || !isset($record['option_name'], $record['option_value']) || !is_string($record['option_name']) || !is_string($record['option_value'])
							|| strpos($record['option_name'], $prefix) !== 0 || !preg_match('/^[0-9]{10,12}\|completed\|[a-f0-9]{64}$/D', $record['option_value']))
								continue;
							//261116.2126 A concurrent update must not be deleted using a stale cleanup snapshot.
							$deleted = $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name = %s AND BINARY option_value = BINARY %s", $record['option_name'], $record['option_value']));
							if($deleted === 1)
								wp_cache_delete($record['option_name'], 'options');
							}
					}
				/**
				 * Inspect one signed delivery claim without changing its state or generating a missing signing key.
				 * A claim records authentication/replay state, NEVER successful payment or external side effects.
				 *
				 * @since 261116.2220
				 * @param string $message_id The original 32-character lowercase hexadecimal v2 message ID.
				 * @return array|bool|WP_Error Read-only classification; false for invalid input or an unavailable signing key.
				 */
				public static function paypal_proxy_v2_claim_inspect($message_id)
					{
						if(!is_string($message_id) || !preg_match('/^[a-f0-9]{32}$/D', $message_id))
							return false;
						//261116.2220 The inspection path must never generate or replace the signing secret: doing so would change the claim namespace.
						//261117.0019 An audit must not create a key or misclassify old claims under an altered signing secret.
						if(($key = self::paypal_proxy_v2_existing_signing_key()) === false)
							return false;
						$option = 'ws_plugin__s2member_proxy_v2_seen_'.hash_hmac('sha256', $message_id, $key);
						global $wpdb;
						//261116.2220 Query durable storage rather than cached get_option; never call replay pruning during an audit.
						$stored = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s LIMIT 1", $option));
						if($wpdb->last_error !== '')
							return new WP_Error('s2m_proxy_v2_inspect_sql', 'The signed-delivery claim could not be inspected.');
						if($stored === null)
							return array('state' => 'absent', 'expires' => 0, 'expired' => false);
						if(is_string($stored) && preg_match('/^([0-9]{10,12})\|(processing|completed)\|[a-f0-9]{64}$/D', $stored, $matches))
							{
								$expires = (int)$matches[1];
								return array('state' => $matches[2], 'expires' => $expires, 'expired' => $expires < time());
							}
						//261116.2220 Older numeric claims and malformed records are unresolved evidence, not authorization to reset/replay.
						return array('state' => is_string($stored) && preg_match('/^[0-9]{10,12}$/D', $stored) ? 'legacy_unknown' : 'invalid_record', 'expires' => 0, 'expired' => false);
					}
				/**
				 * Verify an archived signed body and correlate its exact claim with an existing payment receipt.
				 * This is for operator diagnostics: expired signatures may be inspected but NEVER accepted for delivery.
				 * A local receipt cannot independently prove provider payment, external email, or hook delivery.
				 *
				 * @since 261116.2325
				 * @param array $postvars Original unslashed signed POST values, including verification token.
				 * @return array|bool|WP_Error Read-only evidence classification; false for invalid signatures/input.
				 */
				public static function paypal_proxy_v2_correlate_signed_delivery($postvars)
					{
						if(!is_array($postvars) || empty($postvars['s2member_paypal_proxy_verification']) || !is_string($postvars['s2member_paypal_proxy_verification'])
						|| !preg_match('/^v2\.([0-9]{10,12})\.([a-f0-9]{32})\.([a-f0-9]{64})$/D', $postvars['s2member_paypal_proxy_verification'], $matches))
							return false;
						//261116.2325 Never generate a missing signing key during an audit; a new key would change the namespace of historical claims.
						$key = self::paypal_proxy_v2_key_for_postvars($postvars, true);
						$payload = self::paypal_proxy_v2_payload($postvars);
						//261117.0315 Read-only correlation uses the same scoped key/domain and replay namespace as the original delivery, without rotating secrets.
						if($key === false || $payload === false)
								return false;
						$expected = 'v2.'.(int)$matches[1].'.'.$matches[2].'.'.hash_hmac('sha256', self::paypal_proxy_v2_signed_bytes((int)$matches[1], $matches[2], $payload, $postvars), $key);
						if(!hash_equals($expected, $postvars['s2member_paypal_proxy_verification']))
							return false;

						$option = self::paypal_proxy_v2_claim_name($matches[2], $postvars, true);
						if($option === false)
							return false;
						global $wpdb;
						//261116.2325 Bind the claim to the token digest, not merely a reused message ID; use durable SQL reads without cleanup or replay.
						$stored = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s LIMIT 1", $option));
						if($wpdb->last_error !== '')
							return new WP_Error('s2m_proxy_v2_correlation_sql', 'Signed-delivery correlation could not read the claim.');
						$state = 'absent';
						$binding = 'not_proven';
						if(is_string($stored) && preg_match('/^[0-9]{10,12}\|(processing|completed)\|([a-f0-9]{64})$/D', $stored, $stored_matches))
							{
							$state = $stored_matches[1];
							$binding = hash_equals($stored_matches[2], hash('sha256', $postvars['s2member_paypal_proxy_verification'])) ? 'exact_token' : 'different_token';
							}
						else if(is_string($stored))
							$state = preg_match('/^[0-9]{10,12}$/D', $stored) ? 'legacy_unknown' : 'invalid_record';

						$result = array('claim' => $state, 'binding' => $binding, 'receipt' => 'not_checked', 'origin' => 'unknown');
						if($binding !== 'exact_token')
							return $result;
						$gateway = isset($postvars['s2member_paypal_proxy']) ? $postvars['s2member_paypal_proxy'] : null;
						$transaction = isset($postvars['txn_id']) ? $postvars['txn_id'] : null;
						if(!is_string($gateway) || !is_string($transaction))
							return $result;
						$merchant = c_ws_plugin__s2member_payment_events::trusted_merchant($gateway);
						if(is_wp_error($merchant))
							return $result; // A changed/unknown merchant scope cannot be inferred from posted data.
						$name = c_ws_plugin__s2member_payment_events::option_name($gateway, $merchant, $transaction, 'payment');
						if($name === false)
							return $result;
						$receipt = c_ws_plugin__s2member_payment_events::read($name);
						if(is_wp_error($receipt))
							return $receipt; // Do not misreport a corrupt or unreadable receipt as absent.
						$result['receipt'] = $receipt === false ? 'absent' : $receipt['status'];
						if(is_array($receipt) && $receipt['gateway'] === strtolower(trim($gateway)) && $receipt['event'] === 'payment')
							$result['origin'] = isset($receipt['replay_origin']) ? 'authoritative' : 'legacy_shadow';
						return $result;
					}
				/**
				 * Allow the published legacy proxy key only before an operator explicitly retires it.
				 * A retirement option row, regardless of value, disables static-key authentication.
				 * Its absence preserves the historical soft rollout, including the existing allow-legacy filter.
				 * This does not affect signed GET browser returns, HMAC-v2 POST, or trusted PHP dispatch.
				 *
				 * @since 261117.0256
				 * @return bool True only while no retirement marker exists and SQL can verify that fact.
				 */
				public static function paypal_proxy_legacy_key_not_retired()
					{
						global $wpdb;
						//261117.0256 Use durable SQL, not potentially stale option caches: a retired static credential must never be reaccepted by an old cache entry.
						$marker = 'ws_plugin__s2member_paypal_proxy_legacy_key_retired';
						$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name = %s", $marker));
						//261117.0256 An unreadable/malformed result is not evidence that the legacy key is safe to accept.
						return $wpdb->last_error === '' && ($count === 0 || $count === '0');
					}
				/**
				 * Observe legacy static-key usage without storing secrets or transaction/customer data.
				 * These timestamps are approximate operational indicators, not an audit-grade event ledger.
				 *
				 * @since 261116.2220
				 * @return void
				 */
				private static function paypal_proxy_legacy_note_use()
					{
						$now = time();
						//261116.2220 A unique first-use option survives races; last-use writes are throttled to one per hour per site.
						add_option('ws_plugin__s2member_paypal_proxy_legacy_first_used', $now, '', 'no');
						$last = get_option('ws_plugin__s2member_paypal_proxy_legacy_last_used', false);
						if(!is_numeric($last) || $now - (int)$last >= HOUR_IN_SECONDS)
							update_option('ws_plugin__s2member_paypal_proxy_legacy_last_used', $now, false);
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
							//260909.0217 Normalize host selection so proxy verification behaves consistently across different server configurations.
							$site_host    = preg_replace("/\:[0-9]+$/", "", strtolower((string)parse_url(home_url('/'), PHP_URL_HOST)));
							$request_host = (!empty($_SERVER["HTTP_HOST"]) && is_string($_SERVER["HTTP_HOST"])) ? preg_replace("/\:[0-9]+$/", "", strtolower($_SERVER["HTTP_HOST"])) : '';
							$host         = ($GLOBALS['WS_PLUGIN__']['s2member']['o']['skip_ipn_domain_validation']) ? $site_host : $request_host;
							$host         = strlen($host) ? $host : $site_host;
							$host         = strlen($host) ? $host : 's2member-paypal-proxy'; //260909.0338 Provide a stable final fallback when no usable site host is available.
							$key          = md5(c_ws_plugin__s2member_utils_encryption::xencrypt($host, false, false));
						}

						return apply_filters("ws_plugin__s2member_paypal_proxy_key_gen", $key, get_defined_vars());
					}
				/**
				 * Derive a stable purchase-event identity from the authenticated gateway and transaction ID.
				 *
				 * @since 261111.0100
				 *
				 * @param array  $postvars Authenticated notification fields, including the verified gateway origin.
				 * @param string $purchase_type Purchase handler family: sp or ccaps.
				 *
				 * @return string|bool SHA-256 event identity, or false when required identity is missing.
				 */
				public static function paypal_purchase_event_key($postvars, $purchase_type)
					{
						if(!is_array($postvars) || !in_array($purchase_type, array('sp', 'ccaps'), TRUE)
						|| !isset($postvars['subscr_gateway'], $postvars['txn_id'])
						|| !is_string($postvars['subscr_gateway']) || !is_string($postvars['txn_id']))
							return false;

						$gateway = strtolower(trim($postvars['subscr_gateway']));
						$txn_id = trim($postvars['txn_id']);
						if($gateway === '' || $txn_id === '')
							return false;

						//261111.0100 Use length-delimited fields; mutable descriptions, invoice fields, and delivery metadata must not change purchase identity.
						return hash('sha256', $purchase_type.'|'.strlen($gateway).':'.$gateway.'|'.strlen($txn_id).':'.$txn_id);
					}
				/**
				 * Read a completed purchase marker directly from the database, bypassing potentially stale option caches.
				 *
				 * @since 261111.0125
				 *
				 * @param string $done_option Completed purchase option name.
				 * @param int    $done_ttl    Completed marker lifetime in seconds.
				 *
				 * @return bool Whether the purchase was completed, or the database cannot safely determine that status.
				 */
				private static function paypal_purchase_is_done_fresh($done_option, $done_ttl)
					{
						global $wpdb;

						//261111.0125 Fail closed if the completed marker cannot be read. Cached absence could permit double fulfillment after a concurrent request finishes.
						$done_time = $wpdb->get_var($wpdb->prepare("SELECT `option_value` FROM `{$wpdb->options}` WHERE `option_name` = %s LIMIT 1", $done_option));
						//261116.0440 Unknown completion data must not permit a second fulfillment attempt.
						if($wpdb->last_error !== '' || ($done_time !== NULL && !is_numeric($done_time)))
							return TRUE;

						return is_numeric($done_time) && (int)$done_time > 0 && ((int)$done_ttl <= 0 || time() - (int)$done_time < (int)$done_ttl);
					}
				/**
				 * Confirm a completed purchase against the database, never a stale option cache.
				 *
				 * @since 261116.0439
				 * @return bool
				 */
				public static function paypal_purchase_done_confirmed($done_option, $done_ttl)
					{
						global $wpdb;
						if(!is_string($done_option) || $done_option === '')
							return FALSE;
						$time = $wpdb->get_var($wpdb->prepare("SELECT `option_value` FROM `{$wpdb->options}` WHERE `option_name` = %s LIMIT 1", $done_option));
						return $wpdb->last_error === '' && is_numeric($time) && (int)$time > 0
							&& ((int)$done_ttl <= 0 || time() - (int)$time < (int)$done_ttl);
					}
				/**
				 * Persist a pre-effect checkpoint on the owned purchase lock.
				 *
				 * @since 261116.0439
				 * @return bool
				 */
				public static function paypal_purchase_lock_mark_started($lock_option, $token)
					{
						global $wpdb;
						if(!is_string($lock_option) || $lock_option === '' || !is_string($token) || !preg_match('/^[a-f0-9]{32}$/D', $token))
							return FALSE;
						$stored = $wpdb->get_var($wpdb->prepare("SELECT `option_value` FROM `{$wpdb->options}` WHERE `option_name` = %s LIMIT 1", $lock_option));
						if($wpdb->last_error !== '' || $stored === NULL)
							return FALSE;
						$lock = maybe_unserialize($stored);
						if(!is_array($lock) || empty($lock['token']) || !hash_equals((string)$lock['token'], $token))
							return FALSE;
						if(!empty($lock['started']))
							return TRUE;
						$lock['started'] = time();
						$new_value = maybe_serialize($lock);
						//261116.0439 Compare against the exact owned row so a stale worker cannot establish a checkpoint.
						if($wpdb->update($wpdb->options, array('option_value' => $new_value),
							array('option_name' => $lock_option, 'option_value' => $stored), array('%s'), array('%s', '%s')) !== 1)
							return FALSE;
						self::paypal_purchase_lock_cache_clear($lock_option);
						$fresh = $wpdb->get_var($wpdb->prepare("SELECT `option_value` FROM `{$wpdb->options}` WHERE `option_name` = %s LIMIT 1", $lock_option));
						return $wpdb->last_error === '' && $fresh === $new_value;
					}
				/**
				 * Write and independently verify a purchase's completion marker.
				 *
				 * @since 261116.0439
				 * @return bool
				 */
				public static function paypal_purchase_complete($done_option, $done_ttl)
					{
						self::dedupe_done_mark($done_option);
						return self::paypal_purchase_done_confirmed($done_option, $done_ttl);
					}
				/**
				 * Invalidate a purchase lock's option cache after a direct SQL change.
				 *
				 * @since 261111.0125
				 *
				 * @param string $lock_option Per-purchase lock option name.
				 *
				 * @return void
				 */
				private static function paypal_purchase_lock_cache_clear($lock_option)
					{
						wp_cache_delete($lock_option, 'options');
						$notoptions = wp_cache_get('notoptions', 'options');
						if(is_array($notoptions) && isset($notoptions[$lock_option]))
							{
								unset($notoptions[$lock_option]);
								wp_cache_set('notoptions', $notoptions, 'options');
							}
					}
				/**
				 * Claim exclusive processing of a purchase with an owner token and an uncached completion recheck.
				 *
				 * @since 261111.0125
				 *
				 * @param string $lock_option Per-purchase lock option name.
				 * @param string $done_option Per-purchase completion marker name.
				 * @param int    $lock_timeout Maximum lock age in seconds.
				 * @param int    $done_ttl Completed marker lifetime in seconds.
				 *
				 * @return string|bool Owner token if acquired, else FALSE.
				 */
				public static function paypal_purchase_lock_claim($lock_option, $done_option, $lock_timeout = 900, $done_ttl = 315569260)
					{
						global $wpdb;

						if(!is_string($lock_option) || $lock_option === '' || !is_string($done_option) || $done_option === '')
							return FALSE;

						$lock_timeout = max(30, abs((int)$lock_timeout));
						if(self::paypal_purchase_is_done_fresh($done_option, $done_ttl))
							return FALSE;

						$token = bin2hex(random_bytes(16));
						$lock = array('token' => $token, 'time' => time());
						//261111.0125 Avoid add_option(): overlapping calls can take WordPress's ON DUPLICATE KEY UPDATE path and overwrite a live lock.
						$insert = $wpdb->prepare("INSERT IGNORE INTO `{$wpdb->options}` (`option_name`, `option_value`, `autoload`) VALUES (%s, %s, %s)", $lock_option, maybe_serialize($lock), 'no');
						$acquired = ($wpdb->query($insert) === 1);
						if(!$acquired)
							{
								//261111.0125 Read the current lock from the database rather than a possibly stale WordPress option cache.
								$stored = $wpdb->get_var($wpdb->prepare("SELECT `option_value` FROM `{$wpdb->options}` WHERE `option_name` = %s LIMIT 1", $lock_option));
								if($wpdb->last_error !== '' || $stored === NULL)
									return FALSE;

								$existing = maybe_unserialize($stored);
								$lock_time = is_array($existing) && isset($existing['time']) ? (int)$existing['time'] : (is_numeric($existing) ? (int)$existing : 0);
								//261116.0439 A pre-effect checkpoint outlives an expired lease; an unknown outcome must not be replayed.
								if((is_array($existing) && !empty($existing['started'])) || $lock_time <= 0 || time() - $lock_time < $lock_timeout)
									return FALSE;

								//261111.0125 Compare-and-delete only the version we observed; another worker may already have replaced this expired lock.
								$deleted = $wpdb->delete($wpdb->options, array('option_name' => $lock_option, 'option_value' => $stored), array('%s', '%s'));
								if(!$deleted)
									return FALSE;

								self::paypal_purchase_lock_cache_clear($lock_option);
								$acquired = ($wpdb->query($insert) === 1);
						}
						if(!$acquired)
							return FALSE;

						self::paypal_purchase_lock_cache_clear($lock_option);
						//261111.0125 The first completion check can race with another worker's commit and unlock; recheck after claiming the lock.
						if(self::paypal_purchase_is_done_fresh($done_option, $done_ttl))
							{
								self::paypal_purchase_lock_release($lock_option, $token);
								return FALSE;
							}
						return $token;
					}
				/**
				 * Release a purchase lock only if this request still owns its token.
				 *
				 * @since 261111.0125
				 *
				 * @param string $lock_option Per-purchase lock option name.
				 * @param string $token Owner token returned by paypal_purchase_lock_claim().
				 *
				 * @return bool Whether this request released the lock.
				 */
				public static function paypal_purchase_lock_release($lock_option, $token)
					{
						global $wpdb;

						if(!is_string($lock_option) || $lock_option === '' || !is_string($token) || !preg_match('/^[a-f0-9]{32}$/D', $token))
							return FALSE;

						$stored = $wpdb->get_var($wpdb->prepare("SELECT `option_value` FROM `{$wpdb->options}` WHERE `option_name` = %s LIMIT 1", $lock_option));
						if($wpdb->last_error !== '' || $stored === NULL)
							return FALSE;

						$existing = maybe_unserialize($stored);
						if(!is_array($existing) || empty($existing['token']) || !hash_equals((string)$existing['token'], $token))
							return FALSE;

						//261111.0125 Compare-and-delete prevents an expired owner from releasing a lock now owned by a different request.
						$deleted = $wpdb->delete($wpdb->options, array('option_name' => $lock_option, 'option_value' => $stored), array('%s', '%s'));
						if($deleted)
							self::paypal_purchase_lock_cache_clear($lock_option);

						return (bool)$deleted;
					}
				/**
				 * Check whether an older, timestamp-only PayPal deduplication lock is still active.
				 *
				 * This transitional check fails closed on malformed records or database errors. The
				 * new owner-token locks use a separate namespace and never delete these legacy rows.
				 *
				 * @since 261111.0352
				 *
				 * @param string $lock_option Existing PayPal lock option name.
				 * @param int    $lock_timeout Legacy lock lifetime in seconds.
				 *
				 * @return bool TRUE when a legacy lock might still be active or cannot be read safely.
				 */
				private static function dedupe_lock_legacy_active($lock_option, $lock_timeout)
					{
						global $wpdb;

						$value = $wpdb->get_var($wpdb->prepare("SELECT `option_value` FROM `{$wpdb->options}` WHERE `option_name` = %s LIMIT 1", $lock_option));
						if($wpdb->last_error !== '')
							return TRUE;
						if($value === NULL)
							return FALSE;
						if(!is_string($value) || !preg_match('/^[1-9][0-9]{0,12}$/D', $value))
							return TRUE;

						return ((int)$value > time() - $lock_timeout);
					}
				/**
				 * Acquire an owner-token lease while respecting still-active legacy PayPal locks.
				 *
				 * The old boolean-only dedupe_lock_* API is retained for unmigrated callers.
				 * This bridge protects new requests but cannot fence already-running PHP workers
				 * executing an older plugin version; deployments must drain those workers.
				 *
				 * @since 261111.0352
				 *
				 * @param string $lock_option Original PayPal dedupe lock identity.
				 * @param int    $lock_timeout Lease duration in seconds.
				 *
				 * @return string|bool Owner token on success; FALSE on contention or failure.
				 */
				public static function dedupe_lock_acquire_owned($lock_option, $lock_timeout = 900)
					{
						if(!is_string($lock_option) || $lock_option === '' || !is_int($lock_timeout) || $lock_timeout < 1 || $lock_timeout > 86400)
							return FALSE;
						if(self::dedupe_lock_legacy_active($lock_option, $lock_timeout))
							return FALSE;

						$resource = 'paypal-dedupe/'.$lock_option;
						$token = c_ws_plugin__s2member_database::lock_acquire($resource, $lock_timeout);
						if(!is_string($token)) //261111.0352 WP_Error is truthy; treat database errors as failed acquisition.
							return FALSE;

						//261111.0352 Recheck the old record after our claim so an overlapping older worker cannot be silently ignored.
						if(self::dedupe_lock_legacy_active($lock_option, $lock_timeout))
							{
								c_ws_plugin__s2member_database::lock_release($resource, $token);
								return FALSE;
							}
						return $token;
					}
				/**
				 * Release a migrated PayPal dedupe lease only when this worker owns its token.
				 *
				 * @since 261111.0352
				 *
				 * @param string $lock_option Original PayPal lock identity.
				 * @param string $token Owner token returned by dedupe_lock_acquire_owned().
				 *
				 * @return bool Whether this worker released its lease.
				 */
				public static function dedupe_lock_release_owned($lock_option, $token)
					{
						if(!is_string($lock_option) || $lock_option === '')
							return FALSE;
						return c_ws_plugin__s2member_database::lock_release('paypal-dedupe/'.$lock_option, $token) === TRUE;
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
				 * Returns the first PayPal capture ID/status from an order representation.
				 *
				 * @since 260902.0635
				 *
				 * @param array $order PayPal order representation.
				 *
				 * @return array Capture snapshot with id/status.
				 */
				public static function paypal_checkout_order_capture_snapshot($order = array())
					{
						$capture = (!empty($order['purchase_units'][0]['payments']['captures'][0]) && is_array($order['purchase_units'][0]['payments']['captures'][0])) ? $order['purchase_units'][0]['payments']['captures'][0] : array();

						return array(
							'id'     => !empty($capture['id']) ? (string)$capture['id'] : '',
							'status' => !empty($capture['status']) ? strtoupper((string)$capture['status']) : '',
						);
					}

				/**
				 * Extracts a Gateway Checkout ID from a modern PayPal Checkout Pro-Form invoice.
				 *
				 * @since 260902.0635
				 *
				 * @param string $invoice Membership (`s2mpf-`) or Specific Post/Page (`s2msp-`) invoice.
				 *
				 * @return string Gateway Checkout ID, else an empty string.
				 */
				public static function paypal_checkout_gateway_checkout_id_from_invoice($invoice = '')
					{
						$invoice = (string)$invoice;
						$gateway_checkout_id = '';

						if(strpos($invoice, 's2mpf-') === 0)
							$gateway_checkout_id = substr($invoice, strlen('s2mpf-'));
						else if(strpos($invoice, 's2msp-') === 0)
							$gateway_checkout_id = substr($invoice, strlen('s2msp-'));
						else if(strpos($invoice, 's2mb-') === 0) //260928.1515 Standalone Framework buttons use their own invoice namespace, separate from Pro-Form account preparation.
							$gateway_checkout_id = substr($invoice, strlen('s2mb-'));

						return c_ws_plugin__s2member_gateway_checkouts::valid_id($gateway_checkout_id) ? $gateway_checkout_id : '';
					}

				/**
				 * Starts or resumes a standalone Framework PayPal Checkout button using shared durable state.
				 *
				 * @since 260928.1520
				 *
				 * @param array $token Verified, signed standalone button purchase token.
				 * @param bool  $create_allowed True only before starting provider work.
				 * @return array Operation result containing ok and error.
				 */
				public static function paypal_checkout_button_gateway_checkout_prepare($token = array(), $create_allowed = FALSE)
				{
					$invoice = !empty($token['invoice']) ? (string)$token['invoice'] : '';
					if(strpos($invoice, 's2mb-') !== 0)
						return array('ok' => TRUE, 'coordinator' => FALSE, 'error' => ''); // Existing in-flight button tokens and Pro-Forms use their established paths.

					$id = self::paypal_checkout_gateway_checkout_id_from_invoice($invoice);
					$browser_token = !empty($token['gateway_checkout_token']) ? (string)$token['gateway_checkout_token'] : '';
					if(!$id || empty($token['gateway_checkout_id']) || !hash_equals($id, (string)$token['gateway_checkout_id'])
					|| !c_ws_plugin__s2member_gateway_checkouts::browser_token_verify($id, $browser_token))
						return array('ok' => FALSE, 'coordinator' => TRUE, 'error' => 'gateway_checkout_identity_invalid');

					$operation = (!empty($token['rr']) && strtoupper((string)$token['rr']) !== 'BN') ? 'subscription' : 'payment';
					$purchase_terms = (array)$token;
					unset($purchase_terms['exp'], $purchase_terms['gateway_checkout_token']); //260928.1520 Token renewal does not alter the underlying purchase contract.
					$fingerprint = c_ws_plugin__s2member_gateway_checkouts::purchase_fingerprint($purchase_terms);

					if($create_allowed)
					{
						//260928.1705 Do not rewrite a bound option on every retry: create_or_resume() may otherwise overwrite provider/fulfillment updates committed concurrently by a webhook.
						$state = c_ws_plugin__s2member_gateway_checkouts::load_state_uncached($id);
						if(!$state)
							$state = c_ws_plugin__s2member_gateway_checkouts::create_or_resume('paypal_checkout', $operation, $id, $browser_token, $fingerprint, get_current_user_id());
						else if(!empty($state['user_id']) && (int)$state['user_id'] !== (int)get_current_user_id())
							return array('ok' => FALSE, 'coordinator' => TRUE, 'error' => 'gateway_checkout_user_mismatch');
					}
					else
						$state = c_ws_plugin__s2member_gateway_checkouts::load_state_uncached($id);

					//260928.1520 Reject a checkout returned under a replacement identity: the verified button token and PayPal invoice must keep pointing to the same durable record.
					if(!$state || !hash_equals($id, (string)$state['id']) || (string)$state['gateway'] !== 'paypal_checkout'
					|| (string)$state['operation'] !== $operation || !hash_equals($fingerprint, (string)$state['purchase_fingerprint']))
						return array('ok' => FALSE, 'coordinator' => TRUE, 'error' => 'gateway_checkout_mismatch');

					$private = c_ws_plugin__s2member_gateway_checkouts::private_context_get($id);
					if($private === FALSE)
						return array('ok' => FALSE, 'coordinator' => TRUE, 'error' => 'gateway_checkout_private_context_invalid');

					if(empty($private['paypal_checkout']['token']))
					{
						if(!$create_allowed)
							return array('ok' => FALSE, 'coordinator' => TRUE, 'error' => 'gateway_checkout_purchase_context_missing');
						$private = (array)$private;
						$private['paypal_checkout'] = !empty($private['paypal_checkout']) && is_array($private['paypal_checkout']) ? $private['paypal_checkout'] : array();
						//260928.1520 The first provider operation durably stores the authenticated purchase token for webhook-only fulfillment. No password/card data is stored.
						$private['paypal_checkout']['token'] = $token;
						if(!c_ws_plugin__s2member_gateway_checkouts::private_context_set($id, $private))
							return array('ok' => FALSE, 'coordinator' => TRUE, 'error' => 'gateway_checkout_purchase_context_save_failed');
					}
					return array('ok' => TRUE, 'coordinator' => TRUE, 'error' => '', 'gateway_checkout_id' => $id);
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
						if(!is_array($token))
							return array('__error' => 'invalid_token');

						// token: invoice, custom, item_name, item_number, amount, cc, ns, return, cancel.
						$invoice = !empty($token['invoice']) ? (string)$token['invoice'] : '';
						$custom  = isset($token['custom']) ? (string)$token['custom'] : '';
						$amount  = isset($token['amount']) ? (string)$token['amount'] : '';
						$cc      = !empty($token['cc']) ? strtoupper((string)$token['cc']) : '';
						$gateway_checkout_id = !empty($token['gateway_checkout_id']) && c_ws_plugin__s2member_gateway_checkouts::valid_id((string)$token['gateway_checkout_id']) ? (string)$token['gateway_checkout_id'] : '';
						$gateway_checkout_lock = '';

						if($gateway_checkout_id)
						{
							$gateway_checkout = c_ws_plugin__s2member_gateway_checkouts::load_state($gateway_checkout_id);
							if(!$gateway_checkout || (string)$gateway_checkout['gateway'] !== 'paypal_checkout' || (string)$gateway_checkout['operation'] !== 'payment')
								return array('__error' => 'gateway_checkout_invalid');

							//260902.0635 Return an already-persisted PayPal order before another provider create; a lost browser response can therefore resume the same logical purchase.
							if(!empty($gateway_checkout['gateway_ids']['order_id']))
								return array('id' => (string)$gateway_checkout['gateway_ids']['order_id'], 'status' => !empty($gateway_checkout['gateway_status']) ? (string)$gateway_checkout['gateway_status'] : '');

							//260907.1820 Lock the logical checkout and then re-read it; concurrent browser requests can both arrive before either has observed the PayPal order ID persisted by the other.
							$gateway_checkout_lock = c_ws_plugin__s2member_gateway_checkouts::processing_lock($gateway_checkout_id);
							if(!$gateway_checkout_lock)
								return array('__error' => 'gateway_checkout_busy');

							$gateway_checkout = c_ws_plugin__s2member_gateway_checkouts::load_state($gateway_checkout_id);
							if(!$gateway_checkout || (string)$gateway_checkout['gateway'] !== 'paypal_checkout' || (string)$gateway_checkout['operation'] !== 'payment')
							{
								c_ws_plugin__s2member_gateway_checkouts::processing_unlock($gateway_checkout_id, $gateway_checkout_lock);
								return array('__error' => 'gateway_checkout_invalid');
							}
							if(!empty($gateway_checkout['gateway_ids']['order_id']))
							{
								$order_id = (string)$gateway_checkout['gateway_ids']['order_id'];
								$status = !empty($gateway_checkout['gateway_status']) ? (string)$gateway_checkout['gateway_status'] : '';
								c_ws_plugin__s2member_gateway_checkouts::processing_unlock($gateway_checkout_id, $gateway_checkout_lock);
								return array('id' => $order_id, 'status' => $status);
							}
						}

						try
						{
							$item_name = !empty($token['item_name']) ? trim((string)$token['item_name']) : '';
							if(!$item_name)
								$item_name = 's2Member Purchase';
							if(strlen($item_name) > 127)
								$item_name = substr($item_name, 0, 127);

							$item_sku = !empty($token['item_number']) ? trim((string)$token['item_number']) : '';
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
									'breakdown'     => array('item_total' => array('currency_code' => $cc, 'value' => $item_amount)),
								),
								'description' => $item_name,
								'items'       => array(array('name' => $item_name, 'quantity' => '1', 'unit_amount' => array('currency_code' => $cc, 'value' => $item_amount))),
							);
							if($tax_amount !== '' && (float)$tax_amount > 0)
							{
								$purchase_unit['amount']['breakdown']['tax_total'] = array('currency_code' => $cc, 'value' => $tax_amount);
								$purchase_unit['items'][0]['tax'] = array('currency_code' => $cc, 'value' => $tax_amount);
							}
							if($item_sku)
								$purchase_unit['items'][0]['sku'] = $item_sku;
							if($custom && strlen($custom) <= 127)
								$purchase_unit['custom_id'] = $custom;

							$body = array(
								'intent' => 'CAPTURE',
								'purchase_units' => array($purchase_unit),
								'application_context' => array(
									'user_action' => 'PAY_NOW',
									'shipping_preference' => (!empty($token['ns']) && (string)$token['ns'] === '1') ? 'NO_SHIPPING' : 'GET_FROM_FILE',
									'return_url' => !empty($token['return']) ? (string)$token['return'] : '',
									'cancel_url' => !empty($token['cancel']) ? (string)$token['cancel'] : '',
								),
							);

							//260907.1820 Derive PayPal-Request-Id from durable logical-checkout identity, not a browser request, so reloads and immediate ambiguous retries address the same provider create operation.
							$request_id = $gateway_checkout_id ? 's2m-ppco-order-'.str_replace('-', '', $gateway_checkout_id) : 's2m-ppco-order-'.md5($invoice);
							$headers = array('PayPal-Request-Id' => $request_id);

							if($gateway_checkout_id)
							{
								$private_context = c_ws_plugin__s2member_gateway_checkouts::private_context_get($gateway_checkout_id);
								if($private_context === FALSE)
									return array('__error' => 'gateway_checkout_private_context_failed');
								$private_context = (array)$private_context;
								$private_context['paypal_checkout'] = !empty($private_context['paypal_checkout']) && is_array($private_context['paypal_checkout']) ? $private_context['paypal_checkout'] : array();
								//260902.0635 Save the validated token before contacting PayPal so a later capture webhook has enough trusted server-side context to finish an interrupted browser checkout.
								//260928.1615 An anchor/url checkout temporarily substitutes PayPal's internal approval-return URL for provider creation; keep the canonical, previously validated button token so a capture webhook returns the buyer to the original success page.
								if(strpos($invoice, 's2mb-') !== 0 || empty($private_context['paypal_checkout']['token']))
									$private_context['paypal_checkout']['token'] = $token;
								if(!c_ws_plugin__s2member_gateway_checkouts::private_context_set($gateway_checkout_id, $private_context))
									return array('__error' => 'gateway_checkout_private_context_failed');

								$context = !empty($gateway_checkout['context']) && is_array($gateway_checkout['context']) ? $gateway_checkout['context'] : array();
								$create_started_at = !empty($context['paypal_order_create_started_at']) ? (int)$context['paypal_order_create_started_at'] : 0;
								//260902.0635 PayPal normally retains Orders request IDs for six hours; if no order ID ever came back, the unknown order never reached browser approval and a fresh create is safe after that window.
								if($create_started_at && $create_started_at <= time() - (6 * HOUR_IN_SECONDS))
								{
									unset($context['paypal_order_create_started_at'], $context['paypal_order_request_id']);
									$gateway_checkout = c_ws_plugin__s2member_gateway_checkouts::update($gateway_checkout_id, array('gateway_status' => '', 'context' => $context));
									if(!$gateway_checkout)
										return array('__error' => 'gateway_checkout_save_failed');
									$create_started_at = 0;
								}
								if(!$create_started_at)
								{
									$context['paypal_order_create_started_at'] = time();
									$context['paypal_order_request_id'] = $request_id;
									$gateway_checkout = c_ws_plugin__s2member_gateway_checkouts::update($gateway_checkout_id, array('gateway_status' => 'CREATE_PENDING', 'context' => $context));
									if(!$gateway_checkout)
										return array('__error' => 'gateway_checkout_save_failed');
								}
							}

							$data = array();
							$code = 0;
							$ambiguous = FALSE;
							//260907.1820 Retry only an ambiguous transport/provider result, always with the same PayPal-Request-Id; deterministic rejection must not be treated as a possibly-created order.
							for($attempt = 0; $attempt < 2; $attempt++)
							{
								$r = self::paypal_checkout_api_request('POST', '/v2/checkout/orders', $body, $headers);
								$code = !empty($r['code']) ? (int)$r['code'] : 0;
								$response_body = !empty($r['body']) ? (string)$r['body'] : '';
								$data = $response_body ? json_decode($response_body, true) : array();
								$data = is_array($data) ? $data : array();
								$ambiguous = ($code === 0 || $code === 408 || $code >= 500 || ($code >= 200 && $code <= 299));

								if($code >= 200 && $code <= 299 && !empty($data['id']))
									break;
								if(!$ambiguous)
									break;
							}

							if($code >= 200 && $code <= 299 && !empty($data['id']))
							{
								set_transient('s2m_ppco_order_bind_'.md5($invoice), array('order_id' => (string)$data['id'], 'invoice' => $invoice, 'amount' => $amount, 'cc' => $cc, 'custom' => $custom), 3 * HOUR_IN_SECONDS);

								if($gateway_checkout_id)
								{
									$gateway_ids = !empty($gateway_checkout['gateway_ids']) && is_array($gateway_checkout['gateway_ids']) ? $gateway_checkout['gateway_ids'] : array();
									$gateway_ids['order_id'] = (string)$data['id'];
									$status = !empty($data['status']) ? 'ORDER_'.strtoupper((string)$data['status']) : 'ORDER_CREATED';
									$context = !empty($gateway_checkout['context']) && is_array($gateway_checkout['context']) ? $gateway_checkout['context'] : array();
									unset($context['paypal_order_create_started_at'], $context['paypal_order_request_id']);
									//260902.0635 Persist the PayPal order ID before returning it to the browser; a reload can then reuse it without a second provider create.
									if(!c_ws_plugin__s2member_gateway_checkouts::update($gateway_checkout_id, array('gateway_ids' => $gateway_ids, 'gateway_status' => $status, 'context' => $context)))
										return array('__error' => 'gateway_checkout_save_failed');
								}
							}
							else if($gateway_checkout_id && !$ambiguous)
							{
								//260907.1820 A deterministic create failure proves no unknown-success recovery is needed; clear CREATE_PENDING breadcrumbs so a later validated attempt is not stranded behind stale ambiguity state.
								$context = !empty($gateway_checkout['context']) && is_array($gateway_checkout['context']) ? $gateway_checkout['context'] : array();
								unset($context['paypal_order_create_started_at'], $context['paypal_order_request_id']);
								c_ws_plugin__s2member_gateway_checkouts::update($gateway_checkout_id, array('gateway_status' => '', 'context' => $context));
							}

							if($gateway_checkout_id && $ambiguous && !($code >= 200 && $code <= 299 && !empty($data['id'])))
								return array('__error' => 'order_create_unresolved');

							return $data;
						}
						finally
						{
							if($gateway_checkout_id && $gateway_checkout_lock)
								c_ws_plugin__s2member_gateway_checkouts::processing_unlock($gateway_checkout_id, $gateway_checkout_lock);
						}
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
						$gateway_checkout_id = !empty($token['gateway_checkout_id']) && c_ws_plugin__s2member_gateway_checkouts::valid_id((string)$token['gateway_checkout_id']) ? (string)$token['gateway_checkout_id'] : '';
						$binding_name = $invoice ? 's2m_ppco_order_bind_'.md5($invoice) : '';
						$binding = $binding_name ? get_transient($binding_name) : false;
						$gateway_checkout_lock = '';

						if($gateway_checkout_id)
						{
							//260907.1820 For coordinator-backed captures, the order ID already persisted server-side is authoritative; never let a browser-supplied order ID rebind this logical checkout to another PayPal resource.
							$gateway_checkout = c_ws_plugin__s2member_gateway_checkouts::load_state($gateway_checkout_id);
							$expected_order_id = $gateway_checkout && !empty($gateway_checkout['gateway_ids']['order_id']) ? (string)$gateway_checkout['gateway_ids']['order_id'] : '';
							if(!$gateway_checkout || (string)$gateway_checkout['gateway'] !== 'paypal_checkout' || (string)$gateway_checkout['operation'] !== 'payment' || !$expected_order_id || !hash_equals($expected_order_id, $order_id))
								return array('__error' => 'gateway_checkout_order_mismatch');

							$gateway_checkout_lock = c_ws_plugin__s2member_gateway_checkouts::processing_lock($gateway_checkout_id);
							if(!$gateway_checkout_lock)
								return array('__error' => 'gateway_checkout_busy');
						}
						else if(is_array($binding))
						{
							$binding_matches = (!empty($binding['order_id']) && (string)$binding['order_id'] === $order_id
							&& isset($binding['invoice']) && (string)$binding['invoice'] === $invoice
							&& isset($binding['amount']) && number_format((float)$binding['amount'], 2, '.', '') === number_format((float)$token['amount'], 2, '.', '')
							&& isset($binding['cc']) && strtoupper((string)$binding['cc']) === strtoupper((string)$token['cc'])
							&& isset($binding['custom']) && (string)$binding['custom'] === (string)$token['custom']);
							if(!$binding_matches)
								return array('__error' => 'order_binding_mismatch');
						}

						$capture_lock = $gateway_checkout_id ? '' : 's2m_ppco_capture_lock_'.md5($order_id);
						$capture_lock_token = '';
						if(!$gateway_checkout_id)
							{
								//261111.0352 Coordinator-free capture now uses an owner-token lease; keep its existing 300-second timeout and busy result.
								$capture_lock_token = self::dedupe_lock_acquire_owned($capture_lock, 300);
								if(!$capture_lock_token)
									return array('__error' => 'capture_in_progress');
							}

						try
						{
							if($gateway_checkout_id)
							{
								$gateway_checkout = c_ws_plugin__s2member_gateway_checkouts::load_state($gateway_checkout_id);
								if(!$gateway_checkout || empty($gateway_checkout['gateway_ids']['order_id']) || !hash_equals((string)$gateway_checkout['gateway_ids']['order_id'], $order_id))
									return array('__error' => 'gateway_checkout_order_mismatch');

								$gateway_status = !empty($gateway_checkout['gateway_status']) ? strtoupper((string)$gateway_checkout['gateway_status']) : '';
								//260907.1820 Terminal capture failure is sticky for this logical checkout; recovery must start a fresh validated checkout instead of attempting another capture against the failed order.
								if(in_array($gateway_status, array('CAPTURE_DENIED', 'CAPTURE_FAILED', 'CAPTURE_DECLINED'), TRUE))
									return array('__error' => strtolower($gateway_status));
							}

							//260902.0635 Once a capture is pending, do not POST another capture; read PayPal's current order state and let webhooks/browser recovery converge on the same capture.
							$read_only = ($gateway_checkout_id && !empty($gateway_checkout['gateway_status']) && strtoupper((string)$gateway_checkout['gateway_status']) === 'CAPTURE_PENDING');
							if(!is_array($binding) || $gateway_checkout_id || $read_only)
							{
								$details = self::paypal_checkout_order_details($order_id);
								if(!empty($details['__error']))
									return $details;
								if(($validation_error = self::paypal_checkout_order_validation_error($details, $order_id, $token)))
									return array('__error' => $validation_error);

								$snapshot = self::paypal_checkout_order_capture_snapshot($details);
								if($snapshot['id'] && $snapshot['status'])
								{
									if($gateway_checkout_id)
										self::paypal_checkout_order_gateway_checkout_recover($invoice, $order_id, $snapshot['id'], $snapshot['status'], 'browser', $gateway_checkout_lock);
									if($snapshot['status'] === 'COMPLETED' && !self::paypal_checkout_order_completion_error($details, $order_id, $token))
										return $details;
									if($snapshot['status'] === 'PENDING')
										return array_merge($details, array('__error' => 'capture_pending'));
									if(in_array($snapshot['status'], array('DENIED', 'FAILED', 'DECLINED'), TRUE))
										return array_merge($details, array('__error' => 'capture_'.strtolower($snapshot['status'])));
								}

								if($read_only)
									return array_merge($details, array('__error' => 'capture_pending'));
								if(!empty($details['status']) && strtoupper((string)$details['status']) === 'COMPLETED')
									return array('__error' => self::paypal_checkout_order_completion_error($details, $order_id, $token));
								if(empty($details['status']) || strtoupper((string)$details['status']) !== 'APPROVED')
									return array('__error' => 'order_not_approved');
							}

							if($gateway_checkout_id)
							{
								//260907.1820 Persist CAPTURE_PENDING before the provider POST; if PHP dies after PayPal receives the capture, the next request will recover/read the existing attempt instead of issuing a second capture.
								$context = !empty($gateway_checkout['context']) && is_array($gateway_checkout['context']) ? $gateway_checkout['context'] : array();
								$context['paypal_capture_started_at'] = !empty($context['paypal_capture_started_at']) ? (int)$context['paypal_capture_started_at'] : time();
								$context['paypal_capture_request_id'] = 's2m-ppco-cap-'.md5($order_id);
								$gateway_checkout = c_ws_plugin__s2member_gateway_checkouts::update($gateway_checkout_id, array('gateway_status' => 'CAPTURE_PENDING', 'context' => $context));
								if(!$gateway_checkout)
									return array('__error' => 'gateway_checkout_save_failed');
							}

							//260907.1820 Immediate ambiguous capture retries reuse this same request ID; once a real PENDING capture is observed, later browser requests are read-only and do not POST capture again.
							$headers = array('PayPal-Request-Id' => 's2m-ppco-cap-'.md5($order_id), 'Prefer' => 'return=representation');
							$r = array();
							$data = array();
							$ambiguous = FALSE;
							for($attempt = 0; $attempt < 2; $attempt++)
							{
								$r = self::paypal_checkout_api_request('POST', '/v2/checkout/orders/'.$order_id.'/capture', (object)array(), $headers);
								$code = !empty($r['code']) ? (int)$r['code'] : 0;
								$body = !empty($r['body']) ? (string)$r['body'] : '';
								$data = $body ? json_decode($body, true) : array();
								$data = is_array($data) ? $data : array();
								$ambiguous = ($code === 0 || $code === 408 || $code >= 500);
								if($code >= 200 && $code <= 299)
									break;
								if(!$ambiguous)
									break;
							}

							if($code >= 200 && $code <= 299)
							{
								$snapshot = self::paypal_checkout_order_capture_snapshot($data);
								if($snapshot['id'] && $snapshot['status'])
								{
									if($gateway_checkout_id)
										self::paypal_checkout_order_gateway_checkout_recover($invoice, $order_id, $snapshot['id'], $snapshot['status'], 'browser', $gateway_checkout_lock);
									if($snapshot['status'] === 'COMPLETED' && !self::paypal_checkout_order_completion_error($data, $order_id, $token))
									{
										if($binding_name) delete_transient($binding_name);
										return $data;
									}
									if($snapshot['status'] === 'PENDING')
										return array_merge($data, array('__error' => 'capture_pending'));
									if(in_array($snapshot['status'], array('DENIED', 'FAILED', 'DECLINED'), TRUE))
										return array_merge($data, array('__error' => 'capture_'.strtolower($snapshot['status'])));
								}
							}

							//260902.0635 Resolve ambiguous/incomplete capture responses by reading PayPal's current order state; never issue a second capture after a known PENDING capture exists.
							$details = self::paypal_checkout_order_details($order_id);
							if(empty($details['__error']) && !($validation_error = self::paypal_checkout_order_validation_error($details, $order_id, $token)))
							{
								$snapshot = self::paypal_checkout_order_capture_snapshot($details);
								if($snapshot['id'] && $snapshot['status'])
								{
									if($gateway_checkout_id)
										self::paypal_checkout_order_gateway_checkout_recover($invoice, $order_id, $snapshot['id'], $snapshot['status'], 'browser', $gateway_checkout_lock);
									if($snapshot['status'] === 'COMPLETED' && !self::paypal_checkout_order_completion_error($details, $order_id, $token))
									{
										if($binding_name) delete_transient($binding_name);
										return $details;
									}
									if($snapshot['status'] === 'PENDING')
										return array_merge($details, array('__error' => 'capture_pending'));
									if(in_array($snapshot['status'], array('DENIED', 'FAILED', 'DECLINED'), TRUE))
										return array_merge($details, array('__error' => 'capture_'.strtolower($snapshot['status'])));
								}
							}

							if($gateway_checkout_id && $ambiguous)
								return array('__error' => 'order_capture_unresolved');
							if(!empty($details['__error']))
								return $details;
							return array('__error' => 'order_capture_failed', '__code' => !empty($r['code']) ? (int)$r['code'] : 0, '__body' => !empty($r['body']) ? (string)$r['body'] : '');
						}
						finally
						{
							if($gateway_checkout_id && $gateway_checkout_lock)
								c_ws_plugin__s2member_gateway_checkouts::processing_unlock($gateway_checkout_id, $gateway_checkout_lock);
							else if(!$gateway_checkout_id && $capture_lock_token)
								self::dedupe_lock_release_owned($capture_lock, $capture_lock_token);
						}
					}

				/**
				 * Reconciles a one-time PayPal order/capture into Gateway Checkout state.
				 *
				 * @since 260902.0635
				 */
				public static function paypal_checkout_order_gateway_checkout_recover($invoice = '', $order_id = '', $capture_id = '', $capture_status = '', $via = 'webhook', $gateway_checkout_lock = '')
					{
						$gateway_checkout_id = self::paypal_checkout_gateway_checkout_id_from_invoice($invoice);
						$order_id = trim((string)$order_id);
						$capture_id = trim((string)$capture_id);
						$capture_status = strtoupper(trim((string)$capture_status));
						$owns_lock = FALSE;

						if(!$gateway_checkout_id || !$order_id)
							return array('handled' => FALSE, 'ok' => FALSE, 'error' => 'not_coordinator_checkout');

						if(!$gateway_checkout_lock)
						{
							$gateway_checkout_lock = c_ws_plugin__s2member_gateway_checkouts::processing_lock($gateway_checkout_id, 60);
							if(!$gateway_checkout_lock)
								return array('handled' => TRUE, 'ok' => FALSE, 'error' => 'gateway_checkout_busy', 'gateway_checkout_id' => $gateway_checkout_id);
							$owns_lock = TRUE;
						}

						try
						{
							$gateway_checkout = c_ws_plugin__s2member_gateway_checkouts::load_state($gateway_checkout_id);
							if(!$gateway_checkout || (string)$gateway_checkout['gateway'] !== 'paypal_checkout' || (string)$gateway_checkout['operation'] !== 'payment')
								return array('handled' => FALSE, 'ok' => FALSE, 'error' => 'not_coordinator_checkout');

							//260907.1820 Provider identities are immutable once learned: browser/webhook reconciliation may advance status only for the same PayPal order/capture and must never rebind a checkout to conflicting IDs.
							$existing_order_id = !empty($gateway_checkout['gateway_ids']['order_id']) ? (string)$gateway_checkout['gateway_ids']['order_id'] : '';
							$existing_capture_id = !empty($gateway_checkout['gateway_ids']['capture_id']) ? (string)$gateway_checkout['gateway_ids']['capture_id'] : '';
							if($existing_order_id && !hash_equals($existing_order_id, $order_id))
								return array('handled' => TRUE, 'ok' => FALSE, 'error' => 'gateway_checkout_order_conflict', 'gateway_checkout_id' => $gateway_checkout_id);
							if($existing_capture_id && $capture_id && !hash_equals($existing_capture_id, $capture_id))
								return array('handled' => TRUE, 'ok' => FALSE, 'error' => 'gateway_checkout_capture_conflict', 'gateway_checkout_id' => $gateway_checkout_id);

							$existing_gateway_status = strtoupper((string)$gateway_checkout['gateway_status']);
							//260902.0646 Provider finality is monotonic; stale browser/webhook observations must never downgrade a capture that already completed or reached a terminal failure.
							if(in_array($existing_gateway_status, array('CAPTURE_COMPLETED', 'CAPTURE_DENIED', 'CAPTURE_FAILED', 'CAPTURE_DECLINED'), TRUE))
								return array('handled' => TRUE, 'ok' => TRUE, 'error' => '', 'gateway_checkout_id' => $gateway_checkout_id, 'order_id' => $existing_order_id ? $existing_order_id : $order_id, 'capture_id' => $existing_capture_id ? $existing_capture_id : $capture_id, 'status' => $existing_gateway_status);

							$gateway_ids = !empty($gateway_checkout['gateway_ids']) && is_array($gateway_checkout['gateway_ids']) ? $gateway_checkout['gateway_ids'] : array();
							$gateway_ids['order_id'] = $order_id;
							if($capture_id)
								$gateway_ids['capture_id'] = $capture_id;

							$status = $capture_status ? 'CAPTURE_'.$capture_status : (!empty($gateway_checkout['gateway_status']) ? (string)$gateway_checkout['gateway_status'] : 'ORDER_CREATED');
							$context = !empty($gateway_checkout['context']) && is_array($gateway_checkout['context']) ? $gateway_checkout['context'] : array();
							unset($context['paypal_order_create_started_at'], $context['paypal_order_request_id']);
							if($capture_status && $capture_status !== 'PENDING')
								unset($context['paypal_capture_started_at'], $context['paypal_capture_request_id']);
							if($via === 'webhook')
							{
								//260902.0635 Preserve a compact breadcrumb for the future admin diagnostics screen without retaining raw gateway payloads.
								$context['paypal_capture_recovered_at'] = time();
								$context['paypal_capture_recovered_via'] = 'webhook';
							}

							if(!c_ws_plugin__s2member_gateway_checkouts::update($gateway_checkout_id, array('gateway_ids' => $gateway_ids, 'gateway_status' => $status, 'context' => $context)))
								return array('handled' => TRUE, 'ok' => FALSE, 'error' => 'gateway_checkout_save_failed', 'gateway_checkout_id' => $gateway_checkout_id);

							return array('handled' => TRUE, 'ok' => TRUE, 'error' => '', 'gateway_checkout_id' => $gateway_checkout_id, 'order_id' => $order_id, 'capture_id' => $capture_id, 'status' => $status);
						}
						finally
						{
							if($owns_lock && $gateway_checkout_lock)
								c_ws_plugin__s2member_gateway_checkouts::processing_unlock($gateway_checkout_id, $gateway_checkout_lock);
						}
					}

				/**
				 * Fulfills one completed coordinator-backed PayPal order and saves its browser result.
				 *
				 * @since 260902.0635
				 */
				public static function paypal_checkout_order_fulfill($order = array(), $token = array())
					{
						$order_id = !empty($order['id']) ? (string)$order['id'] : '';
						$invoice = !empty($token['invoice']) ? (string)$token['invoice'] : '';
						$gateway_checkout_id = !empty($token['gateway_checkout_id']) && c_ws_plugin__s2member_gateway_checkouts::valid_id((string)$token['gateway_checkout_id']) ? (string)$token['gateway_checkout_id'] : self::paypal_checkout_gateway_checkout_id_from_invoice($invoice);

						if(!$gateway_checkout_id || ($completion_error = self::paypal_checkout_order_completion_error($order, $order_id, $token)))
							return array('ok' => FALSE, 'error' => $completion_error ? $completion_error : 'gateway_checkout_invalid');

						$gateway_checkout = c_ws_plugin__s2member_gateway_checkouts::load_state($gateway_checkout_id);
						if(!$gateway_checkout || (string)$gateway_checkout['gateway'] !== 'paypal_checkout' || (string)$gateway_checkout['operation'] !== 'payment')
							return array('ok' => FALSE, 'error' => 'gateway_checkout_invalid');

						$private_context = c_ws_plugin__s2member_gateway_checkouts::private_context_get($gateway_checkout_id);
						if($private_context === FALSE)
							return array('ok' => FALSE, 'error' => 'gateway_checkout_private_context_failed');
						//260907.1820 Gateway Checkout's fulfilled result is the outer browser/webhook convergence checkpoint; paypal_checkout_notify_once() remains the inner transaction-level entitlement dedupe.
						if((string)$gateway_checkout['fulfillment_status'] === 'fulfilled' && !empty($private_context['paypal_checkout']['fulfillment_result']) && is_array($private_context['paypal_checkout']['fulfillment_result']))
							return array_merge(array('ok' => TRUE, 'processed' => FALSE, 'duplicate' => TRUE), $private_context['paypal_checkout']['fulfillment_result']);

						$capture = $order['purchase_units'][0]['payments']['captures'][0];
						$pu_cap_id = (string)$capture['id'];
						$paypal = array(
							'txn_type' => 'web_accept', 'payment_status' => 'Completed', 'subscr_gateway' => 'paypal',
							'txn_id' => $pu_cap_id, 'subscr_id' => $pu_cap_id, 'subscr_baid' => $pu_cap_id, 'subscr_cid' => $pu_cap_id,
							'mc_gross' => (string)$capture['amount']['value'], 'mc_currency' => strtoupper((string)$capture['amount']['currency_code']),
							'invoice' => $invoice, 'custom' => isset($token['custom']) ? (string)$token['custom'] : '',
							'item_name' => isset($token['item_name']) ? (string)$token['item_name'] : '', 'item_number' => isset($token['item_number']) ? (string)$token['item_number'] : '',
							'payer_email' => !empty($order['payer']['email_address']) ? (string)$order['payer']['email_address'] : (!empty($token['payer_email']) ? (string)$token['payer_email'] : ''),
							'first_name' => !empty($order['payer']['name']['given_name']) ? (string)$order['payer']['name']['given_name'] : (!empty($token['first_name']) ? (string)$token['first_name'] : ''),
							'last_name' => !empty($order['payer']['name']['surname']) ? (string)$order['payer']['name']['surname'] : (!empty($token['last_name']) ? (string)$token['last_name'] : ''),
							'option_name1' => isset($token['on0']) ? (string)$token['on0'] : '', 'option_selection1' => isset($token['os0']) ? (string)$token['os0'] : '',
							'option_name2' => isset($token['on1']) ? (string)$token['on1'] : '', 'option_selection2' => isset($token['os1']) ? (string)$token['os1'] : '',
						);
						if(isset($token['tax']))
							$paypal['tax'] = (string)$token['tax'];

						$proxy_use = !empty($token['s2member_paypal_proxy_use']) ? (string)$token['s2member_paypal_proxy_use'] : 'paypal_checkout';
						$notify_extra = array();
						if(!empty($token['s2member_paypal_proxy_coupon']) && is_array($token['s2member_paypal_proxy_coupon']))
							$notify_extra['s2member_paypal_proxy_coupon'] = $token['s2member_paypal_proxy_coupon'];
						if(array_key_exists('s2member_paypal_proxy_return_url', $token))
							$notify_extra['s2member_paypal_proxy_return_url'] = (string)$token['s2member_paypal_proxy_return_url'];

						//261115.0113 Only Framework button captures use the local Notify processor; Pro and other callers keep their HTTP path.
						// The existing done marker and outer Checkout lock still govern duplicate fulfillment.
						$in_process = ($proxy_use === 'paypal_checkout' && strpos($invoice, 's2mb-') === 0 && apply_filters('ws_plugin__s2member_paypal_checkout_internal_notify', TRUE, $invoice, $pu_cap_id));
						$notify_result = self::paypal_checkout_notify_once($paypal, 's2m_ppco_capture_done_'.md5($pu_cap_id), $proxy_use, $notify_extra, $in_process);
						if(empty($notify_result['ok']))
							return array('ok' => FALSE, 'error' => !empty($notify_result['error']) ? (string)$notify_result['error'] : 'notify_proxy_failed');

						$return_url = add_query_arg('s2member_paypal_proxy', 'paypal', !empty($token['return']) ? (string)$token['return'] : home_url('/'));
						$return_post = array_merge($paypal, array('s2member_paypal_proxy' => 'paypal', 's2member_paypal_proxy_use' => $proxy_use));
						if(array_key_exists('s2member_paypal_proxy_return_url', $token))
							$return_post['s2member_paypal_proxy_return_url'] = !empty($notify_result['body']) ? trim((string)$notify_result['body']) : '';

						$return_handoff = self::paypal_checkout_return_handoff_create($return_post);
						if(!$return_handoff)
							return array('ok' => FALSE, 'error' => 'return_handoff_failed');
						$return_post['s2member_paypal_checkout_handoff'] = $return_handoff;

						$result = array('rtn_url' => $return_url, 'rtn_post' => $return_post, 'txn_id' => $pu_cap_id);
						$private_context = (array)$private_context;
						$private_context['paypal_checkout'] = !empty($private_context['paypal_checkout']) && is_array($private_context['paypal_checkout']) ? $private_context['paypal_checkout'] : array();
						//260907.1820 Persist the minimal browser handoff before marking fulfillment complete; if the final state write fails after Notify, notify_once still blocks duplicate entitlement work and this result remains recoverable. Passwords/card credentials never belong here.
						$private_context['paypal_checkout']['fulfillment_result'] = $result;
						if(!c_ws_plugin__s2member_gateway_checkouts::private_context_set($gateway_checkout_id, $private_context))
							return array('ok' => FALSE, 'error' => 'gateway_checkout_private_context_failed');

						//260928.1705 Final fulfillment must patch the latest checkout version: a concurrent webhook/browser context write must not be lost or downgrade the terminal fulfilled state.
						if(!c_ws_plugin__s2member_gateway_checkouts::patch($gateway_checkout_id, array('gateway_ids' => array('order_id' => $order_id, 'capture_id' => $pu_cap_id), 'gateway_status' => 'CAPTURE_COMPLETED', 'fulfillment_status' => 'fulfilled')))
							return array('ok' => FALSE, 'error' => 'gateway_checkout_save_failed');

						return array_merge(array('ok' => TRUE, 'processed' => !empty($notify_result['processed']), 'duplicate' => !empty($notify_result['duplicate'])), $result);
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
				 * @param bool   $in_process  Dispatch one trusted Framework capture without a same-site HTTP request.
				 *
				 * @return array Result with ok/processed/duplicate/error and response details.
				 */
				public static function paypal_checkout_notify_once($paypal = array(), $done_option = '', $proxy_use = 'paypal_checkout', $extra = array(), $in_process = FALSE)
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
						//261111.0618 Use the shared owner-token lease for Notify forwarding; the bridge also respects active historical timestamp locks.
						$lock_token = self::dedupe_lock_acquire_owned($lock_option, 900);
						if(!is_string($lock_token))
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

								//261115.0118 Preserve a valid legacy proxy credential whenever the caller uses the existing HTTP transport.
								//261115.0450 Only the two PayPal Checkout-owned proxy routes can enter the isolated local Notify core.
								// Pro, other gateways, and arbitrary custom proxy-use values retain the existing signed HTTP transport.
								$use_internal_notify = ($in_process && in_array($proxy_use, array('paypal_checkout', 'paypal_checkout_webhook'), TRUE));
								$notify_url = home_url('/?s2member_paypal_notify=1');
								$notify_post = array_merge($paypal, $extra, array(
									's2member_paypal_proxy'              => 'paypal',
									's2member_paypal_proxy_use'          => $proxy_use,
									's2member_paypal_proxy_verification' => $use_internal_notify ? '' : self::paypal_proxy_key_gen(),
								));
								//261115.0113 The local transport signs under its isolated canonical Host, while legacy HTTP keeps the original request signing behavior.
								$notify_r = $use_internal_notify ? self::paypal_checkout_notify_inprocess($notify_post) : c_ws_plugin__s2member_utils_urls::remote($notify_url, $notify_post, array('timeout' => 20), true);

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
								//261111.0618 Only this Notify worker can release its lease; retry paths retain the existing done-marker semantics.
								self::dedupe_lock_release_owned($lock_option, $lock_token);
							}
					}

				/**
				 * Process a trusted, server-created Checkout notification with isolated HTTP-style request variables.
				 * This is not an HTTP authentication endpoint; callers must verify provider data first.
				 *
				 * @since 261115.0113
				 * @param array $post Server-created Notify payload, never raw browser or unverified webhook input.
				 * @param bool $include_handler_outcome Return internal handler details for trusted recovery callers only.
				 * @return array Normalized response code, message, and body.
				 */
				public static function paypal_checkout_notify_inprocess($post, $include_handler_outcome = FALSE)
					{
						$home = home_url('/');
						$host = is_string($home) ? parse_url($home, PHP_URL_HOST) : FALSE;
						if(!is_array($post) || !$post || !is_string($host) || $host === '' || !function_exists('wp_slash'))
							return array('code' => 0, 'message' => 'invalid_internal_notify_context', 'body' => '');

						//261115.0113 Never inherit browser GET/POST fields, proxy credentials, or an untrusted Host header into a payment notification.
						// Restore the complete caller context even when a filter or Notify handler throws.
						$prior_get = $_GET;
						$prior_post = $_POST;
						$prior_request = $_REQUEST;
						$prior_cookie = $_COOKIE;
						$prior_files = $_FILES;
						$prior_server = $_SERVER;
						$prior_ignore_user_abort = ignore_user_abort();
						$prior_email_config = c_ws_plugin__s2member_email_configs::email_config_status();
						$prior_user_id = get_current_user_id();
						$prior_internal_dispatch = self::$paypal_proxy_internal_dispatch; //261117.0357 Nested calls must restore the outer trusted context.

						try
							{
								$path = parse_url($home, PHP_URL_PATH);
								//261115.0223 A server-generated Notify must not inherit the browser's headers, credentials, cookies or uploads.
								// Preserve the whole $_SERVER array so even header order and unexpected hook mutations are restored.
								$_GET = array('s2member_paypal_notify' => '1');
								$_POST = wp_slash($post); // Match WordPress's slashed incoming POST contract before paypal_postvars() removes slashes.
								$_REQUEST = array_merge($_GET, $_POST);
								$_COOKIE = array();
								$_FILES = array();
								//261115.2110 Internal Notify runs anonymously, matching a separate HTTP request, then restores the checkout user.
								wp_set_current_user(0);
								foreach(array_keys($_SERVER) as $server_key)
									if(strpos($server_key, 'HTTP_') === 0)
										unset($_SERVER[$server_key]);
								unset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $_SERVER['AUTH_TYPE'], $_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
								$_SERVER['REQUEST_METHOD'] = 'POST';
								$_SERVER['REQUEST_URI'] = (is_string($path) && $path !== '' ? $path : '/').'?s2member_paypal_notify=1';
								$_SERVER['QUERY_STRING'] = 's2member_paypal_notify=1';
								$_SERVER['HTTP_HOST'] = $host;
								$_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
								$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
								$_SERVER['HTTP_USER_AGENT'] = 's2Member internal Notify';

								//261117.0357 Local PHP dispatch authenticates by its isolated call stack, not by a reusable key placed in request globals.
								$_POST['s2member_paypal_proxy_verification'] = '';
								$_REQUEST['s2member_paypal_proxy_verification'] = '';
								self::$paypal_proxy_internal_dispatch = true;
								$outcome = c_ws_plugin__s2member_paypal_notify_in::paypal_notify_process();
								if(!is_array($outcome) || !isset($outcome['code']) || !is_int($outcome['code']))
									return array('code' => 0, 'message' => 'invalid_internal_notify_outcome', 'body' => '');

								//261116.0147 Ordinary Checkout replies stay unchanged; only trusted recovery callers receive handler details.
								$response = array('code' => $outcome['code'], 'message' => $outcome['code'] === 200 ? 'OK' : 'rejected',
								                  'body' => isset($outcome['body']) ? (string)$outcome['body'] : '');
								if($include_handler_outcome)
									$response['paypal'] = isset($outcome['paypal']) && is_array($outcome['paypal']) ? $outcome['paypal'] : array();
								return $response;
							}
						finally
							{
								self::$paypal_proxy_internal_dispatch = $prior_internal_dispatch; //261117.0357 Never leave a trusted bypass active after an exception.
								$_GET = $prior_get;
								$_POST = $prior_post;
								$_REQUEST = $prior_request;
								$_COOKIE = $prior_cookie;
								$_FILES = $prior_files;
								$_SERVER = $prior_server;
								wp_set_current_user($prior_user_id);
								ignore_user_abort($prior_ignore_user_abort);
								//261115.0223 A throwing Notify hook may skip its normal email-filter restoration; preserve the caller's setting.
								if(c_ws_plugin__s2member_email_configs::email_config_status() !== $prior_email_config)
									if($prior_email_config) c_ws_plugin__s2member_email_configs::email_config();
									else c_ws_plugin__s2member_email_configs::email_config_release();
							}
					}

				/**
				 * Recovers a coordinator-backed PayPal subscription ID/status from a verified webhook resource.
				 *
				 * @since 260902.0200
				 *
				 * @param string $invoice         PayPal custom_id/invoice carrying the Gateway Checkout ID.
				 * @param string $subscription_id PayPal subscription ID.
				 * @param string $status          PayPal subscription status, if known.
				 *
				 * @return array Recovery result with handled/ok/recovered/error details.
				 */
				public static function paypal_checkout_subscription_gateway_checkout_recover($invoice = '', $subscription_id = '', $status = '')
					{
						$invoice = trim((string)$invoice);
						$subscription_id = trim((string)$subscription_id);
						$status = strtoupper(trim((string)$status));
						$gateway_checkout_id = self::paypal_checkout_gateway_checkout_id_from_invoice($invoice); //260928.1515 Recover both Pro-Forms and standalone Framework button subscriptions by their signed invoice identity.

						if(!$subscription_id || !c_ws_plugin__s2member_gateway_checkouts::valid_id($gateway_checkout_id))
							return array('handled' => false, 'ok' => false, 'recovered' => false, 'error' => 'not_coordinator_checkout');

						$gateway_checkout = c_ws_plugin__s2member_gateway_checkouts::load_state($gateway_checkout_id);
						if(!$gateway_checkout || (string)$gateway_checkout['gateway'] !== 'paypal_checkout' || (string)$gateway_checkout['operation'] !== 'subscription')
							return array('handled' => false, 'ok' => false, 'recovered' => false, 'error' => 'not_coordinator_checkout');

						$lock = c_ws_plugin__s2member_gateway_checkouts::processing_lock($gateway_checkout_id, 60);
						if(!$lock)
							return array('handled' => true, 'ok' => false, 'recovered' => false, 'error' => 'gateway_checkout_busy', 'gateway_checkout_id' => $gateway_checkout_id);

						try
						{
							$gateway_checkout = c_ws_plugin__s2member_gateway_checkouts::load_state($gateway_checkout_id);
							if(!$gateway_checkout || (string)$gateway_checkout['gateway'] !== 'paypal_checkout' || (string)$gateway_checkout['operation'] !== 'subscription')
								return array('handled' => true, 'ok' => false, 'recovered' => false, 'error' => 'gateway_checkout_invalid', 'gateway_checkout_id' => $gateway_checkout_id);

							$existing_subscription_id = !empty($gateway_checkout['gateway_ids']['subscription_id']) ? (string)$gateway_checkout['gateway_ids']['subscription_id'] : '';
							if($existing_subscription_id && !hash_equals($existing_subscription_id, $subscription_id))
								return array('handled' => true, 'ok' => false, 'recovered' => false, 'error' => 'gateway_checkout_subscription_conflict', 'gateway_checkout_id' => $gateway_checkout_id, 'subscription_id' => $existing_subscription_id);

							$gateway_ids = !empty($gateway_checkout['gateway_ids']) && is_array($gateway_checkout['gateway_ids']) ? $gateway_checkout['gateway_ids'] : array();
							$gateway_ids['subscription_id'] = $subscription_id;
							$context = !empty($gateway_checkout['context']) && is_array($gateway_checkout['context']) ? $gateway_checkout['context'] : array();
							unset($context['paypal_subscription_create_started_at'], $context['paypal_subscription_request_id']);

							if(!$existing_subscription_id)
							{
								//260902.0200 Record webhook repair for future diagnostics without treating CREATED as payment/fulfillment.
								$context['paypal_subscription_recovered_at'] = time();
								$context['paypal_subscription_recovered_via'] = 'webhook';
							}

							$gateway_status = !empty($gateway_checkout['gateway_status']) ? strtoupper((string)$gateway_checkout['gateway_status']) : '';
							if($status === 'ACTIVE' || ($status === 'APPROVED' && $gateway_status === 'APPROVAL_PENDING') || !$gateway_status || $gateway_status === 'CREATE_PENDING')
								$gateway_status = $status ? $status : 'APPROVAL_PENDING';

							if(!c_ws_plugin__s2member_gateway_checkouts::update($gateway_checkout_id, array('gateway_ids' => $gateway_ids, 'gateway_status' => $gateway_status, 'context' => $context)))
								return array('handled' => true, 'ok' => false, 'recovered' => false, 'error' => 'gateway_checkout_save_failed', 'gateway_checkout_id' => $gateway_checkout_id);

							return array('handled' => true, 'ok' => true, 'recovered' => !$existing_subscription_id, 'error' => '', 'gateway_checkout_id' => $gateway_checkout_id, 'subscription_id' => $subscription_id, 'status' => $gateway_status);
						}
						finally
						{
							c_ws_plugin__s2member_gateway_checkouts::processing_unlock($gateway_checkout_id, $lock);
						}
					}

				/**
				 * Completes an approved standalone Framework button subscription from the same
				 * authoritative PayPal resource whether invoked by browser or verified webhook.
				 *
				 * @since 260928.1530
				 */
				public static function paypal_checkout_button_subscription_fulfill($subscription = array(), $token = array(), $via = 'webhook')
				{
					$invoice = !empty($token['invoice']) ? (string)$token['invoice'] : '';
					$id = self::paypal_checkout_gateway_checkout_id_from_invoice($invoice);
					if(!$id || strpos($invoice, 's2mb-') !== 0 || empty($token['gateway_checkout_id']) || !hash_equals($id, (string)$token['gateway_checkout_id']))
						return array('ok' => FALSE, 'error' => 'gateway_checkout_identity_invalid');

					$state = c_ws_plugin__s2member_gateway_checkouts::load_state($id);
					if(!$state || (string)$state['gateway'] !== 'paypal_checkout' || (string)$state['operation'] !== 'subscription')
						return array('ok' => FALSE, 'error' => 'gateway_checkout_invalid');

					$subscription_id = !empty($subscription['id']) ? (string)$subscription['id'] : '';
					$status = !empty($subscription['status']) ? strtoupper((string)$subscription['status']) : '';
					$custom_id = !empty($subscription['custom_id']) ? (string)$subscription['custom_id'] : '';
					if(!$subscription_id || !$custom_id || !hash_equals($invoice, $custom_id))
						return array('ok' => FALSE, 'error' => 'subscription_purchase_identity_mismatch');

					$expected_plan = self::paypal_checkout_plan_get_id($token);
					if(!$expected_plan || empty($subscription['plan_id']) || !hash_equals((string)$expected_plan, (string)$subscription['plan_id']))
						return array('ok' => FALSE, 'error' => 'subscription_plan_mismatch');

					$is_single_cycle = isset($token['rr']) && (string)$token['rr'] === '0';
					$last_payment_amount = isset($subscription['billing_info']['last_payment']['amount']['value']) ? (string)$subscription['billing_info']['last_payment']['amount']['value'] : '';
					$last_payment_currency = !empty($subscription['billing_info']['last_payment']['amount']['currency_code']) ? strtoupper((string)$subscription['billing_info']['last_payment']['amount']['currency_code']) : '';
					//260928.1703 An immediately EXPIRED single-cycle subscription is paid only when PayPal's reported last payment matches the signed price and currency, not merely when a payment field exists.
					$last_paid = ($last_payment_amount !== '' && is_numeric($last_payment_amount) && isset($token['amount'])
						&& number_format((float)$last_payment_amount, 2, '.', '') === number_format((float)$token['amount'], 2, '.', '')
						&& !empty($token['cc']) && $last_payment_currency === strtoupper((string)$token['cc']));
					if($status !== 'ACTIVE' && !($is_single_cycle && $status === 'EXPIRED' && $last_paid))
						return in_array($status, array('APPROVAL_PENDING', 'APPROVED'), TRUE)
							? array('ok' => FALSE, 'pending_activation' => TRUE, 'error' => 'pending_activation', 'status' => $status)
							: array('ok' => FALSE, 'error' => 'subscription_status_invalid', 'status' => $status);

					//260928.1530 Bind the real subscription ID before fulfillment so a second event/browser request cannot attach a different provider subscription to this purchase.
					$recovery = self::paypal_checkout_subscription_gateway_checkout_recover($invoice, $subscription_id, $status);
					if(empty($recovery['handled']) || empty($recovery['ok']))
					{
						//260928.1608 An independent CREATED/ACTIVATED webhook can own the coordinator lock briefly; the browser should poll rather than report a permanent checkout failure.
						//260928.1703 A redirect return also competes with CREATED/ACTIVATED webhook recovery; let it retry the signed return instead of displaying a spurious failure.
						if(in_array($via, array('browser', 'return'), TRUE) && !empty($recovery['error']) && $recovery['error'] === 'gateway_checkout_busy')
							return array('ok' => FALSE, 'pending_activation' => TRUE, 'status' => $status, 'error' => 'gateway_checkout_busy');
						return array('ok' => FALSE, 'error' => !empty($recovery['error']) ? $recovery['error'] : 'subscription_recovery_failed');
					}

					$private = c_ws_plugin__s2member_gateway_checkouts::private_context_get($id);
					if(!is_array($private) || empty($private['paypal_checkout']['token']) || !is_array($private['paypal_checkout']['token']))
						return array('ok' => FALSE, 'error' => 'gateway_checkout_purchase_context_missing');
					$stored_token = $private['paypal_checkout']['token'];
					if(empty($stored_token['invoice']) || !hash_equals($invoice, (string)$stored_token['invoice']) || empty($stored_token['item_number']))
						return array('ok' => FALSE, 'error' => 'gateway_checkout_purchase_context_mismatch');

					$state = c_ws_plugin__s2member_gateway_checkouts::load_state($id);
					if($state && (string)$state['fulfillment_status'] === 'fulfilled' && !empty($private['paypal_checkout']['fulfillment_result']))
						return array_merge(array('ok' => TRUE, 'processed' => FALSE, 'duplicate' => TRUE), $private['paypal_checkout']['fulfillment_result']);

					$paypal = array(
						'txn_type' => 'subscr_signup', 'payment_status' => 'Completed', 'subscr_gateway' => 'paypal',
						'txn_id' => $subscription_id, 'subscr_id' => $subscription_id, 'subscr_baid' => $subscription_id, 'subscr_cid' => $subscription_id,
						'mc_gross' => (string)$stored_token['amount'], 'mc_currency' => strtoupper((string)$stored_token['cc']),
						'period1' => (!empty($stored_token['tp']) && !empty($stored_token['tt'])) ? ((string)$stored_token['tp'].' '.strtoupper((string)$stored_token['tt'])) : '0 D',
						'mc_amount1' => (!empty($stored_token['tp']) && !empty($stored_token['tt'])) ? (string)$stored_token['ta'] : '0.00',
						'period3' => ((string)$stored_token['rp'].' '.strtoupper((string)$stored_token['rt'])),
						'mc_amount3' => (string)$stored_token['amount'],
						'recurring' => ((isset($stored_token['rr']) && (string)$stored_token['rr'] === '1') ? '1' : '0'),
						'invoice' => $invoice, 'custom' => (string)$stored_token['custom'],
						'item_name' => (string)$stored_token['item_name'], 'item_number' => (string)$stored_token['item_number'],
						'payer_email' => !empty($subscription['subscriber']['email_address']) ? (string)$subscription['subscriber']['email_address'] : '',
						'first_name' => !empty($subscription['subscriber']['name']['given_name']) ? (string)$subscription['subscriber']['name']['given_name'] : '',
						'last_name' => !empty($subscription['subscriber']['name']['surname']) ? (string)$subscription['subscriber']['name']['surname'] : '',
						'option_name1' => (string)$stored_token['on0'], 'option_selection1' => (string)$stored_token['os0'],
						'option_name2' => (string)$stored_token['on1'], 'option_selection2' => (string)$stored_token['os1'],
					);

					//261115.0413 Browser confirmation and verified webhook activation share this checked Framework button subscription.
					// Keep the existing subscription completion marker, but avoid a second HTTP request to this same WordPress site.
					$in_process = apply_filters('ws_plugin__s2member_paypal_checkout_internal_notify', TRUE, $invoice, $subscription_id);
					$notify_result = self::paypal_checkout_notify_once($paypal, 's2m_ppco_subscr_done_'.md5($subscription_id), 'paypal_checkout', array(), $in_process);
					if(empty($notify_result['ok']))
						return array('ok' => FALSE, 'error' => !empty($notify_result['error']) ? (string)$notify_result['error'] : 'notify_proxy_failed');

					$return_url = add_query_arg('s2member_paypal_proxy', 'paypal', (string)$stored_token['return']);
					$return_post = array_merge($paypal, array('s2member_paypal_proxy' => 'paypal', 's2member_paypal_proxy_use' => 'paypal_checkout'));
					$handoff = self::paypal_checkout_return_handoff_create($return_post);
					if(!$handoff)
						return array('ok' => FALSE, 'error' => 'return_handoff_failed');
					$return_post['s2member_paypal_checkout_handoff'] = $handoff;
					$result = array('rtn_url' => $return_url, 'rtn_post' => $return_post, 'subscription_id' => $subscription_id);

					$private['paypal_checkout']['fulfillment_result'] = $result;
					if(!c_ws_plugin__s2member_gateway_checkouts::private_context_set($id, $private))
						return array('ok' => FALSE, 'error' => 'gateway_checkout_private_context_save_failed');
					if(!c_ws_plugin__s2member_gateway_checkouts::patch($id, array('gateway_ids' => array('subscription_id' => $subscription_id), 'gateway_status' => $status, 'fulfillment_status' => 'fulfilled')))
						return array('ok' => FALSE, 'error' => 'gateway_checkout_save_failed');

					//260928.1530 Preserve button upgrade semantics: only the request that processed Notify may cancel the old subscription, never a duplicate callback.
					$old_id = !empty($stored_token['old__subscr_id']) ? (string)$stored_token['old__subscr_id'] : '';
					if(!empty($notify_result['processed']) && $old_id && $old_id !== $subscription_id && apply_filters('s2member_pro_cancels_old_rp_before_new_rp', TRUE, array('old__subscr_id' => $old_id, 'subscr_id' => $subscription_id)))
						c_ws_plugin__s2member_utilities::cancel_gateway_subscription(!empty($stored_token['old__subscr_gateway']) ? (string)$stored_token['old__subscr_gateway'] : '', $old_id,
							!empty($stored_token['old__subscr_baid']) ? (string)$stored_token['old__subscr_baid'] : '', !empty($stored_token['old__subscr_cid']) ? (string)$stored_token['old__subscr_cid'] : '',
							!empty($stored_token['old__ipn_signup_vars']) && is_array($stored_token['old__ipn_signup_vars']) ? $stored_token['old__ipn_signup_vars'] : array());

					return array_merge(array('ok' => TRUE, 'processed' => !empty($notify_result['processed']), 'duplicate' => !empty($notify_result['duplicate'])), $result);
				}

				/**
				 * Creates a PayPal Checkout subscription server-side.
				 *
				 * Redirect-mode and coordinator-backed JS flows create here; legacy JS buttons may
				 * still create client-side using plan_id and then confirm server-side.
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
						$gateway_checkout_id = !empty($token['gateway_checkout_id']) && c_ws_plugin__s2member_gateway_checkouts::valid_id((string)$token['gateway_checkout_id']) ? (string)$token['gateway_checkout_id'] : '';
						$gateway_checkout_lock = '';

						if($gateway_checkout_id)
						{
							$gateway_checkout = c_ws_plugin__s2member_gateway_checkouts::load_state($gateway_checkout_id);
							if(!$gateway_checkout || (string)$gateway_checkout['gateway'] !== 'paypal_checkout' || (string)$gateway_checkout['operation'] !== 'subscription')
								return array('__error' => 'gateway_checkout_invalid');

							//260901.2145 Return a previously persisted PayPal subscription before making another create request; this also recovers a browser reload after server-side creation succeeded.
							if(!empty($gateway_checkout['gateway_ids']['subscription_id']))
								return array('id' => (string)$gateway_checkout['gateway_ids']['subscription_id'], 'status' => !empty($gateway_checkout['gateway_status']) ? (string)$gateway_checkout['gateway_status'] : '');

							$gateway_checkout_lock = c_ws_plugin__s2member_gateway_checkouts::processing_lock($gateway_checkout_id);
							if(!$gateway_checkout_lock)
								return array('__error' => 'gateway_checkout_busy');

							$gateway_checkout = c_ws_plugin__s2member_gateway_checkouts::load_state($gateway_checkout_id);
							if(!$gateway_checkout || (string)$gateway_checkout['gateway'] !== 'paypal_checkout' || (string)$gateway_checkout['operation'] !== 'subscription')
							{
								c_ws_plugin__s2member_gateway_checkouts::processing_unlock($gateway_checkout_id, $gateway_checkout_lock);
								return array('__error' => 'gateway_checkout_invalid');
							}
							if(!empty($gateway_checkout['gateway_ids']['subscription_id']))
							{
								$subscription_id = (string)$gateway_checkout['gateway_ids']['subscription_id'];
								$status = !empty($gateway_checkout['gateway_status']) ? (string)$gateway_checkout['gateway_status'] : '';
								c_ws_plugin__s2member_gateway_checkouts::processing_unlock($gateway_checkout_id, $gateway_checkout_lock);
								return array('id' => $subscription_id, 'status' => $status);
							}
						}

						try
						{
							$plan_id = self::paypal_checkout_plan_get_id($token);
							if(!$plan_id)
								return array('__error' => 'plan_create_failed');

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

							//260901.2145 Coordinator-backed Pro-Forms use the logical checkout ID as PayPal's stable idempotency anchor; legacy callers retain the established invoice-derived key.
							$request_id = $gateway_checkout_id ? 's2m-ppco-sub-'.str_replace('-', '', $gateway_checkout_id) : 's2m-ppco-sub-'.md5($invoice);
							$headers = array('PayPal-Request-Id' => $request_id);

							if($gateway_checkout_id)
							{
								$context = !empty($gateway_checkout['context']) && is_array($gateway_checkout['context']) ? $gateway_checkout['context'] : array();
								$create_started_at = !empty($context['paypal_subscription_create_started_at']) ? (int)$context['paypal_subscription_create_started_at'] : 0;

								if($create_started_at && $create_started_at <= time() - (3 * DAY_IN_SECONDS))
								{
									//260902.0200 An unresolved server-created subscription could never reach buyer approval without its ID reaching the browser; after PayPal's 72-hour idempotency window, start a fresh approval-pending create instead of permanently blocking the checkout.
									unset($context['paypal_subscription_create_started_at'], $context['paypal_subscription_request_id']);
									$gateway_checkout = c_ws_plugin__s2member_gateway_checkouts::update($gateway_checkout_id, array('gateway_status' => '', 'context' => $context));
									if(!$gateway_checkout)
										return array('__error' => 'gateway_checkout_save_failed');
									$create_started_at = 0;
								}

								if(!$create_started_at)
								{
									$context['paypal_subscription_create_started_at'] = time();
									$context['paypal_subscription_request_id'] = $request_id;
									//260901.2145 Record an in-flight create before contacting PayPal so changed purchase terms cannot silently abandon an ambiguous subscription attempt.
									$gateway_checkout = c_ws_plugin__s2member_gateway_checkouts::update($gateway_checkout_id, array('gateway_status' => 'CREATE_PENDING', 'context' => $context));
									if(!$gateway_checkout)
										return array('__error' => 'gateway_checkout_save_failed');
								}
							}

							$data = array();
							$code = 0;
							$ambiguous = FALSE;
							for($attempt = 0; $attempt < 2; $attempt++)
							{
								$r = self::paypal_checkout_api_request('POST', '/v1/billing/subscriptions', $body, $headers);
								$code = !empty($r['code']) ? (int)$r['code'] : 0;
								$response_body = !empty($r['body']) ? (string)$r['body'] : '';
								$data = $response_body ? json_decode($response_body, true) : array();
								$data = is_array($data) ? $data : array();
								$ambiguous = ($code === 0 || $code === 408 || $code >= 500 || ($code >= 200 && $code <= 299));

								if($code >= 200 && $code <= 299 && !empty($data['id']))
									break;
								if(!$ambiguous)
									break;
							}

							if($gateway_checkout_id && $code >= 200 && $code <= 299 && !empty($data['id']))
							{
								$gateway_ids = !empty($gateway_checkout['gateway_ids']) && is_array($gateway_checkout['gateway_ids']) ? $gateway_checkout['gateway_ids'] : array();
								$gateway_ids['subscription_id'] = (string)$data['id'];
								$status = !empty($data['status']) ? strtoupper((string)$data['status']) : 'APPROVAL_PENDING';
								$context = !empty($gateway_checkout['context']) && is_array($gateway_checkout['context']) ? $gateway_checkout['context'] : array();
								unset($context['paypal_subscription_create_started_at'], $context['paypal_subscription_request_id']);

								//260901.2145 Persist the PayPal subscription ID before returning it to the browser; if persistence fails, retrying within PayPal's idempotency window recovers the same resource.
								if(!c_ws_plugin__s2member_gateway_checkouts::update($gateway_checkout_id, array('gateway_ids' => $gateway_ids, 'gateway_status' => $status, 'context' => $context)))
									return array('__error' => 'gateway_checkout_save_failed');
							}
							else if($gateway_checkout_id && !$ambiguous)
							{
								//260901.2145 A deterministic rejection did not create a subscription; clear the in-flight marker so a corrected attempt is not treated as an unresolved provider result.
								$context = !empty($gateway_checkout['context']) && is_array($gateway_checkout['context']) ? $gateway_checkout['context'] : array();
								unset($context['paypal_subscription_create_started_at'], $context['paypal_subscription_request_id']);
								c_ws_plugin__s2member_gateway_checkouts::update($gateway_checkout_id, array('gateway_status' => '', 'context' => $context));
							}

							//260902.0200 Preserve an ambiguous create as recoverable state so the browser can briefly wait for the independent CREATED webhook instead of repeatedly calling PayPal.
							if($gateway_checkout_id && $ambiguous && !($code >= 200 && $code <= 299 && !empty($data['id'])))
								return array('__error' => 'subscription_create_unresolved');

							return $data;
						}
						finally
						{
							if($gateway_checkout_id && $gateway_checkout_lock)
								c_ws_plugin__s2member_gateway_checkouts::processing_unlock($gateway_checkout_id, $gateway_checkout_lock);
						}
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
							'PAYMENT.CAPTURE.PENDING',
							'PAYMENT.CAPTURE.COMPLETED',
							'PAYMENT.CAPTURE.DENIED',
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
