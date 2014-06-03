<?php
/**
* File Download routines for s2Member (inner processing routines).
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
* @package s2Member\Files
* @since 3.5
*/
if(realpath(__FILE__) === realpath($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");

if(!class_exists("c_ws_plugin__s2member_files_in"))
	{
		/**
		* File Download routines for s2Member (inner processing routines).
		*
		* @package s2Member\Files
		* @since 3.5
		*/
		class c_ws_plugin__s2member_files_in
			{
				/**
				* Handles Download Access permissions.
				*
				* @package s2Member\Files
				* @since 3.5
				*
				* @attaches-to ``add_action("init");``
				* @also-called-by API Function {@link s2Member\API_Functions\s2member_file_download_url()}, w/ ``$create_file_download_url`` param.
				*
				* @param array $create_file_download_url Optional. If this function is called directly, we can pass arguments through this array.
				* 	Possible array elements: `file_download` *(required)*, `file_download_key`, `file_stream`, `file_inline`, `file_storage`, `file_remote`, `file_ssl`, `file_rewrite`, `file_rewrite_base`, `skip_confirmation`, `url_to_storage_source`, `count_against_user`, `check_user`.
				* @return null|str If called directly with ``$create_file_download_url``, returns a string with the URL, based on configuration.
				* 	Else, this function may exit script execution after serving a File Download.
				*/
				public static function check_file_download_access($create_file_download_url = FALSE)
					{
						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_before_file_download_access", get_defined_vars());
						unset($__refs, $__v);

						$_g = !empty($_GET) ? $_GET : array();
						$_g = c_ws_plugin__s2member_utils_strings::trim_deep(stripslashes_deep($_g));

						$creating = /* Creating URL? */ (is_array($create = $create_file_download_url)) ? true : false;
						$serving = /* If NOT creating a File Download URL, we're serving one. */ (!$creating) ? true : false;
						$serving_range = $range = /* Default values (so these variables DO get defined at all times). */ false;
						if /* If we're serving, let's see if we're serving a byte-range request here. */($serving)
							{
								$range = (string)@$_SERVER["HTTP_RANGE"];
								if(!$range && function_exists("apache_request_headers"))
									// Note: ``apache_request_headers()`` works in FastCGI too, starting w/ PHP v5.4.
									foreach((array)apache_request_headers() as $_header => $_value)
										if(is_string($_header) && strcasecmp($_header, "range") === 0)
											$range = $_value;
								if /* Serving a range? */($range)
									$serving_range = true;
								unset($_header, $_value);
							}
						$req["file_download"] = ($creating) ? @$create["file_download"] : @$_g["s2member_file_download"];
						$req["file_download_key"] = ($creating) ? @$create["file_download_key"] : @$_g["s2member_file_download_key"];

						$req["file_stream"] = ($creating) ? @$create["file_stream"] : @$_g["s2member_file_stream"];
						$req["file_inline"] = ($creating) ? @$create["file_inline"] : @$_g["s2member_file_inline"];
						$req["file_storage"] = ($creating) ? @$create["file_storage"] : @$_g["s2member_file_storage"];
						$req["file_remote"] = ($creating) ? @$create["file_remote"] : @$_g["s2member_file_remote"];
						$req["file_ssl"] = ($creating) ? @$create["file_ssl"] : @$_g["s2member_file_ssl"];

						$req["file_rewrite"] = ($creating) ? @$create["file_rewrite"] : /* N/A. */ null;
						$req["file_rewrite_base"] = ($creating) ? @$create["file_rewrite_base"] : /* N/A. */ null;

						$req["skip_confirmation"] = ($creating) ? @$create["skip_confirmation"] : /* N/A. */ null;
						$req["url_to_storage_source"] = ($creating) ? @$create["url_to_storage_source"] : /* N/A. */ null;
						$req["count_against_user"] = ($creating) ? @$create["count_against_user"] : /* N/A. */ null;
						$req["check_user"] = ($creating) ? @$create["check_user"] : /* N/A. */ null;

						if($req["file_download"] && is_string($req["file_download"]) && ($req["file_download"] = trim($req["file_download"], "/")))
							if(strpos($req["file_download"], "..") === false && strpos(basename($req["file_download"]), ".") !== 0)
								{
									$using_amazon_cf_storage = ((!$req["file_storage"] || strcasecmp((string)$req["file_storage"], "cf") === 0) && c_ws_plugin__s2member_utils_conds::using_amazon_cf_storage()) ? true : false;
									$using_amazon_s3_storage = ((!$req["file_storage"] || strcasecmp((string)$req["file_storage"], "s3") === 0) && c_ws_plugin__s2member_utils_conds::using_amazon_s3_storage()) ? true : false;
									$using_amazon_storage = /* Either? */ ($using_amazon_cf_storage || $using_amazon_s3_storage) ? true : false;

									$excluded = apply_filters("ws_plugin__s2member_check_file_download_access_excluded", false, get_defined_vars());
									$valid_file_download_key = ($req["file_download_key"] && is_string($req["file_download_key"]) && $creating && (!isset($req["check_user"]) || !filter_var($req["check_user"], FILTER_VALIDATE_BOOLEAN)) && (!isset($req["count_against_user"]) || !filter_var($req["count_against_user"], FILTER_VALIDATE_BOOLEAN))) ? true : false;
									$valid_file_download_key = (!$valid_file_download_key && $req["file_download_key"] && is_string($req["file_download_key"])) ? c_ws_plugin__s2member_files_in::check_file_download_key($req["file_download"], $req["file_download_key"]) : false;
									$checking_user = ($excluded || $valid_file_download_key || ($creating && (!isset($req["check_user"]) || !filter_var($req["check_user"], FILTER_VALIDATE_BOOLEAN)) && (!isset($req["count_against_user"]) || !filter_var($req["count_against_user"], FILTER_VALIDATE_BOOLEAN)))) ? false : true;
									$updating_user_counter = ($serving_range || !$checking_user || ($creating && (!isset($req["count_against_user"]) || !filter_var($req["count_against_user"], FILTER_VALIDATE_BOOLEAN)))) ? false : true;

									if( /* In either case, the following routines apply whenever we ARE ``$checking_user``. */($serving || $creating) && $checking_user)
										{
											if(!$using_amazon_storage && !file_exists($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["files_dir"]."/".$req["file_download"]))
												{
													if /* We only need this section when/if we're actually serving. */($serving)
													{
														status_header(404);
														header("Content-Type: text/html; charset=UTF-8");
														while (@ob_end_clean ()); // Clean any existing output buffers.
														exit(_x('<strong>404: Sorry, file not found.</strong> Please contact Support for assistance.', "s2member-front", "s2member"));
													}
													else // Else return false.
														return false;
												}

											else if($req["file_download_key"] && is_string($req["file_download_key"]) && !$valid_file_download_key)
												{
													if /* We only need this section when/if we're actually serving. */($serving)
													{
														status_header(503);
														header("Content-Type: text/html; charset=UTF-8");
														while (@ob_end_clean ()); // Clean any existing output buffers.
														exit(_x('<strong>503 (Invalid Key):</strong> Sorry, your access to this file has expired. Please contact Support for assistance.', "s2member-front", "s2member"));
													}
													else // Else return false.
														return false;
												}

											else // Default behavior; check file download access against the current user.
												{
													if /* We only need remote functionality when/if we're actually serving. */($serving)
														if(!has_filter("ws_plugin__s2member_check_file_download_access_user", "c_ws_plugin__s2member_files_in::check_file_remote_authorization"))
															add_filter("ws_plugin__s2member_check_file_download_access_user", "c_ws_plugin__s2member_files_in::check_file_remote_authorization", 10, 2);

													if /* We only need remote functionality when/if we're actually serving. */($creating)
														if(has_filter("ws_plugin__s2member_check_file_download_access_user", "c_ws_plugin__s2member_files_in::check_file_remote_authorization"))
															remove_filter("ws_plugin__s2member_check_file_download_access_user", "c_ws_plugin__s2member_files_in::check_file_remote_authorization", 10, 2);

													if(!$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["membership_options_page"])
														{
															if /* We only need this section when/if we're actually serving. */($serving)
															{
																status_header(503);
																header("Content-Type: text/html; charset=UTF-8");
																while (@ob_end_clean ()); // Clean any existing output buffers.
																exit(_x('<strong>503: Basic File Downloads are NOT enabled yet.</strong> Please contact Support for assistance. If you are the site owner, please configure: <code>s2Member -› General Options -› Membership Options Page</code>.', "s2member-front", "s2member"));
															}
															else // Else return false.
																return false;
														}

													else if(($file_downloads_enabled_by_site_owner = $min_level_4_downloads = c_ws_plugin__s2member_files::min_level_4_downloads()) === false)
														{
															if /* We only need this section when/if we're actually serving. */($serving)
															{
																status_header(503);
																header("Content-Type: text/html; charset=UTF-8");
																while (@ob_end_clean ()); // Clean any existing output buffers.
																exit(_x('<strong>503: Basic File Downloads are NOT enabled yet.</strong> Please contact Support for assistance. If you are the site owner, please configure: <code>s2Member -› Download Options -› Basic Download Restrictions</code>.', "s2member-front", "s2member"));
															}
															else // Else return false.
																return false;
														}

													else if(!is_object($user = apply_filters("ws_plugin__s2member_check_file_download_access_user", ((is_user_logged_in()) ? wp_get_current_user() : false), get_defined_vars())) || empty($user->ID) || !($user_id = $user->ID) || !is_array($user_file_downloads = c_ws_plugin__s2member_files::user_downloads($user)) || (!$user->has_cap("administrator") && (!$user_file_downloads["allowed"] || !$user_file_downloads["allowed_days"])))
														{
															if(preg_match("/(?:^|\/)access[_\-]s2member[_\-]level([0-9]+)\//", $req["file_download"], $m) && strlen($req_level = $m[1]) && (!is_object($user) || empty($user->ID) || !$user->has_cap("access_s2member_level".$req_level)))
																{
																	if /* We only need this section when/if we're actually serving. */($serving)
																		c_ws_plugin__s2member_mo_page::wp_redirect_w_mop_vars /* Configure MOP Vars here. */("file", $req["file_download"], "level", $req_level, $_SERVER["REQUEST_URI"]).exit();

																	else // Else return false.
																		return false;
																}

															else if(preg_match("/(?:^|\/)access[_\-]s2member[_\-]ccap[_\-](.+?)\//", $req["file_download"], $m) && strlen($req_ccap = preg_replace("/-/", "_", $m[1])) && (!is_object($user) || empty($user->ID) || !$user->has_cap("access_s2member_ccap_".$req_ccap)))
																{
																	if /* We only need this section when/if we're actually serving. */($serving)
																		c_ws_plugin__s2member_mo_page::wp_redirect_w_mop_vars /* Configure MOP Vars here. */("file", $req["file_download"], "ccap", $req_ccap, $_SERVER["REQUEST_URI"]).exit();

																	else // Else return false.
																		return false;
																}

															else if /* We only need this section when/if we're actually serving. */($serving)
																c_ws_plugin__s2member_mo_page::wp_redirect_w_mop_vars /* Configure MOP Vars here. */("file", $req["file_download"], "level", $min_level_4_downloads, $_SERVER["REQUEST_URI"]).exit();

															else // Else return false.
																return false;
														}

													else if(preg_match("/(?:^|\/)access[_\-]s2member[_\-]level([0-9]+)\//", $req["file_download"], $m) && strlen($req_level = $m[1]) && !$user->has_cap("access_s2member_level".$req_level))
														{
															if /* We only need this section when/if we're actually serving. */($serving)
																c_ws_plugin__s2member_mo_page::wp_redirect_w_mop_vars /* Configure MOP Vars here. */("file", $req["file_download"], "level", $req_level, $_SERVER["REQUEST_URI"]).exit();

															else // Else return false.
																return false;
														}

													else if(preg_match("/(?:^|\/)access[_\-]s2member[_\-]ccap[_\-](.+?)\//", $req["file_download"], $m) && strlen($req_ccap = preg_replace("/-/", "_", $m[1])) && !$user->has_cap("access_s2member_ccap_".$req_ccap))
														{
															if /* We only need this section when/if we're actually serving. */($serving)
																c_ws_plugin__s2member_mo_page::wp_redirect_w_mop_vars /* Configure MOP Vars here. */("file", $req["file_download"], "ccap", $req_ccap, $_SERVER["REQUEST_URI"]).exit();

															else // Else return false.
																return false;
														}

													else if /* In either case, the following routines apply. */($serving || $creating)
														{
															$user_previous_file_downloads = /* Downloads the User has already; in current period/cycle. */ 0;
															$user_already_downloaded_this_file = $user_already_downloaded_a_streaming_variation_of_this_file = false;

															$user_file_download_access_log = (is_array($user_file_download_access_log = get_user_option("s2member_file_download_access_log", $user_id))) ? $user_file_download_access_log : array();
															$user_file_download_access_arc = (is_array($user_file_download_access_arc = get_user_option("s2member_file_download_access_arc", $user_id))) ? $user_file_download_access_arc : array();

															$streaming_file_extns = c_ws_plugin__s2member_utils_strings::preg_quote_deep($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["streaming_file_extns"], "/");
															$streaming_variations = /* Only count one streaming media file variation. */ "/\.(".implode("|", $streaming_file_extns).")$/i";

															foreach($user_file_download_access_log as $user_file_download_access_log_entry_key => $user_file_download_access_log_entry)
																{
																	if( /* Weed out corrupt/empty log entries. */isset($user_file_download_access_log_entry["date"], $user_file_download_access_log_entry["file"]))
																		{
																			if(strtotime($user_file_download_access_log_entry["date"]) < strtotime("-".$user_file_downloads["allowed_days"]." days"))
																				{
																					unset /* Remove it from the `log`. */($user_file_download_access_log[$user_file_download_access_log_entry_key]);
																					$user_file_download_access_arc[] = /* Move `log` entry to the `archive` now. */ $user_file_download_access_log_entry;
																				}
																			else if(strtotime($user_file_download_access_log_entry["date"]) >= strtotime("-".$user_file_downloads["allowed_days"]." days"))
																				{
																					$user_previous_file_downloads++; // Previous files always count against this User/Member.

																					$_user_file_download_access_log_entry = &$user_file_download_access_log[$user_file_download_access_log_entry_key];
																					$_user_already_downloaded_this_file = $_user_already_downloaded_a_streaming_variation_of_this_file = false;

																					if /* Already downloaded this file? If yes, mark this flag as true. */($user_file_download_access_log_entry["file"] === $req["file_download"])
																						$user_already_downloaded_this_file = $_user_already_downloaded_this_file = /* Already downloaded this file? If yes, mark as true. */ true;

																					else if(preg_replace($streaming_variations, "", $user_file_download_access_log_entry["file"]) === preg_replace($streaming_variations, "", $req["file_download"]))
																						$user_already_downloaded_this_file = $_user_already_downloaded_this_file = $user_already_downloaded_a_streaming_variation_of_this_file = $_user_already_downloaded_a_streaming_variation_of_this_file = true;

																					if( /* Updating counter? */$updating_user_counter && ($_user_already_downloaded_this_file || $_user_already_downloaded_a_streaming_variation_of_this_file))
																						{
																							$_user_file_download_access_log_entry /* First, we update the last download time for this file. */["ltime"] = time();

																							if( /* Backward compatibility here. Is this even set? */!empty($user_file_download_access_log_entry["counter"]))
																								$_user_file_download_access_log_entry["counter"] = (int)$user_file_download_access_log_entry["counter"] + 1;
																							else // Backward compatibility here. Default value to `1`, if this is NOT even set yet.
																								$_user_file_download_access_log_entry["counter"] = 1 + 1;
																						}
																				}
																		}
																	else // Weed out empty log entries. Some older versions of s2Member may have corrupt/empty log entries.
																		unset /* Remove. */($user_file_download_access_log[$user_file_download_access_log_entry_key]);
																}
															if( /* Updating counter? */$updating_user_counter && /* Do we need a new log entry for this file? */ !$user_already_downloaded_this_file && !$user_already_downloaded_a_streaming_variation_of_this_file)
																$user_file_download_access_log[] = array("date" => date("Y-m-d"), "time" => time(), "ltime" => time(), "file" => $req["file_download"], "counter" => 1);

															if($user_previous_file_downloads >= $user_file_downloads["allowed"] && !$user_already_downloaded_this_file && !$user_already_downloaded_a_streaming_variation_of_this_file && !$user->has_cap("administrator"))
																{
																	if /* We only need this section when/if we're actually serving. */($serving)
																		wp_redirect(add_query_arg(urlencode_deep(array("_s2member_seeking" => array("type" => "file", "file" => $req["file_download"], "_uri" => base64_encode($_SERVER["REQUEST_URI"])), "s2member_seeking" => "file-".$req["file_download"])), get_page_link($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["file_download_limit_exceeded_page"])), apply_filters("ws_plugin__s2member_content_redirect_status", 301, get_defined_vars())).exit();

																	else // Else return false.
																		return false;
																}
															else if /* Save/update counter? By default, we do NOT update the counter when a URL is simply being created for access. */($updating_user_counter)
																update_user_option($user_id, "s2member_file_download_access_log", c_ws_plugin__s2member_utils_arrays::array_unique($user_file_download_access_log)).update_user_option($user_id, "s2member_file_download_access_arc", c_ws_plugin__s2member_utils_arrays::array_unique($user_file_download_access_arc));
														}
												}
										}
									else // Otherwise, we're either NOT ``$checking_user``; or permission was granted with a valid File Download Key.
										{
											if(!$using_amazon_storage && !file_exists($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["files_dir"]."/".$req["file_download"]))
												{
													if /* We only need this section when/if we're actually serving. */($serving)
													{
														status_header(404);
														header("Content-Type: text/html; charset=UTF-8");
														while (@ob_end_clean ()); // Clean any existing output buffers.
														exit(_x('<strong>404: Sorry, file not found.</strong> Please contact Support for assistance.', "s2member-front", "s2member"));
													}
													else // Else return false.
														return false;
												}
										}

									if /* In either case, the following routines apply. */($serving || $creating)
										{
											$basename = basename($req["file_download"]);
											$mimetypes = parse_ini_file(dirname(dirname(dirname(__FILE__)))."/includes/mime-types.ini");
											$extension = strtolower(substr($req["file_download"], strrpos($req["file_download"], ".") + 1));

											$key = ($req["file_download_key"] && is_string($req["file_download_key"])) ? $req["file_download_key"] : false;

											$stream = (isset($req["file_stream"])) ? filter_var($req["file_stream"], FILTER_VALIDATE_BOOLEAN) : ((in_array($extension, preg_split("/[\r\n\t\s;,]+/", $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["file_download_stream_extensions"]))) ? true : false);
											$inline = (!$stream && isset($req["file_inline"])) ? filter_var($req["file_inline"], FILTER_VALIDATE_BOOLEAN) : (($stream || in_array($extension, preg_split("/[\r\n\t\s;,]+/", $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["file_download_inline_extensions"]))) ? true : false);
											$ssl = (isset($req["file_ssl"])) ? filter_var($req["file_ssl"], FILTER_VALIDATE_BOOLEAN) : ((is_ssl()) ? true : false);
											$storage = ($req["file_storage"] && is_string($req["file_storage"])) ? strtolower($req["file_storage"]) : false;
											$remote = (isset($req["file_remote"])) ? filter_var($req["file_remote"], FILTER_VALIDATE_BOOLEAN) : false;

											$_basename_dir_app_data = c_ws_plugin__s2member_utils_dirs::basename_dir_app_data($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["files_dir"]);
											$rewrite_base_guess = (is_dir(dirname($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["dir"])."/".$_basename_dir_app_data)) ? dirname($GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["dir_url"])."/".$_basename_dir_app_data : content_url("/".$_basename_dir_app_data);
											$rewrite_base = ($req["file_rewrite_base"] && is_string($req["file_rewrite_base"])) ? $req["file_rewrite_base"] : false;
											$rewrite = $rewriting = (!$rewrite_base && isset($req["file_rewrite"])) ? filter_var($req["file_rewrite"], FILTER_VALIDATE_BOOLEAN) : (($rewrite_base) ? true : false);
											unset /* A little housekeeping here. */($_basename_dir_app_data);

											$skip_confirmation = (isset($req["skip_confirmation"])) ? filter_var($req["skip_confirmation"], FILTER_VALIDATE_BOOLEAN) : false;
											$url_to_storage_source = (isset($req["url_to_storage_source"])) ? filter_var($req["url_to_storage_source"], FILTER_VALIDATE_BOOLEAN) : false;

											$pathinfo = (!$using_amazon_storage) ? pathinfo(($file = $GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["files_dir"]."/".$req["file_download"])) : array();
											$mimetype = ($mimetypes[$extension]) ? $mimetypes[$extension] : "application/octet-stream";
											$disposition = (($inline) ? "inline" : "attachment")."; filename=\"".c_ws_plugin__s2member_utils_strings::esc_dq($basename)."\"; filename*=UTF-8''".rawurlencode($basename);
											$length = (!$using_amazon_storage && $file) ? filesize($file) : -1;

											foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
											do_action("ws_plugin__s2member_during_file_download_access", get_defined_vars());
											unset($__refs, $__v);

											if($using_amazon_storage && $using_amazon_cf_storage && ($serving || ($creating && $url_to_storage_source)))
												{
													if /* We only need this section when/if we're actually serving. */($serving)
														wp_redirect(c_ws_plugin__s2member_files_in::amazon_cf_url($req["file_download"], $stream, $inline, $ssl, $basename, $mimetype)).exit();

													else // Else return File Download URL.
														return apply_filters("ws_plugin__s2member_file_download_access_url", c_ws_plugin__s2member_files_in::amazon_cf_url($req["file_download"], $stream, $inline, $ssl, $basename, $mimetype), get_defined_vars());
												}

											else if($using_amazon_storage && $using_amazon_s3_storage && ($serving || ($creating && $url_to_storage_source)))
												{
													if /* We only need this section when/if we're actually serving. */($serving)
														wp_redirect(c_ws_plugin__s2member_files_in::amazon_s3_url($req["file_download"], $stream, $inline, $ssl, $basename, $mimetype)).exit();

													else // Else return File Download URL.
														return apply_filters("ws_plugin__s2member_file_download_access_url", c_ws_plugin__s2member_files_in::amazon_s3_url($req["file_download"], $stream, $inline, $ssl, $basename, $mimetype), get_defined_vars());
												}

											else if /* Creating a rewrite URL, pointing to local storage. */($creating && $rewriting)
												{ // Note: we don't URL encode unreserved chars. Improves media player compatibility.
													$_url_e_key = ($key) ? c_ws_plugin__s2member_utils_strings::urldecode_ur_chars_deep(urlencode($key)) : "";
													$_url_e_storage = ($storage) ? c_ws_plugin__s2member_utils_strings::urldecode_ur_chars_deep(urlencode($storage)) : "";
													$_url_e_file = c_ws_plugin__s2member_utils_strings::urldecode_ur_chars_deep(urlencode($req["file_download"]));
													$_url_e_file = str_ireplace("%2F", "/", $_url_e_file);

													$url = ($rewrite_base) ? rtrim($rewrite_base, "/") : rtrim($rewrite_base_guess, "/");
													$url .= (isset($req["file_download_key"])) ? (($key && $_url_e_key) ? "/s2member-file-download-key-".$_url_e_key : "") : "";
													$url .= (isset($req["file_stream"])) ? (($stream) ? "/s2member-file-stream" : "/s2member-file-stream-no") : "";
													$url .= (isset($req["file_inline"])) ? (($inline) ? "/s2member-file-inline" : "/s2member-file-inline-no") : "";
													$url .= (isset($req["file_storage"])) ? (($storage && $_url_e_storage) ? "/s2member-file-storage-".$_url_e_storage : "") : "";
													$url .= (isset($req["file_remote"])) ? (($remote) ? "/s2member-file-remote" : "/s2member-file-remote-no") : "";
													$url .= (isset($req["skip_confirmation"])) ? (($skip_confirmation) ? "/s2member-skip-confirmation" : "/s2member-skip-confirmation-no") : "";

													$url = /* File Download Access URL via `mod_rewrite` functionality. */ $url."/".$_url_e_file;
													$url = ($ssl) ? preg_replace("/^https?/", "https", $url) : preg_replace("/^https?/", "http", $url);

													return apply_filters("ws_plugin__s2member_file_download_access_url", $url, get_defined_vars());
												}

											else if /* Else we're creating a URL w/ a query-string; w/ local storage. */($creating)
												{ // Note: we don't URL encode unreserved chars. Improves media player compatibility.
													$_url_e_key = ($key) ? c_ws_plugin__s2member_utils_strings::urldecode_ur_chars_deep(urlencode($key)) : "";
													$_url_e_storage = ($storage) ? c_ws_plugin__s2member_utils_strings::urldecode_ur_chars_deep(urlencode($storage)) : "";
													$_url_e_file = c_ws_plugin__s2member_utils_strings::urldecode_ur_chars_deep(urlencode($req["file_download"]));
													$_url_e_file = str_ireplace("%2F", "/", $_url_e_file);

													$url = (isset($req["file_download_key"])) ? (($key && $_url_e_key) ? "&s2member_file_download_key=".$_url_e_key : "") : "";
													$url .= (isset($req["file_stream"])) ? (($stream) ? "&s2member_file_stream=yes" : "&s2member_file_stream=no") : "";
													$url .= (isset($req["file_inline"])) ? (($inline) ? "&s2member_file_inline=yes" : "&s2member_file_inline=no") : "";
													$url .= (isset($req["file_storage"])) ? (($storage && $_url_e_storage) ? "&s2member_file_storage=".$_url_e_storage : "") : "";
													$url .= (isset($req["file_remote"])) ? (($remote) ? "&s2member_file_remote=yes" : "&s2member_file_remote=no") : "";
													$url .= (isset($req["skip_confirmation"])) ? (($skip_confirmation) ? "&s2member_skip_confirmation=yes" : "&s2member_skip_confirmation=no") : "";

													$url = site_url("/?".ltrim($url."&s2member_file_download=/".$_url_e_file, "&"));
													$url = ($ssl) ? preg_replace("/^https?/", "https", $url) : preg_replace("/^https?/", "http", $url);

													return apply_filters("ws_plugin__s2member_file_download_access_url", $url, get_defined_vars());
												}

											else if /* Else, ``if ($serving)``, use local storage. */($serving)
												{
													@set_time_limit(0);

													@ini_set("zlib.output_compression", 0);
													if(function_exists("apache_setenv"))
														@apache_setenv("no-gzip", "1");

													$content_encoding_header = "Content-Encoding:"; // Default value; standards compliant.
													if($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["file_download_content_encodong_none"])
														$content_encoding_header = "Content-Encoding: none";

													while /* Cleans existing output buffers. */(@ob_end_clean());

													if /* Requesting a specific byte range? */($range)
														{
															if /* Invalid range? */(strpos($range, "=") === FALSE)
																{
																	status_header(416);
																	nocache_headers();
																	header($content_encoding_header);
																	header("Accept-Ranges: bytes");
																	header("Content-Type: ".$mimetype);
																	header("Content-Length: ".$length);
																	header("Content-Disposition: ".$disposition);
																	exit /* Stop here (invalid). */();
																}
															list($range_type, $byte_range) = preg_split("/\s*\=\s*/", $range, 2);

															$range_type = strtolower(trim($range_type));
															$byte_range = trim($byte_range);

															if /* Invalid range type? */($range_type !== "bytes")
																{
																	status_header(416);
																	nocache_headers();
																	header($content_encoding_header);
																	header("Accept-Ranges: bytes");
																	header("Content-Type: ".$mimetype);
																	header("Content-Length: ".$length);
																	header("Content-Disposition: ".$disposition);
																	exit /* Stop here (invalid). */();
																}
															$byte_ranges = preg_split("/\s*,\s*/", $byte_range);

															if /* Invalid byte range? */(strpos($byte_ranges[0], "-") === FALSE)
																{
																	status_header(416);
																	nocache_headers();
																	header($content_encoding_header);
																	header("Accept-Ranges: bytes");
																	header("Content-Type: ".$mimetype);
																	header("Content-Length: ".$length);
																	header("Content-Disposition: ".$disposition);
																	exit /* Stop here (invalid). */();
																}

															// Only dealing with the first byte range. Others are simply ignored here.
															list($byte_range_start, $byte_range_stops) = preg_split("/\s*\-\s*/", $byte_ranges[0], 2);

															$byte_range_start = trim($byte_range_start);
															$byte_range_stops = trim($byte_range_stops);

															$byte_range_start = ($byte_range_start === "") ? NULL : (integer)$byte_range_start;
															$byte_range_stops = ($byte_range_stops === "") ? NULL : (integer)$byte_range_stops;

															if(!isset($byte_range_start) && $byte_range_stops > 0 && $byte_range_stops <= $length)
																{
																	$byte_range_start = $length - $byte_range_stops;
																	$byte_range_stops = /* The last X number of bytes. */ $length - 1;
																}
															else if(!isset($byte_range_stops) && $byte_range_start >= 0 && $byte_range_start < $length - 1)
																{
																	$byte_range_stops = /* To the end of the file in this case. */ $length - 1;
																}
															else if(isset($byte_range_start, $byte_range_stops) && $byte_range_start >= 0 && $byte_range_start < $length - 1 && $byte_range_stops > $byte_range_start && $byte_range_stops <= $length - 1) {
															// Nothing to do in this case, starts/stops already defined properly.
															}
															else // We have an invalid byte range.
																{
																	status_header(416);
																	nocache_headers();
																	header($content_encoding_header);
																	header("Accept-Ranges: bytes");
																	header("Content-Type: ".$mimetype);
																	header("Content-Length: ".$length);
																	header("Content-Disposition: ".$disposition);
																	exit /* Stop here (invalid). */();
																}
															// Range.
															status_header(206);
															nocache_headers();
															header($content_encoding_header);
															header("Accept-Ranges: bytes");
															header("Content-Type: ".$mimetype);
															header("Content-Range: bytes ".$byte_range_start."-".$byte_range_stops."/".$length);
															$byte_range_size = $byte_range_stops - $byte_range_start + 1;
															header("Content-Length: ".$byte_range_size);
															header("Content-Disposition: ".$disposition);
														}
													else // A normal request (NOT a specific byte range).
														{
															status_header(200);
															nocache_headers();
															header($content_encoding_header);
															header("Accept-Ranges: bytes");
															header("Content-Type: ".$mimetype);
															header("Content-Length: ".$length);
															header("Content-Disposition: ".$disposition);
														}
													if(is_resource($resource = fopen($file, "rb")))
														{
															if($range)
																{
																	$_bytes_to_read = $byte_range_size;
																	fseek($resource, $byte_range_start);
																}
															else // Entire file.
																$_bytes_to_read = $length;

															$chunk_size = apply_filters("ws_plugin__s2member_file_downloads_chunk_size", 2097152, get_defined_vars());

															while /* We have bytes to read here. */($_bytes_to_read)
																{
																	$_bytes_to_read -= ($_reading = ($_bytes_to_read > $chunk_size) ? $chunk_size : $_bytes_to_read);
																	echo /* Serve file in chunks (default chunk size is 2MB). */ fread($resource, $_reading);
																	flush /* Flush each chunk to the browser as it is served (avoids high memory consumption). */();
																}
															fclose /* Close file resource handle. */($resource);
															unset /* Housekeeping. */($_bytes_to_read, $_reading);
														}
													exit /* Stop execution now (the file has been served). */();
												}
										}
								}

							else if /* We only need this section when/if we're actually serving. */($serving && $req["file_download"])
							{
								status_header(503);
								header("Content-Type: text/html; charset=UTF-8");
								while (@ob_end_clean ()); // Clean any existing output buffers.
								exit(_x('<strong>503: Access denied.</strong> Invalid File Download specs.', "s2member-front", "s2member"));
							}
							else if /* We only need this section when/if we're creating a URL. */($creating)
								return false;

						do_action("ws_plugin__s2member_after_file_download_access", get_defined_vars());

						return ($creating) ? /* If creating, false. */ false : null;
					}
				/**
				* Generates a File Download URL for access to a file protected by s2Member.
				*
				* @package s2Member\Files
				* @since 110926
				*
				* @param array $config Required. This is an array of configuration options associated with permissions being checked against the current User/Member; and also the actual URL generated by this routine.
				* 	Possible ``$config`` array elements: `file_download` *(required)*, `file_download_key`, `file_stream`, `file_inline`, `file_storage`, `file_remote`, `file_ssl`, `file_rewrite`, `file_rewrite_base`, `skip_confirmation`, `url_to_storage_source`, `count_against_user`, `check_user`.
				* @param bool $get_streamer_array Optional. Defaults to `false`. If `true`, this function will return an array with the following elements: `streamer`, `file`, `url`. For further details, please review this section in your Dashboard: `s2Member -› Download Options -› JW Player & RTMP Protocol Examples`.
				* @return string A File Download URL string on success; or an array on success, with elements `streamer`, `file`, `url` when/if ``$get_streamer_array`` is true; else false on any type of failure.
				*
				* @see s2Member\API_Functions\s2member_file_download_url()
				*/
				public static function create_file_download_url($config = FALSE, $get_streamer_array = FALSE)
					{
						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_before_create_file_download_url", get_defined_vars());
						unset($__refs, $__v);

						$config = (is_array($config)) ? $config : /* This absolutely MUST be an array. */ array();

						$config["file_download"] = (isset($config["file_download"]) && is_string($config["file_download"])) ? trim($config["file_download"], "/") : @$config["file_download"];
						$config["file_download_key"] = (isset($config["file_download"]) && is_string($config["file_download"]) && !empty($config["file_download_key"])) ? c_ws_plugin__s2member_files::file_download_key($config["file_download"], ((in_array($config["file_download_key"], array("ip-forever", "universal", "cache-compatible"))) ? $config["file_download_key"] : false)) : @$config["file_download_key"];

						$config["url_to_storage_source"] = /* Force a streaming URL here via ``$get_streamer_array``? */ ($get_streamer_array) ? true : @$config["url_to_storage_source"];
						$config["file_stream"] = /* Force a streaming URL here via ``$get_streamer_array``? */ ($get_streamer_array) ? true : @$config["file_stream"];

						if(($_url = c_ws_plugin__s2member_files_in::check_file_download_access /* Successfully created a URL to the file? */(($create_file_download_url = $config))))
							{
								foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
								do_action("ws_plugin__s2member_during_create_file_download_url", get_defined_vars());
								unset($__refs, $__v);

								$extension = strtolower(substr($config["file_download"], strrpos($config["file_download"], ".") + 1));
								$streaming = (isset($config["file_stream"])) ? filter_var($config["file_stream"], FILTER_VALIDATE_BOOLEAN) : ((in_array($extension, preg_split("/[\r\n\t\s;,]+/", $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["file_download_stream_extensions"]))) ? true : false);
								$ssl = (isset($config["file_ssl"])) ? filter_var($config["file_ssl"], FILTER_VALIDATE_BOOLEAN) : ((is_ssl()) ? true : false);

								if($get_streamer_array && $streaming && ($cfx = "/cfx/st") && ($cfx_pos = strpos($_url, $cfx)) !== false && ($streamer = substr($_url, 0, $cfx_pos + strlen($cfx))) && ($url = c_ws_plugin__s2member_files_in::check_file_download_access(array_merge($config, array("file_stream" => false, "check_user" => false, "count_against_user" => false)))))
									$return = array("streamer" => $streamer, "prefix" => $extension.":", "file" => preg_replace("/^".preg_quote($streamer, "/")."\//", "", $_url), "url" => preg_replace("/^.+?\:/", (($ssl) ? "https:" : "http:"), $url));

								else if($get_streamer_array && $streaming && is_array($ups = c_ws_plugin__s2member_utils_urls::parse_url($_url)) && isset($ups["scheme"], $ups["host"]) && ($streamer = $ups["scheme"]."://".$ups["host"].((!empty($ups["port"])) ? ":".$ups["port"] : "")) && ($url = c_ws_plugin__s2member_files_in::check_file_download_access(array_merge($config, array("file_stream" => false, "check_user" => false, "count_against_user" => false)))))
									$return = array("streamer" => $streamer, "prefix" => $extension.":", "file" => preg_replace("/^".preg_quote($streamer, "/")."\//", "", $_url), "url" => preg_replace("/^.+?\:/", (($ssl) ? "https:" : "http:"), $url));

								else if /* If streamer, we MUST return false here; unable to acquire streamer/file. */($get_streamer_array)
									$return = /* We MUST return false here, unable to acquire streamer/file. */ false;

								else // Else return URL string ( ``$get_streamer_array`` is false ).
									$return = /* Else return URL string. */ $_url;
							}

						return apply_filters("ws_plugin__s2member_create_file_download_url", ((isset($return)) ? $return : false), get_defined_vars());
					}
				/**
				* Checks Header Authorization for Remote File Downloads.
				*
				* @package s2Member\Files
				* @since 110926
				*
				* @attaches-to ``add_filter("ws_plugin__s2member_check_file_download_access_user");``
				*
				* @param object $user Expects a WP_User object passed in by the Filter.
				* @return obj A `WP_User` object, possibly obtained through Header Authorization.
				*/
				public static function check_file_remote_authorization($user = FALSE)
					{
						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_before_check_file_remote_authorization", get_defined_vars());
						unset($__refs, $__v);

						$_g = c_ws_plugin__s2member_utils_strings::trim_deep(stripslashes_deep(((!empty($_GET)) ? $_GET : array())));

						if(!is_object($user) && isset($_g["s2member_file_remote"]) && filter_var($_g["s2member_file_remote"], FILTER_VALIDATE_BOOLEAN))
							{
								do_action("ws_plugin__s2member_during_check_file_remote_authorization_before", get_defined_vars());

								if((empty($_SERVER["PHP_AUTH_USER"]) || $_SERVER["PHP_AUTH_USER"] === "NOUSER") && !empty($_SERVER["HTTP_AUTHORIZATION"]))
									{
										$auth = trim(preg_replace("/^.+?\s+/", "", $_SERVER["HTTP_AUTHORIZATION"]));
										$auth = explode(":", base64_decode($auth), 2);

										if(!empty($auth[0])) $_SERVER["PHP_AUTH_USER"] = $auth[0];
										if(!empty($auth[1])) $_SERVER["PHP_AUTH_PW"] = $auth[1];
									}
								if(empty($_SERVER["PHP_AUTH_USER"]) || empty($_SERVER["PHP_AUTH_PW"]) || !user_pass_ok($_SERVER["PHP_AUTH_USER"], $_SERVER["PHP_AUTH_PW"]))
									{
										header('WWW-Authenticate: Basic realm="'.c_ws_plugin__s2member_utils_strings::esc_dq(strip_tags(_x("Members Only", "s2member-front", "s2member"))).'"');

										status_header /* Send an unauthorized 401 status header now. */(401);
										header /* Content-Type with UTF-8. */("Content-Type: text/html; charset=UTF-8");
										while (@ob_end_clean ()); // Clean any existing output buffers.

										exit(_x('<strong>401:</strong> Sorry, access denied.', "s2member-front", "s2member"));
									}
								else if(is_object($_user = new WP_User($_SERVER["PHP_AUTH_USER"])) && !empty($_user->ID))
									$user = /* Now assign ``$user``. */ $_user;

								do_action("ws_plugin__s2member_during_check_file_remote_authorization_after", get_defined_vars());
							}
						return apply_filters("ws_plugin__s2member_check_file_remote_authorization", $user, get_defined_vars());
					}
				/**
				* Checks a File Download Key for validity.
				*
				* @package s2Member\Files
				* @since 110926
				*
				* @param string $file Input File Download to validate.
				* @param string $key Input File Download Key to validate.
				* @return bool True if valid, else false.
				*/
				public static function check_file_download_key($file = FALSE, $key = FALSE)
					{
						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("_ws_plugin__s2member_before_check_file_download_key", get_defined_vars());
						unset($__refs, $__v);

						if($file && is_string($file) && ($file = trim($file, "/")) && $key && is_string($key))
							{
								if($key === c_ws_plugin__s2member_files::file_download_key($file) || $key === c_ws_plugin__s2member_files::file_download_key("/".$file))
									$valid = /* File Download Key is valid. */ true;
								else if($key === c_ws_plugin__s2member_files::file_download_key($file, "ip-forever") || $key === c_ws_plugin__s2member_files::file_download_key("/".$file, "ip-forever"))
									$valid = /* File Download Key is valid. */ true;
								else if($key === c_ws_plugin__s2member_files::file_download_key($file, "universal") || $key === c_ws_plugin__s2member_files::file_download_key("/".$file, "universal"))
									$valid = /* File Download Key is valid. */ true;
							}
						return apply_filters("ws_plugin__s2member_check_file_download_key", ((isset($valid) && $valid) ? true : false), get_defined_vars());
					}
				/**
				* Creates an Amazon S3 HMAC-SHA1 signature.
				*
				* @package s2Member\Files
				* @since 110524RC
				*
				* @param string $string Input string/data, to be signed by this routine.
				* @return string An HMAC-SHA1 signature for Amazon S3.
				*/
				public static function amazon_s3_sign($string = FALSE)
					{
						$s3c["secret_key"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_s3_files_secret_key"];

						return c_ws_plugin__s2member_utils_strings::hmac_sha1_sign((string)$string, $s3c["secret_key"]);
					}
				/**
				* Creates an Amazon S3 HMAC-SHA1 signature URL.
				*
				* @package s2Member\Files
				* @since 110926
				*
				* @param string $file Input file path, to be signed by this routine.
				* @param bool $stream Is this resource file to be served as streaming media?
				* @param bool $inline Is this resource file to be served inline, or no?
				* @param bool $ssl Is this resource file to be served via SSL, or no?
				* @param string $basename The absolute basename of the resource file.
				* @param string $mimetype The MIME content-type of the resource file.
				* @return string An HMAC-SHA1 signature URL for Amazon S3.
				*/
				public static function amazon_s3_url($file = FALSE, $stream = FALSE, $inline = FALSE, $ssl = FALSE, $basename = FALSE, $mimetype = FALSE)
					{
						$file = /* Trim / force string. */ trim((string)$file, "/");
						$url_e_file = c_ws_plugin__s2member_utils_strings::urldecode_ur_chars_deep(urlencode($file));
						$url_e_file = str_ireplace("%2F", "/", $url_e_file);

						foreach($GLOBALS["WS_PLUGIN__"]["s2member"]["o"] as $option => $option_value)
							if(preg_match("/^amazon_s3_files_/", $option) && ($option = preg_replace("/^amazon_s3_files_/", "", $option)))
								$s3c[$option] = $option_value;

						$s3c["expires"] = strtotime("+".apply_filters("ws_plugin__s2member_amazon_s3_file_expires_time", "24 hours", get_defined_vars()));

						$s3_file = add_query_arg(c_ws_plugin__s2member_utils_strings::urldecode_ur_chars_deep(urlencode_deep(array("response-cache-control" => ($s3_cache_control = "no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0"), "response-content-disposition" => ($s3_content_disposition = (((bool)$inline) ? "inline" : "attachment").'; filename="'.(string)$basename.'"'), "response-content-type" => ($s3_content_type = (string)$mimetype), "response-expires" => ($s3_expires = gmdate("D, d M Y H:i:s", strtotime("-1 week"))." GMT")))), "/".$url_e_file);
						$s3_raw_file = add_query_arg(array("response-cache-control" => $s3_cache_control, "response-content-disposition" => $s3_content_disposition, "response-content-type" => $s3_content_type, "response-expires" => $s3_expires), "/".$url_e_file);
						$s3_signature = base64_encode(c_ws_plugin__s2member_files_in::amazon_s3_sign("GET\n\n\n".$s3c["expires"]."\n"."/".$s3c["bucket"].$s3_raw_file));

						$s3_url = ((strtolower($s3c["bucket"]) !== $s3c["bucket"])) ? "http".(($ssl) ? "s" : "")."://s3.amazonaws.com/".$s3c["bucket"].$s3_file : "http".(($ssl) ? "s" : "")."://".$s3c["bucket"].".s3.amazonaws.com".$s3_file;

						return add_query_arg(c_ws_plugin__s2member_utils_strings::urldecode_ur_chars_deep (urlencode_deep(array("AWSAccessKeyId" => $s3c["access_key"], "Expires" => $s3c["expires"], "Signature" => $s3_signature))), $s3_url);
					}
				/**
				* Auto-configures an Amazon S3 Bucket's ACLs.
				*
				* @package s2Member\Files
				* @since 110926
				*
				* @return array Array containing a true `success` element on success, else a failure array.
				* 	Failure array will contain a failure `code`, and a failure `message`.
				*/
				public static function amazon_s3_auto_configure_acls()
					{
						foreach($GLOBALS["WS_PLUGIN__"]["s2member"]["o"] as $option => $option_value)
							if(preg_match("/^amazon_s3_files_/", $option) && ($option = preg_replace("/^amazon_s3_files_/", "", $option)))
								$s3c[$option] = $option_value;

						$cfc["distros_s3_access_id"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_cf_files_distros_s3_access_id"];

						if /* Must have Amazon S3 Bucket/Keys. */($s3c["bucket"] && $s3c["access_key"] && $s3c["secret_key"])
							{
								$s3_date = gmdate("D, d M Y H:i:s")." GMT";
								$s3_location = ((strtolower($s3c["bucket"]) !== $s3c["bucket"])) ? "/".$s3c["bucket"]."/?acl" : "/?acl";
								$s3_domain = ((strtolower($s3c["bucket"]) !== $s3c["bucket"])) ? "s3.amazonaws.com" : $s3c["bucket"].".s3.amazonaws.com";
								$s3_signature = base64_encode(c_ws_plugin__s2member_files_in::amazon_s3_sign("GET\n\n\n".$s3_date."\n/".$s3c["bucket"]."/?acl"));
								$s3_args = array("method" => "GET", "redirection" => 5, "headers" => array("Host" => $s3_domain, "Date" => $s3_date, "Authorization" => "AWS ".$s3c["access_key"].":".$s3_signature));

								if(($s3_response = c_ws_plugin__s2member_utils_urls::remote("https://".$s3_domain.$s3_location, false, array_merge($s3_args, array("timeout" => 20)), "array")) && $s3_response["code"] === 200)
									{
										if(preg_match("/\<Owner\>(.+?)\<\/Owner\>/is", $s3_response["body"], $s3_owner_tag) && preg_match("/\<ID\>(.+?)\<\/ID\>/is", $s3_owner_tag[1], $s3_owner_id_tag) && (preg_match("/\<DisplayName\>(.*?)\<\/DisplayName\>/is", $s3_owner_tag[1], $s3_owner_display_name_tag) || ($s3_owner_display_name_tag = array("-", "Owner"))))
											{
												$s3_owner = array("access_id" => trim($s3_owner_id_tag[1]), "display_name" => trim($s3_owner_display_name_tag[1]));
												$s3_acls_xml = '<AccessControlPolicy><Owner><ID>'.esc_html($s3_owner["access_id"]).'</ID><DisplayName>'.esc_html($s3_owner["display_name"]).'</DisplayName></Owner><AccessControlList><Grant><Grantee xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="CanonicalUser"><ID>'.esc_html($s3_owner["access_id"]).'</ID><DisplayName>'.esc_html($s3_owner["display_name"]).'</DisplayName></Grantee><Permission>FULL_CONTROL</Permission></Grant>'.(($cfc["distros_s3_access_id"]) ? '<Grant><Grantee xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="CanonicalUser"><ID>'.esc_html($cfc["distros_s3_access_id"]).'</ID><DisplayName>s2Member/CloudFront</DisplayName></Grantee><Permission>READ</Permission></Grant>' : '').'</AccessControlList></AccessControlPolicy>';
												$s3_signature = base64_encode(c_ws_plugin__s2member_files_in::amazon_s3_sign("PUT\n\napplication/xml\n".$s3_date."\n/".$s3c["bucket"]."/?acl"));
												$s3_args = array("method" => "PUT", "redirection" => 5, "body" => $s3_acls_xml, "headers" => array("Host" => $s3_domain, "Content-Type" => "application/xml", "Date" => $s3_date, "Authorization" => "AWS ".$s3c["access_key"].":".$s3_signature));

												if(($s3_response = c_ws_plugin__s2member_utils_urls::remote("https://".$s3_domain.$s3_location, false, array_merge($s3_args, array("timeout" => 20)), "array")) && $s3_response["code"] === 200)
													{
														$s3_location = ((strtolower($s3c["bucket"]) !== $s3c["bucket"])) ? "/".$s3c["bucket"]."/?policy" : "/?policy";
														($s3_policy_id = md5(uniqid("s2Member/CloudFront:", true))).($s3_policy_sid = md5(uniqid("s2Member/CloudFront:", true)));
														$s3_policy_json = '{"Version":"2008-10-17","Id":"'.c_ws_plugin__s2member_utils_strings::esc_dq($s3_policy_id).'","Statement":[{"Sid":"'.c_ws_plugin__s2member_utils_strings::esc_dq($s3_policy_sid).'","Effect":"Allow","Principal":{"CanonicalUser":"'.c_ws_plugin__s2member_utils_strings::esc_dq($cfc["distros_s3_access_id"]).'"},"Action":"s3:GetObject","Resource":"arn:aws:s3:::'.c_ws_plugin__s2member_utils_strings::esc_dq($s3c["bucket"]).'/*"}]}';
														$s3_signature = base64_encode(c_ws_plugin__s2member_files_in::amazon_s3_sign("PUT\n\napplication/json\n".$s3_date."\n/".$s3c["bucket"]."/?policy"));
														$s3_args = array("method" => "PUT", "redirection" => 5, "body" => $s3_policy_json, "headers" => array("Host" => $s3_domain, "Content-Type" => "application/json", "Date" => $s3_date, "Authorization" => "AWS ".$s3c["access_key"].":".$s3_signature));

														if(!$cfc["distros_s3_access_id"] || (($s3_response = c_ws_plugin__s2member_utils_urls::remote("https://".$s3_domain.$s3_location, false, array_merge($s3_args, array("timeout" => 20)), "array")) && ($s3_response["code"] === 200 || $s3_response["code"] === 204 /* Also OK. */)))
															{
																$s3_location = ((strtolower($s3c["bucket"]) !== $s3c["bucket"])) ? "/".$s3c["bucket"]."/crossdomain.xml" : "/crossdomain.xml";
																$s3_policy_xml = trim(c_ws_plugin__s2member_utilities::evl(file_get_contents(dirname(dirname(__FILE__))."/templates/cfg-files/s2-cross-xml.php")));
																$s3_signature = base64_encode(c_ws_plugin__s2member_files_in::amazon_s3_sign("PUT\n\ntext/xml\n".$s3_date."\nx-amz-acl:public-read\n/".$s3c["bucket"]."/crossdomain.xml"));
																$s3_args = array("method" => "PUT", "redirection" => 5, "body" => $s3_policy_xml, "headers" => array("Host" => $s3_domain, "Content-Type" => "text/xml", "Date" => $s3_date, "X-Amz-Acl" => "public-read", "Authorization" => "AWS ".$s3c["access_key"].":".$s3_signature));

																if(($s3_response = c_ws_plugin__s2member_utils_urls::remote("https://".$s3_domain.$s3_location, false, array_merge($s3_args, array("timeout" => 20)), "array")) && $s3_response["code"] === 200)
																	return /* Successfully configured Amazon S3 Bucket ACLs and Policy. */ array("success" => true, "code" => null, "message" => null);

																else if(isset($s3_response["code"], $s3_response["message"]))
																	/* translators: In this translation, `%s` may be filled with an English message, which comes from the Amazon S3 API call. Feel free to exclude `%s` if you like. */
																	return array("success" => false, "code" => $s3_response["code"], "message" => sprintf(_x("Unable to update existing Amazon S3 Cross-Domain Policy. %s", "s2member-admin", "s2member"), $s3_response["message"]));

																else // Else, we use a default error code and message.
																	return array("success" => false, "code" => -94, "message" => _x("Unable to update existing Amazon S3 Cross-Domain Policy. Connection failed.", "s2member-admin", "s2member"));
															}
														else if(isset($s3_response["code"], $s3_response["message"]))
															/* translators: In this translation, `%s` may be filled with an English message, which comes from the Amazon S3 API call. Feel free to exclude `%s` if you like. */
															return array("success" => false, "code" => $s3_response["code"], "message" => sprintf(_x("Unable to update existing Amazon S3 Bucket Policy. %s", "s2member-admin", "s2member"), $s3_response["message"]));

														else // Else, we use a default error code and message.
															return array("success" => false, "code" => -95, "message" => _x("Unable to update existing Amazon S3 Bucket Policy. Connection failed.", "s2member-admin", "s2member"));
													}
												else if(isset($s3_response["code"], $s3_response["message"]))
													/* translators: In this translation, `%s` may be filled with an English message, which comes from the Amazon S3 API call. Feel free to exclude `%s` if you like. */
													return array("success" => false, "code" => $s3_response["code"], "message" => sprintf(_x("Unable to update existing Amazon S3 Bucket ACLs. %s", "s2member-admin", "s2member"), $s3_response["message"]));

												else // Else, we use a default error code and message.
													return array("success" => false, "code" => -96, "message" => _x("Unable to update existing Amazon S3 Bucket ACLs. Connection failed.", "s2member-admin", "s2member"));
											}
										else // Else, we use a default error code and message.
											return array("success" => false, "code" => -97, "message" => _x("Unable to acquire/read existing Amazon S3 Bucket ACLs. Unexpected response.", "s2member-admin", "s2member"));
									}
								else if(isset($s3_response["code"], $s3_response["message"]))
									/* translators: In this translation, `%s` may be filled with an English message, which comes from the Amazon S3 API call. Feel free to exclude `%s` if you like. */
									return array("success" => false, "code" => $s3_response["code"], "message" => sprintf(_x("Unable to acquire existing Amazon S3 Bucket ACLs. %s", "s2member-admin", "s2member"), $s3_response["message"]));

								else // Else, we use a default error code and message.
									return array("success" => false, "code" => -98, "message" => _x("Unable to acquire existing Amazon S3 Bucket ACLs. Connection failed.", "s2member-admin", "s2member"));
							}
						else // Else, we use a default error code and message.
							return array("success" => false, "code" => -99, "message" => _x("Unable to auto-configure existing Amazon S3 Bucket ACLs. Incomplete Amazon S3 configuration options. Missing one of: Amazon S3 Bucket, Access Key, or Secret Key.", "s2member-admin", "s2member"));
					}
				/**
				* Creates an Amazon CloudFront HMAC-SHA1 signature.
				*
				* @package s2Member\Files
				* @since 110926
				*
				* @param string $string Input string/data, to be signed by this routine.
				* @return string An HMAC-SHA1 signature for Amazon CloudFront.
				*/
				public static function amazon_cf_sign($string = FALSE)
					{
						$cfc["secret_key"] = $s3c["secret_key"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_s3_files_secret_key"];

						return c_ws_plugin__s2member_utils_strings::hmac_sha1_sign((string)$string, ($cfc["secret_key"] = $s3c["secret_key"]));
					}
				/**
				* Creates an Amazon CloudFront RSA-SHA1 signature.
				*
				* @package s2Member\Files
				* @since 110926
				*
				* @param string $string Input string/data, to be signed by this routine.
				* @return str|bool An RSA-SHA1 signature for Amazon CloudFront, else false on failure.
				*/
				public static function amazon_cf_rsa_sign($string = FALSE)
					{
						$cfc["private_key"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_cf_files_private_key"];

						return c_ws_plugin__s2member_utils_strings::rsa_sha1_sign((string)$string, $cfc["private_key"]);
					}
				/**
				* Creates an Amazon CloudFront RSA-SHA1 signature URL.
				*
				* @package s2Member\Files
				* @since 110926
				*
				* @param string $file Input file path, to be signed by this routine.
				* @param bool $stream Is this resource file to be served as streaming media?
				* @param bool $inline Is this resource file to be served inline, or no?
				* @param bool $ssl Is this resource file to be served via SSL, or no?
				* @param string $basename The absolute basename of the resource file.
				* @param string $mimetype The MIME content-type of the resource file.
				* @return string An RSA-SHA1 signature URL for Amazon CloudFront.
				*/
				public static function amazon_cf_url($file = FALSE, $stream = FALSE, $inline = FALSE, $ssl = FALSE, $basename = FALSE, $mimetype = FALSE)
					{
						$file = /* Trim & force string. */ trim((string)$file, "/");
						$url_e_file = c_ws_plugin__s2member_utils_strings::urldecode_ur_chars_deep(urlencode($file));
						$url_e_file = str_ireplace("%2F", "/", $url_e_file);

						foreach($GLOBALS["WS_PLUGIN__"]["s2member"]["o"] as $option => $option_value)
							if(preg_match("/^amazon_cf_files_/", $option) && ($option = preg_replace("/^amazon_cf_files_/", "", $option)))
								$cfc[$option] = $option_value;

						$cfc["expires"] = strtotime("+".apply_filters("ws_plugin__s2member_amazon_cf_file_expires_time", "24 hours", get_defined_vars()));

						$cf_extn = /* Parses the file extension out so we can scan it in some special scenarios. */ strtolower(substr($file, strrpos($file, ".") + 1));
						$cf_ip_res = /* Do NOT restrict access to a particular IP during `localhost` development. The IP may NOT be the same one Amazon CloudFront sees. */ (c_ws_plugin__s2member_utils_conds::is_localhost()) ? false : true;
						$cf_stream_extn_resource_exclusions = array_unique((array)apply_filters("ws_plugin__s2member_amazon_cf_file_streaming_extension_resource_exclusions", array("mp3" /* MP3 files should NOT include an extension in their resource reference. */), get_defined_vars()));
						$cf_resource = ($stream) ? ((in_array($cf_extn, $cf_stream_extn_resource_exclusions)) ? substr($file, 0, strrpos($file, ".")) : $file) : "http".(($ssl) ? "s" : "")."://".(($cfc["distro_downloads_cname"]) ? $cfc["distro_downloads_cname"] : $cfc["distro_downloads_dname"])."/".$url_e_file;
						$cf_url = ($stream) ? "rtmp".(($ssl) ? "e" : "")."://".(($cfc["distro_streaming_cname"]) ? $cfc["distro_streaming_cname"] : $cfc["distro_streaming_dname"])."/cfx/st/".$file : "http".(($ssl) ? "s" : "")."://".(($cfc["distro_downloads_cname"]) ? $cfc["distro_downloads_cname"] : $cfc["distro_downloads_dname"])."/".$url_e_file;
						$cf_policy = '{"Statement":[{"Resource":"'.c_ws_plugin__s2member_utils_strings::esc_dq($cf_resource).'","Condition":{'.(($cf_ip_res) ? '"IpAddress":{"AWS:SourceIp":"'.c_ws_plugin__s2member_utils_strings::esc_dq($_SERVER["REMOTE_ADDR"]).'/32"},' : '').'"DateLessThan":{"AWS:EpochTime":'.(int)$cfc["expires"].'}}}]}';

						$cf_signature = c_ws_plugin__s2member_files_in::amazon_cf_rsa_sign($cf_policy);
						$cf_base64_url_safe_policy = c_ws_plugin__s2member_utils_strings::base64_url_safe_encode($cf_policy, array("+", "=", "/"), array("-", "_", "~"), false);
						$cf_base64_url_safe_signature = c_ws_plugin__s2member_utils_strings::base64_url_safe_encode($cf_signature, array("+", "=", "/"), array("-", "_", "~"), false);

						return add_query_arg(c_ws_plugin__s2member_utils_strings::urldecode_ur_chars_deep(urlencode_deep(array("Policy" => $cf_base64_url_safe_policy, "Signature" => $cf_base64_url_safe_signature, "Key-Pair-Id" => $cfc["private_key_id"]))), $cf_url);
					}
				/**
				* Auto-configures Amazon S3/CloudFront distros.
				*
				* @package s2Member\Files
				* @since 110926
				*
				* @return array Array containing a true `success` element on success, else a failure array.
				* 	Failure array will contain a failure `code`, and a failure `message`.
				*/
				public static function amazon_cf_auto_configure_distros()
					{
						foreach($GLOBALS["WS_PLUGIN__"]["s2member"]["o"] as $option => $option_value)
							if(preg_match("/^amazon_cf_files_/", $option) && ($option = preg_replace("/^amazon_cf_files_/", "", $option)))
								$cfc[$option] = $option_value;

						$s3c["bucket"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_s3_files_bucket"];
						$cfc["access_key"] = $s3c["access_key"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_s3_files_access_key"];
						$cfc["secret_key"] = $s3c["secret_key"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_s3_files_secret_key"];

						if /* We MUST have an Amazon S3 Bucket and Keys. */($s3c["bucket"] && $s3c["access_key"] && $s3c["secret_key"])
							{
								if /* We MUST have Amazon CloudFront Keys in order to auto-configure. */($cfc["private_key"] && $cfc["private_key_id"])
									{
										if(!$cfc["distro_downloads_id"] || ($cfc["distro_downloads_id"] && ($cf_get_response = c_ws_plugin__s2member_files_in::amazon_cf_get_distro($cfc["distro_downloads_id"], "downloads")) && ($cf_get_response["success"] || $cf_get_response["code"] === 404)))
											{
												if(!$cfc["distro_downloads_id"] || ($cfc["distro_downloads_id"] && $cf_get_response && !$cf_get_response["success"] && $cf_get_response["code"] === 404))
													$cf_distro_downloads_clear = /* Clear, ready for a new one. */ true;

												else if($cfc["distro_downloads_id"] && $cf_get_response && $cf_get_response["success"] && !$cf_get_response["deployed"])
													return array("success" => false, "code" => -86, "message" => _x("Unable to delete existing Amazon CloudFront Downloads Distro. Still in a `pending` state. Please wait 15 minutes, then try again. There is a certain process that s2Member must strictly adhere to when re-configuring your Amazon CloudFront Distros. You may have to tick the auto-configure checkbox again, and re-run s2Member's auto-configuration routine many times, because s2Member will likely run into several `pending` challenges, as it works to completely re-configure your Amazon CloudFront Distros for you. Thanks for your patience. Please wait 15 minutes, then try again.", "s2member-admin", "s2member"));

												else if($cfc["distro_downloads_id"] && $cf_get_response && $cf_get_response["success"] && $cf_get_response["deployed"] && ($cf_del_response = c_ws_plugin__s2member_files_in::amazon_cf_del_distro($cfc["distro_downloads_id"], $cf_get_response["etag"], $cf_get_response["xml"])) && $cf_del_response["success"])
													$cf_distro_downloads_clear = /* Clear, ready for a new one. */ true;

												else if(isset($cf_del_response["code"], $cf_del_response["message"]))
													/* translators: In this translation, `%s` may be filled with an English message, which comes from the Amazon CloudFront API call. Feel free to exclude `%s` if you like. */
													return array("success" => false, "code" => $cf_del_response["code"], "message" => sprintf(_x("Unable to delete existing Amazon CloudFront Downloads Distro. %s", "s2member-admin", "s2member"), $cf_del_response["message"]));

												if /* Successfully cleared? Ready for a new one? */(isset($cf_distro_downloads_clear) && $cf_distro_downloads_clear)
													{
														unset /* Unset these before processing additional routines. Prevents problems in error reporting. */($cf_get_response, $cf_del_response);

														if(!$cfc["distro_streaming_id"] || ($cfc["distro_streaming_id"] && ($cf_get_response = c_ws_plugin__s2member_files_in::amazon_cf_get_distro($cfc["distro_streaming_id"], "streaming")) && ($cf_get_response["success"] || $cf_get_response["code"] === 404)))
															{
																if(!$cfc["distro_streaming_id"] || ($cfc["distro_streaming_id"] && $cf_get_response && !$cf_get_response["success"] && $cf_get_response["code"] === 404))
																	$cf_distro_streaming_clear = /* Clear, ready for a new one. */ true;

																else if($cfc["distro_streaming_id"] && $cf_get_response && $cf_get_response["success"] && !$cf_get_response["deployed"])
																	return array("success" => false, "code" => -87, "message" => _x("Unable to delete existing Amazon CloudFront Streaming Distro. Still in a `pending` state. Please wait 15 minutes, then try again. There is a certain process that s2Member must strictly adhere to when re-configuring your Amazon CloudFront Distros. You may have to tick the auto-configure checkbox again, and re-run s2Member's auto-configuration routine many times, because s2Member will likely run into several `pending` challenges, as it works to completely re-configure your Amazon CloudFront Distros for you. Thanks for your patience. Please wait 15 minutes, then try again.", "s2member-admin", "s2member"));

																else if($cfc["distro_streaming_id"] && $cf_get_response && $cf_get_response["success"] && $cf_get_response["deployed"] && ($cf_del_response = c_ws_plugin__s2member_files_in::amazon_cf_del_distro($cfc["distro_streaming_id"], $cf_get_response["etag"], $cf_get_response["xml"])) && $cf_del_response["success"])
																	$cf_distro_streaming_clear = /* Clear, ready for a new one. */ true;

																else if(isset($cf_del_response["code"], $cf_del_response["message"]))
																	/* translators: In this translation, `%s` may be filled with an English message, which comes from the Amazon CloudFront API call. Feel free to exclude `%s` if you like. */
																	return array("success" => false, "code" => $cf_del_response["code"], "message" => sprintf(_x("Unable to delete existing Amazon CloudFront Streaming Distro. %s", "s2member-admin", "s2member"), $cf_del_response["message"]));

																if /* Successfully cleared? Ready for a new one? */(isset($cf_distro_streaming_clear) && $cf_distro_streaming_clear)
																	{
																		unset /* Unset these before processing additional routines. Prevents problems in error reporting. */($cf_get_response, $cf_del_response);

																		if(!$cfc["distros_access_id"] || ($cfc["distros_access_id"] && ($cf_get_response = c_ws_plugin__s2member_files_in::amazon_cf_get_access_origin_identity($cfc["distros_access_id"])) && ($cf_get_response["success"] || $cf_get_response["code"] === 404)))
																			{
																				if(!$cfc["distros_access_id"] || ($cfc["distros_access_id"] && $cf_get_response && !$cf_get_response["success"] && $cf_get_response["code"] === 404))
																					$cf_distros_access_clear = /* Clear, ready for a new one. */ true;

																				else if($cfc["distros_access_id"] && $cf_get_response && $cf_get_response["success"] && ($cf_del_response = c_ws_plugin__s2member_files_in::amazon_cf_del_access_origin_identity($cfc["distros_access_id"], $cf_get_response["etag"], $cf_get_response["xml"])) && $cf_del_response["success"])
																					$cf_distros_access_clear = /* Clear, ready for a new one. */ true;

																				else if(isset($cf_del_response["code"], $cf_del_response["message"]))
																					/* translators: In this translation, `%s` may be filled with an English message, which comes from the Amazon CloudFront API call. Feel free to exclude `%s` if you like. */
																					return array("success" => false, "code" => $cf_del_response["code"], "message" => sprintf(_x("Unable to delete existing Amazon CloudFront Origin Access Identity. %s", "s2member-admin", "s2member"), $cf_del_response["message"]));

																				if /* Successfully cleared? Ready for a new one? */(isset($cf_distros_access_clear) && $cf_distros_access_clear)
																					{
																						unset /* Unset these before processing additional routines. Prevents problems in error reporting. */($cf_get_response, $cf_del_response);

																						$cfc = array_merge($cfc, array("distros_access_id" => "", "distros_s3_access_id" => "", "distro_downloads_id" => "", "distro_downloads_dname" => "", "distro_streaming_id" => "", "distro_streaming_dname" => "", "distros_auto_config_status" => ""));
																						$cf_options = array("ws_plugin__s2member_amazon_cf_files_distros_access_id" => "", "ws_plugin__s2member_amazon_cf_files_distros_s3_access_id" => "", "ws_plugin__s2member_amazon_cf_files_distro_downloads_id" => "", "ws_plugin__s2member_amazon_cf_files_distro_downloads_dname" => "", "ws_plugin__s2member_amazon_cf_files_distro_streaming_id" => "", "ws_plugin__s2member_amazon_cf_files_distro_streaming_dname" => "", "ws_plugin__s2member_amazon_cf_files_distros_auto_config_status" => "");
																						c_ws_plugin__s2member_menu_pages::update_all_options($cf_options, true, false, false, false, false);

																						if(($cf_response = c_ws_plugin__s2member_files_in::amazon_cf_create_distros_access_origin_identity()) && $cf_response["success"])
																							{
																								$cfc = array_merge($cfc, array("distros_access_id" => $cf_response["distros_access_id"], "distros_s3_access_id" => $cf_response["distros_s3_access_id"]));
																								$cf_options = array("ws_plugin__s2member_amazon_cf_files_distros_access_id" => $cf_response["distros_access_id"], "ws_plugin__s2member_amazon_cf_files_distros_s3_access_id" => $cf_response["distros_s3_access_id"]);
																								c_ws_plugin__s2member_menu_pages::update_all_options($cf_options, true, false, false, false, false);

																								if(($cf_response = c_ws_plugin__s2member_files_in::amazon_cf_create_distro("downloads")) && $cf_response["success"])
																									{
																										$cfc = array_merge($cfc, array("distro_downloads_id" => $cf_response["distro_downloads_id"], "distro_downloads_dname" => $cf_response["distro_downloads_dname"]));
																										$cf_options = array("ws_plugin__s2member_amazon_cf_files_distro_downloads_id" => $cf_response["distro_downloads_id"], "ws_plugin__s2member_amazon_cf_files_distro_downloads_dname" => $cf_response["distro_downloads_dname"]);
																										c_ws_plugin__s2member_menu_pages::update_all_options($cf_options, true, false, false, false, false);

																										if(($cf_response = c_ws_plugin__s2member_files_in::amazon_cf_create_distro("streaming")) && $cf_response["success"])
																											{
																												$cfc = array_merge($cfc, array("distro_streaming_id" => $cf_response["distro_streaming_id"], "distro_streaming_dname" => $cf_response["distro_streaming_dname"]));
																												$cf_options = array("ws_plugin__s2member_amazon_cf_files_distro_streaming_id" => $cf_response["distro_streaming_id"], "ws_plugin__s2member_amazon_cf_files_distro_streaming_dname" => $cf_response["distro_streaming_dname"]);
																												c_ws_plugin__s2member_menu_pages::update_all_options($cf_options, true, false, false, false, false);

																												for($a = 1, $attempts = 4, $sleep = 2, sleep($sleep); $a <= $attempts; $a++, (($a <= $attempts) ? sleep($sleep) : null))
																													/* Allow a generous propagation time here. Amazon's high-availability services do NOT guarantee real-time updates.
																														Since we DO need a fully propagated Origin Access Identity now, we need to make several attempts at success.
																														For further details, please see this thread: <https://forums.aws.amazon.com/message.jspa?messageID=42875>. */
																													if(($s3_response = c_ws_plugin__s2member_files_in::amazon_s3_auto_configure_acls()) && $s3_response["success"])
																														{
																															$cfc = array_merge($cfc, array("distros_auto_config_status" => "configured"));
																															$cf_options = array("ws_plugin__s2member_amazon_cf_files_distros_auto_config_status" => "configured");
																															c_ws_plugin__s2member_menu_pages::update_all_options( /* Now configured! */$cf_options, true, false, false, false, false);
																															return /* Successfully configured Amazon S3/CloudFront distros. */ array("success" => true, "code" => null, "message" => null);
																														}
																												if(isset($s3_response["code"], $s3_response["message"]))
																													/* translators: In this translation, `%s` may be filled with an English message, which comes from the Amazon S3 API call. Feel free to exclude `%s` if you like. */
																													return array("success" => false, "code" => $s3_response["code"], "message" => sprintf(_x("Unable to update existing Amazon S3 ACLs. %s", "s2member-admin", "s2member"), $s3_response["message"]));

																												else // Else, we use a default error code and message.
																													return array("success" => false, "code" => -88, "message" => _x("Unable to update existing Amazon S3 ACLs. Connection failed.", "s2member-admin", "s2member"));
																											}
																										else if(isset($cf_response["code"], $cf_response["message"]))
																											/* translators: In this translation, `%s` may be filled with an English message, which comes from the Amazon CloudFront API call. Feel free to exclude `%s` if you like. */
																											return array("success" => false, "code" => $cf_response["code"], "message" => sprintf(_x("Unable to create Amazon CloudFront Streaming Distro. %s", "s2member-admin", "s2member"), $cf_response["message"]));

																										else // Else, we use a default error code and message.
																											return array("success" => false, "code" => -89, "message" => _x("Unable to create Amazon CloudFront Streaming Distro. Connection failed.", "s2member-admin", "s2member"));
																									}
																								else if(isset($cf_response["code"], $cf_response["message"]))
																									/* translators: In this translation, `%s` may be filled with an English message, which comes from the Amazon CloudFront API call. Feel free to exclude `%s` if you like. */
																									return array("success" => false, "code" => $cf_response["code"], "message" => sprintf(_x("Unable to create Amazon CloudFront Downloads Distro. %s", "s2member-admin", "s2member"), $cf_response["message"]));

																								else // Else, we use a default error code and message.
																									return array("success" => false, "code" => -90, "message" => _x("Unable to create Amazon CloudFront Downloads Distro. Connection failed.", "s2member-admin", "s2member"));
																							}
																						else if(isset($cf_response["code"], $cf_response["message"]))
																							/* translators: In this translation, `%s` may be filled with an English message, which comes from the Amazon CloudFront API call. Feel free to exclude `%s` if you like. */
																							return array("success" => false, "code" => $cf_response["code"], "message" => sprintf(_x("Unable to create Amazon CloudFront Origin Access Identity. %s", "s2member-admin", "s2member"), $cf_response["message"]));

																						else // Else, we use a default error code and message.
																							return array("success" => false, "code" => -91, "message" => _x("Unable to create Amazon CloudFront Origin Access Identity. Connection failed.", "s2member-admin", "s2member"));
																					}
																				else // Else, we use a default error code and message.
																					return array("success" => false, "code" => -92, "message" => _x("Unable to clear existing Amazon CloudFront Origin Access Identity.", "s2member-admin", "s2member"));
																			}
																		else if(isset($cf_get_response["code"], $cf_get_response["message"]))
																			/* translators: In this translation, `%s` may be filled with an English message, which comes from the Amazon CloudFront API call. Feel free to exclude `%s` if you like. */
																			return array("success" => false, "code" => $cf_get_response["code"], "message" => sprintf(_x("Unable to acquire existing Amazon CloudFront Origin Access Identity. %s", "s2member-admin", "s2member"), $cf_get_response["message"]));

																		else // Else, we use a default error code and message.
																			return array("success" => false, "code" => -93, "message" => _x("Unable to acquire existing Amazon CloudFront Origin Access Identity. Connection failed.", "s2member-admin", "s2member"));
																	}
																else // Else, we use a default error code and message.
																	return array("success" => false, "code" => -94, "message" => _x("Unable to clear existing Amazon CloudFront Streaming Distro.", "s2member-admin", "s2member"));
															}
														else if(isset($cf_get_response["code"], $cf_get_response["message"]))
															/* translators: In this translation, `%s` may be filled with an English message, which comes from the Amazon CloudFront API call. Feel free to exclude `%s` if you like. */
															return array("success" => false, "code" => $cf_get_response["code"], "message" => sprintf(_x("Unable to acquire existing Amazon CloudFront Streaming Distro. %s", "s2member-admin", "s2member"), $cf_get_response["message"]));

														else // Else, we use a default error code and message.
															return array("success" => false, "code" => -95, "message" => _x("Unable to acquire existing Amazon CloudFront Streaming Distro. Connection failed.", "s2member-admin", "s2member"));
													}
												else // Else, we use a default error code and message.
													return array("success" => false, "code" => -96, "message" => _x("Unable to clear existing Amazon CloudFront Downloads Distro.", "s2member-admin", "s2member"));
											}
										else if(isset($cf_get_response["code"], $cf_get_response["message"]))
											/* translators: In this translation, `%s` may be filled with an English message, which comes from the Amazon CloudFront API call. Feel free to exclude `%s` if you like. */
											return array("success" => false, "code" => $cf_get_response["code"], "message" => sprintf(_x("Unable to acquire existing Amazon CloudFront Downloads Distro. %s", "s2member-admin", "s2member"), $cf_get_response["message"]));

										else // Else, we use a default error code and message.
											return array("success" => false, "code" => -97, "message" => _x("Unable to acquire existing Amazon CloudFront Downloads Distro. Connection failed.", "s2member-admin", "s2member"));
									}
								else // Else, we use a default error code and message.
									return array("success" => false, "code" => -98, "message" => _x("Unable to auto-configure Amazon CloudFront Distros. Incomplete Amazon CloudFront configuration options. Missing of one: Amazon CloudFront Private Key-Pair-ID, or Private Key file contents.", "s2member-admin", "s2member"));
							}
						else // Else, we use a default error code and message.
							return array("success" => false, "code" => -99, "message" => _x("Unable to auto-configure Amazon S3/CloudFront Distros. Incomplete Amazon S3 configuration options. Missing one of: Amazon S3 Bucket, Access Key, or Secret Key. You must provide s2Member with an Amazon S3 configuration before enabling CloudFront.", "s2member-admin", "s2member"));
					}
				/**
				* Acquires an Amazon S3/CloudFront Access Origin Identity.
				*
				* @package s2Member\Files
				* @since 110926
				*
				* @param string $access_id Required. An Origin Access ID.
				* @return array Array containing a true `success` and `etag`, `xml` elements on success, else a failure array.
				* 	Failure array will contain a failure `code`, and a failure `message`.
				*/
				public static function amazon_cf_get_access_origin_identity($access_id = FALSE)
					{
						if /* Valid parameters? */($access_id && is_string($access_id))
							{
								foreach($GLOBALS["WS_PLUGIN__"]["s2member"]["o"] as $option => $option_value)
									if(preg_match("/^amazon_cf_files_/", $option) && ($option = preg_replace("/^amazon_cf_files_/", "", $option)))
										$cfc[$option] = $option_value;

								$s3c["bucket"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_s3_files_bucket"];
								$cfc["access_key"] = $s3c["access_key"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_s3_files_access_key"];
								$cfc["secret_key"] = $s3c["secret_key"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_s3_files_secret_key"];

								$cf_domain = "cloudfront.amazonaws.com";
								$cf_date = gmdate("D, d M Y H:i:s")." GMT";
								$cf_location = "/2010-11-01/origin-access-identity/cloudfront/".$access_id;
								$cf_signature = base64_encode(c_ws_plugin__s2member_files_in::amazon_cf_sign($cf_date));
								$cf_args = array("method" => "GET", "redirection" => 5, "headers" => array("Host" => $cf_domain, "Date" => $cf_date, "Authorization" => "AWS ".$cfc["access_key"].":".$cf_signature));

								if(($cf_response = c_ws_plugin__s2member_utils_urls::remote("https://".$cf_domain.$cf_location, false, array_merge($cf_args, array("timeout" => 20)), "array")) && (($cf_response["code"] === 404 && $cf_response["message"]) || ($cf_response["code"] === 200 && !empty($cf_response["headers"]["etag"]) && !empty($cf_response["body"]))))
									{
										if($cf_response["code"] === 200 && !empty($cf_response["headers"]["etag"]) && !empty($cf_response["body"]))
											return array("success" => true, "code" => null, "message" => null, "etag" => trim($cf_response["headers"]["etag"]), "xml" => trim($cf_response["body"]));

										else /* translators: In this translation, `%s` may be filled with an English message, which comes from the Amazon CloudFront API call. Feel free to exclude `%s` if you like. */
											return array("success" => false, "code" => $cf_response["code"], "message" => sprintf(_x("Existing Amazon CloudFront Origin Access Identity NOT found. %s", "s2member-admin", "s2member"), $cf_response["message"]));
									}
								else if(isset($cf_response["code"], $cf_response["message"]))
									/* translators: In this translation, `%s` may be filled with an English message, which comes from the Amazon CloudFront API call. Feel free to exclude `%s` if you like. */
									return array("success" => false, "code" => $cf_response["code"], "message" => sprintf(_x("Unable to acquire existing Amazon CloudFront Origin Access Identity. %s", "s2member-admin", "s2member"), $cf_response["message"]));

								else // Else, we use a default error code and message.
									return array("success" => false, "code" => -98, "message" => _x("Unable to acquire existing Amazon CloudFront Origin Access Identity. Connection failed.", "s2member-admin", "s2member"));
							}
						else // Else, we use a default error code and message.
							return array("success" => false, "code" => -99, "message" => _x("Unable to acquire existing Amazon CloudFront Origin Access Identity. Invalid Access ID.", "s2member-admin", "s2member"));
					}
				/**
				* Deletes an Amazon S3/CloudFront Access Origin Identity.
				*
				* @package s2Member\Files
				* @since 110926
				*
				* @param string $access_id Required. An Origin Access ID.
				* @param string $access_id_etag Required. An Origin Access ETag header.
				* @param string $access_id_xml Required. An Origin Access Identity's XML configuration.
				* @return array Array containing a true `success` element on success, else a failure array.
				* 	Failure array will contain a failure `code`, and a failure `message`.
				*/
				public static function amazon_cf_del_access_origin_identity($access_id = FALSE, $access_id_etag = FALSE, $access_id_xml = FALSE)
					{
						if($access_id && is_string($access_id) && $access_id_etag && is_string($access_id_etag) && $access_id_xml && is_string($access_id_xml))
							{
								foreach($GLOBALS["WS_PLUGIN__"]["s2member"]["o"] as $option => $option_value)
									if(preg_match("/^amazon_cf_files_/", $option) && ($option = preg_replace("/^amazon_cf_files_/", "", $option)))
										$cfc[$option] = $option_value;

								$s3c["bucket"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_s3_files_bucket"];
								$cfc["access_key"] = $s3c["access_key"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_s3_files_access_key"];
								$cfc["secret_key"] = $s3c["secret_key"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_s3_files_secret_key"];

								$cf_domain = "cloudfront.amazonaws.com";
								$cf_date = gmdate("D, d M Y H:i:s")." GMT";
								$cf_location = "/2010-11-01/origin-access-identity/cloudfront/".$access_id;
								$cf_signature = base64_encode(c_ws_plugin__s2member_files_in::amazon_cf_sign($cf_date));
								$cf_args = array("method" => "DELETE", "redirection" => 5, "headers" => array("Host" => $cf_domain, "Date" => $cf_date, "If-Match" => $access_id_etag, "Authorization" => "AWS ".$cfc["access_key"].":".$cf_signature));

								if(($cf_response = c_ws_plugin__s2member_utils_urls::remote("https://".$cf_domain.$cf_location, false, array_merge($cf_args, array("timeout" => 20)), "array")) && ($cf_response["code"] === 200 || $cf_response["code"] === 204 /* Deleted. */))
									return /* Deleted successfully. */ array("success" => true, "code" => null, "message" => null);

								else if(isset($cf_response["code"], $cf_response["message"]))
									/* translators: In this translation, `%s` may be filled with an English message, which comes from the Amazon CloudFront API call. Feel free to exclude `%s` if you like. */
									return array("success" => false, "code" => $cf_response["code"], "message" => sprintf(_x("Unable to delete existing Amazon CloudFront Origin Access Identity. %s", "s2member-admin", "s2member"), $cf_response["message"]));

								else // Else, we use a default error code and message.
									return array("success" => false, "code" => -98, "message" => _x("Unable to delete existing Amazon CloudFront Origin Access Identity. Connection failed.", "s2member-admin", "s2member"));
							}
						else // Else, we use a default error code and message.
							return array("success" => false, "code" => -99, "message" => _x("Unable to delete existing Amazon CloudFront Origin Access Identity. Invalid Access ID, ETag, or XML config.", "s2member-admin", "s2member"));
					}
				/**
				* Creates an Amazon S3/CloudFront Access Origin Identity for all Distros.
				*
				* @package s2Member\Files
				* @since 110926
				*
				* @return array Array containing a true `success` and `distros_access_id`, `distros_s3_access_id` elements on success, else a failure array.
				* 	Failure array will contain a failure `code`, and a failure `message`.
				*/
				public static function amazon_cf_create_distros_access_origin_identity()
					{
						foreach($GLOBALS["WS_PLUGIN__"]["s2member"]["o"] as $option => $option_value)
							if(preg_match("/^amazon_cf_files_/", $option) && ($option = preg_replace("/^amazon_cf_files_/", "", $option)))
								$cfc[$option] = $option_value;

						$s3c["bucket"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_s3_files_bucket"];
						$cfc["access_key"] = $s3c["access_key"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_s3_files_access_key"];
						$cfc["secret_key"] = $s3c["secret_key"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_s3_files_secret_key"];

						$cf_domain = "cloudfront.amazonaws.com";
						$cf_date = gmdate("D, d M Y H:i:s")." GMT";
						$cf_location = "/2010-11-01/origin-access-identity/cloudfront";
						$cf_signature = base64_encode(c_ws_plugin__s2member_files_in::amazon_cf_sign($cf_date));
						$cf_distros_access_reference = time().".".md5("access".$s3c["bucket"].$s3c["access_key"].$s3c["secret_key"].$cfc["private_key"].$cfc["private_key_id"]);
						$cf_distros_access_xml = '<?xml version="1.0" encoding="UTF-8"?><CloudFrontOriginAccessIdentityConfig xmlns="http://cloudfront.amazonaws.com/doc/2010-11-01/"><CallerReference>'.esc_html($cf_distros_access_reference).'</CallerReference><Comment>'.esc_html(sprintf(_x("Created by s2Member, for S3 Bucket: %s.", "s2member-admin", "s2member"), $s3c["bucket"])).'</Comment></CloudFrontOriginAccessIdentityConfig>';
						$cf_args = array("method" => "POST", "redirection" => 5, "body" => $cf_distros_access_xml, "headers" => array("Host" => $cf_domain, "Content-Type" => "application/xml", "Date" => $cf_date, "Authorization" => "AWS ".$cfc["access_key"].":".$cf_signature));

						if(($cf_response = c_ws_plugin__s2member_utils_urls::remote("https://".$cf_domain.$cf_location, false, array_merge($cf_args, array("timeout" => 20)), "array")) && ($cf_response["code"] === 200 || $cf_response["code"] === 201 /* Created. */))
							{
								if(preg_match("/\<CloudFrontOriginAccessIdentity.*?\>(.+?)\<\/CloudFrontOriginAccessIdentity\>/is", $cf_response["body"], $cf_distros_access_tag) && preg_match("/\<Id\>(.+?)\<\/Id\>/is", $cf_distros_access_tag[1], $cf_distros_access_id_tag) && preg_match("/\<S3CanonicalUserId\>(.+?)\<\/S3CanonicalUserId\>/is", $cf_distros_access_tag[1], $cf_distros_s3_access_id_tag))
									return array("success" => true, "code" => null, "message" => null, "distros_access_id" => trim($cf_distros_access_id_tag[1]), "distros_s3_access_id" => trim($cf_distros_s3_access_id_tag[1]));

								else // Else, we use a default error code and message.
									return array("success" => false, "code" => -98, "message" => _x("Unable to create/read Amazon CloudFront Origin Access Identity. Unexpected response.", "s2member-admin", "s2member"));
							}
						else if(isset($cf_response["code"], $cf_response["message"]))
							/* translators: In this translation, `%s` may be filled with an English message, which comes from the Amazon CloudFront API call. Feel free to exclude `%s` if you like. */
							return array("success" => false, "code" => $cf_response["code"], "message" => sprintf(_x("Unable to create Amazon CloudFront Origin Access Identity. %s", "s2member-admin", "s2member"), $cf_response["message"]));

						else // Else, we use a default error code and message.
							return array("success" => false, "code" => -99, "message" => _x("Unable to create Amazon CloudFront Origin Access Identity. Connection failed.", "s2member-admin", "s2member"));
					}
				/**
				* Acquires an Amazon S3/CloudFront Distro.
				*
				* @package s2Member\Files
				* @since 110926
				*
				* @param string $distro_id Required. A Distro ID.
				* @param string $distro_type Required: `downloads|streaming`.
				* @return array Array containing a true `success` and `etag`, `xml`, `deployed` elements on success, else a failure array.
				* 	Failure array will contain a failure `code`, and a failure `message`.
				*/
				public static function amazon_cf_get_distro($distro_id = FALSE, $distro_type = FALSE)
					{
						if($distro_id && is_string($distro_id) && $distro_type && is_string($distro_type) && in_array($distro_type, array("downloads", "streaming")))
							{
								foreach($GLOBALS["WS_PLUGIN__"]["s2member"]["o"] as $option => $option_value)
									if(preg_match("/^amazon_cf_files_/", $option) && ($option = preg_replace("/^amazon_cf_files_/", "", $option)))
										$cfc[$option] = $option_value;

								$s3c["bucket"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_s3_files_bucket"];
								$cfc["access_key"] = $s3c["access_key"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_s3_files_access_key"];
								$cfc["secret_key"] = $s3c["secret_key"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_s3_files_secret_key"];

								$cf_domain = "cloudfront.amazonaws.com";
								$cf_date = gmdate("D, d M Y H:i:s")." GMT";
								$cf_signature = base64_encode(c_ws_plugin__s2member_files_in::amazon_cf_sign($cf_date));
								$cf_location = ($distro_type === "streaming") ? "/2010-11-01/streaming-distribution/".$distro_id : "/2010-11-01/distribution/".$distro_id;
								$cf_args = array("method" => "GET", "redirection" => 5, "headers" => array("Host" => $cf_domain, "Date" => $cf_date, "Authorization" => "AWS ".$cfc["access_key"].":".$cf_signature));

								if(($cf_response = c_ws_plugin__s2member_utils_urls::remote("https://".$cf_domain.$cf_location, false, array_merge($cf_args, array("timeout" => 20)), "array")) && (($cf_response["code"] === 404 && $cf_response["message"]) || ($cf_response["code"] === 200 && !empty($cf_response["headers"]["etag"]) && !empty($cf_response["body"]))))
									{
										if($cf_response["code"] === 200 && !empty($cf_response["headers"]["etag"]) && !empty($cf_response["body"]))
											return array("success" => true, "code" => null, "message" => null, "etag" => trim($cf_response["headers"]["etag"]), "xml" => trim($cf_response["body"]), "deployed" => ((stripos($cf_response["body"], "<Status>Deployed</Status>") !== false) ? true : false));

										else /* translators: In this translation, `%s` may be filled with an English message, which comes from the Amazon CloudFront API call. Feel free to exclude `%s` if you like. */
											return array("success" => false, "code" => $cf_response["code"], "message" => sprintf(_x("Existing Amazon CloudFront Distro NOT found. %s", "s2member-admin", "s2member"), $cf_response["message"]));
									}
								else if(isset($cf_response["code"], $cf_response["message"]))
									/* translators: In this translation, `%s` may be filled with an English message, which comes from the Amazon CloudFront API call. Feel free to exclude `%s` if you like. */
									return array("success" => false, "code" => $cf_response["code"], "message" => sprintf(_x("Unable to acquire existing Amazon CloudFront Distro. %s", "s2member-admin", "s2member"), $cf_response["message"]));

								else // Else, we use a default error code and message.
									return array("success" => false, "code" => -98, "message" => _x("Unable to acquire existing Amazon CloudFront Distro. Connection failed.", "s2member-admin", "s2member"));
							}
						else // Else, we use a default error code and message.
							return array("success" => false, "code" => -99, "message" => _x("Unable to acquire existing Amazon CloudFront Distro. Invalid Distro ID and/or Distro type.", "s2member-admin", "s2member"));
					}
				/**
				* Disables an Amazon S3/CloudFront Distro.
				*
				* @package s2Member\Files
				* @since 110926
				*
				* @param string $distro_id Required. A Distro ID.
				* @param string $distro_id_etag Required. A Distro ETag header.
				* @param string $distro_id_xml Required. A Distro's XML configuration.
				* @return array Array containing a true `success` and `etag`, `xml`, `deployed` elements on success, else a failure array.
				* 	Failure array will contain a failure `code`, and a failure `message`.
				*/
				public static function amazon_cf_disable_distro($distro_id = FALSE, $distro_id_etag = FALSE, $distro_id_xml = FALSE)
					{
						if($distro_id && is_string($distro_id) && $distro_id_etag && is_string($distro_id_etag) && $distro_id_xml && is_string($distro_id_xml) && ($distro_id_type = (stripos($distro_id_xml, "<StreamingDistribution") !== false) ? "streaming" : ((stripos($distro_id_xml, "<Distribution") !== false) ? "downloads" : false)) && preg_match("/\<CallerReference\>(.+?)\<\/CallerReference\>/is", $distro_id_xml, $distro_id_reference_tag) && ($distro_id_reference = $distro_id_reference_tag[1]))
							{
								if /* Only if it has NOT already been disabled. We do NOT need to do it again. */(stripos($distro_id_xml, "<Enabled>false</Enabled>") === false)
									{
										if /* Check distro status before we even begin processing. */(stripos($distro_id_xml, "<Status>Deployed</Status>") !== false)
											{
												foreach($GLOBALS["WS_PLUGIN__"]["s2member"]["o"] as $option => $option_value)
													if(preg_match("/^amazon_cf_files_/", $option) && ($option = preg_replace("/^amazon_cf_files_/", "", $option)))
														$cfc[$option] = $option_value;

												$s3c["bucket"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_s3_files_bucket"];
												$cfc["access_key"] = $s3c["access_key"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_s3_files_access_key"];
												$cfc["secret_key"] = $s3c["secret_key"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_s3_files_secret_key"];

												$cf_domain = "cloudfront.amazonaws.com";
												$cf_date = gmdate("D, d M Y H:i:s")." GMT";
												$cf_signature = base64_encode(c_ws_plugin__s2member_files_in::amazon_cf_sign($cf_date));
												$cf_location = ($distro_id_type === "streaming") ? "/2010-11-01/streaming-distribution/".$distro_id."/config" : "/2010-11-01/distribution/".$distro_id."/config";
												$cf_distro_xml = ($distro_id_type === "streaming") ? '<?xml version="1.0" encoding="UTF-8"?><StreamingDistributionConfig xmlns="http://cloudfront.amazonaws.com/doc/2010-11-01/"><S3Origin><DNSName>'.esc_html($s3c["bucket"]).'.s3.amazonaws.com</DNSName></S3Origin><CallerReference>'.esc_html($distro_id_reference).'</CallerReference><Enabled>false</Enabled><TrustedSigners><Self/></TrustedSigners></StreamingDistributionConfig>' : '<?xml version="1.0" encoding="UTF-8"?><DistributionConfig xmlns="http://cloudfront.amazonaws.com/doc/2010-11-01/"><S3Origin><DNSName>'.esc_html($s3c["bucket"]).'.s3.amazonaws.com</DNSName></S3Origin><CallerReference>'.esc_html($distro_id_reference).'</CallerReference><Enabled>false</Enabled><TrustedSigners><Self/></TrustedSigners></DistributionConfig>';
												$cf_args = array("method" => "PUT", "redirection" => 5, "body" => $cf_distro_xml, "headers" => array("Host" => $cf_domain, "Content-Type" => "application/xml", "Date" => $cf_date, "If-Match" => $distro_id_etag, "Authorization" => "AWS ".$cfc["access_key"].":".$cf_signature));

												if(($cf_response = c_ws_plugin__s2member_utils_urls::remote("https://".$cf_domain.$cf_location, false, array_merge($cf_args, array("timeout" => 20)), "array")) && $cf_response["code"] === 200 && !empty($cf_response["headers"]["etag"]) && !empty($cf_response["body"]))
													return array("success" => true, "code" => null, "message" => null, "etag" => trim($cf_response["headers"]["etag"]), "xml" => trim($cf_response["body"]), "deployed" => ((stripos($cf_response["body"], "<Status>Deployed</Status>") !== false) ? true : false));

												else if(isset($cf_response["code"], $cf_response["message"]))
													/* translators: In this translation, `%s` may be filled with an English message, which comes from the Amazon CloudFront API call. Feel free to exclude `%s` if you like. */
													return array("success" => false, "code" => $cf_response["code"], "message" => sprintf(_x("Unable to disable existing Amazon CloudFront Distro. %s", "s2member-admin", "s2member"), $cf_response["message"]));

												else // Else, we use a default error code and message.
													return array("success" => false, "code" => -97, "message" => _x("Unable to disable existing Amazon CloudFront Distro. Connection failed.", "s2member-admin", "s2member"));
											}
										else // Else, we use a default error code and message.
											return array("success" => false, "code" => -98, "message" => _x("Existing Amazon CloudFront Distro cannot be disabled at this time. Still in a `pending` state. Please wait 15 minutes, then try again. There is a certain process that s2Member must strictly adhere to when re-configuring your Amazon CloudFront Distros. You may have to tick the auto-configure checkbox again, and re-run s2Member's auto-configuration routine many times, because s2Member will likely run into several `pending` challenges, as it works to completely re-configure your Amazon CloudFront Distros for you. Thanks for your patience. Please wait 15 minutes, then try again.", "s2member-admin", "s2member"));
									}
								else // Else, we use a default error code and message.
									return array("success" => true, "code" => null, "message" => null, "etag" => $distro_id_etag, "xml" => $distro_id_xml, "deployed" => ((stripos($distro_id_xml, "<Status>Deployed</Status>") !== false) ? true : false));
							}
						else // Else, we use a default error code and message.
							return array("success" => false, "code" => -99, "message" => _x("Unable to disable existing Amazon CloudFront Distro. Invalid Distro ID, ETag, or XML config.", "s2member-admin", "s2member"));
					}
				/**
				* Deletes an Amazon S3/CloudFront Distro.
				*
				* @package s2Member\Files
				* @since 110926
				*
				* @param string $distro_id Required. A Distro ID.
				* @param string $distro_id_etag Required. A Distro ETag header.
				* @param string $distro_id_xml Required. A Distro's XML configuration.
				* @return array Array containing a true `success` element on success, else a failure array.
				* 	Failure array will contain a failure `code`, and a failure `message`.
				*/
				public static function amazon_cf_del_distro($distro_id = FALSE, $distro_id_etag = FALSE, $distro_id_xml = FALSE)
					{
						if($distro_id && is_string($distro_id) && $distro_id_etag && is_string($distro_id_etag) && $distro_id_xml && is_string($distro_id_xml) && ($distro_id_type = (stripos($distro_id_xml, "<StreamingDistribution") !== false) ? "streaming" : ((stripos($distro_id_xml, "<Distribution") !== false) ? "downloads" : false)) && preg_match("/\<CallerReference\>(.+?)\<\/CallerReference\>/is", $distro_id_xml, $distro_id_reference_tag) && ($distro_id_reference = $distro_id_reference_tag[1]))
							{
								if /* Check distro status before we even begin processing this deletion. */(stripos($distro_id_xml, "<Status>Deployed</Status>") !== false)
									{
										if(($cf_response = c_ws_plugin__s2member_files_in::amazon_cf_disable_distro($distro_id, $distro_id_etag, $distro_id_xml)) && $cf_response["success"])
											{
												if(($cf_response = c_ws_plugin__s2member_files_in::amazon_cf_get_distro($distro_id, $distro_id_type)) && $cf_response["success"] && $cf_response["deployed"])
													{
														foreach($GLOBALS["WS_PLUGIN__"]["s2member"]["o"] as $option => $option_value)
															if(preg_match("/^amazon_cf_files_/", $option) && ($option = preg_replace("/^amazon_cf_files_/", "", $option)))
																$cfc[$option] = $option_value;

														$s3c["bucket"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_s3_files_bucket"];
														$cfc["access_key"] = $s3c["access_key"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_s3_files_access_key"];
														$cfc["secret_key"] = $s3c["secret_key"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_s3_files_secret_key"];

														$cf_domain = "cloudfront.amazonaws.com";
														$cf_date = gmdate("D, d M Y H:i:s")." GMT";
														$cf_signature = base64_encode(c_ws_plugin__s2member_files_in::amazon_cf_sign($cf_date));
														$cf_location = ($distro_id_type === "streaming") ? "/2010-11-01/streaming-distribution/".$distro_id : "/2010-11-01/distribution/".$distro_id;
														$cf_args = array("method" => "DELETE", "redirection" => 5, "headers" => array("Host" => $cf_domain, "Date" => $cf_date, "If-Match" => $cf_response["etag"], "Authorization" => "AWS ".$cfc["access_key"].":".$cf_signature));

														if(($cf_response = c_ws_plugin__s2member_utils_urls::remote("https://".$cf_domain.$cf_location, false, array_merge($cf_args, array("timeout" => 20)), "array")) && ($cf_response["code"] === 200 || $cf_response["code"] === 204 /* Deleted. */))
															return /* Deleted successfully. */ array("success" => true, "code" => null, "message" => null);

														else if(isset($cf_response["code"], $cf_response["message"]))
															/* translators: In this translation, `%s` may be filled with an English message, which comes from the Amazon CloudFront API call. Feel free to exclude `%s` if you like. */
															return array("success" => false, "code" => $cf_response["code"], "message" => sprintf(_x("Unable to delete existing Amazon CloudFront Distro. %s", "s2member-admin", "s2member"), $cf_response["message"]));

														else // Else, we use a default error code and message.
															return array("success" => false, "code" => -94, "message" => _x("Unable to delete existing Amazon CloudFront Distro. Connection failed.", "s2member-admin", "s2member"));
													}
												else if(isset($cf_response["success"], $cf_response["deployed"]) && $cf_response["success"] && !$cf_response["deployed"])
													/* translators: In this translation, `%s` may be filled with an English message, which comes from the Amazon CloudFront API call. Feel free to exclude `%s` if you like. */
													return array("success" => false, "code" => -95, "message" => _x("Existing Amazon CloudFront Distro cannot be deleted at this time. Still in a `pending` state after having been disabled by s2Member. Please wait 15 minutes, then try again. There is a certain process that s2Member must strictly adhere to when re-configuring your Amazon CloudFront Distros. You may have to tick the auto-configure checkbox again, and re-run s2Member's auto-configuration routine many times, because s2Member will likely run into several `pending` challenges, as it works to completely re-configure your Amazon CloudFront Distros for you. Thanks for your patience. Please wait 15 minutes, then try again.", "s2member-admin", "s2member"));

												else if(isset($cf_response["code"], $cf_response["message"]))
													/* translators: In this translation, `%s` may be filled with an English message, which comes from the Amazon CloudFront API call. Feel free to exclude `%s` if you like. */
													return array("success" => false, "code" => $cf_response["code"], "message" => sprintf(_x("Unable to check status of existing Amazon CloudFront Distro. %s", "s2member-admin", "s2member"), $cf_response["message"]));

												else // Else, we use a default error code and message.
													return array("success" => false, "code" => -96, "message" => _x("Unable to check status of existing Amazon CloudFront Distro. Connection failed.", "s2member-admin", "s2member"));
											}
										else if(isset($cf_response["code"], $cf_response["message"]))
											/* translators: In this translation, `%s` may be filled with an English message, which comes from the Amazon CloudFront API call. Feel free to exclude `%s` if you like. */
											return array("success" => false, "code" => $cf_response["code"], "message" => sprintf(_x("Unable to disable existing Amazon CloudFront Distro. %s", "s2member-admin", "s2member"), $cf_response["message"]));

										else // Else, we use a default error code and message.
											return array("success" => false, "code" => -97, "message" => _x("Unable to disable existing Amazon CloudFront Distro. Connection failed.", "s2member-admin", "s2member"));
									}
								else // Else, we use a default error code and message.
									return array("success" => false, "code" => -98, "message" => _x("Existing Amazon CloudFront Distro cannot be deleted at this time. Still in a `pending` state. Please wait 15 minutes, then try again. There is a certain process that s2Member must strictly adhere to when re-configuring your Amazon CloudFront Distros. You may have to tick the auto-configure checkbox again, and re-run s2Member's auto-configuration routine many times, because s2Member will likely run into several `pending` challenges, as it works to completely re-configure your Amazon CloudFront Distros for you. Thanks for your patience. Please wait 15 minutes, then try again.", "s2member-admin", "s2member"));
							}
						else // Else, we use a default error code and message.
							return array("success" => false, "code" => -99, "message" => _x("Unable to delete existing Amazon CloudFront Distro. Invalid Distro ID or ETag.", "s2member-admin", "s2member"));
					}
				/**
				* Creates an Amazon S3/CloudFront Distro.
				*
				* @package s2Member\Files
				* @since 110926
				*
				* @param string $distro_type Required: `downloads|streaming`.
				* @return array Array containing a true `success` and `distro_[distro_type]_id`, `distro_[distro_type]_dname` elements on success, else a failure array.
				* 	Failure array will contain a failure `code`, and a failure `message`.
				*/
				public static function amazon_cf_create_distro($distro_type = FALSE)
					{
						if($distro_type && is_string($distro_type) && in_array($distro_type, array("downloads", "streaming")))
							{
								foreach($GLOBALS["WS_PLUGIN__"]["s2member"]["o"] as $option => $option_value)
									if(preg_match("/^amazon_cf_files_/", $option) && ($option = preg_replace("/^amazon_cf_files_/", "", $option)))
										$cfc[$option] = $option_value;

								$s3c["bucket"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_s3_files_bucket"];
								$cfc["access_key"] = $s3c["access_key"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_s3_files_access_key"];
								$cfc["secret_key"] = $s3c["secret_key"] = $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["amazon_s3_files_secret_key"];

								$cf_domain = "cloudfront.amazonaws.com";
								$cf_date = gmdate("D, d M Y H:i:s")." GMT";
								$cf_signature = base64_encode(c_ws_plugin__s2member_files_in::amazon_cf_sign($cf_date));

								if /* Create a `downloads` Distro? This uses a different XML schema. */($distro_type === "downloads")
									{
										$cf_location = /* Create distro. */ "/2010-11-01/distribution";
										$cf_distro_downloads_reference = time().".".md5("downloads".$s3c["bucket"].$s3c["access_key"].$s3c["secret_key"].$cfc["private_key"].$cfc["private_key_id"].$cfc["distro_downloads_cname"]);
										$cf_distro_downloads_xml = '<?xml version="1.0" encoding="UTF-8"?><DistributionConfig xmlns="http://cloudfront.amazonaws.com/doc/2010-11-01/"><S3Origin><DNSName>'.esc_html($s3c["bucket"]).'.s3.amazonaws.com</DNSName><OriginAccessIdentity>origin-access-identity/cloudfront/'.esc_html($cfc["distros_access_id"]).'</OriginAccessIdentity></S3Origin><CallerReference>'.esc_html($cf_distro_downloads_reference).'</CallerReference>'.(($cfc["distro_downloads_cname"]) ? '<CNAME>'.esc_html($cfc["distro_downloads_cname"]).'</CNAME>' : '').'<Comment>'.esc_html(sprintf(_x("Created by s2Member, for S3 Bucket: %s.", "s2member-admin", "s2member"), $s3c["bucket"])).'</Comment><Enabled>true</Enabled><DefaultRootObject>index.html</DefaultRootObject><TrustedSigners><Self/></TrustedSigners></DistributionConfig>';
										$cf_args = array("method" => "POST", "redirection" => 5, "body" => $cf_distro_downloads_xml, "headers" => array("Host" => $cf_domain, "Content-Type" => "application/xml", "Date" => $cf_date, "Authorization" => "AWS ".$cfc["access_key"].":".$cf_signature));

										if(($cf_response = c_ws_plugin__s2member_utils_urls::remote("https://".$cf_domain.$cf_location, false, array_merge($cf_args, array("timeout" => 20)), "array")) && ($cf_response["code"] === 200 || $cf_response["code"] === 201 /* Created. */))
											{
												if(preg_match("/\<Distribution.*?\>(.+?)\<\/Distribution\>/is", $cf_response["body"], $cf_distro_downloads_tag) && preg_match("/\<Id\>(.+?)\<\/Id\>/is", $cf_distro_downloads_tag[1], $cf_distro_downloads_id_tag) && preg_match("/\<DomainName\>(.+?)\<\/DomainName\>/is", $cf_distro_downloads_tag[1], $cf_distro_downloads_dname_tag))
													return array("success" => true, "code" => null, "message" => null, "distro_downloads_id" => trim($cf_distro_downloads_id_tag[1]), "distro_downloads_dname" => trim($cf_distro_downloads_dname_tag[1]));

												else // Else, we use a default error code and message.
													return array("success" => false, "code" => -97, "message" => _x("Unable to create/read Amazon CloudFront Downloads Distro. Unexpected response.", "s2member-admin", "s2member"));
											}
										else if(isset($cf_response["code"], $cf_response["message"]))
											/* translators: In this translation, `%s` may be filled with an English message, which comes from the Amazon CloudFront API call. Feel free to exclude `%s` if you like. */
											return array("success" => false, "code" => $cf_response["code"], "message" => sprintf(_x("Unable to create Amazon CloudFront Downloads Distro. %s", "s2member-admin", "s2member"), $cf_response["message"]));

										else // Else, we use a default error code and message.
											return array("success" => false, "code" => -98, "message" => _x("Unable to create Amazon CloudFront Downloads Distro. Connection failed.", "s2member-admin", "s2member"));
									}

								else if /* Create a `streaming` Distro? A different XML schema. */($distro_type === "streaming")
									{
										$cf_location = /* Create streaming distro. */ "/2010-11-01/streaming-distribution";
										$cf_distro_streaming_reference = time().".".md5("streaming".$s3c["bucket"].$s3c["access_key"].$s3c["secret_key"].$cfc["private_key"].$cfc["private_key_id"].$cfc["distro_streaming_cname"]);
										$cf_distro_streaming_xml = '<?xml version="1.0" encoding="UTF-8"?><StreamingDistributionConfig xmlns="http://cloudfront.amazonaws.com/doc/2010-11-01/"><S3Origin><DNSName>'.esc_html($s3c["bucket"]).'.s3.amazonaws.com</DNSName><OriginAccessIdentity>origin-access-identity/cloudfront/'.esc_html($cfc["distros_access_id"]).'</OriginAccessIdentity></S3Origin><CallerReference>'.esc_html($cf_distro_streaming_reference).'</CallerReference>'.(($cfc["distro_streaming_cname"]) ? '<CNAME>'.esc_html($cfc["distro_streaming_cname"]).'</CNAME>' : '').'<Comment>'.esc_html(sprintf(_x("Created by s2Member, for S3 Bucket: %s.", "s2member-admin", "s2member"), $s3c["bucket"])).'</Comment><Enabled>true</Enabled><DefaultRootObject>index.html</DefaultRootObject><TrustedSigners><Self/></TrustedSigners></StreamingDistributionConfig>';
										$cf_args = array("method" => "POST", "redirection" => 5, "body" => $cf_distro_streaming_xml, "headers" => array("Host" => $cf_domain, "Content-Type" => "application/xml", "Date" => $cf_date, "Authorization" => "AWS ".$cfc["access_key"].":".$cf_signature));

										if(($cf_response = c_ws_plugin__s2member_utils_urls::remote("https://".$cf_domain.$cf_location, false, array_merge($cf_args, array("timeout" => 20)), "array")) && ($cf_response["code"] === 200 || $cf_response["code"] === 201 /* Created. */))
											{
												if(preg_match("/\<StreamingDistribution.*?\>(.+?)\<\/StreamingDistribution\>/is", $cf_response["body"], $cf_distro_streaming_tag) && preg_match("/\<Id\>(.+?)\<\/Id\>/is", $cf_distro_streaming_tag[1], $cf_distro_streaming_id_tag) && preg_match("/\<DomainName\>(.+?)\<\/DomainName\>/is", $cf_distro_streaming_tag[1], $cf_distro_streaming_dname_tag))
													return array("success" => true, "code" => null, "message" => null, "distro_streaming_id" => trim($cf_distro_streaming_id_tag[1]), "distro_streaming_dname" => trim($cf_distro_streaming_dname_tag[1]));

												else // Else, we use a default error code and message.
													return array("success" => false, "code" => -97, "message" => _x("Unable to create/read Amazon CloudFront Streaming Distro. Unexpected response.", "s2member-admin", "s2member"));
											}
										else if(isset($cf_response["code"], $cf_response["message"]))
											/* translators: In this translation, `%s` may be filled with an English message, which comes from the Amazon CloudFront API call. Feel free to exclude `%s` if you like. */
											return array("success" => false, "code" => $cf_response["code"], "message" => sprintf(_x("Unable to create Amazon CloudFront Streaming Distro. %s", "s2member-admin", "s2member"), $cf_response["message"]));

										else // Else, we use a default error code and message.
											return array("success" => false, "code" => -98, "message" => _x("Unable to create Amazon CloudFront Streaming Distro. Connection failed.", "s2member-admin", "s2member"));
									}
							}
						else // Else, we use a default error code and message.
							return array("success" => false, "code" => -99, "message" => _x("Unable to create Amazon CloudFront Distro. Invalid Distro type.", "s2member-admin", "s2member"));
					}
			}
	}
?>