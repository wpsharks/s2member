<?php
// @codingStandardsIgnoreFile
/**
* Directory utilities.
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
	exit ("Do not access this file directly.");

if (!class_exists ("c_ws_plugin__s2member_utils_dirs"))
	{
		/**
		* Directory utilities.
		*
		* @package s2Member\Utilities
		* @since 3.5
		*/
		class c_ws_plugin__s2member_utils_dirs
			{
				/**
				* Normalizes directory separators in dir/file paths.
				*
				* @package s2Member\Utilities
				* @since 111017
				*
				* @param string $path Directory or file path.
				* @return string Directory or file path, after having been normalized by this routine.
				*/
				public static function n_dir_seps ($path = FALSE)
					{
						return rtrim (preg_replace ("/\/+/", "/", str_replace (array(DIRECTORY_SEPARATOR, "\\", "/"), "/", (string)$path)), "/");
					}
				/**
				* Resolves and checks a custom template selected by a shortcode.
				*
				* @package s2Member\Utilities
				* @since 260812
				*
				* @param string $template Template path supplied by a shortcode.
				* @param array $legacy_filenames Exact legacy filenames allowed automatically in the content root or active theme roots.
				* @param string $shortcode Shortcode name for administrator notices.
				* @param int $post_id Post/Page ID where the template was detected, when available.
				* @param bool $enforce When true, return only approved templates. During the transition period this defaults to false.
				* @return string Canonical template path, or an empty string if invalid or blocked.
				*/
				public static function shortcode_template ($template = FALSE, $legacy_filenames = array(), $shortcode = '', $post_id = 0, $enforce = FALSE)
					{
						$template = c_ws_plugin__s2member_utils_dirs::n_dir_seps (trim (str_replace (chr (0), '', (string)$template)));
						if (!$template)
							return '';

						// Reject parent-directory segments before resolving the path.
						if (preg_match ('/(?:^|\/)\.\.(?:\/|$)/', $template))
							return '';

						// Preserve the historical lookup precedence and resolve the file to its actual filesystem path.
						$candidate = '';
						foreach (array(TEMPLATEPATH . '/' . $template, get_stylesheet_directory () . '/' . $template, WP_CONTENT_DIR . '/' . $template) as $_path)
							if (is_file ($_path) && ($_realpath = realpath ($_path)))
								$candidate = c_ws_plugin__s2member_utils_dirs::n_dir_seps ($_realpath);
						unset($_path, $_realpath);

						if (!$candidate || !($_content_dir = realpath (WP_CONTENT_DIR)))
							return '';
						$content_dir = c_ws_plugin__s2member_utils_dirs::n_dir_seps ($_content_dir);
						$is_windows = stripos (PHP_OS, 'WIN') === 0;
						$candidate_cmp = ($is_windows) ? strtolower ($candidate) : $candidate;
						$content_prefix = $content_dir . '/';
						$content_prefix_cmp = ($is_windows) ? strtolower ($content_prefix) : $content_prefix;

						// Require the resolved file to remain under WP_CONTENT_DIR, including after symlink resolution.
						if (strpos ($candidate_cmp, $content_prefix_cmp) !== 0)
							return '';

						// Keep shortcode templates out of WordPress uploads, where uploaded files can be user-controlled.
						$_uploads_dirs = array();
						$_uploads = wp_upload_dir();
						if (!empty($_uploads['basedir']) && ($_uploads_dir = realpath ($_uploads['basedir'])))
							{
								$_uploads_dir = c_ws_plugin__s2member_utils_dirs::n_dir_seps ($_uploads_dir);

								//260812 On Multisite, check the shared uploads root instead of only the current site's subdirectory.
								if (is_multisite ())
									{
										foreach (array('/sites/' . get_current_blog_id (), '/' . get_current_blog_id () . '/files', '/' . get_current_blog_id ()) as $_site_suffix)
											if (substr ($_uploads_dir, -strlen ($_site_suffix)) === $_site_suffix)
												{
													$_uploads_dir = substr ($_uploads_dir, 0, -strlen ($_site_suffix));
													break;
												}
									}
								$_uploads_dirs[] = $_uploads_dir;
							}

						//260812 Also check the standard and legacy Multisite uploads roots.
						foreach (array(WP_CONTENT_DIR . '/uploads', (is_multisite () && defined ('UPLOADBLOGSDIR')) ? ABSPATH . ltrim (UPLOADBLOGSDIR, '/\\') : '') as $_uploads_dir)
							if ($_uploads_dir && ($_realpath = realpath ($_uploads_dir)))
								$_uploads_dirs[] = c_ws_plugin__s2member_utils_dirs::n_dir_seps ($_realpath);

						foreach (array_unique ($_uploads_dirs) as $_uploads_dir)
							{
								$_uploads_dir_cmp = ($is_windows) ? strtolower ($_uploads_dir) : $_uploads_dir;
								if ($candidate_cmp === $_uploads_dir_cmp || strpos ($candidate_cmp, $_uploads_dir_cmp . '/') === 0)
									return '';
							}
						unset($_uploads_dirs, $_uploads, $_uploads_dir, $_uploads_dir_cmp, $_site_suffix, $_realpath);

						$allowed_paths = array();
						foreach ((array)$legacy_filenames as $_legacy_filename)
							{
								$_legacy_filename = ltrim (c_ws_plugin__s2member_utils_dirs::n_dir_seps (trim ((string)$_legacy_filename)), '/');
								if (!$_legacy_filename)
									continue;

								// Allow only the exact historical filename in the content root or active theme roots.
								foreach (array(TEMPLATEPATH . '/' . $_legacy_filename, get_stylesheet_directory () . '/' . $_legacy_filename, WP_CONTENT_DIR . '/' . $_legacy_filename) as $_path)
									if (is_file ($_path) && ($_realpath = realpath ($_path)))
										$allowed_paths[] = c_ws_plugin__s2member_utils_dirs::n_dir_seps ($_realpath);
							}
						unset($_legacy_filename, $_path, $_realpath);

						// Add exact custom template files from the whitelist and track whether the current file is listed.
						$whitelisted = FALSE;
						$_whitelist = apply_filters ('ws_plugin__s2member_shortcode_templates_whitelist', array());
						if (is_string ($_whitelist))
							$_whitelist = preg_split ("/[\\r\\n]+/", $_whitelist, -1, PREG_SPLIT_NO_EMPTY);
						foreach ((array)$_whitelist as $_allowed_file)
							{
								$_allowed_file = ltrim (c_ws_plugin__s2member_utils_dirs::n_dir_seps (trim ((string)$_allowed_file)), '/');
								if ($_allowed_file && is_file (WP_CONTENT_DIR . '/' . $_allowed_file) && ($_realpath = realpath (WP_CONTENT_DIR . '/' . $_allowed_file)))
									{
										$_realpath = c_ws_plugin__s2member_utils_dirs::n_dir_seps ($_realpath);
										$allowed_paths[] = $_realpath;
										if ((($is_windows) ? strtolower ($_realpath) : $_realpath) === $candidate_cmp)
											$whitelisted = TRUE;
									}
							}
						unset($_whitelist, $_allowed_file, $_realpath);

						$allowed = FALSE;
						foreach (array_unique ($allowed_paths) as $_allowed_path)
							if ((($is_windows) ? strtolower ($_allowed_path) : $_allowed_path) === $candidate_cmp)
								{
									$allowed = TRUE;
									break;
								}
						unset($allowed_paths, $_allowed_path);

						$_relative_path = ltrim (substr ($candidate, strlen ($content_dir)), '/');

						if ($allowed)
							{
								// Clear a saved warning only when the site owner explicitly added this file to the whitelist.
								if ($whitelisted)
									do_action ('ws_plugin__s2member_shortcode_template_approved', $_relative_path, $shortcode, (int)$post_id);
								return $candidate;
							}

						// Notify Pro about an unapproved template so it can be included in the admin warning.
						if ($_relative_path && $shortcode)
							do_action ('ws_plugin__s2member_shortcode_template_unapproved', $_relative_path, $shortcode, (int)$post_id);

						// During the transition period unapproved templates still work; enforcement can later be enabled centrally.
						return ($enforce) ? '' : $candidate;
					}
				/**
				* Strips a trailing `/app_data/` sub-directory.
				*
				* @package s2Member\Utilities
				* @since 3.5
				*
				* @param string $path Directory or file path.
				* @return string Directory or file path without `/app_data/`.
				*/
				public static function strip_dir_app_data ($path = FALSE)
					{
						return preg_replace ("/\/app_data$/", "", c_ws_plugin__s2member_utils_dirs::n_dir_seps ((string)$path));
					}
				/**
				* Basename from a full directory or file path.
				*
				* @package s2Member\Utilities
				* @since 110815
				*
				* @param string $path Directory or file path.
				* @return string Basename; including a possible `/app_data/` directory.
				*/
				public static function basename_dir_app_data ($path = FALSE)
					{
						$path = preg_replace ("/\/app_data$/", "", c_ws_plugin__s2member_utils_dirs::n_dir_seps ((string)$path), 1, $app_data);

						return basename ($path) . (($app_data) ? "/app_data" : "");
					}
				/**
				* Shortens to a directory or file path, from document root.
				*
				* @package s2Member\Utilities
				* @since 110815
				*
				* @param string $path Directory or file path.
				* @return string Shorther path, from document root.
				*/
				public static function doc_root_path ($path = FALSE)
					{
						$doc_root = c_ws_plugin__s2member_utils_dirs::n_dir_seps ($_SERVER["DOCUMENT_ROOT"]);

						return preg_replace ("/^" . preg_quote ($doc_root, "/") . "/", "", c_ws_plugin__s2member_utils_dirs::n_dir_seps ((string)$path));
					}
				/**
				* Finds the relative path, from one location to another.
				*
				* @package s2Member\Utilities
				* @since 110815
				*
				* @param string $from The full directory path to calculate a relative path `from`.
				* @param string $to The full directory or file path, which this routine will build a relative path `to`.
				* @param bool $try_realpaths Defaults to true. When true, try to acquire ``realpath()``, thereby resolving all relative paths and/or symlinks in ``$from`` and ``$to`` args.
				* @param bool $use_win_diff_drive_jctn Defaults to true. When true, we'll work around issues with different drives on Windows by trying to create a directory junction.
				* @return string String with the relative path to: ``$to``.
				*/
				public static function rel_path ($from = FALSE, $to = FALSE, $try_realpaths = TRUE, $use_win_diff_drive_jctn = TRUE)
					{
						if ( /* Initialize/validate. */!($rel_path = array()) && is_string ($from) && strlen ($from) && is_string ($to) && strlen ($to))
							{
								$from = ($try_realpaths && ($_real_from = realpath ($from))) ? $_real_from : $from; // Try this?
								$to = ($try_realpaths && ($_real_to = realpath ($to))) ? $_real_to : $to; // Try to find realpath?

								$from = (is_file ($from)) ? dirname ($from) . "/" : $from . "/"; // A (directory) with trailing `/`.

								$from = c_ws_plugin__s2member_utils_dirs::n_dir_seps ($from); // Normalize directory separators now.
								$to = c_ws_plugin__s2member_utils_dirs::n_dir_seps ($to); // Normalize directory separators here too.

								$from = preg_split ("/\//", $from); // Convert ``$from``, to an array. Split on each directory separator.
								$to = preg_split ("/\//", $to); // Also convert ``$to``, to an array. Split this on each directory separator.

								if ($use_win_diff_drive_jctn && stripos (PHP_OS, "win") === 0 /* Test for different drives on Windows servers? */)

									if (/*Drive? */preg_match ("/^([A-Z])\:$/i", $from[0], $_m) && ($_from_drive = $_m[1]) && preg_match ("/^([A-Z])\:$/i", $to[0], $_m) && ($_to_drive = $_m[1]))
										if ( /* Are these locations on completely different drives? */$_from_drive !== $_to_drive)
											{
												$_from_drive_jctn = $_from_drive . ":/s2-" . $_to_drive . "-jctn";
												$_sys_temp_dir_jctn = c_ws_plugin__s2member_utils_dirs::get_temp_dir (false) . "/s2-" . $_to_drive . "-jctn";

												$_jctn = ($_sys_temp_dir_jctn && strpos ($_sys_temp_dir_jctn, $_from_drive) === 0) ? $_sys_temp_dir_jctn : $_from_drive_jctn;

												if (($_from_drive_jctn_exists = (is_dir ($_from_drive_jctn)) ? true : false) || c_ws_plugin__s2member_utils_dirs::create_win_jctn ($_jctn, $_to_drive . ":/"))
													{
														array_shift /* Shift drive off and use junction now. */ ($to);
														foreach (array_reverse (preg_split ("/\//", (($_from_drive_jctn_exists) ? $_from_drive_jctn : $_jctn))) as $_jctn_dir)
															array_unshift ($to, $_jctn_dir);
													}
												else // Else, we should trigger an error in this case. It's NOT possible to generate this.
													{
														trigger_error ("Unable to generate a relative path across different Windows drives." .
															" Please create a Directory Junction here: " . $_from_drive_jctn . ", pointing to: " . $_to_drive . ":/", E_USER_ERROR);
													}
											}

								unset($_real_from, $_real_to, $_from_drive, $_to_drive, $_from_drive_jctn, $_sys_temp_dir_jctn, $_jctn, $_from_drive_jctn_exists, $_jctn_dir, $_m);

								$rel_path = $to; // Re-initialize. Start ``$rel_path`` as the value of the ``$to`` array.

								foreach (array_keys ($from) as $_depth) // Each ``$from`` directory ``$_depth``.
									{
										if (isset ($from[$_depth], $to[$_depth]) && $from[$_depth] === $to[$_depth])
											array_shift ($rel_path);

										else if (($_remaining = count ($from) - $_depth) > 1)
											{
												$_left_p = -1 * (count ($rel_path) + ($_remaining - 1));
												$rel_path = array_pad ($rel_path, $_left_p, "..");
												break; // Stop now, no need to go any further.
											}
										else // Else, set as the same directory `./[0]`.
											{
												$rel_path[0] = "./" . $rel_path[0];
												break; // Stop now.
											}
									}
							}

						return implode ("/", $rel_path);
					}
				/**
				* Creates a directory Junction in Windows.
				*
				* @package s2Member\Utilities
				* @since 111013
				*
				* @param string $jctn Directory location of the Junction (i.e., the link).
				* @param string $target Target directory that this Junction will connect to.
				* @return bool True if created successfully, or already exists, else false.
				*/
				public static function create_win_jctn ($jctn = FALSE, $target = FALSE)
					{
						if ($jctn && is_string ($jctn) && $target && is_string ($target) && stripos (PHP_OS, "win") === 0)
							{
								if (is_dir ($jctn)) // Does it already exist? If so return now.
									return true; // Return now to save extra processing time below.

								else if ( /* Possible? */function_exists ("shell_exec") && ($esa = "escapeshellarg"))
									{
										@shell_exec ("mklink /J " . $esa ($jctn) . " " . $esa ($target));

										clearstatcache (); // Clear ``stat()`` cache now.
										if (is_dir ($jctn)) // Created successfully?
											return true;
									}
							}
						return false; // Else return false.
					}
				/**
				* Get the system's temporary directory.
				*
				* @package s2Member\Utilities
				* @since 111017
				*
				* @param string $fallback Defaults to true. If true, fallback on WordPress routine if not available, or if not writable.
				* @return str|bool Full string path to a writable temp directory, else false on failure.
				*/
				public static function get_temp_dir ($fallback = TRUE)
					{
						$temp_dir = (($temp_dir = realpath (sys_get_temp_dir ())) && is_writable ($temp_dir)) ? $temp_dir : false;
						$temp_dir = (!$temp_dir && $fallback && ($wp_temp_dir = realpath (get_temp_dir ())) && is_writable ($wp_temp_dir)) ? $wp_temp_dir : $temp_dir;

						return ($temp_dir) ? c_ws_plugin__s2member_utils_dirs::n_dir_seps ($temp_dir) : false;
					}
			}
	}
