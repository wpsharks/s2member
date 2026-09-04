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
		 * Returns the normal WordPress front-controller URL used for dynamic frontend asset fallback.
		 *
		 * WordPress exposes its front-controller filename through WP_Rewrite::$index. Use that instead of hard-coding index.php so properly customized setups remain supported.
		 *
		 * @package s2Member\Utilities
		 * @since 260904.0221
		 *
		 * @return string Dynamic frontend asset endpoint URL without query arguments.
		 */
		public static function dynamic_asset_url()
		{
			global $wp_rewrite;

			$index = (is_object($wp_rewrite) && !empty($wp_rewrite->index)) ? ltrim((string)$wp_rewrite->index, '/') : '';
			if($index === '')
				$index = 'index.php';

			//260904.0221 TO-DO: Reassess whether checking the legacy s2member-o.php endpoint is still useful after static assets and normal WordPress dynamic fallback have been deployed.
			// If useful, probe it infrequently with an asynchronous same-origin browser request from wp-admin, cache the HTTP status/content type and a short sanitized failure detail, and avoid frontend-visitor probes or PHP loopback requests.
			// On failure, link to Frontend CSS/JS Optimization and the ModSecurity/security troubleshooting KB because cached, customized, or direct legacy URLs may still use s2member-o.php.
			return home_url('/'.$index);
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
			delete_option('ws_plugin__s2member_static_asset_health');
			self::$static_asset_cache = array();
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
					'js' => array('static_js', 'static_js_minify'),
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
			else if((string)$option === 'WPLANG')
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
				self::invalidate_static_assets('js');
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
		 * Normal requests trust an already-active timestamp instead of performing filesystem stat calls. External deletion is checked on the admin optimization panel and repaired by the Refresh button.
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
			if(!$force && !$dirty && $active_build)
			{
				$location = self::static_assets_location(FALSE);
				if(empty($location['ok']))
					return self::$static_asset_cache[$id] = array('ok' => FALSE, 'url' => '', 'build' => $active_build, 'error' => $location['error']);
				return self::$static_asset_cache[$id] = array('ok' => TRUE, 'url' => $location['url'].'/'.$base.'-'.$active_build.'.'.$type, 'build' => $active_build, 'error' => '');
			}

			$failure_key = 'ws_plugin__s2member_static_asset_failure_'.str_replace('.', '_', $id);
			if(!$force && $dirty && ($failure = get_transient($failure_key)))
				return self::$static_asset_cache[$id] = array('ok' => FALSE, 'url' => '', 'build' => $active_build, 'error' => (string)$failure);

			$switched_locale = FALSE;
			if($type === 'js' && function_exists('switch_to_locale'))
			{
				$site_locale = (string)get_option('WPLANG');
				$switched_locale = switch_to_locale(($site_locale) ? $site_locale : 'en_US');
			}
			$definition = self::static_asset_definition($id, TRUE);
			if(empty($definition['ok']))
			{
				if($switched_locale && function_exists('restore_previous_locale'))
					restore_previous_locale();
				return self::$static_asset_cache[$id] = array('ok' => FALSE, 'url' => '', 'build' => $active_build, 'error' => (string)$definition['error']);
			}

			$build = max(time(), $active_build + 1);
			$result = self::build_static_asset($base, $build, $type, $definition['sources'], !empty($definition['minify']));
			if($switched_locale && function_exists('restore_previous_locale'))
				restore_previous_locale();
			if(!empty($result['ok']))
			{
				self::set_static_asset_build($id, $build);
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
				wp_send_json_success(array('message' => 'Static assets refreshed successfully ('.implode(' + ', $success).'). New timestamped files are active now.'));
			$message = (($success) ? 'Refreshed '.implode(' + ', $success).'. ' : '').'Could not refresh '.implode('; ', $errors).'. Failed types continue with their previous valid files when still current, or legacy dynamic assets when stale.';
			wp_send_json_error(array('message' => $message), 500);
		}

		/**
		 * Checks generated asset files for the admin optimization panel only.
		 *
		 * This deliberately avoids permanent frontend filesystem stat calls for an unusual out-of-band deletion case.
		 *
		 * @package s2Member\Utilities
		 * @since 260903.0525
		 *
		 * @return array Missing active asset files keyed by logical filename.
		 */
		public static function static_assets_health($force = FALSE)
		{
			if(!$force && isset(self::$static_assets_health_cache))
				return self::$static_assets_health_cache;
			if(empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['static_css']) && empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['static_js']))
				return self::$static_assets_health_cache = array();

			$builds = array();
			foreach(array('css' => 'static_css', 'js' => 'static_js') as $type => $option)
				if(!empty($GLOBALS['WS_PLUGIN__']['s2member']['o'][$option]))
					foreach(self::static_asset_ids($type, 'all') as $id)
						$builds[$id] = self::static_asset_build($id);
			$stored = get_option('ws_plugin__s2member_static_asset_health', array());
			if(!$force && is_array($stored) && !empty($stored['checked']) && (int)$stored['checked'] >= time() - DAY_IN_SECONDS
			   && isset($stored['builds'], $stored['missing']) && $stored['builds'] === $builds && is_array($stored['missing']))
				return self::$static_assets_health_cache = $stored['missing'];

			$missing = array();
			$location = self::static_assets_location(FALSE);
			if(empty($location['ok']))
				$missing['location'] = $location['error'];
			else
				foreach($builds as $id => $build)
				{
					$type = substr(strrchr($id, '.'), 1);
					$base = substr($id, 0, -strlen('.'.$type));
					if($build > 0 && !is_file($location['dir'].'/'.$base.'-'.$build.'.'.$type))
						$missing[$id] = 'Expected static asset '.$id.' is missing.';
				}

			//260903.0623 Cache the admin-only health result for one day; this detects out-of-band deletion without adding cron or repeated wp-admin filesystem stats.
			update_option('ws_plugin__s2member_static_asset_health', array('checked' => time(), 'builds' => $builds, 'missing' => $missing));
			return self::$static_assets_health_cache = $missing;
		}

		/**
		 * Displays an admin warning when an active generated frontend asset file is missing.
		 *
		 * Filesystem checks are deliberately limited to administrator requests; normal frontend requests never stat generated asset files.
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
			$health = self::static_assets_health();
			if(!$health)
				return;

			$settings_url = add_query_arg('s2member-open-panel', 'frontend-static-assets', admin_url('/admin.php?page=ws-plugin--s2member-gen-ops')).'#ws-plugin--s2member-static-assets';
			$details = esc_html(implode(' ', $health));
			$message = '<strong>s2Member static frontend asset warning:</strong> '.$details.' <a href="'.esc_url($settings_url).'">Open Frontend CSS/JS Optimization and refresh the static assets.</a>';

			//260903.0612 Surface out-of-band asset deletion anywhere in wp-admin without adding filesystem checks to frontend requests.
			c_ws_plugin__s2member_admin_notices::display_admin_notice($message, TRUE);
			return;
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
				$site_locale = (string)get_option('WPLANG');
				if(!$site_locale && defined('WPLANG'))
					$site_locale = (string)WPLANG;
				$site_locale = ($site_locale) ? $site_locale : 'en_US';
				$current_locale = (function_exists('determine_locale')) ? (string)determine_locale() : (string)get_locale();

				//260903.0729 Framework-level dynamic requirements are authoritative; Pro may narrow only the generic during-JS hook requirement when all callbacks are known static-compatible built-ins.
				$framework_dynamic = apply_filters('ws_plugin__s2member_js_api_constants_enable', FALSE)
					|| has_action('ws_plugin__s2member_before_js_w_globals') || $current_locale !== $site_locale || has_filter('ws_plugin__s2member_files_dir')
					|| has_filter('ws_plugin__s2member_min_password_length') || has_filter('ws_plugin__s2member_min_password_strength_code') || has_filter('ws_plugin__s2member_min_password_strength_score')
					|| isset($GLOBALS['wp_filter']['all']);
				$hook_dynamic = has_action('ws_plugin__s2member_during_js_w_globals');
				$hook_dynamic = (bool)apply_filters('ws_plugin__s2member_dynamic_js_required', $hook_dynamic, get_defined_vars());

				if($framework_dynamic || $hook_dynamic)
					return array('ok' => FALSE, 'sources' => array(), 'minify' => FALSE, 'error' => 'Current JavaScript hooks/configuration require legacy dynamic assets');
				if(!$include_sources)
					return array('ok' => TRUE, 'sources' => array(), 'minify' => !empty($o['static_js_minify']), 'error' => '');

				$sources = ($pro_file) ? array() : array(
					array('file' => $c['dir'].'/src/includes/jquery/jquery.sprintf/jquery.sprintf.js', 'preserve_header' => TRUE),
					array('file' => $c['dir'].'/src/includes/s2member.js', 'render' => TRUE),
				);
				if(!$pro_file)
					$sources = (array)apply_filters('ws_plugin__s2member_static_js_sources', $sources, get_defined_vars());
				if($pro_file || $combine)
					$sources = (array)apply_filters('ws_plugin__s2member_static_pro_js_sources', $sources, get_defined_vars());
				if(!$sources)
					return array('ok' => FALSE, 'sources' => array(), 'minify' => FALSE, 'error' => 'No static JavaScript sources are available for '.$id);
				return array('ok' => TRUE, 'sources' => $sources, 'minify' => !empty($o['static_js_minify']), 'error' => '');
			}
			return array('ok' => FALSE, 'sources' => array(), 'minify' => FALSE, 'error' => 'Invalid static asset type');
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
				if(empty($source['file']) || !is_readable($source['file']))
					return array('ok' => FALSE, 'url' => '', 'path' => '', 'error' => 'Source file is not readable: '.((!empty($source['file'])) ? $source['file'] : '(missing path)'));

				if(!empty($source['render']))
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
						$header = '/*!'.substr(trim($match[1]), 3);
						if(!in_array($header, $headers, TRUE))
							$headers[] = $header;
					}
					$chunk = preg_replace('/\A\s*\/\*\*.*?\*\/\s*/s', '', $chunk, 1);
				}
				if(!empty($source['replacements']) && is_array($source['replacements']))
					$chunk = str_replace(array_keys($source['replacements']), array_values($source['replacements']), $chunk);
				$body .= "\n".((!empty($source['prefix'])) ? $source['prefix']."\n" : '').$chunk.((!empty($source['suffix'])) ? "\n".$source['suffix'] : '');
			}

			try
			{
				$body = ($minify) ? (($type === 'css') ? self::compress_css($body) : self::compress_js($body)) : trim($body);
			}
			catch(Exception $e)
			{
				return array('ok' => FALSE, 'url' => '', 'path' => '', 'error' => 'JavaScript minification failed: '.$e->getMessage());
			}
			$output = (($headers) ? implode("\n", $headers)."\n" : '').$body."\n";
			$tmp = $path.'.tmp-'.uniqid('', TRUE);
			if(file_put_contents($tmp, $output, LOCK_EX) === FALSE || (!@rename($tmp, $path) && !is_file($path)))
			{
				@unlink($tmp);
				return array('ok' => FALSE, 'url' => '', 'path' => '', 'error' => 'Could not write generated asset: '.$path);
			}
			@unlink($tmp);

			//260903.0525 Keep old timestamped files long enough for cached HTML, pruning only generations older than 30 days after a successful replacement build.
			foreach((array)glob($location['dir'].'/'.$id.'-*.'.$type) as $old_path)
				if($old_path !== $path && @filemtime($old_path) < time() - 30 * DAY_IN_SECONDS)
					@unlink($old_path);
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

		/*
		 * JShrink 1.8.1 adaptation.
		 *
		 *260903.0437 The upstream parser is kept intentionally isolated behind `jshrink_*` names. The only
		 * PHP 5.6 compatibility change is avoiding PHP 7.1+ negative string offsets when tracking the last character.
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
