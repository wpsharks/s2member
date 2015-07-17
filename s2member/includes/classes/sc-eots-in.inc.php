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
		 * @return string Value of the requested data.
		 */
		public static function sc_eot_details($attr = array(), $content = '', $shortcode = '')
		{
			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_before_sc_eot_details', get_defined_vars());
			unset($__refs, $__v); // Allow variables to be modified by reference.

			c_ws_plugin__s2member_no_cache::no_cache_constants(true);

			$mode = ''; // Initialize shortcode mode and validate.
			if(!empty($attr['mode']) && in_array(strtolower($attr['mode']), array('fixed', 'next'), TRUE))
				$mode = strtolower($attr['mode']); // A specific mode; i.e., `fixed`, `next`.

			if(empty($attr['user_id']) || !(integer)$attr['user_id'])
				$attr['user_id'] = $user_id = get_current_user_id();
			else $user_id = (integer)$attr['user_id'];

			$subscr_gateway = get_user_option('s2member_subscr_gateway', $user_id);
			$subscr_id      = get_user_option('s2member_subscr_id', $user_id);
			$subscr_cid     = get_user_option('s2member_subscr_cid', $user_id);
			$auto_eot_time  = get_user_option('s2member_auto_eot_time', $user_id);

			$attr = shortcode_atts( // Attributes.
				array(
					'user_id'              => '0', // Current.
					'date_format'          => 'F jS, Y, g:i a T',
					'round_to'             => '', // Optional rounding.
					'offset'               => '0', // Optional time offset.
					'future_format'        => $mode ? '%%date%%' : '<strong class="s2member-sc-eot-label -future">'._x('Access Expires:', 's2member-front', 's2member').'</strong> <span class="s2member-sc-eot-date -future">%%date%%</span>',
					'past_format'          => $mode ? '%%date%%' : '<strong class="s2member-sc-eot-label -past">'._x('Access Expired:', 's2member-front', 's2member').'</strong> <span class="s2member-sc-eot-date -past">%%date%%</span>',
					'next_format'          => $mode ? '%%date%%' : '<strong class="s2member-sc-eot-label -ongoing">'._x('Next Payment:', 's2member-front', 's2member').'</strong> <span class="s2member-sc-eot-date -ongoing">%%date%%</span>',
					'empty_format'         => $mode ? (in_array($subscr_gateway, array('stripe', 'paypal', 'clickbank'), TRUE) ? _x('N/A', 's2member-front', 's2member') : _x('—', 's2member-front', 's2member')) : '',
				),
				c_ws_plugin__s2member_utils_strings::trim_qts_deep((array)$attr)
			);
			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_before_sc_eot_details_after_shortcode_atts', get_defined_vars());
			unset($__refs, $__v); // Allow variables to be modified by reference.

			// Collect and cache the EOT for this user.

			$prefix    = 's2m_eot_'; // Transient prefix for this shortcode.
			$hash_vars = $user_id.$subscr_gateway.$subscr_id.$subscr_cid.$auto_eot_time;
			$transient = $prefix.md5('s2member_sc_eot_'.$mode.serialize($attr).$hash_vars);

			if(!is_array($eot = get_transient($transient)))
			{
				$eot = c_ws_plugin__s2member_utils_users::get_user_eot($user_id, true, $mode);
				set_transient($transient, $eot, DAY_IN_SECONDS / 2);
			}
			if($eot['time'] && $attr['round_to'])
				$eot['time'] = strtotime($attr['round_to'], $eot['time']);

			if($eot['time'] && (integer)$attr['offset'])
				$eot['time'] = $eot['time'] + (integer)$attr['offset'];

			// Initialize EOT details/output format.

			if($eot['type'] === 'fixed' && $eot['time'] && $eot['tense'] === 'past')
				$details = $attr['past_format'];

			else if($eot['type'] === 'fixed' && $eot['time'] && $eot['tense'] === 'future')
				$details = $attr['future_format'];

			else if($eot['type'] === 'next' && $eot['time'] && $eot['tense'] === 'future')
				$details = $attr['next_format'];

			else $details = $attr['empty_format'];

			// Initialize EOT details/output date format.

			if($eot['time'] && $attr['date_format'] === 'timestamp')
				$date = (string)$eot['time']; // Timestamp.

			else if($eot['time'] && $attr['date_format'] === 'default')
				$date = date(get_option('date_format'), $eot['time']);

			else if($eot['time'] && $attr['date_format'])
				$date = date($attr['date_format'], $eot['time']);

			else if($eot['time']) // Default date format.
				$date = date(get_option('date_format'), $eot['time']);

			else $date = ''; // Default date; i.e., nothing.

			$details = str_ireplace('%%date%%', esc_html($date), $details);

			// Check special considerations and the current mode.

			if($eot['type'] === 'fixed' && !$GLOBALS['WS_PLUGIN__']['s2member']['o']['auto_eot_system_enabled'])
				$details = $attr['empty_format']; // EOTs are disabled on this site.

			else if($eot['type'] === 'fixed' && $mode === 'next')
				$details = $attr['empty_format'];

			else if($eot['type'] === 'next' && $mode === 'fixed')
				$details = $attr['empty_format'];

			// Return the details/output from this shortcode.

			return apply_filters('ws_plugin__s2member_sc_eot_details', $details, get_defined_vars());
		}
	}
}
