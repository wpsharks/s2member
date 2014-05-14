<?php
/**
 * User drip access routines.
 *
 * Copyright: © 2009-2014 (coded in the USA)
 * {@link http://www.websharks-inc.com/ WebSharks, Inc.}
 *
 * Released under the terms of the GNU General Public License.
 * You should have received a copy of the GNU General Public License,
 * along with this software. In the main directory, see: /licensing/
 * If not, see: {@link http://www.gnu.org/licenses/}.
 *
 * @package s2Member\User_Drip_Access
 * @since 140514
 */
if(realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME']))
	exit ('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_user_drip_access'))
{
	/**
	 * User drip access routines.
	 *
	 * @package s2Member\User_Drip_Access
	 * @since 140514
	 *
	 * @note MUST use `self::` instead of `static::` for PHP v5.2 compat.
	 */
	class c_ws_plugin__s2member_user_drip_access
	{
		/**
		 * @since 140514 Enhancing `[s2Drip]` shortcode.
		 * @var integer Current `$from_day`; used by callback.
		 */
		protected static $from_day = 0;

		/**
		 * @since 140514 Enhancing `[s2Drip]` shortcode.
		 * @var integer Current `$to_day`; used by callback.
		 */
		protected static $to_day = 0;

		/**
		 * @since 140514 Enhancing `[s2Drip]` shortcode.
		 * @var integer Current `$user_id`; used by callback.
		 */
		protected static $user_id = 0;

		/**
		 * @since 140514 Enhancing `[s2Drip]` shortcode.
		 * @var array Current array of paid times for {@link $user_id}.
		 */
		protected static $all_paid_reg_times = array();

		/**
		 * @since 140514 Enhancing `[s2Drip]` shortcode.
		 * @var array Current array of access capability times for {@link $user_id}.
		 */
		protected static $all_access_cap_times = array();

		/**
		 * Conditional check for drip access.
		 *
		 * @since 140514 Enhancing `[s2Drip]` shortcode.
		 *
		 * @param string       $access Required; conditional expression with access_s2member_ capabilities
		 *    (i.e. leveln, ccap_name), e.g. `level2` or `level3 and (ccap_music or ccap_videos)`.
		 *    Note that `and`, `or` MUST be used in place of `&&`, `||` due to sanitation routines.
		 *    The `$access` string may contain only `[A-Za-z0-9 _()]`.
		 *
		 * @param integer      $from_day Optional. Defaults to `0`. Any value greater than or equal to `0`.
		 * @param integer      $to_day Optional. Defaults to `0`. Any value greater than or equal to `0`.
		 *
		 * @param null|integer $user_id Optional. A `NULL` value indicates the current user.
		 *
		 * @return boolean `TRUE` if user can `$access`; and dripping should occur; based on `$from_day` & `$to_day`.
		 *
		 * @triggers `E_USER_ERROR` if an invalid `$access` syntax is detected; with invalid chars.
		 * @triggers `E_USER_ERROR` if an invalid `$access` syntax is detected; without any word chars.
		 */
		public static function user_can_access_drip($access, $from_day = 0, $to_day = 0, $user_id = NULL)
		{
			$drip     = FALSE;
			$access   = trim((string)$access);
			$from_day = self::$from_day = (integer)$from_day;
			$to_day   = self::$to_day = (integer)$to_day;

			if(!isset($user_id))
				$user_id = get_current_user_id();
			$user_id = self::$user_id = (integer)$user_id;

			if(user_can($user_id, 'administrator'))
				$drip = TRUE;

			else if($access && $user_id)
			{
				if(!is_array($all_access_cap_times = self::$all_access_cap_times = c_ws_plugin__s2member_access_cap_times::get_access_cap_times($user_id)))
					$all_access_cap_times = self::$all_access_cap_times = array();

				$access_expression = strtolower($access); // e.g. 'level1 and ccap_music'
				$access_expression = trim(preg_replace('/[^a-z0-9 _()]/', '', $access_expression, -1, $invalid_chars));
				$access_expression = str_replace(array(' and ', ' or '), array(' && ', ' || '), $access_expression);

				if($invalid_chars)
					trigger_error('Syntax error: invalid chars. Please use only `A-Za-z0-9 _()` in the `access` parameter of s2Drip.', E_USER_ERROR);

				if(!$access_expression || !preg_match('/\w+/', $access_expression))
					trigger_error('Syntax error: no word chars in `access` parameter of s2Drip. Valid example: `level1 and ccap_music`.', E_USER_ERROR);

				$access_expression = preg_replace_callback('/\w+/', 'self::_user_can_access_drip_cb', $access_expression);
				$drip              = eval('return ('.$access_expression.');');
			}
			return apply_filters('ws_plugin__s2member_user_can_access_drip', $drip, get_defined_vars());
		}

		/**
		 * Conditional check for drip access (callback).
		 *
		 * @since 140514 Enhancing `[s2Drip]` shortcode.
		 *
		 * @param array $cap Regex matches passed via {@link \preg_replace_callback()}.
		 *
		 * @return string One of `TRUE` or `FALSE`; as a string value.
		 */
		protected static function _user_can_access_drip_cb($cap)
		{
			$drip = 'FALSE';
			$cap  = (string)$cap[0];

			if($cap && user_can(self::$user_id, 'access_s2member_'.$cap))
			{
				$time            = time();
				$cap_times       = array_keys(self::$all_access_cap_times, $cap, TRUE);
				$cap_time_latest = $cap_times ? max($cap_times) : 0;

				if($cap_time_latest && $time > ($cap_time_latest + (max(0, (self::$from_day - 1)) * 86400)))
				{
					$drip = 'TRUE'; // At/after $from_day.
					if(self::$to_day > 0 && $time > ($cap_time_latest + (self::$to_day * 86400)))
						$drip = 'FALSE'; // After $to_day.
				}
			}

			return apply_filters('ws_plugin__s2member_user_can_access_drip_cb', $drip, get_defined_vars());
		}
	}
}
