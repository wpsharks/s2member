<?php
/**
 * Registration Times.
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
 * @package s2Member\Registrations
 * @since 3.5
 */
if(realpath(__FILE__) === realpath($_SERVER["SCRIPT_FILENAME"]))
	exit ("Do not access this file directly.");

if(!class_exists("c_ws_plugin__s2member_registration_times"))
	{
		/**
		 * Registration Times.
		 *
		 * @package s2Member\Registrations
		 * @since 3.5
		 */
		class c_ws_plugin__s2member_registration_times
		{
			/**
			 * @var array Used internally to track previous caps by user ID.
			 *
			 * @package s2Member\Registrations
			 * @since 140504
			 */
			protected static $prev_caps_by_user = array();

			/**
			 * Synchronizes Paid Registration Times with Role assignments.
			 *
			 * @package s2Member\Registrations
			 * @since 3.5
			 *
			 * @attaches-to ``add_action("set_user_role");``
			 *
			 * @param integer|string $user_id A numeric WordPress User ID should be passed in by the Action Hook.
			 * @param string         $role A WordPress Role ID/Name should be passed in by the Action Hook.
			 *
			 * @return null
			 */
			public static function synchronize_paid_reg_times($user_id = FALSE, $role = FALSE)
				{
					foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
					do_action("ws_plugin__s2member_before_synchronize_paid_reg_times", get_defined_vars());
					unset($__refs, $__v);

					if($user_id && is_object($user = new WP_User ($user_id)) && !empty ($user->ID) && ($level = c_ws_plugin__s2member_user_access::user_access_level($user)) > 0)
						{
							$pr_times                 = get_user_option("s2member_paid_registration_times", $user_id);
							$pr_times["level"]        = (empty ($pr_times["level"])) ? time() : $pr_times["level"];
							$pr_times["level".$level] = (empty ($pr_times["level".$level])) ? time() : $pr_times["level".$level];
							update_user_option($user_id, "s2member_paid_registration_times", $pr_times); // Update now.
						}
				}

			/**
			 * Retrieves a Registration Time.
			 *
			 * @package s2Member\Registrations
			 * @since 3.5
			 *
			 * @param integer|string $user_id Optional. A numeric WordPress User ID. Defaults to the current User, if logged-in.
			 *
			 * @return int A Unix timestamp, indicating Registration Time, else `0` on failure.
			 */
			public static function registration_time($user_id = FALSE)
				{
					foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
					do_action("ws_plugin__s2member_before_registration_time", get_defined_vars());
					unset($__refs, $__v);

					$user = ($user_id) ? new WP_User ($user_id) : ((is_user_logged_in()) ? wp_get_current_user() : FALSE);

					if(is_object($user) && !empty ($user->ID) && ($user_id = $user->ID) && $user->user_registered)
						{
							return apply_filters("ws_plugin__s2member_registration_time", strtotime($user->user_registered), get_defined_vars());
						}
					else // Else we return a default value of 0, because there is insufficient data.
						return apply_filters("ws_plugin__s2member_registration_time", 0, get_defined_vars());
				}

			/**
			 * Retrieves a Paid Registration Time.
			 *
			 * @package s2Member\Registrations
			 * @since 3.5
			 *
			 * @param int|string $level Optional. Defaults to the first/initial Paid Registration Time, regardless of Level#.
			 * @param int|string $user_id Optional. A numeric WordPress User ID. Defaults to the current User, if logged-in.
			 *
			 * @return int A Unix timestamp, indicating Paid Registration Time, else `0` on failure.
			 */
			public static function paid_registration_time($level = FALSE, $user_id = FALSE)
				{
					foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
					do_action("ws_plugin__s2member_before_paid_registration_time", get_defined_vars());
					unset($__refs, $__v);

					$level = (!is_numeric($level)) ? "level" : "level".preg_replace("/[^0-9]/", "", (string)$level);
					$user  = ($user_id) ? new WP_User ($user_id) : ((is_user_logged_in()) ? wp_get_current_user() : FALSE);

					if($level && is_object($user) && !empty ($user->ID) && ($user_id = $user->ID) && is_array($pr_times = get_user_option("s2member_paid_registration_times", $user_id)))
						{
							return apply_filters("ws_plugin__s2member_paid_registration_time", ((isset ($pr_times[$level])) ? (int)$pr_times[$level] : 0), get_defined_vars());
						}
					else // Else we return a default value of `0`, because there is insufficient data.
						return apply_filters("ws_plugin__s2member_paid_registration_time", 0, get_defined_vars());
				}

			/**
			 * Get user caps before udpate.
			 *
			 * @package s2Member\Registrations
			 * @since 140504
			 *
			 * @attaches-to ``add_action("update_user_meta")``
			 *
			 * @param integer $meta_id Meta row ID in database.
			 * @param integer $object_id User ID.
			 * @param string  $meta_key Meta key.
			 * @param mixed   $meta_value Meta value.
			 */
			public static function get_user_caps_before_update($meta_id, $object_id, $meta_key, $meta_value)
				{
					$wpdb = $GLOBALS["wpdb"];
					/** @var $wpdb \wpdb For IDEs. */

					if(strpos($meta_key, "capabilities") === FALSE || $meta_key !== $wpdb->get_blog_prefix()."capabilities")
						return; // Not updating caps.

					$user_id = $object_id;
					$user    = new WP_User($user_id);
					if(!$user->ID || !$user->exists())
						return; // Not a valid user.

					self::$prev_caps_by_user[$user_id] = $user->caps;
				}

			/**
			 * Logs access capability times.
			 *
			 * @package s2Member\Registrations
			 * @since 140418
			 *
			 * @attaches-to ``add_action("updated_user_meta")``
			 *
			 * @param integer $meta_id Meta row ID in database.
			 * @param integer $object_id User ID.
			 * @param string  $meta_key Meta key.
			 * @param mixed   $meta_value Meta value.
			 */
			public static function log_access_cap_time($meta_id, $object_id, $meta_key, $meta_value)
				{
					$wpdb = $GLOBALS["wpdb"];
					/** @var $wpdb \wpdb For IDEs. */

					if(strpos($meta_key, "capabilities") === FALSE || $meta_key !== $wpdb->get_blog_prefix()."capabilities")
						return; // Not updating caps.

					$user_id = $object_id;
					$user    = new WP_User($user_id);
					if(!$user->ID || !$user->exists())
						return; // Not a valid user.

					$caps["prev"]            = !empty(self::$prev_caps_by_user[$user_id]) ? self::$prev_caps_by_user[$user_id] : array();
					self::$prev_caps_by_user = array();
					$caps["new"]             = is_array($meta_value) ? $meta_value : array();
					$role_objects            = $GLOBALS["wp_roles"]->role_objects;

					foreach($caps as &$_caps)
						{
							foreach(array_intersect($_caps, array_keys($role_objects)) as $_role)
								$_caps = array_merge($_caps, array_keys($role_objects[$_role]->capabilities));
							$_caps = array_unique($_caps);

							foreach($_caps as $_k => $_cap)
								if(strpos($_cap, "access_s2member_") !== 0)
									unset($_caps[$_k]);
						}
					unset($_caps, $_role, $_k, $_cap);

					$ac_times = get_user_option("s2member_access_cap_times", $user_id);
					$time     = (float)time();

					foreach($caps["new"] as $_new_cap => $_is_enabled)
						if($_is_enabled && (!array_key_exists($_new_cap, $caps["prev"]) || !$caps["prev"][$_new_cap]))
							$ac_times[(string)($time += .0001)] = $_new_cap;
					unset($_new_cap, $_is_enabled);

					foreach($caps["prev"] as $_prev_cap => $_was_enabled)
						if(!array_key_exists($_prev_cap, $caps["new"]) || (!$caps["new"][$_prev_cap] && $_was_enabled))
							$ac_times[(string)($time += .0001)] = "-".$_prev_cap;
					unset($_prev_cap, $_was_enabled);

					update_user_option($user_id, "s2member_access_cap_times", $ac_times);
				}

			/**
			 * Gets access capability times.
			 *
			 * @package s2Member\Registrations
			 * @since 140418
			 *
			 * @param integer $user_id WP User ID.
			 * @param boolean $access_caps Optional. An array of access capabilities to get the times for.
			 *    If removal times are desired, you should add a `-` prefix.
			 *
			 * @return array An array of all access capability times.
			 *    Keys are UTC timestamps, values are the capabilities (including `-` prefixed removals).
			 */
			public static function get_access_cap_times($user_id, $access_caps = FALSE)
				{
					if(($user_id = (integer)$user_id))
						{
							$ac_times = get_user_option("s2member_access_cap_times", $user_id);

							if(!is_array($ac_times))
								$ac_times = array();

							else if($access_caps)
								$ac_times = array_intersect($ac_times, (array)$access_caps);

							ksort($ac_times, SORT_NUMERIC);
						}
					else $ac_times = array();

					return apply_filters("ws_plugin__s2member_get_access_cap_times", $ac_times, get_defined_vars());
				}
		}
	}
?>