<?php
// @codingStandardsIgnoreFile
/**
 * CSS/JS integrations with theme.
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
 * @package s2Member\CSS_JS
 * @since 3.5
 */
if(!defined('WPINC')) // MUST have WordPress.
	exit ('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_css_js_themes'))
{
	/**
	 * CSS/JS integrations with theme.
	 *
	 * @package s2Member\CSS_JS
	 * @since 3.5
	 */
	class c_ws_plugin__s2member_css_js_themes
	{
		/**
		 * Lazy load CSS/JS files?
		 *
		 * @package s2Member\CSS_JS
		 * @since 131028
		 *
		 * @return boolean TRUE if we should load; else FALSE.
		 */
		public static function lazy_load_css_js()
		{
			static $load; // Static cache var.

			if(isset($load)) return $load;

			$null = NULL; // Needed below in earlier versions of WP.

			if(!$GLOBALS['WS_PLUGIN__']['s2member']['o']['lazy_load_css_js'])
				$load = TRUE;

			else if(c_ws_plugin__s2member_systematics::is_s2_systematic_use_page())
				$load = TRUE;

			else if(!empty($_GET[apply_filters('ws_plugin__s2member_check_force_ssl_get_var_name', 's2-ssl', array())]))
				$load = TRUE;

			else if(c_ws_plugin__s2member_utils_conds::bp_is_installed()
			        && (bp_is_register_page() || bp_is_activation_page() || bp_is_user_profile())
			) $load = TRUE;

			else if(is_singular() && ($post = get_post($null))
			        && (stripos($post->post_content, 's2member') !== FALSE || stripos($post->post_content, '[s2') !== FALSE)
			) $load = TRUE;

			else if(preg_match('/\/wp\-signup\.php|\/wp\-login\.php|\/wp\-admin\/(?:user\/)?profile\.php|[?&]s2member/', $_SERVER['REQUEST_URI']))
				$load = TRUE;

			if(!isset($load)) $load = FALSE; // Make sure it's set; always.

			return ($load = apply_filters('ws_plugin__s2member_lazy_load_css_js', $load));
		}

		/**
		 * Enqueues CSS file for theme integration.
		 *
		 * @package s2Member\CSS_JS
		 * @since 3.5
		 *
		 * @attaches-to ``add_action('wp_print_styles');``
		 *
		 * @return null After enqueuing CSS for theme integration.
		 */
		public static function add_css()
		{
			do_action('ws_plugin__s2member_before_add_css', get_defined_vars());

			if(!is_admin() && c_ws_plugin__s2member_css_js_themes::lazy_load_css_js())
			{
				$static = (!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['static_css'])) ? c_ws_plugin__s2member_utils_assets::ensure_static_assets('css') : array();
				$static_css = !empty($static['ok']) && !empty($static['assets']);
				$dynamic_css = !$static_css;

				if($static_css)
				{
					$dependency = array();
					foreach($static['assets'] as $id => $asset)
					{
						$handle = ($id === 's2member-pro.css') ? 'ws-plugin--s2member-pro' : 'ws-plugin--s2member';
						wp_enqueue_style($handle, $asset['url'], $dependency, NULL, 'all');
						c_ws_plugin__s2member_utils_assets::register_page_asset_expectations($id, 'css', $asset['url'], 'static', $asset['build']);
						$dependency = array($handle);
					}
				}
				else
				{
					//260906.0738 If requested static delivery cannot be used, prefer full WordPress so normal-plugin hooks/customizations that caused the fallback are preserved.
					$dynamic_css_url = c_ws_plugin__s2member_utils_assets::dynamic_asset_url(!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['static_css'])).'?ws_plugin__s2member_css=1&qcABC=1';
					$dynamic_css_delivery = (strpos($dynamic_css_url, $GLOBALS['WS_PLUGIN__']['s2member']['c']['s2o_url'].'?') === 0) ? 'dynamic-s2member-o' : 'dynamic-wordpress'; //260910.0709 Runtime diagnostics name the physical route; the saved loader preference intentionally remains the legacy `s2o` value.
					$static_css_failed_id = '';
					if(!empty($static['assets']) && is_array($static['assets']))
						foreach($static['assets'] as $_static_css_id => $_static_css_asset)
							if(empty($_static_css_asset['ok']))
							{
								$static_css_failed_id = (string)$_static_css_id;
								break;
							}
					wp_enqueue_style('ws-plugin--s2member', $dynamic_css_url, array(), c_ws_plugin__s2member_utilities::ver_checksum(), 'all');
					//260911.1806 Preserve the failed preferred static asset and cause server-side so Last issue can remain specific after delivery recovers.
					c_ws_plugin__s2member_utils_assets::register_page_asset_expectations($static_css_failed_id, 'css', $dynamic_css_url, $dynamic_css_delivery, 0, (!empty($static['error'])) ? (string)$static['error'] : '');
				}

				//260903.1918 Static CSS keeps Framework/Pro files separate by default, with optional combining; any incompatible hook/build failure retains the single legacy dynamic response.
				do_action('ws_plugin__s2member_during_add_css', get_defined_vars());
			}
			do_action('ws_plugin__s2member_after_add_css', get_defined_vars());
		}

		/**
		 * Enqueues JS file for theme integration.
		 *
		 * Current-user inline globals tolerate unusually early script printing before s2Member's normal API Constants setup.
		 *
		 * @package s2Member\CSS_JS
		 * @since 3.5
		 *
		 * @attaches-to ``add_action('wp_print_scripts');``
		 *
		 * @return null After enqueuing JS for theme integration.
		 */
		public static function add_js_w_globals()
		{
			global $pagenow; // Need this for comparisons.

			do_action('ws_plugin__s2member_before_add_js_w_globals', get_defined_vars());

			if((!is_admin() && c_ws_plugin__s2member_css_js_themes::lazy_load_css_js()) || (is_user_admin() && $pagenow === 'profile.php' && !current_user_can('edit_users')))
			{
				$static = (!is_admin() && !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['static_js']) && function_exists('wp_add_inline_script')) ? c_ws_plugin__s2member_utils_assets::ensure_static_assets('js') : array();
				$static_js = !empty($static['ok']) && !empty($static['assets']);
				$page_text = c_ws_plugin__s2member_utils_assets::static_js_text_delivery() === 'page';
				$static_inline_js = ($static_js && $page_text) ? c_ws_plugin__s2member_utils_assets::static_js_inline_data($static['assets']) : '';
				if($static_js && $page_text && $static_inline_js === '')
					$static_js = FALSE; //260906.2049 Page-loaded JavaScript text requires the matching shipped data map before slot-based static JavaScript can be used.
				$dynamic_js = !$static_js;

				if($static_js)
				{
					$dependency = array('jquery');
					foreach($static['assets'] as $id => $asset)
					{
						$handle = ($id === 's2member-pro.js') ? 'ws-plugin--s2member-pro' : 'ws-plugin--s2member';
						wp_enqueue_script($handle, $asset['url'], $dependency, NULL, TRUE);
						if($handle === 'ws-plugin--s2member')
							wp_add_inline_script($handle, c_ws_plugin__s2member_css_js_in::current_user_js_globals(TRUE).(($static_inline_js !== '') ? "\n".$static_inline_js : ''), 'before');
						c_ws_plugin__s2member_utils_assets::register_page_asset_expectations($id, 'js', $asset['url'], 'static', $asset['build']);
						$dependency = array($handle);
					}
				}
				else
				{
					//260906.0738 A static-JS compatibility fallback uses full WordPress so runtime gettext/plugin customizations remain available; static-disabled sites retain their selected dynamic loader.
					$dynamic_asset_url = c_ws_plugin__s2member_utils_assets::dynamic_asset_url(!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['static_js']));
					$dynamic_js_value = (is_user_logged_in()) ? WS_PLUGIN__S2MEMBER_API_CONSTANTS_MD5 : '1';
					$dynamic_js_url = $dynamic_asset_url.'?ws_plugin__s2member_js_w_globals='.urlencode($dynamic_js_value).'&qcABC=1';
					$dynamic_js_delivery = (strpos($dynamic_js_url, $GLOBALS['WS_PLUGIN__']['s2member']['c']['s2o_url'].'?') === 0) ? 'dynamic-s2member-o' : 'dynamic-wordpress'; //260910.0709 Runtime diagnostics name the physical route; the saved loader preference intentionally remains the legacy `s2o` value.
					$static_js_failed_id = '';
					if(!empty($static['assets']) && is_array($static['assets']))
						foreach($static['assets'] as $_static_js_id => $_static_js_asset)
							if(empty($_static_js_asset['ok']))
							{
								$static_js_failed_id = (string)$_static_js_id;
								break;
							}
					wp_enqueue_script('ws-plugin--s2member', $dynamic_js_url, array('jquery'), c_ws_plugin__s2member_utilities::ver_checksum(), TRUE);
					//260911.1806 Preserve the failed preferred static asset and cause server-side so Last issue can remain specific after delivery recovers.
					c_ws_plugin__s2member_utils_assets::register_page_asset_expectations($static_js_failed_id, 'js', $dynamic_js_url, $dynamic_js_delivery, 0, (!empty($static['error'])) ? (string)$static['error'] : '');
				}

				do_action('ws_plugin__s2member_during_add_js_w_globals', get_defined_vars());
			}
			do_action('ws_plugin__s2member_after_add_js_w_globals', get_defined_vars());
		}

		/**
		 * Disallow async loading.
		 *
		 * @package s2Member\CSS_JS
		 * @since 170221 Enhancing CloudFlare compat.
		 *
		 * @attaches-to ``add_filter('script_loader_tag');``
		 *
		 * @param string $tag The script tag.
		 * @param string $handle The script handle.
		 *
		 * @return string Possibly altered script tag.
		 */
		public static function script_loader_tag($tag = '', $handle = '')
		{
			if (in_array($handle, array('ws-plugin--s2member', 'ws-plugin--s2member-pro'), TRUE)) {
				$tag = str_replace(' src=', ' data-cfasync="false" src=', $tag);
			}
			return $tag; // Prevent RocketLoader from loading async.
			// See: <https://support.cloudflare.com/hc/en-us/articles/200169436-How-can-I-have-Rocket-Loader-ignore-my-script-s-in-Automatic-Mode->
		}
	}
}
