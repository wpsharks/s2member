<?php
// @codingStandardsIgnoreFile
/**
* Frontend asset utilities.
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
* @package s2Member\Utilities
* @since 260903.0437
*/
if(!defined('WPINC'))
	exit("Do not access this file directly.");

if(!class_exists('c_ws_plugin__s2member_utils_assets'))
{
	/**
	 * Frontend asset utilities.
	 *
	 * @package s2Member\Utilities
	 * @since 260903.0437
	 */
	class c_ws_plugin__s2member_utils_assets
	{
		protected static $static_asset_cache = array();
		protected static $static_assets_location_cache = array();
		protected static $static_assets_health_cache;
		protected static $static_js_data_map_cache = array(); //260906.1530 Parsed shipped static JavaScript data maps, keyed by path.
		protected static $static_assets_rebuild_after_save = array(); //260911.1834 Relevant saved CSS/JS option changes queue enabled static types for an immediate post-save rebuild.
		protected static $asset_http_health_cache;
		protected static $page_asset_expectations = array();
		protected static $asset_health_force_full_probe = FALSE; //260910.0630 The Health panel can request a fresh trusted current-delivery probe without changing saved delivery settings.

		/**
		 * Handles CSS compression.
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @param string $css A string of CSS.
		 * @return string String of CSS, after compression.
		 */
		public static function compress_css($css = FALSE)
		{
			$c6 = "/(\:#| #)([A-Z0-9]{6})/i";
			$css = preg_replace("/\/\*(.*?)\*\//s", "", $css);
			$css = preg_replace("/[\r\n\t]+/", "", $css);
			$css = preg_replace("/ {2,}/", " ", $css);
			$css = preg_replace("/ , | ,|, /", ",", $css);
			$css = preg_replace("/ \> | \>|\> /", ">", $css);
			$css = preg_replace("/\[ /", "[", $css);
			$css = preg_replace("/ \]/", "]", $css);
			$css = preg_replace("/ \!\= | \!\=|\!\= /", "!=", $css);
			$css = preg_replace("/ \|\= | \|\=|\|\= /", "|=", $css);
			$css = preg_replace("/ \^\= | \^\=|\^\= /", "^=", $css);
			$css = preg_replace("/ \$\= | \$\=|\$\= /", "$=", $css);
			$css = preg_replace("/ \*\= | \*\=|\*\= /", "*=", $css);
			$css = preg_replace("/ ~\= | ~\=|~\= /", "~=", $css);
			$css = preg_replace("/ \= | \=|\= /", "=", $css);
			$css = preg_replace("/ \+ | \+|\+ /", "+", $css);
			$css = preg_replace("/ ~ | ~|~ /", "~", $css);
			$css = preg_replace("/ \{ | \{|\{ /", "{", $css);
			$css = preg_replace("/ \} | \}|\} /", "}", $css);
			$css = preg_replace("/ \: | \:|\: /", ":", $css);
			$css = preg_replace("/ ; | ;|; /", ";", $css);
			$css = preg_replace("/;\}/", "}", $css);

			return preg_replace_callback($c6, 'c_ws_plugin__s2member_utils_assets::_compress_css_c3', $css);
		}

		/**
		 * Compresses JavaScript using an s2Member-adapted implementation of JShrink 1.8.1.
		 *
		 * JShrink is Copyright (c) Robert Hafner and licensed under BSD-3-Clause.
		 * See `/src/licensing/jshrink.txt` for the complete license and attribution.
		 * @see https://github.com/tedious/JShrink
		 *
		 * @package s2Member\Utilities
		 * @since 260903.0437
		 *
		 * @param string $js JavaScript source.
		 * @return string Minified JavaScript.
		 * @throws RuntimeException If malformed JavaScript cannot be minified safely.
		 */
		public static function compress_js($js = '')
		{
			$minifier = new self();
			try
			{
				$js = $minifier->jshrink_lock((string)$js);
				$js = ltrim($minifier->jshrink_minify_to_string($js, array('flaggedComments' => TRUE)));
				$js = $minifier->jshrink_unlock($js);
				$minifier->jshrink_clean();
				return $js;
			}
			catch(Exception $e)
			{
				$minifier->jshrink_clean();
				throw $e;
			}
		}

		/**
		 * Returns the selected URL used whenever frontend CSS/JavaScript needs dynamic generation.
		 *
		 * The s2Member Dynamic Loader remains the default. If its file is missing or a trusted browser probe has confirmed that it is unreachable, the normal WordPress loader is used temporarily without changing the saved preference.
		 * In the current UI this established route is named the s2Member-Only Dynamic Loader and is served by s2member-o.php.
		 *
		 * @package s2Member\Utilities
		 * @since 260904.0221
		 *
		 * @param bool $force_wordpress Force the full WordPress route for a compatibility fallback.
		 * @return string Dynamic frontend asset URL without query arguments.
		 */
		public static function dynamic_asset_url($force_wordpress = FALSE)
		{
			if(!$force_wordpress && (empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['dynamic_asset_loader']) || $GLOBALS['WS_PLUGIN__']['s2member']['o']['dynamic_asset_loader'] !== 'wordpress')
			   && is_file(self::s2o_file_path()) && !self::asset_http_target_failed('s2o', $GLOBALS['WS_PLUGIN__']['s2member']['c']['s2o_url']))
				return $GLOBALS['WS_PLUGIN__']['s2member']['c']['s2o_url'];

			return self::wordpress_dynamic_asset_url();
		}

		/**
		 * Returns the normal WordPress front-controller URL for dynamic frontend assets.
		 *
		 * @package s2Member\Utilities
		 * @since 260904.2110
		 *
		 * @return string WordPress dynamic frontend asset URL without query arguments.
		 */
		protected static function wordpress_dynamic_asset_url()
		{
			global $wp_rewrite;

			$index = (is_object($wp_rewrite) && !empty($wp_rewrite->index)) ? ltrim((string)$wp_rewrite->index, '/') : '';
			if($index === '')
				$index = 'index.php';
			return home_url('/'.$index);
		}

		/**
		 * Returns the local s2member-o.php path.
		 *
		 * @package s2Member\Utilities
		 * @since 260904.2110
		 *
		 * @return string Local filesystem path.
		 */
		protected static function s2o_file_path()
		{
			return $GLOBALS['WS_PLUGIN__']['s2member']['c']['dir'].'/'.preg_replace('/\.php$/', '-o.php', basename($GLOBALS['WS_PLUGIN__']['s2member']['l']));
		}

		/**
		 * Returns true when a trusted browser probe has confirmed that one current asset URL is unreachable.
		 *
		 * @package s2Member\Utilities
		 * @since 260904.2110
		 *
		 * @param string $id Logical health target ID.
		 * @param string $url Current public asset URL.
		 * @return bool True when the exact current URL has a recorded failure.
		 */
		protected static function asset_http_target_failed($id = '', $url = '')
		{
			$health = self::asset_http_health_state();
			return !empty($health['failures'][$id]['url']) && (string)$health['failures'][$id]['url'] === (string)$url;
		}

		/**
		 * Returns the browser-reported frontend asset health state once per PHP request.
		 *
		 * @package s2Member\Utilities
		 * @since 260904.2110
		 *
		 * @return array Stored HTTP health state.
		 */
		protected static function asset_http_health_state()
		{
			if(!isset(self::$asset_http_health_cache))
			{
				$health = get_option('ws_plugin__s2member_asset_http_health', array());
				self::$asset_http_health_cache = (is_array($health)) ? $health : array();
			}
			return self::$asset_http_health_cache;
		}

		/**
		 * Returns true while a trusted Full WordPress fallback failure is still active.
		 *
		 * @package s2Member\Utilities
		 * @since 260912.1959
		 *
		 * @return bool True when CSS or JS fallback health is currently failed.
		 */
		protected static function asset_health_fallback_problem_active()
		{
			$health = self::asset_http_health_state();
			$failures = (is_array($health) && !empty($health['failures']) && is_array($health['failures'])) ? $health['failures'] : array();
			return !empty($health['fallback_problem_since']) && (!empty($failures['fallback:dynamic_css']) || !empty($failures['fallback:dynamic_js']));
		}

		/**
		 * Adds or updates compact recent per-asset issue details for the Health panel.
		 *
		 * This diagnostic summary is intentionally kept with the trusted HTTP-health state instead of
		 * the frontend load log. It remains available when css-js.log is disabled, and one keyed entry
		 * per affected physical target prevents the state from growing with traffic.
		 *
		 * @package s2Member\Utilities
		 * @since 260910.2346
		 *
		 * @param array $issues Existing recent issue map.
		 * @param string $key Stable physical-target key.
		 * @param string $result Issue result (`late` or `failed`).
		 * @param string $label Human-readable physical asset/route label.
		 * @param string $detail Concise failure/timing detail.
		 * @param string $url Relevant asset URL.
		 * @param string $delivery Delivery mode when known.
		 * @return array Updated issue map.
		 */
		protected static function add_asset_health_recent_issue($issues = array(), $key = '', $result = '', $label = '', $detail = '', $url = '', $delivery = '')
		{
			$issues = (is_array($issues)) ? $issues : array();
			$key = substr(sanitize_key((string)$key), 0, 120);
			$result = strtolower((string)$result);
			if($key === '' || !in_array($result, array('late', 'failed'), TRUE))
				return $issues;

			$previous = (!empty($issues[$key]) && is_array($issues[$key])) ? $issues[$key] : array();
			$issues[$key] = array(
				'label' => substr(sanitize_text_field((string)$label), 0, 120),
				'result' => $result,
				'delivery' => substr(sanitize_text_field((string)$delivery), 0, 80),
				'detail' => substr(sanitize_text_field((string)$detail), 0, 200),
				'url' => esc_url_raw((string)$url),
				'first_seen' => (!empty($previous['first_seen'])) ? (int)$previous['first_seen'] : time(),
				'last_seen' => time(),
				'count' => (!empty($previous['count'])) ? (int)$previous['count'] + 1 : 1,
			);
			return $issues;
		}


		/**
		 * Returns the compact rolling CSS/JavaScript asset-load health log.
		 *
		 * The state keeps only the latest 10 individual asset loads, populated clock-minute
		 * aggregates from the latest 10 minutes, and populated clock-aligned 10-minute aggregates
		 * from the latest 6 hours since Health last became non-Green. Minute/block keys are their
		 * clock-aligned ending timestamps. Buckets retain sum/count so an accepted Late report can
		 * correct the earlier Okay rating exactly, even after that clock period ended. Empty periods
		 * are never manufactured as health evidence.
		 *
		 * @package s2Member\Utilities
		 * @since 260910.0630
		 *
		 * @return array Stored rolling asset-load health state.
		 */
		protected static function asset_health_log_state()
		{
			//260910.2346 Keep the hot-path state small and self-describing; every retained collection has a fixed request/time horizon and the option does not autoload.
			$state = get_option('ws_plugin__s2member_assets_health_log', array());
			$state = (is_array($state)) ? $state : array();
			$state['last_10_asset_loads'] = (!empty($state['last_10_asset_loads']) && is_array($state['last_10_asset_loads'])) ? array_values($state['last_10_asset_loads']) : array();
			$state['last_10min_minutes'] = (!empty($state['last_10min_minutes']) && is_array($state['last_10min_minutes'])) ? $state['last_10min_minutes'] : array();
			$state['last_6hour_10min_blocks'] = (!empty($state['last_6hour_10min_blocks']) && is_array($state['last_6hour_10min_blocks'])) ? $state['last_6hour_10min_blocks'] : array();
			//260911.1806 Keep one compact historical issue outside the rolling score windows so Last issue remains useful after busy healthy traffic or natural recovery.
			$state['last_issue'] = (!empty($state['last_issue']) && is_array($state['last_issue'])) ? $state['last_issue'] : array();
			//260912.0258 Keep a small duplicate-processing safeguard inside the existing health log in case a queued event survives after its changes were already stored.
			$state['processed_event_times'] = (!empty($state['processed_event_times']) && is_array($state['processed_event_times'])) ? array_slice(array_values($state['processed_event_times']), -100) : array();
			return $state;
		}

		/**
		 * Returns the option-name prefix used by queued asset-health events.
		 *
		 * Frontend requests queue separate non-autoloaded events instead of rewriting the shared rolling
		 * log. The Health Logkeeper later merges those events into the one persistent health-log option.
		 *
		 * @package s2Member\Utilities
		 * @since 260912.0258
		 *
		 * @return string Event option prefix.
		 */
		protected static function asset_health_event_option_prefix()
		{
			return 'ws_plugin__s2member_assets_health_event_';
		}

		/**
		 * Queues one asset-health event without waiting for or rewriting the shared rolling log.
		 *
		 * The option suffix combines the event time with the queue time in microseconds. That meaningful
		 * pair gives chronological ordering, practical uniqueness, and duplicate-processing identity.
		 *
		 * @package s2Member\Utilities
		 * @since 260912.0258
		 *
		 * @param array $event Compact load/issue event.
		 * @return string Queued event-times suffix, or an empty string on failure.
		 */
		protected static function queue_asset_health_event($event = array())
		{
			$event = (is_array($event)) ? $event : array();
			$event_time = (!empty($event['event_time'])) ? max(1, (int)$event['event_time']) : time();
			$microtime = explode(' ', microtime(), 2);
			$queued_sec = (!empty($microtime[1])) ? max(1, (int)$microtime[1]) : time();
			$queued_usec = (!empty($microtime[0])) ? (int)substr($microtime[0], 2, 6) : 0;
			$prefix = self::asset_health_event_option_prefix();
			$event_time_order = str_pad((string)$event_time, 12, '0', STR_PAD_LEFT);

			//260912.0258 add_option() provides the atomic uniqueness check; an extraordinarily unlikely collision simply advances the queue time by one microsecond and retries.
			for($attempt = 0; $attempt < 3; $attempt++)
			{
				$queued_time_order = str_pad((string)$queued_sec, 12, '0', STR_PAD_LEFT).str_pad((string)$queued_usec, 6, '0', STR_PAD_LEFT);
				$event_times = $event_time_order.'_'.$queued_time_order;
				if(add_option($prefix.$event_times, $event, '', 'no'))
				{
					//260912.0258 Schedule the Health Logkeeper without making the visitor wait for health-log maintenance.
					if(!wp_next_scheduled('ws_plugin__s2member_assets_health_logkeeper'))
						wp_schedule_single_event(time() + 10, 'ws_plugin__s2member_assets_health_logkeeper');
					return $event_times;
				}
				if(++$queued_usec > 999999)
				{
					$queued_usec = 0;
					$queued_sec++;
				}
			}
			return '';
		}

		/**
		 * Acquires the Health Logkeeper lock without waiting.
		 *
		 * Only the Health Logkeeper writes the shared rolling health log. If another Logkeeper run is
		 * active, this request exits immediately; frontend requests only queue events and never wait here.
		 *
		 * @package s2Member\Utilities
		 * @since 260912.0258
		 *
		 * @return string Unique lock value, or an empty string when another Logkeeper run owns it.
		 */
		protected static function health_logkeeper_lock_acquire()
		{
			$option = 'ws_plugin__s2member_assets_health_logkeeper_lock';
			$lock = time().':'.sha1(microtime(TRUE)."\0".wp_rand());
			if(add_option($option, $lock, '', 'no'))
				return $lock;

			$current = (string)get_option($option, '');
			$parts = explode(':', $current, 2);
			$locked_at = (!empty($parts[0]) && is_numeric($parts[0])) ? (int)$parts[0] : 0;
			//260912.0258 Recover a Logkeeper lock left behind by an interrupted request, but never sleep waiting for a live run.
			if(!$locked_at || $locked_at < time() - 2 * MINUTE_IN_SECONDS)
			{
				delete_option($option);
				if(add_option($option, $lock, '', 'no'))
					return $lock;
			}
			return '';
		}

		/**
		 * Releases the Health Logkeeper lock when this request still owns it.
		 *
		 * @package s2Member\Utilities
		 * @since 260912.0258
		 *
		 * @param string $lock Unique lock value returned by health_logkeeper_lock_acquire().
		 * @return null
		 */
		protected static function health_logkeeper_lock_release($lock = '')
		{
			$option = 'ws_plugin__s2member_assets_health_logkeeper_lock';
			if($lock !== '' && (string)get_option($option, '') === (string)$lock)
				delete_option($option);
			return;
		}

		/**
		 * Replaces Last issue only when the candidate issue is at least as recent as the current one.
		 *
		 * @package s2Member\Utilities
		 * @since 260911.2325
		 *
		 * @param array  $state Asset-health log state, passed by reference.
		 * @param int    $time Issue timestamp.
		 * @param string $result Compact historical result key.
		 * @param string $label Site-owner-friendly asset label.
		 * @param string $detail Concise explanation.
		 * @param string $load_id Optional related load ID.
		 * @return bool True when Last issue was replaced.
		 */
		protected static function set_asset_health_last_issue(&$state, $time = 0, $result = '', $label = '', $detail = '', $load_id = '')
		{
			$time = max(1, (int)$time);
			$result = strtolower((string)$result);
			$current_time = (!empty($state['last_issue']['time'])) ? (int)$state['last_issue']['time'] : 0;
			if($result === '' || $current_time > $time)
				return FALSE;

			//260911.2325 Delayed browser reports may arrive out of order; Last issue must follow event time, not whichever request happened to write last.
			$state['last_issue'] = array(
				'time' => $time,
				'result' => $result,
				'label' => substr((string)$label, 0, 100),
				'detail' => substr(wp_strip_all_tags((string)$detail), 0, 240),
				'load_id' => (string)$load_id,
			);
			return TRUE;
		}

		/**
		 * Returns the numeric rating for one asset-load result.
		 *
		 * @package s2Member\Utilities
		 * @since 260910.0630
		 *
		 * @param string $result Asset-load result: `okay`, `late`, `fallback`, or `failed`.
		 * @return int Rating from 1 through 4, or zero when invalid.
		 */
		protected static function asset_health_load_rating($result = '')
		{
			$ratings = array('okay' => 4, 'late' => 3, 'fallback' => 2, 'failed' => 1); //260910.2346 Persist full result words so the health log remains readable without an O/L/F/X legend; the numeric value is used only for scoring.
			$result = strtolower((string)$result);
			return isset($ratings[$result]) ? $ratings[$result] : 0;
		}

		/**
		 * Returns the ending timestamp of the clock-aligned period containing a timestamp.
		 *
		 * A timestamp exactly on a boundary belongs to the period ending at that boundary. Thus a
		 * 10-minute period ending 12:10:00 represents 12:00:01 through 12:10:00 at whole-second precision.
		 *
		 * @package s2Member\Utilities
		 * @since 260910.2346
		 *
		 * @param int $time Timestamp.
		 * @param int $seconds Period size in seconds.
		 * @return int Clock-aligned period ending timestamp.
		 */
		protected static function asset_health_period_end($time = 0, $seconds = 0)
		{
			$time = max(1, (int)$time);
			$seconds = max(1, (int)$seconds);
			return (int)(ceil($time / $seconds) * $seconds);
		}

		/**
		 * Converts the final 1.00-4.00 health score to the site-owner status color.
		 *
		 * @package s2Member\Utilities
		 * @since 260910.0630
		 *
		 * @param float|null $score Final health score, or NULL when there is no evidence yet.
		 * @param string $latest_result Latest individual asset-load result when available.
		 * @return string Status-light key.
		 */
		protected static function asset_health_status_from_score($score = NULL, $latest_result = '')
		{
			if($score === NULL)
				return 'unknown';
			$score = (float)$score;
			$latest_result = strtolower((string)$latest_result);
			//260912.2005 Exact half-point boundaries belong to the less-healthy band; use the documented two-decimal cutoffs so 2.50, for example, is Working, review suggested rather than Recent issue.
			if($score >= 3.51 && ($latest_result === '' || $latest_result === 'okay'))
				return 'healthy';
			if($score >= 2.51)
				return 'delayed';
			if($score >= 1.51)
				return 'attention';
			return 'error';
		}

		/**
		 * Recalculates request, time, and final health scores from the retained asset-load log.
		 *
		 * Newer asset loads have importance 10 down through 1. Each populated clock minute first
		 * averages all asset-load ratings inside it, then receives importance 10 for the current minute
		 * down through 1 nine minutes ago. Empty minutes are skipped instead of inventing evidence.
		 *
		 * @package s2Member\Utilities
		 * @since 260910.0630
		 *
		 * @param array|null $state Optional already-loaded asset health log.
		 * @return array Request/time/final scores and supporting counts.
		 */
		protected static function asset_health_scores($state = NULL)
		{
			$state = (is_array($state)) ? $state : self::asset_health_log_state();
			$loads = (!empty($state['last_10_asset_loads']) && is_array($state['last_10_asset_loads'])) ? array_values($state['last_10_asset_loads']) : array();
			$loads = array_slice($loads, -10);
			$request_total = 0.0;
			$request_importance = 0;
			$importance = 10;
			//260910.0709 Newest asset load matters most (10) and the oldest retained load least (1); divide by total importance below so the result stays on the same 1.00-4.00 scale.
			for($i = count($loads) - 1; $i >= 0 && $importance >= 1; $i--, $importance--)
			{
				$rating = (!empty($loads[$i]['result'])) ? self::asset_health_load_rating($loads[$i]['result']) : 0;
				if(!$rating)
					continue;
				$request_total += $rating * $importance;
				$request_importance += $importance;
			}
			$request_score = ($request_importance) ? $request_total / $request_importance : NULL;

			$current_minute_end = self::asset_health_period_end(time(), MINUTE_IN_SECONDS);
			$time_total = 0.0;
			$time_importance = 0;
			$time_count = 0;
			//260910.0709 Time Health averages every load inside a populated clock minute before applying recency importance, so heavy traffic cannot dominate other minutes and mixed outcomes inside one minute are not discarded.
			foreach((!empty($state['last_10min_minutes']) && is_array($state['last_10min_minutes'])) ? $state['last_10min_minutes'] : array() as $minute_end => $bucket)
			{
				$minute_end = (int)$minute_end;
				$age = (int)(($current_minute_end - $minute_end) / MINUTE_IN_SECONDS);
				$bucket_count = (!empty($bucket['count'])) ? (int)$bucket['count'] : 0;
				$bucket_sum = (isset($bucket['sum'])) ? (float)$bucket['sum'] : 0.0;
				if($age < 0 || $age > 9 || $bucket_count < 1)
					continue;
				$rating = $bucket_sum / $bucket_count;
				if($rating < 1 || $rating > 4)
					continue;
				$importance = 10 - $age;
				$time_total += $rating * $importance;
				$time_importance += $importance;
				$time_count++;
			}
			$time_score = ($time_importance) ? $time_total / $time_importance : NULL;
			//260910.0709 Request history and clock-time history get equal final influence when both exist; neither perspective can silently dominate the other.
			if($request_score !== NULL && $time_score !== NULL)
				$score = ($request_score + $time_score) / 2;
			else if($request_score !== NULL)
				$score = $request_score;
			else if($time_score !== NULL)
				$score = $time_score;
			else
				$score = NULL;

			$latest_result = ($loads && !empty($loads[count($loads) - 1]['result'])) ? (string)$loads[count($loads) - 1]['result'] : '';

			return array(
				'request_score' => $request_score,
				'time_score' => $time_score,
				'score' => $score,
				'latest_result' => $latest_result,
				'request_count' => count($loads),
				'time_count' => $time_count,
			);
		}

		/**
		 * Returns the equal-block average from populated clock-aligned 10-minute blocks in the last 6 hours.
		 *
		 * Each populated 10-minute block contributes one average regardless of traffic volume. Empty
		 * blocks contribute nothing because absence of traffic is not health evidence.
		 *
		 * @package s2Member\Utilities
		 * @since 260910.0630
		 *
		 * @param array $state Asset health log.
		 * @return float|null Rolling six-hour average, or NULL without retained non-Green evidence.
		 */
		protected static function asset_health_six_hour_average($state = array())
		{
			$blocks = (!empty($state['last_6hour_10min_blocks']) && is_array($state['last_6hour_10min_blocks'])) ? $state['last_6hour_10min_blocks'] : array();
			if(!$blocks)
				return NULL;
			$cutoff = time() - 6 * HOUR_IN_SECONDS;
			$total = 0.0;
			$count = 0;
			//260910.2346 The persistent-review calculation runs only when an admin request has already passed cheaper status/age checks; at most about 37 populated blocks can contribute.
			foreach($blocks as $block_end => $bucket)
			{
				$block_end = (int)$block_end;
				$bucket_count = (!empty($bucket['count'])) ? (int)$bucket['count'] : 0;
				$bucket_sum = (isset($bucket['sum'])) ? (float)$bucket['sum'] : 0.0;
				if($block_end <= $cutoff || $bucket_count < 1)
					continue;
				$rating = $bucket_sum / $bucket_count;
				if($rating < 1 || $rating > 4)
					continue;
				$total += $rating;
				$count++;
			}
			return ($count) ? $total / $count : NULL;
		}

		/**
		 * Returns a signature for compact page-load metadata used by a later Late correction.
		 *
		 * @package s2Member\Utilities
		 * @since 260910.2346
		 *
		 * @param array $load Asset-load metadata.
		 * @return string Signature.
		 */
		protected static function asset_health_load_signature($load = array())
		{
			$parts = array();
			foreach(array('load_id', 'load_time', 'orig_result', 'orig_6hour') as $key)
				$parts[$key] = isset($load[$key]) ? (string)$load[$key] : '';
			return hash_hmac('sha256', serialize($parts), wp_salt('nonce'));
		}

		/**
		 * Queues one frontend asset load or signed Late correction for later collection.
		 *
		 * A WordPress-rendered frontend page queues one page-level result using the worst required
		 * asset outcome: Okay=4, Late=3, Fallback=2, Failed=1. A signed Late report later corrects
		 * that original load instead of counting the same page twice.
		 *
		 * @package s2Member\Utilities
		 * @since 260910.0630
		 *
		 * @param string $result Asset-load result: `okay`, `late`, `fallback`, or `failed`.
		 * @param bool $reset_on_ok Reset prior active history when an explicit trusted recheck returns Okay.
		 * @param array $load Optional signed original-load metadata for a Late correction.
		 * @param array $issue Optional compact issue snapshot with `label` and `detail`.
		 * @return array Compact metadata for the queued load; `scores` remains an empty compatibility field.
		 */
		protected static function queue_asset_health_load($result = '', $reset_on_ok = FALSE, $load = array(), $issue = array())
		{
			$result = strtolower((string)$result);
			$rating = self::asset_health_load_rating($result);
			if(!$rating)
				return array('scores' => array(), 'load' => array());

			$now = time();
			$load = (is_array($load)) ? $load : array();
			$issue = (is_array($issue)) ? $issue : array();
			$is_late_correction = $result === 'late' && !empty($load['load_id']) && !empty($load['load_time']);
			if(!$is_late_correction)
			{
				$load = array(
					'load_id' => sha1(microtime(TRUE)."\0".wp_rand()."\0".home_url('/')),
					'load_time' => $now,
					'orig_result' => $result,
					'orig_6hour' => 0,
				);
			}

			$event_time = ($is_late_correction && !empty($load['load_time'])) ? (int)$load['load_time'] : $now;
			//260912.0258 Queue the event and return without reading, locking, or rewriting the shared rolling health log.
			self::queue_asset_health_event(array(
				'type' => 'load',
				'event_time' => $event_time,
				'result' => $result,
				'reset_on_ok' => (bool)$reset_on_ok,
				'load' => $load,
				'issue' => $issue,
			));

			if(!$is_late_correction)
				$load['signature'] = self::asset_health_load_signature($load);
			return array('scores' => array(), 'load' => $load);
		}

		/**
		 * Queues a useful historical issue that did not itself degrade the page-level health score.
		 *
		 * @package s2Member\Utilities
		 * @since 260911.1834
		 *
		 * @param string $result Compact historical result key.
		 * @param string $label Site-owner-friendly asset label.
		 * @param string $detail Concise explanation.
		 * @return null
		 */
		protected static function queue_asset_health_issue_snapshot($result = '', $label = '', $detail = '')
		{
			$result = strtolower((string)$result);
			if($result === '')
				return;

			//260912.0258 Queue self-repair history like scored loads so a simultaneous healthy page cannot erase it with stale state.
			self::queue_asset_health_event(array(
				'type' => 'issue',
				'event_time' => time(),
				'result' => $result,
				'issue' => array('label' => (string)$label, 'detail' => (string)$detail),
			));
			return;
		}

		/**
		 * Applies one queued scored-load event to an already-loaded health-log state.
		 *
		 * @package s2Member\Utilities
		 * @since 260911.2356
		 *
		 * @param array $state Rolling health-log state, passed by reference.
		 * @param array $event Queued load event.
		 * @return bool True when the event was valid and consumed.
		 */
		protected static function apply_asset_health_load_event(&$state, $event = array())
		{
			$event = (is_array($event)) ? $event : array();
			$result = (!empty($event['result'])) ? strtolower((string)$event['result']) : '';
			$rating = self::asset_health_load_rating($result);
			if(!$rating)
				return FALSE;

			$now = time();
			$event_time = (!empty($event['event_time'])) ? max(1, (int)$event['event_time']) : $now;
			$reset_on_ok = !empty($event['reset_on_ok']);
			$load = (!empty($event['load']) && is_array($event['load'])) ? $event['load'] : array();
			$issue = (!empty($event['issue']) && is_array($event['issue'])) ? $event['issue'] : array();

			if($reset_on_ok && $result === 'okay')
			{
				//260912.0258 A trusted successful recheck resets scoring only; Last issue and duplicate-processing protection remain historical state.
				$last_issue = (!empty($state['last_issue']) && is_array($state['last_issue'])) ? $state['last_issue'] : array();
				$processed_event_times = (!empty($state['processed_event_times']) && is_array($state['processed_event_times'])) ? $state['processed_event_times'] : array();
				$state = array('last_10_asset_loads' => array(), 'last_10min_minutes' => array(), 'last_6hour_10min_blocks' => array(), 'last_issue' => $last_issue, 'processed_event_times' => $processed_event_times, 'status' => 'unknown', 'not_green_since' => 0, 'history_reset_at' => $event_time);
				delete_option('ws_plugin__s2member_asset_notice_dismissed');
			}

			$late_before_reset = FALSE;
			$is_late_correction = $result === 'late' && !empty($load['load_id']) && !empty($load['load_time']) && !empty($load['orig_result']) && isset($load['orig_6hour']) && !empty($load['signature']);
			if($is_late_correction)
			{
				$load['load_id'] = preg_replace('/[^a-f0-9]/', '', strtolower((string)$load['load_id']));
				$load['load_time'] = (int)$load['load_time'];
				$load['orig_result'] = strtolower((string)$load['orig_result']);
				$load['orig_6hour'] = !empty($load['orig_6hour']) ? 1 : 0;
				$signature = (string)$load['signature'];
				unset($load['signature']);
				$is_late_correction = strlen($load['load_id']) === 40 && $load['load_time'] > 0 && in_array($load['orig_result'], array('okay', 'fallback'), TRUE) && hash_equals(self::asset_health_load_signature($load), $signature);
				if($is_late_correction && !empty($state['history_reset_at']) && $load['load_time'] < (int)$state['history_reset_at'])
				{
					$late_before_reset = TRUE;
					$is_late_correction = FALSE;
				}
			}

			if($late_before_reset)
			{
				self::set_asset_health_last_issue($state, $load['load_time'], 'late', (!empty($issue['label'])) ? $issue['label'] : '', (!empty($issue['detail'])) ? $issue['detail'] : '', (!empty($load['load_id'])) ? $load['load_id'] : '');
				return TRUE; //260911.2356 Old delayed reports remain useful history but never re-enter a newer scoring epoch.
			}

			$original_contributed_to_6hour = FALSE;
			if($is_late_correction)
			{
				$orig_rating = self::asset_health_load_rating($load['orig_result']);
				if($rating >= $orig_rating)
					return TRUE; // Fallback is already worse than Late.
				$late_key = 'ws_plugin__s2member_asset_load_late_'.$load['load_id'];
				if(get_transient($late_key))
					return TRUE;

				$found = FALSE;
				foreach($state['last_10_asset_loads'] as &$entry)
					if(!empty($entry['load_id']) && hash_equals((string)$entry['load_id'], $load['load_id']))
					{
						$original_contributed_to_6hour = !empty($entry['orig_6hour']);
						$entry['result'] = 'late';
						$found = TRUE;
						break;
					}
				unset($entry);
				//260912.0258 A queued Late correction may arrive after its original load was processed, so derive six-hour membership from retained server state.
				if(!$found && !empty($state['not_green_since']) && (int)$state['not_green_since'] <= $load['load_time'])
					$original_contributed_to_6hour = TRUE;

				$minute_end = self::asset_health_period_end($load['load_time'], MINUTE_IN_SECONDS);
				$delta = $rating - $orig_rating;
				if(isset($state['last_10min_minutes'][$minute_end]) && !empty($state['last_10min_minutes'][$minute_end]['count']))
					$state['last_10min_minutes'][$minute_end]['sum'] += $delta;
				set_transient($late_key, 1, HOUR_IN_SECONDS);
			}
			else
			{
				$load_id = (!empty($load['load_id'])) ? preg_replace('/[^a-f0-9]/', '', strtolower((string)$load['load_id'])) : '';
				$load_id = (strlen($load_id) === 40) ? $load_id : sha1(microtime(TRUE)."\0".wp_rand()."\0".home_url('/'));
				$load_time = (!empty($load['load_time'])) ? max(1, (int)$load['load_time']) : $event_time;
				$load = array('load_id' => $load_id, 'load_time' => $load_time, 'orig_result' => $result, 'orig_6hour' => 0);
				//260912.0551 The Health Logkeeper already processes queued loads in event-time/queue-time order; preserve that order so simultaneous same-second requests are not randomized by load ID.
				$state['last_10_asset_loads'][] = array('time' => $load_time, 'result' => $result, 'load_id' => $load_id, 'orig_6hour' => 0);
				$state['last_10_asset_loads'] = array_slice($state['last_10_asset_loads'], -10);

				$minute_end = self::asset_health_period_end($load_time, MINUTE_IN_SECONDS);
				if(empty($state['last_10min_minutes'][$minute_end]) || !is_array($state['last_10min_minutes'][$minute_end]))
					$state['last_10min_minutes'][$minute_end] = array('sum' => 0.0, 'count' => 0);
				$state['last_10min_minutes'][$minute_end]['sum'] += $rating;
				$state['last_10min_minutes'][$minute_end]['count']++;
			}

			$current_minute_end = self::asset_health_period_end($now, MINUTE_IN_SECONDS);
			foreach($state['last_10min_minutes'] as $minute_end => $bucket)
				if((int)$minute_end < $current_minute_end - 9 * MINUTE_IN_SECONDS || (int)$minute_end > $current_minute_end)
					unset($state['last_10min_minutes'][$minute_end]);

			$scores = self::asset_health_scores($state);
			$status = self::asset_health_status_from_score($scores['score'], $scores['latest_result']);
			$previous_status = (!empty($state['status'])) ? (string)$state['status'] : 'unknown';
			$state['status'] = $status;

			if($status === 'healthy')
			{
				$state['not_green_since'] = 0;
				$state['last_6hour_10min_blocks'] = array();
				//260912.1959 Rolling delivery may be Healthy while a trusted standby fallback is still unavailable; keep that combined-health notice dismissal until the fallback recovers.
				if(!self::asset_health_fallback_problem_active())
					delete_option('ws_plugin__s2member_asset_notice_dismissed');
			}
			else
			{
				if(empty($state['not_green_since']))
					$state['not_green_since'] = ($is_late_correction) ? $load['load_time'] : $event_time;
				$block_time = ($is_late_correction) ? $load['load_time'] : $event_time;
				$block_end = self::asset_health_period_end($block_time, 10 * MINUTE_IN_SECONDS);
				if($is_late_correction && $original_contributed_to_6hour && isset($state['last_6hour_10min_blocks'][$block_end]) && !empty($state['last_6hour_10min_blocks'][$block_end]['count']))
					$state['last_6hour_10min_blocks'][$block_end]['sum'] += $rating - self::asset_health_load_rating($load['orig_result']);
				else if(!$is_late_correction || !$original_contributed_to_6hour)
				{
					if(empty($state['last_6hour_10min_blocks'][$block_end]) || !is_array($state['last_6hour_10min_blocks'][$block_end]))
						$state['last_6hour_10min_blocks'][$block_end] = array('sum' => 0.0, 'count' => 0);
					$state['last_6hour_10min_blocks'][$block_end]['sum'] += $rating;
					$state['last_6hour_10min_blocks'][$block_end]['count']++;
					if(!$is_late_correction)
						foreach($state['last_10_asset_loads'] as &$entry)
							if(!empty($entry['load_id']) && hash_equals((string)$entry['load_id'], (string)$load['load_id']))
							{
								$entry['orig_6hour'] = 1;
								break;
							}
					unset($entry);
				}
				$cutoff = $now - 6 * HOUR_IN_SECONDS;
				foreach($state['last_6hour_10min_blocks'] as $end => $bucket)
					if((int)$end <= $cutoff)
						unset($state['last_6hour_10min_blocks'][$end]);
			}

			if($previous_status === 'healthy' && $status !== 'healthy')
				delete_option('ws_plugin__s2member_asset_notice_dismissed');
			if($result !== 'okay')
			{
				$issue_time = ($is_late_correction && !empty($load['load_time'])) ? (int)$load['load_time'] : $event_time;
				self::set_asset_health_last_issue($state, $issue_time, $result, (!empty($issue['label'])) ? $issue['label'] : '', (!empty($issue['detail'])) ? $issue['detail'] : '', (!empty($load['load_id'])) ? $load['load_id'] : '');
			}
			return TRUE;
		}

		/**
		 * Synchronizes time-derived status fields after queued events are merged.
		 *
		 * @package s2Member\Utilities
		 * @since 260911.2356
		 *
		 * @param array $state Rolling health-log state, passed by reference.
		 * @return bool True when derived state changed.
		 */
		protected static function sync_asset_health_derived_state(&$state)
		{
			$scores = self::asset_health_scores($state);
			$overall = self::asset_health_status_from_score($scores['score'], $scores['latest_result']);
			$changed = (!isset($state['status']) || (string)$state['status'] !== $overall);
			$state['status'] = $overall;

			if($overall === 'healthy')
			{
				if(!empty($state['not_green_since']) || !empty($state['last_6hour_10min_blocks']))
				{
					$state['not_green_since'] = 0;
					$state['last_6hour_10min_blocks'] = array();
					$changed = TRUE;
					//260912.1959 Do not clear a dismissed combined-health notice while the independent fallback problem is still active.
					if(!self::asset_health_fallback_problem_active())
						delete_option('ws_plugin__s2member_asset_notice_dismissed');
				}
			}
			else if($overall !== 'unknown' && empty($state['not_green_since']))
			{
				$state['not_green_since'] = time();
				$changed = TRUE;
			}
			return $changed;
		}

		/**
		 * Runs the Health Logkeeper, merging queued frontend asset-health events into the rolling log.
		 *
		 * The Logkeeper never waits for another run. Frontend requests only queue separate event options,
		 * so page delivery is never serialized behind health-log maintenance.
		 *
		 * @package s2Member\Utilities
		 * @since 260912.0258
		 *
		 * @attaches-to ``add_action('ws_plugin__s2member_assets_health_logkeeper');``
		 * @return int Number of queued events handled.
		 */
		public static function run_health_logkeeper()
		{
			$lock = self::health_logkeeper_lock_acquire();
			if($lock === '')
			{
				//260912.0258 Never wait for a live Logkeeper; leave a background retry so a one-off scheduling collision cannot strand queued events.
				if(!wp_next_scheduled('ws_plugin__s2member_assets_health_logkeeper'))
					wp_schedule_single_event(time() + 30, 'ws_plugin__s2member_assets_health_logkeeper');
				return 0;
			}

			global $wpdb;
			$prefix = self::asset_health_event_option_prefix();
			$like = $wpdb->esc_like($prefix).'%';
			//260912.0258 Fetch each queued option and its value in one indexed prefix query; the timestamp-based option names already provide chronological order.
			$rows = $wpdb->get_results($wpdb->prepare("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s ORDER BY option_name ASC LIMIT 100", $like));
			$events = array();
			foreach((array)$rows as $row)
			{
				$option_name = (!empty($row->option_name)) ? (string)$row->option_name : '';
				$event_times = ($option_name !== '' && strpos($option_name, $prefix) === 0) ? substr($option_name, strlen($prefix)) : '';
				$event = c_ws_plugin__s2member_utils_arrays::maybe_unserialize(isset($row->option_value) ? $row->option_value : NULL);
				if(!preg_match('/^\\d{12}_\\d{18}$/D', $event_times) || !is_array($event))
				{
					//260912.0258 Malformed telemetry is disposable; delete it instead of carrying unexpected data into the health log.
					if($option_name !== '')
						delete_option($option_name);
					continue;
				}
				$events[] = array('option_name' => $option_name, 'event_times' => $event_times, 'event' => $event);
			}

			$state = self::asset_health_log_state();
			$processed = (!empty($state['processed_event_times']) && is_array($state['processed_event_times'])) ? array_fill_keys($state['processed_event_times'], TRUE) : array();
			$handled = 0;
			$changed = FALSE;
			foreach($events as $queued)
			{
				$event = $queued['event'];
				$event_times = $queued['event_times'];
				if(empty($processed[$event_times]))
				{
					$type = (!empty($event['type'])) ? strtolower((string)$event['type']) : '';
					$valid = FALSE;
					if($type === 'load')
						$valid = self::apply_asset_health_load_event($state, $event);
					else if($type === 'issue' && !empty($event['result']))
					{
						$issue = (!empty($event['issue']) && is_array($event['issue'])) ? $event['issue'] : array();
						$changed = self::set_asset_health_last_issue($state, (!empty($event['event_time'])) ? (int)$event['event_time'] : time(), (string)$event['result'], (!empty($issue['label'])) ? (string)$issue['label'] : '', (!empty($issue['detail'])) ? (string)$issue['detail'] : '') || $changed;
						$valid = TRUE;
					}
					if(!$valid)
					{
						delete_option($queued['option_name']);
						continue;
					}
					if($type === 'load')
						$changed = TRUE;
					$state['processed_event_times'][] = $event_times;
					$state['processed_event_times'] = array_slice(array_values(array_unique($state['processed_event_times'])), -100);
					$processed[$event_times] = TRUE;
				}
				$handled++;
			}
			$changed = self::sync_asset_health_derived_state($state) || $changed;

			$stored = TRUE;
			if($changed || $events)
			{
				$stored = update_option('ws_plugin__s2member_assets_health_log', $state, FALSE);
				if(!$stored)
					$stored = serialize(self::asset_health_log_state()) === serialize($state); //260912.0258 update_option() also returns false when the requested value is already stored; distinguish that harmless case from a failed write before deleting queue rows.
			}
			if($stored)
			{
				//260912.0258 Delete only after the merged state and processed event-times are stored; if interrupted first, the next Logkeeper run can safely deduplicate the retained queue rows.
				foreach($events as $queued)
					delete_option($queued['option_name']);
			}

			if((!$stored || count((array)$rows) >= 100) && !wp_next_scheduled('ws_plugin__s2member_assets_health_logkeeper'))
				wp_schedule_single_event(time() + 5, 'ws_plugin__s2member_assets_health_logkeeper');
			self::health_logkeeper_lock_release($lock);
			return ($stored) ? $handled : 0;
		}

		/**
		 * Returns the page-level Okay/Fallback result for the delivery routes selected by WordPress.
		 *
		 * A normal configured route is Okay. WordPress Dynamic delivery is Fallback when it was selected
		 * only because requested static delivery or the selected s2member-o.php route could not
		 * be used. Browser activation is checked separately by the frontend activation monitor.
		 *
		 * @package s2Member\Utilities
		 * @since 260910.0630
		 *
		 * @return string `okay` or `fallback`.
		 */
		protected static function page_asset_health_load_result()
		{
			$selected_s2o = empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['dynamic_asset_loader']) || $GLOBALS['WS_PLUGIN__']['s2member']['o']['dynamic_asset_loader'] !== 'wordpress';
			//260910.0818 A page is Fallback only when WordPress had to choose Full WordPress Dynamic instead of a requested static or selected s2member-o.php route; intentionally selected Full WordPress Dynamic is Okay.
			foreach(self::$page_asset_expectations as $expectation)
			{
				$type = (!empty($expectation['type'])) ? (string)$expectation['type'] : '';
				if(!in_array($type, array('css', 'js'), TRUE))
					continue;
				if(!empty($expectation['delivery']) && $expectation['delivery'] === 'dynamic-wordpress' && (!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['static_'.$type]) || $selected_s2o))
					return 'fallback';
			}
			return 'okay';
		}

		/**
		 * Returns compact context for a non-Okay page-level delivery result.
		 *
		 * @package s2Member\Utilities
		 * @since 260911.1806
		 *
		 * @param string $result Page-level asset-load result.
		 * @return array Issue snapshot with `label` and `detail`.
		 */
		protected static function page_asset_health_issue($result = '')
		{
			$result = strtolower((string)$result);
			if($result !== 'fallback')
				return array();

			$selected_s2o = empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['dynamic_asset_loader']) || $GLOBALS['WS_PLUGIN__']['s2member']['o']['dynamic_asset_loader'] !== 'wordpress';
			foreach(self::$page_asset_expectations as $expectation)
			{
				$type = (!empty($expectation['type'])) ? (string)$expectation['type'] : '';
				if(!in_array($type, array('css', 'js'), TRUE) || empty($expectation['delivery']) || $expectation['delivery'] !== 'dynamic-wordpress')
					continue;
				if(!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['static_'.$type]))
				{
					$asset_id = (!empty($expectation['asset_id'])) ? (string)$expectation['asset_id'] : '';
					$health_id = ($asset_id !== '') ? self::asset_runtime_health_id($asset_id, $type, 'static') : '';
					return array(
						'label' => ($health_id !== '') ? self::asset_runtime_health_label($health_id) : strtoupper($type).' delivery',
						'detail' => (!empty($expectation['issue_detail'])) ? (string)$expectation['issue_detail'] : 'Requested static delivery was unavailable, so Full WordPress Dynamic fallback was used.',
					);
				}
				if($selected_s2o)
					return array(
						'label' => 'Dynamic '.(($type === 'js') ? 'JS' : 'CSS'),
						'detail' => 'The selected s2Member-Only Dynamic Loader was unavailable, so Full WordPress Dynamic fallback was used.',
					);
			}
			return array();
		}

		/**
		 * Returns Okay/Fallback/Failed for the site's currently configured delivery using trusted failure state.
		 *
		 * @package s2Member\Utilities
		 * @since 260910.0630
		 *
		 * @param array $failures Trusted current probe failures.
		 * @return string `okay`, `fallback`, or `failed`.
		 */
		protected static function asset_health_current_delivery_result($failures = array())
		{
			$failures = (is_array($failures)) ? $failures : array();
			$selected_s2o = empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['dynamic_asset_loader']) || $GLOBALS['WS_PLUGIN__']['s2member']['o']['dynamic_asset_loader'] !== 'wordpress';
			$local_health = self::static_assets_health(TRUE);
			$location = self::static_assets_location(FALSE);
			$overall_rating = 4;

			//260910.0818 Trusted current delivery takes the worse CSS/JS result: 4=preferred route works, 2=WordPress fallback works, 1=no usable route verifies; Late is browser timing evidence and is not manufactured here.
			foreach(array('css', 'js') as $type)
			{
				$type_rating = 4;
				if(!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['static_'.$type]))
				{
					$fallback = !empty($local_health['location']);
					foreach(self::static_asset_ids($type, 'all') as $id)
					{
						$state = self::static_asset_build($id);
						$definition = self::static_asset_definition($id, FALSE);
						$generation_failure = get_transient('ws_plugin__s2member_static_asset_failure_'.str_replace('.', '_', $id));
						if(empty($definition['ok']) || ($generation_failure && $state <= 0) || isset($local_health[$id]))
							$fallback = TRUE;
						if($state > 0 && !empty($location['ok']))
						{
							$base = substr($id, 0, -strlen('.'.$type));
							$url = $location['url'].'/'.$base.'-'.$state.'.'.$type;
							if(!empty($failures['static:'.$id]) && !empty($failures['static:'.$id]['url']) && (string)$failures['static:'.$id]['url'] === $url)
								$fallback = TRUE;
						}
					}
					if($fallback)
						$type_rating = (!empty($failures['fallback:dynamic_'.$type])) ? 1 : 2;
				}
				else if($selected_s2o)
				{
					$s2o_problem = !is_file(self::s2o_file_path()) || (!empty($failures['s2o']));
					if($s2o_problem)
						$type_rating = (!empty($failures['fallback:dynamic_'.$type])) ? 1 : 2;
				}
				else if(!empty($failures['dynamic:dynamic_'.$type]))
					$type_rating = 1;

				$overall_rating = min($overall_rating, $type_rating);
			}
			return ($overall_rating <= 1) ? 'failed' : (($overall_rating === 2) ? 'fallback' : 'okay');
		}

		/**
		 * Returns recent low-trust runtime suspicions reported by real frontend pages.
		 *
		 * Reports are only hints. They never change delivery by themselves. A trusted administrator-browser probe must confirm the exact asset response before persistent fallback or a confirmed notice is used.
		 *
		 * @package s2Member\Utilities
		 * @since 260904.2255
		 *
		 * @return array Current runtime suspicions.
		 */
		protected static function asset_runtime_suspicions()
		{
			$suspicions = get_option('ws_plugin__s2member_asset_runtime_suspicions', array());
			$suspicions = (is_array($suspicions)) ? $suspicions : array();
			foreach($suspicions as $key => $suspicion)
				if(empty($suspicion['reported']) || (int)$suspicion['reported'] < time() - HOUR_IN_SECONDS)
					unset($suspicions[$key]);
			return $suspicions;
		}

		/**
		 * Returns the current public asset URLs that an administrator's browser should probe.
		 *
		 * Normal checks are deliberately cheap. Static files use HEAD and s2member-o.php has a special early health response that exits before loading WordPress. A real-page suspicion adds a one-time full activation-tag check for the exact asset that page expected.
		 * A full Health-panel/recheck probe additionally verifies activation tags for the configured dynamic route,
		 * the WordPress Dynamic fallback, and static files so it can produce a fresh Okay/Fallback/Failed asset-load result.
		 *
		 * @package s2Member\Utilities
		 * @since 260904.2110
		 *
		 * @param bool $full Include current-delivery/fallback activation-tag checks for a fresh asset-load health result.
		 * @return array Health targets keyed by logical target ID.
		 */
		protected static function asset_http_health_targets($full = FALSE)
		{
			$targets = array();
			$selected_s2o = empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['dynamic_asset_loader']) || $GLOBALS['WS_PLUGIN__']['s2member']['o']['dynamic_asset_loader'] !== 'wordpress';
			if($selected_s2o && is_file(self::s2o_file_path()))
				$targets['s2o'] = array(
					'id' => 's2o',
					'url' => $GLOBALS['WS_PLUGIN__']['s2member']['c']['s2o_url'],
					'probe_url' => add_query_arg('s2member_health_check', '1', $GLOBALS['WS_PLUGIN__']['s2member']['c']['s2o_url']),
					'type' => 'health',
					'mode' => 's2o-health',
					'label' => 's2Member-Only Dynamic Loader',
					'failure_id' => 's2o',
					'failure_url' => $GLOBALS['WS_PLUGIN__']['s2member']['c']['s2o_url'],
				);

			$location = self::static_assets_location(FALSE);
			if(!empty($location['ok']))
				foreach(array('css' => 'static_css', 'js' => 'static_js') as $type => $option)
					if(!empty($GLOBALS['WS_PLUGIN__']['s2member']['o'][$option]))
						foreach(self::static_asset_ids($type, 'all') as $id)
						{
							$build = self::static_asset_build($id);
							if($build <= 0)
								continue;
							$base = substr($id, 0, -strlen('.'.$type));
							$url = $location['url'].'/'.$base.'-'.$build.'.'.$type;
							if(is_file($location['dir'].'/'.$base.'-'.$build.'.'.$type))
							{
								$target = array(
									'id' => 'static:'.$id,
									'url' => $url,
									'probe_url' => $url,
									'type' => $type,
									'mode' => ($full) ? 'activation-tag' : 'head',
									'label' => self::asset_runtime_health_label(self::asset_runtime_health_id($id, $type, 'static')),
									'failure_id' => 'static:'.$id,
									'failure_url' => $url,
								);
								if($full)
								{
									//260912.0522 Full probes validate the activation tag too; cheap background probes stay HEAD-only to avoid unnecessary body downloads.
									$health_id = self::asset_runtime_health_id($id, $type, 'static');
									$tag_value = ($type === 'css') ? (string)(int)$build : 'static-'.(int)$build;
									$target['activation_tags'] = array(self::activation_tag_snippet($health_id, $type, $tag_value));
								}
								$targets['static:'.$id] = $target;
							}
						}

			if($full)
			{
				//260910.0818 A full trusted check includes the actual dynamic response and its available WordPress fallback so Fallback can be distinguished from Failed instead of assuming a fallback works.
				foreach(array('css', 'js') as $type)
				{
					$health_id = 'dynamic_'.$type;
					$wordpress_url = self::wordpress_dynamic_asset_url();
					$wordpress_url = ($type === 'css')
						? add_query_arg(array('ws_plugin__s2member_css' => '1', 'qcABC' => '1'), $wordpress_url)
						: add_query_arg(array('ws_plugin__s2member_js_w_globals' => '1', 'qcABC' => '1'), $wordpress_url);
					$wordpress_tag_value = ($type === 'css') ? '2147483640' : 'dynamic-wordpress';
					$wordpress_target = array(
						'url' => $wordpress_url,
						'probe_url' => $wordpress_url,
						'type' => $type,
						'mode' => 'activation-tag',
						'label' => 'WP Loader '.(($type === 'css') ? 'CSS' : 'JS'),
						'activation_tags' => array(self::activation_tag_snippet($health_id, $type, $wordpress_tag_value)),
					);

					if(!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['static_'.$type]))
					{
						$target_id = 'fallback:'.$health_id;
						$wordpress_target['id'] = $target_id;
						$wordpress_target['failure_id'] = $target_id;
						$wordpress_target['failure_url'] = $wordpress_url;
						$targets[$target_id] = $wordpress_target;
					}
					else if($selected_s2o)
					{
						if(is_file(self::s2o_file_path()))
						{
							$s2o_url = ($type === 'css')
								? add_query_arg(array('ws_plugin__s2member_css' => '1', 'qcABC' => '1'), $GLOBALS['WS_PLUGIN__']['s2member']['c']['s2o_url'])
								: add_query_arg(array('ws_plugin__s2member_js_w_globals' => '1', 'qcABC' => '1'), $GLOBALS['WS_PLUGIN__']['s2member']['c']['s2o_url']);
							$s2o_tag_value = ($type === 'css') ? '2147483639' : 'dynamic-s2member-o';
							$target_id = 'active:s2member-o:'.$health_id;
							$targets[$target_id] = array(
								'id' => $target_id,
								'url' => $s2o_url,
								'probe_url' => $s2o_url,
								'type' => $type,
								'mode' => 'activation-tag',
								'label' => 's2Member-Only '.(($type === 'css') ? 'CSS' : 'JS'),
								'activation_tags' => array(self::activation_tag_snippet($health_id, $type, $s2o_tag_value)),
								'failure_id' => 's2o',
								'failure_url' => $GLOBALS['WS_PLUGIN__']['s2member']['c']['s2o_url'],
							);
						}
						$target_id = 'fallback:'.$health_id;
						$wordpress_target['id'] = $target_id;
						$wordpress_target['failure_id'] = $target_id;
						$wordpress_target['failure_url'] = $wordpress_url;
						$targets[$target_id] = $wordpress_target;
					}
					else
					{
						$target_id = 'active:'.$health_id;
						$wordpress_target['id'] = $target_id;
						$wordpress_target['failure_id'] = 'dynamic:'.$health_id;
						$wordpress_target['failure_url'] = $wordpress_url;
						$targets[$target_id] = $wordpress_target;
					}
				}
			}

			//260912.0522 Real-page Late reports remain low-trust hints; add exact URL/activation-tag targets so the administrator-browser probe can confirm or reject them without changing delivery from the report alone.
			foreach(self::asset_runtime_suspicions() as $key => $suspicion)
			{
				//260912.0522 Ignore pre-rename in-flight suspicions instead of carrying a compatibility alias for this new Beta schema.
				if(empty($suspicion['activation_tag']) || !self::asset_runtime_expectation_is_current($suspicion))
					continue;
				$id = 'runtime:'.$key;
				$failure_id = '';
				$failure_url = (string)$suspicion['url'];
				if($suspicion['delivery'] === 'dynamic-s2member-o')
				{
					$failure_id = 's2o';
					$failure_url = $GLOBALS['WS_PLUGIN__']['s2member']['c']['s2o_url'];
				}
				else if($suspicion['delivery'] === 'static' && !empty($suspicion['asset_id']))
					$failure_id = 'static:'.$suspicion['asset_id'];
				else
					$failure_id = 'dynamic:'.(string)$suspicion['id'];

				$targets[$id] = array(
					'id' => $id,
					'url' => (string)$suspicion['url'],
					'probe_url' => (string)$suspicion['url'],
					'type' => (string)$suspicion['type'],
					'mode' => 'activation-tag',
					'label' => self::asset_runtime_health_label((string)$suspicion['id']),
					'activation_tags' => array((string)$suspicion['activation_tag']),
					'failure_id' => $failure_id,
					'failure_url' => $failure_url,
					'suspicion_key' => $key,
					'suspicion' => $suspicion,
				);
			}
			return $targets;
		}

		/**
		 * Returns a stable hash for the current browser health targets.
		 *
		 * @package s2Member\Utilities
		 * @since 260904.2110
		 *
		 * @param array $targets Current health targets.
		 * @return string Target hash.
		 */
		protected static function asset_http_health_target_hash($targets = array())
		{
			$hash = array();
			foreach((array)$targets as $id => $target)
				$hash[$id] = array((string)$target['url'], (string)$target['type'], (string)$target['mode'], (!empty($target['activation_tags'])) ? array_values((array)$target['activation_tags']) : array());
			return md5(serialize($hash));
		}

		/**
		 * Upgrades the frontend Asset Health format once per site.
		 *
		 * Existing timestamped files remain available to already-cached HTML, while fresh pages
		 * regenerate active static assets with one activation tag per physical response.
		 *
		 * @package s2Member\Utilities
		 * @since 260909.2015
		 *
		 * @return null
		 */
		public static function maybe_upgrade_asset_health_format()
		{
			if((string)get_option('ws_plugin__s2member_asset_health_format_version', '') === '3')
				return;

			//260912.0522 Reset the first-v260909 activation-tag/build and health-history state together so old component tags and pre-score load history cannot bleed into the physical-file scoring model.
			self::reset_static_asset_builds();
			delete_option('ws_plugin__s2member_asset_runtime_suspicions');
			delete_option('ws_plugin__s2member_asset_http_health');
			delete_option('ws_plugin__s2member_asset_attention_state');
			delete_option('ws_plugin__s2member_assets_health_log');
			delete_option('ws_plugin__s2member_asset_notice_dismissed');
			self::$asset_http_health_cache = NULL;
			update_option('ws_plugin__s2member_asset_health_format_version', '3', FALSE);
			return;
		}

		/**
		 * Returns the runtime-health ID for one physical frontend asset response.
		 *
		 * @package s2Member\Utilities
		 * @since 260909.2015
		 *
		 * @param string $asset_id Static logical filename, or an empty string for dynamic delivery.
		 * @param string $type `css` or `js`.
		 * @param string $delivery Delivery mode.
		 * @return string Runtime-health ID.
		 */
		protected static function asset_runtime_health_id($asset_id = '', $type = '', $delivery = '')
		{
			$type = strtolower((string)$type);
			if(!in_array($type, array('css', 'js'), TRUE))
				return '';
			if($delivery === 'static')
			{
				$base = substr((string)$asset_id, 0, -strlen('.'.$type));
				$base = str_replace('-', '_', strtolower($base));
				return ($base) ? $base.'_'.$type : '';
			}
			return 'dynamic_'.$type;
		}

		/**
		 * Returns a site-owner-friendly label for one runtime-health ID.
		 *
		 * @package s2Member\Utilities
		 * @since 260909.2015
		 *
		 * @param string $id Runtime-health ID.
		 * @return string Human-readable label.
		 */
		protected static function asset_runtime_health_label($id = '')
		{
			$combined = !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['static_assets_combine']) && (defined('WS_PLUGIN__S2MEMBER_PRO_VERSION') || isset($GLOBALS['WS_PLUGIN__']['s2member_pro']));
			$labels = array(
				's2member_css' => ($combined) ? 'Combined CSS' : 'Framework CSS',
				's2member_pro_css' => 'Pro CSS',
				's2member_js' => ($combined) ? 'Combined JS' : 'Framework JS',
				's2member_pro_js' => 'Pro JS',
				'dynamic_css' => 'Dynamic CSS',
				'dynamic_js' => 'Dynamic JS',
			);
			return isset($labels[$id]) ? $labels[$id] : (string)$id;
		}

		/**
		 * Returns the activation-tag snippet used to verify one physical frontend asset response.
		 *
		 * @package s2Member\Utilities
		 * @since 260909.2015
		 *
		 * @param string $id Runtime-health ID.
		 * @param string $type `css` or `js`.
		 * @param string $tag_value Value the activation tag is expected to expose.
		 * @return string Activation-tag source snippet.
		 */
		protected static function activation_tag_snippet($id = '', $type = '', $tag_value = '')
		{
			if($type === 'css')
				return '#ws-plugin--s2member-asset-health-'.str_replace('_', '-', (string)$id).'{z-index:'.(string)$tag_value.'!important}';
			return 'ws_plugin__s2member_asset_health["'.(string)$id.'"]="'.(string)$tag_value.'"';
		}

		/**
		 * Returns the activation-tag snippet appended to a generated static asset.
		 *
		 * @package s2Member\Utilities
		 * @since 260904.2255
		 *
		 * @param string $id Stable generated asset identifier without extension.
		 * @param string $type `css` or `js`.
		 * @param int $build Generated build timestamp.
		 * @return string Activation-tag snippet.
		 */
		protected static function static_activation_tag_snippet($id = '', $type = '', $build = 0)
		{
			//260912.0522 Activation-tag identity follows the physical response, not Framework/Pro logical components, so a combined file produces one tag and one possible Late report.
			$health_id = self::asset_runtime_health_id($id.'.'.$type, $type, 'static');
			$tag_value = ($type === 'css') ? (string)(int)$build : 'static-'.(int)$build;
			$activation_tag = self::activation_tag_snippet($health_id, $type, $tag_value);
			if($type === 'css')
				return $activation_tag;
			return ';window.ws_plugin__s2member_asset_health=window.ws_plugin__s2member_asset_health||{};window.'.$activation_tag.';';
		}

		/**
		 * Returns the activation-tag snippet appended to dynamically generated CSS or JavaScript.
		 *
		 * @package s2Member\Utilities
		 * @since 260904.2255
		 *
		 * @param string $type `css` or `js`.
		 * @return string Activation-tag snippet.
		 */
		public static function dynamic_activation_tag_snippet($type = '')
		{
			$type = strtolower((string)$type);
			if(!in_array($type, array('css', 'js'), TRUE))
				return '';
			$delivery = (defined('_WS_PLUGIN__S2MEMBER_ONLY')) ? 'dynamic-s2member-o' : 'dynamic-wordpress';
			$health_id = self::asset_runtime_health_id('', $type, $delivery);
			$tag_value = ($type === 'css') ? (($delivery === 'dynamic-s2member-o') ? '2147483639' : '2147483640') : $delivery;
			$activation_tag = self::activation_tag_snippet($health_id, $type, $tag_value);
			if($type === 'css')
				return "\n".$activation_tag."\n";
			return "\n;window.ws_plugin__s2member_asset_health=window.ws_plugin__s2member_asset_health||{};window.".$activation_tag.";\n";
		}

		/**
		 * Registers the exact CSS/JavaScript activation tags expected on the current frontend page.
		 * Each call represents one physical response, so combined Framework+Pro delivery registers one activation tag for that combined file instead of one tag per logical component.
		 *
		 * @package s2Member\Utilities
		 * @since 260904.2255
		 *
		 * @param string $asset_id Logical static asset ID, or an empty string for dynamic delivery.
		 * @param string $type `css` or `js`.
		 * @param string $url Public URL emitted on this page.
		 * @param string $delivery `static`, `dynamic-s2member-o`, or `dynamic-wordpress`.
		 * @param int $build Static build timestamp, or zero for dynamic delivery.
		 * @param string $issue_detail Optional reason a preferred route fell back before this response was selected.
		 * @return null
		 */
		public static function register_page_asset_expectations($asset_id = '', $type = '', $url = '', $delivery = '', $build = 0, $issue_detail = '')
		{
			$type = strtolower((string)$type);
			$url = (string)$url;
			if(!in_array($type, array('css', 'js'), TRUE) || !$url || !in_array($delivery, array('static', 'dynamic-s2member-o', 'dynamic-wordpress'), TRUE))
				return;
			$id = self::asset_runtime_health_id($asset_id, $type, $delivery);
			if(!$id)
				return;
			if($delivery === 'static')
				$tag_value = ($type === 'css') ? (string)(int)$build : 'static-'.(int)$build;
			else
				$tag_value = ($type === 'css') ? (($delivery === 'dynamic-s2member-o') ? '2147483639' : '2147483640') : $delivery;
			$expectation = array(
				'id' => $id,
				'asset_id' => (string)$asset_id,
				'type' => $type,
				'url' => $url,
				'delivery' => $delivery,
				'tag_value' => $tag_value,
				'activation_tag' => self::activation_tag_snippet($id, $type, $tag_value),
				//260911.1806 Server-side fallback context is not sent to the browser; it only supplies a useful Last issue snapshot for the page that selected fallback.
				'issue_detail' => substr(wp_strip_all_tags((string)$issue_detail), 0, 240),
			);
			$expectation['signature'] = self::asset_runtime_expectation_signature($expectation);
			self::$page_asset_expectations[$id] = $expectation;
			return;
		}

		/**
		 * Expands one compact browser runtime expectation into the full signed structure.
		 *
		 * Frontend pages only need a few fields to check asset activation. Reconstruct the
		 * descriptive fields here when a miss is actually reported, keeping healthy page source small.
		 * Recovery fields from the first v260909 monitor are intentionally no longer part of current expectations because Late results no longer trigger speculative fallback injection.
		 *
		 * @package s2Member\Utilities
		 * @since 260905.0106
		 *
		 * @param array $compact Compact expectation fields.
		 * @return array Full expectation, or an empty array when invalid.
		 */
		protected static function expand_asset_runtime_expectation($compact = array())
		{
			if(!is_array($compact) || count($compact) < 6)
				return array();
			$id = isset($compact[0]) ? (string)$compact[0] : '';
			$asset_id = isset($compact[1]) ? (string)$compact[1] : '';
			$url = isset($compact[2]) ? (string)$compact[2] : '';
			$delivery = isset($compact[3]) ? (string)$compact[3] : '';
			$tag_value = isset($compact[4]) ? (string)$compact[4] : '';
			$signature = isset($compact[5]) ? (string)$compact[5] : '';
			if(!preg_match('/\A(?:s2member(?:_pro)?|dynamic)_(css|js)\z/', $id, $match))
				return array(); //260912.0522 Cached pages using the first-v260909 component-level activation-tag IDs are intentionally stale after the Asset Health format upgrade.
			$type = $match[1];
			return array(
				'id' => $id,
				'asset_id' => $asset_id,
				'type' => $type,
				'url' => $url,
				'delivery' => $delivery,
				'tag_value' => $tag_value,
				'activation_tag' => self::activation_tag_snippet($id, $type, $tag_value),
				'signature' => $signature,
			);
		}

		/**
		 * Returns a signature for one low-trust runtime expectation report.
		 *
		 * @package s2Member\Utilities
		 * @since 260904.2255
		 *
		 * @param array $expectation Runtime expectation fields.
		 * @return string Signature.
		 */
		protected static function asset_runtime_expectation_signature($expectation = array())
		{
			$parts = array();
			foreach(array('id', 'asset_id', 'type', 'url', 'delivery', 'tag_value', 'activation_tag') as $key)
				$parts[$key] = isset($expectation[$key]) ? (string)$expectation[$key] : '';
			return hash_hmac('sha256', serialize($parts), wp_salt('nonce'));
		}

		/**
		 * Returns true when a reported expectation still describes the site's current delivery state.
		 *
		 * @package s2Member\Utilities
		 * @since 260904.2255
		 *
		 * @param array $expectation Runtime expectation.
		 * @return bool True when current.
		 */
		protected static function asset_runtime_expectation_is_current($expectation = array())
		{
			if(empty($expectation['url']) || empty($expectation['delivery']) || empty($expectation['type']))
				return FALSE;
			if($expectation['delivery'] === 'static')
			{
				$id = (string)$expectation['asset_id'];
				$type = (string)$expectation['type'];
				if(!in_array($id, self::static_asset_ids($type, 'all'), TRUE))
					return FALSE;
				$build = self::static_asset_build($id);
				$location = self::static_assets_location(FALSE);
				$base = substr($id, 0, -strlen('.'.$type));
				return !empty($location['ok']) && $build > 0 && (string)$expectation['url'] === $location['url'].'/'.$base.'-'.$build.'.'.$type;
			}
			if($expectation['delivery'] === 'dynamic-s2member-o')
				return (empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['dynamic_asset_loader']) || $GLOBALS['WS_PLUGIN__']['s2member']['o']['dynamic_asset_loader'] !== 'wordpress') && strpos((string)$expectation['url'], $GLOBALS['WS_PLUGIN__']['s2member']['c']['s2o_url'].'?') === 0;
			if($expectation['delivery'] === 'dynamic-wordpress')
				return strpos((string)$expectation['url'], self::wordpress_dynamic_asset_url().'?') === 0;
			return FALSE;
		}

		/**
		 * Returns active generated frontend asset IDs for one type/component.
		 *
		 * Framework and Pro files stay separate by default. Combined mode reuses the Framework ID because that file becomes the combined representation.
		 *
		 * @package s2Member\Utilities
		 * @since 260903.1918
		 *
		 * @param string $type      `css` or `js`.
		 * @param string $component `all`, `framework`, or `pro`.
		 * @return array Logical generated filenames.
		 */
		public static function static_asset_ids($type = '', $component = 'all')
		{
			$type = strtolower((string)$type);
			$component = strtolower((string)$component);
			if(!in_array($type, array('css', 'js'), TRUE) || !in_array($component, array('all', 'framework', 'pro'), TRUE))
				return array();

			$framework = 's2member.'.$type;
			$pro = (!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['static_assets_combine'])) ? $framework : 's2member-pro.'.$type;
			if($component === 'framework')
				return array($framework);
			if($component === 'pro')
				return array($pro);

			$ids = array($framework);
			if((defined('WS_PLUGIN__S2MEMBER_PRO_VERSION') || isset($GLOBALS['WS_PLUGIN__']['s2member_pro'])) && $pro !== $framework)
				$ids[] = $pro;
			return $ids;
		}

		/**
		 * Returns how s2Member text used by static JavaScript should be delivered.
		 *
		 * @package s2Member\Utilities
		 * @since 260906.2049
		 *
		 * @return string `static` to include text in generated JavaScript, or `page` to load it with each WordPress page.
		 */
		public static function static_js_text_delivery()
		{
			return (!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['static_js_text']) && $GLOBALS['WS_PLUGIN__']['s2member']['o']['static_js_text'] === 'page') ? 'page' : 'static';
		}

		/**
		 * Determines whether page-loaded JavaScript text is supported by the active Framework/Pro combination.
		 *
		 * Framework can always load its own text with the page. Pro explicitly advertises support because
		 * older Pro releases predate the shipped static-JS data map needed for this delivery mode.
		 *
		 * @package s2Member\Utilities
		 * @since 260906.2049
		 *
		 * @return bool True if page-loaded JavaScript text is available.
		 */
		public static function static_js_page_text_supported()
		{
			if(!c_ws_plugin__s2member_utils_conds::pro_is_installed())
				return TRUE;
			return (bool)apply_filters('ws_plugin__s2member_static_js_page_text_supported', FALSE);
		}

		/**
		 * Determines whether the active Pro JavaScript hook contains only built-in callbacks safe to cache in a static file.
		 *
		 * This lets a newer Framework retain static-file delivery with older Pro releases that predate
		 * the static-source filters. Unknown, reordered, or deprecated gateway callbacks remain dynamic.
		 *
		 * @package s2Member\Utilities
		 * @since 260906.2049
		 *
		 * @return bool True if the active Pro hook can be captured safely.
		 */
		protected static function static_js_builtin_pro_callbacks_supported()
		{
			if(!c_ws_plugin__s2member_utils_conds::pro_is_installed() || has_filter('ws_plugin__s2member_pro_available_gateways'))
				return FALSE;
			$built_ins = array(
				'c_ws_plugin__s2member_pro_css_js::js_w_globals',
				'c_ws_plugin__s2member_pro_paypal_css_js::paypal_js_w_globals',
				'c_ws_plugin__s2member_pro_stripe_css_js::stripe_js_w_globals',
				'c_ws_plugin__s2member_pro_authnet_css_js::authnet_js_w_globals',
				'c_ws_plugin__s2member_pro_clickbank_css_js::clickbank_js_w_globals',
			);
			$callbacks = isset($GLOBALS['wp_filter']['ws_plugin__s2member_during_js_w_globals']) ? $GLOBALS['wp_filter']['ws_plugin__s2member_during_js_w_globals'] : array();
			if(is_object($callbacks) && isset($callbacks->callbacks))
				$callbacks = $callbacks->callbacks;
			$found = FALSE;
			foreach((array)$callbacks as $priority => $priority_callbacks)
				foreach((array)$priority_callbacks as $callback)
				{
					if((int)$priority !== 10 || !is_array($callback) || !isset($callback['function'], $callback['accepted_args']) || !in_array($callback['function'], $built_ins, TRUE) || (int)$callback['accepted_args'] !== 1)
						return FALSE;
					$found = TRUE;
				}
			return $found;
		}

		/**
		 * Captures built-in Pro JavaScript from an older Pro release for static-file text delivery.
		 *
		 * @package s2Member\Utilities
		 * @since 260906.2049
		 *
		 * @return string Captured Pro JavaScript, or an empty string if the hook is not safely cacheable.
		 */
		protected static function static_js_builtin_pro_output()
		{
			if(!self::static_js_builtin_pro_callbacks_supported())
				return '';
			//260906.2256 Match the template variables passed by the normal dynamic loader when capturing older Pro callbacks.
			$u = $GLOBALS['WS_PLUGIN__']['s2member']['c']['dir_url'];
			$i = $u.'/src/images';
			ob_start();
			do_action('ws_plugin__s2member_during_js_w_globals', get_defined_vars());
			return (string)ob_get_clean();
		}

		/**
		 * Returns shipped static JavaScript data maps used by one generated static JavaScript file.
		 *
		 * Framework owns its data map. Pro appends its independent data map through a filter so the two
		 * release packages never need cross-repo slot coordination.
		 *
		 * @package s2Member\Utilities
		 * @since 260906.0738
		 *
		 * @param string $id Logical generated JavaScript filename.
		 * @return array Data-map paths keyed by the compact browser-data namespace.
		 */
		protected static function static_js_data_map_paths($id = '')
		{
			if(self::static_js_text_delivery() !== 'page')
				return array();
			$id = strtolower((string)$id);
			$paths = array();
			if($id === 's2member.js')
				$paths['f'] = $GLOBALS['WS_PLUGIN__']['s2member']['c']['dir'].'/src/includes/s2member.js.php';
			$paths = (array)apply_filters('ws_plugin__s2member_static_js_data_map_paths', $paths, $id, get_defined_vars());
			foreach($paths as $key => $path)
				if(!preg_match('/^[a-z][a-z0-9_]*$/i', (string)$key) || !(string)$path)
					unset($paths[$key]);
			return $paths;
		}

		/**
		 * Parses one shipped static JavaScript data map into an exact expression-to-slot lookup.
		 *
		 * @package s2Member\Utilities
		 * @since 260906.0738
		 *
		 * @param string $path Data-map path.
		 * @return array Parse result.
		 */
		protected static function static_js_data_map($path = '')
		{
			$path = (string)$path;
			if(isset(self::$static_js_data_map_cache[$path]))
				return self::$static_js_data_map_cache[$path];
			if(!$path || !is_readable($path) || ($source = file_get_contents($path)) === FALSE)
				return self::$static_js_data_map_cache[$path] = array('ok' => FALSE, 'slots' => array(), 'hash' => '', 'error' => 'Static JavaScript data map is not readable: '.$path);

			$slots = array();
			if(!preg_match_all('/\\$data\\[(\\d+)\\]\\s*=\\s*\\/\\*d\\*\\/(.*?)\\/\\*b\\*\\/;/s', $source, $matches, PREG_SET_ORDER))
				return self::$static_js_data_map_cache[$path] = array('ok' => FALSE, 'slots' => array(), 'hash' => '', 'error' => 'Static JavaScript data map contains no marked entries: '.$path);
			foreach($matches as $index => $match)
			{
				$slot = (int)$match[1];
				$expression = trim((string)$match[2]);
				if($slot !== $index || !$expression || isset($slots[$expression]))
					return self::$static_js_data_map_cache[$path] = array('ok' => FALSE, 'slots' => array(), 'hash' => '', 'error' => 'Static JavaScript data-map slots are invalid or duplicated: '.$path);
				$slots[$expression] = $slot;
			}
			return self::$static_js_data_map_cache[$path] = array('ok' => TRUE, 'slots' => $slots, 'hash' => hash('sha256', $source), 'error' => '');
		}

		/**
		 * Returns the current shipped data-map signature for one static JavaScript representation.
		 *
		 * @package s2Member\Utilities
		 * @since 260906.0738
		 *
		 * @param string $id Logical generated JavaScript filename.
		 * @return array Signature result.
		 */
		protected static function static_js_data_map_signature($id = '')
		{
			$hashes = array();
			foreach(self::static_js_data_map_paths($id) as $key => $path)
			{
				$data_map = self::static_js_data_map($path);
				if(empty($data_map['ok']))
					return array('ok' => FALSE, 'signature' => '', 'error' => (string)$data_map['error']);
				$hashes[(string)$key] = (string)$data_map['hash'];
			}
			if(!$hashes)
				return array('ok' => FALSE, 'signature' => '', 'error' => 'No static JavaScript data map is available for '.$id);
			return array('ok' => TRUE, 'signature' => hash('sha256', wp_json_encode($hashes)), 'error' => '');
		}

		/**
		 * Returns the data-map signature saved with the active generated JavaScript file.
		 *
		 * @package s2Member\Utilities
		 * @since 260906.0738
		 *
		 * @param string $id Logical generated JavaScript filename.
		 * @return string Saved signature.
		 */
		protected static function static_asset_data_map_signature($id = '')
		{
			$signatures = get_option('ws_plugin__s2member_static_asset_data_map_signatures', array());
			return (is_array($signatures) && isset($signatures[$id])) ? (string)$signatures[$id] : '';
		}

		/**
		 * Saves the data-map signature paired with one generated JavaScript file.
		 *
		 * @package s2Member\Utilities
		 * @since 260906.0738
		 *
		 * @param string $id        Logical generated JavaScript filename.
		 * @param string $signature Current data-map signature.
		 * @return null
		 */
		protected static function set_static_asset_data_map_signature($id = '', $signature = '')
		{
			if(!in_array($id, array('s2member.js', 's2member-pro.js'), TRUE))
				return;
			$signatures = get_option('ws_plugin__s2member_static_asset_data_map_signatures', array());
			$signatures = is_array($signatures) ? $signatures : array();
			$signatures[$id] = (string)$signature;
			update_option('ws_plugin__s2member_static_asset_data_map_signatures', $signatures);
			return;
		}

		/**
		 * Returns the signed build timestamp for one generated frontend asset file.
		 *
		 * Positive values are current. Negative values preserve the previous timestamp while marking that exact file stale.
		 *
		 * @package s2Member\Utilities
		 * @since 260903.0525
		 *
		 * @param string $id Logical generated filename, e.g. `s2member.js` or `s2member-pro.css`.
		 * @return int Signed build timestamp.
		 */
		public static function static_asset_build($id = '')
		{
			$id = strtolower((string)$id);
			if(in_array($id, array('css', 'js'), TRUE))
				$id = 's2member.'.$id;
			$builds = get_option('ws_plugin__s2member_static_asset_builds', array());
			return (in_array($id, array('s2member.css', 's2member-pro.css', 's2member.js', 's2member-pro.js'), TRUE) && is_array($builds) && isset($builds[$id])) ? (int)$builds[$id] : 0;
		}

		/**
		 * Updates the signed build timestamp for one generated frontend asset file.
		 *
		 * @package s2Member\Utilities
		 * @since 260903.0525
		 *
		 * @param string $id    Logical generated filename.
		 * @param int    $build Signed build timestamp.
		 * @return null
		 */
		protected static function set_static_asset_build($id = '', $build = 0)
		{
			$id = strtolower((string)$id);
			if(!in_array($id, array('s2member.css', 's2member-pro.css', 's2member.js', 's2member-pro.js'), TRUE))
				return;
			$builds = get_option('ws_plugin__s2member_static_asset_builds', array());
			$builds = is_array($builds) ? $builds : array();
			//260903.1918 Build state is keyed by the actual logical generated filename; old type-only beta keys are discarded on the next successful state write.
			$builds = array_intersect_key($builds, array_flip(array('s2member.css', 's2member-pro.css', 's2member.js', 's2member-pro.js')));
			$builds[$id] = (int)$build;
			update_option('ws_plugin__s2member_static_asset_builds', $builds);
			unset(self::$static_asset_cache[$id]);
			self::$static_assets_health_cache = NULL;
			delete_option('ws_plugin__s2member_static_asset_health');
			return;
		}

		/**
		 * Clears generated frontend asset build state when the active file representation changes.
		 *
		 * Existing timestamped files remain on disk for already-cached HTML; fresh requests generate only the newly active representation.
		 *
		 * @package s2Member\Utilities
		 * @since 260903.1918
		 *
		 * @return null
		 */
		protected static function reset_static_asset_builds()
		{
			delete_option('ws_plugin__s2member_static_asset_builds');
			delete_option('ws_plugin__s2member_static_asset_data_map_signatures'); //260906.1530 Static JavaScript and its data-map slot layout must stay synchronized.
			delete_option('ws_plugin__s2member_static_asset_health');
			self::$static_asset_cache = array();
			self::$static_js_data_map_cache = array();
			self::$static_assets_health_cache = NULL;
			foreach(array('s2member.css', 's2member-pro.css', 's2member.js', 's2member-pro.js') as $id)
				delete_transient('ws_plugin__s2member_static_asset_failure_'.str_replace('.', '_', $id));
			return;
		}

		/**
		 * Invalidates selected generated frontend assets.
		 *
		 * Selectors may be `css`, `js`, `framework_css`, `framework_js`, `pro_css`, `pro_js`, or exact logical generated filenames.
		 * Existing files remain available for already-cached HTML. Current pages stop referencing a stale generation until its replacement succeeds.
		 *
		 * @package s2Member\Utilities
		 * @since 260903.0437
		 *
		 * @param array|string $assets Asset selectors.
		 * @return null
		 */
		public static function invalidate_static_assets($assets = array('css', 'js'))
		{
			$assets = is_array($assets) ? $assets : array($assets);
			$ids = array();
			foreach($assets as $asset)
			{
				$asset = strtolower((string)$asset);
				if(in_array($asset, array('css', 'js'), TRUE))
					$ids = array_merge($ids, self::static_asset_ids($asset, 'all'));
				else if(preg_match('/^(framework|pro)_(css|js)$/', $asset, $match))
					$ids = array_merge($ids, self::static_asset_ids($match[2], $match[1]));
				else if(in_array($asset, array('s2member.css', 's2member-pro.css', 's2member.js', 's2member-pro.js'), TRUE))
					$ids[] = $asset;
			}
			foreach(array_unique($ids) as $id)
			{
				$build = self::static_asset_build($id);
				//260903.1918 Preserve an existing file's timestamp while marking only that file stale; never create build-state entries for files that have not yet been generated.
				if($build)
					self::set_static_asset_build($id, -abs($build));
				else
					unset(self::$static_asset_cache[$id]);
				delete_transient('ws_plugin__s2member_static_asset_failure_'.str_replace('.', '_', $id));
			}
			return;
		}

		/**
		 * Invalidates generated assets when s2Member or WordPress values rendered into them change.
		 *
		 * @package s2Member\Utilities
		 * @since 260903.0437
		 *
		 * @param string $option Option name.
		 * @param mixed  $old_value Previous value.
		 * @param mixed  $value New value.
		 * @return null
		 */
		public static function maybe_invalidate_after_wp_option_update($option = '', $old_value = NULL, $value = NULL)
		{
			if($old_value === $value)
				return;

			if((string)$option === 'ws_plugin__s2member_options')
			{
				if(!is_array($old_value) || !is_array($value))
					return;
				$old = (array)$old_value;
				$new = (array)$value;

				//260907.2203 Keep a concise operational history of CSS/JS configuration changes when s2Member logging is enabled.
				$config_changes = array();
				//260912.0522 Include wait-time changes because they can explain a sudden change in Late asset loads even when delivery settings themselves did not change.
				foreach(array('dynamic_asset_loader', 'static_css', 'static_css_minify', 'static_js', 'static_js_text', 'static_js_minify', 'static_assets_combine', 'asset_health_wait_seconds') as $key)
					if(serialize(isset($old[$key]) ? $old[$key] : NULL) !== serialize(isset($new[$key]) ? $new[$key] : NULL))
						$config_changes[$key] = array('old' => isset($old[$key]) ? $old[$key] : NULL, 'new' => isset($new[$key]) ? $new[$key] : NULL);
				if($config_changes)
					c_ws_plugin__s2member_utils_logs::log_entry('css-js', array('event' => 'CSS/JS configuration changed', 'changes' => $config_changes));

				if((string)(isset($old['static_assets_combine']) ? $old['static_assets_combine'] : '0') !== (string)(isset($new['static_assets_combine']) ? $new['static_assets_combine'] : '0'))
				{
					//260911.1834 A combine-mode change resets both representations; queue enabled CSS/JS for immediate rebuilding after the complete new option set has finished saving.
					self::$static_assets_rebuild_after_save = array('css', 'js');
					self::reset_static_asset_builds();
					return;
				}

				$keys = array(
					'css' => array('static_css', 'static_css_minify'),
					'js' => array('static_js', 'static_js_text', 'static_js_minify'),
					'framework_js' => array('custom_reg_force_personal_emails', 'custom_reg_password_min_length', 'custom_reg_password_min_strength'),
					'pro_css' => array('pro_gateways_enabled'),
					'pro_js' => array(
						'pro_gateways_enabled', 'pro_stripe_api_publishable_key', 'pro_stripe_api_image', 'pro_stripe_api_allow_remember_me',
						'paypal_checkout_enable', 'paypal_checkout_sandbox', 'paypal_checkout_client_id', 'paypal_checkout_sandbox_client_id', 'sec_encryption_key',
					),
				);
				$keys = (array)apply_filters('ws_plugin__s2member_static_asset_option_keys', $keys, get_defined_vars());
				$invalidate = array();
				foreach($keys as $selector => $option_keys)
					foreach((array)$option_keys as $key)
						if((isset($old[$key]) || isset($new[$key])) && serialize(isset($old[$key]) ? $old[$key] : NULL) !== serialize(isset($new[$key]) ? $new[$key] : NULL))
						{
							$invalidate[] = $selector;
							break;
						}
				if($invalidate)
				{
					//260911.1834 Remember only the affected types until update_all_options has finished; rebuilding here could still see the request's old global option set.
					foreach($invalidate as $_static_asset_selector)
						foreach(array('css', 'js') as $_static_asset_type)
							if(strpos((string)$_static_asset_selector, $_static_asset_type) !== FALSE)
								self::$static_assets_rebuild_after_save[] = $_static_asset_type;
					self::$static_assets_rebuild_after_save = array_values(array_unique(self::$static_assets_rebuild_after_save));
					self::invalidate_static_assets($invalidate);
				}
				return;
			}
			if(in_array((string)$option, array('siteurl', 'home'), TRUE))
				self::invalidate_static_assets(array('css', 'js'));
			else if((string)$option === 'WPLANG' && self::static_js_text_delivery() !== 'page')
				self::invalidate_static_assets('js');
			return;
		}

		/**
		 * Rebuilds enabled static asset types after s2Member finishes saving relevant options.
		 *
		 * @package s2Member\Utilities
		 * @since 260911.1834
		 *
		 * @param array $vars Variables passed by ws_plugin__s2member_after_update_all_options.
		 * @return null
		 */
		public static function rebuild_static_assets_after_options_save($vars = array())
		{
			if(empty($vars['updated_all_options']) || !self::$static_assets_rebuild_after_save)
				return;

			$types = array_values(array_unique(self::$static_assets_rebuild_after_save));
			self::$static_assets_rebuild_after_save = array();
			$options = (!empty($vars['options']) && is_array($vars['options'])) ? $vars['options'] : get_option('ws_plugin__s2member_options', array());
			if(!is_array($options))
				return;

			//260911.1834 Build from the complete newly saved option set; lazy frontend generation remains the recovery path for later upgrades, deletions, or transient failures.
			$previous_options = $GLOBALS['WS_PLUGIN__']['s2member']['o'];
			$GLOBALS['WS_PLUGIN__']['s2member']['o'] = $options;
			$results = array();
			foreach($types as $type)
				if(in_array($type, array('css', 'js'), TRUE) && !empty($options['static_'.$type]))
					$results[$type] = self::ensure_static_assets($type);
			$GLOBALS['WS_PLUGIN__']['s2member']['o'] = $previous_options;

			if($results)
				c_ws_plugin__s2member_utils_logs::log_entry('css-js', array('event' => 'Static CSS/JS rebuilt after option save', 'result' => 'completed', 'types' => array_keys($results)));
			return;
		}

		/**
		 * Rebuilds enabled static assets opportunistically on normal privileged administrator page-loads.
		 *
		 * Routine requests only perform a few build-state/filesystem checks. Actual generation runs only when an active asset is pending, has never been generated, or its current local file is missing.
		 *
		 * @package s2Member\Utilities
		 * @since 260911.1924
		 *
		 * @return null
		 */
		public static function maybe_rebuild_static_assets_on_admin_request()
		{
			if(!is_admin() || !current_user_can('create_users') || (defined('DOING_AJAX') && DOING_AJAX))
				return;
			if(empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['static_css']) && empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['static_js']))
				return;

			$location = self::static_assets_location(FALSE);
			foreach(array('css' => 'static_css', 'js' => 'static_js') as $type => $option)
			{
				if(empty($GLOBALS['WS_PLUGIN__']['s2member']['o'][$option]))
					continue;
				$needs_rebuild = FALSE;
				foreach(self::static_asset_ids($type, 'all') as $id)
				{
					$build = self::static_asset_build($id);
					if($build <= 0)
					{
						$needs_rebuild = TRUE;
						continue;
					}
					if(!empty($location['ok']))
					{
						$base = substr($id, 0, -strlen('.'.$type));
						if(!is_file($location['dir'].'/'.$base.'-'.$build.'.'.$type))
						{
							$needs_rebuild = TRUE;
						}
					}
				}
				//260911.2325 Generation/failure cooldown, missing-file repair locking, and repaired-issue history are centralized in ensure_static_asset(); avoid a second history write from the admin recovery wrapper.
				if($needs_rebuild)
					self::ensure_static_assets($type);
			}
			return;
		}

		/**
		 * Invalidates generated frontend assets when plugin activation/deactivation can change frontend integrations.
		 *
		 * @package s2Member\Utilities
		 * @since 260903.0437
		 *
		 * @return null
		 */
		public static function invalidate_after_plugin_change($plugin = '')
		{
			$plugin = (string)$plugin;
			if($plugin === 's2member-pro/s2member-pro.php')
				self::reset_static_asset_builds();
			else if($plugin === 'buddypress/bp-loader.php')
				self::invalidate_static_assets('framework_js');
			return;
		}

		/**
		 * Invalidates generated frontend assets after plugin/translation upgrades.
		 *
		 * @package s2Member\Utilities
		 * @since 260903.0437
		 *
		 * @param object $upgrader WordPress upgrader instance.
		 * @param array  $options Upgrade details.
		 * @return null
		 */
		public static function maybe_invalidate_after_upgrade($upgrader = NULL, $options = array())
		{
			if(!is_array($options) || empty($options['type']))
				return;
			if($options['type'] === 'translation')
			{
				if(self::static_js_text_delivery() !== 'page')
					self::invalidate_static_assets('js'); //260906.2049 Page-loaded JavaScript text follows the current translation without rebuilding the external static file.
				return;
			}
			if($options['type'] !== 'plugin')
				return;

			$plugins = array();
			if(!empty($options['plugin']))
				$plugins[] = (string)$options['plugin'];
			if(!empty($options['plugins']) && is_array($options['plugins']))
				$plugins = array_merge($plugins, $options['plugins']);
			foreach(array_unique($plugins) as $plugin)
			{
				if($plugin === 's2member/s2member.php')
					self::invalidate_static_assets(array('framework_css', 'framework_js'));
				else if($plugin === 's2member-pro/s2member-pro.php')
					self::invalidate_static_assets(array('pro_css', 'pro_js'));
				else if($plugin === 'buddypress/bp-loader.php')
					self::invalidate_static_assets('framework_js');
			}
			return;
		}

		/**
		 * Returns one current generated frontend asset URL, building it when stale/uninitialized.
		 *
		 * Active timestamped files are existence-checked before their URLs are emitted. A missing or
		 * trusted-browser-confirmed unreachable file returns a failure so callers can use dynamic delivery immediately.
		 *
		 * @package s2Member\Utilities
		 * @since 260903.0525
		 *
		 * @param string $id    Logical generated filename.
		 * @param bool   $force Force a new build timestamp immediately.
		 * @return array Result with `ok`, `url`, `build`, and `error` keys.
		 */
		public static function ensure_static_asset($id = '', $force = FALSE)
		{
			$id = strtolower((string)$id);
			if(in_array($id, array('css', 'js'), TRUE))
				$id = 's2member.'.$id;
			if(!in_array($id, array('s2member.css', 's2member-pro.css', 's2member.js', 's2member-pro.js'), TRUE))
				return array('ok' => FALSE, 'url' => '', 'build' => 0, 'error' => 'Invalid static asset ID');
			$type = substr(strrchr($id, '.'), 1);
			if(!in_array($id, self::static_asset_ids($type, 'all'), TRUE))
				return array('ok' => FALSE, 'url' => '', 'build' => 0, 'error' => 'Static asset is not active in the current delivery mode');
			if(!$force && isset(self::$static_asset_cache[$id]))
				return self::$static_asset_cache[$id];

			//260903.0544 Normal requests only check whether current hooks/configuration permit static delivery; source assembly and filesystem work wait until a build is actually needed.
			$compatibility = self::static_asset_definition($id, FALSE);
			if(empty($compatibility['ok']))
				return self::$static_asset_cache[$id] = array('ok' => FALSE, 'url' => '', 'build' => abs(self::static_asset_build($id)), 'error' => (string)$compatibility['error']);

			$state = self::static_asset_build($id);
			$dirty = $state < 0;
			$active_build = abs($state);
			$base = substr($id, 0, -strlen('.'.$type));
			$failure_key = 'ws_plugin__s2member_static_asset_failure_'.str_replace('.', '_', $id);
			$repair_lock_key = '';
			$data_map_signature = array('ok' => TRUE, 'signature' => '', 'error' => '');
			$uses_data_map = $type === 'js' && self::static_js_text_delivery() === 'page';
			if($uses_data_map)
			{
				$data_map_signature = self::static_js_data_map_signature($id);
				if(empty($data_map_signature['ok']))
					return self::$static_asset_cache[$id] = array('ok' => FALSE, 'url' => '', 'build' => $active_build, 'error' => (string)$data_map_signature['error']);
				//260906.1530 Regenerate static JavaScript when its shipped data-map layout changes so slot numbers stay synchronized.
				if($active_build && self::static_asset_data_map_signature($id) !== (string)$data_map_signature['signature'])
				{
					$dirty = TRUE;
					$state = -$active_build;
					self::set_static_asset_build($id, $state);
				}
			}
			if(!$force && !$dirty && $active_build)
			{
				$location = self::static_assets_location(FALSE);
				if(empty($location['ok']))
					return self::$static_asset_cache[$id] = array('ok' => FALSE, 'url' => '', 'build' => $active_build, 'error' => $location['error']);
				$url = $location['url'].'/'.$base.'-'.$active_build.'.'.$type;
				$path = $location['dir'].'/'.$base.'-'.$active_build.'.'.$type;
				if(!is_file($path))
				{
					//260911.1806 A configured static file that vanished locally is not an admin preference: try one guarded synchronous repair, then let normal dynamic fallback handle this request if repair cannot complete.
					if($failure = get_transient($failure_key))
						return self::$static_asset_cache[$id] = array('ok' => FALSE, 'url' => '', 'build' => $active_build, 'error' => (string)$failure);
					$repair_lock_key = 'ws_plugin__s2member_static_asset_repair_lock_'.str_replace('.', '_', $id);
					$repair_lock_time = (int)get_option($repair_lock_key, 0);
					if($repair_lock_time && $repair_lock_time < time() - 30)
					{
						delete_option($repair_lock_key);
						$repair_lock_time = 0;
					}
					if(!add_option($repair_lock_key, time(), '', 'no'))
						return self::$static_asset_cache[$id] = array('ok' => FALSE, 'url' => '', 'build' => $active_build, 'error' => 'Expected static asset '.$id.' is missing; another request is already rebuilding it.');
					$dirty = TRUE;
				}
				else
				{
					//260904.2110 A local file check is cheaper than sending a broken static URL; browser-confirmed public-URL failures still fall back without rebuilding a valid local file.
					if(self::asset_http_target_failed('static:'.$id, $url))
						return self::$static_asset_cache[$id] = array('ok' => FALSE, 'url' => '', 'build' => $active_build, 'error' => 'Static asset '.$id.' could not be loaded from its public URL.');
					return self::$static_asset_cache[$id] = array('ok' => TRUE, 'url' => $url, 'build' => $active_build, 'error' => '');
				}
			}

			if(!$force && $dirty && ($failure = get_transient($failure_key)))
				return self::$static_asset_cache[$id] = array('ok' => FALSE, 'url' => '', 'build' => $active_build, 'error' => (string)$failure);

			$definition = self::static_asset_definition($id, TRUE);
			if(empty($definition['ok']))
			{
				if($repair_lock_key !== '')
				{
					set_transient($failure_key, (string)$definition['error'], 5 * MINUTE_IN_SECONDS);
					delete_option($repair_lock_key);
				}
				return self::$static_asset_cache[$id] = array('ok' => FALSE, 'url' => '', 'build' => $active_build, 'error' => (string)$definition['error']);
			}

			$build = max(time(), $active_build + 1);
			$result = self::build_static_asset($base, $build, $type, $definition['sources'], !empty($definition['minify']));
			if(!empty($result['ok']))
			{
				if($uses_data_map)
					self::set_static_asset_data_map_signature($id, (string)$data_map_signature['signature']);
				self::set_static_asset_build($id, $build);

				//260907.2203 Record successful generation so automatic and manual rebuilds remain visible later.
				c_ws_plugin__s2member_utils_logs::log_entry('css-js', array(
					'event' => 'Static CSS/JS asset generated', 'result' => 'success', 'asset' => $id, 'build' => $build,
					'trigger' => $force ? 'forced refresh' : 'automatic generation', 'minified' => !empty($definition['minify']), 'url' => $result['url'],
				));

				//260905.0106 Prune only after the new timestamp is current so the previous generation is treated as stale instead of protected.
				self::prune_static_asset_generations(dirname($result['path']), $result['path']);
				delete_transient($failure_key);
				if($repair_lock_key !== '')
				{
					//260911.1834 A missing active file that repaired successfully is still useful history, but it must not lower the health score because this request retained static delivery.
					$health_id = self::asset_runtime_health_id($id, $type, 'static');
					self::queue_asset_health_issue_snapshot('repaired', self::asset_runtime_health_label($health_id), 'Expected static asset '.$id.' was missing and was rebuilt automatically.');
					delete_option($repair_lock_key);
				}
				return self::$static_asset_cache[$id] = array('ok' => TRUE, 'url' => $result['url'], 'build' => $build, 'error' => '');
			}
			set_transient($failure_key, (string)$result['error'], 5 * MINUTE_IN_SECONDS);
			if($repair_lock_key !== '')
				delete_option($repair_lock_key);

			//260907.2203 Preserve failed generation details even when delivery later falls back or recovers automatically.
			c_ws_plugin__s2member_utils_logs::log_entry('css-js', array(
				'event' => 'Static CSS/JS asset generation failed', 'result' => 'failure', 'asset' => $id, 'attempted_build' => $build,
				'previous_build' => $active_build, 'trigger' => $force ? 'forced refresh' : 'automatic generation', 'error' => (string)$result['error'],
			));

			return self::$static_asset_cache[$id] = array('ok' => FALSE, 'url' => '', 'build' => $active_build, 'error' => $result['error']);
		}

		/**
		 * Returns all active generated frontend assets for one type.
		 *
		 * If any active file cannot be generated safely, callers fall back to the legacy dynamic asset for the entire type rather than mixing static and dynamic representations.
		 *
		 * @package s2Member\Utilities
		 * @since 260903.1918
		 *
		 * @param string $type  `css` or `js`.
		 * @param bool   $force Force fresh timestamps for every active file of this type.
		 * @return array Aggregate result with individual assets keyed by logical filename.
		 */
		public static function ensure_static_assets($type = '', $force = FALSE)
		{
			$type = strtolower((string)$type);
			if(!in_array($type, array('css', 'js'), TRUE))
				return array('ok' => FALSE, 'assets' => array(), 'error' => 'Invalid static asset type');
			$option = 'static_'.$type;
			if(empty($GLOBALS['WS_PLUGIN__']['s2member']['o'][$option]))
				return array('ok' => FALSE, 'assets' => array(), 'error' => 'Static '.strtoupper($type).' Delivery is disabled');

			$assets = array();
			foreach(self::static_asset_ids($type, 'all') as $id)
			{
				$assets[$id] = self::ensure_static_asset($id, $force);
				if(empty($assets[$id]['ok']))
					return array('ok' => FALSE, 'assets' => $assets, 'error' => (string)$assets[$id]['error']);
			}
			return array('ok' => (bool)$assets, 'assets' => $assets, 'error' => '');
		}

		/**
		 * Refreshes all currently enabled static frontend asset types immediately.
		 *
		 * CSS and JS remain independent; within each type only files active in the current separate/combined representation are rebuilt.
		 *
		 * @package s2Member\Utilities
		 * @since 260903.0525
		 *
		 * @return array Results keyed by `css` and/or `js`.
		 */
		public static function refresh_static_assets()
		{
			$results = array();
			foreach(array('css' => 'static_css', 'js' => 'static_js') as $type => $option)
				if(!empty($GLOBALS['WS_PLUGIN__']['s2member']['o'][$option]))
				{
					foreach(self::static_asset_ids($type, 'all') as $id)
					{
						unset(self::$static_asset_cache[$id]);
						delete_transient('ws_plugin__s2member_static_asset_failure_'.str_replace('.', '_', $id));
					}
					$results[$type] = self::ensure_static_assets($type, TRUE);
				}
			return $results;
		}

		/**
		 * AJAX handler for the General Options “Refresh Static Assets” button.
		 *
		 * @package s2Member\Utilities
		 * @since 260903.0437
		 *
		 * @return null Exits through WordPress JSON helpers.
		 */
		public static function ajax_refresh_static_assets()
		{
			check_ajax_referer('ws-plugin--s2member-refresh-static-assets');
			if(!current_user_can('create_users'))
				wp_send_json_error(array('message' => 'You do not have permission to refresh s2Member static assets.'), 403);

			$results = self::refresh_static_assets();
			if(!$results)
				wp_send_json_error(array('message' => 'Enable Static CSS Delivery or Static JS Delivery and save the options first.'), 400);

			$success = $errors = array();
			foreach($results as $type => $result)
				if(!empty($result['ok']))
					$success[] = strtoupper($type);
				else
					$errors[] = strtoupper($type).': '.((!empty($result['error'])) ? $result['error'] : 'unknown build error');

			if(!$errors)
			{
				//260907.2203 Record the administrator-triggered refresh result.
				c_ws_plugin__s2member_utils_logs::log_entry('css-js', array('event' => 'Manual Static CSS/JS refresh', 'result' => 'success', 'refreshed' => $success));

				wp_send_json_success(array('message' => 'Static '.implode(' + ', $success).' refreshed. New timestamped files are active.'));
			}

			//260907.2203 Record partial and failed administrator-triggered refreshes too.
			c_ws_plugin__s2member_utils_logs::log_entry('css-js', array('event' => 'Manual Static CSS/JS refresh', 'result' => ($success ? 'partial failure' : 'failure'), 'refreshed' => $success, 'errors' => $errors));

			$message = (($success) ? 'Refreshed '.implode(' + ', $success).'. ' : '').'Could not refresh '.implode('; ', $errors).'. Failed types continue with their previous valid files when still current, or dynamic delivery when stale.';
			wp_send_json_error(array('message' => $message), 500);
		}

		/**
		 * Checks the few currently active generated files for local filesystem availability.
		 *
		 * This runs on administrator requests and is also mirrored by the per-file check immediately before a static frontend URL is used. With at most four active generated files, direct existence checks avoid stale health results with a small, bounded cost.
		 *
		 * @package s2Member\Utilities
		 * @since 260903.0525
		 *
		 * @param bool $force Recheck even if this request already has a cached result.
		 * @return array Missing active asset files keyed by logical filename.
		 */
		public static function static_assets_health($force = FALSE)
		{
			if(!$force && isset(self::$static_assets_health_cache))
				return self::$static_assets_health_cache;
			if(empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['static_css']) && empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['static_js']))
				return self::$static_assets_health_cache = array();

			$missing = array();
			$location = self::static_assets_location(FALSE);
			if(empty($location['ok']))
				$missing['location'] = $location['error'];
			else
				foreach(array('css' => 'static_css', 'js' => 'static_js') as $type => $option)
					if(!empty($GLOBALS['WS_PLUGIN__']['s2member']['o'][$option]))
						foreach(self::static_asset_ids($type, 'all') as $id)
						{
							$build = self::static_asset_build($id);
							$base = substr($id, 0, -strlen('.'.$type));
							if($build > 0 && !is_file($location['dir'].'/'.$base.'-'.$build.'.'.$type))
								$missing[$id] = 'Expected static asset '.$id.' is missing.';
						}

			//260907.2203 Log only local-health transitions so recurring admin checks do not repeat the same event.
			$previous = get_option('ws_plugin__s2member_static_asset_health', array());
			$previous = is_array($previous) ? $previous : array();
			if(serialize($previous) !== serialize($missing))
			{
				update_option('ws_plugin__s2member_static_asset_health', $missing, FALSE);
				if($missing)
					c_ws_plugin__s2member_utils_logs::log_entry('css-js', array('event' => 'Static CSS/JS local health issue', 'result' => 'failure', 'issues' => $missing));
				else if($previous)
					c_ws_plugin__s2member_utils_logs::log_entry('css-js', array('event' => 'Static CSS/JS local health recovered', 'result' => 'recovered', 'previous_issues' => $previous));
			}

			return self::$static_assets_health_cache = $missing;
		}

		/**
		 * Returns timing status for one physical frontend CSS/JavaScript response.
		 *
		 * A real-page activation delay is Late/Yellow evidence. The trusted browser check decides
		 * whether the response is actually valid or contributes a later Failed asset-load result.
		 * Timing frequency alone does not escalate a response to Orange or Red.
		 *
		 * @package s2Member\Utilities
		 * @since 260909.2021
		 *
		 * @param string $id Runtime-health ID.
		 * @return array Status details.
		 */
		protected static function asset_runtime_health_event($id = '')
		{
			$id = (string)$id;
			$count = 0;
			$latest = 0;
			$pending = FALSE;
			$health = self::asset_http_health_state();

			if(is_array($health) && !empty($health['runtime_warnings']) && is_array($health['runtime_warnings']))
				foreach($health['runtime_warnings'] as $warning)
					if(!empty($warning['id']) && (string)$warning['id'] === $id && !empty($warning['reported']) && (int)$warning['reported'] >= time() - HOUR_IN_SECONDS)
					{
						$count += (!empty($warning['count'])) ? max(1, (int)$warning['count']) : 1;
						$latest = max($latest, (int)$warning['reported']);
					}
			foreach(self::asset_runtime_suspicions() as $suspicion)
				if(!empty($suspicion['id']) && (string)$suspicion['id'] === $id)
				{
					$pending = TRUE;
					$latest = max($latest, (int)$suspicion['reported']);
				}

			if($pending)
				return array(
					'status' => 'delayed',
					'label' => 'Late',
					'detail' => 'A frontend page could not confirm that this asset became active within the configured wait time. A trusted browser check will verify the asset response.',
					'reported' => $latest,
				);
			if($count)
				return array(
					'status' => 'delayed',
					'label' => 'Late',
					'detail' => 'This asset was late '.number_format_i18n($count).' time'.(($count === 1) ? '' : 's').' in the past hour. Trusted follow-up checks verified that the expected asset response was available.',
					'reported' => $latest,
				);
			return array('status' => 'healthy', 'label' => 'Healthy', 'detail' => '', 'reported' => 0);
		}

		/**
		 * Returns consolidated site-owner health for active frontend CSS/JavaScript delivery.
		 *
		 * Current rows explain the actual configured/preferred route and any fallback. The
		 * headline color comes from the Okay/Late/Fallback/Failed asset-load score: the latest 10
		 * individual loads plus populated clock minutes from the latest 10 minutes, with newer evidence more important.
		 *
		 * @package s2Member\Utilities
		 * @since 260909.2021
		 *
		 * @param bool $force Recheck local static-file health and request a full trusted browser probe on this admin page.
		 * @return array Overall status, score details, rows, notice level/items, and notice signature.
		 */
		public static function frontend_asset_health($force = FALSE)
		{
			if($force)
				self::$asset_health_force_full_probe = TRUE; //260912.0522 Opening the Health panel asks the footer probe for full current-route activation checks, not only the cheap background reachability checks.

			//260912.0258 Run the Health Logkeeper before rendering admin health so queued frontend evidence is reflected without requiring another refresh.
			self::run_health_logkeeper();
			$rows = array();
			$error_notice_items = array();
			$attention_items = array();
			//260910.0709 Rows describe the actual route in use; notice item lists are separate so Yellow/Orange status can remain informative without automatically becoming an admin-wide alarm.
			$http_health = self::asset_http_health_state();
			$failures = (is_array($http_health) && !empty($http_health['failures']) && is_array($http_health['failures'])) ? $http_health['failures'] : array();
			$runtime_warnings = (is_array($http_health) && !empty($http_health['runtime_warnings']) && is_array($http_health['runtime_warnings'])) ? $http_health['runtime_warnings'] : array();
			$local_health = self::static_assets_health($force);
			$location = self::static_assets_location(FALSE);
			$selected_s2o = empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['dynamic_asset_loader']) || $GLOBALS['WS_PLUGIN__']['s2member']['o']['dynamic_asset_loader'] !== 'wordpress';
			$dynamic_normal = array('css' => FALSE, 'js' => FALSE);
			$wp_loader_active = array('css' => FALSE, 'js' => FALSE);
			$wp_loader_fallback = array('css' => FALSE, 'js' => FALSE);

			foreach(array('css' => 'CSS', 'js' => 'JS') as $type => $type_label)
			{
				$static_requested = !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['static_'.$type]);
				if(!$static_requested)
				{
					$dynamic_normal[$type] = TRUE;
					$using_wordpress = self::dynamic_asset_url(FALSE) === self::wordpress_dynamic_asset_url();
					$wp_loader_active[$type] = $using_wordpress;
					$wp_loader_fallback[$type] = $selected_s2o;
					if($selected_s2o)
					{
						//260912.1956 Show the configured s2Member-Only route separately from its WordPress fallback so each route's current health is understandable at a glance.
						$s2o_url = ($type === 'css')
							? add_query_arg(array('ws_plugin__s2member_css' => '1', 'qcABC' => '1'), $GLOBALS['WS_PLUGIN__']['s2member']['c']['s2o_url'])
							: add_query_arg(array('ws_plugin__s2member_js_w_globals' => (defined('WS_PLUGIN__S2MEMBER_API_CONSTANTS_MD5') ? WS_PLUGIN__S2MEMBER_API_CONSTANTS_MD5 : '1'), 'qcABC' => '1'), $GLOBALS['WS_PLUGIN__']['s2member']['c']['s2o_url']);
						$event = self::asset_runtime_health_event('dynamic_'.$type);
						if($using_wordpress)
						{
							$status = 'error';
							$status_label = 'Failed';
							$detail = 'The selected s2Member-Only Dynamic Loader could not be used; Full WordPress Dynamic fallback is serving this asset.';
							$attention_items['fallback:'.$type] = $type_label.' is using Full WordPress Dynamic Loader because the selected s2Member-Only Dynamic Loader could not be used.';
						}
						else
						{
							$status = $event['status'];
							$status_label = $event['label'];
							$detail = 's2Member-Only Dynamic Loader.'.(($event['detail']) ? ' '.$event['detail'] : '');
						}
						$rows[] = array('label' => 's2Member-Only '.$type_label, 'delivery' => 'Dynamic', 'status' => $status, 'status_label' => $status_label, 'detail' => $detail, 'url' => $s2o_url);
					}
					continue;
				}

				$ids = self::static_asset_ids($type, 'all');
				$fallback = FALSE;
				$fallback_reasons = array();
				$states = array();
				$generation_failures = array();

				//260910.0630 A compatibility/build fallback applies to the whole asset type; stale failures for static files that are no longer being served must not masquerade as current delivery failures.
				foreach($ids as $id)
				{
					$states[$id] = self::static_asset_build($id);
					$generation_failures[$id] = get_transient('ws_plugin__s2member_static_asset_failure_'.str_replace('.', '_', $id));
					$definition = self::static_asset_definition($id, FALSE);
					if(empty($definition['ok']))
					{
						$fallback = TRUE;
						$fallback_reasons[] = (string)$definition['error'];
					}
					else if($generation_failures[$id] && $states[$id] <= 0)
					{
						$fallback = TRUE;
						$fallback_reasons[] = $id.': '.(string)$generation_failures[$id];
					}
				}

				if(!$fallback)
					foreach($ids as $id)
					{
						$state = $states[$id];
						$build = abs($state);
						if(!empty($local_health['location']))
						{
							$fallback = TRUE;
							$fallback_reasons[] = (string)$local_health['location'];
							continue;
						}
						if(isset($local_health[$id]))
						{
							$fallback = TRUE;
							$fallback_reasons[] = (string)$local_health[$id];
							continue;
						}
						if($state > 0)
						{
							$base = substr($id, 0, -strlen('.'.$type));
							$url = (!empty($location['ok'])) ? $location['url'].'/'.$base.'-'.$build.'.'.$type : '';
							if($url && self::asset_http_target_failed('static:'.$id, $url))
							{
								$fallback = TRUE;
								$fallback_reasons[] = $id.' could not be loaded from its public URL.';
							}
						}
					}

				if($fallback)
				{
					$wp_loader_active[$type] = TRUE;
					$wp_loader_fallback[$type] = TRUE;
					$status = 'attention';
					$status_label = 'Using dynamic fallback';
					$detail = 'Full WordPress Dynamic Loader is being used instead of the requested static '.$type_label.' delivery.';
					if($fallback_reasons)
						$detail .= ' '.implode(' ', array_unique($fallback_reasons));
					$attention_items['fallback:'.$type] = 'Requested static '.$type_label.' delivery is unavailable; Full WordPress Dynamic Loader is being used instead.';
					$delivery_url = self::wordpress_dynamic_asset_url();
					$delivery_url = ($type === 'css') ? add_query_arg(array('ws_plugin__s2member_css' => '1', 'qcABC' => '1'), $delivery_url) : add_query_arg(array('ws_plugin__s2member_js_w_globals' => (defined('WS_PLUGIN__S2MEMBER_API_CONSTANTS_MD5') ? WS_PLUGIN__S2MEMBER_API_CONSTANTS_MD5 : '1'), 'qcABC' => '1'), $delivery_url);
					$event = self::asset_runtime_health_event('dynamic_'.$type);
					if($event['detail'])
						$detail .= ' '.$event['detail'];
					if(!empty($failures['fallback:dynamic_'.$type]) || !empty($failures['dynamic:dynamic_'.$type]))
					{
						$status = 'error';
						$status_label = 'Fallback check failed';
						$detail = 'The requested static '.$type_label.' delivery is unavailable, and the Full WordPress Dynamic fallback could not be loaded or verified.';
						$error_notice_items['delivery:'.$type] = $type_label.' preferred delivery is unavailable and the Full WordPress Dynamic fallback could not be loaded or verified.';
					}
					$rows[] = array('label' => $type_label.' Delivery', 'delivery' => 'Dynamic fallback', 'status' => $status, 'status_label' => $status_label, 'detail' => $detail, 'url' => $delivery_url);
					continue;
				}

				$wp_loader_fallback[$type] = TRUE;
				foreach($ids as $id)
				{
					$state = self::static_asset_build($id);
					$build = abs($state);
					$failure = get_transient('ws_plugin__s2member_static_asset_failure_'.str_replace('.', '_', $id));
					$health_id = self::asset_runtime_health_id($id, $type, 'static');
					$event = self::asset_runtime_health_event($health_id);
					$status = $event['status'];
					$status_label = $event['label'];
					if($state < 0)
					{
						$status = ($status === 'healthy') ? 'delayed' : $status;
						$status_label = ($status === 'delayed' && $event['status'] === 'healthy') ? 'Pending rebuild' : $status_label;
						$detail = 'Static file is pending rebuild; its previous timestamp remains only for already-cached HTML.';
					}
					else
						$detail = ($build > 0) ? 'Static file is current (build '.date_i18n('Y-m-d H:i:s', $build).').' : 'Static file has not been created yet; s2Member will build it automatically.';
					if($failure && $state > 0)
					{
						$status = ($status === 'healthy') ? 'delayed' : $status;
						$status_label = ($status === 'delayed' && $event['status'] === 'healthy') ? 'Recent refresh issue' : $status_label;
						$detail .= ' A recent refresh failed, but this previous valid file remains active. '.(string)$failure;
					}
					if($event['detail'])
						$detail .= ' '.$event['detail'];
					$base = substr($id, 0, -strlen('.'.$type));
					$url = ($build > 0 && !empty($location['ok'])) ? $location['url'].'/'.$base.'-'.$build.'.'.$type : '';
					$rows[] = array('label' => self::asset_runtime_health_label($health_id), 'delivery' => 'Static', 'status' => $status, 'status_label' => $status_label, 'detail' => $detail, 'url' => $url);
				}
			}

			$s2o_missing = $selected_s2o && !is_file(self::s2o_file_path());
			$s2o_failed = $selected_s2o && !$s2o_missing && self::asset_http_target_failed('s2o', $GLOBALS['WS_PLUGIN__']['s2member']['c']['s2o_url']);
			$s2o_needed = $selected_s2o && ($dynamic_normal['css'] || $dynamic_normal['js']);
			$s2o_problem = $s2o_missing || $s2o_failed;

			if($selected_s2o && $s2o_problem && $s2o_needed)
				$attention_items['s2o'] = ($s2o_missing) ? 'The selected s2Member-Only Dynamic Loader file <code>s2member-o.php</code> is missing; Full WordPress Dynamic Loader is being used automatically.' : 'The selected s2Member-Only Dynamic Loader could not be reached; Full WordPress Dynamic Loader is being used automatically.';

			//260907.2203 Track missing/recovered loader transitions without logging every admin health check.
			//260909.2021 Keep those transitions in css-js.log even when the loader is not currently needed by fully static delivery.
			$s2o_missing_logged = (bool)get_option('ws_plugin__s2member_css_js_s2o_missing', FALSE);
			if($s2o_missing && !$s2o_missing_logged)
			{
				update_option('ws_plugin__s2member_css_js_s2o_missing', 1, FALSE);
				c_ws_plugin__s2member_utils_logs::log_entry('css-js', array('event' => 's2Member-Only Dynamic Loader file missing', 'result' => 'failure', 'file' => self::s2o_file_path(), 'fallback' => 'Full WordPress Dynamic Loader'));
			}
			else if($selected_s2o && !$s2o_missing && $s2o_missing_logged)
			{
				delete_option('ws_plugin__s2member_css_js_s2o_missing');
				c_ws_plugin__s2member_utils_logs::log_entry('css-js', array('event' => 's2Member-Only Dynamic Loader file recovered', 'result' => 'recovered', 'file' => self::s2o_file_path()));
			}

			//260912.1956 Full WordPress is always either the configured dynamic route or the safety-net fallback, so keep its CSS/JS health visible even when static delivery is currently healthy.
			$full_checked = (!empty($http_health['full_checked'])) ? (int)$http_health['full_checked'] : 0;
			foreach(array('css' => 'CSS', 'js' => 'JS') as $type => $type_label)
			{
				$is_fallback = !empty($wp_loader_fallback[$type]);
				$failure_id = ($is_fallback) ? 'fallback:dynamic_'.$type : 'dynamic:dynamic_'.$type;
				$failed = !empty($failures[$failure_id]);
				$delivery_url = self::wordpress_dynamic_asset_url();
				$delivery_url = ($type === 'css')
					? add_query_arg(array('ws_plugin__s2member_css' => '1', 'qcABC' => '1'), $delivery_url)
					: add_query_arg(array('ws_plugin__s2member_js_w_globals' => (defined('WS_PLUGIN__S2MEMBER_API_CONSTANTS_MD5') ? WS_PLUGIN__S2MEMBER_API_CONSTANTS_MD5 : '1'), 'qcABC' => '1'), $delivery_url);
				if($failed)
				{
					$status = 'error';
					$status_label = 'Failed';
					$detail = ($is_fallback) ? 'The Full WordPress Dynamic fallback could not be loaded or confirmed active.' : 'The Full WordPress Dynamic response could not be loaded or confirmed active.';
				}
				else if(!$full_checked)
				{
					$status = 'disabled';
					$status_label = 'Not checked yet';
					$detail = 'Recheck Asset Health to verify this delivery route.';
				}
				else if(!empty($wp_loader_active[$type]))
				{
					$event = self::asset_runtime_health_event('dynamic_'.$type);
					$status = $event['status'];
					$status_label = $event['label'];
					$detail = ($is_fallback) ? 'Full WordPress Dynamic Loader is currently serving this asset as fallback.' : 'Full WordPress Dynamic Loader is the configured delivery route.';
					if($event['detail'])
						$detail .= ' '.$event['detail'];
				}
				else
				{
					$status = 'healthy';
					$status_label = 'Healthy';
					$detail = 'Full WordPress Dynamic fallback is available if the preferred delivery route cannot be used.';
				}
				$rows[] = array('label' => 'WP Loader '.$type_label, 'delivery' => ($is_fallback) ? 'Dynamic fallback' : 'Dynamic', 'status' => $status, 'status_label' => $status_label, 'detail' => $detail, 'url' => $delivery_url);
			}

			//260912.0258 Only the Health Logkeeper mutates the rolling health log; this view reads the merged state without another read/modify/write race.
			$state = self::asset_health_log_state();
			$scores = self::asset_health_scores($state);
			$rolling_score = $scores['score'];
			$current_delivery_result = self::asset_health_current_delivery_result($failures);
			$standby_fallback_failures = array();
			if($full_checked && $current_delivery_result === 'okay')
				foreach(array('css' => 'CSS', 'js' => 'JS') as $type => $type_label)
					if(!empty($wp_loader_fallback[$type]) && !empty($failures['fallback:dynamic_'.$type]))
					{
						$standby_fallback_failures[$type] = TRUE;
						$attention_items['standby-fallback:'.$type] = 'WP Loader '.$type_label.' fallback is unavailable while the preferred '.$type_label.' delivery is still working.';
					}
			//260912.1956 A broken standby fallback can never improve Health: average its failed score with the established rolling score only while preferred delivery itself still works.
			if($standby_fallback_failures && $rolling_score !== NULL)
				$scores['score'] = ($rolling_score + 1.0) / 2;
			$overall = self::asset_health_status_from_score($scores['score'], $scores['latest_result']);
			$recent_issues = (!empty($http_health['recent_issues']) && is_array($http_health['recent_issues'])) ? $http_health['recent_issues'] : array();
			//260910.2350 Recent per-asset details explain a non-Green rolling score even when css-js.log is disabled; clear them only after the overall calculated Health is Green and no trusted/pending problem remains.
			if($overall === 'healthy' && !$failures && !$runtime_warnings && !self::asset_runtime_suspicions() && $recent_issues)
			{
				unset($http_health['recent_issues']);
				update_option('ws_plugin__s2member_asset_http_health', $http_health, FALSE);
				self::$asset_http_health_cache = $http_health;
				$recent_issues = array();
			}
			$labels = array(
				'unknown' => 'Not checked yet',
				'healthy' => 'Healthy',
				'delayed' => 'Recent issue',
				'attention' => 'Working, review suggested',
				'error' => 'Needs attention',
			);
			$summaries = array(
				'unknown' => 'No recent frontend asset-load health is available yet. This panel will run a trusted current-delivery check.',
				'healthy' => 'Recent frontend CSS/JavaScript asset loads are healthy.',
				'delayed' => 'Recent asset loads are mixed or include late activation, but they do not currently average into degraded delivery.',
				'attention' => 'Frontend asset delivery is working, but recent results or an unavailable fallback route suggest that the configuration should be reviewed.',
				'error' => 'Recent asset loads average into serious delivery failure. s2Member forms, buttons, behavior, or styling may currently be affected.',
			);

			$not_green_since = (!empty($state['not_green_since'])) ? (int)$state['not_green_since'] : 0;
			$fallback_problem_since = ($standby_fallback_failures && !empty($http_health['fallback_problem_since'])) ? (int)$http_health['fallback_problem_since'] : 0;
			//260912.1956 Keep the existing rolling-health age, but let a continuously unavailable standby fallback start/extend the same non-Healthy review period without creating synthetic page-load events.
			if($overall !== 'healthy' && $fallback_problem_since > 0 && ($not_green_since <= 0 || $fallback_problem_since < $not_green_since))
				$not_green_since = $fallback_problem_since;
			if($overall === 'healthy')
				$not_green_since = 0;
			$six_hour_average = NULL;
			$notice_level = '';
			$notice_items = array();
			$notice_signature = '';

			if($overall === 'error' && $not_green_since > 0)
			{
				//260910.0709 Red is immediate because the recent score says delivery is failing badly enough to threaten frontend behavior; no persistence delay is added.
				$notice_level = 'error';
				$notice_items = ($error_notice_items) ? $error_notice_items : array('score' => 'Recent CSS/JavaScript asset loads show repeated delivery failures severe enough that frontend s2Member functionality may be affected.');
				$notice_signature = 'error:'.$not_green_since;
			}
			else if($not_green_since > 0 && $not_green_since <= time() - 6 * HOUR_IN_SECONDS)
			{
				//260912.1956 Reuse the established six-hour review logic; when the standby fallback itself has stayed unavailable for the full period, average its failed score into the retained delivery history just as Current Health does.
				$six_hour_average = self::asset_health_six_hour_average($state);
				if($standby_fallback_failures && $fallback_problem_since > 0 && $fallback_problem_since <= time() - 6 * HOUR_IN_SECONDS)
				{
					if($six_hour_average === NULL)
						$six_hour_average = $rolling_score;
					if($six_hour_average !== NULL)
						$six_hour_average = ($six_hour_average + 1.0) / 2;
				}
				if($six_hour_average !== NULL && $six_hour_average <= 2.5)
				{
					$notice_level = 'attention';
					//260911.1705 Keep admin-facing health wording understandable without requiring familiarity with the internal Green/Yellow/Orange/Red state model.
					$notice_items = ($attention_items) ? $attention_items : array('score' => 'CSS/JavaScript asset-load health has remained substantially degraded across the latest six hours without returning to normal.');
					$notice_signature = 'attention:'.$not_green_since;
				}
			}

			//260910.0709 Dismissal is scoped to severity + one continuous non-Green period; changing Orange-review severity to Red surfaces again, while Green clears the old dismissal before another period can begin.
			$dismissed = (string)get_option('ws_plugin__s2member_asset_notice_dismissed', '');
			$active_signatures = array_values(array_filter(array(($not_green_since > 0) ? 'error:'.$not_green_since : '', ($not_green_since > 0) ? 'attention:'.$not_green_since : '')));
			if($dismissed !== '' && !in_array($dismissed, $active_signatures, TRUE))
				delete_option('ws_plugin__s2member_asset_notice_dismissed');

			return array(
				'status' => $overall,
				'status_label' => $labels[$overall],
				'summary' => $summaries[$overall],
				'rows' => $rows,
				'score' => $scores['score'],
				'rolling_score' => $rolling_score,
				'standby_fallback_failures' => array_keys($standby_fallback_failures),
				'request_score' => $scores['request_score'],
				'time_score' => $scores['time_score'],
				'request_count' => $scores['request_count'],
				'time_count' => $scores['time_count'],
				'recent_issues' => $recent_issues,
				'six_hour_average' => $six_hour_average,
				'not_green_since' => $not_green_since,
				'notice_level' => $notice_level,
				'notice_items' => $notice_items,
				'notice_signature' => $notice_signature,
			);
		}

		/**
		 * Dismisses the current frontend-asset notice for the current continuous non-Green health period.
		 *
		 * @package s2Member\Utilities
		 * @since 260909.2021
		 *
		 * @attaches-to ``add_action('admin_init');``
		 * @return null
		 */
		public static function dismiss_static_assets_admin_notice()
		{
			if(!is_admin() || !current_user_can('create_users') || empty($_GET['s2member-dismiss-asset-health-notice']))
				return;

			check_admin_referer('s2member-dismiss-asset-health-notice');
			$health = self::frontend_asset_health(TRUE);
			if(!empty($health['notice_signature']))
				update_option('ws_plugin__s2member_asset_notice_dismissed', (string)$health['notice_signature'], FALSE);

			wp_safe_redirect(wp_get_referer() ? wp_get_referer() : admin_url());
			exit;
		}

		/**
		 * Displays one non-Green-period-scoped admin-wide notice for red failures or persistent degraded health.
		 *
		 * Missing or browser-confirmed unreachable frontend assets remain part of the Red diagnosis when usable delivery/fallback also fails; preferred-route failures with a working fallback are not treated as Red.
		 * Red means the recent weighted asset loads average into serious failure and is immediate.
		 * Orange remains non-alarming unless six hours have passed without Green and the equal-block
		 * rolling six-hour average remains below Yellow territory. Current Yellow may therefore still
		 * surface the calm review notice when the longer recent history remains substantially degraded.
		 *
		 * @package s2Member\Utilities
		 * @since 260903.0612
		 *
		 * @attaches-to ``add_action('admin_notices');``
		 * @return null
		 */
		public static function static_assets_admin_notice()
		{
			if(!current_user_can('create_users') || (defined('DOING_AJAX') && DOING_AJAX))
				return;

			$health = self::frontend_asset_health();
			if(empty($health['notice_items']) || empty($health['notice_signature']) || empty($health['notice_level']))
				return;
			$dismissed = (string)get_option('ws_plugin__s2member_asset_notice_dismissed', '');
			if($dismissed === (string)$health['notice_signature'])
				return;
			//260911.0012 A dismissed Red problem also suppresses the later calmer Orange review notice in the same non-Green period. A dismissed Orange notice never suppresses a later Red escalation.
			if($health['notice_level'] !== 'error' && !empty($health['not_green_since']) && $dismissed === 'error:'.(int)$health['not_green_since'])
				return;

			//260911.1707 Give the specific asset-health reason a compact, visually distinct line without requiring familiarity with the internal health-state colors.
			$items = array();
			foreach($health['notice_items'] as $item)
				$items[] = '&bull;&nbsp; <strong><em>'.$item.'</em></strong>';
			$settings_url = add_query_arg('s2member-open-panel', 'frontend-static-assets', admin_url('/admin.php?page=ws-plugin--s2member-gen-ops')).'#ws-plugin--s2member-asset-health';
			$dismiss_url = wp_nonce_url(add_query_arg('s2member-dismiss-asset-health-notice', '1', admin_url()), 's2member-dismiss-asset-health-notice');

			$_notice_items = '<span style="display:block; margin:.4em 0 .45em .65em;">'.implode('<br />', $items).'</span>';
			if($health['notice_level'] === 'error')
			{
				$message = 'Recent frontend CSS/JavaScript asset loads average into serious delivery failure. This can affect s2Member forms, buttons, behavior, or styling.'.$_notice_items.'<a href="'.esc_url($settings_url).'">Open CSS/JS Asset Health</a> for the current delivery details and troubleshooting.';
				c_ws_plugin__s2member_admin_notices::display_branded_notice('s2Member CSS/JS Asset Delivery Problem', $message, TRUE, $dismiss_url);
			}
			else
			{
				$message = 'Frontend CSS/JavaScript asset-load health has remained substantially degraded across the latest six hours without returning to normal. Delivery may currently be improving or may still be working through fallback. This is a suggestion to review the configuration, not an emergency.'.$_notice_items.'<a href="'.esc_url($settings_url).'">Open CSS/JS Asset Health</a> to review the current delivery details.';
				c_ws_plugin__s2member_admin_notices::display_branded_notice('s2Member CSS/JS Asset Health: Review Suggested', $message, FALSE, $dismiss_url);
			}
			unset($_notice_items);
			return;
		}

		/**
		 * Prints an infrequent trusted browser-side reachability probe for active frontend assets.
		 *
		 * Healthy static files use HEAD.
		 * The s2Member-Only Dynamic Loader (s2member-o.php) uses its tiny pre-WordPress health mode.
		 * A frontend runtime suspicion forces one full cache-busted activation check for that exact URL.
		 *
		 * @package s2Member\Utilities
		 * @since 260904.2110
		 *
		 * @attaches-to ``add_action('admin_footer');``
		 * @attaches-to ``add_action('wp_footer');``
		 * @return null
		 */
		public static function asset_http_health_probe()
		{
			if(!current_user_can('create_users') || (defined('DOING_AJAX') && DOING_AJAX))
				return;

			$health_log = self::asset_health_log_state();
			$scores = self::asset_health_scores($health_log);
			$health = self::asset_http_health_state();
			$has_failures = is_array($health) && !empty($health['failures']);
			$has_suspicions = (bool)self::asset_runtime_suspicions();
			$current_status = (!empty($health_log['status'])) ? (string)$health_log['status'] : 'unknown';
			$full = self::$asset_health_force_full_probe || !$scores['time_count'] || $has_failures || $has_suspicions || !in_array($current_status, array('unknown', 'healthy'), TRUE);
			$targets = self::asset_http_health_targets($full);
			if(!$targets)
				return;

			$target_hash = self::asset_http_health_target_hash($targets);
			$interval = ($full || $has_failures || $has_suspicions) ? MINUTE_IN_SECONDS : 10 * MINUTE_IN_SECONDS;
			$auto_due = $has_suspicions || !is_array($health) || empty($health['checked']) || empty($health['target_hash']) || (string)$health['target_hash'] !== $target_hash || (int)$health['checked'] < time() - $interval;
			if(!$auto_due && !self::$asset_health_force_full_probe)
				return;

			$config = array(
				'targets' => array_values($targets),
				'target_hash' => $target_hash,
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('ws-plugin--s2member-asset-http-health'),
				'reload_on_change' => is_admin(),
				'full' => (bool)$full,
				'auto_run' => (bool)$auto_due,
			);

			//260910.0818 The same trusted probe can run automatically for stale/no-current evidence and on demand from the Health panel; only an explicit recheck is allowed to reset active score history after a clean Okay result.
			echo '<script type="text/javascript">(function(c){if(!window.fetch||!window.URL||!window.Promise)return;var b=document.getElementById("ws-plugin--s2member-recheck-asset-health"),s=document.getElementById("ws-plugin--s2member-recheck-asset-health-status"),busy=false;function u(t,i){var x=new URL(t.probe_url,window.location.href),n=Date.now().toString(36)+"-"+i+"-"+Math.random().toString(36).slice(2);x.searchParams.set("s2member_asset_health",n);if(t.mode==="s2o-health")x.searchParams.set("s2member_health_token",n);return{x:x.toString(),n:n}}function ct(r,t){var v=(r.headers.get("content-type")||"").toLowerCase();if(t.type==="css")return v.indexOf("text/css")!==-1;if(t.type==="js")return /(javascript|ecmascript)/.test(v);return v.indexOf("text/plain")!==-1}function f(t,m,i,body){var z=u(t,i);return fetch(z.x,{method:m,cache:"no-store",credentials:"same-origin",headers:{"Cache-Control":"no-cache, no-store, max-age=0","Pragma":"no-cache"}}).then(function(r){var h=r.headers.get("x-s2member-health-token")||"",tm=r.headers.get("x-s2member-health-time")||"";if(!body)return{ok:r.ok&&ct(r,t),status:r.status,content_type:r.headers.get("content-type")||"",text:"",token:z.n,health_token:h,health_time:tm};return r.text().then(function(x){return{ok:r.ok&&ct(r,t),status:r.status,content_type:r.headers.get("content-type")||"",text:x,token:z.n,health_token:h,health_time:tm}})}).catch(function(){return{ok:false,status:0,content_type:"",text:"",token:z.n,health_token:"",health_time:""}})}function p(t,i){if(t.mode==="s2o-health")return f(t,"GET",i,true).then(function(r){r.ok=r.ok&&r.health_token===r.token&&r.health_time!==""&&r.text.indexOf("s2member-o-health:"+r.token+":"+r.health_time)===0;return{id:t.id,ok:r.ok,status:r.status,content_type:r.content_type,detail:r.ok?"":"Dynamic Loader health response could not be verified."}});if(t.mode==="activation-tag")return f(t,"GET",i,true).then(function(r){if(r.ok&&t.activation_tags)for(var j=0;j<t.activation_tags.length;j++)if(r.text.indexOf(t.activation_tags[j])===-1){r.ok=false;break}return{id:t.id,ok:r.ok,status:r.status,content_type:r.content_type,detail:r.ok?"":"Expected asset could not be verified as active."}});return f(t,"HEAD",i,false).then(function(r){if(r.ok)return{id:t.id,ok:true,status:r.status,content_type:r.content_type,detail:""};return f(t,"GET",i+"g",false).then(function(g){return{id:t.id,ok:g.ok,status:g.status,content_type:g.content_type,detail:g.ok?"":"Public URL check failed"}})})}function run(reset,retried){if(busy)return;busy=true;if(reset&&b)b.disabled=true;if(reset&&s)s.textContent="Checking current asset delivery...";Promise.all(c.targets.map(p)).then(function(results){var body="action="+encodeURIComponent("ws_plugin__s2member_asset_http_health")+"&_ajax_nonce="+encodeURIComponent(c.nonce)+"&target_hash="+encodeURIComponent(c.target_hash)+"&full="+(c.full?"1":"0")+"&reset_health="+(reset?"1":"0")+"&results="+encodeURIComponent(JSON.stringify(results));return fetch(c.ajax_url,{method:"POST",cache:"no-store",credentials:"same-origin",headers:{"Content-Type":"application/x-www-form-urlencoded;charset=UTF-8","Cache-Control":"no-cache, no-store, max-age=0","Pragma":"no-cache"},body:body})}).then(function(r){return r.json()}).then(function(j){busy=false;if(j&&j.success&&j.data&&j.data.stale){if(!retried&&j.data.targets&&j.data.target_hash){c.targets=j.data.targets;c.target_hash=j.data.target_hash;if(reset&&s)s.textContent=j.data.recheck_message||"Delivery changed. Rechecking...";return run(reset,true)}if(reset&&s)s.textContent="Delivery changed again. Refreshing...";window.location.reload();return}if(reset&&s)s.textContent=(j&&j.success&&j.data&&j.data.recheck_message)?j.data.recheck_message:"Check complete.";if(reset&&b)b.disabled=false;if((reset||c.reload_on_change)&&j&&j.success&&j.data&&j.data.reload)window.location.reload()}).catch(function(){busy=false;if(reset&&b)b.disabled=false;if(reset&&s)s.textContent="The check could not be completed."})}if(b)b.addEventListener("click",function(){run(true,false)},false);if(c.auto_run)run(false,false)})('.wp_json_encode($config).');</script>' . "\n";
			return;
		}

		/**
		 * Stores trusted administrator-browser asset probe results.
		 *
		 * @package s2Member\Utilities
		 * @since 260904.2110
		 *
		 * @return null Exits through WordPress JSON helpers.
		 */
		public static function ajax_asset_http_health_report()
		{
			check_ajax_referer('ws-plugin--s2member-asset-http-health');
			if(!current_user_can('create_users'))
				wp_send_json_error(array('message' => 'You do not have permission to report s2Member asset health.'), 403);

			$full = !empty($_POST['full']);
			$reset_health = $full && !empty($_POST['reset_health']);
			$targets = self::asset_http_health_targets($full);
			$target_hash = self::asset_http_health_target_hash($targets);
			if(empty($_POST['target_hash']) || (string)wp_unslash($_POST['target_hash']) !== $target_hash)
				wp_send_json_success(array('stale' => TRUE, 'reload' => FALSE, 'targets' => array_values($targets), 'target_hash' => $target_hash, 'recheck_message' => 'Delivery targets changed. Rechecking current routes...'));

			$results = (!empty($_POST['results'])) ? json_decode(wp_unslash($_POST['results']), TRUE) : array();
			$by_id = array();
			if(is_array($results))
				foreach($results as $result)
					if(is_array($result) && !empty($result['id']))
						$by_id[(string)$result['id']] = $result;

			$old = self::asset_http_health_state();
			$old_failures = (is_array($old) && !empty($old['failures']) && is_array($old['failures'])) ? $old['failures'] : array();
			$runtime_warnings = (is_array($old) && !empty($old['runtime_warnings']) && is_array($old['runtime_warnings'])) ? $old['runtime_warnings'] : array();
			$recent_issues = (is_array($old) && !empty($old['recent_issues']) && is_array($old['recent_issues'])) ? $old['recent_issues'] : array(); //260910.2346 Preserve compact per-asset troubleshooting context across probes until the calculated overall Health returns Green.
			foreach($runtime_warnings as $key => $warning)
				if(empty($warning['reported']) || (int)$warning['reported'] < time() - HOUR_IN_SECONDS)
					unset($runtime_warnings[$key]);
			$old_runtime_warnings = $runtime_warnings; //260907.2203 Preserve prior warning state so only new trusted transitions are logged.
			$old_health_log = self::asset_health_log_state();
			$old_health_status = (!empty($old_health_log['status'])) ? (string)$old_health_log['status'] : 'unknown';

			$failures = array();
			$suspicions = self::asset_runtime_suspicions();

			foreach($targets as $id => $target)
			{
				$result = (isset($by_id[$id]) && is_array($by_id[$id])) ? $by_id[$id] : array();
				if(empty($result['ok']))
				{
					$failure_id = (!empty($target['failure_id'])) ? (string)$target['failure_id'] : $id;
					$failures[$failure_id] = array(
						'url' => (!empty($target['failure_url'])) ? (string)$target['failure_url'] : (string)$target['url'],
						'label' => (string)$target['label'],
						'status' => (!empty($result['status'])) ? (int)$result['status'] : 0,
						'content_type' => (!empty($result['content_type'])) ? substr(sanitize_text_field((string)$result['content_type']), 0, 100) : '',
						'detail' => (!empty($result['detail'])) ? substr(sanitize_text_field((string)$result['detail']), 0, 160) : '',
					);
					$recent_issues = self::add_asset_health_recent_issue($recent_issues, 'failure-'.$failure_id, 'failed', (string)$target['label'], (!empty($failures[$failure_id]['detail'])) ? (string)$failures[$failure_id]['detail'] : 'Trusted browser check failed.', (string)$failures[$failure_id]['url']);
				}
				else if(!empty($target['suspicion_key']) && !empty($target['suspicion']))
				{
					$key = (string)$target['suspicion_key'];
					$previous_warning = (isset($runtime_warnings[$key]) && is_array($runtime_warnings[$key])) ? $runtime_warnings[$key] : array();
					$previous_count = (!empty($previous_warning['count'])) ? max(1, (int)$previous_warning['count']) : (($previous_warning) ? 1 : 0);
					$runtime_warnings[$key] = array(
						'id' => (string)$target['suspicion']['id'],
						'url' => (string)$target['suspicion']['url'],
						'delivery' => (string)$target['suspicion']['delivery'],
						'first_reported' => (!empty($previous_warning['first_reported'])) ? (int)$previous_warning['first_reported'] : ((!empty($previous_warning['reported'])) ? (int)$previous_warning['reported'] : time()),
						'reported' => time(),
						'count' => $previous_count + 1,
					);
					$recent_issues = self::add_asset_health_recent_issue($recent_issues, 'late-'.(string)$target['suspicion']['id'], 'late', self::asset_runtime_health_label((string)$target['suspicion']['id']), 'A frontend page could not confirm this asset within the configured wait time, but the trusted follow-up check verified the expected asset response.', (string)$target['suspicion']['url'], (string)$target['suspicion']['delivery']);
				}
				if(!empty($target['suspicion_key']))
					unset($suspicions[(string)$target['suspicion_key']]);
			}

			update_option('ws_plugin__s2member_asset_runtime_suspicions', $suspicions, FALSE);

			//260907.2203 Keep an operational history of newly confirmed failures, recoveries, and runtime warnings.
			//260910.0818 Routine Okay/Fallback/Late/Failed asset loads stay only in the fixed-size non-autoloaded health log so operational history is not flooded by normal frontend traffic.
			foreach($failures as $id => $failure)
				if(!isset($old_failures[$id]) || serialize($old_failures[$id]) !== serialize($failure))
					c_ws_plugin__s2member_utils_logs::log_entry('css-js', array(
						'event' => 'CSS/JS delivery health failure', 'result' => 'failure', 'target' => $id, 'details' => $failure,
						'fallback' => ($id === 's2o' || strpos((string)$id, 'static:') === 0) ? 'Full WordPress Dynamic Loader' : 'none',
					));
			foreach($old_failures as $id => $failure)
				if(!isset($failures[$id]))
					c_ws_plugin__s2member_utils_logs::log_entry('css-js', array('event' => 'CSS/JS delivery health recovered', 'result' => 'recovered', 'target' => $id, 'previous_details' => $failure));
			foreach($runtime_warnings as $key => $warning)
				if(!isset($old_runtime_warnings[$key]))
					c_ws_plugin__s2member_utils_logs::log_entry('css-js', array('event' => 'CSS/JS runtime warning confirmed', 'result' => 'warning', 'details' => $warning, 'delivery_changed' => FALSE));

			$full_checked = ($full) ? time() : ((!empty($old['full_checked'])) ? (int)$old['full_checked'] : 0);
			$fallback_problem = !empty($failures['fallback:dynamic_css']) || !empty($failures['fallback:dynamic_js']);
			$fallback_problem_since = 0;
			if($full && $fallback_problem)
				$fallback_problem_since = (!empty($old['fallback_problem_since'])) ? (int)$old['fallback_problem_since'] : time();
			else if(!$full && !empty($old['fallback_problem_since']))
				$fallback_problem_since = (int)$old['fallback_problem_since'];
			//260912.1956 Preserve when the complete route set was last checked, and how long a WordPress fallback has stayed unavailable, without turning standby-route health into extra page-load records.
			$new_health = array('checked' => time(), 'full_checked' => $full_checked, 'fallback_problem_since' => $fallback_problem_since, 'target_hash' => $target_hash, 'failures' => $failures, 'runtime_warnings' => $runtime_warnings, 'recent_issues' => $recent_issues);
			update_option('ws_plugin__s2member_asset_http_health', $new_health, FALSE);
			self::$asset_http_health_cache = $new_health;

			$load_result = '';
			$recheck_message = '';
			$new_health_status = $old_health_status;
			if($full)
			{
				//260910.0818 A full trusted probe contributes one Okay/Fallback/Failed asset load through the same scoring path as frontend evidence; only an explicit successful admin recheck may start a clean epoch.
				$load_result = self::asset_health_current_delivery_result($failures);
				$standby_fallback_failures = ($load_result === 'okay') ? array_intersect_key($failures, array('fallback:dynamic_css' => TRUE, 'fallback:dynamic_js' => TRUE)) : array();
				$reset_on_ok = $reset_health && $load_result === 'okay' && !$standby_fallback_failures;
				$load_issue = array();
				if($load_result !== 'okay' && $recent_issues)
				{
					//260911.1806 A trusted non-Okay recheck can refresh Last issue with the same concise per-asset context already retained for troubleshooting.
					$latest_issue = end($recent_issues);
					if(is_array($latest_issue))
						$load_issue = array('label' => (!empty($latest_issue['label'])) ? (string)$latest_issue['label'] : '', 'detail' => (!empty($latest_issue['detail'])) ? (string)$latest_issue['detail'] : '');
				}
				self::queue_asset_health_load($load_result, $reset_on_ok, array(), $load_issue);
				//260912.1956 A standby fallback outage is historical issue context, not an extra page-load; queue it once on the failure transition while Current Health applies the separate safety-net score.
				foreach($standby_fallback_failures as $failure_id => $failure)
					if(empty($old_failures[$failure_id]))
					{
						$type_label = (substr($failure_id, -3) === '_js') ? 'JS' : 'CSS';
						self::queue_asset_health_issue_snapshot('fallback-unavailable', 'WP Loader '.$type_label, 'The Full WordPress Dynamic fallback could not be loaded or confirmed active while preferred '.$type_label.' delivery was still working.');
					}
				//260912.0258 Trusted administrator probes run the Health Logkeeper immediately so their response reflects the event just queued; frontend pages remain queue-only.
				self::run_health_logkeeper();
				$new_health_log = self::asset_health_log_state();
				$new_health_status = (!empty($new_health_log['status'])) ? (string)$new_health_log['status'] : 'unknown';
				if($reset_health)
					$recheck_message = ($reset_on_ok) ? 'Current delivery and its fallback are healthy. Recent scoring history was reset.' : (($standby_fallback_failures) ? 'Current delivery is working, but a fallback route is unavailable. Recent health history was kept.' : (($load_result === 'fallback') ? 'Current delivery is working through fallback. Recent health history was kept.' : 'Current delivery still has a failure. Recent health history was kept.'));
			}

			$health_changed = serialize($old_failures) !== serialize($failures) || serialize($old_runtime_warnings) !== serialize($runtime_warnings) || $old_health_status !== $new_health_status;
			wp_send_json_success(array(
				'failures' => count($failures),
				'runtime_warnings' => count($runtime_warnings),
				'load_result' => $load_result,
				'recheck_message' => $recheck_message,
				'reload' => $health_changed || $reset_health,
			));
		}

		/**
		 * Prints the real-page asset activation monitor.
		 *
		 * A short configurable wait after window.load avoids racing normal delivery; healthy pages make no runtime-suspicion request.
		 * An asset that cannot be confirmed active after that wait is a low-trust timing suspicion only.
		 * The real page reports it for a later trusted browser check; unlike the first v260909 monitor, a recoverable miss no longer injects a WordPress fallback request merely because an optimizer may have delayed execution.
		 *
		 * @package s2Member\Utilities
		 * @since 260904.2255
		 *
		 * @attaches-to ``add_action('wp_footer');``
		 * @return null
		 */
		public static function page_asset_runtime_monitor()
		{
			if(is_admin() || !self::$page_asset_expectations)
				return;

			//260912.0304 Queue one page-level asset load now, preserving compact fallback context before healthy traffic can push it out of the rolling score window.
			$page_result = self::page_asset_health_load_result();
			$load_record = self::queue_asset_health_load($page_result, FALSE, array(), self::page_asset_health_issue($page_result));
			$load = (!empty($load_record['load']) && is_array($load_record['load'])) ? $load_record['load'] : array();

			$expectations = array();
			foreach(self::$page_asset_expectations as $expectation)
				$expectations[] = array(
					(string)$expectation['id'],
					(string)$expectation['asset_id'],
					(string)$expectation['url'],
					(string)$expectation['delivery'],
					(string)$expectation['tag_value'],
					(string)$expectation['signature'],
				);
			//260912.0522 The wait setting changes only when an asset becomes a Late suspicion; it never delays loading and does not trigger speculative fallback injection.
			$wait_seconds = (!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['asset_health_wait_seconds'])) ? (int)$GLOBALS['WS_PLUGIN__']['s2member']['o']['asset_health_wait_seconds'] : 3;
			$wait_seconds = max(1, min(60, $wait_seconds));
			$config = array('ajax_url' => admin_url('admin-ajax.php'), 'expectations' => $expectations, 'delay' => $wait_seconds * 1000, 'load' => $load);

			//260912.0522 A Late activation is diagnostic evidence, not proof that loading failed; report it without racing an optimizer with a second CSS/JS response.
			echo '<script type="text/javascript" id="ws-plugin--s2member-asset-runtime-monitor">(function(c){function t(e){return /_js$/.test(e[0])?"js":"css"}function n(e){var i="ws-plugin--s2member-asset-health-"+e[0].replace(/_/g,"-"),o=document.getElementById(i);if(!o){o=document.createElement("span");o.id=i;o.style.cssText="position:absolute;left:-99999px;top:-99999px;width:1px;height:1px;visibility:hidden";(document.body||document.documentElement).appendChild(o)}return o}function ok(e){if(t(e)==="js")return !!(window.ws_plugin__s2member_asset_health&&window.ws_plugin__s2member_asset_health[e[0]]===e[4]);return !window.getComputedStyle||String(getComputedStyle(n(e)).zIndex)===e[4]}function report(m){if(!window.fetch||!m.length)return;fetch(c.ajax_url,{method:"POST",cache:"no-store",credentials:"same-origin",keepalive:true,headers:{"Content-Type":"application/x-www-form-urlencoded;charset=UTF-8"},body:"action=ws_plugin__s2member_asset_runtime_suspect&missing="+encodeURIComponent(JSON.stringify(m))+"&load="+encodeURIComponent(JSON.stringify(c.load||{}))}).catch(function(){})}function check(){var m=c.expectations.filter(function(e){return !ok(e)});if(m.length)report(m)}c.expectations.filter(function(e){return t(e)==="css"}).forEach(n);function go(){setTimeout(check,c.delay)}document.readyState==="complete"?go():addEventListener("load",go,false)})('.wp_json_encode($config).');</script>' . "\n";
			return;
		}

		/**
		 * Records signed low-trust frontend runtime suspicions without changing delivery state.
		 *
		 * Reports are rate-limited and only force a later trusted administrator-browser confirmation.
		 * The standalone AJAX reporter is now the normal path; the recovery-query validator remains for compatibility with already-cached pages from the first v260909 monitor.
		 *
		 * @package s2Member\Utilities
		 * @since 260905.0009
		 *
		 * @param array $missing Missing runtime expectations.
		 * @param array $load Optional signed metadata for the page asset load being corrected to Late.
		 * @return int Number of newly recorded suspicions.
		 */
		protected static function record_asset_runtime_suspicions($missing = array(), $load = array())
		{
			if(!is_array($missing) || !$missing)
				return 0;
			$missing = array_slice($missing, 0, 4);
			$suspicions = self::asset_runtime_suspicions();
			$recorded = 0;
			$valid_late_page = FALSE;
			$late_issue = array();
			foreach($missing as $expectation)
			{
				if(is_array($expectation) && isset($expectation[0]) && !isset($expectation['id']))
					$expectation = self::expand_asset_runtime_expectation($expectation);
				if(!is_array($expectation) || empty($expectation['signature']))
					continue;
				$signature = (string)$expectation['signature'];
				unset($expectation['signature']);
				if(!hash_equals(self::asset_runtime_expectation_signature($expectation), $signature) || !self::asset_runtime_expectation_is_current($expectation))
					continue;
				$valid_late_page = TRUE;
				if(!$late_issue)
					$late_issue = array(
						'label' => self::asset_runtime_health_label((string)$expectation['id']),
						'detail' => 's2Member could not confirm that this asset became active within the configured wait time.',
					);
				$key = md5((string)$expectation['id']."\0".(string)$expectation['url']."\0".(string)$expectation['activation_tag']);
				if(get_transient('ws_plugin__s2member_asset_runtime_suspect_'.$key))
					continue;

				$first_report = empty($suspicions[$key]); //260907.2203 Avoid duplicating the same low-trust runtime suspicion in the log.

				set_transient('ws_plugin__s2member_asset_runtime_suspect_'.$key, 1, MINUTE_IN_SECONDS);
				$expectation['reported'] = time();
				$expectation['signature'] = $signature;
				$suspicions[$key] = $expectation;

				//260907.2203 Record the first frontend runtime suspicion for later troubleshooting and trusted confirmation.
				if($first_report)
					c_ws_plugin__s2member_utils_logs::log_entry('css-js', array(
						'event' => 'Frontend CSS/JS runtime issue reported', 'result' => 'suspected', 'asset' => (string)$expectation['id'],
						'delivery' => (string)$expectation['delivery'], 'url' => (string)$expectation['url'], 'trusted_confirmation_pending' => TRUE,
					));

				$recorded++;
			}
			if($recorded)
				update_option('ws_plugin__s2member_asset_runtime_suspicions', $suspicions, FALSE);
			if($valid_late_page)
			{
				//260910.2346 Current pages send signed load metadata so Late corrects the original page instead of becoming a second load. Cached first-v260909 pages have no load metadata, so only a newly accepted/rate-limited suspicion contributes standalone Late evidence.
				if($load || $recorded)
					self::queue_asset_health_load('late', FALSE, $load, $late_issue);
			}
			return $recorded;
		}

		/**
		 * Records signed runtime suspicions carried by a page-local WordPress recovery request.
		 *
		 * @package s2Member\Utilities
		 * @since 260905.0009
		 *
		 * @return int Number of newly recorded suspicions.
		 */
		public static function record_asset_runtime_recovery_suspicion()
		{
			if(empty($_GET['s2member_asset_runtime_suspect']))
				return 0;
			$missing = json_decode(wp_unslash($_GET['s2member_asset_runtime_suspect']), TRUE);
			return self::record_asset_runtime_suspicions($missing);
		}

		/**
		 * Records a low-trust frontend runtime suspicion without changing delivery state.
		 *
		 * This endpoint remains available for misses where loading a full fallback could duplicate JavaScript that already ran.
		 *
		 * @package s2Member\Utilities
		 * @since 260904.2255
		 *
		 * @return null Exits through WordPress JSON helpers.
		 */
		public static function ajax_asset_runtime_suspicion()
		{
			$missing = (!empty($_POST['missing'])) ? json_decode(wp_unslash($_POST['missing']), TRUE) : array();
			$load = (!empty($_POST['load'])) ? json_decode(wp_unslash($_POST['load']), TRUE) : array();
			wp_send_json_success(array('recorded' => self::record_asset_runtime_suspicions($missing, $load)));
		}

		/**
		 * Returns the source definition for one currently enabled/compatible generated frontend asset file.
		 *
		 * @package s2Member\Utilities
		 * @since 260903.0525
		 *
		 * @param string $id              Logical generated filename.
		 * @param bool   $include_sources Build ordered source definitions only when an asset actually needs generation.
		 * @return array Definition result.
		 */
		protected static function static_asset_definition($id = '', $include_sources = TRUE)
		{
			$id = strtolower((string)$id);
			if(!in_array($id, array('s2member.css', 's2member-pro.css', 's2member.js', 's2member-pro.js'), TRUE))
				return array('ok' => FALSE, 'sources' => array(), 'minify' => FALSE, 'error' => 'Invalid static asset ID');
			$type = substr(strrchr($id, '.'), 1);
			$pro_file = strpos($id, 's2member-pro.') === 0;
			$combine = !$pro_file && !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['static_assets_combine']);
			$o = $GLOBALS['WS_PLUGIN__']['s2member']['o'];
			$c = $GLOBALS['WS_PLUGIN__']['s2member']['c'];
			if($pro_file && !in_array($id, self::static_asset_ids($type, 'all'), TRUE))
				return array('ok' => FALSE, 'sources' => array(), 'minify' => FALSE, 'error' => 'Separate Pro static asset is not active');

			if($type === 'css')
			{
				if(empty($o['static_css']))
					return array('ok' => FALSE, 'sources' => array(), 'minify' => FALSE, 'error' => 'Static CSS Delivery is disabled');

				//260903.0729 Framework-level dynamic requirements are authoritative; Pro may narrow only the generic during-CSS hook requirement when all callbacks are known static-compatible built-ins.
				$framework_dynamic = has_action('ws_plugin__s2member_before_css') || isset($GLOBALS['wp_filter']['all']);
				$hook_dynamic = has_action('ws_plugin__s2member_during_css');
				$hook_dynamic = (bool)apply_filters('ws_plugin__s2member_dynamic_css_required', $hook_dynamic, get_defined_vars());

				if($framework_dynamic || $hook_dynamic)
					return array('ok' => FALSE, 'sources' => array(), 'minify' => FALSE, 'error' => 'Current CSS hooks require legacy dynamic assets');
				if(!$include_sources)
					return array('ok' => TRUE, 'sources' => array(), 'minify' => !empty($o['static_css_minify']), 'error' => '');

				$sources = ($pro_file) ? array() : array(array('file' => $c['dir'].'/src/includes/s2member.css', 'preserve_header' => TRUE));
				if(!$pro_file)
					$sources = (array)apply_filters('ws_plugin__s2member_static_css_sources', $sources, get_defined_vars());
				if($pro_file || $combine)
					$sources = (array)apply_filters('ws_plugin__s2member_static_pro_css_sources', $sources, get_defined_vars());
				if(!$sources)
					return array('ok' => FALSE, 'sources' => array(), 'minify' => FALSE, 'error' => 'No static CSS sources are available for '.$id);
				return array('ok' => TRUE, 'sources' => $sources, 'minify' => !empty($o['static_css_minify']), 'error' => '');
			}
			if($type === 'js')
			{
				if(empty($o['static_js']))
					return array('ok' => FALSE, 'sources' => array(), 'minify' => FALSE, 'error' => 'Static JS Delivery is disabled');
				if(!function_exists('wp_add_inline_script'))
					return array('ok' => FALSE, 'sources' => array(), 'minify' => FALSE, 'error' => 'Static JS requires WordPress 4.5+');

				$page_text = self::static_js_text_delivery() === 'page';
				if($page_text && !self::static_js_page_text_supported())
					return array('ok' => FALSE, 'sources' => array(), 'minify' => FALSE, 'error' => 'Loading JavaScript text with each WordPress page requires a current s2Member Pro version');

				if($page_text)
				{
					//260906.2049 Text and other page-specific values resolve in the normal HTML request, so they do not make the external JavaScript dynamic.
					$framework_dynamic = apply_filters('ws_plugin__s2member_js_api_constants_enable', FALSE)
						|| has_action('ws_plugin__s2member_before_js_w_globals') || isset($GLOBALS['wp_filter']['all']);
				}
				else
				{
					$site_locale = (string)get_option('WPLANG');
					if(!$site_locale && defined('WPLANG'))
						$site_locale = (string)WPLANG;
					$site_locale = ($site_locale) ? $site_locale : 'en_US';
					$current_locale = (function_exists('determine_locale')) ? (string)determine_locale() : (string)get_locale();
					$framework_dynamic = apply_filters('ws_plugin__s2member_js_api_constants_enable', FALSE)
						|| has_action('ws_plugin__s2member_before_js_w_globals') || $current_locale !== $site_locale || has_filter('ws_plugin__s2member_files_dir')
						|| has_filter('ws_plugin__s2member_min_password_length') || has_filter('ws_plugin__s2member_min_password_strength_code') || has_filter('ws_plugin__s2member_min_password_strength_score')
						|| isset($GLOBALS['wp_filter']['all']);
				}

				$hook_dynamic = has_action('ws_plugin__s2member_during_js_w_globals');
				$hook_dynamic = (bool)apply_filters('ws_plugin__s2member_dynamic_js_required', $hook_dynamic, get_defined_vars());
				if($hook_dynamic && !$page_text && self::static_js_builtin_pro_callbacks_supported())
					$hook_dynamic = FALSE; //260906.2049 Older Pro releases can still use static-file text when their JavaScript hook contains only known built-ins.

				if($framework_dynamic || $hook_dynamic)
					return array('ok' => FALSE, 'sources' => array(), 'minify' => FALSE, 'error' => 'Current JavaScript hooks/configuration require dynamic assets');
				if($page_text)
				{
					$data_map_signature = self::static_js_data_map_signature($id);
					if(empty($data_map_signature['ok']))
						return array('ok' => FALSE, 'sources' => array(), 'minify' => FALSE, 'error' => (string)$data_map_signature['error']);
				}
				if(!$include_sources)
					return array('ok' => TRUE, 'sources' => array(), 'minify' => !empty($o['static_js_minify']), 'error' => '');

				$sources = ($pro_file) ? array() : array(
					array('file' => $c['dir'].'/src/includes/jquery/jquery.sprintf/jquery.sprintf.js', 'preserve_header' => TRUE),
					($page_text)
						? array('file' => $c['dir'].'/src/includes/s2member.js', 'data_map' => $c['dir'].'/src/includes/s2member.js.php', 'data_key' => 'f')
						: array('file' => $c['dir'].'/src/includes/s2member.js', 'render' => TRUE),
				);
				if(!$pro_file)
					$sources = (array)apply_filters('ws_plugin__s2member_static_js_sources', $sources, get_defined_vars());
				if($pro_file || $combine)
				{
					$source_count = count($sources);
					$sources = (array)apply_filters('ws_plugin__s2member_static_pro_js_sources', $sources, get_defined_vars());
					if(!$page_text && c_ws_plugin__s2member_utils_conds::pro_is_installed() && count($sources) === $source_count)
					{
						$pro_output = self::static_js_builtin_pro_output();
						if($pro_output === '')
							return array('ok' => FALSE, 'sources' => array(), 'minify' => FALSE, 'error' => 'No compatible static JavaScript source is available from the installed s2Member Pro version');
						$sources[] = array('contents' => $pro_output);
					}
				}
				if(!$sources)
					return array('ok' => FALSE, 'sources' => array(), 'minify' => FALSE, 'error' => 'No static JavaScript sources are available for '.$id);
				return array('ok' => TRUE, 'sources' => $sources, 'minify' => !empty($o['static_js_minify']), 'error' => '');
			}
			return array('ok' => FALSE, 'sources' => array(), 'minify' => FALSE, 'error' => 'Invalid static asset type');
		}

		/**
		 * Replaces marked PHP interpolations in a canonical JavaScript source with compact data-map slots.
		 *
		 * The current canonical sources place every marked interpolation inside a single-quoted JavaScript
		 * string. Replacing only the PHP block with `'+d[n]+'` preserves that historical string coercion.
		 *
		 * @package s2Member\Utilities
		 * @since 260906.0738
		 *
		 * @param string $source       Canonical mixed JS/PHP source.
		 * @param string $data_map_path Shipped data-map path.
		 * @param string $data_key     Browser namespace key (`f` or `p`).
		 * @return array Transform result.
		 */
		protected static function static_js_data_source($source = '', $data_map_path = '', $data_key = '')
		{
			$data_map = self::static_js_data_map($data_map_path);
			if(empty($data_map['ok']))
				return array('ok' => FALSE, 'source' => '', 'error' => (string)$data_map['error']);
			if(!preg_match('/^[a-z][a-z0-9_]*$/i', (string)$data_key))
				return array('ok' => FALSE, 'source' => '', 'error' => 'Invalid JavaScript data namespace key');
			$slots = $data_map['slots'];
			$errors = array();
			$source = preg_replace_callback('/<\\?php.*?\\?>/s', function($match) use ($slots, &$errors) {
				if(!preg_match('/\\/\\*d\\*\\/(.*?)\\/\\*b\\*\\//s', $match[0], $data_match))
				{
					$errors[] = 'An unmarked PHP interpolation remains in a static JavaScript data source';
					return $match[0];
				}
				$expression = trim((string)$data_match[1]);
				if(!isset($slots[$expression]))
				{
					$errors[] = 'A marked JavaScript expression is missing from its shipped data map';
					return $match[0];
				}
				return "'+d[".(int)$slots[$expression]."]+'";
			}, (string)$source);
			if($errors || strpos($source, '<?php') !== FALSE || strpos($source, '?>') !== FALSE)
				return array('ok' => FALSE, 'source' => '', 'error' => ($errors) ? implode('; ', array_unique($errors)) : 'PHP remained after static JavaScript data transformation');
			//260906.0738 Keep the short alias lexical to this source so Framework and Pro slots cannot overwrite one another in separate or combined files.
			return array('ok' => TRUE, 'source' => "(function(d){\n".$source."\n})(window.s2_data.".$data_key.");", 'error' => '');
		}

		/**
		 * Includes one trusted shipped static JavaScript data map in normal WordPress page context.
		 *
		 * @package s2Member\Utilities
		 * @since 260906.0738
		 *
		 * @param string $path Data-map path.
		 * @param array|null $keys Optional stable slot IDs; NULL evaluates every value in the data map.
		 * @return array|false Data-map values, or FALSE on failure.
		 */
		protected static function load_static_js_data_map($path = '', $keys = NULL)
		{
			if(!$path || !is_readable($path))
				return FALSE;
			$s2_data_keys = (is_array($keys)) ? array_fill_keys(array_map('intval', $keys), TRUE) : NULL;
			$data = include $path;
			return (is_array($data)) ? $data : FALSE;
		}

		/**
		 * Returns page-local JavaScript data for the active generated static files.
		 *
		 * Complete data maps are emitted for now. TO-DO: pass page-specific sparse slot sets once feature requirements can be determined safely.
		 *
		 * @package s2Member\Utilities
		 * @since 260906.0738
		 *
		 * @param array $assets Active generated JavaScript assets keyed by logical filename.
		 * @return string Inline JavaScript, or an empty string when data-map loading fails.
		 */
		public static function static_js_inline_data($assets = array())
		{
			if(self::static_js_text_delivery() !== 'page')
				return '';
			$paths = array();
			foreach(array_keys((array)$assets) as $id)
				foreach(self::static_js_data_map_paths($id) as $key => $path)
					$paths[$key] = $path;
			if(!$paths)
				return '';

			$data = array();
			foreach($paths as $key => $path)
			{
				$data_map = self::load_static_js_data_map($path);
				if($data_map === FALSE)
					return '';
				$data[$key] = $data_map;
			}
			$json = wp_json_encode($data);
			if(!is_string($json) || $json === '')
				return '';
			//260906.0738 wp_add_inline_script() prints this in HTML; neutralize user-translatable closing-script sequences just like existing inline current-user globals.
			$inline = 'window.s2_data='.str_ireplace('</', '<\\/', $json).';';
			$extra = (string)apply_filters('ws_plugin__s2member_static_js_inline_globals', '', $assets, get_defined_vars());
			$extra = str_ireplace('</', '<\\/', $extra); //260906.0738 Pro gateway globals may contain translated text too, so apply the same closing-script protection.
			return $inline.(($extra !== '') ? "\n".$extra : '');
		}

		/**
		 * Formats one preserved source notice compactly without turning it into an unreadable single line.
		 *
		 * @package s2Member\Utilities
		 * @since 260905.0106
		 *
		 * @param string $comment Original leading source docblock.
		 * @return string Compact readable preserved notice.
		 */
		protected static function preserved_asset_header($comment = '')
		{
			$header = trim((string)$comment);
			$header = preg_replace('/\A\/\*\*|\*\/\z/', '', $header);
			$header = preg_replace('/^\s*\*\s?/m', '', $header);
			$header = preg_replace('/\s+/', ' ', trim($header));
			$header = str_replace(array('Â©', '©'), '(c)', $header);
			return "/*!\n * ".wordwrap($header, 140, "\n * ", FALSE)."\n */";
		}

		/**
		 * Prunes stale generated generations after a successful build.
		 *
		 * Keep current build files protected, retain up to ten older generations for cached HTML,
		 * and remove anything older than 30 days. This bounds normal disk use without deleting the
		 * previous timestamp immediately after a refresh.
		 *
		 * @package s2Member\Utilities
		 * @since 260905.0106
		 *
		 * @param string $dir Current generated-asset directory.
		 * @param string $new_path Newly generated file path that must be preserved.
		 * @return null
		 */
		protected static function prune_static_asset_generations($dir = '', $new_path = '')
		{
			$dir = rtrim((string)$dir, '/\\');
			if(!$dir || !is_dir($dir))
				return;
			$protected = array();
			$builds = get_option('ws_plugin__s2member_static_asset_builds', array());
			if(is_array($builds))
				foreach($builds as $id => $build)
				if(in_array($id, array('s2member.css', 's2member-pro.css', 's2member.js', 's2member-pro.js'), TRUE) && ($build = abs((int)$build)))
				{
					$type = substr(strrchr($id, '.'), 1);
					$base = substr($id, 0, -strlen('.'.$type));
					$protected[$dir.'/'.$base.'-'.$build.'.'.$type] = TRUE;
				}
			if($new_path)
				$protected[(string)$new_path] = TRUE;

			$groups = array();
			foreach(array('css', 'js') as $type)
				foreach((array)glob($dir.'/s2member*.'.$type) as $path)
					if(preg_match('/\/(s2member(?:-pro)?)-\d+\.(css|js)\z/', str_replace('\\', '/', $path), $match))
						$groups[$match[1].'.'.$match[2]][] = $path;

			$removed = array(); //260907.2203 Collect only files actually removed so cleanup logging stays accurate.

			foreach($groups as $paths)
			{
				usort($paths, function($a, $b) {
					return (int)@filemtime($b) - (int)@filemtime($a);
				});
				$stale_kept = 0;
				foreach($paths as $old_path)
				{
					if(isset($protected[$old_path]))
						continue;
					$stale_kept++;
					if((int)@filemtime($old_path) < time() - 30 * DAY_IN_SECONDS || $stale_kept > 10)
						if(@unlink($old_path))
							$removed[] = basename($old_path);
				}
			}

			//260907.2203 Record cleanup only when stale files were actually deleted.
			if($removed)
				c_ws_plugin__s2member_utils_logs::log_entry('css-js', array('event' => 'Stale Static CSS/JS files pruned', 'result' => 'success', 'removed' => $removed));

			return;
		}

		/**
		 * Builds one timestamped CSS/JS file in the WordPress uploads tree.
		 *
		 * @package s2Member\Utilities
		 * @since 260903.0525
		 *
		 * @param string $id      Stable asset identifier.
		 * @param int    $build   Timestamp used in the generated filename.
		 * @param string $type    `css` or `js`.
		 * @param array  $sources Ordered source definitions/files.
		 * @param bool   $minify  Whether generated output should be minified.
		 * @return array Build result with URL/path or error.
		 */
		protected static function build_static_asset($id = '', $build = 0, $type = '', $sources = array(), $minify = FALSE)
		{
			$id = trim(preg_replace('/[^a-z0-9_\-]/i', '-', (string)$id), '-');
			$type = strtolower((string)$type);
			if(!$id || !$build || !in_array($type, array('css', 'js'), TRUE) || !$sources)
				return array('ok' => FALSE, 'url' => '', 'path' => '', 'error' => 'Invalid generated asset parameters');

			$location = self::static_assets_location(TRUE);
			if(empty($location['ok']))
				return array('ok' => FALSE, 'url' => '', 'path' => '', 'error' => $location['error']);
			$filename = $id.'-'.(int)$build.'.'.$type;
			$path = $location['dir'].'/'.$filename;
			$url = $location['url'].'/'.$filename;
			$headers = array();
			$body = '';
			foreach($sources as $source)
			{
				$source = is_array($source) ? $source : array('file' => $source);
				if(array_key_exists('contents', $source))
					$chunk = (string)$source['contents'];
				else if(empty($source['file']) || !is_readable($source['file']))
					return array('ok' => FALSE, 'url' => '', 'path' => '', 'error' => 'Source file is not readable: '.((!empty($source['file'])) ? $source['file'] : '(missing path)'));
				else if(!empty($source['data_map']))
				{
					if(($chunk = file_get_contents($source['file'])) === FALSE)
						return array('ok' => FALSE, 'url' => '', 'path' => '', 'error' => 'Could not read source file: '.$source['file']);
					$transformed = self::static_js_data_source($chunk, (string)$source['data_map'], (!empty($source['data_key'])) ? (string)$source['data_key'] : '');
					if(empty($transformed['ok']))
						return array('ok' => FALSE, 'url' => '', 'path' => '', 'error' => (string)$transformed['error'].' in '.$source['file']);
					$chunk = $transformed['source'];
				}
				else if(!empty($source['render']))
				{
					$template_vars = (!empty($source['vars']) && is_array($source['vars'])) ? $source['vars'] : array();
					extract($template_vars, EXTR_SKIP);
					ob_start();
					include $source['file'];
					$chunk = ob_get_clean();
				}
				else if(($chunk = file_get_contents($source['file'])) === FALSE)
					return array('ok' => FALSE, 'url' => '', 'path' => '', 'error' => 'Could not read source file: '.$source['file']);

				if(preg_match('/\A\s*(\/\*\*.*?\*\/)\s*/s', $chunk, $match))
				{
					if(!empty($source['preserve_header']))
					{
						//260905.0106 Preserve the complete notice in a compact wrapped block instead of a huge original banner or an unreadable single line.
						$header = self::preserved_asset_header($match[1]);
						if(!in_array($header, $headers, TRUE))
							$headers[] = $header;
					}
					$chunk = preg_replace('/\A\s*\/\*\*.*?\*\/\s*/s', '', $chunk, 1);
				}
				if(!empty($source['replacements']) && is_array($source['replacements']))
					$chunk = str_replace(array_keys($source['replacements']), array_values($source['replacements']), $chunk);
				$body .= "\n".((!empty($source['prefix'])) ? $source['prefix']."\n" : '').$chunk.((!empty($source['suffix'])) ? "\n".$source['suffix'] : '');
			}

			//260906.2219 Personal/member globals are page-specific by design and must never be written into a publicly cacheable static JavaScript file.
			if($type === 'js' && preg_match('/\bS2MEMBER_CURRENT_USER_[A-Z0-9_]+\s*=(?!=)/', $body))
				return array('ok' => FALSE, 'url' => '', 'path' => '', 'error' => 'Page-specific member globals cannot be stored in static JavaScript');

			try
			{
				$body = ($minify) ? (($type === 'css') ? self::compress_css($body) : self::compress_js($body)) : trim($body);
			}
			catch(Exception $e)
			{
				return array('ok' => FALSE, 'url' => '', 'path' => '', 'error' => 'JavaScript minification failed: '.$e->getMessage());
			}
			$activation_tag = self::static_activation_tag_snippet($id, $type, $build);
			$output = (($headers) ? implode("\n", $headers)."\n" : '').$body."\n".$activation_tag."\n";
			$tmp = $path.'.tmp-'.uniqid('', TRUE);
			if(file_put_contents($tmp, $output, LOCK_EX) === FALSE || (!@rename($tmp, $path) && !is_file($path)))
			{
				@unlink($tmp);
				return array('ok' => FALSE, 'url' => '', 'path' => '', 'error' => 'Could not write generated asset: '.$path);
			}
			@unlink($tmp);

			return array('ok' => TRUE, 'url' => $url, 'path' => $path, 'error' => '');
		}

		/**
		 * Resolves the writable/public directory used for generated static frontend assets.
		 *
		 * @package s2Member\Utilities
		 * @since 260903.0437
		 *
		 * @param bool $for_write Create/validate the directory for a write operation.
		 * @return array Location result.
		 */
		protected static function static_assets_location($for_write = FALSE)
		{
			$key = ($for_write) ? 'write' : 'read';
			if(isset(self::$static_assets_location_cache[$key]))
				return self::$static_assets_location_cache[$key];
			$uploads = wp_upload_dir(NULL, (bool)$for_write);
			if(!empty($uploads['error']) || empty($uploads['basedir']) || empty($uploads['baseurl']))
				return self::$static_assets_location_cache[$key] = array('ok' => FALSE, 'dir' => '', 'url' => '', 'error' => 'WordPress could not resolve a usable uploads directory'.((!empty($uploads['error'])) ? ': '.$uploads['error'] : ''));

			$dir = untrailingslashit((string)apply_filters('ws_plugin__s2member_static_assets_dir', trailingslashit($uploads['basedir']).'s2member-assets', $uploads));
			$url = untrailingslashit((string)apply_filters('ws_plugin__s2member_static_assets_url', trailingslashit($uploads['baseurl']).'s2member-assets', $uploads));
			if(!$dir || !$url)
				return self::$static_assets_location_cache[$key] = array('ok' => FALSE, 'dir' => '', 'url' => '', 'error' => 'The static-assets directory or URL filter returned an empty value');
			if($for_write)
			{
				if(!is_dir($dir) && !wp_mkdir_p($dir))
					return self::$static_assets_location_cache[$key] = array('ok' => FALSE, 'dir' => $dir, 'url' => $url, 'error' => 'Could not create static-assets directory: '.$dir);
				if(!is_writable($dir))
					return self::$static_assets_location_cache[$key] = array('ok' => FALSE, 'dir' => $dir, 'url' => $url, 'error' => 'Static-assets directory is not writable: '.$dir);

				//260905.0158 Discourage casual directory listing without adding executable PHP to the public uploads directory.
				$index_file = $dir.'/index.html';
				if(!is_file($index_file))
					@file_put_contents($index_file, '<!-- Silence is golden. -->'."\n", LOCK_EX);
			}
			return self::$static_assets_location_cache[$key] = array('ok' => TRUE, 'dir' => $dir, 'url' => $url, 'error' => '');
		}

		/**
		 * Handles CSS compression of hex colors.
		 *
		 * @package s2Member\Utilities
		 * @since 3.5
		 *
		 * @param array $m Array of matches from ``preg_replace_callback()``.
		 * @return string Shortened hex code when possible, full hex code otherwise.
		 */
		public static function _compress_css_c3($m = FALSE)
		{
			if($m[2][0] === $m[2][1] && $m[2][2] === $m[2][3] && $m[2][4] === $m[2][5])
				return $m[1].$m[2][0].$m[2][2].$m[2][4];
			return $m[0];
		}

		/**
		 * JShrink 1.8.1 adaptation used for generated JavaScript minification.
		 *
		 * JShrink is Copyright (c) Robert Hafner and licensed under BSD-3-Clause.
		 * See `/src/licensing/jshrink.txt` for the complete license and attribution.
		 * @see https://github.com/tedious/JShrink
		 *
		 * The upstream parser is kept intentionally isolated behind `jshrink_*` names.
		 * The only PHP 5.6 compatibility change avoids PHP 7.1+ negative string offsets
		 * when tracking the last character.
		 *
		 * @package s2Member\Utilities
		 * @since 260903.0437
		 */
		protected $jshrink_input;
		protected $jshrink_len = 0;
		protected $jshrink_index = 0;
		protected $jshrink_a = '';
		protected $jshrink_b = '';
		protected $jshrink_c;
		protected $jshrink_last_char;
		protected $jshrink_output = '';
		protected $jshrink_options = array();
		protected $jshrink_string_delimiters = array("'" => TRUE, '"' => TRUE, '`' => TRUE);
		protected $jshrink_no_new_line_characters = array('(' => TRUE, '-' => TRUE, '+' => TRUE, '[' => TRUE, '#' => TRUE, '@' => TRUE);
		protected static $jshrink_default_options = array('flaggedComments' => TRUE);
		protected static $jshrink_keywords = array('delete', 'do', 'for', 'in', 'instanceof', 'return', 'typeof', 'yield');
		protected $jshrink_max_keyword_len = 0;
		protected $jshrink_locks = array();

		protected function jshrink_minify_to_string($js, $options)
		{
			$this->jshrink_initialize($js, $options);
			$this->jshrink_loop();
			$output = $this->jshrink_output;
			$this->jshrink_clean();
			return $output;
		}

		protected function jshrink_initialize($js, $options)
		{
			$this->jshrink_options = array_merge(self::$jshrink_default_options, $options);
			$this->jshrink_input = $js.PHP_EOL;
			$this->jshrink_len = strlen($this->jshrink_input);
			$this->jshrink_a = "\n";
			$this->jshrink_b = "\n";
			$this->jshrink_last_char = "\n";
			$this->jshrink_output = '';
			$this->jshrink_max_keyword_len = max(array_map('strlen', self::$jshrink_keywords));
		}

		protected function jshrink_echo($char)
		{
			$this->jshrink_output .= $char;
			//260903.0437 JShrink 1.8.1 uses `$char[-1]`; `substr()` preserves that behavior on s2Member's PHP 5.6 minimum.
			$this->jshrink_last_char = substr($char, -1);
		}

		protected function jshrink_loop()
		{
			while($this->jshrink_a !== FALSE && !is_null($this->jshrink_a) && $this->jshrink_a !== '')
			{
				switch($this->jshrink_a)
				{
					case "\r":
					case "\n":
						if($this->jshrink_b !== FALSE && isset($this->jshrink_no_new_line_characters[$this->jshrink_b]))
						{
							$this->jshrink_echo($this->jshrink_a);
							$this->jshrink_save_string();
							break;
						}
						if($this->jshrink_b === ' ')
							break;
					case ' ':
						if(self::jshrink_is_alphanumeric($this->jshrink_b))
							$this->jshrink_echo($this->jshrink_a);
						$this->jshrink_save_string();
						break;
					default:
						switch($this->jshrink_b)
						{
							case "\r":
							case "\n":
								if(strpos('}])+-"\'', $this->jshrink_a) !== FALSE)
								{
									$this->jshrink_echo($this->jshrink_a);
									$this->jshrink_save_string();
									break;
								}
								else if(self::jshrink_is_alphanumeric($this->jshrink_a))
								{
									$this->jshrink_echo($this->jshrink_a);
									$this->jshrink_save_string();
								}
								break;
							case ' ':
								if(!self::jshrink_is_alphanumeric($this->jshrink_a))
									break;
							default:
								if($this->jshrink_a === '/' && ($this->jshrink_b === "'" || $this->jshrink_b === '"'))
								{
									$this->jshrink_save_regex();
									continue 3;
								}
								$this->jshrink_echo($this->jshrink_a);
								$this->jshrink_save_string();
								break;
						}
				}

				$this->jshrink_b = $this->jshrink_get_real();
				if($this->jshrink_b == '/')
				{
					$valid_tokens = "(,=:[!&|?\n";
					$last_token = ($this->jshrink_a == ' ') ? $this->jshrink_last_char : $this->jshrink_a;
					if(strpos($valid_tokens, $last_token) !== FALSE || $this->jshrink_ends_in_keyword())
						$this->jshrink_save_regex();
				}
			}
		}

		protected function jshrink_clean()
		{
			unset($this->jshrink_input, $this->jshrink_c, $this->jshrink_options);
			$this->jshrink_len = $this->jshrink_index = 0;
			$this->jshrink_a = $this->jshrink_b = '';
			$this->jshrink_output = '';
		}

		protected function jshrink_get_char()
		{
			if(isset($this->jshrink_c))
			{
				$char = $this->jshrink_c;
				unset($this->jshrink_c);
			}
			else
			{
				$char = ($this->jshrink_index < $this->jshrink_len) ? $this->jshrink_input[$this->jshrink_index] : FALSE;
				if($char === FALSE)
					return FALSE;
				$this->jshrink_index++;
			}
			if($char == "\r")
				$char = "\n";
			if($char !== "\n" && $char < "\x20")
				return ' ';
			return $char;
		}

		protected function jshrink_peek()
		{
			if($this->jshrink_index >= $this->jshrink_len)
				return FALSE;
			$char = $this->jshrink_input[$this->jshrink_index];
			if($char == "\r")
				$char = "\n";
			if($char !== "\n" && $char < "\x20")
				return ' ';
			return $char;
		}

		protected function jshrink_get_real()
		{
			$start_index = $this->jshrink_index;
			$char = $this->jshrink_get_char();
			if($char !== '/')
				return $char;
			$this->jshrink_c = $this->jshrink_get_char();
			if($this->jshrink_c === '/')
			{
				$this->jshrink_process_one_line_comments($start_index);
				return $this->jshrink_get_real();
			}
			else if($this->jshrink_c === '*')
			{
				$this->jshrink_process_multi_line_comments($start_index);
				return $this->jshrink_get_real();
			}
			return $char;
		}

		protected function jshrink_process_one_line_comments($start_index)
		{
			$third = ($this->jshrink_index < $this->jshrink_len) ? $this->jshrink_input[$this->jshrink_index] : FALSE;
			$this->jshrink_get_next("\n");
			unset($this->jshrink_c);
			if($third == '@')
			{
				$end = $this->jshrink_index - $start_index;
				$this->jshrink_c = "\n".substr($this->jshrink_input, $start_index, $end);
			}
		}

		protected function jshrink_process_multi_line_comments($start_index)
		{
			$this->jshrink_get_char();
			$third = $this->jshrink_get_char();
			if($third == '*' && $this->jshrink_peek() == '/')
			{
				$this->jshrink_index++;
				return;
			}
			if($this->jshrink_get_next('*/'))
			{
				$this->jshrink_get_char();
				$this->jshrink_get_char();
				$char = $this->jshrink_get_char();
				if((!empty($this->jshrink_options['flaggedComments']) && $third === '!') || $third === '@')
				{
					if($start_index > 0)
					{
						$this->jshrink_echo($this->jshrink_a);
						$this->jshrink_a = ' ';
						if($this->jshrink_input[$start_index - 1] === "\n")
							$this->jshrink_echo("\n");
					}
					$end = ($this->jshrink_index - 1) - $start_index;
					$this->jshrink_echo(substr($this->jshrink_input, $start_index, $end));
					$this->jshrink_c = $char;
					return;
				}
			}
			else
				$char = FALSE;
			if($char === FALSE)
				throw new RuntimeException('Unclosed multiline comment at position: '.($this->jshrink_index - 2));
			$this->jshrink_c = $char;
		}

		protected function jshrink_get_next($string)
		{
			$pos = strpos($this->jshrink_input, $string, $this->jshrink_index);
			if($pos === FALSE)
				return FALSE;
			$this->jshrink_index = $pos;
			return ($this->jshrink_index < $this->jshrink_len) ? $this->jshrink_input[$this->jshrink_index] : FALSE;
		}

		protected function jshrink_save_string()
		{
			$start = $this->jshrink_index;
			$this->jshrink_a = $this->jshrink_b;
			if(!isset($this->jshrink_string_delimiters[$this->jshrink_a]))
				return;
			$type = $this->jshrink_a;
			$this->jshrink_echo($this->jshrink_a);
			while(($this->jshrink_a = $this->jshrink_get_char()) !== FALSE)
			{
				switch($this->jshrink_a)
				{
					case $type:
						break 2;
					case "\n":
						if($type === '`')
							$this->jshrink_echo($this->jshrink_a);
						else
							throw new RuntimeException('Unclosed string at position: '.$start);
						break;
					case '\\':
						$this->jshrink_b = $this->jshrink_get_char();
						if($this->jshrink_b !== "\n")
							$this->jshrink_echo($this->jshrink_a.$this->jshrink_b);
						break;
					default:
						$this->jshrink_echo($this->jshrink_a);
				}
			}
		}

		protected function jshrink_save_regex()
		{
			if($this->jshrink_a != ' ')
				$this->jshrink_echo($this->jshrink_a);
			$this->jshrink_echo($this->jshrink_b);
			$character_class = FALSE;
			$character_class_index = NULL;
			while(($this->jshrink_a = $this->jshrink_get_char()) !== FALSE)
			{
				if($this->jshrink_a === '/' && !$character_class)
					break;
				if($this->jshrink_a === '[')
				{
					$character_class = TRUE;
					$character_class_index = $this->jshrink_index;
				}
				else if($this->jshrink_a === ']')
					$character_class = FALSE;
				if($this->jshrink_a === '\\')
				{
					$this->jshrink_echo($this->jshrink_a);
					$this->jshrink_a = $this->jshrink_get_char();
				}
				if($this->jshrink_a === "\n")
				{
					if($character_class)
						throw new RuntimeException('Unclosed character class at position: '.$character_class_index);
					throw new RuntimeException('Unclosed regex pattern at position: '.$this->jshrink_index);
				}
				$this->jshrink_echo($this->jshrink_a);
			}
			$this->jshrink_b = $this->jshrink_get_real();
		}

		protected static function jshrink_is_alphanumeric($char)
		{
			return preg_match('/^[\w\$\pL]$/', $char) === 1 || $char == '/';
		}

		protected function jshrink_ends_in_keyword()
		{
			$test = substr($this->jshrink_output.$this->jshrink_a, -1 * ($this->jshrink_max_keyword_len + 10));
			foreach(self::$jshrink_keywords as $keyword)
				if(preg_match('/[^\w]'.$keyword.'[ ]?$/i', $test) === 1)
					return TRUE;
			return FALSE;
		}

		protected function jshrink_lock($js)
		{
			$lock = '"LOCK---'.crc32(time()).'"';
			$matches = array();
			preg_match('/([+-])(\s+)([+-])/S', $js, $matches);
			if(empty($matches))
				return $js;
			$this->jshrink_locks[$lock] = $matches[2];
			return preg_replace('/([+-])\s+([+-])/S', '$1'.$lock.'$2', $js);
		}

		protected function jshrink_unlock($js)
		{
			foreach($this->jshrink_locks as $lock => $replacement)
				$js = str_replace($lock, $replacement, $js);
			return $js;
		}
	}
}
