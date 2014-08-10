<?php
/**
 * Shortcode `[s2File /]` (inner processing routines).
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
 * @package s2Member\s2File
 * @since 110926
 */
if(realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME']))
	exit('Do not access this file directly.');

if(!class_exists('c_ws_plugin__s2member_sc_files_in'))
{
	/**
	 * Shortcode `[s2File /]` (inner processing routines).
	 *
	 * @package s2Member\s2File
	 * @since 110926
	 */
	class c_ws_plugin__s2member_sc_files_in
	{
		/**
		 * Handles the Shortcode for: `[s2File /]`.
		 *
		 * @package s2Member\s2File
		 * @since 110926
		 *
		 * @attaches-to ``add_shortcode('s2File');``
		 *
		 * @param array  $attr An array of Attributes.
		 * @param string $content Content inside the Shortcode.
		 * @param string $shortcode The actual Shortcode name itself.
		 *
		 * @return string Value of requested File Download URL, streamer array element; or null on failure.
		 */
		public static function sc_get_file($attr = array(), $content = '', $shortcode = '')
		{
			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_before_sc_get_file', get_defined_vars());
			unset($__refs, $__v); // Housekeeping.

			$attr = c_ws_plugin__s2member_utils_strings::trim_qts_deep((array)$attr); // Force array; trim quote entities.

			$attr = shortcode_atts(array('download'           => '', 'download_key' => '',
			                             'stream'             => '', 'inline' => '', 'storage' => '',
			                             'remote'             => '', 'ssl' => '', 'rewrite' => '', 'rewrite_base' => '',
			                             'skip_confirmation'  => '', 'url_to_storage_source' => '',
			                             'count_against_user' => '', 'check_user' => '',
			                             'get_streamer_json'  => '', 'get_streamer_array' => ''), $attr);

			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_before_sc_get_file_after_shortcode_atts', get_defined_vars());
			unset($__refs, $__v); // Housekeeping.

			$get_streamer_json  = filter_var($attr['get_streamer_json'], FILTER_VALIDATE_BOOLEAN);
			$get_streamer_array = filter_var($attr['get_streamer_array'], FILTER_VALIDATE_BOOLEAN);
			$get_streamer_json  = $get_streamer_array = ($get_streamer_array || $get_streamer_json) ? TRUE : FALSE;

			foreach($attr as $key => $value) // Now we need to go through and a `file_` prefix  to certain Attribute keys, for compatibility.
				if(strlen($value) && in_array($key, array('download', 'download_key', 'stream', 'inline', 'storage', 'remote', 'ssl', 'rewrite', 'rewrite_base')))
					$config['file_'.$key] = $value; // Set prefixed config parameter here so we can pass properly in ``$config`` array.
				else if(strlen($value) && !in_array($key, array('get_streamer_json', 'get_streamer_array')))
					$config[$key] = $value;

			unset($key, $value); // We don't want these bleeding into Hooks/Filters anyway.

			if(!empty($config) && isset($config['file_download'])) // Looking for a File Download URL?
			{
				$_get = c_ws_plugin__s2member_files::create_file_download_url($config, $get_streamer_array);

				if($get_streamer_array && $get_streamer_json && is_array($_get))
					$get = json_encode($_get);

				else if($get_streamer_array && $get_streamer_json)
					$get = 'null'; // Null object value.

				else if(!empty($_get))
					$get = $_get;
			}
			return apply_filters('ws_plugin__s2member_sc_get_file', isset($get) ? $get : NULL, get_defined_vars());
		}

		/**
		 * Handles the Shortcode for: `[s2Stream /]`.
		 *
		 * @package s2Member\s2File
		 * @since 130119
		 *
		 * @attaches-to ``add_shortcode('s2Stream');``
		 *
		 * @param array  $attr An array of Attributes.
		 * @param string $content Content inside the Shortcode.
		 * @param string $shortcode The actual Shortcode name itself.
		 *
		 * @return string HTML markup that produces an audio/video stream for a specific player.
		 */
		public static function sc_get_stream($attr = array(), $content = '', $shortcode = '')
		{
			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_before_sc_get_stream', get_defined_vars());
			unset($__refs, $__v); // Housekeeping.

			$attr = c_ws_plugin__s2member_utils_strings::trim_qts_deep((array)$attr);

			$attr = shortcode_atts(array('download'             => '', 'file_download' => '', 'download_key' => '',
			                             'stream'               => 'yes', 'inline' => 'yes', 'storage' => '',
			                             'remote'               => '', 'ssl' => '', 'rewrite' => 'yes', 'rewrite_base' => '',
			                             'skip_confirmation'    => '', 'url_to_storage_source' => 'yes',
			                             'count_against_user'   => 'yes', 'check_user' => 'yes',

			                             // Configuration
			                             'player'               => 'jwplayer-v6-rtmp', 'player_id' => 's2-stream-'.md5(uniqid('', TRUE)),
			                             'player_path'          => '/jwplayer/jwplayer.js', 'player_key' => '', 'player_title' => '',
			                             'player_image'         => '', 'player_mediaid' => '', 'player_description' => '', 'player_captions' => '',
			                             'player_resolutions'   => '', // A comma-delimited list of resolution options.

			                             // Layout
			                             'player_controls'      => 'yes', 'player_skin' => '', 'player_stretching' => 'uniform',
			                             'player_width'         => '480', 'player_height' => '270', 'player_aspectratio' => '',

			                             // Playback
			                             'player_autostart'     => 'no', 'player_fallback' => 'yes', 'player_mute' => 'no',
			                             'player_primary'       => (($attr['player'] === 'jw-player-v6') ? 'html5' : 'flash'),
			                             'player_repeat'        => 'no', 'player_startparam' => '',

			                             // Advanced Option Blocks
			                             'player_option_blocks' => ''), $attr);

			$attr['download'] = (!empty($attr['file_download'])) ? $attr['file_download'] : $attr['download'];

			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_before_sc_get_stream_after_shortcode_atts', get_defined_vars());
			unset($__refs, $__v); // Housekeeping.

			foreach($attr as $key => $value) // Now we need to go through and a `file_` prefix  to certain Attribute keys, for compatibility.
				if(strlen($value) && in_array($key, array('download', 'download_key', 'stream', 'inline', 'storage', 'remote', 'ssl', 'rewrite', 'rewrite_base')))
					$config['file_'.$key] = $value; // Set prefixed config parameter here so we can pass properly in ``$config`` array.
				else if(strlen($value) && !in_array($key, array('file_download', 'player')) && strpos($key, 'player_') !== 0)
					$config[$key] = $value;

			unset($key, $value); // Ditch these now. We don't want these bleeding into Hooks/Filters anyway.

			if(!empty($config) && isset($config['file_download'])) // Looking for a File Download URL?
			{
				if($attr['player_resolutions']) // There are multiple variations of the file; in various resolutions?
					foreach(preg_split('/[,;\s]+/', $attr['player_resolutions'], NULL, PREG_SPLIT_NO_EMPTY) as $_resolution)
					{
						// @TODO Need to finish this up.
					}
				$_get = c_ws_plugin__s2member_files::create_file_download_url($config, TRUE);

				if(is_array($_get) && !empty($_get) && $attr['player'] && is_file($template = dirname(dirname(__FILE__)).'/templates/players/'.$attr['player'].'.php') && $attr['player_id'] && $attr['player_path'])
				{
					$template = (is_file(TEMPLATEPATH.'/'.basename($template))) ? TEMPLATEPATH.'/'.basename($template) : $template;
					$template = (is_file(get_stylesheet_directory().'/'.basename($template))) ? get_stylesheet_directory().'/'.basename($template) : $template;
					$template = (is_file(WP_CONTENT_DIR.'/'.basename($template))) ? WP_CONTENT_DIR.'/'.basename($template) : $template;

					if(strpos($attr['player'], 'jwplayer-v6') === 0)
					{
						$get = trim(c_ws_plugin__s2member_utilities::evl(file_get_contents($template)));

						$get = preg_replace('/%%streamer%%/', $_get['streamer'], $get);
						$get = preg_replace('/%%prefix%%/', $_get['prefix'], $get);
						$get = preg_replace('/%%file%%/', $_get['file'], $get);
						$get = preg_replace('/%%url%%/', $_get['url'], $get);

						$get = preg_replace('/%%player_id%%/', $attr['player_id'], $get);
						$get = preg_replace('/%%player_path%%/', $attr['player_path'], $get);
						$get = preg_replace('/%%player_key%%/', $attr['player_key'], $get);

						$get = preg_replace('/%%player_title%%/', $attr['player_title'], $get);
						$get = preg_replace('/%%player_image%%/', $attr['player_image'], $get);

						$get = preg_replace('/%%player_mediaid%%/', $attr['player_mediaid'], $get);
						$get = preg_replace('/%%player_description%%/', $attr['player_description'], $get);

						if(($attr['player_captions'] = c_ws_plugin__s2member_utils_strings::trim($attr['player_captions'], NULL, '[]')))
							$get = preg_replace('/%%player_captions%%/', '['.((strpos($attr['player_captions'], ':') !== FALSE) ? $attr['player_captions'] : base64_decode($attr['player_captions'])).']', $get);
						else $get = preg_replace('/%%player_captions%%/', '[]', $get);

						$get = preg_replace('/%%player_controls%%/', ((filter_var($attr['player_controls'], FILTER_VALIDATE_BOOLEAN)) ? 'true' : 'false'), $get);
						$get = preg_replace('/%%player_width%%/', ((strpos($attr['player_width'], '%') !== FALSE) ? "'".$attr['player_width']."'" : (integer)$attr['player_width']), $get);
						$get = preg_replace('/%%player_height%%/', (($attr['player_aspectratio']) ? "''" : ((strpos($attr['player_height'], '%') !== FALSE) ? "'".$attr['player_height']."'" : (integer)$attr['player_height'])), $get);
						$get = preg_replace('/%%player_aspectratio%%/', $attr['player_aspectratio'], $get);
						$get = preg_replace('/%%player_stretching%%/', $attr['player_stretching'], $get);
						$get = preg_replace('/%%player_skin%%/', $attr['player_skin'], $get);

						$get = preg_replace('/%%player_autostart%%/', ((filter_var($attr['player_autostart'], FILTER_VALIDATE_BOOLEAN)) ? 'true' : 'false'), $get);
						$get = preg_replace('/%%player_fallback%%/', ((filter_var($attr['player_fallback'], FILTER_VALIDATE_BOOLEAN)) ? 'true' : 'false'), $get);
						$get = preg_replace('/%%player_mute%%/', ((filter_var($attr['player_mute'], FILTER_VALIDATE_BOOLEAN)) ? 'true' : 'false'), $get);
						$get = preg_replace('/%%player_repeat%%/', ((filter_var($attr['player_repeat'], FILTER_VALIDATE_BOOLEAN)) ? 'true' : 'false'), $get);
						$get = preg_replace('/%%player_startparam%%/', $attr['player_startparam'], $get);
						$get = preg_replace('/%%player_primary%%/', $attr['player_primary'], $get);

						$get = preg_replace('/%%player_option_blocks%%/', ((strpos($attr['player_option_blocks'], ':') !== FALSE) ? $attr['player_option_blocks'] : base64_decode($attr['player_option_blocks'])), $get);
					}
				}
			}
			return apply_filters('ws_plugin__s2member_sc_get_stream', isset($get) ? $get : NULL, get_defined_vars());
		}
	}
}