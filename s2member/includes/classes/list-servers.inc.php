<?php
/**
 * List Server integrations.
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
 * @package s2Member\List_Servers
 * @since 3.5
 */
if(realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME']))
	exit('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_list_servers'))
{
	/**
	 * List Server integrations.
	 *
	 * @package s2Member\List_Servers
	 * @since 3.5
	 */
	class c_ws_plugin__s2member_list_servers
	{
		/**
		 * Processes list server integrations.
		 *
		 * @since 3.5
		 * @package s2Member\List_Servers
		 *
		 * @param string     $role A WP role.
		 * @param int|string $level A numeric level.
		 * @param string     $login Username for the user.
		 * @param string     $pass Plain text password for the User.
		 * @param string     $email Email address for the user.
		 * @param string     $fname First name for the user.
		 * @param string     $lname Last name for the user.
		 * @param string     $ip IP address for the user.
		 * @param bool       $opt_in Defaults to `FALSE`; must be set to `TRUE`.
		 * @param bool       $double_opt_in Defaults to `TRUE`. Use at your own risk.
		 * @param int|string $user_id A WordPress User ID, numeric string or integer.
		 *
		 * @return bool True if at least one list server is processed successfully.
		 */
		public static function process_list_servers($role = '', $level = '',
		                                            $login = '', $pass = '', $email = '', $fname = '', $lname = '', $ip = '',
		                                            $opt_in = FALSE, $double_opt_in = TRUE,
		                                            $user_id = 0)
		{
			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_before_process_list_servers', get_defined_vars());
			unset($__refs, $__v); // Allows vars to be modified by reference.

			if(c_ws_plugin__s2member_list_servers::list_servers_integrated())
			{
				$args                = get_defined_vars(); // Function args.
				$mailchimp_success   = c_ws_plugin__s2member_mailchimp::subscribe($args);
				$getresponse_success = c_ws_plugin__s2member_getresponse::subscribe($args);
				$aweber_success      = c_ws_plugin__s2member_aweber::subscribe($args);
				$success             = $mailchimp_success || $getresponse_success || $aweber_success;

				if($user_id) update_user_option($user_id, 's2member_opt_in', '1');

				do_action('ws_plugin__s2member_during_process_list_servers', get_defined_vars());
			}
			do_action('ws_plugin__s2member_after_process_list_servers', get_defined_vars());

			return apply_filters('ws_plugin__s2member_process_list_servers', !empty($success), get_defined_vars());
		}

		/**
		 * Process list servers against current user.
		 *
		 * @since 141004
		 * @package s2Member\List_Servers
		 *
		 * See {@link process_list_servers()} for further details.
		 *
		 * @param bool $opt_in Defaults to `FALSE`; must be set to `TRUE`.
		 * @param bool $double_opt_in Defaults to `TRUE`. Use at your own risk.
		 * @param bool $clean_user_cache Defaults to `TRUE`; i.e. we start from a fresh copy of the current user.
		 *
		 * @return bool True if at least one list server is processed successfully.
		 */
		public static function process_list_servers_against_current_user($opt_in = FALSE, $double_opt_in = TRUE, $clean_user_cache = TRUE)
		{
			if($clean_user_cache) // Start from a fresh user object here?
			{
				clean_user_cache(get_current_user_id());
				wp_cache_delete(get_current_user_id(), 'user_meta');
				$user = new WP_User(get_current_user_id());
			}
			else $user = wp_get_current_user();

			return self::process_list_servers(
				($role = c_ws_plugin__s2member_user_access::user_access_role($user)),
				($level = c_ws_plugin__s2member_user_access::user_access_level($user)),
				($login = $user->user_login),
				($pass = $user->user_pass),
				($email = $user->user_email),
				($fname = $user->first_name),
				($lname = $user->last_name),
				($ip = @$_SERVER['REMOTE_ADDR']),
				($opt_in = $opt_in),
				($double_opt_in = $double_opt_in),
				($user_id = $user->ID)
			);
		}

		/**
		 * Processes list server removals.
		 *
		 * @since 3.5
		 * @package s2Member\List_Servers
		 *
		 * @param string     $role A WP role.
		 * @param int|string $level A numeric level.
		 * @param string     $login Username for the user.
		 * @param string     $pass Plain text password for the User.
		 * @param string     $email Email address for the user.
		 * @param string     $fname First name for the user.
		 * @param string     $lname Last name for the user.
		 * @param string     $ip IP address for the user.
		 * @param bool       $opt_out Defaults to `FALSE`; must be set to `TRUE`.
		 * @param int|string $user_id A WordPress User ID, numeric string or integer.
		 *
		 * @return bool True if at least one list server removal is processed successfully.
		 */
		public static function process_list_server_removals($role = '', $level = '',
		                                                    $login = '', $pass = '', $email = '', $fname = '', $lname = '', $ip = '',
		                                                    $opt_out = FALSE,
		                                                    $user_id = 0)
		{
			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_before_process_list_server_removals', get_defined_vars());
			unset($__refs, $__v); // Allows vars to be modified by reference.

			if(c_ws_plugin__s2member_list_servers::list_servers_integrated())
			{
				$args                = get_defined_vars(); // Function args.
				$mailchimp_success   = c_ws_plugin__s2member_mailchimp::unsubscribe($args);
				$getresponse_success = c_ws_plugin__s2member_getresponse::unsubscribe($args);
				$aweber_success      = c_ws_plugin__s2member_aweber::unsubscribe($args);
				$success             = $mailchimp_success || $getresponse_success || $aweber_success;

				do_action('ws_plugin__s2member_during_process_list_server_removals', get_defined_vars());

				if($user_id) update_user_option($user_id, 's2member_opt_in', '0');
			}
			do_action('ws_plugin__s2member_after_process_list_server_removals', get_defined_vars());

			return apply_filters('ws_plugin__s2member_process_list_server_removals', !empty($success), get_defined_vars());
		}

		/**
		 * Process list server removals against current user.
		 *
		 * See {@link process_list_server_removals()} for further details.
		 *
		 * @since 141004
		 * @package s2Member\List_Servers
		 *
		 * @param bool $opt_out Defaults to `FALSE`; must be set to `TRUE`.
		 * @param bool $clean_user_cache Defaults to `TRUE`; i.e. we start from a fresh copy of the current user.
		 *
		 * @return bool True if at least one list server removal is processed successfully.
		 */
		public static function process_list_server_removals_against_current_user($opt_out = FALSE, $clean_user_cache = TRUE)
		{
			if($clean_user_cache) // Start from a fresh user object here?
			{
				clean_user_cache(get_current_user_id());
				wp_cache_delete(get_current_user_id(), 'user_meta');
				$user = new WP_User(get_current_user_id());
			}
			else $user = wp_get_current_user();

			return self::process_list_server_removals(
				($role = c_ws_plugin__s2member_user_access::user_access_role($user)),
				($level = c_ws_plugin__s2member_user_access::user_access_level($user)),
				($login = $user->user_login),
				($pass = $user->user_pass),
				($email = $user->user_email),
				($fname = $user->first_name),
				($lname = $user->last_name),
				($ip = @$_SERVER['REMOTE_ADDR']),
				($opt_out = $opt_out),
				($user_id = $user->ID)
			);
		}

		/**
		 * Listens to Collective EOT/MOD Events processed internally by s2Member.
		 *
		 * This is only applicable when ``['custom_reg_auto_opt_outs']`` contains related Event(s).
		 *
		 * @package s2Member\List_Servers
		 * @since 3.5
		 *
		 * @attaches-to ``add_action('ws_plugin__s2member_during_collective_mods');``
		 * @attaches-to ``add_action('ws_plugin__s2member_during_collective_eots');``
		 *
		 * @param int|string $user_id Required. A WordPress User ID, numeric string or integer.
		 * @param array      $vars Required. An array of defined variables passed by the calling Hook.
		 * @param string     $event Required. A specific event that triggered this call from the Action Hook.
		 * @param string     $event_spec Required. A specific event specification *(a broader classification)*.
		 * @param string     $mod_new_role Required if ``$event_spec === 'modification'`` (but can be empty). Role the User is being modified to.
		 * @param string     $mod_new_user Optional. If ``$event_spec === 'modification'``, the new User object with current details.
		 * @param string     $mod_old_user Optional. If ``$event_spec === 'modification'``, the old/previous User obj with old details.
		 *
		 * @TODO Geez Louise! Refactor, refactor!!!!!
		 */
		public static function auto_process_list_server_removals($user_id, $vars, $event, $event_spec, $mod_new_role = NULL, $mod_new_user = NULL, $mod_old_user = NULL)
		{
			global $current_site, $current_blog; // For Multisite support.
			static $auto_processed = array( /* Process ONE time for each User. */);

			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_before_auto_process_list_server_removals', get_defined_vars());
			unset($__refs, $__v);

			$custom_reg_auto_op_outs = c_ws_plugin__s2member_utils_strings::wrap_deep($GLOBALS['WS_PLUGIN__']['s2member']['o']['custom_reg_auto_opt_outs'], '/^', '$/i');

			if(c_ws_plugin__s2member_list_servers::list_servers_integrated() && $user_id && is_numeric($user_id) && !in_array($user_id, $auto_processed) && is_array($vars) && is_string($event = (string)$event) && is_string($event_spec = (string)$event_spec) && (c_ws_plugin__s2member_utils_arrays::in_regex_array($event, $custom_reg_auto_op_outs) || c_ws_plugin__s2member_utils_arrays::in_regex_array($event_spec, $custom_reg_auto_op_outs)) && is_object($user = $_user = new WP_User ($user_id)) && !empty($user->ID))
			{
				$mod_new_role = ($event_spec === 'modification' && $mod_new_role && is_string($mod_new_role)) ? $mod_new_role : FALSE; // Might be empty(i.e. they now have NO Role).
				$mod_new_user = ($event_spec === 'modification' && $mod_new_user && is_object($mod_new_user) && !empty($mod_new_user->ID) && $mod_new_user->ID === $_user->ID) ? $mod_new_user : FALSE;
				$mod_old_user = ($event_spec === 'modification' && $mod_old_user && is_object($mod_old_user) && !empty($mod_old_user->ID) && $mod_old_user->ID === $_user->ID) ? $mod_old_user : FALSE;

				$user = ($event_spec === 'modification' && $mod_old_user) ? $mod_old_user : $_user; // Now, should we switch over to the old/previous User object ``$mod_old_user`` here? Or, should we use the one pulled by this routine with the User's ID?

				if(($event_spec !== 'modification' || ($event_spec === 'modification' && (string)$mod_new_role !== c_ws_plugin__s2member_user_access::user_access_role($user) && strtotime($user->user_registered) < strtotime('-10 seconds') && ($event !== 'user-role-change' || ($event === 'user-role-change' && !empty($vars['_p']['ws_plugin__s2member_custom_reg_auto_opt_out_transitions']))))) && ($auto_processed[$user->ID] = TRUE))
				{
					$removed = c_ws_plugin__s2member_list_servers::process_list_server_removals(c_ws_plugin__s2member_user_access::user_access_role($user), c_ws_plugin__s2member_user_access::user_access_level($user), $user->user_login, FALSE, $user->user_email, $user->first_name, $user->last_name, FALSE, TRUE, $user->ID);

					if($event_spec === 'modification' && $mod_new_role && ($GLOBALS['WS_PLUGIN__']['s2member']['o']['custom_reg_auto_opt_out_transitions'] === '2' || ($GLOBALS['WS_PLUGIN__']['s2member']['o']['custom_reg_auto_opt_out_transitions'] === '1' && $removed)))
					{
						$user = ($event_spec === 'modification' && $mod_new_user) ? $mod_new_user : $_user; // Now, should we switch over to a new/current User object ``$mod_new_user`` here? (which may contain newly updated details). Or, should we simply use the User object pulled by this routine with the User's ID?

						$transitioned = c_ws_plugin__s2member_list_servers::process_list_servers($mod_new_role, c_ws_plugin__s2member_user_access::user_access_role_to_level($mod_new_role), $user->user_login, FALSE, $user->user_email, $user->first_name, $user->last_name, FALSE, TRUE, (($removed) ? FALSE : TRUE), $user->ID);

						foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
						do_action('ws_plugin__s2member_during_auto_process_list_server_removal_transitions', get_defined_vars());
						unset($__refs, $__v);
					}

					foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
					do_action('ws_plugin__s2member_during_auto_process_list_server_removals', get_defined_vars());
					unset($__refs, $__v);
				}
			}
			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_after_auto_process_list_server_removals', get_defined_vars());
			unset($__refs, $__v);
		}

		/**
		 * Determines whether or not any List Servers have been integrated.
		 *
		 * @package s2Member\List_Servers
		 * @since 3.5
		 *
		 * @return bool True if List Servers have been integrated, else false.
		 */
		public static function list_servers_integrated()
		{
			do_action('ws_plugin__s2member_before_list_servers_integrated', get_defined_vars());

			for($n = 0; $n <= $GLOBALS['WS_PLUGIN__']['s2member']['c']['levels']; $n++ /* Go through each Level; looking for a configured list. */)
				if(!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['level'.$n.'_mailchimp_list_ids']) || !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['level'.$n.'_getresponse_list_ids']) || !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['level'.$n.'_aweber_list_ids']))
					return apply_filters('ws_plugin__s2member_list_servers_integrated', TRUE, get_defined_vars());

			return apply_filters('ws_plugin__s2member_list_servers_integrated', FALSE, get_defined_vars());
		}
	}
}