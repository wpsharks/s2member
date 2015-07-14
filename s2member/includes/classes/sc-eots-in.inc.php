<?php
/**
 * Shortcode `[s2Eot /]` (inner processing routines).
 *
 * Copyright: © 2009-2011
 * {@link http://www.websharks-inc.com/ WebSharks, Inc.}
 * (coded in the USA)
 *
 * Released under the terms of the GNU General Public License.
 * You should have received a copy of the GNU General Public License,
 * along with this software. In the main directory, see: /licensing/
 * If not, see: {@link http://www.gnu.org/licenses/}.
 *
 * @package s2Member\s2Eot
 * @since 150713
 */
if(!defined('WPINC')) // MUST have WordPress.
	exit('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_sc_eots_in'))
{
	/**
	 * Shortcode `[s2Eot /]` (inner processing routines).
	 *
	 * @package s2Member\s2Eot
	 * @since 150713
	 */
	class c_ws_plugin__s2member_sc_eots_in
	{
		/**
		 * Handles the Shortcode for: `[s2Eot /]`.
		 *
		 * @package s2Member\s2Eot
		 * @since 150713
		 *
		 * @attaches-to ``add_shortcode('s2Eot');``
		 *
		 * @param array  $attr An array of Attributes.
		 * @param string $content Content inside the Shortcode.
		 * @param string $shortcode The actual Shortcode name itself.
		 *
		 * @return mixed Value of the requested data, or null on failure.
		 */
		public static function sc_eot_details($attr = array(), $content = '', $shortcode = '')
		{
			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_before_sc_eot_details', get_defined_vars());
			unset($__refs, $__v); // Allow variables to be modified by reference.

			$attr = shortcode_atts( // Attributes.
				array(
					'user_id'              => '',
					'date_format'          => get_option('date_format'),
					'future_format'        => _x('%%date%%', 's2member-front', 's2member'),
					'past_format'          => _x('%%date%%', 's2member-front', 's2member'),
					'empty_format'         => _x('N/A', 's2member-front', 's2member'),
				),
				c_ws_plugin__s2member_utils_strings::trim_qts_deep((array)$attr)
			);
			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_before_sc_eot_details_after_shortcode_atts', get_defined_vars());
			unset($__refs, $__v); // Allow variables to be modified by reference.

			if(($time = (integer)get_user_option('s2member_auto_eot_time', (int)$attr['user_id'])))
			{
				if($time <= time())
					$details = $attr['past_format'];
				else $details = $attr['future_format'];

				if($attr['date_format'] === 'timestamp')
					$date = (string)$time; // Timestamp.

				else if($attr['date_format'] === 'default')
					$date = date(get_option('date_format'), $time);

				else if($attr['date_format']) // Anything?
					$date = date($attr['date_format'], $time);

				else $date = date(get_option('date_format'), $time);

				$details = str_ireplace('%%date%%', esc_html($date), $details);
			}
			else $details = $attr['empty_format']; // Default format for empty EOT time.

			return apply_filters('ws_plugin__s2member_sc_eot_details', $details, get_defined_vars());
		}
	}
}
