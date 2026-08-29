<?php
// @codingStandardsIgnoreFile
/**
 * s2Member's Auto-EOT System *(EOT = End Of Term)*.
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
 * @package s2Member\Auto_EOT_System
 * @since 3.5
 */
if(!defined('WPINC')) // MUST have WordPress.
	exit ('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_auto_eots'))
{
	/**
	 * s2Member's Auto-EOT System *(EOT = End Of Term)*.
	 *
	 * @package s2Member\Auto_EOT_System
	 * @since 3.5
	 */
	class c_ws_plugin__s2member_auto_eots
	{
		/**
		 * Adds a scheduled task for s2Member's Auto-EOT System.
		 *
		 * @package s2Member\Auto_EOT_System
		 * @since 3.5
		 *
		 * @return bool True if able to add Auto-EOT System schedule, else false.
		 */
		public static function add_auto_eot_system()
		{
			do_action('ws_plugin__s2member_before_add_auto_eot_system', get_defined_vars());

			if(!c_ws_plugin__s2member_auto_eots::delete_auto_eot_system())
			{
				return apply_filters('ws_plugin__s2member_add_auto_eot_system', FALSE, get_defined_vars());
			}
			else if(function_exists('wp_cron') /* Otherwise, we can schedule? */)
			{
				//260823.1829 Verify the scheduled event itself because older WordPress versions supported by s2Member return no success value from wp_schedule_event().
				wp_schedule_event(time(), 'every10m', 'ws_plugin__s2member_auto_eot_system__schedule');
				$scheduled = (bool)wp_next_scheduled('ws_plugin__s2member_auto_eot_system__schedule') && wp_get_schedule('ws_plugin__s2member_auto_eot_system__schedule') === 'every10m';

				return apply_filters('ws_plugin__s2member_add_auto_eot_system', $scheduled, get_defined_vars());
			}
			else // Otherwise, it would appear that WP-Cron is not available.
			{
				return apply_filters('ws_plugin__s2member_add_auto_eot_system', FALSE, get_defined_vars());
			}
		}

		/**
		 * Recreates a missing recurring Auto-EOT event while preserving any queued catch-up pass.
		 *
		 * @package s2Member\Auto_EOT_System
		 * @since 260819.0613
		 *
		 * @return bool True when no repair is needed or the recurring event exists after repair; otherwise false.
		 */
		public static function ensure_auto_eot_system()
		{
			if(empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['auto_eot_system_enabled']) || (string)$GLOBALS['WS_PLUGIN__']['s2member']['o']['auto_eot_system_enabled'] !== '1')
				return TRUE;

			if(!function_exists('wp_cron'))
				return FALSE;

			if(wp_next_scheduled('ws_plugin__s2member_auto_eot_system__schedule'))
				return TRUE;

			//260820.0149 Preserve a pending catch-up pass because add_auto_eot_system() clears all Auto-EOT schedules before rebuilding the recurring one.
			$continuation_at = wp_next_scheduled('ws_plugin__s2member_auto_eot_system__continuation');
			$scheduled = c_ws_plugin__s2member_auto_eots::add_auto_eot_system();
			if($scheduled && $continuation_at && !wp_next_scheduled('ws_plugin__s2member_auto_eot_system__continuation'))
				wp_schedule_single_event(max(time() + 1, (int)$continuation_at), 'ws_plugin__s2member_auto_eot_system__continuation');

			//260820.0149 Retain self-heal results for diagnostics, but throttle repeated failure writes on sites where WordPress rejects scheduling every request.
			$state = get_option('ws_plugin__s2member_auto_eot_state');
			$state = is_array($state) ? $state : array();
			if($scheduled)
			{
				$state['last_schedule_repaired_at'] = time();
				$state['schedule_failure_count'] = 0;
				update_option('ws_plugin__s2member_auto_eot_state', $state, FALSE);
			}
			else if(empty($state['last_schedule_failure_at']) || time() - (int)$state['last_schedule_failure_at'] >= 300)
			{
				$state['last_schedule_failure_at'] = time();
				$state['schedule_failure_count'] = !empty($state['schedule_failure_count']) ? (int)$state['schedule_failure_count'] + 1 : 1;
				update_option('ws_plugin__s2member_auto_eot_state', $state, FALSE);
			}
			delete_transient('ws_plugin__s2member_auto_eot_health');

			return $scheduled;
		}

		/**
		 * Deletes all scheduled tasks for s2Member's Auto-EOT System.
		 *
		 * @package s2Member\Auto_EOT_System
		 * @since 3.5
		 *
		 * @return bool True if able to delete Auto-EOT System schedule, else false.
		 */
		public static function delete_auto_eot_system()
		{
			do_action('ws_plugin__s2member_before_delete_auto_eot_system', get_defined_vars());

			if(function_exists('wp_cron') /* Is `wp_cron()` even available? */)
			{
				wp_clear_scheduled_hook('ws_plugin__s2member_auto_eot_system__schedule' /* Since v3.0.3. */);
				wp_clear_scheduled_hook('ws_plugin__s2member_auto_eot_system__continuation'); //260820.0056 Remove any pending catch-up pass when Auto-EOT scheduling is deleted.
				delete_transient('ws_plugin__s2member_auto_eot_health'); //260820.0149 Invalidate schedule-dependent health data.

				return apply_filters('ws_plugin__s2member_delete_auto_eot_system', TRUE, get_defined_vars());
			}
			else // Otherwise, it would appear that WP-Cron is not available.
			{
				return apply_filters('ws_plugin__s2member_delete_auto_eot_system', FALSE, get_defined_vars());
			}
		}

		/**
		 * Determines a safe wall-clock budget for one Auto-EOT pass.
		 *
		 * Automatic mode leaves more headroom in shared WP-Cron than in a dedicated external-cron request.
		 * Custom mode is still bounded below PHP's finite execution limit; the developer filter remains final.
		 *
		 * @package s2Member\Auto_EOT_System
		 * @since 260820.0056
		 *
		 * @param bool|null $is_external_cron Optional explicit execution context; null auto-detects the external-cron endpoint.
		 *
		 * @return float Runtime budget in seconds.
		 */
		public static function auto_eot_system_runtime_budget($is_external_cron = NULL)
		{
			$php_max_execution_time = (int)ini_get('max_execution_time');
			if($is_external_cron === NULL)
				$is_external_cron = !empty($_GET['s2member_auto_eot_system_via_cron']);
			else
				$is_external_cron = (bool)$is_external_cron;

			$automatic_budget = $php_max_execution_time > 0 ? floor($php_max_execution_time * ($is_external_cron ? 0.80 : 0.60)) : ($is_external_cron ? 60 : 30);

			//260820.0306 Custom runtime may raise/lower the automatic target, but keep 10% PHP headroom unless a developer deliberately overrides the final filter.
			if((string)$GLOBALS['WS_PLUGIN__']['s2member']['o']['auto_eot_system_runtime_mode'] === 'custom')
			{
				$runtime_budget = max(1, (float)$GLOBALS['WS_PLUGIN__']['s2member']['o']['auto_eot_system_runtime_custom']);
				if($php_max_execution_time > 0)
					$runtime_budget = min($runtime_budget, max(1, floor($php_max_execution_time * 0.90)));
			}
			else
				$runtime_budget = max(1, $automatic_budget);

			$runtime_budget = (float)apply_filters('ws_plugin__s2member_auto_eot_system_runtime', $runtime_budget, get_defined_vars());

			return max(1, $runtime_budget);
		}

		/**
		 * Describes legacy Auto-EOT per-process filters for diagnostics.
		 *
		 * This never executes the legacy filter. It only reports hooked callbacks and any effective cap
		 * recorded by the last Auto-EOT run, so an inherited customization is visible to site owners.
		 *
		 * @package s2Member\Auto_EOT_System
		 * @since 260820.0306
		 *
		 * @return array Legacy filter information.
		 */
		public static function auto_eot_system_legacy_cap_info()
		{
			global $wp_filter;

			$hook = 'ws_plugin__s2member_auto_eot_system_per_process';
			$state = get_option('ws_plugin__s2member_auto_eot_state');
			$state = is_array($state) ? $state : array();
			$info = array(
				'detected'             => has_filter($hook) !== FALSE,
				'sources'              => array(),
				'last_hard_cap'        => isset($state['last_hard_cap']) && $state['last_hard_cap'] !== NULL ? (int)$state['last_hard_cap'] : NULL,
				'last_hard_cap_source' => !empty($state['last_hard_cap_source']) ? (string)$state['last_hard_cap_source'] : '',
				'estimated_additional' => !empty($state['legacy_cap_estimated_additional']) ? (int)$state['legacy_cap_estimated_additional'] : 0,
				'last_stop_reason'     => !empty($state['last_stop_reason']) ? (string)$state['last_stop_reason'] : '',
			);

			if(!$info['detected'] || empty($wp_filter[$hook]) || !is_object($wp_filter[$hook]) || empty($wp_filter[$hook]->callbacks))
				return $info;

			//260820.0306 Reflection is best-effort diagnostics only; unusual callback forms still count as detected even when their source cannot be identified.
			foreach($wp_filter[$hook]->callbacks as $priority => $callbacks)
			{
				foreach((array)$callbacks as $callback_data)
				{
					if(empty($callback_data['function']))
						continue;

					$callback = $callback_data['function'];
					$label = '';
					$reflection = NULL;

					try
					{
						if(is_string($callback))
						{
							$label = $callback;
							if(strpos($callback, '::') !== FALSE)
							{
								$_callback_parts = explode('::', $callback, 2);
								$reflection = new ReflectionMethod($_callback_parts[0], $_callback_parts[1]);
								unset($_callback_parts);
							}
							else
								$reflection = new ReflectionFunction($callback);
						}
						else if(is_array($callback) && count($callback) === 2)
						{
							$label = (is_object($callback[0]) ? get_class($callback[0]) : (string)$callback[0]).'::'.$callback[1];
							$reflection = new ReflectionMethod($callback[0], $callback[1]);
						}
						else if($callback instanceof Closure)
						{
							$label = 'Closure';
							$reflection = new ReflectionFunction($callback);
						}
						else if(is_object($callback) && is_callable($callback))
						{
							$label = get_class($callback).'::__invoke';
							$reflection = new ReflectionMethod($callback, '__invoke');
						}
					}
					catch(ReflectionException $e)
					{
						$reflection = NULL;
					}

					$file = ($reflection && $reflection->getFileName()) ? wp_normalize_path($reflection->getFileName()) : '';
					if($file && strpos($file, wp_normalize_path(ABSPATH)) === 0)
						$file = ltrim(substr($file, strlen(wp_normalize_path(ABSPATH))), '/');

					$info['sources'][] = array(
						'priority' => (int)$priority,
						'callback' => $label,
						'file'     => $file,
						'line'     => ($reflection && $reflection->getStartLine()) ? (int)$reflection->getStartLine() : 0,
					);
				}
			}
			return $info;
		}

		/**
		 * Determines whether End-of-Term processing may irreversibly delete a WordPress user.
		 *
		 * The safe default is false: the stored `delete` behavior moves the account to Pending Deletion instead.
		 * Developers that intentionally require automatic account deletion can opt in through this filter. Keeping
		 * the decision centralized ensures scheduled Auto-EOT and immediate gateway-triggered EOTs use the same policy.
		 *
		 * @package s2Member\Auto_EOT_System
		 * @since 260822.0520
		 *
		 * @param int    $user_id WordPress user ID being processed.
		 * @param string $eot_del_type Prospective irreversible-deletion event type.
		 *
		 * @return bool True only when a developer explicitly allows irreversible End-of-Term deletion.
		 */
		public static function allow_eot_user_deletion($user_id = 0, $eot_del_type = '')
		{
			return (bool)apply_filters('ws_plugin__s2member_allow_eot_user_deletion', FALSE, get_defined_vars());
		}

		/**
		 * Records when an End-of-Term action was processed and appends one compact history note.
		 *
		 * @package s2Member\Auto_EOT_System
		 * @since 260822.0653
		 *
		 * @param int   $user_id WordPress user ID that survived End-of-Term processing.
		 * @param array $details Named End-of-Term history details.
		 *
		 * @return null
		 */
		public static function record_eot_history($user_id = 0, $details = array())
		{
			$user_id = (int)$user_id;
			//260822.1458 Keep evolving EOT context named instead of positional so call sites cannot silently misorder history fields as this record grows.
			$details = array_merge(array(
				'eot_time'         => 0,
				'processed_at'     => 0,
				'original_role'    => '',
				'destination_role' => '',
				'removed_ccaps'    => array(),
				'subscr_gateway'   => '',
				'subscr_id'        => '',
			), (array)$details);
			$eot_time = (int)$details['eot_time'];
			$processed_at = (int)$details['processed_at'];
			$original_role = (string)$details['original_role'];
			$destination_role = (string)$details['destination_role'];
			$removed_ccaps = (array)$details['removed_ccaps'];
			$subscr_gateway = (string)$details['subscr_gateway'];
			$subscr_id = (string)$details['subscr_id'];
			if(!$user_id || !$processed_at)
				return;

			try
			{
				//260822.0653 Prefer WordPress' timezone object when available so historical EOT and processing timestamps each get the correct DST abbreviation.
				if(function_exists('wp_timezone'))
					$timezone = wp_timezone();
				else if(($timezone_string = (string)get_option('timezone_string')))
					$timezone = new DateTimeZone($timezone_string);
				else
				{
					$offset = (float)get_option('gmt_offset', 0);
					$offset_abs = abs($offset);
					$timezone = new DateTimeZone(sprintf('%s%02d:%02d', $offset < 0 ? '-' : '+', floor($offset_abs), round(($offset_abs - floor($offset_abs)) * 60)));
				}
				$processed_date = new DateTime('@'.$processed_at);
				$processed_date->setTimezone($timezone);
				$eot_date = new DateTime('@'.($eot_time ?: $processed_at));
				$eot_date->setTimezone($timezone);
				$processed_display = $processed_date->format('Y-m-d H:i T');
				$eot_display = $eot_date->format('Y-m-d H:i T');
			}
			catch(Exception $exception)
			{
				//260822.0653 Invalid legacy timezone settings must not block EOT processing; UTC is the deterministic fallback for the audit note.
				$processed_display = gmdate('Y-m-d H:i', $processed_at).' UTC';
				$eot_display = gmdate('Y-m-d H:i', $eot_time ?: $processed_at).' UTC';
			}

			global $wp_roles;
			if(!is_object($wp_roles))
				$wp_roles = new WP_Roles();
			$role_labels = array();
			foreach(array($original_role, $destination_role) as $_role)
			{
				$_role = (string)$_role;
				if(preg_match('/^s2member_level([0-9]+)$/', $_role, $_matches))
					$role_labels[$_role] = 'Level '.(int)$_matches[1];
				else if($_role === 's2member_pending_deletion')
					$role_labels[$_role] = 'Pending Deletion';
				else if($_role && isset($wp_roles->roles[$_role]['name']))
					$role_labels[$_role] = translate_user_role($wp_roles->roles[$_role]['name']);
				else
					$role_labels[$_role] = $_role ? ucwords(str_replace(array('-', '_'), ' ', $_role)) : 'Unknown Role';
			}
			unset($_role, $_matches);

			$gateway_labels = array('paypal' => 'PayPal', 'authnet' => 'Authorize.Net', 'clickbank' => 'ClickBank', 'ccbill' => 'ccBill', 'alipay' => 'AliPay', 'google' => 'Google Wallet', 'stripe' => 'Stripe');
			$gateway_key = strtolower((string)$subscr_gateway);
			$gateway_label = isset($gateway_labels[$gateway_key]) ? $gateway_labels[$gateway_key] : ucwords(str_replace(array('-', '_'), ' ', $gateway_key));
			$removed_ccaps = array_values(array_unique(array_filter(array_map('strval', (array)$removed_ccaps), 'strlen')));
			sort($removed_ccaps, SORT_STRING);

			$note = $processed_display.' s2Member: Demoted from '.$role_labels[(string)$original_role].' to '.$role_labels[(string)$destination_role];
			if($removed_ccaps)
				$note .= ' (removed ccaps: '.implode(', ', $removed_ccaps).')';
			$note .= '.';
			if($subscr_gateway && $subscr_id)
				$note .= ' '.$gateway_label.' '.$subscr_id.'.';
			$note .= ' EOT '.$eot_display.'.';

			//260822.0653 Keep the action timestamp independent from the triggering EOT timestamp; delayed processing can make these materially different.
			update_user_option($user_id, 's2member_last_auto_eot_processed_time', $processed_at);
			c_ws_plugin__s2member_user_notes::append_user_notes($user_id, $note);
		}

		/**
		 * Starts a best-effort upgrade backfill of historical EOT processing times.
		 *
		 * @package s2Member\Auto_EOT_System
		 * @since 260822.2048
		 *
		 * @return null
		 */
		public static function start_eot_processed_time_backfill()
		{
			$state_option = 'ws_plugin__s2member_auto_eot_state';
			$state = get_option($state_option);
			$state = is_array($state) ? $state : array();

			//260822.2048 Reuse Auto-EOT's operational state for this temporary migration cursor; no separate migration option or table is needed.
			if(!array_key_exists('processed_time_backfill_cursor_umeta_id', $state))
			{
				$state['processed_time_backfill_cursor_umeta_id'] = 0;
				update_option($state_option, $state, FALSE);
			}
			self::ensure_eot_processed_time_backfill();
		}

		/**
		 * Ensures that an unfinished historical EOT processing-time backfill has a continuation event.
		 *
		 * @package s2Member\Auto_EOT_System
		 * @since 260822.2048
		 *
		 * @return null
		 */
		public static function ensure_eot_processed_time_backfill()
		{
			$state = get_option('ws_plugin__s2member_auto_eot_state');
			$hook = 'ws_plugin__s2member_eot_processed_time_backfill';

			if(is_array($state) && array_key_exists('processed_time_backfill_cursor_umeta_id', $state) && !wp_next_scheduled($hook))
				wp_schedule_single_event(time() + 5, $hook);
		}

		/**
		 * Backfills EOT processing times that can be recovered from legacy Administrative Notes.
		 *
		 * @package s2Member\Auto_EOT_System
		 * @since 260822.2048
		 *
		 * @return null
		 */
		public static function backfill_eot_processed_times()
		{
			global $wpdb;

			$state_option = 'ws_plugin__s2member_auto_eot_state';
			$state = get_option($state_option);
			$state = is_array($state) ? $state : array();
			if(!array_key_exists('processed_time_backfill_cursor_umeta_id', $state))
				return;

			$cursor_umeta_id = (int)$state['processed_time_backfill_cursor_umeta_id'];
			$last_key = $wpdb->prefix.'s2member_last_auto_eot_time';
			$processed_key = $wpdb->prefix.'s2member_last_auto_eot_processed_time';
			$notes_key = $wpdb->prefix.'s2member_notes';
			$rows = $wpdb->get_results($wpdb->prepare(
				"SELECT `last`.`umeta_id`, `last`.`user_id`, CAST(`last`.`meta_value` AS UNSIGNED) AS `eot_time`, `notes`.`meta_value` AS `notes` FROM `".$wpdb->usermeta."` `last` INNER JOIN `".$wpdb->usermeta."` `notes` ON `notes`.`user_id` = `last`.`user_id` AND `notes`.`meta_key` = %s LEFT JOIN `".$wpdb->usermeta."` `processed` ON `processed`.`user_id` = `last`.`user_id` AND `processed`.`meta_key` = %s WHERE `last`.`meta_key` = %s AND `last`.`umeta_id` > %d AND CAST(`last`.`meta_value` AS UNSIGNED) > 0 AND `processed`.`umeta_id` IS NULL AND `notes`.`meta_value` LIKE %s ORDER BY `last`.`umeta_id` ASC LIMIT 100",
				$notes_key, $processed_key, $last_key, $cursor_umeta_id, '%Demoted by s2Member:%'
			));
			$rows = is_array($rows) ? $rows : array();

			foreach($rows as $row)
			{
				$cursor_umeta_id = (int)$row->umeta_id;
				$lines = preg_split('/\r\n|\r|\n/', (string)$row->notes);
				foreach(array_reverse((array)$lines) as $line)
					if(preg_match('/^Demoted by s2Member:\s*(.+)$/', trim($line), $matches))
					{
						$processed_at = strtotime($matches[1]);
						//260822.2048 Legacy notes have minute precision; accept up to 59 seconds before an immediate EOT timestamp, but never guess from an older unrelated demotion note.
						if($processed_at && $processed_at + MINUTE_IN_SECONDS >= (int)$row->eot_time)
						{
							$current_last_eot = $wpdb->get_var($wpdb->prepare("SELECT CAST(`meta_value` AS UNSIGNED) FROM `".$wpdb->usermeta."` WHERE `umeta_id` = %d AND `user_id` = %d AND `meta_key` = %s LIMIT 1", (int)$row->umeta_id, (int)$row->user_id, $last_key));
							$processed_exists = $wpdb->get_var($wpdb->prepare("SELECT 1 FROM `".$wpdb->usermeta."` WHERE `user_id` = %d AND `meta_key` = %s LIMIT 1", (int)$row->user_id, $processed_key));
							//260822.2259 Revalidate before writing legacy history; a newly processed EOT always wins over this best-effort upgrade backfill.
							if($current_last_eot !== NULL && (int)$current_last_eot === (int)$row->eot_time && !$processed_exists)
								add_user_meta((int)$row->user_id, $processed_key, $processed_at, TRUE);
							break;
						}
					}
			}
			unset($row, $lines, $line, $matches, $processed_at);

			$state = get_option($state_option);
			$state = is_array($state) ? $state : array();
			if(count($rows) === 100)
				$state['processed_time_backfill_cursor_umeta_id'] = $cursor_umeta_id;
			else
				unset($state['processed_time_backfill_cursor_umeta_id']);
			update_option($state_option, $state, FALSE);

			if(count($rows) === 100)
				self::ensure_eot_processed_time_backfill();
		}

		/**
		 * Applies the effective `delete` End-of-Term behavior.
		 *
		 * @package s2Member\Auto_EOT_System
		 * @since 260822.0535
		 *
		 * @param int    $user_id WordPress user ID being processed.
		 * @param string $eot_del_type EOT/deletion event type.
		 * @param int    $eot_time Unix timestamp that triggered this End-of-Term action.
		 *
		 * @return string `pending_deletion`, `deleted`, `removed`, or an empty string when no user was processed.
		 */
		public static function process_eot_deletion($user_id = 0, $eot_del_type = '', $eot_time = 0)
		{
			$user_id = (int)$user_id;
			$eot_time = (int)$eot_time;
			if(!$user_id || !is_object($user = new WP_User($user_id)) || !$user->ID)
				return '';

			if(self::allow_eot_user_deletion($user_id, $eot_del_type))
			{
				//260822.0535 True deletion is deliberately opt-in; preserve the historical deletion/removal path only after the developer filter explicitly allows it.
				$GLOBALS['ws_plugin__s2member_eot_del_type'] = (string)$eot_del_type;
				if(is_multisite())
				{
					$blog_id = get_current_blog_id();
					remove_user_from_blog($user_id, $blog_id);
					c_ws_plugin__s2member_user_deletions::handle_ms_user_deletions($user_id, $blog_id, 's2says');
					return 'removed';
				}
				include_once ABSPATH.'wp-admin/includes/admin.php';
				wp_delete_user($user_id);
				return 'deleted';
			}

			$pending_role = 's2member_pending_deletion';
			$pending_meta = get_user_option('s2member_eot_pending_deletion', $user_id);
			$already_pending = in_array($pending_role, (array)$user->roles, TRUE) && is_array($pending_meta) && isset($pending_meta['eot_time'], $pending_meta['processed_at'], $pending_meta['original_role']);
			$original_role = $already_pending ? (string)$pending_meta['original_role'] : c_ws_plugin__s2member_user_access::user_access_role($user);
			$processed_at = time();
			$removed_ccaps = $already_pending ? array() : c_ws_plugin__s2member_user_access::user_access_ccaps($user);
			$subscr_gateway = $already_pending ? '' : get_user_option('s2member_subscr_gateway', $user_id);
			$subscr_id = $already_pending ? '' : get_user_option('s2member_subscr_id', $user_id);

			//260822.0549 A surviving account can receive a replayed gateway event; preserve the first transition record and avoid duplicate EOT history/notifications when it is already safely pending.
			if(!$already_pending)
				update_user_option($user_id, 's2member_eot_pending_deletion', array(
					'eot_time'      => $eot_time ?: $processed_at,
					'processed_at'  => $processed_at,
					'original_role' => $original_role,
				));
			delete_user_option($user_id, 's2member_auto_eot_time');
			delete_user_option($user_id, 's2member_auto_eot_details');

			//260822.0535 Activation normally creates this role; the fallback keeps an EOT safe if role configuration has not yet been refreshed after an in-place update.
			if(!get_role($pending_role))
				add_role($pending_role, 'Pending Deletion', array('read' => TRUE));
			if(!in_array($pending_role, (array)$user->roles, TRUE))
				$user->set_role($pending_role);

			//260822.0535 Pending Deletion must never retain user-specific s2Member Level or Custom Capability grants after the role change.
			foreach($user->allcaps as $cap => $cap_enabled)
				if($cap_enabled && preg_match('/^access_s2member_(?:level[0-9]+|ccap_)/', $cap))
					$user->remove_cap($cap);

			if(!$already_pending)
			{
				//260822.0653 Pending Deletion survives the EOT, so archive the triggering timestamp just like an ordinary demotion; this keeps Last EOT/reporting complete without clearing gateway metadata needed for review.
				update_user_option($user_id, 's2member_last_auto_eot_time', $eot_time ?: $processed_at);
				self::record_eot_history($user_id, array(
					'eot_time'         => $eot_time ?: $processed_at,
					'processed_at'     => $processed_at,
					'original_role'    => $original_role,
					'destination_role' => $pending_role,
					'removed_ccaps'    => $removed_ccaps,
					'subscr_gateway'   => $subscr_gateway,
					'subscr_id'        => $subscr_id,
				));
				//260822.0535 A preserved account never reaches WordPress' deletion hook, so send the configured EOT/Deletion notifications explicitly instead of silently dropping them.
				self::pending_deletion_notifications($user_id, $eot_del_type);
			}

			return 'pending_deletion';
		}

		/**
		 * Sends configured EOT/Deletion notifications for an account preserved in Pending Deletion.
		 *
		 * @package s2Member\Auto_EOT_System
		 * @since 260822.0535
		 *
		 * @param int    $user_id WordPress user ID being preserved.
		 * @param string $eot_del_type EOT/deletion event type.
		 *
		 * @return null
		 */
		public static function pending_deletion_notifications($user_id = 0, $eot_del_type = '')
		{
			$user_id = (int)$user_id;
			if(!$user_id || !is_object($user = new WP_User($user_id)) || !$user->ID)
				return;

			$custom      = get_user_option('s2member_custom', $user_id);
			$subscr_id   = get_user_option('s2member_subscr_id', $user_id);
			$subscr_baid = get_user_option('s2member_subscr_baid', $user_id);
			$subscr_cid  = get_user_option('s2member_subscr_cid', $user_id);
			$fields      = get_user_option('s2member_custom_fields', $user_id);
			$user_reg_ip = get_user_option('s2member_registration_ip', $user_id);

			if($GLOBALS['WS_PLUGIN__']['s2member']['o']['eot_del_notification_urls'])
			{
				foreach(preg_split("/[\r\n\t]+/", $GLOBALS['WS_PLUGIN__']['s2member']['o']['eot_del_notification_urls']) as $url)
					if(($url = c_ws_plugin__s2member_utils_strings::fill_cvs($url, $custom, true)) && ($url = preg_replace('/%%eot_del_type%%/i', c_ws_plugin__s2member_utils_strings::esc_refs(urlencode($eot_del_type)), $url)) && ($url = preg_replace('/%%subscr_id%%/i', c_ws_plugin__s2member_utils_strings::esc_refs(urlencode($subscr_id)), $url)))
						if(($url = preg_replace('/%%subscr_baid%%/i', c_ws_plugin__s2member_utils_strings::esc_refs(urlencode($subscr_baid)), $url)) && ($url = preg_replace('/%%subscr_cid%%/i', c_ws_plugin__s2member_utils_strings::esc_refs(urlencode($subscr_cid)), $url)))
							if(($url = preg_replace('/%%user_first_name%%/i', c_ws_plugin__s2member_utils_strings::esc_refs(urlencode($user->first_name)), $url)) && ($url = preg_replace('/%%user_last_name%%/i', c_ws_plugin__s2member_utils_strings::esc_refs(urlencode($user->last_name)), $url)))
								if(($url = preg_replace('/%%user_full_name%%/i', c_ws_plugin__s2member_utils_strings::esc_refs(urlencode(trim($user->first_name.' '.$user->last_name))), $url)))
									if(($url = preg_replace('/%%user_email%%/i', c_ws_plugin__s2member_utils_strings::esc_refs(urlencode($user->user_email)), $url)))
										if(($url = preg_replace('/%%user_login%%/i', c_ws_plugin__s2member_utils_strings::esc_refs(urlencode($user->user_login)), $url)))
											if(($url = preg_replace('/%%user_ip%%/i', c_ws_plugin__s2member_utils_strings::esc_refs(urlencode($user_reg_ip)), $url)))
												if(($url = preg_replace('/%%user_id%%/i', c_ws_plugin__s2member_utils_strings::esc_refs(urlencode($user_id)), $url)))
												{
													if(is_array($fields) && !empty($fields))
														foreach($fields as $var => $val)
															if(!($url = preg_replace('/%%'.preg_quote($var, '/').'%%/i', c_ws_plugin__s2member_utils_strings::esc_refs(urlencode(maybe_serialize($val))), $url)))
																break;

													if(($url = trim(preg_replace('/%%(.+?)%%/i', '', $url))))
														c_ws_plugin__s2member_utils_urls::remote($url);
												}
			}
			if($GLOBALS['WS_PLUGIN__']['s2member']['o']['eot_del_notification_recipients'])
			{
				$email_configs_were_on = c_ws_plugin__s2member_email_configs::email_config_status();
				c_ws_plugin__s2member_email_configs::email_config_release();

				$msg = $sbj = '(s2Member / API Notification Email) - EOT/Deletion';
				$msg .= "\n\n";

				$msg .= 'eot_del_type: %%eot_del_type%%'."\n";
				$msg .= 'subscr_id: %%subscr_id%%'."\n";
				$msg .= 'subscr_baid: %%subscr_baid%%'."\n";
				$msg .= 'subscr_cid: %%subscr_cid%%'."\n";
				$msg .= 'user_first_name: %%user_first_name%%'."\n";
				$msg .= 'user_last_name: %%user_last_name%%'."\n";
				$msg .= 'user_full_name: %%user_full_name%%'."\n";
				$msg .= 'user_email: %%user_email%%'."\n";
				$msg .= 'user_login: %%user_login%%'."\n";
				$msg .= 'user_ip: %%user_ip%%'."\n";
				$msg .= 'user_id: %%user_id%%'."\n";

				if(is_array($fields) && !empty($fields))
					foreach($fields as $var => $val)
						$msg .= $var.': %%'.$var.'%%'."\n";

				$msg .= 'cv0: %%cv0%%'."\n";
				$msg .= 'cv1: %%cv1%%'."\n";
				$msg .= 'cv2: %%cv2%%'."\n";
				$msg .= 'cv3: %%cv3%%'."\n";
				$msg .= 'cv4: %%cv4%%'."\n";
				$msg .= 'cv5: %%cv5%%'."\n";
				$msg .= 'cv6: %%cv6%%'."\n";
				$msg .= 'cv7: %%cv7%%'."\n";
				$msg .= 'cv8: %%cv8%%'."\n";
				$msg .= 'cv9: %%cv9%%';

				if(($msg = c_ws_plugin__s2member_utils_strings::fill_cvs($msg, $custom)) && ($msg = preg_replace('/%%eot_del_type%%/i', c_ws_plugin__s2member_utils_strings::esc_refs($eot_del_type), $msg)) && ($msg = preg_replace('/%%subscr_id%%/i', c_ws_plugin__s2member_utils_strings::esc_refs($subscr_id), $msg)))
					if(($msg = preg_replace('/%%subscr_baid%%/i', c_ws_plugin__s2member_utils_strings::esc_refs($subscr_baid), $msg)) && ($msg = preg_replace('/%%subscr_cid%%/i', c_ws_plugin__s2member_utils_strings::esc_refs($subscr_cid), $msg)))
						if(($msg = preg_replace('/%%user_first_name%%/i', c_ws_plugin__s2member_utils_strings::esc_refs($user->first_name), $msg)) && ($msg = preg_replace('/%%user_last_name%%/i', c_ws_plugin__s2member_utils_strings::esc_refs($user->last_name), $msg)))
							if(($msg = preg_replace('/%%user_full_name%%/i', c_ws_plugin__s2member_utils_strings::esc_refs(trim($user->first_name.' '.$user->last_name)), $msg)))
								if(($msg = preg_replace('/%%user_email%%/i', c_ws_plugin__s2member_utils_strings::esc_refs($user->user_email), $msg)))
									if(($msg = preg_replace('/%%user_login%%/i', c_ws_plugin__s2member_utils_strings::esc_refs($user->user_login), $msg)))
										if(($msg = preg_replace('/%%user_ip%%/i', c_ws_plugin__s2member_utils_strings::esc_refs($user_reg_ip), $msg)))
											if(($msg = preg_replace('/%%user_id%%/i', c_ws_plugin__s2member_utils_strings::esc_refs($user_id), $msg)))
											{
												if(is_array($fields) && !empty($fields))
													foreach($fields as $var => $val)
														if(!($msg = preg_replace('/%%'.preg_quote($var, '/').'%%/i', c_ws_plugin__s2member_utils_strings::esc_refs(maybe_serialize($val)), $msg)))
															break;

												if($sbj && ($msg = trim(preg_replace('/%%(.+?)%%/i', '', $msg))))
													foreach(c_ws_plugin__s2member_utils_strings::parse_emails($GLOBALS['WS_PLUGIN__']['s2member']['o']['eot_del_notification_recipients']) as $recipient)
														wp_mail($recipient, apply_filters('ws_plugin__s2member_eot_del_notification_email_sbj', $sbj, get_defined_vars()), apply_filters('ws_plugin__s2member_eot_del_notification_email_msg', $msg, get_defined_vars()), 'Content-Type: text/plain; charset=UTF-8');
											}
				if($email_configs_were_on)
					c_ws_plugin__s2member_email_configs::email_config();
			}
		}

		/**
		 * Returns a cached health snapshot for the Auto-EOT system.
		 *
		 * @package s2Member\Auto_EOT_System
		 * @since 260820.0149
		 *
		 * @param bool $force_refresh Force a fresh usermeta/schedule check.
		 *
		 * @return array Auto-EOT health information for diagnostics and UI.
		 */
		public static function auto_eot_system_health($force_refresh = FALSE)
		{
			global $wpdb;
			/** @var $wpdb \wpdb */

			$cache_key = 'ws_plugin__s2member_auto_eot_health';
			if(!$force_refresh && is_array($health = get_transient($cache_key)))
				return $health;

			$now = time();
			$mode = (string)$GLOBALS['WS_PLUGIN__']['s2member']['o']['auto_eot_system_enabled'];
			$state = get_option('ws_plugin__s2member_auto_eot_state');
			$state = is_array($state) ? $state : array();
			$lock = get_option('ws_plugin__s2member_auto_eot_lock');
			$lock = is_array($lock) ? $lock : array();
			$meta_key = $wpdb->prefix.'s2member_auto_eot_time';

			//260820.0149 One exact-meta-key aggregate supplies both pending volume and oldest overdue age without loading EOT rows into PHP.
			$pending = $wpdb->get_row($wpdb->prepare("SELECT COUNT(*) AS `pending_count`, MIN(CAST(`meta_value` AS UNSIGNED)) AS `oldest_due_at` FROM `".$wpdb->usermeta."` WHERE `meta_key` = %s AND CAST(`meta_value` AS UNSIGNED) > 0 AND CAST(`meta_value` AS UNSIGNED) <= %d", $meta_key, $now));
			$pending_count = ($pending && !empty($pending->pending_count)) ? (int)$pending->pending_count : 0;
			$oldest_due_at = ($pending && !empty($pending->oldest_due_at)) ? (int)$pending->oldest_due_at : 0;
			$oldest_overdue_seconds = $oldest_due_at ? max(0, $now - $oldest_due_at) : 0;

			$recurring_at = ($mode === '1' && function_exists('wp_cron')) ? wp_next_scheduled('ws_plugin__s2member_auto_eot_system__schedule') : FALSE;
			$continuation_at = ($mode === '1' && function_exists('wp_cron')) ? wp_next_scheduled('ws_plugin__s2member_auto_eot_system__continuation') : FALSE;
			$issues = array();
			$critical = FALSE;
			$last_completed_at = !empty($state['last_completed_at']) ? (int)$state['last_completed_at'] : 0;
			$last_processed = isset($state['last_processed']) ? (int)$state['last_processed'] : 0;
			$last_more_due_work = !empty($state['last_more_due_work']);
			$runtime_budget = self::auto_eot_system_runtime_budget($mode === '2');
			$lock_stale_after = max(120, (int)ceil(($runtime_budget * 2) + 30));
			//260823.0021 A lock means active processing only while its heartbeat is inside the same stale window used by the worker; an abandoned lock must not mask health as current work.
			$is_running = !empty($lock['heartbeat_at']) && $now - (int)$lock['heartbeat_at'] <= $lock_stale_after;
			$catchup_fresh_after = ($mode === '2') ? 2 * HOUR_IN_SECONDS : 30 * MINUTE_IN_SECONDS;
			//260822.0614 Catch-up is ordinary queue progress, not a separate incident: report it only while a recent productive pass says more due work remains.
			$catching_up = $pending_count && $last_more_due_work && $last_processed > 0 && $last_completed_at && $now - $last_completed_at < $catchup_fresh_after;

			//260820.0149 Escalate scheduler failures independently of pending EOTs so a broken cron can be noticed before months of expirations accumulate.
			if($mode === '1')
			{
				if(!function_exists('wp_cron') || !$recurring_at)
					$issues['cron_missing'] = $critical = TRUE;
				else if((int)$recurring_at < $now - HOUR_IN_SECONDS)
					$issues['cron_overdue'] = $critical = TRUE;
			}
			else if($mode === '2' && !empty($state['last_external_completed_at']) && $now - (int)$state['last_external_completed_at'] >= 2 * HOUR_IN_SECONDS)
				$issues['external_cron_stale'] = $critical = TRUE;

			if(($mode === '1' || $mode === '2') && $pending_count)
			{
				if($catching_up)
					$issues['catching_up'] = TRUE;
				else if($oldest_overdue_seconds >= 2 * HOUR_IN_SECONDS)
					$issues['eot_overdue'] = $critical = TRUE;
				else if($oldest_overdue_seconds >= 30 * MINUTE_IN_SECONDS)
					$issues['eot_delayed'] = TRUE;
			}

			$consecutive_abandoned = !empty($state['consecutive_abandoned_runs']) ? (int)$state['consecutive_abandoned_runs'] : 0;
			if(($mode === '1' || $mode === '2') && $consecutive_abandoned >= 2)
				$issues['repeated_abandoned'] = $critical = TRUE;
			else if(($mode === '1' || $mode === '2') && $consecutive_abandoned === 1)
				$issues['abandoned'] = TRUE;

			$health = array(
				'generated_at'               => $now,
				'mode'                       => $mode,
				'status'                     => !$mode ? 'disabled' : ($critical ? 'error' : ($is_running ? 'processing' : (isset($issues['catching_up']) && count($issues) === 1 ? 'catching_up' : ($issues ? 'attention' : 'healthy')))),
				'needs_admin_notice'         => $critical ? 1 : 0,
				'issues'                     => array_keys($issues),
				'pending_count'              => $pending_count,
				'oldest_due_at'              => $oldest_due_at,
				'oldest_overdue_seconds'     => $oldest_overdue_seconds,
				'recurring_at'               => $recurring_at ? (int)$recurring_at : 0,
				'continuation_at'            => $continuation_at ? (int)$continuation_at : 0,
				'is_running'                 => $is_running ? 1 : 0,
				'last_started_at'            => !empty($state['last_started_at']) ? (int)$state['last_started_at'] : 0,
				'last_completed_at'          => $last_completed_at,
				'last_runtime'               => isset($state['last_runtime']) ? (float)$state['last_runtime'] : 0.0,
				'last_processed'             => $last_processed,
				'last_more_due_work'         => $last_more_due_work ? 1 : 0,
				'last_stop_reason'           => !empty($state['last_stop_reason']) ? (string)$state['last_stop_reason'] : '',
				'last_abandoned_at'          => !empty($state['last_abandoned_at']) ? (int)$state['last_abandoned_at'] : 0,
				'consecutive_abandoned_runs' => $consecutive_abandoned,
				'last_schedule_failure_at'   => !empty($state['last_schedule_failure_at']) ? (int)$state['last_schedule_failure_at'] : 0,
				'schedule_failure_count'     => !empty($state['schedule_failure_count']) ? (int)$state['schedule_failure_count'] : 0,
				'last_external_completed_at' => !empty($state['last_external_completed_at']) ? (int)$state['last_external_completed_at'] : 0,
			);
			$health = apply_filters('ws_plugin__s2member_auto_eot_system_health', $health, get_defined_vars());

			//260820.0149 Cache the admin-facing aggregate briefly; processing itself never relies on this snapshot.
			set_transient($cache_key, $health, 5 * MINUTE_IN_SECONDS);

			return $health;
		}

		/**
		 * Displays a site-wide administrative warning when Auto-EOT health becomes materially unsafe.
		 *
		 * @package s2Member\Auto_EOT_System
		 * @since 260820.0149
		 *
		 * @return null
		 */
		public static function auto_eot_system_admin_notice()
		{
			if(!is_admin() || !current_user_can('manage_options'))
				return;

			$health = self::auto_eot_system_health();
			if(empty($health['needs_admin_notice']))
				return;

			$reasons = array();
			if(in_array('cron_missing', $health['issues'], TRUE))
				$reasons[] = 'The recurring WP-Cron event is missing and s2Member could not restore it.';
			if(in_array('cron_overdue', $health['issues'], TRUE))
				$reasons[] = 'The recurring WP-Cron event is more than an hour overdue.';
			if(in_array('external_cron_stale', $health['issues'], TRUE))
				$reasons[] = 'The configured external cron has not completed an Auto-EOT pass in more than two hours.';
			if(in_array('eot_overdue', $health['issues'], TRUE))
				$reasons[] = number_format_i18n($health['pending_count']).' End-of-Term action'.($health['pending_count'] === 1 ? ' is' : 's are').' pending; the oldest has been overdue for '.human_time_diff($health['oldest_due_at'], time()).'.';
			if(in_array('repeated_abandoned', $health['issues'], TRUE))
				$reasons[] = number_format_i18n($health['consecutive_abandoned_runs']).' consecutive Automatic End-of-Term workers ended without reaching normal completion.';

			$settings_url = admin_url('/admin.php?page=ws-plugin--s2member-paypal-ops').'#ws-plugin--s2member-auto-eot-system-enabled';
			$notice = '<strong>s2Member Automatic End-of-Term needs attention.</strong> '.esc_html(implode(' ', $reasons)).' <a href="'.esc_url($settings_url).'">Review Automatic End-of-Term settings</a>.';
			c_ws_plugin__s2member_admin_notices::display_admin_notice($notice, TRUE);
		}

		/**
		 * Runs an Auto-EOT catch-up continuation.
		 *
		 * Catch-up passes drain overdue EOTs promptly while remaining separate from the historical
		 * collective after-hook, so Pro reminder/gateway polling is not multiplied during catch-up.
		 *
		 * @package s2Member\Auto_EOT_System
		 * @since 260820.0056
		 *
		 * @return null
		 */
		public static function auto_eot_system_continuation()
		{
			self::auto_eot_system(10, TRUE);
		}


		/**
		 * Processed by WP_Cron; this handles Auto-EOTs *(EOT = End Of Term)*.
		 *
		 * Normal processing is runtime-adaptive. The historical `$per_process` argument/filter remains
		 * available as a legacy hard item cap when a caller supplies it explicitly or a filter is attached.
		 *
		 * This function makes an important Hook available: `ws_plugin__s2member_after_auto_eot_system`.
		 * This Hook is used by some of s2Member Pro's Gateway integrations; allowing CRON processing
		 * to run for important communications; which poll Payment Gateway APIs for possible EOTs.
		 * Internal catch-up continuations intentionally do not fire that collective after-hook.
		 *
		 * 260821.0626 `ws_plugin__s2member_auto_eot_lock` is a short-lived non-autoloaded option containing
		 * `token`, `started_at`, `heartbeat_at`, `processed`, and `current_user_id`. Timestamps are Unix timestamps;
		 * counters/IDs are integers. A surviving stale lock is evidence that a worker did not reach normal cleanup.
		 *
		 * `ws_plugin__s2member_auto_eot_state` is non-autoloaded operational state. Fields are added when relevant:
		 * - Run: `last_started_at`, `active_run_token`, `last_completed_at`, `last_runtime`, `last_runtime_budget`,
		 *   `last_processed`, `last_stop_reason`, `last_invocation`, `last_external_completed_at`.
		 *   Stop reasons are `queue_empty`, `runtime_budget`, or `legacy_item_cap`; invocation is `continuation`,
		 *   `external_cron`, `wp_cron`, or `direct`.
		 * - Pending work: `last_more_due_work`, `last_pending_count`, `last_oldest_due_at`, `last_oldest_overdue_seconds`.
		 * - Legacy cap: `last_hard_cap` (int|null), `last_hard_cap_source` (`filter` or `explicit`),
		 *   `legacy_cap_estimated_additional`.
		 * - Abandoned run: `last_abandoned_at`, `last_abandoned_started_at`, `last_abandoned_heartbeat_at`,
		 *   `last_abandoned_processed`, `last_abandoned_user_id`, `consecutive_abandoned_runs`.
		 * - Scheduler repair: `last_schedule_repaired_at`, `last_schedule_failure_at`, `schedule_failure_count`.
		 * 260822.0614 Catch-up health is derived from ordinary pending/run state; there is no separate incident, cutoff,
		 * backlog audit, or review-role state that can change how overdue users are processed.
		 * Performance timing is descriptive for the last pass only; it is never persistent runtime-learning input.
		 *
		 * @package s2Member\Auto_EOT_System
		 * @since 3.5
		 *
		 * @param int  $per_process Legacy maximum database records to process in this pass when explicitly supplied or filtered.
		 * @param bool $is_continuation Internal catch-up continuation; skips the collective after-hook.
		 *
		 * @return null
		 */
		public static function auto_eot_system($per_process = 10, $is_continuation = FALSE)
		{
			global $wpdb;
			/** @var $wpdb \wpdb */
			global $current_site, $current_blog;

			include_once ABSPATH.'wp-admin/includes/admin.php';

			//260820.0056 Do not disable PHP's execution limit here; the adaptive engine deliberately works inside a measured wall-clock budget.
			@ini_set('memory_limit', apply_filters('admin_memory_limit', WP_MAX_MEMORY_LIMIT));

			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_before_auto_eot_system', get_defined_vars());
			unset($__refs, $__v); // Housekeeping.

			//260823.0421 !!! TO-DO: Revisit disabled Auto-EOT lifecycle semantics. Consider archiving an elapsed current EOT as Last EOT with an explicit skip/no-change outcome while leaving membership access untouched, instead of keeping it pending for later demotion/deletion when processing is re-enabled. This requires a safe lifecycle trigger while the action worker is disabled and must preserve reminder/provenance history correctly.
			if($GLOBALS['WS_PLUGIN__']['s2member']['o']['auto_eot_system_enabled']  /* Enabled? */)
			{
				//260820.0056 Count the budget from the request start, not merely this callback, so WordPress bootstrap/earlier cron work consumes its share too.
				$runtime_budget = self::auto_eot_system_runtime_budget();
				$request_started = isset($_SERVER['REQUEST_TIME_FLOAT']) && is_numeric($_SERVER['REQUEST_TIME_FLOAT']) ? (float)$_SERVER['REQUEST_TIME_FLOAT'] : microtime(TRUE);
				$run_started = microtime(TRUE);
				$deadline = $request_started + $runtime_budget;

				//260820.0056 Reserve padding beyond the predicted next user's cost; cap that reserve at 25% so short runtime budgets still retain useful processing time.
				$safety_buffer = min($runtime_budget * 0.25, max(0.25, (float)apply_filters('ws_plugin__s2member_auto_eot_system_runtime_safety_buffer', 1.0, get_defined_vars())));

				//260820.0056 A small non-autoloaded lock detects overlap and leaves evidence when a worker dies before reaching normal cleanup.
				$run_token = function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid('s2-eot-', TRUE);
				$lock_option = 'ws_plugin__s2member_auto_eot_lock';
				$state_option = 'ws_plugin__s2member_auto_eot_state';
				$lock_stale_after = max(120, (int)ceil(($runtime_budget * 2) + 30));
				$existing_lock = get_option($lock_option);

				//260820.0149 Discard malformed leftover state before evaluating whether another worker is active.
				if($existing_lock !== FALSE && (!is_array($existing_lock) || empty($existing_lock['heartbeat_at'])))
				{
					delete_option($lock_option);
					delete_transient('ws_plugin__s2member_auto_eot_health');
					$existing_lock = FALSE;
				}

				//260820.0056 A stale marker means the previous process never reached cleanup; preserve the useful evidence without guessing whether it was timeout, OOM, fatal error, etc.
				if(is_array($existing_lock) && !empty($existing_lock['heartbeat_at']) && time() - (int)$existing_lock['heartbeat_at'] > $lock_stale_after)
				{
					$state = get_option($state_option);
					$state = is_array($state) ? $state : array();
					$state['last_abandoned_at'] = time();
					$state['last_abandoned_started_at'] = !empty($existing_lock['started_at']) ? (int)$existing_lock['started_at'] : 0;
					$state['last_abandoned_heartbeat_at'] = !empty($existing_lock['heartbeat_at']) ? (int)$existing_lock['heartbeat_at'] : 0;
					$state['last_abandoned_processed'] = !empty($existing_lock['processed']) ? (int)$existing_lock['processed'] : 0;
					$state['last_abandoned_user_id'] = !empty($existing_lock['current_user_id']) ? (int)$existing_lock['current_user_id'] : 0;
					$state['consecutive_abandoned_runs'] = !empty($state['consecutive_abandoned_runs']) ? (int)$state['consecutive_abandoned_runs'] + 1 : 1;
					update_option($state_option, $state, FALSE);
					delete_option($lock_option);
					delete_transient('ws_plugin__s2member_auto_eot_health');
					$existing_lock = FALSE;
				}

				//260820.0056 A fresh marker belongs to another worker that should still be alive; never process the same overdue population concurrently.
				if(is_array($existing_lock) && !empty($existing_lock['heartbeat_at']))
					return;

				//260820.0056 Use add_option() for lock acquisition so two workers racing here cannot both believe they acquired it.
				$lock = array('token' => $run_token, 'started_at' => time(), 'heartbeat_at' => time(), 'processed' => 0, 'current_user_id' => 0);
				if(!add_option($lock_option, $lock, '', FALSE))
					return; // Another worker acquired the lock between our read and add.

				//260820.0056 Persist only operational health between runs; performance timing remains local to each pass so it adapts organically to current conditions.
				$state = get_option($state_option);
				$state = is_array($state) ? $state : array();
				$state['last_started_at'] = time();
				$state['active_run_token'] = $run_token;
				update_option($state_option, $state, FALSE);
				delete_transient('ws_plugin__s2member_auto_eot_health'); //260820.0149 Invalidate any cached pre-run status.

				//260820.0056 The historical count becomes a hard cap only when code explicitly supplies/filters it; the untouched default no longer throttles normal installations.
				$per_process_filter_attached = has_filter('ws_plugin__s2member_auto_eot_system_per_process') !== FALSE;
				$per_process_was_explicit = func_num_args() > 0 && !$is_continuation;
				$per_process = apply_filters('ws_plugin__s2member_auto_eot_system_per_process', $per_process, get_defined_vars());
				$hard_cap = ($per_process_filter_attached || $per_process_was_explicit) ? max(0, (int)$per_process) : NULL;
				$hard_cap_source = $per_process_filter_attached ? 'filter' : ($per_process_was_explicit ? 'explicit' : '');

				//260820.0056 Fetch modest ordered chunks from MySQL; 100 is only a query-buffer size, never the normal processing throttle.
				$chunk_size = 100;
				$processed_count = 0;
				$item_total_duration = 0.0;
				$last_item_duration = 0.0;
				$last_heartbeat = microtime(TRUE);
				$cursor_time = 0;
				$cursor_umeta_id = 0;
				$stop_reason = 'queue_empty';
				$meta_key = $wpdb->prefix.'s2member_auto_eot_time';

				while(TRUE)
				{
					//260820.0056 Honor an intentional legacy ceiling before doing another query or user operation.
					if($hard_cap !== NULL && $processed_count >= $hard_cap)
					{
						$stop_reason = 'legacy_item_cap';
						break;
					}

					//260820.0056 Near the deadline, use only this run's last/average item times to decide whether another EOT is likely to fit safely.
					$remaining_runtime = $deadline - microtime(TRUE);
					$average_item_duration = $processed_count ? $item_total_duration / $processed_count : 0.0;
					$estimated_next_duration = max($last_item_duration, $average_item_duration);
					if($remaining_runtime <= $safety_buffer + $estimated_next_duration)
					{
						$stop_reason = 'runtime_budget';
						break;
					}

					//260820.0056 A legacy hard cap may make the final SQL chunk smaller, but otherwise query size and processing capacity remain independent.
					$query_limit = $chunk_size;
					if($hard_cap !== NULL)
						$query_limit = min($query_limit, max(0, $hard_cap - $processed_count));
					if($query_limit < 1)
					{
						$stop_reason = 'legacy_item_cap';
						break;
					}

					//260820.0056 Query only due EOT metadata, oldest timestamp first; `umeta_id` makes equal timestamps deterministic and provides cursor pagination without OFFSET.
					$now = time();
					$sql = "SELECT `umeta_id`, `user_id` AS `ID`, CAST(`meta_value` AS UNSIGNED) AS `auto_eot_time` FROM `".$wpdb->usermeta."` WHERE `meta_key` = %s AND CAST(`meta_value` AS UNSIGNED) > 0 AND CAST(`meta_value` AS UNSIGNED) <= %d";
					$sql_args = array($meta_key, $now);

					//260820.0056 Continue strictly after the previous timestamp/umeta_id pair, avoiding increasingly expensive SQL OFFSET pagination.
					if($cursor_time || $cursor_umeta_id)
					{
						$sql .= " AND (CAST(`meta_value` AS UNSIGNED) > %d OR (CAST(`meta_value` AS UNSIGNED) = %d AND `umeta_id` > %d))";
						$sql_args[] = $cursor_time;
						$sql_args[] = $cursor_time;
						$sql_args[] = $cursor_umeta_id;
					}
					$sql .= " ORDER BY CAST(`meta_value` AS UNSIGNED) ASC, `umeta_id` ASC LIMIT ".(int)$query_limit;
					$eots = $wpdb->get_results($wpdb->prepare($sql, $sql_args));

					if(!is_array($eots) || !$eots)
						break;

					foreach($eots as $eot) // Oldest overdue EOT first; equal timestamps are deterministic by `umeta_id`.
					{
						$cursor_time = (int)$eot->auto_eot_time;
						$cursor_umeta_id = (int)$eot->umeta_id;

						//260820.0056 Recheck both stopping conditions inside the chunk because each user's hooks/notifications can materially change elapsed time.
						if($hard_cap !== NULL && $processed_count >= $hard_cap)
						{
							$stop_reason = 'legacy_item_cap';
							break 2;
						}
						$remaining_runtime = $deadline - microtime(TRUE);
						$average_item_duration = $processed_count ? $item_total_duration / $processed_count : 0.0;
						$estimated_next_duration = max($last_item_duration, $average_item_duration);
						if($remaining_runtime <= $safety_buffer + $estimated_next_duration)
						{
							$stop_reason = 'runtime_budget';
							break 2;
						}

						//260820.0056 Re-read only the exact selected row immediately before destructive work; skip it if its EOT was changed/deleted after selection.
						$current_eot = $wpdb->get_row($wpdb->prepare("SELECT `user_id`, `meta_key`, `meta_value` FROM `".$wpdb->usermeta."` WHERE `umeta_id` = %d LIMIT 1", $cursor_umeta_id));
						if(!$current_eot || (int)$current_eot->user_id !== (int)$eot->ID || (string)$current_eot->meta_key !== $meta_key || (int)$current_eot->meta_value !== $cursor_time || (int)$current_eot->meta_value <= 0 || (int)$current_eot->meta_value > time())
							continue;

						//260820.0056 Time the complete per-user EOT operation, including hooks/notifications, because extension work may dominate the actual cost.
						$item_started = microtime(TRUE);
						$user_id = (int)$eot->ID;
						$auto_eot_time = (int)$current_eot->meta_value;
						if($user_id && is_object($user = new WP_User ($user_id)) && $user->ID)
						{
							$log_entry = array('user' => (array)$user); // Intialize.
							$log_entry['auto_eot_time'] = $auto_eot_time; // Record EOT time.

							//260414 Keep a minimal pre-demotion subscription snapshot in the log so we can tell later
							// whether this member still had subscription metadata before anything was cleared.
							$log_entry['subscr_gateway'] = get_user_option('s2member_subscr_gateway', $user_id);
							$log_entry['subscr_id'] = get_user_option('s2member_subscr_id', $user_id);
							$log_entry['has_ipn_signup_vars'] = is_array(get_user_option('s2member_ipn_signup_vars', $user_id)) ? 'yes' : 'no';

							//260414 Defense in depth. A bad stored value of `0` caused false demotions in the wild.
							// If one still reaches this loop for any reason, log it and skip instead of clearing fields.
							if($auto_eot_time <= 0)
							{
								$log_entry['auto_eot_skip_reason'] = 'Skipped. Stored `s2member_auto_eot_time` was <= 0.';
								c_ws_plugin__s2member_utils_logs::log_entry('auto-eot-system', $log_entry);
								continue;
							}

							//260821.0626 `s2member_auto_eot_details` and `s2member_last_auto_eot_details` share the provenance format
							// `array('time' => EOT Unix timestamp, 'source' => string, 'updated_at' => Unix timestamp)`. `time` must
							// exactly match the corresponding current/archived EOT; otherwise the details are stale and ignored.
							// `source` currently uses `refund_reversal` for payment exceptions that must not be treated as renewal opportunities.
							$auto_eot_details = get_user_option('s2member_auto_eot_details', $user_id);
							if(!is_array($auto_eot_details) || empty($auto_eot_details['time']) || (int)$auto_eot_details['time'] !== $auto_eot_time)
								$auto_eot_details = array();

							delete_user_option($user_id, 's2member_last_auto_eot_time');
							delete_user_option($user_id, 's2member_last_auto_eot_details');
							delete_user_option($user_id, 's2member_auto_eot_time');
							delete_user_option($user_id, 's2member_auto_eot_details');

							if(!$user->has_cap('administrator') /* Do NOT process Administrator accounts. */)
							{
								if($GLOBALS['WS_PLUGIN__']['s2member']['o']['membership_eot_behavior'] === 'demote')
								{
									$eot_del_type = 'auto-eot-cancellation-expiration-demotion'; // Set EOT/Del type.
									$log_entry['eot_del_type'] = $eot_del_type; // Deleting user in this case.

									$custom          = get_user_option('s2member_custom', $user_id);
									$subscr_gateway  = get_user_option('s2member_subscr_gateway', $user_id);
									$subscr_id       = get_user_option('s2member_subscr_id', $user_id);
									$subscr_baid     = get_user_option('s2member_subscr_baid', $user_id);
									$subscr_cid      = get_user_option('s2member_subscr_cid', $user_id);
									$fields          = get_user_option('s2member_custom_fields', $user_id);
									$user_reg_ip     = get_user_option('s2member_registration_ip', $user_id);
									$ipn_signup_vars = get_user_option('s2member_ipn_signup_vars', $user_id);

									$demotion_role = c_ws_plugin__s2member_option_forces::force_demotion_role('subscriber');
									$existing_role = c_ws_plugin__s2member_user_access::user_access_role($user);
									$removed_ccaps = array();

									foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
									do_action('ws_plugin__s2member_during_auto_eot_system_during_before_demote', get_defined_vars());
									do_action('ws_plugin__s2member_during_collective_mods', $user_id, get_defined_vars(), $eot_del_type, 'modification', $demotion_role);
									do_action('ws_plugin__s2member_during_collective_eots', $user_id, get_defined_vars(), $eot_del_type, 'modification');
									unset($__refs, $__v); // Housekeeping.

									if($existing_role !== $demotion_role /* Only if NOT the existing Role. */)
										$user->set_role($demotion_role /* Give User the demotion Role. */);

									if(apply_filters('ws_plugin__s2member_remove_ccaps_during_eot_events', (bool)$GLOBALS['WS_PLUGIN__']['s2member']['o']['eots_remove_ccaps'], get_defined_vars()))
										foreach($user->allcaps as $cap => $cap_enabled)
											if(preg_match('/^access_s2member_ccap_/', $cap))
											{
												$removed_ccaps[] = preg_replace('/^access_s2member_ccap_/', '', $cap);
												$user->remove_cap($ccap = $cap);
											}

									delete_user_option($user_id, 's2member_subscr_gateway');
									delete_user_option($user_id, 's2member_subscr_id');
									delete_user_option($user_id, 's2member_subscr_baid');
									delete_user_option($user_id, 's2member_subscr_cid');

									delete_user_option($user_id, 's2member_ipn_signup_vars');
									if(!apply_filters('ws_plugin__s2member_preserve_paid_registration_times', TRUE))
										delete_user_option($user_id, 's2member_paid_registration_times');

									delete_user_option($user_id, 's2member_last_status_scan');
									delete_user_option($user_id, 's2member_first_payment_txn_id');
									delete_user_option($user_id, 's2member_last_payment_time');
									delete_user_option($user_id, 's2member_last_auto_eot_time');
									delete_user_option($user_id, 's2member_last_auto_eot_details');
									delete_user_option($user_id, 's2member_auto_eot_time');
									delete_user_option($user_id, 's2member_auto_eot_details');

									delete_user_option($user_id, 's2member_file_download_access_log');
									delete_user_option($user_id, 's2member_authnet_payment_failures');

									$processed_at = time();
									update_user_option($user_id, 's2member_last_auto_eot_time', $auto_eot_time);
									//260821.0057 Preserve only matching provenance (e.g., refund/reversal) alongside the archived EOT.
									if($auto_eot_details)
										update_user_option($user_id, 's2member_last_auto_eot_details', $auto_eot_details);

									//260822.0653 Record the triggering EOT separately from when this worker actually completed the demotion, using the pre-cleanup role/payment snapshot above.
									self::record_eot_history($user_id, array(
										'eot_time'         => $auto_eot_time,
										'processed_at'     => $processed_at,
										'original_role'    => $existing_role,
										'destination_role' => $demotion_role,
										'removed_ccaps'    => $removed_ccaps,
										'subscr_gateway'   => $subscr_gateway,
										'subscr_id'        => $subscr_id,
									));

									if($GLOBALS['WS_PLUGIN__']['s2member']['o']['eot_del_notification_urls'])
									{
										foreach(preg_split('/['."\r\n\t".']+/', $GLOBALS['WS_PLUGIN__']['s2member']['o']['eot_del_notification_urls']) as $url) // Handle EOT Notifications.

											if(($url = c_ws_plugin__s2member_utils_strings::fill_cvs($url, $custom, true)) && ($url = preg_replace('/%%eot_del_type%%/i', c_ws_plugin__s2member_utils_strings::esc_refs(urlencode('auto-eot-cancellation-expiration-demotion')), $url)) && ($url = preg_replace('/%%subscr_id%%/i', c_ws_plugin__s2member_utils_strings::esc_refs(urlencode($subscr_id)), $url)))
												if(($url = preg_replace('/%%user_first_name%%/i', c_ws_plugin__s2member_utils_strings::esc_refs(urlencode($user->first_name)), $url)) && ($url = preg_replace('/%%user_last_name%%/i', c_ws_plugin__s2member_utils_strings::esc_refs(urlencode($user->last_name)), $url)))
													if(($url = preg_replace('/%%user_full_name%%/i', c_ws_plugin__s2member_utils_strings::esc_refs(urlencode(trim($user->first_name.' '.$user->last_name))), $url)))
														if(($url = preg_replace('/%%user_email%%/i', c_ws_plugin__s2member_utils_strings::esc_refs(urlencode($user->user_email)), $url)))
															if(($url = preg_replace('/%%user_login%%/i', c_ws_plugin__s2member_utils_strings::esc_refs(urlencode($user->user_login)), $url)))
																if(($url = preg_replace('/%%user_ip%%/i', c_ws_plugin__s2member_utils_strings::esc_refs(urlencode($user_reg_ip)), $url)))
																	if(($url = preg_replace('/%%user_id%%/i', c_ws_plugin__s2member_utils_strings::esc_refs(urlencode($user_id)), $url)))
																	{
																		if(is_array($fields) && !empty($fields))
																			foreach($fields as $var => $val /* Custom Registration/Profile Fields. */)
																				if(!($url = preg_replace('/%%'.preg_quote($var, '/').'%%/i', c_ws_plugin__s2member_utils_strings::esc_refs(urlencode(maybe_serialize($val))), $url)))
																					break;

																		if(($url = trim(preg_replace('/%%(.+?)%%/i', '', $url))))
																			c_ws_plugin__s2member_utils_urls::remote($url);
																	}
									}
									if($GLOBALS['WS_PLUGIN__']['s2member']['o']['eot_del_notification_recipients'])
									{
										$email_configs_were_on = c_ws_plugin__s2member_email_configs::email_config_status();
										c_ws_plugin__s2member_email_configs::email_config_release();

										$msg = $sbj = '(s2Member / API Notification Email) - EOT/Deletion';
										$msg .= "\n\n"; // Spacing in the message body.

										$msg .= 'eot_del_type: %%eot_del_type%%'."\n";
										$msg .= 'subscr_id: %%subscr_id%%'."\n";
										$msg .= 'subscr_baid: %%subscr_baid%%'."\n";
										$msg .= 'subscr_cid: %%subscr_cid%%'."\n";
										$msg .= 'user_first_name: %%user_first_name%%'."\n";
										$msg .= 'user_last_name: %%user_last_name%%'."\n";
										$msg .= 'user_full_name: %%user_full_name%%'."\n";
										$msg .= 'user_email: %%user_email%%'."\n";
										$msg .= 'user_login: %%user_login%%'."\n";
										$msg .= 'user_ip: %%user_ip%%'."\n";
										$msg .= 'user_id: %%user_id%%'."\n";

										if(is_array($fields) && !empty($fields))
											foreach($fields as $var => $val)
												$msg .= $var.': %%'.$var.'%%'."\n";

										$msg .= 'cv0: %%cv0%%'."\n";
										$msg .= 'cv1: %%cv1%%'."\n";
										$msg .= 'cv2: %%cv2%%'."\n";
										$msg .= 'cv3: %%cv3%%'."\n";
										$msg .= 'cv4: %%cv4%%'."\n";
										$msg .= 'cv5: %%cv5%%'."\n";
										$msg .= 'cv6: %%cv6%%'."\n";
										$msg .= 'cv7: %%cv7%%'."\n";
										$msg .= 'cv8: %%cv8%%'."\n";
										$msg .= 'cv9: %%cv9%%';

										if(($msg = c_ws_plugin__s2member_utils_strings::fill_cvs($msg, $custom)) && ($msg = preg_replace('/%%eot_del_type%%/i', c_ws_plugin__s2member_utils_strings::esc_refs('auto-eot-cancellation-expiration-demotion'), $msg)) && ($msg = preg_replace('/%%subscr_id%%/i', c_ws_plugin__s2member_utils_strings::esc_refs($subscr_id), $msg)))
											if(($msg = preg_replace('/%%subscr_baid%%/i', c_ws_plugin__s2member_utils_strings::esc_refs($subscr_baid), $msg)) && ($msg = preg_replace('/%%subscr_cid%%/i', c_ws_plugin__s2member_utils_strings::esc_refs($subscr_cid), $msg)))
												if(($msg = preg_replace('/%%user_first_name%%/i', c_ws_plugin__s2member_utils_strings::esc_refs($user->first_name), $msg)) && ($msg = preg_replace('/%%user_last_name%%/i', c_ws_plugin__s2member_utils_strings::esc_refs($user->last_name), $msg)))
													if(($msg = preg_replace('/%%user_full_name%%/i', c_ws_plugin__s2member_utils_strings::esc_refs(trim($user->first_name.' '.$user->last_name)), $msg)))
														if(($msg = preg_replace('/%%user_email%%/i', c_ws_plugin__s2member_utils_strings::esc_refs($user->user_email), $msg)))
															if(($msg = preg_replace('/%%user_login%%/i', c_ws_plugin__s2member_utils_strings::esc_refs($user->user_login), $msg)))
																if(($msg = preg_replace('/%%user_ip%%/i', c_ws_plugin__s2member_utils_strings::esc_refs($user_reg_ip), $msg)))
																	if(($msg = preg_replace('/%%user_id%%/i', c_ws_plugin__s2member_utils_strings::esc_refs($user_id), $msg)))
																	{
																		if(is_array($fields) && !empty($fields))
																			foreach($fields as $var => $val /* Custom Registration/Profile Fields. */)
																				if(!($msg = preg_replace('/%%'.preg_quote($var, '/').'%%/i', c_ws_plugin__s2member_utils_strings::esc_refs(maybe_serialize($val)), $msg)))
																					break;

																		if($sbj && ($msg = trim(preg_replace('/%%(.+?)%%/i', '', $msg))) /* Still have a ``$sbj`` and a ``$msg``? */)

																			foreach(c_ws_plugin__s2member_utils_strings::parse_emails($GLOBALS['WS_PLUGIN__']['s2member']['o']['eot_del_notification_recipients']) as $recipient)
																				wp_mail($recipient, apply_filters('ws_plugin__s2member_eot_del_notification_email_sbj', $sbj, get_defined_vars()), apply_filters('ws_plugin__s2member_eot_del_notification_email_msg', $msg, get_defined_vars()), 'Content-Type: text/plain; charset=UTF-8');
																	}
										if($email_configs_were_on) c_ws_plugin__s2member_email_configs::email_config();
									}
									foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
									do_action('ws_plugin__s2member_during_auto_eot_system_during_demote', get_defined_vars());
									unset($__refs, $__v); // Housekeeping.
								}
								else if($GLOBALS['WS_PLUGIN__']['s2member']['o']['membership_eot_behavior'] === 'delete')
								{
									$eot_del_type = 'auto-eot-cancellation-expiration-deletion';
									$log_entry['eot_del_type'] = $eot_del_type;

									foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
									do_action('ws_plugin__s2member_during_auto_eot_system_during_before_delete', get_defined_vars());
									do_action('ws_plugin__s2member_during_collective_eots', $user_id, get_defined_vars(), $eot_del_type, 'removal-deletion');
									unset($__refs, $__v); // Housekeeping.

									//260822.0535 One operation now owns both safe Pending Deletion and the explicit developer opt-in for historical irreversible deletion.
									$eot_delete_action = self::process_eot_deletion($user_id, $eot_del_type, $auto_eot_time);
									$log_entry['eot_delete_action'] = $eot_delete_action;

									foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
									do_action('ws_plugin__s2member_during_auto_eot_system_during_delete', get_defined_vars());
									unset($__refs, $__v); // Housekeeping.
								}
								foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
								do_action('ws_plugin__s2member_during_auto_eot_system', get_defined_vars());
								unset($__refs, $__v); // Housekeeping.

								c_ws_plugin__s2member_utils_logs::log_entry('auto-eot-system', $log_entry);
							}

						}

						//260820.0056 Feed the completed item's wall-clock cost into this pass only; no timing average is persisted between runs.
						$last_item_duration = max(0, microtime(TRUE) - $item_started);
						$item_total_duration += $last_item_duration;
						$processed_count++;

						//260820.0056 Refresh the lock periodically rather than per user, preserving useful crash evidence without creating unnecessary option writes.
						if($processed_count % 5 === 0 || microtime(TRUE) - $last_heartbeat >= 5)
						{
							$lock['heartbeat_at'] = time();
							$lock['processed'] = $processed_count;
							$lock['current_user_id'] = $user_id;
							update_option($lock_option, $lock, FALSE);
							$last_heartbeat = microtime(TRUE);
						}
					}

					//260820.0056 A short chunk means the ordered query reached the end of the due rows visible during this pass; otherwise fetch the next cursor chunk.
					if(count($eots) < $query_limit)
						break;
				}

				//260820.0149 One aggregate gives both catch-up state and the pending/oldest values needed by diagnostics.
				$run_runtime = max(0, microtime(TRUE) - $run_started);
				$pending = $wpdb->get_row($wpdb->prepare("SELECT COUNT(*) AS `pending_count`, MIN(CAST(`meta_value` AS UNSIGNED)) AS `oldest_due_at` FROM `".$wpdb->usermeta."` WHERE `meta_key` = %s AND CAST(`meta_value` AS UNSIGNED) > 0 AND CAST(`meta_value` AS UNSIGNED) <= %d", $meta_key, time()));
				$pending_count = ($pending && !empty($pending->pending_count)) ? (int)$pending->pending_count : 0;
				$oldest_due_at = ($pending && !empty($pending->oldest_due_at)) ? (int)$pending->oldest_due_at : 0;
				$more_due_work = $pending_count > 0;

				//260820.0056 Preserve enough current-run timing information to explain when a legacy item cap, rather than runtime, unnecessarily constrained throughput.
				$average_item_duration = $processed_count ? $item_total_duration / $processed_count : 0.0;
				$estimated_next_duration = max($last_item_duration, $average_item_duration);
				$remaining_safe_runtime = max(0, ($deadline - microtime(TRUE)) - $safety_buffer);
				$legacy_cap_estimated_additional = ($stop_reason === 'legacy_item_cap' && $more_due_work && $estimated_next_duration > 0) ? (int)floor($remaining_safe_runtime / $estimated_next_duration) : 0;

				//260820.0056 Save compact operational health for diagnostics/UI; these are run results, not persistent performance-learning values.
				$state = get_option($state_option);
				$state = is_array($state) ? $state : array();
				$state['last_completed_at'] = time();
				$state['last_runtime'] = $run_runtime;
				$state['last_runtime_budget'] = $runtime_budget;
				$state['last_processed'] = $processed_count;
				$state['last_stop_reason'] = $stop_reason;
				$state['last_hard_cap'] = $hard_cap;
				$state['last_hard_cap_source'] = $hard_cap_source;
				$state['last_more_due_work'] = $more_due_work ? 1 : 0;
				$state['last_pending_count'] = $pending_count;
				$state['last_oldest_due_at'] = $oldest_due_at;
				$state['last_oldest_overdue_seconds'] = $oldest_due_at ? max(0, time() - $oldest_due_at) : 0;
				$state['legacy_cap_estimated_additional'] = $legacy_cap_estimated_additional;
				$state['last_invocation'] = $is_continuation ? 'continuation' : (!empty($_GET['s2member_auto_eot_system_via_cron']) ? 'external_cron' : ((defined('DOING_CRON') && DOING_CRON) ? 'wp_cron' : 'direct'));
				if($state['last_invocation'] === 'external_cron')
					$state['last_external_completed_at'] = time();
				$state['consecutive_abandoned_runs'] = 0; //260820.0149 A clean completion breaks the abandoned-run sequence.
				$state['active_run_token'] = '';

				update_option($state_option, $state, FALSE);

				//260820.0056 Delete the lock only after state is safely recorded; if PHP dies earlier, the surviving lock is what lets a future pass detect the abandoned run.
				delete_option($lock_option);

				//260820.0056 In WP-Cron mode, continue soon while overdue EOTs remain; external-cron installations already control their own invocation cadence.
				if((string)$GLOBALS['WS_PLUGIN__']['s2member']['o']['auto_eot_system_enabled'] === '1' && $more_due_work && ($hard_cap === NULL || $hard_cap > 0))
				{
					if(!wp_next_scheduled('ws_plugin__s2member_auto_eot_system__continuation'))
						wp_schedule_single_event(time() + 60, 'ws_plugin__s2member_auto_eot_system__continuation');
				}
				else if(!$more_due_work)
					wp_clear_scheduled_hook('ws_plugin__s2member_auto_eot_system__continuation');

				delete_transient('ws_plugin__s2member_auto_eot_health'); //260820.0149 Run completion changes the health snapshot.
			}
			c_ws_plugin__s2member_utils_logs::cleanup_expired_s2m_transients();

			//260820.0056 The historical collective after-hook runs only on normal passes; otherwise every one-minute catch-up pass would also multiply Pro reminders/gateway API polling.
			if(!$is_continuation)
			{
				foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
				do_action('ws_plugin__s2member_after_auto_eot_system', get_defined_vars());
				unset($__refs, $__v); // Housekeeping.
			}
			else
			{
				//260820.0056 Continuations still repair the recurring Auto-EOT event directly because they deliberately skip the collective after-hook that normally performs this check.
				self::ensure_auto_eot_system();
			}
		}
	}
}
