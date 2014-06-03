<?php
/**
* Main Multisite patches.
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
* @package s2Member\Main_Multisite_Patches
* @since 3.5
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit("Do not access this file directly.");

if (!class_exists ("c_ws_plugin__s2member_mms_patches"))
	{
		/**
		* Main Multisite patches.
		*
		* @package s2Member\Main_Multisite_Patches
		* @since 3.5
		*/
		class c_ws_plugin__s2member_mms_patches
			{
				/**
				* Synchronizes Multisite patches with WordPress core upgrades.
				*
				* @package s2Member\Main_Multisite_Patches
				* @since 3.5
				*
				* @attaches-to ``add_filter("update_feedback");``
				*
				* @param string $message Expects message string passed through by the Filter.
				* @return string Message after having been Filtered by this routine.
				*/
				public static function sync_mms_patches ($message = FALSE)
					{
						global $pagenow; // Need access to this global var.

						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_before_sync_mms_patches", get_defined_vars ());
						unset($__refs, $__v);

						if (is_multisite () && is_network_admin () && $pagenow === "update-core.php")
							if ($message === "Upgrading database&#8230;" && c_ws_plugin__s2member_mms_patches::mms_patches ())
								apply_filters("update_feedback", "s2 Multisite patches applied&#8230;");

						return apply_filters("ws_plugin__s2member_sync_mms_patches", $message, get_defined_vars ());
					}
				/**
				* Handles patches on a Multisite Network installation.
				*
				* @package s2Member\Main_Multisite_Patches
				* @since 3.5
				*
				* @param bool $display_notices Defaults to false. If true, notices are displayed.
				* @return bool True if Multisite patches were processed, else false.
				*/
				public static function mms_patches ($display_notices = FALSE)
					{
						foreach(array_keys(get_defined_vars())as$__v)$__refs[$__v]=&$$__v;
						do_action("ws_plugin__s2member_before_mms_patches", get_defined_vars ());
						unset($__refs, $__v);

						if (is_multisite () && is_admin () && is_main_site () && $GLOBALS["WS_PLUGIN__"]["s2member"]["c"]["configured"])
							if ($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["mms_auto_patch"] && (!defined ("DISALLOW_FILE_MODS") || !DISALLOW_FILE_MODS))
								{
									do_action("ws_plugin__s2member_during_mms_patches_before", get_defined_vars ());

									$wp_login_file = ABSPATH . "wp-login.php"; // This works for WordPress 3.0, 3.1, all the way up to 3.7. WordPress 3.1+ uses: `site_url('wp-signup.php')`. WordPress 3.5+ uses: `network_site_url('wp-signup.php')`. WordPress v3.7 uses `$sign_up_url`.
									$wp_login_section = "/(\s+)(wp_redirect( *?)\(( *?)apply_filters( *?)\(( *?)['\"]wp_signup_location['\"]( *?),( *?)(site_url( *?)\(( *?)['\"]wp-signup\.php['\"]( *?)\)|network_site_url( *?)\(( *?)['\"]wp-signup\.php['\"]( *?)\)|get_bloginfo( *?)\(['\"]wpurl['\"]\)( *?)\.( *?)['\"]\/wp-signup\.php['\"]|\\\$sign_?up_url)( *?)\)( *?)\)( *?);)(\s+)(exit( *?);)/";
									$wp_login_replace = "\n\t\t// Modified for full plugin compatiblity.\n\t\t//wp_redirect( apply_filters( 'wp_signup_location', \$sign_up_url ) );\n\t\t//exit;";

									if (file_exists ($wp_login_file) && ($wp_login = file_get_contents ($wp_login_file)) && is_writable ($wp_login_file))
										{
											if ((($wp_login_written = file_put_contents ($wp_login_file, preg_replace ($wp_login_section, $wp_login_replace, $wp_login, 1, $wp_login_patched))) && $wp_login_patched) || ($wp_login_patched_already = $wp_login_patched = strpos ($wp_login, $wp_login_replace)))
												($display_notices) ? c_ws_plugin__s2member_admin_notices::display_admin_notice ('Your <code>/wp-login.php</code> file ' . (($wp_login_patched_already) ? 'is patched' : 'has been patched successfully') . '.') : null;
											else if (!$wp_login_written) // Otherwise, we need to report that /wp-login.php could NOT be updated. Possibly a permissions error.
												($display_notices) ? c_ws_plugin__s2member_admin_notices::display_admin_notice ('Your <code>/wp-login.php</code> file could NOT be patched. Patch NOT written.', true) : null;
											else if (!$wp_login_patched) // Otherwise, we need to report that /wp-login.php could NOT be updated. Wrong WordPress version?
												($display_notices) ? c_ws_plugin__s2member_admin_notices::display_admin_notice ('Your <code>/wp-login.php</code> file could NOT be patched. Unverifiable.', true) : null;
										}
									else // Otherwise, we need to report that /wp-login.php could NOT be updated. Possibly a permissions error.
										($display_notices) ? c_ws_plugin__s2member_admin_notices::display_admin_notice ('Your <code>/wp-login.php</code> file could NOT be patched. File NOT writable.', true) : null;

									$load_file = ABSPATH . WPINC . "/load.php";
									$load_section = "/([\r\n\t\s ]+)(if( *?)\(( *?)empty( *?)\(( *?)\\\$active_plugins( *?)\)( *?)\|\|( *?)defined( *?)\(( *?)['\"]WP_INSTALLING['\"]( *?)\)( *?)\))/";
									$load_replace = "\n\n\t// Modified for full plugin compatiblity.\n\t//if ( empty( \$active_plugins ) || defined( 'WP_INSTALLING' ) )\n\tif ( empty( \$active_plugins ) || ( defined( 'WP_INSTALLING' ) && !preg_match(\"/\/wp-activate\.php/\", \$_SERVER[\"REQUEST_URI\"]) ) )";

									if (file_exists ($load_file) && ($load = file_get_contents ($load_file)) && is_writable ($load_file))
										{
											if ((($load_written = file_put_contents ($load_file, preg_replace ($load_section, $load_replace, $load, 1, $load_patched))) && $load_patched) || ($load_patched_already = $load_patched = strpos ($load, $load_replace)))
												($display_notices) ? c_ws_plugin__s2member_admin_notices::display_admin_notice ('Your <code>/wp-includes/load.php</code> file ' . (($load_patched_already) ? 'is patched' : 'has been patched successfully') . '.') : null;
											else if (!$load_written) // Otherwise, we need to report that /wp-includes/load.php could NOT be updated. Possibly a permissions error.
												($display_notices) ? c_ws_plugin__s2member_admin_notices::display_admin_notice ('Your <code>/wp-includes/load.php</code> file could NOT be patched. Patch NOT written.', true) : null;
											else if (!$load_patched) // Otherwise, we need to report that /wp-includes/load.php could NOT be updated. Wrong WordPress version?
												($display_notices) ? c_ws_plugin__s2member_admin_notices::display_admin_notice ('Your <code>/wp-includes/load.php</code> file could NOT be patched. Unverifiable.', true) : null;
										}
									else // Otherwise, we need to report that /wp-includes/load.php could NOT be updated. Possibly a permissions error.
										($display_notices) ? c_ws_plugin__s2member_admin_notices::display_admin_notice ('Your <code>/wp-includes/load.php</code> file could NOT be patched. File NOT writable.', true) : null;

									$user_new_file = ABSPATH . "wp-admin/user-new.php";
									$user_new_section = "/([\r\n\t\s ]+)(wpmu_signup_user( *?)\(( *?)\\\$new_user_login,( *?)\\\$_REQUEST\[( *?)'email'( *?)\],( *?)array( *?)\(( *?)'add_to_blog'( *?)\=\>( *?)\\\$wpdb->blogid,( *?)'new_role'( *?)\=\>( *?)\\\$_REQUEST\[( *?)'role'( *?)\]( *?)\)( *?)\);)/";
									$user_new_replace = "\n\t\t\t// Modified for full plugin compatiblity.\n\t\t\t//wpmu_signup_user( \$new_user_login, \$_REQUEST[ 'email' ], array( 'add_to_blog' => \$wpdb->blogid, 'new_role' => \$_REQUEST[ 'role' ] ) );\n\t\t\twpmu_signup_user( \$new_user_login, \$_REQUEST[ 'email' ], apply_filters( 'add_signup_meta', array( 'add_to_blog' => \$wpdb->blogid, 'new_role' => \$_REQUEST[ 'role' ] ) ) );";

									if (file_exists ($user_new_file) && ($user_new = file_get_contents ($user_new_file)) && is_writable ($user_new_file))
										{
											if ((($user_new_written = file_put_contents ($user_new_file, preg_replace ($user_new_section, $user_new_replace, $user_new, 1, $user_new_patched))) && $user_new_patched) || ($user_new_patched_already = $user_new_patched = strpos ($user_new, $user_new_replace)))
												($display_notices) ? c_ws_plugin__s2member_admin_notices::display_admin_notice ('Your <code>/wp-admin/user-new.php</code> file ' . (($user_new_patched_already) ? 'is patched' : 'has been patched successfully') . '.') : null;
											else if (!$user_new_written) // Otherwise, we need to report that /wp-admin/user-new.php could NOT be updated. Possibly a permissions error.
												($display_notices) ? c_ws_plugin__s2member_admin_notices::display_admin_notice ('Your <code>/wp-admin/user-new.php</code> file could NOT be patched. Patch NOT written.', true) : null;
											else if (!$user_new_patched) // Otherwise, we need to report that /wp-admin/user-new.php could NOT be updated. Wrong WordPress version?
												($display_notices) ? c_ws_plugin__s2member_admin_notices::display_admin_notice ('Your <code>/wp-admin/user-new.php</code> file could NOT be patched. Unverifiable.', true) : null;
										}
									else // Otherwise, we need to report that /wp-admin/user-new.php could NOT be updated. Possibly a permissions error.
										($display_notices) ? c_ws_plugin__s2member_admin_notices::display_admin_notice ('Your <code>/wp-admin/user-new.php</code> file could NOT be patched. File NOT writable.', true) : null;

									$ms_functions_file = ABSPATH . "wp-includes/ms-functions.php";
									$ms_functions_section = "/([\r\n\t\s ]+)(return new WP_Error( *?)\(( *?)'user_already_exists'( *?),( *?)__( *?)\(( *?)'That username is already activated.'( *?)\),( *?)\\\$signup( *?)\);)/";
									$ms_functions_replace = "\n\t\t\t// Modified for full plugin compatiblity.\n\t\t\t//return new WP_Error( 'user_already_exists', __( 'That username is already activated.' ), \$signup);\n\t\t\treturn apply_filters('_wpmu_activate_existing_error_', new WP_Error( 'user_already_exists', __( 'That username is already activated.' ), \$signup), get_defined_vars());";

									if (file_exists ($ms_functions_file) && ($ms_functions = file_get_contents ($ms_functions_file)) && is_writable ($ms_functions_file))
										{
											if ((($ms_functions_written = file_put_contents ($ms_functions_file, preg_replace ($ms_functions_section, $ms_functions_replace, $ms_functions, 1, $ms_functions_patched))) && $ms_functions_patched) || ($ms_functions_patched_already = $ms_functions_patched = strpos ($ms_functions, $ms_functions_replace)))
												($display_notices) ? c_ws_plugin__s2member_admin_notices::display_admin_notice ('Your <code>/wp-includes/ms-functions.php</code> file ' . (($ms_functions_patched_already) ? 'is patched' : 'has been patched successfully') . '.') : null;
											else if (!$ms_functions_written) // Otherwise, we need to report that /wp-includes/ms-functions.php could NOT be updated. Possibly a permissions error.
												($display_notices) ? c_ws_plugin__s2member_admin_notices::display_admin_notice ('Your <code>/wp-includes/ms-functions.php</code> file could NOT be patched. Patch NOT written.', true) : null;
											else if (!$ms_functions_patched) // Otherwise, we need to report that /wp-includes/ms-functions.php could NOT be updated. Wrong WordPress version?
												($display_notices) ? c_ws_plugin__s2member_admin_notices::display_admin_notice ('Your <code>/wp-includes/ms-functions.php</code> file could NOT be patched. Unverifiable.', true) : null;
										}
									else // Otherwise, we need to report that /wp-includes/ms-functions.php could NOT be updated. Possibly a permissions error.
										($display_notices) ? c_ws_plugin__s2member_admin_notices::display_admin_notice ('Your <code>/wp-includes/ms-functions.php</code> file could NOT be patched. File NOT writable.', true) : null;

									$ran_mms_patches = true; // Flag indicating this routine was indeed processed.

									do_action("ws_plugin__s2member_during_mms_patches_after", get_defined_vars ());
								}

						do_action("ws_plugin__s2member_after_mms_patches", get_defined_vars ());

						return !empty($ran_mms_patches) ? $ran_mms_patches : false;
					}
			}
	}
?>