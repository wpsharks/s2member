<?php
// @codingStandardsIgnoreFile
/**
 * WordPress with s2Member only.
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
 * @package s2Member
 * @since 110912
 */
//260904.2255 A public health request proves that this exact PHP file is reachable, then exits before loading any s2Member or WordPress code.
if(isset($_GET['s2member_health_check']))
{
	$ws_plugin__s2member_health_token = (isset($_GET['s2member_health_token'])) ? preg_replace('/[^a-zA-Z0-9_-]/', '', substr((string)$_GET['s2member_health_token'], 0, 80)) : '';
	$ws_plugin__s2member_health_time = sprintf('%.6F', microtime(TRUE));
	header('Content-Type: text/plain; charset=UTF-8');
	header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
	header('Pragma: no-cache');
	header('X-s2Member-Loader: lightweight');
	header('X-s2Member-Health-Token: '.$ws_plugin__s2member_health_token);
	header('X-s2Member-Health-Time: '.$ws_plugin__s2member_health_time);
	echo 's2member-o-health:'.$ws_plugin__s2member_health_token.':'.$ws_plugin__s2member_health_time;
	exit;
}

define ('_WS_PLUGIN__S2MEMBER_ONLY', TRUE);

include_once dirname(__FILE__).'/src/includes/classes/utils-s2o.inc.php';

if(($ws_plugin__s2member_o['wp_dir'] = c_ws_plugin__s2member_utils_s2o::wp_dir(dirname(__FILE__), dirname($_SERVER['SCRIPT_FILENAME']))))
{
	if(($ws_plugin__s2member_o['wp_settings_as'] = c_ws_plugin__s2member_utils_s2o::wp_settings_as($ws_plugin__s2member_o['wp_dir'], __FILE__)))
	{
		/**
		 * Short initialization mode for WordPress.
		 *
		 * @package s2Member
		 * @since 110912
		 *
		 * @var bool
		 */
		define ('SHORTINIT', TRUE);

		/**
		 * Flag indicating only s2Member is being loaded.
		 *
		 * @package s2Member
		 * @since 110912
		 *
		 * @var bool
		 */
		define ('WS_PLUGIN__S2MEMBER_ONLY', TRUE);

		/*
		Load WordPress.
		*/
		require($ws_plugin__s2member_o['wp_dir'].'/wp-load.php');
		eval ('?>'.$ws_plugin__s2member_o['wp_settings_as']);
	}
	else // Else fallback on full WordPress.
		require($ws_plugin__s2member_o['wp_dir'].'/wp-load.php');
}
unset($ws_plugin__s2member_o);
