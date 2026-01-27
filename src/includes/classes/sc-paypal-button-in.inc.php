<?php
// @codingStandardsIgnoreFile
/**
* Shortcode `[s2Member-PayPal-Button]` (inner processing routines).
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

if (!class_exists ("c_ws_plugin__s2member_sc_paypal_button_in"))
	{
		/**
		* Shortcode `[s2Member-PayPal-Button]` (inner processing routines).
		*
		* @package s2Member\PayPal
		* @since 3.5
		*/
		class c_ws_plugin__s2member_sc_paypal_button_in
			{
				/**
				* Handles the Shortcode for: `[s2Member-PayPal-Button /]`.
				*
				* @package s2Member\PayPal
				* @since 3.5
				*
				* @attaches-to ``add_shortcode("s2Member-PayPal-Button");``
				*
				* @param array $attr An array of Attributes.
				* @param string $content Content inside the Shortcode.
				* @param string $shortcode The actual Shortcode name itself.
				* @return string The resulting PayPal Button Code.
				*/
				public static function sc_paypal_button ($attr = FALSE, $content = FALSE, $shortcode = FALSE)
					{
						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_before_sc_paypal_button", get_defined_vars ());
						unset($__refs, $__v);

						c_ws_plugin__s2member_no_cache::no_cache_constants /* No caching on pages that contain this Payment Button. */ (true);

						$attr = /* Force array. Trim quote entities. */ c_ws_plugin__s2member_utils_strings::trim_qts_deep ((array)$attr);

						$attr = shortcode_atts (apply_filters("ws_plugin__s2member_sc_paypal_button_default_attrs", array("ids" => "0", "exp" => "72", "level" => "1", "ccaps" => "", "desc" => "", "ps" => "paypal", "lc" => "", "lang" => "", "cc" => "USD", "dg" => "0", "ns" => "1", "custom" => $_SERVER["HTTP_HOST"], "ta" => "0", "tp" => "0", "tt" => "D", "ra" => "0.01", "rp" => "1", "rt" => "M", "rr" => "1", "rrt" => "", "rra" => "1", "modify" => "0", "cancel" => "0", "sp" => "0", "image" => "default", "output" => "button"), get_defined_vars ()), $attr);

						// "modify" has been deprecated. https://developer.paypal.com/docs/classic/paypal-payments-standard/integration-guide/Appx-websitestandard-htmlvariables/#deprecated-variables
						$attr["modify"] = "0";

						$attr["lc"] = /* Locale code absolutely must be provided in upper-case format. Only after running shortcode_atts(). */ strtoupper ($attr["lc"]);
						$attr["tt"] = /* Term lengths absolutely must be provided in upper-case format. Only after running shortcode_atts(). */ strtoupper ($attr["tt"]);
						$attr["rt"] = /* Term lengths absolutely must be provided in upper-case format. Only after running shortcode_atts(). */ strtoupper ($attr["rt"]);
						$attr["rr"] = /* Must be provided in upper-case format. Numerical, or BN value. Only after running shortcode_atts(). */ strtoupper ($attr["rr"]);
						$attr["ccaps"] = /* Custom Capabilities must be typed in lower-case format. Only after running shortcode_atts(). */ strtolower ($attr["ccaps"]);
						$attr["ccaps"] = /* Custom Capabilities should not have spaces. */ str_replace(" ", "", $attr["ccaps"]);
						$attr["rr"] = /* Lifetime Subscriptions require Buy Now. Only after running shortcode_atts(). */ ($attr["rt"] === "L") ? "BN" : $attr["rr"];
						$attr["rr"] = /* Independent Ccaps require Buy Now. Only after running shortcode_atts(). */ ($attr["level"] === "*") ? "BN" : $attr["rr"];
						$attr["ns"] = /* No shipping directive must be 1 for digital items. After shortcode_atts(). */ ($attr["dg"] === "1") ? "1" : $attr["ns"];

						$force_notify_url_scheme = apply_filters("ws_plugin__s2member_during_sc_paypal_button_force_notify_url_scheme", null, get_defined_vars ());
						$force_return_url_scheme = apply_filters("ws_plugin__s2member_during_sc_paypal_button_force_return_url_scheme", null, get_defined_vars ());

						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_before_sc_paypal_button_after_shortcode_atts", get_defined_vars ());
						unset($__refs, $__v);

						if /* Cancellation Buttons. */ ($attr["cancel"])
							{
								//260114 PayPal Checkout (REST) cancellation override.
								// - output="button": on-site cancel via REST API (logged-in users only).
								// - output="anchor|url": link to PayPal subscription management UI (sandbox/live aware).
								// Falls back to legacy PayPal cancellation flow when user is not logged in or has no subscription id.
								if(c_ws_plugin__s2member_paypal_utilities::paypal_checkout_is_enabled() && in_array($attr["output"], array("button", "anchor", "url"), true) && is_user_logged_in() && get_user_option('s2member_subscr_id', (int)get_current_user_id()))
									{
										$user_id   = (int)get_current_user_id();
										$subscr_id = (string)get_user_option('s2member_subscr_id', $user_id);

										if($subscr_id)
											{
												$ppco_sandbox  = c_ws_plugin__s2member_paypal_utilities::paypal_checkout_is_sandbox();
												$pp_manage_url = ($ppco_sandbox) ? 'https://www.sandbox.paypal.com/myaccount/autopay/connect/' : 'https://www.paypal.com/myaccount/autopay/connect/';

												// output="url": return the PayPal management URL.
												if($attr["output"] === "url")
													{
														$code = $_code = $pp_manage_url;

														foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
														do_action("ws_plugin__s2member_during_sc_paypal_cancellation_button", get_defined_vars ());
														unset($__refs, $__v);

														$code = c_ws_plugin__s2member_sc_paypal_button_e::sc_paypal_button_encryption ($code, get_defined_vars ());
														return apply_filters("ws_plugin__s2member_sc_paypal_button", $code, get_defined_vars ());
													}
												// output="anchor": real anchor tag to PayPal management UI (no JS).
												else if($attr["output"] === "anchor")
													{
														$default_image = "https://www.paypal.com/" . (($attr["lang"]) ? $attr["lang"] : _x ("en_US", "s2member-front paypal-button-lang-code", "s2member")) . "/i/btn/btn_unsubscribe_LG.gif";
														$img_src = ($attr["image"] && $attr["image"] !== "default") ? $attr["image"] : $default_image;

														$code = $_code = '<a href="'.esc_attr($pp_manage_url).'" target="_blank" rel="nofollow noopener"><img src="'.esc_attr($img_src).'" style="width:auto; height:auto; border:0;" alt="PayPal" /></a>';

														foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
														do_action("ws_plugin__s2member_during_sc_paypal_cancellation_button", get_defined_vars ());
														unset($__refs, $__v);

														$code = c_ws_plugin__s2member_sc_paypal_button_e::sc_paypal_button_encryption ($code, get_defined_vars ());
														return apply_filters("ws_plugin__s2member_sc_paypal_button", $code, get_defined_vars ());
													}

												// output="button": REST cancel (modern-looking HTML button).
												$ppco_endpoint = home_url("/?s2member_paypal_checkout=1", $force_notify_url_scheme);

												// Token must satisfy the standard token validator (invoice/ip/item_number/checksum).
												$ppco_invoice = 'cancel-'.$user_id.'-'.$subscr_id;

												$ppco_token = array(
													'exp'         => time() + 3600,
													'invoice'     => $ppco_invoice,
													'ip'          => c_ws_plugin__s2member_utils_ip::current(),
													'item_number' => $subscr_id,
													'checksum'    => md5($ppco_invoice.c_ws_plugin__s2member_utils_ip::current().$subscr_id),

													'user_id'     => $user_id,
													'subscr_id'   => $subscr_id,
												);

												$ppco_token = urlencode(c_ws_plugin__s2member_utils_encryption::encrypt(serialize($ppco_token)));
												$ppco_nonce = wp_create_nonce('s2m_ppco_cancel_'.$user_id);

												$ppco_btn_id = 's2member_ppco_cancel_'.md5($user_id.$subscr_id);
												$ppco_msg_id = 's2member_ppco_cancel_msg_'.md5($user_id.$subscr_id);

												$code  = '<style type="text/css">#'.esc_attr($ppco_btn_id).'{display:inline-flex;align-items:center;justify-content:center;width:150px;height:40px;padding:10px 0;border-radius:4px;border:1px solid rgba(0,0,0,0.06);background:#ffc439;color:#003087;font-family:Helvetica, Arial, sans-serif;font-size:14px;font-weight:600;cursor:pointer;box-sizing:border-box;white-space:nowrap;}#'.esc_attr($ppco_btn_id).':hover{filter:brightness(0.98);}#'.esc_attr($ppco_btn_id).':disabled{opacity:0.65;cursor:not-allowed;}</style>'."\n";
												$code .= '<button type="button" id="'.esc_attr($ppco_btn_id).'">Unsubscribe</button>'."\n";
												$code .= '<div id="'.esc_attr($ppco_msg_id).'" style="display:none; margin-top:8px;"></div>'."\n";
												$code .= '<script type="text/javascript">'."\n";
												$code .= '(function(){'."\n";
												$code .= 'var b=document.getElementById("'.esc_js($ppco_btn_id).'");'."\n";
												$code .= 'var m=document.getElementById("'.esc_js($ppco_msg_id).'");'."\n";
												$code .= 'function show(msg){try{if(m){m.style.display="block";m.innerHTML=msg;}}catch(e){}}'."\n";
												$code .= 'function enc(o){var s=[];for(var k in o){if(!o.hasOwnProperty(k))continue;s.push(encodeURIComponent(k)+"="+encodeURIComponent(o[k]));}return s.join("&");}'."\n";
												$code .= 'if(!b){return;}'."\n";
												$code .= 'b.addEventListener("click",function(e){'."\n";
												$code .= 'e.preventDefault();'."\n";
												$code .= 'if(b.disabled){return;}'."\n";
												$code .= 'if(!window.confirm("Cancel your subscription now?")){return;}'."\n";
												$code .= 'b.disabled=true;'."\n";
												$code .= 'fetch("'.esc_js($ppco_endpoint).'",{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"},body:enc({s2member_paypal_checkout_op:"cancel_subscription",s2member_paypal_checkout_t:"'.esc_js($ppco_token).'",s2member_paypal_checkout_nonce:"'.esc_js($ppco_nonce).'"} )})'."\n";
												$code .= '.then(function(r){return r.json();})'."\n";
												$code .= '.then(function(res){if(res&&res.ok){show("Subscription cancelled.");}else{show("Unable to cancel subscription. Please contact support."); b.disabled=false;}})'."\n";
												$code .= '.catch(function(){show("Unable to cancel subscription. Please contact support."); b.disabled=false;});'."\n";
												$code .= '});'."\n";
												$code .= '})();'."\n";
												$code .= '</script>'."\n";
												$_code = $code;

												foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
												do_action("ws_plugin__s2member_during_sc_paypal_cancellation_button", get_defined_vars ());
												unset($__refs, $__v);

												$code = c_ws_plugin__s2member_sc_paypal_button_e::sc_paypal_button_encryption ($code, get_defined_vars ());
												return apply_filters("ws_plugin__s2member_sc_paypal_button", $code, get_defined_vars ());
											}
									}
								else if(c_ws_plugin__s2member_paypal_utilities::paypal_checkout_is_enabled() && in_array($attr["output"], array("button", "anchor"), true))
									{
										// Guest user, or logged-in without a subscription id: show a disabled PayPal-styled button (no action).
										$ppco_btn_id = 's2member_ppco_cancel_disabled_'.md5((string)microtime(true));
										$code  = '<style type="text/css">#'.esc_attr($ppco_btn_id).'{display:inline-flex;align-items:center;justify-content:center;width:150px;height:40px;padding:10px 0;border-radius:4px;border:1px solid rgba(0,0,0,0.06);background:#ffc439;color:#003087;font-family:Helvetica, Arial, sans-serif;font-size:14px;font-weight:600;cursor:not-allowed;box-sizing:border-box;opacity:0.55;white-space:nowrap;}</style>'."\n";
										$code .= '<button type="button" id="'.esc_attr($ppco_btn_id).'" disabled="disabled">Unsubscribe</button>'."\n";
										$_code = $code;

										foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
										do_action("ws_plugin__s2member_during_sc_paypal_cancellation_button", get_defined_vars ());
										unset($__refs, $__v);

										$code = c_ws_plugin__s2member_sc_paypal_button_e::sc_paypal_button_encryption ($code, get_defined_vars ());
										return apply_filters("ws_plugin__s2member_sc_paypal_button", $code, get_defined_vars ());
									}

								$default_image = "https://www.paypal.com/" . (($attr["lang"]) ? $attr["lang"] : _x ("en_US", "s2member-front paypal-button-lang-code", "s2member")) . "/i/btn/btn_unsubscribe_LG.gif";

								$code = trim (c_ws_plugin__s2member_utilities::evl (file_get_contents (dirname (dirname (__FILE__)) . "/templates/buttons/paypal-cancellation-button.php")));
								$code = preg_replace ("/%%images%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["dir_url"] . "/src/images")), $code);
								$code = preg_replace ("/%%wpurl%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr (home_url ())), $code);

								$endpoint_host = ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_sandbox"]) ? "www.sandbox.paypal.com" : "www.paypal.com";
								if(c_ws_plugin__s2member_paypal_utilities::paypal_checkout_is_enabled())
									$endpoint_host = c_ws_plugin__s2member_paypal_utilities::paypal_checkout_is_sandbox() ? "www.sandbox.paypal.com" : "www.paypal.com";

								$code = preg_replace ("/%%endpoint%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($endpoint_host)), $code);
								$code = preg_replace ("/%%paypal_business%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_business"])), $code);
								$code = preg_replace ("/%%paypal_merchant_id%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_merchant_id"])), $code);

								$code = $_code = ($attr["image"] && $attr["image"] !== "default") ? preg_replace ('/ src\="(.*?)"/', ' src="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["image"])) . '"', $code) : preg_replace ('/ src\="(.*?)"/', ' src="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($default_image)) . '"', $code);

								$code = ($attr["output"] === "anchor") ? /* Already in anchor format; `button` format is not used in Cancellations. */ $code : $code;
								if ($attr["output"] === "url" && preg_match ('/ href\="(.*?)"/', $code, $m) && ($href = $m[1]))
									$code = ($url = c_ws_plugin__s2member_utils_urls::n_amps ($href));

								unset /* Just a little housekeeping */ ($href, $url, $m);

								foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
								do_action("ws_plugin__s2member_during_sc_paypal_cancellation_button", get_defined_vars ());
								unset($__refs, $__v);
							}
						else if /* Specific Post/Page Buttons. */ ($attr["sp"])
							{
								$default_image = "https://www.paypal.com/" . (($attr["lang"]) ? $attr["lang"] : _x ("en_US", "s2member-front paypal-button-lang-code", "s2member")) . "/i/btn/btn_xpressCheckout.gif";

								$paypal_on0_input_value = ($referencing = c_ws_plugin__s2member_utils_users::get_user_subscr_or_wp_id ()) ? "Referencing Customer ID" : "Originating Domain";
								$paypal_os0_input_value = /* Current User's Paid Subscr. ID, or WP User ID, or domain. */ ($referencing) ? $referencing : $_SERVER["HTTP_HOST"];

								$paypal_on1_input_value = /* Identifies the Customer's IP Address for tracking purposes. */ "Customer IP Address";
								$paypal_os1_input_value = /* Current User's IP Address for tracking purposes. */ c_ws_plugin__s2member_utils_ip::current();

								$paypal_invoice_input_value = /* s2Member's Unique Code~IP combo. */ uniqid () . "~" . c_ws_plugin__s2member_utils_ip::current();

								$attr["sp_ids_exp"] = /* Combined "sp:ids:expiration hours". */ "sp:" . $attr["ids"] . ":" . $attr["exp"];

								$success_return_url = /* s2Member handles this all by itself. However, it can be Filtered. */ home_url ("/?s2member_paypal_return=1", $force_return_url_scheme);
								$success_return_url = apply_filters("ws_plugin__s2member_during_sc_paypal_button_success_return_url", $success_return_url, get_defined_vars ());

								//260114 PayPal Checkout (REST) SP (Specific Post/Page) override.
								// Uses server-side create + server-side capture, then posts into existing IPN + Return handlers.
								if(c_ws_plugin__s2member_paypal_utilities::paypal_checkout_is_enabled())
									{
										static $ppco_sdks = array();

										$ppco_sandbox   = c_ws_plugin__s2member_paypal_utilities::paypal_checkout_is_sandbox();
										$ppco_client_id = (string)$GLOBALS["WS_PLUGIN__"]["s2member"]["o"][($ppco_sandbox) ? "paypal_checkout_sandbox_client_id" : "paypal_checkout_client_id"];

										$ppco_cc     = strtoupper((string)$attr["cc"]);
										$ppco_intent = 'capture';

										$ppco_sdk_key = (($ppco_sandbox) ? 'sandbox' : 'live').'|'.$ppco_cc.'|'.$ppco_intent;

										$ppco_sdk_just_loaded = false;
										if(empty($ppco_sdks[$ppco_sdk_key]) || !is_array($ppco_sdks[$ppco_sdk_key]))
											{
												$ppco_sdks[$ppco_sdk_key] = array(
													'ns' => 's2m_ppco_'.substr(md5($ppco_sdk_key), 0, 10),
													'id' => 's2member_ppco_sdk_'.substr(md5($ppco_sdk_key), 0, 10),
												);
												$ppco_sdk_just_loaded = true;
											}

										$ppco_sdk_ns = (string)$ppco_sdks[$ppco_sdk_key]['ns'];
										$ppco_sdk_id = (string)$ppco_sdks[$ppco_sdk_key]['id'];

										$ppco_endpoint = home_url("/?s2member_paypal_checkout=1", $force_notify_url_scheme);
										$ppco_cancel   = home_url("/");

										$ppco_token = array(
											'exp'         => time() + 3600,

											'invoice'     => $paypal_invoice_input_value,
											'ip'          => c_ws_plugin__s2member_utils_ip::current(),
											'item_name'   => $attr["desc"],
											'item_number' => $attr["sp_ids_exp"],
											'custom'      => $attr["custom"],

											'amount'      => $attr["ra"],
											'cc'          => $ppco_cc,
											'ns'          => $attr["ns"],

											'rr'          => 'BN',

											'on0'         => $paypal_on0_input_value,
											'os0'         => $paypal_os0_input_value,
											'on1'         => $paypal_on1_input_value,
											'os1'         => $paypal_os1_input_value,

											'return'      => $success_return_url,
											'cancel'      => $ppco_cancel,

											'checksum'    => md5($paypal_invoice_input_value.c_ws_plugin__s2member_utils_ip::current().$attr["sp_ids_exp"]),
										);

										$ppco_token = urlencode(c_ws_plugin__s2member_utils_encryption::encrypt(serialize($ppco_token)));

										// output="anchor|url" support (no JS SDK; redirects through s2Member, then to PayPal approval URL).
										if($attr["output"] === "anchor" || $attr["output"] === "url")
										{
											$ppco_redirect_url = $ppco_endpoint.'&s2member_paypal_checkout_op=redirect&s2member_paypal_checkout_t='.$ppco_token;

											if($attr["output"] === "url")
												$code = $_code = $ppco_redirect_url;

											else // output="anchor"
											{
												$img_src = ($attr["image"] && $attr["image"] !== "default") ? $attr["image"] : $default_image;
												$code = $_code = '<a href="'.esc_attr($ppco_redirect_url).'"><img src="'.esc_attr($img_src).'" style="width:auto; height:auto; border:0;" alt="PayPal" /></a>';
											}

											foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
											do_action("ws_plugin__s2member_during_sc_paypal_sp_button", get_defined_vars ());
											unset($__refs, $__v);

											$code = c_ws_plugin__s2member_sc_paypal_button_e::sc_paypal_button_encryption ($code, get_defined_vars ());
											return apply_filters("ws_plugin__s2member_sc_paypal_button", $code, get_defined_vars ());
										}

										$ppco_div_id = 's2member_ppco_btn_'.md5($paypal_invoice_input_value.$attr["sp_ids_exp"]);
										$ppco_err_id = 's2member_ppco_err_'.md5($paypal_invoice_input_value.$attr["sp_ids_exp"]);

										$ppco_locale = ($attr["lang"]) ? (string)$attr["lang"] : '';
										$ppco_lc     = ($attr["lc"]) ? (string)$attr["lc"] : '';

										if(!$ppco_locale && $ppco_lc)
										{
											$site_locale = (string)get_locale();
											if(preg_match('/^([a-z]{2})[_-]/i', $site_locale, $m))
												$ppco_locale = strtolower($m[1]).'_'.strtoupper($ppco_lc);
											else
												$ppco_locale = 'en_'.strtoupper($ppco_lc);
										}

										$ppco_locale_q        = ($ppco_locale) ? '&locale='.rawurlencode($ppco_locale) : '';
										$ppco_buyer_country_q = ($ppco_sandbox && $ppco_lc) ? '&buyer-country='.rawurlencode(strtoupper($ppco_lc)) : '';

										$ppco_sdk_src = ($ppco_sandbox ? 'https://www.sandbox.paypal.com/sdk/js' : 'https://www.paypal.com/sdk/js');
										//260114 !!! SP is capture-only; conditional subscription path below is dead code (cleanup/refactor later).
										if($ppco_intent === 'subscription')
											$ppco_sdk_src = $ppco_sdk_src.'?client-id='.rawurlencode($ppco_client_id).'&currency='.rawurlencode($ppco_cc).'&vault=true&intent=subscription&disable-funding=card'.$ppco_buyer_country_q.$ppco_locale_q;
										else
											$ppco_sdk_src = $ppco_sdk_src.'?client-id='.rawurlencode($ppco_client_id).'&currency='.rawurlencode($ppco_cc).'&intent=capture&commit=true&disable-funding=card'.$ppco_buyer_country_q.$ppco_locale_q;

										$code  = '<div id="'.esc_attr($ppco_div_id).'" style="max-width:145px; width:auto;"></div>'."\n";
										$code .= '<div id="'.esc_attr($ppco_err_id).'" style="display:none;"></div>'."\n";

										if($ppco_sdk_just_loaded)
											{
												$code .= '<script id="'.esc_attr($ppco_sdk_id).'" data-namespace="'.esc_attr($ppco_sdk_ns).'" src="'.esc_attr($ppco_sdk_src).'"></script>'."\n";
											}

										$code .= '<script type="text/javascript">'."\n";
										$code .= '(function(){'."\n";
										$code .= 'var d="'.esc_js($ppco_div_id).'";'."\n";
										$code .= 'var e="'.esc_js($ppco_err_id).'";'."\n";
										$code .= 'var t="'.esc_js($ppco_token).'";'."\n";
										$code .= 'var u="'.esc_js($ppco_endpoint).'";'."\n";
										$code .= 'var ns="'.esc_js($ppco_sdk_ns).'";'."\n";
										$code .= 'var cid="'.esc_js($paypal_invoice_input_value).'";'."\n";
										$code .= 'function showErr(m){try{var el=document.getElementById(e);if(el){el.style.display="block";el.innerHTML=m;}}catch(x){}}'."\n";
										$code .= 'function postTo(url, data){var f=document.createElement("form");f.method="post";f.action=url;for(var k in data){if(!data.hasOwnProperty(k))continue;var i=document.createElement("input");i.type="hidden";i.name=k;i.value=data[k];f.appendChild(i);}document.body.appendChild(f);f.submit();}'."\n";
										$code .= 'function enc(o){var s=[];for(var k in o){if(!o.hasOwnProperty(k))continue;s.push(encodeURIComponent(k)+"="+encodeURIComponent(o[k]));}return s.join("&");}'."\n";
										if($ppco_intent === 'subscription')
											{
												$code .= 'function getPlanId(){return fetch(u,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"},body:enc({s2member_paypal_checkout_op:"get_plan_id",s2member_paypal_checkout_t:t})}).then(function(r){return r.json();}).then(function(res){if(res&&res.plan_id)return res.plan_id;throw(res&&res.error?res.error:"plan_get_failed");});}'."\n";
												$code .= 'var planId=null;'."\n";
												$code .= 'function createSubscription(data,actions){if(planId)return actions.subscription.create({plan_id:planId,custom_id:cid,application_context:{shipping_preference:"NO_SHIPPING"}});return getPlanId().then(function(pid){planId=pid;return actions.subscription.create({plan_id:planId,custom_id:cid,application_context:{shipping_preference:"NO_SHIPPING"}});});}'."\n";
												$code .= 'function onApprove(data){return fetch(u,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"},body:enc({s2member_paypal_checkout_op:"confirm_subscription",s2member_paypal_checkout_t:t,subscription_id:(data&&data.subscriptionID?data.subscriptionID:"")})}).then(function(r){return r.json();}).then(function(res){if(res&&res.rtn_url&&res.rtn_post){postTo(res.rtn_url,res.rtn_post);return;}throw(res&&res.error?res.error:"subscription_confirm_failed");}).catch(function(e){showErr("Subscription could not be completed. Please try again.");});}'."\n";
												$code .= 'function onCancel(){showErr("Subscription cancelled.");}'."\n";
												$code .= 'function onError(err){var m="PayPal error. Please try again.";try{if(err){if(typeof err==="string")m="PayPal error: "+err;else if(err.message)m="PayPal error: "+err.message;}}catch(x){}showErr(m);}'."\n";
												$code .= 'function init(){var P=window[ns];if(!P||!P.Buttons){showErr("PayPal SDK failed to load.");return;}P.Buttons({fundingSource:P.FUNDING.PAYPAL,style:{layout:"vertical",tagline:false,height:40},createSubscription:createSubscription,onApprove:onApprove,onCancel:onCancel,onError:onError}).render("#"+d);}'."\n";
											}
										else
											{
												$code .= 'function createOrder(){return fetch(u,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"},body:enc({s2member_paypal_checkout_op:"create_order",s2member_paypal_checkout_t:t})}).then(function(r){return r.json();}).then(function(res){if(res&&res.order_id)return res.order_id;throw(res&&res.error?res.error:"order_create_failed");});}'."\n";
												$code .= 'function onApprove(data){return fetch(u,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"},body:enc({s2member_paypal_checkout_op:"capture_order",s2member_paypal_checkout_t:t,order_id:(data&&data.orderID?data.orderID:"")})}).then(function(r){return r.json();}).then(function(res){if(res&&res.rtn_url&&res.rtn_post){postTo(res.rtn_url,res.rtn_post);return;}throw(res&&res.error?res.error:"order_capture_failed");}).catch(function(e){showErr("Payment could not be completed. Please try again.");});}'."\n";
												$code .= 'function onCancel(){showErr("Payment cancelled.");}'."\n";
												$code .= 'function onError(err){var m="PayPal error. Please try again.";try{if(err){if(typeof err==="string")m="PayPal error: "+err;else if(err.message)m="PayPal error: "+err.message;}}catch(x){}showErr(m);}'."\n";
												$code .= 'function init(){var P=window[ns];if(!P||!P.Buttons){showErr("PayPal SDK failed to load.");return;}P.Buttons({fundingSource:P.FUNDING.PAYPAL,style:{layout:"vertical",tagline:false,height:40},createOrder:createOrder,onApprove:onApprove,onCancel:onCancel,onError:onError}).render("#"+d);}'."\n";
											}
										$code .= 'if(document.readyState==="complete"){init();}else{window.addEventListener("load",init);}'."\n";
										$code .= '})();'."\n";
										$code .= '</script>'."\n";
										$_code = $code;

										foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
										do_action("ws_plugin__s2member_during_sc_paypal_sp_button", get_defined_vars ());
										unset($__refs, $__v);

										$code = c_ws_plugin__s2member_sc_paypal_button_e::sc_paypal_button_encryption ($code, get_defined_vars ());
										return apply_filters("ws_plugin__s2member_sc_paypal_button", $code, get_defined_vars ());
									}

								$code = trim (c_ws_plugin__s2member_utilities::evl (file_get_contents (dirname (dirname (__FILE__)) . "/templates/buttons/paypal-sp-checkout-button.php")));
								$code = preg_replace ("/%%images%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["dir_url"] . "/src/images")), $code);
								$code = preg_replace ("/%%wpurl%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr (home_url ())), $code);

								$code = preg_replace ("/%%endpoint%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr (($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_sandbox"]) ? "www.sandbox.paypal.com" : "www.paypal.com")), $code);
								$code = preg_replace ("/%%paypal_business%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_business"])), $code);
								$code = preg_replace ("/%%paypal_merchant_id%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_merchant_id"])), $code);
								$code = preg_replace ("/%%cancel_return%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr (home_url ("/"))), $code);
								$code = preg_replace ("/%%notify_url%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr (home_url ("/?s2member_paypal_notify=1", $force_notify_url_scheme))), $code);
								$code = preg_replace ("/%%return%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($success_return_url)), $code);
								$code = preg_replace ("/%%custom%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["custom"])), $code);

								$code = preg_replace ('/ name\="lc" value\="(.*?)"/', ' name="lc" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["lc"])) . '"', $code);
								$code = preg_replace ('/ name\="no_shipping" value\="(.*?)"/', ' name="no_shipping" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["ns"])) . '"', $code);
								$code = preg_replace ('/ name\="item_name" value\="(.*?)"/', ' name="item_name" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["desc"])) . '"', $code);
								$code = preg_replace ('/ name\="item_number" value\="(.*?)"/', ' name="item_number" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["sp_ids_exp"])) . '"', $code);
								$code = preg_replace ('/ name\="page_style" value\="(.*?)"/', ' name="page_style" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["ps"])) . '"', $code);
								$code = preg_replace ('/ name\="currency_code" value\="(.*?)"/', ' name="currency_code" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["cc"])) . '"', $code);
								$code = preg_replace ('/ name\="custom" value\="(.*?)"/', ' name="custom" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["custom"])) . '"', $code);

								$code = preg_replace ('/ name\="invoice" value\="(.*?)"/', ' name="invoice" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($paypal_invoice_input_value)) . '"', $code);

								$code = preg_replace ('/ name\="on0" value\="(.*?)"/', ' name="on0" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($paypal_on0_input_value)) . '"', $code);
								$code = preg_replace ('/ name\="os0" value\="(.*?)"/', ' name="os0" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($paypal_os0_input_value)) . '"', $code);
								$code = preg_replace ('/ name\="on1" value\="(.*?)"/', ' name="on1" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($paypal_on1_input_value)) . '"', $code);
								$code = preg_replace ('/ name\="os1" value\="(.*?)"/', ' name="os1" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($paypal_os1_input_value)) . '"', $code);

								$code = preg_replace ('/ name\="amount" value\="(.*?)"/', ' name="amount" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["ra"])) . '"', $code);

								$code = $_code = ($attr["image"] && $attr["image"] !== "default") ? preg_replace ('/ src\="(.*?)"/', ' src="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["image"])) . '"', $code) : preg_replace ('/ src\="(.*?)"/', ' src="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($default_image)) . '"', $code);

								$code = ($attr["output"] === "anchor") ? '<a href="' . esc_attr (c_ws_plugin__s2member_utils_forms::form_whips_2_url ($code)) . '"><img src="' . esc_attr (($attr["image"] && $attr["image"] !== "default") ? $attr["image"] : $default_image) . '" style="width:auto; height:auto; border:0;" alt="PayPal" /></a>' : $code;
								$code = ($attr["output"] === "url") ? c_ws_plugin__s2member_utils_forms::form_whips_2_url ($code) : $code;

								foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
								do_action("ws_plugin__s2member_during_sc_paypal_sp_button", get_defined_vars ());
								unset($__refs, $__v);
							}
						else if /* Independent Custom Capabilities. */ ($attr["level"] === "*")
							{
								$default_image = "https://www.paypal.com/" . (($attr["lang"]) ? $attr["lang"] : _x ("en_US", "s2member-front paypal-button-lang-code", "s2member")) . "/i/btn/btn_xpressCheckout.gif";

								$paypal_on0_input_value = ($referencing = c_ws_plugin__s2member_utils_users::get_user_subscr_or_wp_id ()) ? "Referencing Customer ID" : "Originating Domain";
								$paypal_os0_input_value = /* Current User's Paid Subscr. ID, or WP User ID, or domain. */ ($referencing) ? $referencing : $_SERVER["HTTP_HOST"];

								$paypal_on1_input_value = /* Identifies the Customer's IP Address for tracking purposes. */ "Customer IP Address";
								$paypal_os1_input_value = /* Current User's IP Address for tracking purposes. */ c_ws_plugin__s2member_utils_ip::current();

								$paypal_invoice_input_value = /* s2Member's Unique Code~IP combo. */ uniqid () . "~" . c_ws_plugin__s2member_utils_ip::current();

								$attr["level_ccaps_eotper"] = ($attr["rr"] === "BN" && $attr["rt"] !== "L") ? $attr["level"] . ":" . $attr["ccaps"] . ":" . $attr["rp"] . " " . $attr["rt"] : $attr["level"] . ":" . $attr["ccaps"];
								$attr["level_ccaps_eotper"] = /* Clean any trailing separators from this string. */ rtrim ($attr["level_ccaps_eotper"], ":");

								$success_return_url = /* s2Member handles this all by itself. However, it can be Filtered. */ home_url ("/?s2member_paypal_return=1", $force_return_url_scheme);
								$success_return_url = apply_filters("ws_plugin__s2member_during_sc_paypal_button_success_return_url", $success_return_url, get_defined_vars ());


								//260114 PayPal Checkout (REST) CCAPS (Independent Custom Capabilities) override.
								// Uses server-side create + server-side capture, then posts into existing IPN + Return handlers.
								if(c_ws_plugin__s2member_paypal_utilities::paypal_checkout_is_enabled())
									{
										static $ppco_sdks = array();

										$ppco_sandbox   = c_ws_plugin__s2member_paypal_utilities::paypal_checkout_is_sandbox();
										$ppco_client_id = (string)$GLOBALS["WS_PLUGIN__"]["s2member"]["o"][($ppco_sandbox) ? "paypal_checkout_sandbox_client_id" : "paypal_checkout_client_id"];

										$ppco_cc     = strtoupper((string)$attr["cc"]);
										$ppco_intent = 'capture';

										$ppco_sdk_key = (($ppco_sandbox) ? 'sandbox' : 'live').'|'.$ppco_cc.'|'.$ppco_intent;

										$ppco_sdk_just_loaded = false;
										if(empty($ppco_sdks[$ppco_sdk_key]) || !is_array($ppco_sdks[$ppco_sdk_key]))
											{
												$ppco_sdks[$ppco_sdk_key] = array(
													'ns' => 's2m_ppco_'.substr(md5($ppco_sdk_key), 0, 10),
													'id' => 's2member_ppco_sdk_'.substr(md5($ppco_sdk_key), 0, 10),
												);
												$ppco_sdk_just_loaded = true;
											}

										$ppco_sdk_ns = (string)$ppco_sdks[$ppco_sdk_key]['ns'];
										$ppco_sdk_id = (string)$ppco_sdks[$ppco_sdk_key]['id'];

										$ppco_endpoint = home_url("/?s2member_paypal_checkout=1", $force_notify_url_scheme);
										$ppco_cancel   = home_url("/");

										$ppco_token = array(
											'exp'         => time() + 3600,

											'invoice'     => $paypal_invoice_input_value,
											'ip'          => c_ws_plugin__s2member_utils_ip::current(),
											'item_name'   => $attr["desc"],
											'item_number' => $attr["level_ccaps_eotper"],
											'custom'      => $attr["custom"],

											'amount'      => $attr["ra"],
											'cc'          => $ppco_cc,
											'ns'          => $attr["ns"],

											'rr'          => 'BN',

											'on0'         => $paypal_on0_input_value,
											'os0'         => $paypal_os0_input_value,
											'on1'         => $paypal_on1_input_value,
											'os1'         => $paypal_os1_input_value,

											'return'      => $success_return_url,
											'cancel'      => $ppco_cancel,

											'checksum'    => md5($paypal_invoice_input_value.c_ws_plugin__s2member_utils_ip::current().$attr["level_ccaps_eotper"]),
										);

										$ppco_token = urlencode(c_ws_plugin__s2member_utils_encryption::encrypt(serialize($ppco_token)));

										// output="anchor|url" support (no JS SDK; redirects through s2Member, then to PayPal approval URL).
										if($attr["output"] === "anchor" || $attr["output"] === "url")
										{
											$ppco_redirect_url = $ppco_endpoint.'&s2member_paypal_checkout_op=redirect&s2member_paypal_checkout_t='.$ppco_token;

											if($attr["output"] === "url")
												$code = $_code = $ppco_redirect_url;

											else // output="anchor"
											{
												$img_src = ($attr["image"] && $attr["image"] !== "default") ? $attr["image"] : $default_image;
												$code = $_code = '<a href="'.esc_attr($ppco_redirect_url).'"><img src="'.esc_attr($img_src).'" style="width:auto; height:auto; border:0;" alt="PayPal" /></a>';
											}

											foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
											do_action("ws_plugin__s2member_during_sc_paypal_ccaps_button", get_defined_vars ());
											unset($__refs, $__v);

											$code = c_ws_plugin__s2member_sc_paypal_button_e::sc_paypal_button_encryption ($code, get_defined_vars ());
											return apply_filters("ws_plugin__s2member_sc_paypal_button", $code, get_defined_vars ());
										}

										$ppco_div_id = 's2member_ppco_btn_'.md5($paypal_invoice_input_value.$attr["level_ccaps_eotper"]);
										$ppco_err_id = 's2member_ppco_err_'.md5($paypal_invoice_input_value.$attr["level_ccaps_eotper"]);

										$ppco_locale = ($attr["lang"]) ? (string)$attr["lang"] : '';
										$ppco_lc     = ($attr["lc"]) ? (string)$attr["lc"] : '';

										if(!$ppco_locale && $ppco_lc)
										{
											$site_locale = (string)get_locale();
											if(preg_match('/^([a-z]{2})[_-]/i', $site_locale, $m))
												$ppco_locale = strtolower($m[1]).'_'.strtoupper($ppco_lc);
											else
												$ppco_locale = 'en_'.strtoupper($ppco_lc);
										}

										$ppco_locale_q        = ($ppco_locale) ? '&locale='.rawurlencode($ppco_locale) : '';
										$ppco_buyer_country_q = ($ppco_sandbox && $ppco_lc) ? '&buyer-country='.rawurlencode(strtoupper($ppco_lc)) : '';

										$ppco_sdk_src = ($ppco_sandbox ? 'https://www.sandbox.paypal.com/sdk/js' : 'https://www.paypal.com/sdk/js');
										//260114 !!! Independent CCAPS is capture-only; conditional subscription path below is dead code (cleanup/refactor later).
										if($ppco_intent === 'subscription')
											$ppco_sdk_src = $ppco_sdk_src.'?client-id='.rawurlencode($ppco_client_id).'&currency='.rawurlencode($ppco_cc).'&vault=true&intent=subscription&disable-funding=card'.$ppco_buyer_country_q.$ppco_locale_q;
										else
											$ppco_sdk_src = $ppco_sdk_src.'?client-id='.rawurlencode($ppco_client_id).'&currency='.rawurlencode($ppco_cc).'&intent=capture&commit=true&disable-funding=card'.$ppco_buyer_country_q.$ppco_locale_q;

										$code  = '<div id="'.esc_attr($ppco_div_id).'" style="max-width:145px; width:auto;"></div>'."\n";
										$code .= '<div id="'.esc_attr($ppco_err_id).'" style="display:none;"></div>'."\n";

										if($ppco_sdk_just_loaded)
											{
												$code .= '<script id="'.esc_attr($ppco_sdk_id).'" data-namespace="'.esc_attr($ppco_sdk_ns).'" src="'.esc_attr($ppco_sdk_src).'"></script>'."\n";
											}

										$code .= '<script type="text/javascript">'."\n";
										$code .= '(function(){'."\n";
										$code .= 'var d="'.esc_js($ppco_div_id).'";'."\n";
										$code .= 'var e="'.esc_js($ppco_err_id).'";'."\n";
										$code .= 'var t="'.esc_js($ppco_token).'";'."\n";
										$code .= 'var u="'.esc_js($ppco_endpoint).'";'."\n";
										$code .= 'var ns="'.esc_js($ppco_sdk_ns).'";'."\n";
										$code .= 'var cid="'.esc_js($paypal_invoice_input_value).'";'."\n";
										$code .= 'function showErr(m){try{var el=document.getElementById(e);if(el){el.style.display="block";el.innerHTML=m;}}catch(x){}}'."\n";
										$code .= 'function postTo(url, data){var f=document.createElement("form");f.method="post";f.action=url;for(var k in data){if(!data.hasOwnProperty(k))continue;var i=document.createElement("input");i.type="hidden";i.name=k;i.value=data[k];f.appendChild(i);}document.body.appendChild(f);f.submit();}'."\n";
										$code .= 'function enc(o){var s=[];for(var k in o){if(!o.hasOwnProperty(k))continue;s.push(encodeURIComponent(k)+"="+encodeURIComponent(o[k]));}return s.join("&");}'."\n";
										if($ppco_intent === 'subscription')
											{
												$code .= 'function getPlanId(){return fetch(u,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"},body:enc({s2member_paypal_checkout_op:"get_plan_id",s2member_paypal_checkout_t:t})}).then(function(r){return r.json();}).then(function(res){if(res&&res.plan_id)return res.plan_id;throw(res&&res.error?res.error:"plan_get_failed");});}'."\n";
												$code .= 'var planId=null;'."\n";
												$code .= 'function createSubscription(data,actions){if(planId)return actions.subscription.create({plan_id:planId,custom_id:cid,application_context:{shipping_preference:"NO_SHIPPING"}});return getPlanId().then(function(pid){planId=pid;return actions.subscription.create({plan_id:planId,custom_id:cid,application_context:{shipping_preference:"NO_SHIPPING"}});});}'."\n";
												$code .= 'function onApprove(data){return fetch(u,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"},body:enc({s2member_paypal_checkout_op:"confirm_subscription",s2member_paypal_checkout_t:t,subscription_id:(data&&data.subscriptionID?data.subscriptionID:"")})}).then(function(r){return r.json();}).then(function(res){if(res&&res.rtn_url&&res.rtn_post){postTo(res.rtn_url,res.rtn_post);return;}throw(res&&res.error?res.error:"subscription_confirm_failed");}).catch(function(e){showErr("Subscription could not be completed. Please try again.");});}'."\n";
												$code .= 'function onCancel(){showErr("Subscription cancelled.");}'."\n";
												$code .= 'function onError(err){var m="PayPal error. Please try again.";try{if(err){if(typeof err==="string")m="PayPal error: "+err;else if(err.message)m="PayPal error: "+err.message;}}catch(x){}showErr(m);}'."\n";
												$code .= 'function init(){var P=window[ns];if(!P||!P.Buttons){showErr("PayPal SDK failed to load.");return;}P.Buttons({fundingSource:P.FUNDING.PAYPAL,style:{layout:"vertical",tagline:false,height:40},createSubscription:createSubscription,onApprove:onApprove,onCancel:onCancel,onError:onError}).render("#"+d);}'."\n";
											}
										else
											{
												$code .= 'function createOrder(){return fetch(u,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"},body:enc({s2member_paypal_checkout_op:"create_order",s2member_paypal_checkout_t:t})}).then(function(r){return r.json();}).then(function(res){if(res&&res.order_id)return res.order_id;throw(res&&res.error?res.error:"order_create_failed");});}'."\n";
												$code .= 'function onApprove(data){return fetch(u,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"},body:enc({s2member_paypal_checkout_op:"capture_order",s2member_paypal_checkout_t:t,order_id:(data&&data.orderID?data.orderID:"")})}).then(function(r){return r.json();}).then(function(res){if(res&&res.rtn_url&&res.rtn_post){postTo(res.rtn_url,res.rtn_post);return;}throw(res&&res.error?res.error:"order_capture_failed");}).catch(function(e){showErr("Payment could not be completed. Please try again.");});}'."\n";
												$code .= 'function onCancel(){showErr("Payment cancelled.");}'."\n";
												$code .= 'function onError(err){var m="PayPal error. Please try again.";try{if(err){if(typeof err==="string")m="PayPal error: "+err;else if(err.message)m="PayPal error: "+err.message;}}catch(x){}showErr(m);}'."\n";
												$code .= 'function init(){var P=window[ns];if(!P||!P.Buttons){showErr("PayPal SDK failed to load.");return;}P.Buttons({fundingSource:P.FUNDING.PAYPAL,style:{layout:"vertical",tagline:false,height:40},createOrder:createOrder,onApprove:onApprove,onCancel:onCancel,onError:onError}).render("#"+d);}'."\n";
											}
										$code .= 'if(document.readyState==="complete"){init();}else{window.addEventListener("load",init);}'."\n";
										$code .= '})();'."\n";
										$code .= '</script>'."\n";
										$_code = $code;

										foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
										do_action("ws_plugin__s2member_during_sc_paypal_ccaps_button", get_defined_vars ());
										unset($__refs, $__v);

										$code = c_ws_plugin__s2member_sc_paypal_button_e::sc_paypal_button_encryption ($code, get_defined_vars ());
										return apply_filters("ws_plugin__s2member_sc_paypal_button", $code, get_defined_vars ());
									}

								$code = trim (c_ws_plugin__s2member_utilities::evl (file_get_contents (dirname (dirname (__FILE__)) . "/templates/buttons/paypal-ccaps-checkout-button.php")));
								$code = preg_replace ("/%%images%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["dir_url"] . "/src/images")), $code);
								$code = preg_replace ("/%%wpurl%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr (home_url ())), $code);

								$code = preg_replace ("/%%endpoint%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr (($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_sandbox"]) ? "www.sandbox.paypal.com" : "www.paypal.com")), $code);
								$code = preg_replace ("/%%paypal_business%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_business"])), $code);
								$code = preg_replace ("/%%paypal_merchant_id%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_merchant_id"])), $code);
								$code = preg_replace ("/%%cancel_return%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr (home_url ("/"))), $code);
								$code = preg_replace ("/%%notify_url%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr (home_url ("/?s2member_paypal_notify=1", $force_notify_url_scheme))), $code);
								$code = preg_replace ("/%%return%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($success_return_url)), $code);
								$code = preg_replace ("/%%custom%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["custom"])), $code);

								$code = preg_replace ('/ name\="lc" value\="(.*?)"/', ' name="lc" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["lc"])) . '"', $code);
								$code = preg_replace ('/ name\="no_shipping" value\="(.*?)"/', ' name="no_shipping" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["ns"])) . '"', $code);
								$code = preg_replace ('/ name\="item_name" value\="(.*?)"/', ' name="item_name" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["desc"])) . '"', $code);
								$code = preg_replace ('/ name\="item_number" value\="(.*?)"/', ' name="item_number" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["level_ccaps_eotper"])) . '"', $code);
								$code = preg_replace ('/ name\="page_style" value\="(.*?)"/', ' name="page_style" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["ps"])) . '"', $code);
								$code = preg_replace ('/ name\="currency_code" value\="(.*?)"/', ' name="currency_code" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["cc"])) . '"', $code);
								$code = preg_replace ('/ name\="custom" value\="(.*?)"/', ' name="custom" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["custom"])) . '"', $code);

								$code = preg_replace ('/ name\="invoice" value\="(.*?)"/', ' name="invoice" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($paypal_invoice_input_value)) . '"', $code);

								$code = preg_replace ('/ name\="on0" value\="(.*?)"/', ' name="on0" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($paypal_on0_input_value)) . '"', $code);
								$code = preg_replace ('/ name\="os0" value\="(.*?)"/', ' name="os0" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($paypal_os0_input_value)) . '"', $code);
								$code = preg_replace ('/ name\="on1" value\="(.*?)"/', ' name="on1" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($paypal_on1_input_value)) . '"', $code);
								$code = preg_replace ('/ name\="os1" value\="(.*?)"/', ' name="os1" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($paypal_os1_input_value)) . '"', $code);

								$code = preg_replace ('/ name\="amount" value\="(.*?)"/', ' name="amount" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["ra"])) . '"', $code);

								$code = $_code = ($attr["image"] && $attr["image"] !== "default") ? preg_replace ('/ src\="(.*?)"/', ' src="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["image"])) . '"', $code) : preg_replace ('/ src\="(.*?)"/', ' src="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($default_image)) . '"', $code);

								$code = ($attr["output"] === "anchor") ? '<a href="' . esc_attr (c_ws_plugin__s2member_utils_forms::form_whips_2_url ($code)) . '"><img src="' . esc_attr (($attr["image"] && $attr["image"] !== "default") ? $attr["image"] : $default_image) . '" style="width:auto; height:auto; border:0;" alt="PayPal" /></a>' : $code;
								$code = ($attr["output"] === "url") ? c_ws_plugin__s2member_utils_forms::form_whips_2_url ($code) : $code;

								foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
								do_action("ws_plugin__s2member_during_sc_paypal_ccaps_button", get_defined_vars ());
								unset($__refs, $__v);
							}
						else // Otherwise, we'll process this Button normally, using Membership routines.
							{
								$default_image = "https://www.paypal.com/" . (($attr["lang"]) ? $attr["lang"] : _x ("en_US", "s2member-front paypal-button-lang-code", "s2member")) . "/i/btn/btn_xpressCheckout.gif";

								$paypal_on0_input_value = ($referencing = c_ws_plugin__s2member_utils_users::get_user_subscr_or_wp_id ()) ? "Referencing Customer ID" : "Originating Domain";
								$paypal_os0_input_value = /* Current User's Paid Subscr. ID, or WP User ID, or domain. */ ($referencing) ? $referencing : $_SERVER["HTTP_HOST"];

								$paypal_on1_input_value = /* Identifies the Customer's IP Address for tracking purposes. */ "Customer IP Address";
								$paypal_os1_input_value = /* Current User's IP Address for tracking purposes. */ c_ws_plugin__s2member_utils_ip::current();

								$paypal_invoice_input_value = /* s2Member's Unique Code~IP combo. */ uniqid () . "~" . c_ws_plugin__s2member_utils_ip::current();

								$attr["desc"] = (!$attr["desc"]) ? $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["level" . $attr["level"] . "_label"] : $attr["desc"];

								// PayPal Checkout: rr=0 with no trial/initial should behave like Buy Now (one-time),
								// so s2Member’s standard Buy Now/EOT routines run (mirrors other gateways).
								if(c_ws_plugin__s2member_paypal_utilities::paypal_checkout_is_enabled()
								   && (string)$attr["rr"] === '0'
								   && (float)$attr["ta"] <= 0
								   && (int)$attr["tp"] <= 0)
									{
										$attr["rr"] = 'BN';
									}

								$attr["level_ccaps_eotper"] = ($attr["rr"] === "BN" && $attr["rt"] !== "L") ? $attr["level"] . ":" . $attr["ccaps"] . ":" . $attr["rp"] . " " . $attr["rt"] : $attr["level"] . ":" . $attr["ccaps"];
								$attr["level_ccaps_eotper"] = /* Clean any trailing separators from this string. */ rtrim ($attr["level_ccaps_eotper"], ":");

								$success_return_tra = array("ta" => $attr["ta"], "tp" => $attr["tp"], "tt" => $attr["tt"], "ra" => $attr["ra"], "rp" => $attr["rp"], "rt" => $attr["rt"], "rr" => $attr["rr"], "rrt" => $attr["rrt"], "rra" => $attr["rra"], "invoice" => $paypal_invoice_input_value, "checksum" => md5 ($paypal_invoice_input_value . c_ws_plugin__s2member_utils_ip::current() . $attr["level_ccaps_eotper"]));

								$success_return_url = /* s2Member handles this all by itself. However, it can be Filtered (see below). */ home_url ("/?s2member_paypal_return=1", $force_return_url_scheme);
								$success_return_url = add_query_arg ("s2member_paypal_return_tra", urlencode (c_ws_plugin__s2member_utils_encryption::encrypt (serialize ($success_return_tra))), $success_return_url);
								$success_return_url = apply_filters("ws_plugin__s2member_during_sc_paypal_button_success_return_url", $success_return_url, get_defined_vars ());

								//260106 PayPal Checkout (REST) Buy Now (rr=BN) override.
								// Uses server-side create + server-side capture/confirm, then posts into existing IPN + Return handlers.
								if(c_ws_plugin__s2member_paypal_utilities::paypal_checkout_is_enabled())
									{
										static $ppco_sdks = array();

										$ppco_sandbox   = c_ws_plugin__s2member_paypal_utilities::paypal_checkout_is_sandbox();
										$ppco_client_id = (string)$GLOBALS["WS_PLUGIN__"]["s2member"]["o"][($ppco_sandbox) ? "paypal_checkout_sandbox_client_id" : "paypal_checkout_client_id"];

										$ppco_cc     = strtoupper((string)$attr["cc"]);
										$ppco_intent = ($attr["rr"] === "BN") ? 'capture' : 'subscription';

										$ppco_sdk_key = (($ppco_sandbox) ? 'sandbox' : 'live').'|'.$ppco_cc.'|'.$ppco_intent;

										$ppco_sdk_just_loaded = false;
										if(empty($ppco_sdks[$ppco_sdk_key]) || !is_array($ppco_sdks[$ppco_sdk_key]))
											{
												$ppco_sdks[$ppco_sdk_key] = array(
													'ns' => 's2m_ppco_'.substr(md5($ppco_sdk_key), 0, 10),
													'id' => 's2member_ppco_sdk_'.substr(md5($ppco_sdk_key), 0, 10),
												);
												$ppco_sdk_just_loaded = true;
											}

										$ppco_sdk_ns = (string)$ppco_sdks[$ppco_sdk_key]['ns'];
										$ppco_sdk_id = (string)$ppco_sdks[$ppco_sdk_key]['id'];

										$ppco_endpoint = home_url("/?s2member_paypal_checkout=1", $force_notify_url_scheme);
										$ppco_cancel   = home_url("/");

										$ppco_token = array(
											'exp'         => time() + 3600,

											'invoice'     => $paypal_invoice_input_value,
											'ip'          => c_ws_plugin__s2member_utils_ip::current(),
											'item_name'   => $attr["desc"],
											'item_number' => $attr["level_ccaps_eotper"],
											'custom'      => $attr["custom"],

											'amount'      => $attr["ra"],
											'cc'          => $ppco_cc,
											'ns'          => $attr["ns"],

											'rr'          => $attr["rr"],
											'rp'          => $attr["rp"],
											'rt'          => $attr["rt"],

											'rrt'         => $attr["rrt"],
											'rra'         => $attr["rra"],

											'ta'          => $attr["ta"],
											'tp'          => $attr["tp"],
											'tt'          => $attr["tt"],

											'on0'         => $paypal_on0_input_value,
											'os0'         => $paypal_os0_input_value,
											'on1'         => $paypal_on1_input_value,
											'os1'         => $paypal_os1_input_value,

											'return'      => $success_return_url,
											'cancel'      => $ppco_cancel,

											'checksum'    => md5($paypal_invoice_input_value.c_ws_plugin__s2member_utils_ip::current().$attr["level_ccaps_eotper"]),
										);

										$ppco_token = urlencode(c_ws_plugin__s2member_utils_encryption::encrypt(serialize($ppco_token)));

										// output="anchor|url" support (no JS SDK; redirects through s2Member, then to PayPal approval URL).
										if($attr["output"] === "anchor" || $attr["output"] === "url")
										{
											$ppco_redirect_url = $ppco_endpoint.'&s2member_paypal_checkout_op=redirect&s2member_paypal_checkout_t='.$ppco_token;

											if($attr["output"] === "url")
												$code = $_code = $ppco_redirect_url;

											else // output="anchor"
											{
												$img_src = ($attr["image"] && $attr["image"] !== "default") ? $attr["image"] : $default_image;
												$code = $_code = '<a href="'.esc_attr($ppco_redirect_url).'"><img src="'.esc_attr($img_src).'" style="width:auto; height:auto; border:0;" alt="PayPal" /></a>';
											}

											foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
											do_action("ws_plugin__s2member_during_sc_paypal_button", get_defined_vars ());
											unset($__refs, $__v);

											$code = c_ws_plugin__s2member_sc_paypal_button_e::sc_paypal_button_encryption ($code, get_defined_vars ());
											return apply_filters("ws_plugin__s2member_sc_paypal_button", $code, get_defined_vars ());
										}

										$ppco_div_id = 's2member_ppco_btn_'.md5($paypal_invoice_input_value.$attr["level_ccaps_eotper"]);
										$ppco_err_id = 's2member_ppco_err_'.md5($paypal_invoice_input_value.$attr["level_ccaps_eotper"]);

										$ppco_locale = ($attr["lang"]) ? (string)$attr["lang"] : '';
										$ppco_lc     = ($attr["lc"]) ? (string)$attr["lc"] : '';

										if(!$ppco_locale && $ppco_lc)
										{
											$site_locale = (string)get_locale();
											if(preg_match('/^([a-z]{2})[_-]/i', $site_locale, $m))
												$ppco_locale = strtolower($m[1]).'_'.strtoupper($ppco_lc);
											else
												$ppco_locale = 'en_'.strtoupper($ppco_lc);
										}

										$ppco_locale_q        = ($ppco_locale) ? '&locale='.rawurlencode($ppco_locale) : '';
										$ppco_buyer_country_q = ($ppco_sandbox && $ppco_lc) ? '&buyer-country='.rawurlencode(strtoupper($ppco_lc)) : '';

										$ppco_sdk_src = ($ppco_sandbox ? 'https://www.sandbox.paypal.com/sdk/js' : 'https://www.paypal.com/sdk/js');
										if($ppco_intent === 'subscription')
											$ppco_sdk_src = $ppco_sdk_src.'?client-id='.rawurlencode($ppco_client_id).'&currency='.rawurlencode($ppco_cc).'&vault=true&intent=subscription&disable-funding=card'.$ppco_buyer_country_q.$ppco_locale_q;
										else
											$ppco_sdk_src = $ppco_sdk_src.'?client-id='.rawurlencode($ppco_client_id).'&currency='.rawurlencode($ppco_cc).'&intent=capture&commit=true&disable-funding=card'.$ppco_buyer_country_q.$ppco_locale_q;

										$code  = '<div id="'.esc_attr($ppco_div_id).'" style="max-width:145px; width:auto;"></div>'."\n";
										$code .= '<div id="'.esc_attr($ppco_err_id).'" style="display:none;"></div>'."\n";

										if($ppco_sdk_just_loaded)
											{
												$code .= '<script id="'.esc_attr($ppco_sdk_id).'" data-namespace="'.esc_attr($ppco_sdk_ns).'" src="'.esc_attr($ppco_sdk_src).'"></script>'."\n";
											}

										$code .= '<script type="text/javascript">'."\n";
										$code .= '(function(){'."\n";
										$code .= 'var d="'.esc_js($ppco_div_id).'";'."\n";
										$code .= 'var e="'.esc_js($ppco_err_id).'";'."\n";
										$code .= 'var t="'.esc_js($ppco_token).'";'."\n";
										$code .= 'var u="'.esc_js($ppco_endpoint).'";'."\n";
										$code .= 'var ns="'.esc_js($ppco_sdk_ns).'";'."\n";
										$code .= 'var cid="'.esc_js($paypal_invoice_input_value).'";'."\n";
										$code .= 'function showErr(m){try{var el=document.getElementById(e);if(el){el.style.display="block";el.innerHTML=m;}}catch(x){}}'."\n";
										$code .= 'function postTo(url, data){var f=document.createElement("form");f.method="post";f.action=url;for(var k in data){if(!data.hasOwnProperty(k))continue;var i=document.createElement("input");i.type="hidden";i.name=k;i.value=data[k];f.appendChild(i);}document.body.appendChild(f);f.submit();}'."\n";
										$code .= 'function enc(o){var s=[];for(var k in o){if(!o.hasOwnProperty(k))continue;s.push(encodeURIComponent(k)+"="+encodeURIComponent(o[k]));}return s.join("&");}'."\n";
										if($ppco_intent === 'subscription')
											{
												$code .= 'function getPlanId(){return fetch(u,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"},body:enc({s2member_paypal_checkout_op:"get_plan_id",s2member_paypal_checkout_t:t})}).then(function(r){return r.json();}).then(function(res){if(res&&res.plan_id)return res.plan_id;throw(res&&res.error?res.error:"plan_get_failed");});}'."\n";
												$code .= 'var planId=null;'."\n";
												$code .= 'function createSubscription(data,actions){if(planId)return actions.subscription.create({plan_id:planId,custom_id:cid,application_context:{shipping_preference:"NO_SHIPPING"}});return getPlanId().then(function(pid){planId=pid;return actions.subscription.create({plan_id:planId,custom_id:cid,application_context:{shipping_preference:"NO_SHIPPING"}});});}'."\n";
												$code .= 'function onApprove(data){return fetch(u,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"},body:enc({s2member_paypal_checkout_op:"confirm_subscription",s2member_paypal_checkout_t:t,subscription_id:(data&&data.subscriptionID?data.subscriptionID:"")})}).then(function(r){return r.json();}).then(function(res){if(res&&res.rtn_url&&res.rtn_post){postTo(res.rtn_url,res.rtn_post);return;}throw(res&&res.error?res.error:"subscription_confirm_failed");}).catch(function(e){showErr("Subscription could not be completed. Please try again.");});}'."\n";
												$code .= 'function onCancel(){showErr("Subscription cancelled.");}'."\n";
												$code .= 'function onError(err){var m="PayPal error. Please try again.";try{if(err){if(typeof err==="string")m="PayPal error: "+err;else if(err.message)m="PayPal error: "+err.message;}}catch(x){}showErr(m);}'."\n";
												$code .= 'function init(){var P=window[ns];if(!P||!P.Buttons){showErr("PayPal SDK failed to load.");return;}P.Buttons({fundingSource:P.FUNDING.PAYPAL,style:{layout:"vertical",tagline:false,height:40},createSubscription:createSubscription,onApprove:onApprove,onCancel:onCancel,onError:onError}).render("#"+d);}'."\n";
											}
										else
											{
												$code .= 'function createOrder(){return fetch(u,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"},body:enc({s2member_paypal_checkout_op:"create_order",s2member_paypal_checkout_t:t})}).then(function(r){return r.json();}).then(function(res){if(res&&res.order_id)return res.order_id;throw(res&&res.error?res.error:"order_create_failed");});}'."\n";
												$code .= 'function onApprove(data){return fetch(u,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"},body:enc({s2member_paypal_checkout_op:"capture_order",s2member_paypal_checkout_t:t,order_id:(data&&data.orderID?data.orderID:"")})}).then(function(r){return r.json();}).then(function(res){if(res&&res.rtn_url&&res.rtn_post){postTo(res.rtn_url,res.rtn_post);return;}throw(res&&res.error?res.error:"order_capture_failed");}).catch(function(e){showErr("Payment could not be completed. Please try again.");});}'."\n";
												$code .= 'function onCancel(){showErr("Payment cancelled.");}'."\n";
												$code .= 'function onError(err){var m="PayPal error. Please try again.";try{if(err){if(typeof err==="string")m="PayPal error: "+err;else if(err.message)m="PayPal error: "+err.message;}}catch(x){}showErr(m);}'."\n";
												$code .= 'function init(){var P=window[ns];if(!P||!P.Buttons){showErr("PayPal SDK failed to load.");return;}P.Buttons({fundingSource:P.FUNDING.PAYPAL,style:{layout:"vertical",tagline:false,height:40},createOrder:createOrder,onApprove:onApprove,onCancel:onCancel,onError:onError}).render("#"+d);}'."\n";
											}
										$code .= 'if(document.readyState==="complete"){init();}else{window.addEventListener("load",init);}'."\n";
										$code .= '})();'."\n";
										$code .= '</script>'."\n";
										$_code = $code;

										foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
										do_action("ws_plugin__s2member_during_sc_paypal_button", get_defined_vars ());
										unset($__refs, $__v);

										$code = c_ws_plugin__s2member_sc_paypal_button_e::sc_paypal_button_encryption ($code, get_defined_vars ());
										return apply_filters("ws_plugin__s2member_sc_paypal_button", $code, get_defined_vars ());
									}

								$code = trim (c_ws_plugin__s2member_utilities::evl (file_get_contents (dirname (dirname (__FILE__)) . "/templates/buttons/paypal-checkout-button.php")));
								$code = preg_replace ("/%%images%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["dir_url"] . "/src/images")), $code);
								$code = preg_replace ("/%%wpurl%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr (home_url ())), $code);

								$code = preg_replace ("/%%endpoint%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr (($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_sandbox"]) ? "www.sandbox.paypal.com" : "www.paypal.com")), $code);
								$code = preg_replace ("/%%paypal_business%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_business"])), $code);
								$code = preg_replace ("/%%paypal_merchant_id%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["paypal_merchant_id"])), $code);
								$code = preg_replace ("/%%level_label%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["level" . $attr["level"] . "_label"])), $code);
								$code = preg_replace ("/%%cancel_return%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr (home_url ("/"))), $code); // This brings them back to Front Page.
								$code = preg_replace ("/%%notify_url%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr (home_url ("/?s2member_paypal_notify=1", $force_notify_url_scheme))), $code);
								$code = preg_replace ("/%%return%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($success_return_url)), $code);
								$code = preg_replace ("/%%custom%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["custom"])), $code);
								$code = preg_replace ("/%%level%%/", c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["level"])), $code);

								$code = preg_replace ('/ \<\!--(\<input type\="hidden" name\="(amount|src|srt|sra|a1|p1|t1|a3|p3|t3)" value\="(.*?)" \/\>)--\>/', " $1", $code);
								$code = ($attr["rr"] === "BN") ? preg_replace ('/ (\<input type\="hidden" name\="cmd" value\=")(.*?)(" \/\>)/', " $1_xclick$3", $code) : $code;
								$code = ($attr["rr"] === "BN") ? preg_replace ('/ (\<input type\="hidden" name\="(src|srt|sra|a1|p1|t1|a3|p3|t3)" value\="(.*?)" \/\>)/', " <!--$1-->", $code) : $code;
								$code = ($attr["rr"] === "BN" || !$attr["tp"]) ? preg_replace ('/ (\<input type\="hidden" name\="(a1|p1|t1)" value\="(.*?)" \/\>)/', " <!--$1-->", $code) : $code;
								$code = ($attr["rr"] !== "BN") ? preg_replace ('/ (\<input type\="hidden" name\="cmd" value\=")(.*?)(" \/\>)/', " $1_xclick-subscriptions$3", $code) : $code;
								$code = ($attr["rr"] !== "BN") ? preg_replace ('/ (\<input type\="hidden" name\="amount" value\="(.*?)" \/\>)/', " <!--$1-->", $code) : $code;

								$code = preg_replace ('/ name\="lc" value\="(.*?)"/', ' name="lc" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["lc"])) . '"', $code);
								$code = preg_replace ('/ name\="no_shipping" value\="(.*?)"/', ' name="no_shipping" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["ns"])) . '"', $code);
								$code = preg_replace ('/ name\="item_name" value\="(.*?)"/', ' name="item_name" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["desc"])) . '"', $code);
								$code = preg_replace ('/ name\="item_number" value\="(.*?)"/', ' name="item_number" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["level_ccaps_eotper"])) . '"', $code);
								$code = preg_replace ('/ name\="page_style" value\="(.*?)"/', ' name="page_style" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["ps"])) . '"', $code);
								$code = preg_replace ('/ name\="currency_code" value\="(.*?)"/', ' name="currency_code" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["cc"])) . '"', $code);
								$code = preg_replace ('/ name\="custom" value\="(.*?)"/', ' name="custom" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["custom"])) . '"', $code);

								$code = preg_replace ('/ name\="invoice" value\="(.*?)"/', ' name="invoice" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($paypal_invoice_input_value)) . '"', $code);

								$code = preg_replace ('/ name\="on0" value\="(.*?)"/', ' name="on0" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($paypal_on0_input_value)) . '"', $code);
								$code = preg_replace ('/ name\="os0" value\="(.*?)"/', ' name="os0" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($paypal_os0_input_value)) . '"', $code);
								$code = preg_replace ('/ name\="on1" value\="(.*?)"/', ' name="on1" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($paypal_on1_input_value)) . '"', $code);
								$code = preg_replace ('/ name\="os1" value\="(.*?)"/', ' name="os1" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($paypal_os1_input_value)) . '"', $code);

								$code = preg_replace ('/ name\="modify" value\="(.*?)"/', ' name="modify" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["modify"])) . '"', $code);

								$code = preg_replace ('/ name\="amount" value\="(.*?)"/', ' name="amount" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["ra"])) . '"', $code);

								$code = preg_replace ('/ name\="src" value\="(.*?)"/', ' name="src" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["rr"])) . '"', $code);
								$code = preg_replace ('/ name\="srt" value\="(.*?)"/', ' name="srt" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["rrt"])) . '"', $code);
								$code = preg_replace ('/ name\="sra" value\="(.*?)"/', ' name="sra" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["rra"])) . '"', $code);

								$code = preg_replace ('/ name\="a1" value\="(.*?)"/', ' name="a1" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["ta"])) . '"', $code);
								$code = preg_replace ('/ name\="p1" value\="(.*?)"/', ' name="p1" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["tp"])) . '"', $code);
								$code = preg_replace ('/ name\="t1" value\="(.*?)"/', ' name="t1" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["tt"])) . '"', $code);
								$code = preg_replace ('/ name\="a3" value\="(.*?)"/', ' name="a3" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["ra"])) . '"', $code);
								$code = preg_replace ('/ name\="p3" value\="(.*?)"/', ' name="p3" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["rp"])) . '"', $code);
								$code = preg_replace ('/ name\="t3" value\="(.*?)"/', ' name="t3" value="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["rt"])) . '"', $code);

								$code = $_code = ($attr["image"] && $attr["image"] !== "default") ? preg_replace ('/ src\="(.*?)"/', ' src="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($attr["image"])) . '"', $code) : preg_replace ('/ src\="(.*?)"/', ' src="' . c_ws_plugin__s2member_utils_strings::esc_refs (esc_attr ($default_image)) . '"', $code);

								$code = ($attr["output"] === "anchor") ? '<a href="' . esc_attr (c_ws_plugin__s2member_utils_forms::form_whips_2_url ($code)) . '"><img src="' . esc_attr (($attr["image"] && $attr["image"] !== "default") ? $attr["image"] : $default_image) . '" style="width:auto; height:auto; border:0;" alt="PayPal" /></a>' : $code;
								$code = ($attr["output"] === "url") ? c_ws_plugin__s2member_utils_forms::form_whips_2_url ($code) : $code;

								foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
								($attr["modify"]) ? do_action("ws_plugin__s2member_during_sc_paypal_modification_button", get_defined_vars ()) : do_action("ws_plugin__s2member_during_sc_paypal_button", get_defined_vars ());
								unset($__refs, $__v);
							}
						$code = c_ws_plugin__s2member_sc_paypal_button_e::sc_paypal_button_encryption ($code, get_defined_vars ());

						return apply_filters("ws_plugin__s2member_sc_paypal_button", $code, get_defined_vars ());
					}
			}
	}
