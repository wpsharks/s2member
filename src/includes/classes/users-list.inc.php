<?php
// @codingStandardsIgnoreFile
/**
 * Users list.
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
 * @package s2Member\Users_List
 * @since 3.5
 */
if(!defined('WPINC')) // MUST have WordPress.
	exit ("Do not access this file directly.");

if(!class_exists("c_ws_plugin__s2member_users_list"))
{
	/**
	 * Users list.
	 *
	 * @package s2Member\Users_List
	 * @since 3.5
	 */
	class c_ws_plugin__s2member_users_list
	{
		/**
		 * Adds Custom Fields to the admin Profile editing page.
		 *
		 * @package s2Member\Users_List
		 * @since 3.5
		 *
		 * @attaches-to ``add_action("edit_user_profile");``
		 * @attaches-to ``add_action("show_user_profile");``
		 *
		 * @param WP_User $user Expects a `WP_User` object passed in by the Action Hook.
		 */
		public static function users_list_edit_cols($user = NULL)
		{
			c_ws_plugin__s2member_users_list_in::users_list_edit_cols($user);
		}

		/**
		 * Saves Custom Fields after an admin updates Profile.
		 *
		 * @package s2Member\Users_List
		 * @since 3.5
		 *
		 * @attaches-to ``add_action("edit_user_profile_update");``
		 * @attaches-to ``add_action("personal_options_update");``
		 *
		 * @param int|string $user_id Expects a numeric WordPress User ID passed in by the Action Hook.
		 */
		public static function users_list_update_cols($user_id = NULL)
		{
			return c_ws_plugin__s2member_users_list_in::users_list_update_cols($user_id);
		}

		/**
		 * Modifies the search query.
		 *
		 * Affects searches performed in the list of Users.
		 *
		 * @package s2Member\Users_List
		 * @since 3.5
		 *
		 * @attaches-to ``add_action("pre_user_query");``
		 *
		 * @param WP_User_Query $query Expects a `WP_User_Query` object, by reference.
		 */
		public static function users_list_query(&$query = NULL)
		{
			global $wpdb;
			/** @var $wpdb wpdb */

			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action("ws_plugin__s2member_before_users_list_search", get_defined_vars());
			unset($__refs, $__v);

			if(is_admin() && !empty($GLOBALS['pagenow']) && $GLOBALS['pagenow'] === 'users.php')
				if(isset ($query->query_vars) && !is_network_admin()) // NOT in Network admin panels.
					if(is_array($qv = $query->query_vars) && ($s = trim($qv["search"], "* \t\n\r\0\x0B")) && ($s = "%".esc_sql(c_ws_plugin__s2member_utils_strings::like_escape($s))."%"))
					{
						$query->query_fields = "SQL_CALC_FOUND_ROWS DISTINCT(`".$wpdb->users."`.`ID`)";
						$query->query_from   = " FROM `".$wpdb->users."`, `".$wpdb->usermeta."`"; // Include meta table also.
						$query->query_where  = " WHERE `".$wpdb->users."`.`ID` = `".$wpdb->usermeta."`.`user_id`"; // Join w/ meta table.
						$query->query_where .= " AND (".apply_filters("ws_plugin__s2member_before_users_list_search_where_or_before", "", get_defined_vars());
						$query->query_where .= " (`".$wpdb->users."`.`user_login` LIKE '".$s."' OR `".$wpdb->users."`.`user_nicename` LIKE '".$s."' OR `".$wpdb->users."`.`display_name` LIKE '".$s."' OR `".$wpdb->users."`.`user_email` LIKE '".$s."' OR `".$wpdb->users."`.`user_url` LIKE '".$s."')";
						$query->query_where .= " OR ((`".$wpdb->usermeta."`.`meta_key` = 'first_name' OR `".$wpdb->usermeta."`.`meta_key` = 'last_name') AND `".$wpdb->usermeta."`.`meta_value` LIKE '".$s."')";
						$query->query_where .= " OR (`".$wpdb->usermeta."`.`meta_key` = '".$wpdb->base_prefix."s2member_subscr_id' AND `".$wpdb->usermeta."`.`meta_value` LIKE '".$s."')";
						$query->query_where .= " OR (`".$wpdb->usermeta."`.`meta_key` = '".$wpdb->base_prefix."s2member_custom' AND `".$wpdb->usermeta."`.`meta_value` LIKE '".$s."')";
						$query->query_where .= " OR (`".$wpdb->usermeta."`.`meta_key` = '".$wpdb->base_prefix."s2member_custom_fields' AND `".$wpdb->usermeta."`.`meta_value` LIKE '".$s."')";
						if(apply_filters("ws_plugin__s2member_users_list_search_admin_notes", FALSE, get_defined_vars())) // Off by default; this can get very slow on large sites.
							$query->query_where .= " OR (`".$wpdb->usermeta."`.`meta_key` = '".$wpdb->base_prefix."s2member_notes' AND `".$wpdb->usermeta."`.`meta_value` LIKE '".$s."')";
						$query->query_where .= apply_filters("ws_plugin__s2member_before_users_list_search_where_or_after", "", get_defined_vars()).")"; // Leaving room for additional searches here.

						if(is_multisite()) // On a Multisite Network we need to make sure we're searching only users w/ capabilities on this blog.
							$query->query_where .= " AND `".$wpdb->users."`.`ID` IN(SELECT DISTINCT(`user_id`) FROM `".$wpdb->usermeta."` WHERE `meta_key` = '".$wpdb->prefix."capabilities')";

						$query->query_from  = apply_filters("ws_plugin__s2member_before_users_list_search_from", $query->query_from, get_defined_vars());
						$query->query_where = apply_filters("ws_plugin__s2member_before_users_list_search_where", $query->query_where, get_defined_vars());
					}

			if(is_admin() && !is_network_admin() && !empty($GLOBALS['pagenow']) && $GLOBALS['pagenow'] === 'users.php' && !empty($_GET['s2member_view']) && $_GET['s2member_view'] === 'eot' && isset($query->query_vars))
			{
				$current_eot_key = esc_sql($wpdb->prefix.'s2member_auto_eot_time');
				$last_eot_key = esc_sql($wpdb->prefix.'s2member_last_auto_eot_time');
				//260822.1519 EXISTS keeps the native Users query free of duplicate rows when a user has both current and historical EOT metadata, including while s2Member's expanded Users search is active.
				$query->query_where .= " AND (EXISTS (SELECT 1 FROM `".$wpdb->usermeta."` `___s2_eot_current` WHERE `___s2_eot_current`.`user_id` = `".$wpdb->users."`.`ID` AND `___s2_eot_current`.`meta_key` = '".$current_eot_key."' AND CAST(`___s2_eot_current`.`meta_value` AS UNSIGNED) > 0) OR EXISTS (SELECT 1 FROM `".$wpdb->usermeta."` `___s2_eot_last` WHERE `___s2_eot_last`.`user_id` = `".$wpdb->users."`.`ID` AND `___s2_eot_last`.`meta_key` = '".$last_eot_key."' AND CAST(`___s2_eot_last`.`meta_value` AS UNSIGNED) > 0))";

				if(empty($_REQUEST['orderby']) && empty($_REQUEST['s']))
				{
					//260823.1829 Keep EOT Time as the report default only outside searches; s2Member deliberately leaves WordPress's native search sorting and header state untouched.
					$query->query_from .= " LEFT JOIN `".$wpdb->usermeta."` `___s2_eot_order_current` ON (`".$wpdb->users."`.`ID` = `___s2_eot_order_current`.`user_id` AND `___s2_eot_order_current`.`meta_key` = '".$current_eot_key."')";
					$query->query_orderby = "ORDER BY CAST(`___s2_eot_order_current`.`meta_value` AS UNSIGNED) ASC";
				}
			}
			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action("ws_plugin__s2member_after_users_list_search", get_defined_vars());
			unset($__refs, $__v);
		}

		/**
		 * Adds columns to the list of Users.
		 *
		 * @package s2Member\Users_List
		 * @since 3.5
		 *
		 * @attaches-to ``add_filter ("manage_users_columns");``
		 *
		 * @param array $cols Expects an array of columns to be passed through by the Filter.
		 *
		 * @return array Array of columns, merged with columns introduced by this routine.
		 */
		public static function users_list_cols($cols = array())
		{
			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action("ws_plugin__s2member_before_users_list_cols", get_defined_vars());
			unset($__refs, $__v);

			$cols["s2member_registration_time"] = "Registration Date";

			if(apply_filters("ws_plugin__s2member_users_list_cols_display_paid_registration_times", FALSE))
				$cols["s2member_paid_registration_times"] = "Paid Registr. Date";

			$cols["s2member_subscr_id"] = "Paid Subscr. ID";

			if(!is_multisite() || !c_ws_plugin__s2member_utils_conds::is_multisite_farm() || is_main_site())
				$cols["s2member_ccaps"] = "Custom Capabilities";

			$cols["s2member_auto_eot_time"] = "EOT Time";
			//260822.1509 Keep the triggering EOT and its actual processing time separate; both remain optional history columns on the normal Users screen.
			$cols["s2member_last_auto_eot_time"] = "Last EOT";
			$cols["s2member_last_auto_eot_processed_time"] = "EOT Demotion";

			if($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_fields"])
				foreach(json_decode($GLOBALS["WS_PLUGIN__"]["s2member"]["o"]["custom_reg_fields"], TRUE) as $field)
				{
					$field_var      = preg_replace("/[^a-z0-9]/i", "_", strtolower($field["id"]));
					$field_id_class = preg_replace("/_/", "-", $field_var);

					$field_title                               = ucwords(preg_replace("/_/", " ", $field_var));
					$cols["s2member_custom_field_".$field_var] = $field_title;
				}

			$cols["s2member_login_counter"]   = "# Of Logins";
			$cols["s2member_last_login_time"] = "Last Login Time";

			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action("ws_plugin__s2member_during_users_list_cols", get_defined_vars());
			unset($__refs, $__v);

			return apply_filters("ws_plugin__s2member_users_list_cols", $cols, get_defined_vars());
		}

		/**
		 * Re-adds required EOT columns late on s2Member's review views after column-management plugins have filtered the Users table.
		 *
		 * @package s2Member\Users_List
		 * @since 260826.0542
		 *
		 * @attaches-to ``add_filter("manage_users_columns");``
		 *
		 * @param array $cols User list columns after other plugins have filtered them.
		 *
		 * @return array User list columns with the required EOT review columns restored.
		 */
		public static function users_list_required_eot_cols($cols = array())
		{
			if(!is_admin() || empty($GLOBALS['pagenow']) || $GLOBALS['pagenow'] !== 'users.php')
				return $cols;

			$is_eot_view = !empty($_GET['s2member_view']) && $_GET['s2member_view'] === 'eot';
			$is_pending_deletion_view = !empty($_GET['role']) && $_GET['role'] === 's2member_pending_deletion';

			if($is_eot_view || $is_pending_deletion_view)
			{
				//260826.0542 These timestamps are part of the EOT review itself, so restore them if a Users-table customization plugin removed them from its saved column layout.
				$cols['s2member_auto_eot_time'] = 'EOT Time';
				$cols['s2member_last_auto_eot_time'] = 'Last EOT';
				$cols['s2member_last_auto_eot_processed_time'] = 'EOT Demotion';
			}
			return $cols;
		}

		/**
		 * Hides optional s2Member history columns by default on the Users screen.
		 *
		 * @package s2Member\Users_List
		 * @since 260822.1509
		 *
		 * @attaches-to ``add_filter("default_hidden_columns");``
		 *
		 * @param array     $hidden Default hidden column IDs.
		 * @param WP_Screen $screen Current screen object.
		 *
		 * @return array Filtered hidden column IDs.
		 */
		public static function users_list_default_hidden_cols($hidden = array(), $screen = NULL)
		{
			if(is_object($screen) && !empty($screen->id) && $screen->id === 'users')
			{
				//260822.1509 Preserve the normal Users table's compact default while leaving both EOT history columns available through Screen Options.
				$hidden[] = 's2member_last_auto_eot_time';
				$hidden[] = 's2member_last_auto_eot_processed_time';
				$hidden = array_values(array_unique($hidden));
			}
			return $hidden;
		}

		/**
		 * 260823.0108 Exposes End-of-Term columns in the End-of-Term and Pending Deletion review views.
		 *
		 * @package s2Member\Users_List
		 * @since 260822.1519
		 *
		 * @attaches-to ``add_filter("hidden_columns");``
		 *
		 * @param array     $hidden Hidden column IDs.
		 * @param WP_Screen $screen Current screen object.
		 *
		 * @return array Filtered hidden column IDs.
		 */
		public static function users_list_hidden_cols($hidden = array(), $screen = NULL)
		{
			if(is_object($screen) && !empty($screen->id) && $screen->id === 'users')
			{
				$is_eot_view = !empty($_GET['s2member_view']) && $_GET['s2member_view'] === 'eot';
				$is_pending_deletion_view = !empty($_GET['role']) && $_GET['role'] === 's2member_pending_deletion';

				if($is_eot_view || $is_pending_deletion_view)
				{
					//260823.0116 End-of-Term and Pending Deletion are EOT review contexts; force their timestamps visible here while leaving every other Users view entirely to WordPress Screen Options.
					$hidden = array_diff($hidden, array('s2member_auto_eot_time', 's2member_last_auto_eot_time', 's2member_last_auto_eot_processed_time'));
					$hidden = array_values($hidden);
				}
			}
			return $hidden;
		}

		/**
		 * Adds the End-of-Term report to WordPress' native Users views.
		 *
		 * @package s2Member\Users_List
		 * @since 260822.1519
		 *
		 * @attaches-to ``add_filter("views_users");``
		 *
		 * @param array $views Native Users view links.
		 *
		 * @return array Filtered Users view links.
		 */
		public static function users_list_views($views = array())
		{
			global $wpdb;

			if(!is_admin() || is_network_admin() || empty($GLOBALS['pagenow']) || $GLOBALS['pagenow'] !== 'users.php')
				return $views;

			$is_eot_view = !empty($_GET['s2member_view']) && $_GET['s2member_view'] === 'eot';
			$is_pending_deletion_view = !empty($_GET['role']) && $_GET['role'] === 's2member_pending_deletion';
			$current_eot_key = $wpdb->prefix.'s2member_auto_eot_time';
			$last_eot_key = $wpdb->prefix.'s2member_last_auto_eot_time';
			$has_eot_users = $is_eot_view || (bool)$wpdb->get_var($wpdb->prepare("SELECT 1 FROM `".$wpdb->usermeta."` WHERE `meta_key` IN (%s, %s) AND CAST(`meta_value` AS UNSIGNED) > 0 LIMIT 1", $current_eot_key, $last_eot_key));

			if($is_eot_view && isset($views['all']))
				$views['all'] = str_replace(' class="current" aria-current="page"', '', $views['all']);

			//260823.0130 Keep the EOT report out of unused Users screens, but retain it permanently once current or historical EOT data exists; direct report URLs remain self-identifying even when empty.
			if($has_eot_users)
			{
				$url = add_query_arg('s2member_view', 'eot', admin_url('users.php'));
				$views['s2member_eot'] = '<a href="'.esc_url($url).'"'.($is_eot_view ? ' class="current" aria-current="page"' : '').'>End-of-Term</a>';
			}

			$pending_deletion_view = isset($views['s2member_pending_deletion']) ? $views['s2member_pending_deletion'] : '';
			unset($views['s2member_pending_deletion']);
			$delete_behavior = isset($GLOBALS['WS_PLUGIN__']['s2member']['o']['membership_eot_behavior']) && $GLOBALS['WS_PLUGIN__']['s2member']['o']['membership_eot_behavior'] === 'delete';
			$has_pending_deletion_users = $is_pending_deletion_view || (!$delete_behavior && (bool)get_users(array('role' => 's2member_pending_deletion', 'number' => 1, 'fields' => 'ID')));

			//260823.0130 Pending Deletion is useful navigation while "Delete" is configured or review work exists; hide the otherwise-empty role link without unregistering the role or affecting direct access.
			if($delete_behavior || $has_pending_deletion_users)
			{
				if(!$pending_deletion_view)
				{
					$url = add_query_arg('role', 's2member_pending_deletion', admin_url('users.php'));
					$pending_deletion_view = '<a href="'.esc_url($url).'"'.($is_pending_deletion_view ? ' class="current" aria-current="page"' : '').'>Pending Deletion <span class="count">(0)</span></a>';
				}
				$views['s2member_pending_deletion'] = $pending_deletion_view;
			}

			return $views;
		}

		/**
		 * Explains the native End-of-Term Users view.
		 *
		 * @package s2Member\Users_List
		 * @since 260823.0302
		 *
		 * @attaches-to ``add_action("admin_notices");``
		 *
		 * @return null
		 */
		public static function users_list_eot_notice()
		{
			if(!is_admin() || is_network_admin() || empty($GLOBALS['pagenow']) || $GLOBALS['pagenow'] !== 'users.php' || empty($_GET['s2member_view']) || $_GET['s2member_view'] !== 'eot')
				return;

			//260823.0302 The report combines pending and historical End-of-Term records, so explain its scope without turning it into a separate management screen.
			c_ws_plugin__s2member_admin_notices::display_admin_notice('s2Member adds this view for users with a current or previous End-of-Term time.');
		}

		/**
		 * Explains the native Pending Deletion role view.
		 *
		 * @package s2Member\Users_List
		 * @since 260822.1519
		 *
		 * @attaches-to ``add_action("admin_notices");``
		 *
		 * @return null
		 */
		public static function users_list_pending_deletion_notice()
		{
			if(!is_admin() || is_network_admin() || empty($GLOBALS['pagenow']) || $GLOBALS['pagenow'] !== 'users.php' || empty($_GET['role']) || $_GET['role'] !== 's2member_pending_deletion')
				return;

			//260822.1519 Pending Deletion is deliberately a native role review queue; explain why these accounts survive instead of introducing a separate management screen.
			c_ws_plugin__s2member_admin_notices::display_admin_notice('s2Member adds this review view for accounts preserved in <strong>Pending Deletion</strong> after their membership access was removed at End-of-Term. Use WordPress\'s normal bulk Delete action when you are ready to delete them.');
		}

		/**
		 * Displays column data in the row of details.
		 *
		 * @package s2Member\Users_List
		 * @since 3.5
		 *
		 * @attaches-to ``add_filter ("manage_users_custom_column");``
		 *
		 * @param string     $val A value for this column, passed through by the Filter.
		 * @param string     $col The name of the column for which we might need to supply data for.
		 * @param int|string $user_id Expects a WordPress User ID, passed through by the Filter.
		 *
		 * @return string A column value introduced by this routine, or existing value, or, if empty, a dash.
		 */
		public static function users_list_display_cols($val = '', $col = '', $user_id = '')
		{
			static $user, $last_user_id; // Used internally for optimization.
			static $fields, $last_fields_id; // Used for optimization.

			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action("ws_plugin__s2member_before_users_list_display_cols", get_defined_vars());
			unset($__refs, $__v);

			$user = (is_object($user) && $user_id === $last_user_id) ? $user : new WP_User ($user_id);

			if($col === "s2member_registration_time")
				$val = (($time = strtotime(get_date_from_gmt($user->user_registered)))) ? esc_html(date("D M jS, Y", $time)).'<br /><small>@ precisely '.esc_html(date("g:i a", $time)).'</small>' : "—";

			else if($col === "s2member_paid_registration_times")
			{
				$val = ""; // Initialize $val before we begin.
				if(is_array($v = get_user_option("s2member_paid_registration_times", $user_id)))
					foreach($v as $level => $time) // Go through each Paid Registration Time.
					{
						$time = strtotime(get_date_from_gmt(date("Y-m-d H:i:s", $time)));

						if($level === "level") // First Payment Time, regardless of Level.
							$val .= (($val) ? "<br />" : "").'<span title="'.esc_attr(date("D M jS, Y", $time)).' @ precisely '.esc_attr(date("g:i a", $time)).'">'.esc_html(date("D M jS, Y", $time)).'</span>';
						else if(preg_match("/^level([0-9]+)$/i", $level) && ($level = preg_replace("/^level/", "", $level)))
							$val .= (($val) ? "<br />" : "").'<small><em>@Level '.esc_html($level).': <span title="'.esc_attr(date("D M jS, Y", $time)).' @ precisely '.esc_attr(date("g:i a", $time)).'">'.esc_html(date("D M jS, Y", $time)).'</span></em></small>';
					}
			}
			else if($col === "s2member_subscr_id")
				$val = ($v = get_user_option("s2member_subscr_id", $user_id)) ? esc_html($v) : "—";

			else if($col === "s2member_ccaps") // Custom Capabilities.
			{
				foreach($user->allcaps as $cap => $cap_enabled)
					if(preg_match("/^access_s2member_ccap_/", $cap))
						$ccaps[] = preg_replace("/^access_s2member_ccap_/", "", $cap);

				$val = (!empty($ccaps)) ? implode("<br />", $ccaps) : "—";
			}
			else if($col === "s2member_auto_eot_time")
				$val = ($v = get_user_option("s2member_auto_eot_time", $user_id)) ? date("D M jS, Y", (int)$v)."<br /><small>@ precisely ".date("g:i a", (int)$v)."</small>" : "—";

			else if($col === "s2member_last_auto_eot_time" || $col === "s2member_last_auto_eot_processed_time")
			{
				//260822.1509 Match the existing EOT Time presentation so current, triggering, and processed timestamps compare directly in the same Users table.
				$val = ($v = get_user_option($col, $user_id)) ? date("D M jS, Y", (int)$v)."<br /><small>@ precisely ".date("g:i a", (int)$v)."</small>" : "—";
			}

			else if(preg_match("/^s2member_custom_field_/", $col))
			{
				if(!$last_fields_id || $last_fields_id !== $user_id)
					$fields = get_user_option("s2member_custom_fields", $user_id);

				$field_var = preg_replace("/^s2member_custom_field_/", "", $col);

				if(isset ($fields[$field_var]) && is_string($fields[$field_var]) && preg_match("/^http(s?)\:/i", $fields[$field_var]))
					$val = '<a href="'.esc_attr($fields[$field_var]).'" target="_blank">'.esc_html(substr($fields[$field_var], strpos($fields[$field_var], ":") + 3, 25)."...").'</a>';

				else if(isset ($fields[$field_var]) && is_array($fields[$field_var]) && !empty($fields[$field_var]))
					$val = preg_replace("/-\|br\|-/", "<br />", esc_html(implode("-|br|-", $fields[$field_var])));

				else if(isset ($fields[$field_var]) && is_string($fields[$field_var]) && strlen($fields[$field_var]))
					$val = esc_html($fields[$field_var]);

				$last_fields_id = $user_id; // Record this.
			}
			else if($col === "s2member_login_counter")
				$val = ($v = get_user_option("s2member_login_counter", $user_id)) ? esc_html($v) : "—";

			else if($col === "s2member_last_login_time")
			{
				if(($time = get_user_option("s2member_last_login_time", $user_id)))
				{
					$time = strtotime(get_date_from_gmt(date("Y-m-d H:i:s", $time)));
					$val  = esc_html(date("D M jS, Y", $time)).'<br /><small>@ precisely '.esc_html(date("g:i a", $time)).'</small>';
				}
				else $val = "—"; // Not applicable (we've never recorded them logging into the site).
			}
			$last_user_id = $user_id; // Record this for internal optimizations.

			return apply_filters("ws_plugin__s2member_users_list_display_cols", ((strlen((string) $val)) ? $val : "—"), get_defined_vars());
		}

		/**
		 * Tells WordPress certain fields s2Member adds are sortable
		 *
		 * @package s2Member\Users_List
		 * @since 140518
		 *
		 * @attaches-to ``add_filter ("manage_users_sortable_columns");``
		 *
		 * @param array $columns An array of sortable user list columns.
		 *
		 * @return array The input array after having been filtered here.
		 */
		public static function users_list_add_sortable($columns = array())
		{
			if(!empty($_REQUEST['s']))
				return $columns;

			$is_eot_view = !empty($_GET['s2member_view']) && $_GET['s2member_view'] === 'eot';
			if($is_eot_view)
			{
				//260823.0716 WordPress normally marks Username as the initially sorted Users column; EOT Time is this report's default sort, so make that native sort state visible.
				foreach($columns as $_column_key => $_column_data)
					if(is_array($_column_data) && isset($_column_data[4]))
						$columns[$_column_key][4] = false;
			}

			$columns['s2member_registration_time']               = 's2member_registration_time';
			$columns['s2member_subscr_id']                       = 's2member_subscr_id';
			$columns['s2member_auto_eot_time']                   = $is_eot_view ? array('s2member_auto_eot_time', false, 'EOT Time', 'Table ordered by End-of-Term time.', 'asc') : 's2member_auto_eot_time';
			$columns['s2member_last_auto_eot_time']              = 's2member_last_auto_eot_time';
			$columns['s2member_last_auto_eot_processed_time']    = 's2member_last_auto_eot_processed_time';
			$columns['s2member_login_counter']                   = 's2member_login_counter';
			$columns['s2member_last_login_time']                 = 's2member_last_login_time';

			return $columns;
		}

		/**
		 * Alters WP_Query object to make custom columns sortable
		 *
		 * @package s2Member\Users_List
		 * @since 140518
		 *
		 * @attaches-to ``add_filter ("pre_user_query");``
		 *
		 * @param WP_User_Query $query `WP_Query` Object passed from WordPress
		 */
		public static function users_list_make_sortable($query)
		{
			if(!is_admin()
			   || empty($GLOBALS['pagenow']) || $GLOBALS['pagenow'] !== 'users.php'
			   || !isset ($query->query_vars) || !empty($_REQUEST['s'])
			) return;

			global $wpdb;
			/** @var $wpdb wpdb */
			$vars = $query->query_vars;

			switch($vars['orderby'])
			{
				// This isn't a usermeta value, so we don't need to `LEFT JOIN` here.
				case 's2member_registration_time':
					$query->query_orderby = "ORDER BY CAST(`user_registered` AS UNSIGNED) ".$vars['order'];
					break;

				// s2Member Subscription ID can contain non-integer characters. We don't `CAST` this value as `UNSIGNED`
				case 's2member_subscr_id':
					$query->query_from .= " LEFT JOIN `".$wpdb->usermeta."` `___m` ON (`".$wpdb->users."`.`ID` = `___m`.`user_id` AND `___m`.`meta_key` = '".esc_sql($wpdb->prefix.$vars['orderby'])."')";
					$query->query_orderby = "ORDER BY `___m`.`meta_value` ".$vars['order'];
					break;

				case 's2member_auto_eot_time':
				case 's2member_last_auto_eot_time':
				case 's2member_last_auto_eot_processed_time':
				case 's2member_login_counter':
				case 's2member_last_login_time':
					$query->query_from .= " LEFT JOIN `".$wpdb->usermeta."` `___m` ON (`".$wpdb->users."`.`ID` = `___m`.`user_id` AND `___m`.`meta_key` = '".esc_sql($wpdb->prefix.$vars['orderby'])."')";
					$query->query_orderby = "ORDER BY CAST(`___m`.`meta_value` AS UNSIGNED) ".$vars['order'];
					break;
			}
		}
	}
}
