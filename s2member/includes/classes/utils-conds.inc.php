<?php
/**
* Conditional utilities.
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

if(!class_exists("c_ws_plugin__s2member_utils_conds"))
	{
		/**
		* Conditional utilities.
		*
		* @package s2Member\Utilities
		* @since 3.5
		*/
		class c_ws_plugin__s2member_utils_conds
			{
				/**
				* Determines whether or not s2Member Pro is installed.
				*
				* @package s2Member\Utilities
				* @since 110720
				*
				* @return bool True if s2Member Pro is installed, else false.
				*/
				public static function pro_is_installed()
					{
						return (defined("WS_PLUGIN__S2MEMBER_PRO_VERSION") && did_action("ws_plugin__s2member_pro_loaded"));
					}
				/**
				* Determines whether or not bbPress is installed.
				*
				* @package s2Member\Utilities
				* @since 140807
				*
				* @param bool $query_active_plugins Optional. If true, this conditional will query active plugins too. Defaults to true if {@link s2Member\WS_PLUGIN__S2MEMBER_ONLY} is true, else false.
				* @return bool True if bbPress is installed, else false.
				*/
				public static function bbp_is_installed($query_active_plugins = NULL)
					{
						if(function_exists('bbpress'))
							return true; // Quickest/easiest way to determine.

						$s2o = (defined("WS_PLUGIN__S2MEMBER_ONLY") && WS_PLUGIN__S2MEMBER_ONLY) ? true : false;

						if(($query_active_plugins = (!isset($query_active_plugins) && $s2o) ? true : $query_active_plugins))
							{
								$bbpress = "bbpress/bbpress.php"; // bbPress.

								$active_plugins = (is_multisite()) ? wp_get_active_network_plugins() : array();
								$active_plugins = array_unique(array_merge($active_plugins, wp_get_active_and_valid_plugins()));

								foreach($active_plugins as $active_plugin) // Search.
									if(plugin_basename($active_plugin) === $bbpress)
										return true; // bbPress active.
							}
						return false; // Default return false.
					}
				/**
				* Determines whether or not BuddyPress is installed.
				*
				* @package s2Member\Utilities
				* @since 110720
				*
				* @param bool $query_active_plugins Optional. If true, this conditional will query active plugins too. Defaults to true if {@link s2Member\WS_PLUGIN__S2MEMBER_ONLY} is true, else false.
				* @return bool True if BuddyPress is installed, else false.
				*/
				public static function bp_is_installed($query_active_plugins = NULL)
					{
						if(defined("BP_VERSION") && did_action("bp_core_loaded"))
							return true; // Quickest/easiest way to determine.

						$s2o = (defined("WS_PLUGIN__S2MEMBER_ONLY") && WS_PLUGIN__S2MEMBER_ONLY) ? true : false;

						if(($query_active_plugins = (!isset($query_active_plugins) && $s2o) ? true : $query_active_plugins))
							{
								$buddypress = "buddypress/bp-loader.php"; // BuddyPress.

								$active_plugins = (is_multisite()) ? wp_get_active_network_plugins() : array();
								$active_plugins = array_unique(array_merge($active_plugins, wp_get_active_and_valid_plugins()));

								foreach($active_plugins as $active_plugin) // Search.
									if(plugin_basename($active_plugin) === $buddypress)
										return true; // BuddyPress active.
							}
						return false; // Default return false.
					}
				/**
				* Determines whether or not this is a Multisite Farm;
				* *( i.e. if ``MULTISITE_FARM == true`` inside `/wp-config.php` )*.
				*
				* With s2Member, this option may also indicate a Multisite Blog Farm.
				* ``$GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["mms_registration_file"] === "wp-signup"``.
				*
				* @package s2Member\Utilities
				* @since 3.5
				*
				* @return bool True if this is a Multisite Farm, else false.
				*/
				public static function is_multisite_farm()
					{
						return (is_multisite() && ((is_main_site() && $GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["mms_registration_file"] === "wp-signup") || (defined("MULTISITE_FARM") && MULTISITE_FARM)));
					}
				/**
				* Checks if a Post is in a child Category.
				*
				* @package s2Member\Utilities
				* @since 3.5
				*
				* @param array $cats An array of Category IDs.
				* @param int|string $post_id A numeric WordPress Post ID.
				* @return bool True if the Post is inside a desendant of at least one of the specified Categories; else false.
				*/
				public static function in_descendant_category($cats = FALSE, $post_id = FALSE)
					{
						foreach((array)$cats as $cat)
							{
								$descendants = get_term_children((int)$cat, "category");
								if($descendants && in_category($descendants, $post_id))
									return true;
							}
						return false; // Default return false.
					}
				/**
				* Checks to see if a URL/URI leads to the site root.
				*
				* @package s2Member\Utilities
				* @since 3.5
				*
				* @param string $url_uri Either a full URL, or a partial URI to test.
				* @return bool True if the URL or URI leads to the site root, else false.
				*/
				public static function is_site_root($url_uri = FALSE)
					{
						if(is_array($parse = c_ws_plugin__s2member_utils_urls::parse_url($url_uri)))
							{
								$parse["path"] = (!empty($parse["path"])) ? ((strpos($parse["path"], "/") === 0) ? $parse["path"] : "/".$parse["path"]) : "/";

								if(empty($parse["host"]) || strcasecmp($parse["host"], c_ws_plugin__s2member_utils_urls::parse_url(site_url(), PHP_URL_HOST)) === 0)
									if($parse["path"] === "/" || rtrim($parse["path"], "/") === rtrim(c_ws_plugin__s2member_utils_urls::parse_url(site_url(), PHP_URL_PATH), "/"))
										if(get_option("permalink_structure") || (empty($_GET["post_id"]) && empty($_GET["page_id"]) && empty($_GET["p"])))
											return true;
							}
						return false; // Default return false.
					}
				/**
				* Checks to see if we're in a localhost environment.
				*
				* @package s2Member\Utilities
				* @since 111101
				*
				* @return bool True if we're in a localhost environment, else false.
				*/
				public static function is_localhost()
					{
						if((defined("LOCALHOST") && LOCALHOST) || stripos($_SERVER["HTTP_HOST"], "localhost") !== false || strpos($_SERVER["HTTP_HOST"], "127.0.0.1") !== false)
							return true;

						return /* Default return false. */ false;
					}
				/**
				* Checks to see if we're using Amazon S3.
				*
				* @package s2Member\Utilities
				* @since 110926
				*
				* @return bool True if using Amazon S3, else false.
				*/
				public static function using_amazon_s3_storage()
					{
						foreach($GLOBALS["WS_PLUGIN__"]["s2member"]["o"] as $option => $option_value)
							if(preg_match("/^amazon_s3_files_/", $option) && ($option = preg_replace("/^amazon_s3_files_/", "", $option)))
								$s3c[$option] = $option_value;

						if(!empty($s3c["bucket"]) && !empty($s3c["access_key"]) && !empty($s3c["secret_key"]))
							return true;

						return /* Default return false. */ false;
					}
				/**
				* Checks to see if we're using Amazon CloudFront.
				*
				* @package s2Member\Utilities
				* @since 110926
				*
				* @return bool True if using Amazon CloudFront, else false.
				*/
				public static function using_amazon_cf_storage()
					{
						foreach($GLOBALS["WS_PLUGIN__"]["s2member"]["o"] as $option => $option_value)
							if(preg_match("/^amazon_s3_files_/", $option) && ($option = preg_replace("/^amazon_s3_files_/", "", $option)))
								$s3c[$option] = $option_value;

						foreach($GLOBALS["WS_PLUGIN__"]["s2member"]["o"] as $option => $option_value)
							if(preg_match("/^amazon_cf_files_/", $option) && ($option = preg_replace("/^amazon_cf_files_/", "", $option)))
								$cfc[$option] = $option_value;

						if(!empty($s3c["bucket"]) && !empty($s3c["access_key"]) && !empty($s3c["secret_key"]))
							if(!empty($cfc["private_key"]) && !empty($cfc["private_key_id"]) && !empty($cfc["distros_access_id"]) && !empty($cfc["distros_s3_access_id"]) && !empty($cfc["distro_downloads_id"]) && !empty($cfc["distro_downloads_dname"]) && !empty($cfc["distro_streaming_id"]) && !empty($cfc["distro_streaming_dname"]))
								return true;

						return /* Default return false. */ false;
					}
			}
	}
?>