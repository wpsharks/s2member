<?php
// @codingStandardsIgnoreFile
/**
 * Shortcode `[s2Get /]` (inner processing routines).
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
 * @package s2Member\s2Get
 * @since 3.5
 */
if(!defined('WPINC')) // MUST have WordPress.
	exit('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_sc_gets_in'))
{
	/**
	 * Shortcode `[s2Get /]` (inner processing routines).
	 *
	 * @package s2Member\s2Get
	 * @since 3.5
	 */
	class c_ws_plugin__s2member_sc_gets_in
	{
		/**
		 * Handles the Shortcode for: `[s2Get /]`.
		 *
		 * @package s2Member\s2Get
		 * @since 3.5
		 *
		 * @attaches-to ``add_shortcode('s2Get');``
		 *
		 * @param array  $attr An array of Attributes.
		 * @param string $content Content inside the Shortcode.
		 * @param string $shortcode The actual Shortcode name itself.
		 *
		 * @return mixed Value of the requested data.
		 */
		public static function sc_get_details($attr = array(), $content = '', $shortcode = '')
		{
			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_before_sc_get_details', get_defined_vars());
			unset($__refs, $__v); // Allow variables to be modified by reference.

			c_ws_plugin__s2member_no_cache::no_cache_constants(true);

			$attr = shortcode_atts( // Attributes.
				array(
					// One of these.
					'constant'      => '',
					'user_field'    => '',
					'user_option'   => '',

					// Options.
					'user_id'        => '',
					'date_format'    => '',
					'size'           => '',
				),
				c_ws_plugin__s2member_utils_strings::trim_qts_deep((array)$attr)
			);

			//241114 Valid attribute values
			$valid_constants    = array_flip(array('S2MEMBER_CURRENT_USER_ACCESS_LABEL', 'S2MEMBER_CURRENT_USER_ACCESS_LEVEL', 'S2MEMBER_CURRENT_USER_CUSTOM', 'S2MEMBER_CURRENT_USER_DISPLAY_NAME', 'S2MEMBER_CURRENT_USER_DOWNLOADS_ALLOWED', 'S2MEMBER_CURRENT_USER_DOWNLOADS_ALLOWED_DAYS', 'S2MEMBER_CURRENT_USER_DOWNLOADS_ALLOWED_IS_UNLIMITED', 'S2MEMBER_CURRENT_USER_DOWNLOADS_CURRENTLY', 'S2MEMBER_CURRENT_USER_EMAIL', 'S2MEMBER_CURRENT_USER_FIELDS', 'S2MEMBER_CURRENT_USER_FIRST_NAME', 'S2MEMBER_CURRENT_USER_ID', 'S2MEMBER_CURRENT_USER_IP', 'S2MEMBER_CURRENT_USER_IS_LOGGED_IN', 'S2MEMBER_CURRENT_USER_IS_LOGGED_IN_AS_MEMBER', 'S2MEMBER_CURRENT_USER_LAST_NAME', 'S2MEMBER_CURRENT_USER_LOGIN', 'S2MEMBER_CURRENT_USER_LOGIN_COUNTER', 'S2MEMBER_CURRENT_USER_PAID_REGISTRATION_DAYS', 'S2MEMBER_CURRENT_USER_PAID_REGISTRATION_TIME', 'S2MEMBER_CURRENT_USER_PROFILE_MODIFICATION_PAGE_URL', 'S2MEMBER_CURRENT_USER_REGISTRATION_DAYS', 'S2MEMBER_CURRENT_USER_REGISTRATION_IP', 'S2MEMBER_CURRENT_USER_REGISTRATION_TIME', 'S2MEMBER_CURRENT_USER_SUBSCR_GATEWAY', 'S2MEMBER_CURRENT_USER_SUBSCR_ID', 'S2MEMBER_CURRENT_USER_SUBSCR_OR_WP_ID', 'S2MEMBER_CURRENT_USER_VALUE_FOR_PP_ON0', 'S2MEMBER_CURRENT_USER_VALUE_FOR_PP_ON1', 'S2MEMBER_CURRENT_USER_VALUE_FOR_PP_OS0', 'S2MEMBER_CURRENT_USER_VALUE_FOR_PP_OS1'));
			$valid_user_fields  = array_flip(array('user_login', 'user_email', 'first_name', 'last_name', 'full_name', 'display_name', 'avatar', 's2member_custom', 's2member_subscr_id', 's2member_subscr_or_wp_id', 's2member_subscr_gateway', 's2member_registration_ip', 's2member_custom_fields', 's2member_file_download_access_log', 's2member_file_download_access_arc', 's2member_auto_eot_time', 's2member_last_payment_time', 's2member_paid_registration_times', 's2member_access_role', 's2member_access_level', 's2member_access_label', 's2member_ccaps', 's2member_file_downloads_allowed', 's2member_file_downloads_allowed_is_unlimited', 's2member_file_downloads_current', 's2member_login_counter', 's2member_last_login_time', 's2member_notes'));
			$valid_user_options = array_flip(array('s2member_custom', 's2member_subscr_id', 's2member_subscr_gateway', 's2member_registration_ip', 's2member_login_counter', 's2member_last_payment_time', 's2member_access_label', 's2member_ccaps'));
			// Current user only
			$attr['user_id_backup'] = $attr['user_id'];
			$attr['user_id'] = 0;

			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_before_sc_get_details_after_shortcode_atts', get_defined_vars());
			unset($__refs, $__v); // Allow variables to be modified by reference.

			if($attr['constant'] && defined($attr['constant']) && isset($valid_constants[$attr['constant']]))
			{
				$get = constant($attr['constant']);
			}
			else if($attr['user_field'] && isset($valid_user_fields[$attr['user_field']]))
				{
					$user_field_args = array('size' => $attr['size']);
					$get = c_ws_plugin__s2member_utils_users::get_user_field($attr['user_field'], (int)$attr['user_id'], $user_field_args);

					if(preg_match('/time$/i', $attr['user_field']) && $attr['date_format'])
						if((is_numeric($get) && strlen($get) === 10) || ($get = strtotime($get))) // Timestamp?
							{
								if($attr['date_format'] === 'timestamp')
									$get = (string)$get; // No change.

								else if($attr['date_format'] === 'default')
									$get = date(get_option('date_format'), (integer)$get);

								else $get = date($attr['date_format'], (integer)$get);
							}
				}
			else if($attr['user_option'] && isset($valid_user_options[$attr['user_option']]))
				{
					$get = get_user_option($attr['user_option'], (int)$attr['user_id']);

					if(preg_match('/time$/i', $attr['user_option']) && $attr['date_format'])
						if((is_numeric($get) && strlen($get) === 10) || ($get = strtotime($get))) // Timestamp?
							{
								if($attr['date_format'] === 'timestamp')
									$get = (string)$get; // No change.

								else if($attr['date_format'] === 'default')
									$get = date(get_option('date_format'), (integer)$get);

								else $get = date($attr['date_format'], (integer)$get);
							}
				}
			if(isset($get) && (is_array($get) || is_object($get)))
			{
				$_get_array = $get; // Temporary variable.
				$get        = array(); // New empty array.

				foreach($_get_array as $_key_prop => $_value)
				{
					if(is_scalar($_value)) // One dimension only.
						$get[$_key_prop] = (string)$_value;
				}
				unset($_get_array, $_key_prop, $_value); // Housekeeping.

				$get = implode(', ', $get); // Convert to a string now.
			}
			if(isset($get) && !is_scalar($get))
				$get = ''; // Do not allow non-scalar values to be returned by a shortcode.
			else if(isset($get)) $get = (string)$get; // Convert to a string.

			return apply_filters('ws_plugin__s2member_sc_get_details', isset($get) ? $get : '', get_defined_vars());
		}
	}
}
