<?php
/**
* s2Member-only utilities.
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
* @since 110912
*/
if (!class_exists ("c_ws_plugin__s2member_utils_s2o"))
	{
		/**
		* s2Member-only utilities.
		*
		* @package s2Member\Utilities
		* @since 110912
		*/
		class c_ws_plugin__s2member_utils_s2o
			{
				/*
				* WordPress directory.
				*
				* @package s2Member\Utilities
				* @since 110912
				*
				* @param string $starting_dir A directory to start searching from.
				* @param string $alt_starting_dir An alternate directory to search from.
				* @return string|null WordPress directory, else exits script execution on failure.
				*/
				public static function wp_dir ($starting_dir = FALSE, $alt_starting_dir = FALSE)
					{
						if(!empty($_SERVER['WP_DIR']))
							return (string)$_SERVER['WP_DIR'];

						foreach(array($starting_dir, $alt_starting_dir) as $directory)
							if($directory && is_string($directory) && is_dir($directory))
								for($i = 0, $dir = $directory; $i <= 20; $i++, $dir = dirname($dir))
									if(file_exists($dir."/wp-settings.php"))
										return ($wp_dir = $dir);

						header ("HTTP/1.0 500 Error");
						header ("Content-Type: text/plain; charset=UTF-8");
						while (@ob_end_clean ()); // Clean any existing output buffers.
						exit ("ERROR: s2Member unable to locate WordPress directory.");
					}
				/*
				* WordPress settings, after ``SHORTINIT`` section.
				*
				* @package s2Member\Utilities
				* @since 110912
				*
				* @param string $wp_dir WordPress directory path.
				* @param string $o_file Location of calling `*-o.php` file.
				* @return str|bool WordPress settings, else false on failure.
				*/
				public static function wp_settings_as ($wp_dir = FALSE, $o_file = FALSE)
					{
						if ($wp_dir && is_dir ($wp_dir) && is_readable (($wp_settings = $wp_dir . "/wp-settings.php")) && $o_file && file_exists ($o_file) && ($_wp_settings = trim (file_get_contents ($wp_settings))))
							{
								$wp_shortinit_section /* Run ``preg_match()`` to confirm existence. */ = "/if *\( *SHORTINIT *\)[\r\n\t\s ]*\{?[\r\n\t\s ]*return false;[\r\n\t\s ]*\}?[\r\n\t\s ]*/";
								if (preg_match ($wp_shortinit_section, $_wp_settings) && ($_wp_settings_parts = preg_split ($wp_shortinit_section, $_wp_settings, 2)) && ($_wp_settings = trim ($_wp_settings_parts[1])) && ($_wp_settings = "<?php\n" . $_wp_settings))
									{
										if (($_wp_settings = str_replace ("__FILE__", "'" . str_replace ("'", "\'", $wp_settings) . "'", $_wp_settings))) // Eval compatible. Hard-code the ``__FILE__`` location here.
											{
												$mu_plugins_section = "/[\r\n\t\s ]+foreach *\( *wp_get_mu_plugins *\( *\) *as *\\\$mu_plugin *\)[\r\n\t\s ]*\{?[\r\n\t\s ]*include_once *\( *\\\$mu_plugin *\);[\r\n\t\s ]*\}?[\r\n\t\s ]*unset *\( *\\\$mu_plugin *\);/";
												$mu_plugins_replace = "\n\n" . c_ws_plugin__s2member_utils_s2o::esc_ds (trim (c_ws_plugin__s2member_utils_s2o::evl (file_get_contents (dirname (dirname (__FILE__)) . "/templates/cfg-files/s2o-mu-plugins.php")))) . "\n";
												if (($_wp_settings = preg_replace ($mu_plugins_section, $mu_plugins_replace, $_wp_settings, 1, $mu_plugins_replaced)) && $mu_plugins_replaced)
													{
														$nw_plugins_section = "/[\r\n\t\s ]+foreach *\( *wp_get_active_network_plugins *\( *\) *as *\\\$network_plugin *\)[\r\n\t\s ]*\{?[\r\n\t\s ]*include_once *\( *\\\$network_plugin *\);[\r\n\t\s ]*\}?[\r\n\t\s ]*unset *\( *\\\$network_plugin *\);/";
														$nw_plugins_replace = "\n\n" . c_ws_plugin__s2member_utils_s2o::esc_ds (trim (c_ws_plugin__s2member_utils_s2o::evl (file_get_contents (dirname (dirname (__FILE__)) . "/templates/cfg-files/s2o-nw-plugins.php")))) . "\n";
														if (($_wp_settings = preg_replace ($nw_plugins_section, $nw_plugins_replace, $_wp_settings, 1, $nw_plugins_replaced)) && $nw_plugins_replaced)
															{
																$st_plugins_section = "/[\r\n\t\s ]+foreach *\( *wp_get_active_and_valid_plugins *\( *\) *as *\\\$plugin *\)[\r\n\t\s ]*\{?[\r\n\t\s ]*include_once *\( *\\\$plugin *\);[\r\n\t\s ]*\}?[\r\n\t\s ]*unset *\( *\\\$plugin *\);/";
																$st_plugins_replace = "\n\n" . c_ws_plugin__s2member_utils_s2o::esc_ds (trim (c_ws_plugin__s2member_utils_s2o::evl (file_get_contents (dirname (dirname (__FILE__)) . "/templates/cfg-files/s2o-st-plugins.php")))) . "\n";
																if (($_wp_settings = preg_replace ($st_plugins_section, $st_plugins_replace, $_wp_settings, 1, $st_plugins_replaced)) && $st_plugins_replaced)
																	{
																		$th_funcs_section = "/[\r\n\t\s ]+if *\( *\! *defined *\( *['\"]WP_INSTALLING['\"] *\) *\|\| *['\"]wp-activate.php['\"] *\=\=\= *\\\$pagenow *\)[\r\n\t\s ]*\{[\r\n\t\s ]*if *\( *TEMPLATEPATH *\!\=\= *STYLESHEETPATH *&& *file_exists *\( *STYLESHEETPATH *\. *['\"]\/functions\.php['\"] *\) *\)[\r\n\t\s ]*\{?[\r\n\t\s ]*include *\( *STYLESHEETPATH *\. *['\"]\/functions\.php['\"] *\);[\r\n\t\s ]*\}?[\r\n\t\s ]*if *\( *file_exists *\( *TEMPLATEPATH *\. *['\"]\/functions\.php['\"] *\) *\)[\r\n\t\s ]*\{?[\r\n\t\s ]*include *\( *TEMPLATEPATH *\. *['\"]\/functions\.php['\"] *\);[\r\n\t\s ]*\}?[\r\n\t\s ]*\}/";
																		$th_funcs_replace = "\n\n" . c_ws_plugin__s2member_utils_s2o::esc_ds (trim (c_ws_plugin__s2member_utils_s2o::evl (file_get_contents (dirname (dirname (__FILE__)) . "/templates/cfg-files/s2o-th-funcs.php")))) . "\n";
																		if (($_wp_settings = preg_replace ($th_funcs_section, $th_funcs_replace, $_wp_settings, 1, $th_funcs_replaced)) && $th_funcs_replaced)
																			{
																				if (($_wp_settings = str_replace ("__FILE__", '"' . str_replace ('"', '\"', $o_file) . '"', $_wp_settings))) // Eval compatible.
																					{
																						if (($_wp_settings = trim ($_wp_settings))) // WordPress, with s2Member only.
																							return ($wp_settings_as = $_wp_settings); // After ``SHORTINIT``.
																					}
																			}
																	}
															}
													}
											}
									}
							}
						return false;
					}
				/**
				* Escapes dollars signs (for regex patterns).
				*
				* @package s2Member\Utilities
				* @since 110917
				*
				* @param string $string Input string.
				* @param int $times Mumber of escapes. Defaults to 1.
				* @return string Output string after dollar signs are escaped.
				*/
				public static function esc_ds ($string = FALSE, $times = FALSE)
					{
						$times = (is_numeric ($times) && $times >= 0) ? (int)$times : 1;
						return str_replace ('$', str_repeat ("\\", $times) . '$', (string)$string);
					}
				/**
				* Evaluates PHP code, and "returns" output.
				*
				* @package s2Member\Utilities
				* @since 110917
				*
				* @param string $code A string of data, possibly with embedded PHP code.
				* @return string Output after PHP evaluation.
				*/
				public static function evl ($code = FALSE)
					{
						ob_start (); // Output buffer.

						eval ("?>" . trim ($code));

						return ob_get_clean ();
					}
			}
	}
?>