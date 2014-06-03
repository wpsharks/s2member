<?php
/**
* s2Member class autoloader.
*
* Defines the __autoload function for s2Member classes.
* This highly optimizes s2Member. Giving it a much smaller footprint.
* See: {@link http://www.php.net/manual/en/function.spl-autoload-register.php}
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
* @package s2Member
* @since 3.5
*/
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit ("Do not access this file directly.");

if (!function_exists ("ws_plugin__s2member_classes"))
	{
		/**
		* s2Member class autoloader.
		*
		* The __autoload function for s2Member classes.
		* This highly optimizes s2Member. Giving it a much smaller footprint.
		* See: {@link http://www.php.net/manual/en/function.spl-autoload-register.php}
		*
		* @package s2Member
		* @since 3.5
		*
		* @param string $class The class that needs to be loaded. Passed in by PHP itself.
		* @return null
		*/
		function ws_plugin__s2member_classes ($class = FALSE)
			{
				static /* Holds the classes directory location (location is optimized with a static var). */ $c;
				static /* All possible dir & sub-directory locations (with a static var). */ $c_class_dirs;

				if (strpos ($class, "c_ws_plugin__s2member_") === 0 && strpos ($class, "c_ws_plugin__s2member_pro_") === false)
					{
						$c = /* Configures location of classes. */ (!isset ($c)) ? dirname (dirname (__FILE__)) . "/classes" : $c;
						$c_class_dirs = (!isset ($c_class_dirs)) ? array_merge (array($c), _ws_plugin__s2member_classes_scan_dirs_r ($c)) : $c_class_dirs;

						$class = str_replace ("_", "-", str_replace ("c_ws_plugin__s2member_", "", $class));

						foreach /* Start looking for the class. */ ($c_class_dirs as $class_dir)
							if ($class_dir === $c || strpos ($class, basename ($class_dir)) === 0)
								if (file_exists ($class_dir . "/" . $class . ".inc.php"))
									{
										include_once $class_dir . "/" . $class . ".inc.php";

										break /* Now stop looking. */;
									}
					}
				return /* Return for uniformity. */;
			}
		/**
		* Scans recursively for class sub-directories.
		*
		* Used by the s2Member autoloader.
		*
		* @package s2Member
		* @since 3.5
		*
		* @param string $starting_dir The directory to start scanning from.
		* @return str[] An array of class directories.
		*/
		function _ws_plugin__s2member_classes_scan_dirs_r ($starting_dir = FALSE)
			{
				$dirs = /* Initialize dirs array. */ array();

				foreach (func_get_args () as $starting_dir)
					if /* Does this directory exist? */ (is_dir ($starting_dir))
						foreach /* Scan this directory. */ (scandir ($starting_dir) as $dir)
							if ($dir !== "." && $dir !== ".." && is_dir ($dir = $starting_dir . "/" . $dir))
								$dirs = array_merge ($dirs, array($dir), _ws_plugin__s2member_classes_scan_dirs_r ($dir));

				return /* Return array of all directories. */ $dirs;
			}
		spl_autoload_register /* Register __autoload. */ ("ws_plugin__s2member_classes");
	}
?>