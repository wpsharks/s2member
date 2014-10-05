<?php
/**
 * AWeber
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
 * @since 141004
 * @package s2Member\List_Servers
 */
if(realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME']))
	exit('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_aweber'))
{
	/**
	 * AWeber
	 *
	 * @since 141004
	 * @package s2Member\List_Servers
	 */
	class c_ws_plugin__s2member_aweber extends c_ws_plugin__s2member_list_server_base
	{
		/**
		 * Subscribe.
		 *
		 * @since 141004
		 * @package s2Member\List_Servers
		 *
		 * @param array $args Input arguments.
		 *
		 * @return bool True if successful.
		 */
		public static function subscribe($args)
		{
			if($GLOBALS['WS_PLUGIN__']['s2member']['o']['aweber_api_type'] === 'email')
				return c_ws_plugin__s2member_aweber_e::subscribe($args);

			if(!($args = self::validate_args($args)))
				return FALSE; // Invalid args.

			if(!$args->opt_in) // Double check.
				return FALSE; // Must say explicitly.

			if(!$GLOBALS['WS_PLUGIN__']['s2member']['o']['aweber_api_key'])
				return FALSE; // Not possible.

			if(empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['level'.$args->level.'_aweber_list_ids']))
				return FALSE; // No list configured at this level.

			$aw_level_list_ids = $GLOBALS['WS_PLUGIN__']['s2member']['o']['level'.$args->level.'_aweber_list_ids'];

			foreach(preg_split('/['."\r\n\t".'\s;,]+/', $aw_level_list_ids, NULL, PREG_SPLIT_NO_EMPTY) as $_aw_list)
			{
				$_aw = array(
					'args'       => $args,
					'function'   => __FUNCTION__,
					'list'       => trim($_aw_list),
					'list_id'    => trim($_aw_list),
					'api_method' => 'listSubscribe'
				);
				if(!$_aw['list']) continue; // List missing.

				c_ws_plugin__s2member_utils_logs::log_entry('aweber-api', $_aw);
			}
			unset($_aw_list, $_aw); // Just a little housekeeping.

			return !empty($success); // If one suceeds.
		}

		/**
		 * Unsubscribe.
		 *
		 * @since 141004
		 * @package s2Member\List_Servers
		 *
		 * @param array $args Input arguments.
		 *
		 * @return bool True if successful.
		 */
		public static function unsubscribe($args)
		{
			if($GLOBALS['WS_PLUGIN__']['s2member']['o']['aweber_api_type'] === 'email')
				return c_ws_plugin__s2member_aweber_e::unsubscribe($args);

			if(!($args = self::validate_args($args)))
				return FALSE; // Invalid args.

			if(!$args->opt_out) // Double check.
				return FALSE; // Must say explicitly.

			if(!$GLOBALS['WS_PLUGIN__']['s2member']['o']['aweber_api_key'])
				return FALSE; // Not possible.

			if(empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['level'.$args->level.'_aweber_list_ids']))
				return FALSE; // No list configured at this level.

			$aw_level_list_ids = $GLOBALS['WS_PLUGIN__']['s2member']['o']['level'.$args->level.'_aweber_list_ids'];

			foreach(preg_split('/['."\r\n\t".'\s;,]+/', $aw_level_list_ids, NULL, PREG_SPLIT_NO_EMPTY) as $_aw_list)
			{
				$_aw = array(
					'args'       => $args,
					'function'   => __FUNCTION__,
					'list'       => trim($_aw_list),
					'list_id'    => trim($_aw_list),
					'api_method' => 'listUnsubscribe'
				);
				if(!$_aw['list']) continue; // List missing.

				c_ws_plugin__s2member_utils_logs::log_entry('aweber-api', $_aw);
			}
			unset($_aw_list, $_aw); // Just a little housekeeping.

			return !empty($success); // If one suceeds.
		}

		/**
		 * Transition.
		 *
		 * @since 141004
		 * @package s2Member\List_Servers
		 *
		 * @param array $old_args Input arguments.
		 * @param array $new_args Input arguments.
		 *
		 * @return bool True if successful.
		 */
		public static function transition($old_args, $new_args)
		{
			if($GLOBALS['WS_PLUGIN__']['s2member']['o']['aweber_api_type'] === 'email')
				return c_ws_plugin__s2member_aweber_e::transition($old_args, $new_args);

			return self::unsubscribe($old_args) && self::subscribe($new_args);
		}
	}
}