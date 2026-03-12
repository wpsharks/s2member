<?php
// @codingStandardsIgnoreFile
/**
 * No-cache routines.
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
 * @package s2Member\No_Cache
 * @since 3.5
 */
if(!defined('WPINC')) // MUST have WordPress.
	exit ('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_no_cache'))
{
	/**
	 * No-cache routines.
	 *
	 * @package s2Member\No_Cache
	 * @since 3.5
	 */
	class c_ws_plugin__s2member_no_cache
	{
		/**
		 * No-cache headers required?
		 *
		 * @package s2Member\No_Cache
		 * @since 111115
		 *
		 * @var null|bool
		 */
		public static $headers;

		/**
		 * Sets up no-cache header behavior mode.
		 *
		 * This runs on `init` so s2Member options are available (options are configured after hooks are included).
		 *
		 * @package s2Member\No_Cache
		 * @since 260307
		 *
		 * @attaches-to ``add_action('init');``
		 *
		 * @return void
		 */
		public static function setup_no_cache_headers_mode()
		{
			static $once; // Run only once per request.

			if($once)
				return;

			$once = TRUE;

			$mode = (!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['no_cache_headers_mode']))
				? (string)$GLOBALS['WS_PLUGIN__']['s2member']['o']['no_cache_headers_mode']
				: 'always';

			if($mode === 'evaluative' || !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['no_cache_headers_debug']))
				add_filter('wp_headers', 'c_ws_plugin__s2member_no_cache::no_cache_wp_headers', 9999); //260309 Run last; also adds debug header in all modes when enabled.

			if($mode === 'evaluative')
			{
				add_filter('ws_plugin__s2member_no_cache_headers_selective', '__return_true');
				c_ws_plugin__s2member_no_cache::no_cache_constants(FALSE);

				add_action('wp', 'c_ws_plugin__s2member_no_cache::evaluative_no_cache_scan', 3); //260308 Rename staged scan to evaluative scan.
			}
			else
			{
				if($mode === 'selective')
					add_filter('ws_plugin__s2member_no_cache_headers_selective', '__return_true');

				c_ws_plugin__s2member_no_cache::no_cache(FALSE);
			}
		}

		/**
		 * Evaluative mode: scans queried post content for s2Member shortcodes before headers are sent.
		 *
		 * Shortcodes execute after headers (during rendering), so evaluative mode must pre-detect likely s2Member
		 * shortcodes to ensure no-cache headers are applied when needed.
		 *
		 * @package s2Member\No_Cache
		 * @since 260307
		 *
		 * @attaches-to ``add_action('wp');``
		 *
		 * @return void
		 */
		public static function evaluative_no_cache_scan()
		{
			if(!is_singular())
				return;

			$post = get_queried_object();
			if(!is_object($post) || empty($post->post_content) || !is_string($post->post_content))
				return;

			//260307 Conservative pre-detection of s2Member shortcodes in post content.
			// This intentionally errs on the safe side (prevents caching) for any `[s2...` usage.
			if(stripos($post->post_content, '[s2') !== FALSE)
				c_ws_plugin__s2member_no_cache::no_cache_constants(true);
		}

		/**
		 * Handles no-cache constants, and no-cache headers.
		 *
		 * @package s2Member\No_Cache
		 * @since 3.5
		 *
		 * @attaches-to ``add_action('init');``
		 * @also-called-by Other routines within s2Member.
		 *
		 * @param bool $no_cache Optional. Defaults to false. If true, force no-cache if at all possible.
		 *
		 * @return bool This function will always return `true`.
		 */
		public static function no_cache($no_cache = FALSE)
		{
			do_action('ws_plugin__s2member_before_no_cache', get_defined_vars());

			c_ws_plugin__s2member_no_cache::no_cache_constants($no_cache).c_ws_plugin__s2member_no_cache::no_cache_headers($no_cache);

			do_action('ws_plugin__s2member_after_no_cache', get_defined_vars());

			return TRUE; // Always return true.
		}

		/**
		 * Defines no-cache constants for various WordPress plugins.
		 *
		 * This is compatible with Quick Cache, W3 Total Cache, and also with WP Super Cache.
		 * Quick Cache uses: ``QUICK_CACHE_ALLOWED``, and other plugins use: ``DONOTCACHEPAGE``.
		 * W3 Total Cache is also known to be compatible with ``DONOTCACHEOBJECT`` and ``DONOTCACHEDB``.
		 *
		 * Disallow caching if the ``$no_cache`` parameter is passed in as ``true``, by other routines.
		 * In addition, always disallow caching for logged in users, and GET requests with: `/?s2member` Systematics.
		 * For clarity on s2Member Systematics, see: {@link s2Member\Systematics\c_ws_plugin__s2member_systematics::is_s2_systematic_use_page()}.
		 *
		 * However, this routine will ALWAYS obey the `?qcAC` query string parameter.
		 *   This Quick Cache parameter explicitly allows caching to occur.
		 *
		 * @package s2Member\No_Cache
		 * @since 3.5
		 *
		 * @also-called-by Other routines within s2Member.
		 *
		 * @param bool $no_cache Optional. Defaults to false. If true, force no-cache if at all possible.
		 *
		 * @return bool This function will always return `true`.
		 */
		public static function no_cache_constants($no_cache = FALSE)
		{
			static $once; // We only need to set these constants once.

			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_before_no_cache_constants', get_defined_vars());
			unset($__refs, $__v); // Housekeeping.

			if(!$once && (empty($_GET['qcAC']) || !filter_var($_GET['qcAC'], FILTER_VALIDATE_BOOLEAN))
			   && (
					$no_cache === TRUE // Forces no-cache constants; if `TRUE` explicitly.
					|| ($no_cache === 'restricted' && (!defined('COMET_CACHE_WHEN_LOGGED_IN') || !COMET_CACHE_WHEN_LOGGED_IN) && (!defined('ZENCACHE_WHEN_LOGGED_IN') || !ZENCACHE_WHEN_LOGGED_IN) && (!defined('QUICK_CACHE_WHEN_LOGGED_IN') || !QUICK_CACHE_WHEN_LOGGED_IN))
					|| (is_user_logged_in() && (!defined('COMET_CACHE_WHEN_LOGGED_IN') || !COMET_CACHE_WHEN_LOGGED_IN) && (!defined('ZENCACHE_WHEN_LOGGED_IN') || !ZENCACHE_WHEN_LOGGED_IN) && (!defined('QUICK_CACHE_WHEN_LOGGED_IN') || !QUICK_CACHE_WHEN_LOGGED_IN))
					|| c_ws_plugin__s2member_systematics::is_s2_systematic_use_page()
				)
			)
			{
				/**
				 * No-cache DB queries for plugins.
				 *
				 * @package s2Member\No_Cache
				 * @since 111115
				 *
				 * @var bool
				 */
				if(!defined('DONOTCACHEDB'))
					define('DONOTCACHEDB', TRUE);

				/**
				 * No-cache Page for plugins.
				 *
				 * @package s2Member\No_Cache
				 * @since 3.5
				 *
				 * @var bool
				 */
				if(!defined('DONOTCACHEPAGE'))
					define('DONOTCACHEPAGE', TRUE);

				/**
				 * No-cache Objects for plugins.
				 *
				 * @package s2Member\No_Cache
				 * @since 111115
				 *
				 * @var bool
				 */
				if(!defined('DONOTCACHEOBJECT'))
					define('DONOTCACHEOBJECT', TRUE);

				/**
				 * No-cache anything for Comet Cache plugin.
				 *
				 * @package s2Member\No_Cache
				 * @since 160222
				 *
				 * @var bool
				 */
				if(!defined('COMET_CACHE_ALLOWED'))
					define('COMET_CACHE_ALLOWED', FALSE);

				/**
				 * No-cache anything for ZenCache plugin.
				 *
				 * @package s2Member\No_Cache
				 * @since 3.5
				 *
				 * @var bool
				 */
				if(!defined('ZENCACHE_ALLOWED'))
					define('ZENCACHE_ALLOWED', FALSE);

				/**
				 * No-cache anything for Quick Cache plugin.
				 *
				 * @package s2Member\No_Cache
				 * @since 3.5
				 *
				 * @var bool
				 */
				if(!defined('QUICK_CACHE_ALLOWED'))
					define('QUICK_CACHE_ALLOWED', FALSE);

				$once = TRUE; // Set these one time only.

				c_ws_plugin__s2member_no_cache::$headers = TRUE;

				do_action('ws_plugin__s2member_during_no_cache_constants', get_defined_vars());
			}
			do_action('ws_plugin__s2member_after_no_cache_constants', get_defined_vars());

			return TRUE; // Always return true.
		}

		/**
		 * Filters HTTP headers (wp_headers) to apply no-cache headers when needed.
		 *
		 * This is used by the `evaluative` no-cache headers mode. It allows s2Member runtime checks/shortcodes
		 * (e.g., `[s2If]`) to set no-cache constants/flags during rendering, while still applying no-cache
		 * headers during WordPress's standard header-sending stage (wp_headers/send_headers).
		 *
		 * Obeys `?qcABC`, `?zcABC`, and `?ccABC` query string parameters, which explicitly allow caching.
		 *
		 * @package s2Member\No_Cache
		 * @since 260307
		 *
		 * @param array $headers An associative array of HTTP headers.
		 *
		 * @return array Possibly modified headers.
		 */
		public static function no_cache_wp_headers($headers = array())
		{
			static $once; // We only need to filter these headers one time.

			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_before_no_cache_headers', get_defined_vars());
			unset($__refs, $__v); // Housekeeping.

			$mode                     = (!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['no_cache_headers_mode']))
				? (string)$GLOBALS['WS_PLUGIN__']['s2member']['o']['no_cache_headers_mode']
				: 'always';
			$no_cache                 = (bool)apply_filters('ws_plugin__s2member_no_cache', FALSE, get_defined_vars());
			$using_selective_behavior = (bool)apply_filters('ws_plugin__s2member_no_cache_headers_selective', FALSE, get_defined_vars());

			if($mode === 'evaluative' && !$no_cache && $using_selective_behavior && !c_ws_plugin__s2member_no_cache::$headers) //260309 Evaluative mode: pre-detect s2 shortcodes in queried content.
			{
				global $wp_query;

				if(!empty($wp_query) && !empty($wp_query->post) && is_object($wp_query->post) && !empty($wp_query->post->post_content))
					if(stripos((string)$wp_query->post->post_content, '[s2') !== FALSE)
						c_ws_plugin__s2member_no_cache::$headers = TRUE;
			}

			//260308 Debug header (support use only).
			if(!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['no_cache_headers_debug']))
			{
				$_st = 's2member;desc="mode='.$mode.';no_cache='.(int)$no_cache.';selective='.(int)$using_selective_behavior.';headers='.(int)c_ws_plugin__s2member_no_cache::$headers.'"';

				if(!empty($headers['Server-Timing']))
					$headers['Server-Timing'] .= ', '.$_st;
				else
					$headers['Server-Timing'] = $_st;

				unset($_st); // Housekeeping.
			}

			if($mode === 'evaluative' && !$once
			   && (empty($_GET['ccABC']) || !filter_var($_GET['ccABC'], FILTER_VALIDATE_BOOLEAN))
			   && (empty($_GET['zcABC']) || !filter_var($_GET['zcABC'], FILTER_VALIDATE_BOOLEAN))
			   && (empty($_GET['qcABC']) || !filter_var($_GET['qcABC'], FILTER_VALIDATE_BOOLEAN))
			   && ($no_cache || !$using_selective_behavior || c_ws_plugin__s2member_no_cache::$headers)
			)
				if(!apply_filters('ws_plugin__s2member_disable_no_cache_headers', FALSE, get_defined_vars()))
				{
					$nocache_headers = wp_get_nocache_headers();

					foreach(array('Expires', 'Cache-Control', 'Pragma') as $_k)
						if(isset($headers[$_k])) unset($headers[$_k]);

					foreach((array)$nocache_headers as $_k => $_v)
						$headers[$_k] = $_v;

					unset($_k, $_v); // Housekeeping.

					$once = TRUE; // This is static var. Only apply once.

					do_action('ws_plugin__s2member_during_no_cache_headers', get_defined_vars());
				}
			do_action('ws_plugin__s2member_after_no_cache_headers', get_defined_vars());

			return $headers;
		}

		/**
		 * Sends Cache-Control (no-cache) headers.
		 *
		 * Disallow browser caching if the ``$no_cache`` parameter is passed in as ``true``, by other routines.
		 * Disallow browser caching when/if no-cache Constants are set by {@link s2Member\No_Cache\c_ws_plugin__s2member_no_cache::no_cache_constants()},
		 *   via static variable boolean value for: ``c_ws_plugin__s2member_no_cache::$headers``.
		 *
		 * However, this routine will ALWAYS obey the `?qcABC` query string parameter.
		 *   This Quick Cache parameter explicitly allows browser caching to occur.
		 *
		 * @package s2Member\No_Cache
		 * @since 3.5
		 *
		 * @also-called-by Other routines within s2Member.
		 *
		 * @param bool $no_cache Optional. Defaults to false. If true, force no-cache if at all possible.
		 *
		 * @return bool This function will always return `true`.
		 */
		public static function no_cache_headers($no_cache = FALSE)
		{
			static $once; // We only need to set these headers one time.

			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_before_no_cache_headers', get_defined_vars());
			unset($__refs, $__v); // Housekeeping.

			$using_selective_behavior = apply_filters('ws_plugin__s2member_no_cache_headers_selective', FALSE, get_defined_vars());

			if(!$once && !headers_sent() // Only once, and only if possible.
			   && (empty($_GET['ccABC']) || !filter_var($_GET['ccABC'], FILTER_VALIDATE_BOOLEAN))
			   && (empty($_GET['zcABC']) || !filter_var($_GET['zcABC'], FILTER_VALIDATE_BOOLEAN))
			   && (empty($_GET['qcABC']) || !filter_var($_GET['qcABC'], FILTER_VALIDATE_BOOLEAN))
			   && ($no_cache || !$using_selective_behavior || c_ws_plugin__s2member_no_cache::$headers)
			)
				if(!apply_filters('ws_plugin__s2member_disable_no_cache_headers', FALSE, get_defined_vars()))
				{
					foreach(headers_list() as $header) // No-cache headers already sent? We need to check here.
						if(stripos($header, 'no-cache') !== FALSE) // No-cache headers already sent?
						{
							$no_cache_headers_already_sent = TRUE; // Yep, sent.
							break; // Break now, no need to continue further.
						}
					if(!isset ($no_cache_headers_already_sent)) // Not yet?
						nocache_headers(); // Only if NOT already sent.

					$once = TRUE; // This is static var. Only send headers once.

					do_action('ws_plugin__s2member_during_no_cache_headers', get_defined_vars());
				}
			do_action('ws_plugin__s2member_after_no_cache_headers', get_defined_vars());

			return TRUE; // Always return true.
		}
	}
}
