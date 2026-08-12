<?php
// @codingStandardsIgnoreFile
/**
 * Enqueues/displays administrative notices.
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
 * @package s2Member\Admin_Notices
 * @since 3.5
 */
if(!defined('WPINC')) // MUST have WordPress.
	exit('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_admin_notices'))
{
	/**
	 * Enqueues/displays administrative notices.
	 *
	 * @package s2Member\Admin_Notices
	 * @since 3.5
	 */
	class c_ws_plugin__s2member_admin_notices
	{
		/**
		 * Enqueues administrative notices.
		 *
		 * @package s2Member\Admin_Notices
		 * @since 3.5
		 *
		 * @param string       $notice String value of actual notice *(i.e., the message)*.
		 * @param string|array $on_pages Optional. Defaults to any page. String or array of pages to display this notice on.
		 * @param bool         $error Optional. True if this notice is regarding an error. Defaults to false.
		 * @param int          $time Optional. Unix timestamp indicating when this notice will be displayed.
		 * @param bool         $dismiss Optional. If true, the notice will remain persistent, until dismissed. Defaults to false.
		 */
		public static function enqueue_admin_notice($notice = '', $on_pages = array(), $error = FALSE, $time = 0, $dismiss = FALSE)
		{
			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_before_enqueue_admin_notice', get_defined_vars());
			unset($__refs, $__v); // Allow variables to be modified by reference.

			if($notice && is_string($notice))// Have a valid string.
			{
				$notices = (array)get_option('ws_plugin__s2member_notices');
				array_push($notices, array('notice' => $notice, 'on_pages' => $on_pages, 'error' => $error, 'time' => $time, 'dismiss' => $dismiss));

				foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
				do_action('ws_plugin__s2member_during_enqueue_admin_notice', get_defined_vars());
				unset($__refs, $__v); // Allow variables to be modified by reference.

				update_option('ws_plugin__s2member_notices', c_ws_plugin__s2member_utils_arrays::array_unique($notices));
			}
			do_action('ws_plugin__s2member_after_enqueue_admin_notice', get_defined_vars());
		}

		/**
		 * Displays an administrative notice.
		 *
		 * @package s2Member\Admin_Notices
		 * @since 3.5
		 *
		 * @param string $notice String value of actual notice *(i.e., the message)*.
		 * @param bool   $error Optional. True if this notice is regarding an error. Defaults to false.
		 * @param bool   $dismiss Optional. If true, the notice will be displayed with a dismissal link. Defaults to false.
		 */
		public static function display_admin_notice($notice = '', $error = FALSE, $dismiss = FALSE)
		{
			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_before_display_admin_notice', get_defined_vars());
			unset($__refs, $__v); // Allow variables to be modified by reference.

			if($dismiss) $dismissal_link = '<div style="float:right; margin:0 0 0 1em; font-weight:bold;">'.
				'[ <a href="'.esc_attr(add_query_arg('ws-plugin--s2member-dismiss-admin-notice', urlencode(md5($notice)), $_SERVER['REQUEST_URI'])).'">dismiss</a> ]'.
				'</div>';
			if($notice && is_string($notice) && $error)
			{
				if($dismiss && !empty($dismissal_link))
					$notice = $dismissal_link.$notice;
				echo '<div class="notice notice-error"><p>'.wp_kses_post($notice).'</p></div>';
			}
			else if($notice && is_string($notice))
			{
				if($dismiss && !empty($dismissal_link))
					$notice = $dismissal_link.$notice;
				echo '<div class="notice notice-info"><p>'.wp_kses_post($notice).'</p></div>';
			}
			do_action('ws_plugin__s2member_after_display_admin_notice', get_defined_vars());
		}

		/**
		 * Displays a branded s2Member security notice.
		 *
		 * @package s2Member\Admin_Notices
		 * @since 260813
		 *
		 * @param string $message Main notice message.
		 * @param string $review Review prompt shown above the items.
		 * @param array $items Notice items, with safe HTML allowed.
		 * @param string $dismiss_url Optional dismissal URL.
		 */
		public static function display_security_notice($message = '', $review = '', $items = array(), $dismiss_url = '')
		{
			$message = trim((string)$message);
			$review = trim((string)$review);
			$items = (array)$items;
			if(!$message)
				return;

			$_items = array();
			foreach($items as $_item)
				if(is_string($_item) && trim($_item) !== '')
					$_items[] = '<em>&bull;&nbsp; '.wp_kses_post($_item).'</em>';

			$_logo_url = $GLOBALS['WS_PLUGIN__']['s2member']['c']['dir_url'].'/src/images/logo-square-big.png';
			$_dismiss = (($dismiss_url !== '') ? '<a href="'.esc_url($dismiss_url).'" title="Dismiss until detected again" style="position:absolute; top:8px; right:10px; text-decoration:none;">Dismiss</a>' : '');
			echo '<div class="notice notice-warning" style="position:relative; margin:0 0 15px 2px !important; padding:8px 60px 8px 8px !important;">'.$_dismiss.'<table cellspacing="0" cellpadding="0"><tr><td style="vertical-align:top; padding:0 10px 0 0;"><img src="'.esc_url($_logo_url).'" alt="" width="40" height="40" style="border:0;" /></td><td style="vertical-align:top;"><strong>s2Member Security Notice</strong><br />'.wp_kses_post($message).(($review !== '') ? '<br />'.wp_kses_post($review) : '').(($_items) ? '<br />'.implode('<br />', $_items) : '').'</td></tr></table></div>';
		}

		/**
		 * Records a shortcode user field that is not approved for cross-user display.
		 *
		 * @package s2Member\Admin_Notices
		 * @since 260813
		 *
		 * @param string $field User field ID.
		 * @param string $shortcode Shortcode name.
		 * @param int $post_id Post/Page ID.
		 */
		public static function shortcode_user_field_unapproved($field = '', $shortcode = '', $post_id = 0)
		{
			$field = trim((string)$field);
			$shortcode = trim((string)$shortcode);
			$post_id = (int)$post_id;
			if(!$field || !$shortcode)
				return;

			$_fields = (array)get_option('ws_plugin__s2member_shortcode_user_fields_transition_fields', array());
			$_entry_key = md5(strtolower($field)."\0".strtolower($shortcode)."\0".$post_id);

			//260813 Keep each detected shortcode location separate, while limiting stored warning data.
			if(!isset($_fields[$_entry_key]) && count($_fields) >= 40)
				return;
			$_old_fields = $_fields;
			$_fields[$_entry_key] = array('field' => $field, 'shortcode' => $shortcode, 'post_id' => $post_id);

			if($_fields !== $_old_fields)
				update_option('ws_plugin__s2member_shortcode_user_fields_transition_fields', $_fields, FALSE);
		}

		/**
		 * Dismisses the shortcode user-fields security notice until another affected shortcode is detected.
		 *
		 * @package s2Member\Admin_Notices
		 * @since 260813
		 */
		public static function dismiss_shortcode_user_fields_notice()
		{
			if(!is_admin() || !current_user_can('create_users') || empty($_GET['s2member-dismiss-shortcode-user-fields-notice']))
				return;

			check_admin_referer('s2member-dismiss-shortcode-user-fields-notice');
			delete_option('ws_plugin__s2member_shortcode_user_fields_transition_fields');

			wp_safe_redirect(wp_get_referer() ? wp_get_referer() : admin_url());
			exit;
		}

		/**
		 * Displays the shared shortcode user-fields security notice.
		 *
		 * @package s2Member\Admin_Notices
		 * @since 260813
		 */
		public static function shortcode_user_fields_notice()
		{
			if(!current_user_can('create_users'))
				return;

			$_fields = (array)get_option('ws_plugin__s2member_shortcode_user_fields_transition_fields', array());
			if(!$_fields)
				return;

			//260813 Use the submitted whitelist on save so the notice updates immediately.
			$_using_submitted_whitelist = !empty($_POST['ws_plugin__s2member_options_save']) && is_string($_POST['ws_plugin__s2member_options_save']) && wp_verify_nonce($_POST['ws_plugin__s2member_options_save'], 'ws-plugin--s2member-options-save') && isset($_POST['ws_plugin__s2member_sc_user_fields_whitelist']) && is_string($_POST['ws_plugin__s2member_sc_user_fields_whitelist']);
			$_field_whitelist = ($_using_submitted_whitelist) ? trim((string)wp_unslash($_POST['ws_plugin__s2member_sc_user_fields_whitelist'])) : trim((string)$GLOBALS['WS_PLUGIN__']['s2member']['o']['sc_user_fields_whitelist']);
			$_field_whitelist = ($_field_whitelist !== '') ? preg_split('/\s*,\s*/', strtolower($_field_whitelist), -1, PREG_SPLIT_NO_EMPTY) : array();
			$_field_whitelist = array_flip($_field_whitelist);
			foreach($_fields as $_key => $_details)
			{
				if(!is_array($_details) || empty($_details['field']) || empty($_details['shortcode']))
					unset($_fields[$_key]);
				else if(isset($_field_whitelist[strtolower($_details['field'])]))
					unset($_fields[$_key]);
			}

			if(!$_fields)
			{
				delete_option('ws_plugin__s2member_shortcode_user_fields_transition_fields');
				return;
			}
			update_option('ws_plugin__s2member_shortcode_user_fields_transition_fields', $_fields, FALSE);

			// Build a useful field list with a separate entry for each detected shortcode location.
			$_field_items = array();
			foreach($_fields as $_details)
			{
				$_item = esc_html($_details['field']).' — ['.esc_html($_details['shortcode']).']';
				$_post_id = (!empty($_details['post_id'])) ? (int)$_details['post_id'] : 0;
				if($_post_id > 0 && ($_edit_link = get_edit_post_link($_post_id, '')))
				{
					$_post_title = get_the_title($_post_id);
					$_post_title = ($_post_title !== '') ? $_post_title : '(no title)';
					$_item .= ' — <a href="'.esc_url($_edit_link).'">'.esc_html($_post_title).' (#'.$_post_id.')</a>';
				}
				$_field_items[] = $_item;
			}
			unset($_details, $_item, $_post_id, $_edit_link, $_post_title);

			$_settings_url = add_query_arg('s2member-open-panel', 'shortcode-user-fields-whitelist', admin_url('/admin.php?page=ws-plugin--s2member-gen-ops')).'#ws-plugin--s2member-shortcode-user-fields-whitelist';
			$_dismiss_url = wp_nonce_url(add_query_arg('s2member-dismiss-shortcode-user-fields-notice', '1', admin_url()), 's2member-dismiss-shortcode-user-fields-notice');
			$_message = 'Some s2Member shortcodes use user fields that are not in <em><a href="'.esc_url($_settings_url).'">s2Member → General Options → Shortcode User Fields Whitelist</a></em>';
			c_ws_plugin__s2member_admin_notices::display_security_notice($_message, 'Review the fields below and allow the ones that are okay for other users to see:', $_field_items, $_dismiss_url);
		}

		/**
		 * Processes all administrative notices.
		 *
		 * @package s2Member\Admin_Notices
		 * @since 3.5
		 *
		 * @attaches-to ``add_action('admin_notices');``
		 * @attaches-to ``add_action('user_admin_notices');``
		 * @attaches-to ``add_action('network_admin_notices');``
		 * @todo Update to ``add_action('all_admin_notices');``.
		 */
		public static function admin_notices()
		{
			global $pagenow; // This holds the current page filename.

			do_action('ws_plugin__s2member_before_admin_notices', get_defined_vars());

			if(is_admin() && is_array($notices = get_option('ws_plugin__s2member_notices')) && !empty($notices))
			{
				$a = (is_blog_admin()) ? 'blog' : '';
				$a = (is_user_admin()) ? 'user' : $a;
				$a = (is_network_admin()) ? 'network' : $a;
				$a = (!$a) ? 'blog' : $a; // Default blog admin.

				foreach($notices as $i => $notice) // Check several things about each notice.
				{
					//250510 Fixed for PHP 8.1+: safely normalize on_pages before foreach
					$notice = (array)$notice;
					$notice['on_pages'] = empty($notice['on_pages']) ? array('*') : (array)$notice['on_pages'];
					foreach($notice['on_pages'] as $page) 
					{
						if(!preg_match('/^(.+?)\:/', $page)) // NO prefix?
							$page = 'blog:'.ltrim($page, ':'); // `blog:`

						$adms = preg_split('/\|/', preg_replace('/\:(.*)$/i', '', $page));
						$page = preg_replace('/^([^\:]*)\:/i', '', $page);

						if(empty($adms) || in_array('*', $adms) || in_array($a, $adms))
							if(!$page || '*' === $page || $pagenow === $page || @$_GET['page'] === $page)
							{
								if(strtotime('now') >= (int)$notice['time']) // Time to show it?
								{
									foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
									do_action('ws_plugin__s2member_during_admin_notices_before_display', get_defined_vars());
									unset($__refs, $__v); // Allow variables to be modified by reference.

									if(!$notice['dismiss'] || (!empty($_GET['ws-plugin--s2member-dismiss-admin-notice']) && $_GET['ws-plugin--s2member-dismiss-admin-notice'] === md5($notice['notice'])))
										unset($notices[$i]); // Clear this administrative notice now?

									if(!$notice['dismiss'] || empty($_GET['ws-plugin--s2member-dismiss-admin-notice']) || $_GET['ws-plugin--s2member-dismiss-admin-notice'] !== md5($notice['notice']))
										c_ws_plugin__s2member_admin_notices::display_admin_notice($notice['notice'], $notice['error'], $notice['dismiss']);

									do_action('ws_plugin__s2member_during_admin_notices_after_display', get_defined_vars());
								}
								continue 2; // This notice processed; continue.
							}
					}
				}
				$notices = array_merge($notices); // Re-index array.

				foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
				do_action('ws_plugin__s2member_during_admin_notices', get_defined_vars());
				unset($__refs, $__v); // Allow variables to be modified by reference.

				update_option('ws_plugin__s2member_notices', $notices);
			}
			do_action('ws_plugin__s2member_after_admin_notices', get_defined_vars());
		}
	}
}
