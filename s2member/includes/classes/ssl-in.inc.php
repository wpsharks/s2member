<?php
/**
* SSL routines (inner processing routines).
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
* @package s2Member\SSL
* @since 3.5
*/
if(realpath(__FILE__) === realpath($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");

if(!class_exists("c_ws_plugin__s2member_ssl_in"))
	{
		/**
		* SSL routines (inner processing routines).
		*
		* @package s2Member\SSL
		* @since 3.5
		*/
		class c_ws_plugin__s2member_ssl_in
			{
				/**
				* Forces SSL on specific Posts/Pages, or any page for that matter.
				*
				* Triggered by Custom Field: `s2member_force_ssl = yes|port#`
				*
				* Triggered by: `?s2-ssl` or `?s2-ssl=yes|port#`.
				*
				* @package s2Member\SSL
				* @since 3.5
				*
				* @attaches-to ``add_action("init");``
				* @also-attaches-to ``add_action("wp");``
				*
				* @return null Possibly exiting script execution after redirection to SSL variation.
				*
				* @todo Add `form` to the array ``$non_ssl_attr_only_tags``?
				* @todo Cleanup this routine and convert callback functions to static class methods?
				*/
				public static function force_ssl($vars = array()) // Phase 2 of ``c_ws_plugin__s2member_ssl::check_force_ssl()``.
					{
						extract /* Extract all vars passed in from: ``c_ws_plugin__s2member_ssl::check_force_ssl()``. */($vars);

						$force_ssl = (!is_string($force_ssl)) ? /* Force string. */ (string)(int)$force_ssl : $force_ssl;
						$force_ssl = (is_numeric($force_ssl) && $force_ssl > 1) ? $force_ssl : /* Use `yes`. */ "yes";

						$ssl_host = /* Remove port here. */ preg_replace("/\:[0-9]+$/", "", $_SERVER["HTTP_HOST"]);
						$ssl_port = /* Port? */ (is_numeric($force_ssl) && $force_ssl > 1) ? $force_ssl : false;
						$ssl_host_port = /* Use port #? */ $ssl_host.(($ssl_port) ? ":".$ssl_port : "");

						if(!is_ssl() || !isset($_GET[$s2_ssl_gv]) /* SSL must be enabled. */)
							{
								$https = "https://".$ssl_host_port.$_SERVER["REQUEST_URI"];
								$https_with_s2_ssl_gv = add_query_arg($s2_ssl_gv, urlencode($force_ssl), $https);
								wp_redirect($https_with_s2_ssl_gv).exit();
							}
						else // Otherwise, we buffer all output, and switch all content over to `https`.
							// Assume here that other links on the site should NOT be converted to `https`.
							{
								add_filter("redirect_canonical", "__return_false");

								define("_ws_plugin__s2member_force_ssl_host", $ssl_host);
								define("_ws_plugin__s2member_force_ssl_port", $ssl_port);
								define("_ws_plugin__s2member_force_ssl_host_port", $ssl_host_port);

								// Filter these. Do NOT create a sitewide conversion to `https`.
								add_filter("home_url", "_ws_plugin__s2member_force_non_ssl_scheme", 10, 3);
								add_filter("network_home_url", "_ws_plugin__s2member_force_non_ssl_scheme", 10, 3);

								// Filter these. Do NOT create a sitewide conversion to `https`.
								add_filter("site_url", "_ws_plugin__s2member_force_non_ssl_scheme", 10, 3);
								add_filter("network_site_url", "_ws_plugin__s2member_force_non_ssl_scheme", 10, 3);
								/*
								These additional URLs are NOT Filtered by default; but can be if needed. Use these Filters. */
								if(apply_filters("_ws_plugin__s2member_force_non_ssl_scheme_plugins_url", false, get_defined_vars()))
									add_filter("plugins_url", "_ws_plugin__s2member_force_non_ssl_scheme", 10, 2);
								/*
								These additional URLs are NOT Filtered by default; but can be if needed. Use these Filters. */
								if(apply_filters("_ws_plugin__s2member_force_non_ssl_scheme_content_url", false, get_defined_vars()))
									add_filter("content_url", "_ws_plugin__s2member_force_non_ssl_scheme", 10, 2);
								/*
								Now we create various callback functions associated with SSL and non-SSL buffering.
								*/
								if(!function_exists("_ws_plugin__s2member_force_ssl_buffer_callback"))
									{
										function _ws_plugin__s2member_force_ssl_buffer_callback($m = FALSE)
											{
												$s = /* Conversion to SSL mode via `https`. */ preg_replace("/http\:\/\//i", "https://", $m[0]);

												if(_ws_plugin__s2member_force_ssl_host && /* Convert port? */ _ws_plugin__s2member_force_ssl_port && _ws_plugin__s2member_force_ssl_host_port)
													$s = preg_replace("/(?:https?\:)?\/\/".preg_quote(_ws_plugin__s2member_force_ssl_host, "/")."(?:\:[0-9]+)?/i", "https://"._ws_plugin__s2member_force_ssl_host_port, $s);

												$s = (strtolower($m[1]) === "link" && preg_match /* These are fine to leave like they are. */("/['\"](?:alternate|profile|pingback|EditURI|wlwmanifest|prev|next)['\"]/i", $m[0])) ? $m[0] : $s;

												return /* Return string with conversions. */ $s;
											}
									}

								if(!function_exists("_ws_plugin__s2member_force_non_ssl_buffer_callback"))
									{
										function _ws_plugin__s2member_force_non_ssl_buffer_callback($m = FALSE)
											{
												$s = preg_replace("/(?:https?\:)?\/\/".preg_quote(_ws_plugin__s2member_force_ssl_host_port, "/")."/i", "http://"._ws_plugin__s2member_force_ssl_host, $m[0]);

												$s = preg_replace("/(?:https?\:)?\/\/".preg_quote(_ws_plugin__s2member_force_ssl_host, "/")."/i", "http://"._ws_plugin__s2member_force_ssl_host, $s);

												return /* Return string with conversions. */ $s;
											}
									}

								if(!function_exists("_ws_plugin__s2member_force_non_ssl_scheme"))
									{
										function _ws_plugin__s2member_force_non_ssl_scheme($url = FALSE, $path = FALSE, $scheme = FALSE)
											{
												if($scheme === "relative") return $url; // Nothing to do in this case.

												if(!in_array /* If NOT explicitly passed through. */($scheme, array("http", "https"), true))
													{
														if(($scheme === "login_post" || $scheme === "rpc") && (force_ssl_login() || force_ssl_admin()))
															$scheme = "https";

														else if(($scheme === "login" || $scheme === "admin") && force_ssl_admin())
															$scheme = "https";

														else // Default to non-SSL: `http`.
															$scheme = "http";
													}
												return preg_replace("/^(?:https?\:)?\/\//i", $scheme."://", $url);
											}
									}

								if(!function_exists("_ws_plugin__s2member_force_ssl_buffer"))
									{
										function _ws_plugin__s2member_force_ssl_buffer($buffer = FALSE)
											{
												$o_pcre = /* Record existing PCRE backtrack limit. */ @ini_get("pcre.backtrack_limit");
												@ini_set /* Increase PCRE backtrack limit for this routine. */("pcre.backtrack_limit", 10000000);

												$ssl_entire_tags = array_unique(array_map("strtolower", apply_filters("_ws_plugin__s2member_force_ssl_buffer_entire_tags", array("script", "style", "iframe", "object", "embed", "video"), get_defined_vars())));
												$non_ssl_entire_tags = array_unique(array_map("strtolower", apply_filters("_ws_plugin__s2member_force_non_ssl_buffer_entire_tags", array(), get_defined_vars())));

												$ssl_attr_only_tags = array_unique( /* Diff here. No need to re-run entire tags. */array_diff(array_map("strtolower", apply_filters("_ws_plugin__s2member_force_ssl_buffer_attr_only_tags", array("link", "img", "input"), get_defined_vars())), $ssl_entire_tags));
												$non_ssl_attr_only_tags = array_unique( /* No need to re-run entire tags. */array_diff(array_map("strtolower", apply_filters("_ws_plugin__s2member_force_non_ssl_buffer_attr_only_tags", array("a"), get_defined_vars())), $non_ssl_entire_tags));

												$buffer = ($ssl_entire_tags) ? preg_replace_callback("/\<(".implode("|", c_ws_plugin__s2member_utils_strings::preg_quote_deep($ssl_entire_tags, "/")).")(?![a-z_0-9\-])[^\>]*?\>.*?\<\/\\1\>/is", "_ws_plugin__s2member_force_ssl_buffer_callback", $buffer) : $buffer;
												$buffer = ($ssl_attr_only_tags) ? preg_replace_callback("/\<(".implode("|", c_ws_plugin__s2member_utils_strings::preg_quote_deep($ssl_attr_only_tags, "/")).")(?![a-z_0-9\-])[^\>]+?\>/i", "_ws_plugin__s2member_force_ssl_buffer_callback", $buffer) : $buffer;

												$buffer = ($non_ssl_entire_tags) ? preg_replace_callback("/\<(".implode("|", c_ws_plugin__s2member_utils_strings::preg_quote_deep($non_ssl_entire_tags, "/")).")(?![a-z_0-9\-])[^\>]*?\>.*?\<\/\\1\>/is", "_ws_plugin__s2member_force_non_ssl_buffer_callback", $buffer) : $buffer;
												$buffer = ($non_ssl_attr_only_tags) ? preg_replace_callback("/\<(".implode("|", c_ws_plugin__s2member_utils_strings::preg_quote_deep($non_ssl_attr_only_tags, "/")).")(?![a-z_0-9\-])[^\>]+?\>/i", "_ws_plugin__s2member_force_non_ssl_buffer_callback", $buffer) : $buffer;

												@ini_set /* Restore original PCRE backtrack limit. This just keeps things tidy; probably NOT necessary. */("pcre.backtrack_limit", $o_pcre);

												return apply_filters("_ws_plugin__s2member_force_ssl_buffer", $buffer, get_defined_vars());
											}
									}

								ob_start("_ws_plugin__s2member_force_ssl_buffer");
							}
						return /* Return for uniformity. */;
					}
			}
	}
?>