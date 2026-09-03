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
				$static = (!empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['static_css'])) ? c_ws_plugin__s2member_utils_assets::ensure_static_asset('css') : array();
				$static_css = !empty($static['ok']) && !empty($static['url']);
				$dynamic_css = !$static_css;

				if($static_css)
					wp_enqueue_style('ws-plugin--s2member', $static['url'], array(), NULL, 'all');
				else
				{
					$s2o = $GLOBALS['WS_PLUGIN__']['s2member']['c']['s2o_url'];
					wp_enqueue_style('ws-plugin--s2member', $s2o.'?ws_plugin__s2member_css=1&qcABC=1', array(), c_ws_plugin__s2member_utilities::ver_checksum(), 'all');
				}

				//260903.0525 Static CSS mirrors the established combined Framework/Pro response in one cacheable file; incompatible hooks or build failures retain legacy dynamic CSS.
				do_action('ws_plugin__s2member_during_add_css', get_defined_vars());
			}
			do_action('ws_plugin__s2member_after_add_css', get_defined_vars());
		}

		/**
		 * Enqueues JS file for theme integration.
		 *
		 * Be sure s2Member's API Constants are already defined before firing this.
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
				$s2o = $GLOBALS['WS_PLUGIN__']['s2member']['c']['s2o_url'];
				$static = (!is_admin() && !empty($GLOBALS['WS_PLUGIN__']['s2member']['o']['static_js']) && function_exists('wp_add_inline_script')) ? c_ws_plugin__s2member_utils_assets::ensure_static_asset('js') : array();
				$static_js = !empty($static['ok']) && !empty($static['url']);
				$dynamic_js = !$static_js;

				if($static_js)
				{
					wp_enqueue_script('ws-plugin--s2member', $static['url'], array('jquery'), NULL, TRUE);
					//260903.0437 Only current-user values vary per request; WordPress prints them immediately before the cacheable site-wide frontend script.
					wp_add_inline_script('ws-plugin--s2member', c_ws_plugin__s2member_css_js_in::current_user_js_globals(TRUE), 'before');
				}
				else if(is_user_logged_in())
				{
					$md5 = WS_PLUGIN__S2MEMBER_API_CONSTANTS_MD5;
					wp_enqueue_script('ws-plugin--s2member', $s2o.'?ws_plugin__s2member_js_w_globals='.urlencode($md5).'&qcABC=1', array('jquery'), c_ws_plugin__s2member_utilities::ver_checksum(), TRUE);
				}
				else
					wp_enqueue_script('ws-plugin--s2member', $s2o.'?ws_plugin__s2member_js_w_globals=1&qcABC=1', array('jquery'), c_ws_plugin__s2member_utilities::ver_checksum(), TRUE);

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
			if ($handle === 'ws-plugin--s2member') {
				$tag = str_replace(' src=', ' data-cfasync="false" src=', $tag);
			}
			return $tag; // Prevent RocketLoader from loading async.
			// See: <https://support.cloudflare.com/hc/en-us/articles/200169436-How-can-I-have-Rocket-Loader-ignore-my-script-s-in-Automatic-Mode->
		}
	}
}
