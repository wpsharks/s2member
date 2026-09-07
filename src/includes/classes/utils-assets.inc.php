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
		protected static $asset_http_health_cache;
		protected static $page_asset_expectations = array();

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
		 * Returns recent low-trust runtime suspicions reported by real frontend pages.
		 *
		 * Reports are only hints. They never change delivery by themselves. A trusted administrator-browser probe must confirm the exact URL/marker before persistent fallback or a confirmed notice is used.
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
		 * Normal checks are deliberately cheap. Static files use HEAD and s2member-o.php has a special early health response that exits before loading WordPress. A real-page suspicion adds a one-time full marker check for the exact asset that page expected.
		 *
		 * @package s2Member\Utilities
		 * @since 260904.2110
		 *
		 * @return array Health targets keyed by logical target ID.
		 */
		protected static function asset_http_health_targets()
		{
			$targets = array();
			if((empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['dynamic_asset_loader']) || $GLOBALS['WS_PLUGIN__']['s2member']['o']['dynamic_asset_loader'] !== 'wordpress') && is_file(self::s2o_file_path()))
				$targets['s2o'] = array(
					'id' => 's2o',
					'url' => $GLOBALS['WS_PLUGIN__']['s2member']['c']['s2o_url'],
					'probe_url' => add_query_arg('s2member_health_check', '1', $GLOBALS['WS_PLUGIN__']['s2member']['c']['s2o_url']),
					'type' => 'health',
					'mode' => 's2o-health',
					'label' => 's2Member Dynamic Loader',
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
								$targets['static:'.$id] = array(
									'id' => 'static:'.$id,
									'url' => $url,
									'probe_url' => $url,
									'type' => $type,
									'mode' => 'head',
									'label' => $id,
									'failure_id' => 'static:'.$id,
									'failure_url' => $url,
								);
						}

			foreach(self::asset_runtime_suspicions() as $key => $suspicion)
			{
				if(!self::asset_runtime_expectation_is_current($suspicion))
					continue;
				$id = 'runtime:'.$key;
				$failure_id = '';
				$failure_url = (string)$suspicion['url'];
				if($suspicion['delivery'] === 'dynamic-lightweight')
				{
					$failure_id = 's2o';
					$failure_url = $GLOBALS['WS_PLUGIN__']['s2member']['c']['s2o_url'];
				}
				else if($suspicion['delivery'] === 'static' && !empty($suspicion['asset_id']))
					$failure_id = 'static:'.$suspicion['asset_id'];
				else
					$failure_id = $id;

				$targets[$id] = array(
					'id' => $id,
					'url' => (string)$suspicion['url'],
					'probe_url' => (string)$suspicion['url'],
					'type' => (string)$suspicion['type'],
					'mode' => 'marker',
					'label' => (string)$suspicion['id'],
					'markers' => array((string)$suspicion['marker']),
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
				$hash[$id] = array((string)$target['url'], (string)$target['type'], (string)$target['mode'], (!empty($target['markers'])) ? array_values((array)$target['markers']) : array());
			return md5(serialize($hash));
		}

		/**
		 * Returns marker output appended to a generated static asset.
		 *
		 * @package s2Member\Utilities
		 * @since 260904.2255
		 *
		 * @param string $id Stable generated asset identifier without extension.
		 * @param string $type `css` or `js`.
		 * @param int $build Generated build timestamp.
		 * @return string Marker output.
		 */
		protected static function static_asset_marker_output($id = '', $type = '', $build = 0)
		{
			$components = array();
			if($id === 's2member-pro')
				$components[] = 'pro';
			else
			{
				$components[] = 'framework';
				if(!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['static_assets_combine']) && (defined('WS_PLUGIN__S2MEMBER_PRO_VERSION') || isset($GLOBALS['WS_PLUGIN__']['s2member_pro'])))
					$components[] = 'pro';
			}
			$token = 'static-'.(int)$build;
			if($type === 'css')
			{
				$markers = array();
				foreach($components as $component)
					$markers[] = '#ws-plugin--s2member-'.$component.'-css-health{z-index:'.(($component === 'framework') ? '2147483641' : '2147483642').'!important}';
				return implode('', $markers);
			}
			$markers = ';window.ws_plugin__s2member_asset_health=window.ws_plugin__s2member_asset_health||{};';
			foreach($components as $component)
				$markers .= 'window.ws_plugin__s2member_asset_health["'.$component.'_js"]="'.$token.'";';
			return $markers;
		}

		/**
		 * Returns marker output appended to dynamically generated CSS or JavaScript.
		 *
		 * @package s2Member\Utilities
		 * @since 260904.2255
		 *
		 * @param string $type `css` or `js`.
		 * @return string Marker output.
		 */
		public static function dynamic_asset_marker_output($type = '')
		{
			$type = strtolower((string)$type);
			if(!in_array($type, array('css', 'js'), TRUE))
				return '';
			$token = (defined('_WS_PLUGIN__S2MEMBER_ONLY')) ? 'dynamic-lightweight' : 'dynamic-wordpress';
			$components = array('framework');
			if(defined('WS_PLUGIN__S2MEMBER_PRO_VERSION') || isset($GLOBALS['WS_PLUGIN__']['s2member_pro']))
				$components[] = 'pro';
			if($type === 'css')
			{
				$markers = array();
				foreach($components as $component)
					$markers[] = '#ws-plugin--s2member-'.$component.'-css-health{z-index:'.(($component === 'framework') ? '2147483641' : '2147483642').'!important}';
				return "\n".implode('', $markers)."\n";
			}
			$markers = "\n;window.ws_plugin__s2member_asset_health=window.ws_plugin__s2member_asset_health||{};";
			foreach($components as $component)
				$markers .= 'window.ws_plugin__s2member_asset_health["'.$component.'_js"]="'.$token.'";';
			return $markers."\n";
		}

		/**
		 * Registers the exact CSS/JavaScript markers expected on the current frontend page.
		 *
		 * @package s2Member\Utilities
		 * @since 260904.2255
		 *
		 * @param string $asset_id Logical static asset ID, or an empty string for dynamic delivery.
		 * @param string $type `css` or `js`.
		 * @param string $url Public URL emitted on this page.
		 * @param string $delivery `static`, `dynamic-lightweight`, or `dynamic-wordpress`.
		 * @param int $build Static build timestamp, or zero for dynamic delivery.
		 * @return null
		 */
		public static function register_page_asset_expectations($asset_id = '', $type = '', $url = '', $delivery = '', $build = 0)
		{
			$type = strtolower((string)$type);
			$url = (string)$url;
			if(!in_array($type, array('css', 'js'), TRUE) || !$url)
				return;
			$components = array();
			if($delivery === 'static')
			{
				if(strpos((string)$asset_id, 's2member-pro.') === 0)
					$components[] = 'pro';
				else
				{
					$components[] = 'framework';
					if(!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['static_assets_combine']) && (defined('WS_PLUGIN__S2MEMBER_PRO_VERSION') || isset($GLOBALS['WS_PLUGIN__']['s2member_pro'])))
						$components[] = 'pro';
				}
				$token = 'static-'.(int)$build;
			}
			else
			{
				$components[] = 'framework';
				if(defined('WS_PLUGIN__S2MEMBER_PRO_VERSION') || isset($GLOBALS['WS_PLUGIN__']['s2member_pro']))
					$components[] = 'pro';
				$token = ($delivery === 'dynamic-lightweight') ? 'dynamic-lightweight' : 'dynamic-wordpress';
			}
			$recovery_url = '';
			if($delivery !== 'dynamic-wordpress')
			{
				if($type === 'css')
					$recovery_url = add_query_arg(array('ws_plugin__s2member_css' => '1', 'qcABC' => '1'), self::wordpress_dynamic_asset_url());
				else
				{
					$js_value = (is_user_logged_in() && defined('WS_PLUGIN__S2MEMBER_API_CONSTANTS_MD5')) ? WS_PLUGIN__S2MEMBER_API_CONSTANTS_MD5 : '1';
					$recovery_url = add_query_arg(array('ws_plugin__s2member_js_w_globals' => $js_value, 'qcABC' => '1'), self::wordpress_dynamic_asset_url());
				}
			}
			foreach($components as $component)
			{
				$id = $component.'_'.$type;
				if($type === 'css')
				{
					$token = ($component === 'framework') ? '2147483641' : '2147483642';
					$marker = '#ws-plugin--s2member-'.$component.'-css-health{z-index:'.$token.'!important}';
				}
				else
					$marker = 'ws_plugin__s2member_asset_health["'.$component.'_js"]="'.$token.'"';
				$expectation = array(
					'id' => $id,
					'asset_id' => (string)$asset_id,
					'type' => $type,
					'component' => $component,
					'url' => $url,
					'delivery' => $delivery,
					'token' => $token,
					'marker' => $marker,
					'recovery_url' => $recovery_url,
				);
				$expectation['signature'] = self::asset_runtime_expectation_signature($expectation);
				self::$page_asset_expectations[$id] = $expectation;
			}
			return;
		}

		/**
		 * Expands one compact browser runtime expectation into the full signed structure.
		 *
		 * Frontend pages only need a few fields to check markers and recover. Reconstruct the
		 * descriptive fields here when a miss is actually reported, keeping healthy page source small.
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
			$token = isset($compact[4]) ? (string)$compact[4] : '';
			$signature = isset($compact[5]) ? (string)$compact[5] : '';
			if(!preg_match('/\A(framework|pro)_(css|js)\z/', $id, $match))
				return array();
			$component = $match[1];
			$type = $match[2];
			if($type === 'css')
				$marker = '#ws-plugin--s2member-'.$component.'-css-health{z-index:'.$token.'!important}';
			else
				$marker = 'ws_plugin__s2member_asset_health["'.$component.'_js"]="'.$token.'"';

			$recovery_url = '';
			if($delivery !== 'dynamic-wordpress')
			{
				if($type === 'css')
					$recovery_url = add_query_arg(array('ws_plugin__s2member_css' => '1', 'qcABC' => '1'), self::wordpress_dynamic_asset_url());
				else
				{
					$js_value = (is_user_logged_in() && defined('WS_PLUGIN__S2MEMBER_API_CONSTANTS_MD5')) ? WS_PLUGIN__S2MEMBER_API_CONSTANTS_MD5 : '1';
					$recovery_url = add_query_arg(array('ws_plugin__s2member_js_w_globals' => $js_value, 'qcABC' => '1'), self::wordpress_dynamic_asset_url());
				}
			}
			return array(
				'id' => $id,
				'asset_id' => $asset_id,
				'type' => $type,
				'component' => $component,
				'url' => $url,
				'delivery' => $delivery,
				'token' => $token,
				'marker' => $marker,
				'recovery_url' => $recovery_url,
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
			foreach(array('id', 'asset_id', 'type', 'component', 'url', 'delivery', 'token', 'marker', 'recovery_url') as $key)
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
			if($expectation['delivery'] === 'dynamic-lightweight')
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
				if((string)(isset($old['static_assets_combine']) ? $old['static_assets_combine'] : '0') !== (string)(isset($new['static_assets_combine']) ? $new['static_assets_combine'] : '0'))
				{
					//260903.1918 A combine-mode change changes what the s2member.* filenames represent; discard all build state so the new 2-file/4-file representation starts with fresh timestamps.
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
					self::invalidate_static_assets($invalidate);
				return;
			}
			if(in_array((string)$option, array('siteurl', 'home'), TRUE))
				self::invalidate_static_assets(array('css', 'js'));
			else if((string)$option === 'WPLANG' && self::static_js_text_delivery() !== 'page')
				self::invalidate_static_assets('js');
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
				//260904.2110 A few local file checks are cheaper than sending a broken static URL. Missing or browser-confirmed unreachable files fall back to dynamic delivery immediately.
				if(!is_file($path))
					return self::$static_asset_cache[$id] = array('ok' => FALSE, 'url' => '', 'build' => $active_build, 'error' => 'Expected static asset '.$id.' is missing.');
				if(self::asset_http_target_failed('static:'.$id, $url))
					return self::$static_asset_cache[$id] = array('ok' => FALSE, 'url' => '', 'build' => $active_build, 'error' => 'Static asset '.$id.' could not be loaded from its public URL.');
				return self::$static_asset_cache[$id] = array('ok' => TRUE, 'url' => $url, 'build' => $active_build, 'error' => '');
			}

			$failure_key = 'ws_plugin__s2member_static_asset_failure_'.str_replace('.', '_', $id);
			if(!$force && $dirty && ($failure = get_transient($failure_key)))
				return self::$static_asset_cache[$id] = array('ok' => FALSE, 'url' => '', 'build' => $active_build, 'error' => (string)$failure);

			$definition = self::static_asset_definition($id, TRUE);
			if(empty($definition['ok']))
				return self::$static_asset_cache[$id] = array('ok' => FALSE, 'url' => '', 'build' => $active_build, 'error' => (string)$definition['error']);

			$build = max(time(), $active_build + 1);
			$result = self::build_static_asset($base, $build, $type, $definition['sources'], !empty($definition['minify']));
			if(!empty($result['ok']))
			{
				if($uses_data_map)
					self::set_static_asset_data_map_signature($id, (string)$data_map_signature['signature']);
				self::set_static_asset_build($id, $build);
				//260905.0106 Prune only after the new timestamp is current so the previous generation is treated as stale instead of protected.
				self::prune_static_asset_generations(dirname($result['path']), $result['path']);
				delete_transient($failure_key);
				return self::$static_asset_cache[$id] = array('ok' => TRUE, 'url' => $result['url'], 'build' => $build, 'error' => '');
			}
			set_transient($failure_key, (string)$result['error'], 5 * MINUTE_IN_SECONDS);
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
				wp_send_json_success(array('message' => 'Static '.implode(' + ', $success).' refreshed. New timestamped files are active.'));
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
			return self::$static_assets_health_cache = $missing;
		}

		/**
		 * Displays a branded admin warning for missing or browser-confirmed unreachable frontend assets.
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

			$messages = array();
			$static_settings_url = add_query_arg('s2member-open-panel', 'frontend-static-assets', admin_url('/admin.php?page=ws-plugin--s2member-gen-ops')).'#ws-plugin--s2member-static-assets';
			$dynamic_settings_url = add_query_arg('s2member-open-panel', 'dynamic-asset-loader', admin_url('/admin.php?page=ws-plugin--s2member-gen-ops')).'#ws-plugin--s2member-dynamic-asset-loader-section';
			$health = self::static_assets_health();
			if($health)
				$messages[] = esc_html(implode(' ', $health)).' Pages that need the missing file are using dynamic delivery instead. <a href="'.esc_url($static_settings_url).'">Open Static CSS/JS Optimization and refresh the static assets.</a>';

			$using_s2o = empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['dynamic_asset_loader']) || $GLOBALS['WS_PLUGIN__']['s2member']['o']['dynamic_asset_loader'] !== 'wordpress';
			$s2o_missing = $using_s2o && !is_file(self::s2o_file_path());
			if($s2o_missing)
				$messages[] = 'The selected s2Member Dynamic Loader file <code>s2member-o.php</code> is missing. s2Member is using the WordPress Dynamic Loader instead. Restore the file or <a href="'.esc_url($dynamic_settings_url).'">choose the WordPress Dynamic Loader</a>.';

			$http_health = self::asset_http_health_state();
			$has_confirmed_failure = (bool)($health || $s2o_missing);
			if(is_array($http_health) && !empty($http_health['runtime_warnings']) && is_array($http_health['runtime_warnings']))
				foreach($http_health['runtime_warnings'] as $warning)
					if(!empty($warning['reported']) && (int)$warning['reported'] >= time() - HOUR_IN_SECONDS)
						$messages[] = 'A real frontend page reported that <code>'.esc_html((string)$warning['id']).'</code> did not become active, even though a follow-up browser check could load the expected file and marker. This can indicate script/style optimization, execution order, a browser extension, or another runtime conflict. Delivery has not been changed automatically.';
			if(is_array($http_health) && !empty($http_health['failures']) && is_array($http_health['failures']))
				foreach($http_health['failures'] as $id => $failure)
				{
					$status = (!empty($failure['status'])) ? ' HTTP '.(int)$failure['status'].'.' : '';
					if($id === 's2o' && $using_s2o && !$s2o_missing && !empty($failure['url']) && (string)$failure['url'] === (string)$GLOBALS['WS_PLUGIN__']['s2member']['c']['s2o_url'])
					{
						$has_confirmed_failure = TRUE;
						$messages[] = 'The selected s2Member Dynamic Loader could not be reached.'.$status.' s2Member is using the WordPress Dynamic Loader instead. <a href="'.esc_url($dynamic_settings_url).'">Review Dynamic CSS/JS Loader</a> or see <a href="https://s2member.com/kb-article/mod-security-odd-403-503-500-errors/">Mod Security (Odd 403, 503, 500 Errors)</a>.';
					}
					else if(strpos((string)$id, 'static:') === 0 && !empty($failure['url']) && self::asset_http_target_failed($id, (string)$failure['url']))
					{
						$has_confirmed_failure = TRUE;
						$messages[] = 'A generated static file could not be loaded from its public URL.'.$status.' Pages that need it are using dynamic delivery instead. <a href="'.esc_url($static_settings_url).'">Open Static CSS/JS Optimization</a>.';
					}
					else if(strpos((string)$id, 'runtime:') === 0 && !empty($http_health['checked']) && (int)$http_health['checked'] >= time() - HOUR_IN_SECONDS)
					{
						$has_confirmed_failure = TRUE;
						$messages[] = 'A dynamically generated frontend asset could not be loaded or did not contain its expected completion marker.'.$status.' Review the browser console and your CSS/JavaScript optimization or security settings.';
					}
				}

			if($messages)
				c_ws_plugin__s2member_admin_notices::display_branded_notice('s2Member Frontend Asset Notice', implode('<br /><br />', $messages), $has_confirmed_failure);
			return;
		}

		/**
		 * Prints an infrequent trusted browser-side reachability probe for active frontend assets.
		 *
		 * Healthy static files use HEAD. The lightweight loader uses its tiny pre-WordPress health mode.
		 * A frontend runtime suspicion forces one full cache-busted marker check for that exact URL.
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
			$targets = self::asset_http_health_targets();
			if(!$targets)
				return;

			$target_hash = self::asset_http_health_target_hash($targets);
			$health = self::asset_http_health_state();
			$has_failures = is_array($health) && !empty($health['failures']);
			$has_suspicions = (bool)self::asset_runtime_suspicions();
			$interval = ($has_failures || $has_suspicions) ? MINUTE_IN_SECONDS : 10 * MINUTE_IN_SECONDS;
			if(!$has_suspicions && is_array($health) && !empty($health['checked']) && !empty($health['target_hash']) && (string)$health['target_hash'] === $target_hash && (int)$health['checked'] >= time() - $interval)
				return;

			$config = array(
				'targets' => array_values($targets),
				'target_hash' => $target_hash,
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('ws-plugin--s2member-asset-http-health'),
				'reload_on_change' => is_admin(),
			);
			echo '<script type="text/javascript">(function(c){if(!window.fetch||!window.URL||!window.Promise)return;function u(t,i){var x=new URL(t.probe_url,window.location.href),n=Date.now().toString(36)+"-"+i+"-"+Math.random().toString(36).slice(2);x.searchParams.set("s2member_asset_health",n);if(t.mode==="s2o-health")x.searchParams.set("s2member_health_token",n);return{x:x.toString(),n:n}}function ct(r,t){var v=(r.headers.get("content-type")||"").toLowerCase();if(t.type==="css")return v.indexOf("text/css")!==-1;if(t.type==="js")return /(javascript|ecmascript)/.test(v);return v.indexOf("text/plain")!==-1}function f(t,m,i,body){var z=u(t,i);return fetch(z.x,{method:m,cache:"no-store",credentials:"same-origin",headers:{"Cache-Control":"no-cache, no-store, max-age=0","Pragma":"no-cache"}}).then(function(r){var h=r.headers.get("x-s2member-health-token")||"",tm=r.headers.get("x-s2member-health-time")||"";if(!body)return{ok:r.ok&&ct(r,t),status:r.status,content_type:r.headers.get("content-type")||"",text:"",token:z.n,health_token:h,health_time:tm};return r.text().then(function(x){return{ok:r.ok&&ct(r,t),status:r.status,content_type:r.headers.get("content-type")||"",text:x,token:z.n,health_token:h,health_time:tm}})}).catch(function(){return{ok:false,status:0,content_type:"",text:"",token:z.n,health_token:"",health_time:""}})}function p(t,i){if(t.mode==="s2o-health")return f(t,"GET",i,true).then(function(r){r.ok=r.ok&&r.health_token===r.token&&r.health_time!==""&&r.text.indexOf("s2member-o-health:"+r.token+":"+r.health_time)===0;return{id:t.id,ok:r.ok,status:r.status,content_type:r.content_type,detail:r.ok?"":"Health marker mismatch"}});if(t.mode==="marker")return f(t,"GET",i,true).then(function(r){if(r.ok&&t.markers)for(var j=0;j<t.markers.length;j++)if(r.text.indexOf(t.markers[j])===-1){r.ok=false;break}return{id:t.id,ok:r.ok,status:r.status,content_type:r.content_type,detail:r.ok?"":"Expected marker missing"}});return f(t,"HEAD",i,false).then(function(r){if(r.ok)return{id:t.id,ok:true,status:r.status,content_type:r.content_type,detail:""};return f(t,"GET",i+"g",false).then(function(g){return{id:t.id,ok:g.ok,status:g.status,content_type:g.content_type,detail:g.ok?"":"Public URL check failed"}})})}Promise.all(c.targets.map(p)).then(function(results){var body="action="+encodeURIComponent("ws_plugin__s2member_asset_http_health")+"&_ajax_nonce="+encodeURIComponent(c.nonce)+"&target_hash="+encodeURIComponent(c.target_hash)+"&results="+encodeURIComponent(JSON.stringify(results));return fetch(c.ajax_url,{method:"POST",cache:"no-store",credentials:"same-origin",headers:{"Content-Type":"application/x-www-form-urlencoded;charset=UTF-8","Cache-Control":"no-cache, no-store, max-age=0","Pragma":"no-cache"},body:body})}).then(function(r){return r.json()}).then(function(j){if(c.reload_on_change&&j&&j.success&&j.data&&j.data.reload)window.location.reload()}).catch(function(){})})('.wp_json_encode($config).');</script>' . "\n";
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

			$targets = self::asset_http_health_targets();
			$target_hash = self::asset_http_health_target_hash($targets);
			if(empty($_POST['target_hash']) || (string)wp_unslash($_POST['target_hash']) !== $target_hash)
				wp_send_json_success(array('stale' => TRUE, 'reload' => FALSE));

			$results = (!empty($_POST['results'])) ? json_decode(wp_unslash($_POST['results']), TRUE) : array();
			$by_id = array();
			if(is_array($results))
				foreach($results as $result)
					if(is_array($result) && !empty($result['id']))
						$by_id[(string)$result['id']] = $result;

			$old = self::asset_http_health_state();
			$old_failures = (is_array($old) && !empty($old['failures']) && is_array($old['failures'])) ? $old['failures'] : array();
			$runtime_warnings = (is_array($old) && !empty($old['runtime_warnings']) && is_array($old['runtime_warnings'])) ? $old['runtime_warnings'] : array();
			foreach($runtime_warnings as $key => $warning)
				if(empty($warning['reported']) || (int)$warning['reported'] < time() - HOUR_IN_SECONDS)
					unset($runtime_warnings[$key]);
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
				}
				else if(!empty($target['suspicion_key']) && !empty($target['suspicion']))
				{
					$key = (string)$target['suspicion_key'];
					$runtime_warnings[$key] = array(
						'id' => (string)$target['suspicion']['id'],
						'url' => (string)$target['suspicion']['url'],
						'delivery' => (string)$target['suspicion']['delivery'],
						'reported' => time(),
					);
				}
				if(!empty($target['suspicion_key']))
					unset($suspicions[(string)$target['suspicion_key']]);
			}

			update_option('ws_plugin__s2member_asset_runtime_suspicions', $suspicions, FALSE);
			$new_health = array('checked' => time(), 'target_hash' => $target_hash, 'failures' => $failures, 'runtime_warnings' => $runtime_warnings);
			update_option('ws_plugin__s2member_asset_http_health', $new_health, FALSE);
			self::$asset_http_health_cache = $new_health;
			wp_send_json_success(array('failures' => count($failures), 'runtime_warnings' => count($runtime_warnings), 'reload' => serialize($old_failures) !== serialize($failures)));
		}

		/**
		 * Prints the late real-page asset marker monitor.
		 *
		 * A normal browser has already finished loading ordinary CSS/JavaScript by window.load, so a short extra grace period is enough to avoid racing normal delivery. Healthy pages make no request. A recoverable miss uses one WordPress fallback request that also carries compact signed diagnostic details.
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

			$expectations = array();
			$recovery = array('css' => '', 'js' => '');
			foreach(self::$page_asset_expectations as $expectation)
			{
				$expectations[] = array(
					(string)$expectation['id'],
					(string)$expectation['asset_id'],
					(string)$expectation['url'],
					(string)$expectation['delivery'],
					(string)$expectation['token'],
					(string)$expectation['signature'],
				);
				if(!empty($expectation['recovery_url']) && empty($recovery[(string)$expectation['type']]))
					$recovery[(string)$expectation['type']] = (string)$expectation['recovery_url'];
			}
			$config = array('a' => admin_url('admin-ajax.php'), 'e' => $expectations, 'r' => $recovery, 'd' => 1000);
			echo '<script type="text/javascript" id="ws-plugin--s2member-asset-runtime-monitor">(function(c){function t(e){return /_js$/.test(e[0])?"js":"css"}function p(e){return /^pro_/.test(e[0])?"pro":"framework"}function n(e){var i="ws-plugin--s2member-"+p(e)+"-css-health",o=document.getElementById(i);if(!o){o=document.createElement("span");o.id=i;o.style.cssText="position:absolute;left:-99999px;top:-99999px;width:1px;height:1px;visibility:hidden";(document.body||document.documentElement).appendChild(o)}return o}function ok(e){if(t(e)==="js")return !!(window.ws_plugin__s2member_asset_health&&window.ws_plugin__s2member_asset_health[p(e)+"_js"]===e[4]);return !window.getComputedStyle||String(getComputedStyle(n(e)).zIndex)===e[4]}function u(x,m){var q=Date.now().toString(36)+"-"+Math.random().toString(36).slice(2),j=JSON.stringify(m);if(!window.URL)return x+(x.indexOf("?")<0?"?":"&")+"s2member_asset_recovery="+encodeURIComponent(q)+"&s2member_asset_runtime_suspect="+encodeURIComponent(j);var o=new URL(x,location.href);o.searchParams.set("s2member_asset_recovery",q);o.searchParams.set("s2member_asset_runtime_suspect",j);return o.toString()}function report(m){if(!window.fetch||!m.length)return;fetch(c.a,{method:"POST",cache:"no-store",credentials:"same-origin",keepalive:true,headers:{"Content-Type":"application/x-www-form-urlencoded;charset=UTF-8"},body:"action=ws_plugin__s2member_asset_runtime_suspect&missing="+encodeURIComponent(JSON.stringify(m))}).catch(function(){})}function recover(m){var ro=[],cm=m.filter(function(e){return t(e)==="css"}),ja=c.e.filter(function(e){return t(e)==="js"}),jm=m.filter(function(e){return t(e)==="js"});if(cm.length){if(c.r.css){var l=document.createElement("link");l.rel="stylesheet";l.href=u(c.r.css,cm);l.onerror=function(){report(cm)};document.head.appendChild(l)}else ro=ro.concat(cm)}if(jm.length){if(ja.length&&jm.length===ja.length&&c.r.js){var s=document.createElement("script");s.src=u(c.r.js,jm);s.async=false;s.onerror=function(){report(jm)};(document.body||document.documentElement).appendChild(s)}else ro=ro.concat(jm)}if(ro.length)report(ro)}function check(){var m=c.e.filter(function(e){return !ok(e)});if(m.length)recover(m)}c.e.filter(function(e){return t(e)==="css"}).forEach(n);function go(){setTimeout(check,c.d)}document.readyState==="complete"?go():addEventListener("load",go,false)})('.wp_json_encode($config).');</script>' . "\n";
			return;
		}

		/**
		 * Records signed low-trust frontend runtime suspicions without changing delivery state.
		 *
		 * Reports are rate-limited and only force a later trusted administrator-browser confirmation. A recovery request and the standalone AJAX reporter share this validator so successful page-local fallback normally needs no separate reporting request.
		 *
		 * @package s2Member\Utilities
		 * @since 260905.0009
		 *
		 * @param array $missing Missing runtime expectations.
		 * @return int Number of newly recorded suspicions.
		 */
		protected static function record_asset_runtime_suspicions($missing = array())
		{
			if(!is_array($missing) || !$missing)
				return 0;
			$missing = array_slice($missing, 0, 4);
			$suspicions = self::asset_runtime_suspicions();
			$recorded = 0;
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
				$key = md5((string)$expectation['id']."\0".(string)$expectation['url']."\0".(string)$expectation['marker']);
				if(get_transient('ws_plugin__s2member_asset_runtime_suspect_'.$key))
					continue;
				set_transient('ws_plugin__s2member_asset_runtime_suspect_'.$key, 1, MINUTE_IN_SECONDS);
				$expectation['reported'] = time();
				$expectation['signature'] = $signature;
				$suspicions[$key] = $expectation;
				$recorded++;
			}
			if($recorded)
				update_option('ws_plugin__s2member_asset_runtime_suspicions', $suspicions, FALSE);
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
			wp_send_json_success(array('recorded' => self::record_asset_runtime_suspicions($missing)));
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
						@unlink($old_path);
				}
			}
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
			$marker = self::static_asset_marker_output($id, $type, $build);
			$output = (($headers) ? implode("\n", $headers)."\n" : '').$body."\n".$marker."\n";
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
