<?php
/**
 * User utilities.
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
	exit ("Do not access this file directly.");

if(!class_exists("c_ws_plugin__s2member_utils_users"))
{
	/**
	 * User utilities.
	 *
	 * @package s2Member\Utilities
	 * @since 3.5
	 */
	class c_ws_plugin__s2member_utils_users
	{
		/**
		 * Determines the total Users/Members in the database.
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @return int Number of Users in the database, total.
		 */
		public static function users_in_database()
		{
			global $wpdb;
			/** @var wpdb $wpdb */

			$wpdb->query("SELECT SQL_CALC_FOUND_ROWS `".$wpdb->users."`.`ID` FROM `".$wpdb->users."`, `".$wpdb->usermeta."` WHERE `".$wpdb->users."`.`ID` = `".$wpdb->usermeta."`.`user_id` AND `".$wpdb->usermeta."`.`meta_key` = '".esc_sql($wpdb->prefix."capabilities")."' LIMIT 1");
			$users = (int)$wpdb->get_var("SELECT FOUND_ROWS()");

			return $users;
		}

		/**
		 * Obtains Custom String for an existing Member, referenced by a Subscr. or Transaction ID.
		 *
		 * A second lookup parameter can be provided as well *(optional)*.
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @param string $subscr_txn_baid_cid_id Either a Paid Subscr. ID, or a Paid Transaction ID.
		 * @param string $os0 Optional. A second lookup parameter, usually the `os0` value for PayPal integrations.
		 *
		 * @return string|bool The Custom String value on success, else false on failure.
		 */
		public static function get_user_custom_with($subscr_txn_baid_cid_id = '', $os0 = '')
		{
			global $wpdb; /** @var wpdb $wpdb */

			if($subscr_txn_baid_cid_id && $os0) // This case includes some additional routines that can use the ``$os0`` value.
			{
				if(($q = $wpdb->get_row("SELECT `user_id` FROM `".$wpdb->usermeta."` WHERE (`meta_key` = '".$wpdb->prefix."s2member_subscr_id' OR `meta_key` = '".$wpdb->prefix."s2member_subscr_baid' OR `meta_key` = '".$wpdb->prefix."s2member_subscr_cid' OR `meta_key` = '".$wpdb->prefix."s2member_first_payment_txn_id') AND (`meta_value` = '".esc_sql($subscr_txn_baid_cid_id)."' OR `meta_value` = '".esc_sql($os0)."') LIMIT 1"))
				   || ($q = $wpdb->get_row("SELECT `ID` AS `user_id` FROM `".$wpdb->users."` WHERE `ID` = '".esc_sql($os0)."' LIMIT 1"))
				) if(($custom = get_user_option("s2member_custom", $q->user_id)))
					return $custom;
			}
			else if($subscr_txn_baid_cid_id) // Otherwise, if all we have is a Subscr./Txn. ID value.
			{
				if(($q = $wpdb->get_row("SELECT `user_id` FROM `".$wpdb->usermeta."` WHERE (`meta_key` = '".$wpdb->prefix."s2member_subscr_id' OR `meta_key` = '".$wpdb->prefix."s2member_subscr_baid' OR `meta_key` = '".$wpdb->prefix."s2member_subscr_cid' OR `meta_key` = '".$wpdb->prefix."s2member_first_payment_txn_id') AND `meta_value` = '".esc_sql($subscr_txn_baid_cid_id)."' LIMIT 1")))
					if(($custom = get_user_option("s2member_custom", $q->user_id)))
						return $custom;
			}
			return FALSE; // Otherwise, return false.
		}

		/**
		 * Obtains the User ID for an existing Member, referenced by a Subscr. or Transaction ID.
		 *
		 * A second lookup parameter can be provided as well *(optional)*.
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @param string $subscr_txn_baid_cid_id Either a Paid Subscr. ID, or a Paid Transaction ID.
		 * @param string $os0 Optional. A second lookup parameter, usually the `os0` value for PayPal integrations.
		 *
		 * @return integer|bool A WordPress User ID on success, else false on failure.
		 */
		public static function get_user_id_with($subscr_txn_baid_cid_id = '', $os0 = '')
		{
			global $wpdb;
			/** @var wpdb $wpdb */

			if($subscr_txn_baid_cid_id && $os0) // This case includes some additional routines that can use the ``$os0`` value.
			{
				if(($q = $wpdb->get_row("SELECT `user_id` FROM `".$wpdb->usermeta."` WHERE (`meta_key` = '".$wpdb->prefix."s2member_subscr_id' OR `meta_key` = '".$wpdb->prefix."s2member_subscr_baid' OR `meta_key` = '".$wpdb->prefix."s2member_subscr_cid' OR `meta_key` = '".$wpdb->prefix."s2member_first_payment_txn_id') AND (`meta_value` = '".esc_sql($subscr_txn_baid_cid_id)."' OR `meta_value` = '".esc_sql($os0)."') LIMIT 1"))
				   || ($q = $wpdb->get_row("SELECT `ID` AS `user_id` FROM `".$wpdb->users."` WHERE `ID` = '".esc_sql($os0)."' LIMIT 1"))
				) return $q->user_id;
			}
			else if($subscr_txn_baid_cid_id) // Otherwise, if all we have is a Subscr./Txn. ID value.
			{
				if(($q = $wpdb->get_row("SELECT `user_id` FROM `".$wpdb->usermeta."` WHERE (`meta_key` = '".$wpdb->prefix."s2member_subscr_id' OR `meta_key` = '".$wpdb->prefix."s2member_subscr_baid' OR `meta_key` = '".$wpdb->prefix."s2member_subscr_cid' OR `meta_key` = '".$wpdb->prefix."s2member_first_payment_txn_id') AND `meta_value` = '".esc_sql($subscr_txn_baid_cid_id)."' LIMIT 1")))
					return $q->user_id;
			}
			return FALSE; // Otherwise, return false.
		}

		/**
		 * Obtains the Email Address for an existing Member, referenced by a Subscr. or Transaction ID.
		 *
		 * A second lookup parameter can be provided as well *(optional)*.
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @param string $subscr_txn_baid_cid_id Either a Paid Subscr. ID, or a Paid Transaction ID.
		 * @param string $os0 Optional. A second lookup parameter, usually the `os0` value for PayPal integrations.
		 *
		 * @return int|bool A User's Email Address on success, else false on failure.
		 */
		public static function get_user_email_with($subscr_txn_baid_cid_id = '', $os0 = '')
		{
			global $wpdb;
			/** @var wpdb $wpdb */

			if($subscr_txn_baid_cid_id && $os0) // This case includes some additional routines that can use the ``$os0`` value.
			{
				if(($q = $wpdb->get_row("SELECT `user_id` FROM `".$wpdb->usermeta."` WHERE (`meta_key` = '".$wpdb->prefix."s2member_subscr_id' OR `meta_key` = '".$wpdb->prefix."s2member_subscr_baid' OR `meta_key` = '".$wpdb->prefix."s2member_subscr_cid' OR `meta_key` = '".$wpdb->prefix."s2member_first_payment_txn_id') AND (`meta_value` = '".esc_sql($subscr_txn_baid_cid_id)."' OR `meta_value` = '".esc_sql($os0)."') LIMIT 1"))
				   || ($q = $wpdb->get_row("SELECT `ID` AS `user_id` FROM `".$wpdb->users."` WHERE `ID` = '".esc_sql($os0)."' LIMIT 1"))
				) if(is_object($user = new WP_User ($q->user_id)) && !empty($user->ID) && ($email = $user->user_email))
					return $email;
			}
			else if($subscr_txn_baid_cid_id) // Otherwise, if all we have is a Subscr./Txn. ID value.
			{
				if(($q = $wpdb->get_row("SELECT `user_id` FROM `".$wpdb->usermeta."` WHERE (`meta_key` = '".$wpdb->prefix."s2member_subscr_id' OR `meta_key` = '".$wpdb->prefix."s2member_subscr_baid' OR `meta_key` = '".$wpdb->prefix."s2member_subscr_cid' OR `meta_key` = '".$wpdb->prefix."s2member_first_payment_txn_id') AND `meta_value` = '".esc_sql($subscr_txn_baid_cid_id)."' LIMIT 1")))
					if(is_object($user = new WP_User ($q->user_id)) && !empty($user->ID) && ($email = $user->user_email))
						return $email;
			}
			return FALSE; // Otherwise, return false.
		}

		/**
		 * Retrieves IPN Signup Vars & validates their Subscription ID.
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @param integer|string $user_id Optional. A numeric WordPress User ID.
		 *
		 * @param string         $subscr_txn_baid_cid_id Optional. Can be used instead of passing in a ``$user_id``.
		 *   If ``$subscr_baid_cid_id`` is passed in, it has to match the one found inside the resulting IPN Signup Vars collected by this routine.
		 *   If neither of these parameters are passed in, the current User is assumed instead, obtained through ``wp_get_current_user()``.
		 *
		 * @return array|bool A User's IPN Signup Vars on success, else false on failure.
		 */
		public static function get_user_ipn_signup_vars($user_id = 0, $subscr_txn_baid_cid_id = '')
		{
			if($user_id || ($subscr_txn_baid_cid_id && ($user_id = c_ws_plugin__s2member_utils_users::get_user_id_with($subscr_txn_baid_cid_id)))
			   || (!$user_id && !$subscr_txn_baid_cid_id && is_object($user = wp_get_current_user()) && !empty($user->ID) && ($user_id = $user->ID))
			)
			{
				$_subscr_baid = get_user_option("s2member_subscr_baid", $user_id);
				$_subscr_cid  = get_user_option("s2member_subscr_cid", $user_id);
				$_subscr_id   = get_user_option("s2member_subscr_id", $user_id);

				if($_subscr_id && (!$subscr_txn_baid_cid_id || $subscr_txn_baid_cid_id === $_subscr_id || $subscr_txn_baid_cid_id === $_subscr_baid || $subscr_txn_baid_cid_id === $_subscr_cid))
					if(is_array($ipn_signup_vars = get_user_option("s2member_ipn_signup_vars", $user_id)))
						if($ipn_signup_vars["subscr_id"] === $_subscr_id)
							return $ipn_signup_vars;
			}
			return FALSE; // Otherwise, return false.
		}

		/**
		 * Retrieves IPN Signup Var & validates their Subscription ID.
		 *
		 * The ``$user_id`` can be passed in directly; or a lookup can be performed with ``$subscr_id``.
		 *
		 * @package s2Member\Utilities
		 * @since 110912
		 *
		 * @param string     $var Required. The requested Signup Var.
		 * @param int|string $user_id Optional. A numeric WordPress User ID.
		 * @param string     $subscr_txn_baid_cid_id Optional. Can be used instead of passing in a ``$user_id``.
		 *   If ``$subscr_id`` is passed in, it has to match the one found inside the resulting IPN Signup Vars collected by this routine.
		 *   If neither of these parameters are passed in, the current User is assumed instead, obtained through ``wp_get_current_user()``.
		 *
		 * @return mixed|bool A User's IPN Signup Var on success, else false on failure.
		 */
		public static function get_user_ipn_signup_var($var = '', $user_id = 0, $subscr_txn_baid_cid_id = '')
		{
			if(!empty($var) && is_array($user_ipn_signup_vars = c_ws_plugin__s2member_utils_users::get_user_ipn_signup_vars($user_id, $subscr_txn_baid_cid_id)))
			{
				if(isset($user_ipn_signup_vars[$var]))
					return $user_ipn_signup_vars[$var];
			}
			return FALSE; // Otherwise, return false.
		}

		/**
		 * Obtains a User's Paid Subscr. ID *(if available)*; otherwise their WP User ID.
		 *
		 * If ``$user`` IS passed in, this function will return data from a specific ``$user``, or fail if not possible.
		 * If ``$user`` is NOT passed in, check the current User/Member.
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @param object $user Optional. A `WP_User` object.
		 *   In order to check the current User, you must call this function with no arguments/parameters.
		 *
		 * @return int|string|bool If possible, the User's Paid Subscr. ID, else their WordPress User ID, else false.
		 */
		public static function get_user_subscr_or_wp_id($user = NULL)
		{
			if((func_num_args() && (!is_object($user) || empty($user->ID)))
			   || (!func_num_args() && (!is_object($user = (is_user_logged_in()) ? wp_get_current_user() : FALSE) || empty($user->ID)))
			) return FALSE; // The ``$user`` was passed in but is NOT an object; or nobody is logged in.

			return ($subscr_id = get_user_option("s2member_subscr_id", $user->ID)) ? $subscr_id : $user->ID;
		}

		/**
		 * Determines whether or not a Username/Email is already in the database.
		 *
		 * Returns the WordPress User ID if they exist.
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @param string $user_login A User's Username.
		 * @param string $user_email A User's Email Address.
		 *
		 * @return int|bool If exists, a WordPress User ID, else false.
		 */
		public static function user_login_email_exists($user_login = '', $user_email = '')
		{
			global $wpdb;
			/** @var wpdb $wpdb */

			if($user_login && $user_email) // Only if we have both of these.
				if(($user_id = $wpdb->get_var("SELECT `ID` FROM `".$wpdb->users."` WHERE `user_login` LIKE '".esc_sql(like_escape($user_login))."' AND `user_email` LIKE '".esc_sql(like_escape($user_email))."' LIMIT 1")))
					return $user_id; // Return the associated WordPress ID.

			return FALSE; // Otherwise, return false.
		}

		/**
		 * Determines whether or not a Username/Email is already in the database for this Blog.
		 *
		 * Returns the WordPress User ID if they exist.
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @param string     $user_login A User's Username.
		 * @param string     $user_email A User's Email Address.
		 * @param int|string $blog_id A numeric WordPress Blog ID.
		 *
		 * @return int|bool If exists *(but not on Blog)*, a WordPress User ID, else false.
		 */
		public static function ms_user_login_email_exists_but_not_on_blog($user_login = '', $user_email = '', $blog_id = 0)
		{
			if($user_login && $user_email) // Only if we have both of these.
				if(is_multisite() && ($user_id = c_ws_plugin__s2member_utils_users::user_login_email_exists($user_login, $user_email)) && !is_user_member_of_blog($user_id, $blog_id))
					return $user_id;

			return FALSE; // Otherwise, return false.
		}

		/**
		 * Determines whether or not a Username/Email is already in the database for this Blog.
		 *
		 * This is an alias for: `c_ws_plugin__s2member_utils_users::ms_user_login_email_exists_but_not_on_blog()`.
		 *
		 * Returns the WordPress User ID if they exist.
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @param string     $user_login A User's Username.
		 * @param string     $user_email A User's Email Address.
		 * @param int|string $blog_id A numeric WordPress Blog ID.
		 *
		 * @return int|bool If exists *(but not on Blog)*, a WordPress User ID, else false.
		 */
		public static function ms_user_login_email_can_join_blog($user_login = '', $user_email = '', $blog_id = 0)
		{
			return c_ws_plugin__s2member_utils_users::ms_user_login_email_exists_but_not_on_blog($user_login, $user_email, $blog_id);
		}

		/**
		 * Retrieves a field value. Also supports Custom Fields.
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @param string     $field_id Required. A unique Custom Registration/Profile Field ID, that you configured with s2Member.
		 *   Or, this could be set to any property that exists on the WP_User object for a particular User;
		 *   ( i.e. `id`, `ID`, `user_login`, `user_email`, `first_name`, `last_name`, `display_name`, `ip`, `IP`,
		 *   `s2member_registration_ip`, `s2member_custom`, `s2member_subscr_id`, `s2member_subscr_or_wp_id`,
		 *   `s2member_subscr_gateway`, `s2member_custom_fields`, `s2member_file_download_access_[log|arc]`,
		 *   `s2member_auto_eot_time`, `s2member_last_payment_time`, `s2member_paid_registration_times`,
		 *   `s2member_access_role`, `s2member_access_level`, `s2member_access_label`,
		 *   `s2member_access_ccaps`, etc, etc. ).
		 * @param int|string $user_id Optional. Defaults to the current User's ID.
		 *
		 * @return mixed The value of the requested field, or false if the field does not exist.
		 */
		public static function get_user_field($field_id = '', $user_id = 0)
		{
			global $wpdb;
			/** @var wpdb $wpdb */

			$current_user = wp_get_current_user(); // Current User's object (used when/if `$user_id` is empty).

			if(is_object($user = ($user_id) ? new WP_User ($user_id) : $current_user) && !empty($user->ID) && ($user_id = $user->ID))
			{
				if(isset ($user->$field_id))
					return $user->$field_id;

				else if(isset ($user->data->$field_id))
					return $user->data->$field_id;

				else if(isset ($user->{$wpdb->prefix.$field_id}))
					return $user->{$wpdb->prefix.$field_id};

				else if(isset ($user->data->{$wpdb->prefix.$field_id}))
					return $user->data->{$wpdb->prefix.$field_id};

				else if(strcasecmp($field_id, "full_name") === 0)
					return trim($user->first_name." ".$user->last_name);

				else if(preg_match("/^(email|user_email)$/i", $field_id))
					return $user->user_email;

				else if(preg_match("/^(login|user_login)$/i", $field_id))
					return $user->user_login;

				else if(strcasecmp($field_id, "s2member_access_role") === 0)
					return c_ws_plugin__s2member_user_access::user_access_role($user);

				else if(strcasecmp($field_id, "s2member_access_level") === 0)
					return c_ws_plugin__s2member_user_access::user_access_level($user);

				else if(strcasecmp($field_id, "s2member_access_label") === 0)
					return c_ws_plugin__s2member_user_access::user_access_label($user);

				else if(strcasecmp($field_id, "s2member_access_ccaps") === 0)
					return c_ws_plugin__s2member_user_access::user_access_ccaps($user);

				else if(strcasecmp($field_id, "ip") === 0 && is_object($current_user) && !empty($current_user->ID) && $current_user->ID === ($user_id = $user->ID))
					return $_SERVER["REMOTE_ADDR"];

				else if(strcasecmp($field_id, "s2member_registration_ip") === 0 || strcasecmp($field_id, "reg_ip") === 0 || strcasecmp($field_id, "ip") === 0)
					return get_user_option("s2member_registration_ip", $user_id);

				else if(strcasecmp($field_id, "s2member_subscr_or_wp_id") === 0)
					return ($subscr_id = get_user_option("s2member_subscr_id", $user_id)) ? $subscr_id : $user_id;

				else if(is_array($fields = get_user_option("s2member_custom_fields", $user_id)))
					if(isset ($fields[preg_replace("/[^a-z0-9]/i", "_", strtolower($field_id))]))
						return $fields[preg_replace("/[^a-z0-9]/i", "_", strtolower($field_id))];
			}
			return FALSE; // Otherwise, return false.
		}
	}
}