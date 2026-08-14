<?php
// @codingStandardsIgnoreFile
/**
 * Shortcode `[s2File /]` (inner processing routines).
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
 * @package s2Member\s2File
 * @since 110926
 */
if(!defined('WPINC')) // MUST have WordPress.
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

			//260811 Validate download key.
			if(!in_array($attr['download_key'], array('ip-forever', 'universal'), true))
				$attr['download_key'] = filter_var($attr['download_key'], FILTER_VALIDATE_BOOLEAN) ? 'yes' : '';

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
			                             'player'               => 'jwplayer-v7-rtmp', 'player_id' => 's2-stream-'.md5(uniqid('', TRUE)),
			                             'player_path'          => '/jwplayer/jwplayer.js', 'player_key' => '', 'player_title' => '',
			                             'player_image'         => '', 'player_mediaid' => '', 'player_description' => '', 'player_captions' => '', 'player_tracks' => '',
			                             'player_resolutions'   => '', // A comma-delimited list of resolution options.

			                             // Layout
			                             'player_controls'      => 'yes', 'player_skin' => '', 'player_stretching' => 'uniform',
			                             'player_width'         => '480', 'player_height' => '270', 'player_aspectratio' => '',

			                             // Playback
			                             'player_autostart'     => 'no', 'player_fallback' => 'yes', 'player_mute' => 'no',
			                             'player_primary'       => (($attr['player'] === 'jw-player-v7' || $attr['player'] === 'jw-player-v6') ? 'html5' : 'flash'),
			                             'player_repeat'        => 'no', 'player_startparam' => '', // `startparam` seems to be JW Player v6 only.

			                             // Advanced Option Blocks
			                             'player_option_blocks' => ''), $attr);

			$attr['download'] = (!empty($attr['file_download'])) ? $attr['file_download'] : $attr['download'];

			//260811 Validate download key.
			if(!in_array($attr['download_key'], array('ip-forever', 'universal'), true))
				$attr['download_key'] = filter_var($attr['download_key'], FILTER_VALIDATE_BOOLEAN) ? 'yes' : '';

			foreach(array_keys(get_defined_vars()) as $__v) $__refs[$__v] =& $$__v;
			do_action('ws_plugin__s2member_before_sc_get_stream_after_shortcode_atts', get_defined_vars());
			unset($__refs, $__v); // Housekeeping.

			//260805 Validate the final player configuration after shortcode hooks, before it affects paths or generated markup.
			$player_templates = array('jwplayer-v6', 'jwplayer-v6-rtmp', 'jwplayer-v6-rtmp-only', 'jwplayer-v7', 'jwplayer-v7-rtmp', 'jwplayer-v7-rtmp-only');
			if(!in_array($attr['player'], $player_templates, TRUE))
				$attr['player'] = 'jwplayer-v7-rtmp';

			//260805 Shortcode content may select only an exact player script path allowlisted through trusted PHP.
			$player_paths = array_map('strval', (array)apply_filters('ws_plugin__s2member_sc_get_stream_player_paths', array('/jwplayer/jwplayer.js'), $attr));
			$player_paths = array_values(array_unique(array_filter(array_map('trim', $player_paths), 'strlen')));
			if(!$player_paths)
				$player_paths = array('/jwplayer/jwplayer.js');
			if(!in_array((string)$attr['player_path'], $player_paths, TRUE))
				$attr['player_path'] = $player_paths[0];

			//260805 Preserve supported enums and numeric formats; invalid values fall back instead of entering JavaScript syntax.
			$attr['player_primary'] = strtolower(trim((string)$attr['player_primary']));
			if(!in_array($attr['player_primary'], array('html5', 'flash'), TRUE))
				$attr['player_primary'] = 'flash';
			$attr['player_stretching'] = strtolower(trim((string)$attr['player_stretching']));
			if(!in_array($attr['player_stretching'], array('uniform', 'exactfit', 'fill', 'none'), TRUE))
				$attr['player_stretching'] = 'uniform';
			//260805 Keep JW Player v6 query parameter names as strings; complete output encoding prevents JavaScript-string breakout.
			$attr['player_startparam'] = trim((string)$attr['player_startparam']);
			if(!preg_match('/^(?:[0-9]+|[0-9]+(?:\.[0-9]+)?%)$/D', (string)$attr['player_width']))
				$attr['player_width'] = '480';
			if(!preg_match('/^(?:[0-9]+|[0-9]+(?:\.[0-9]+)?%)$/D', (string)$attr['player_height']))
				$attr['player_height'] = '270';
			if($attr['player_aspectratio'] && (!preg_match('/^([0-9]+):([0-9]+)$/D', (string)$attr['player_aspectratio'], $_player_aspectratio) || !(integer)$_player_aspectratio[1] || !(integer)$_player_aspectratio[2]))
				$attr['player_aspectratio'] = '';
			unset($_player_aspectratio); //260805 Housekeeping.

			//260805 Resolution tokens become filename suffixes and labels, so discard tokens outside the supported token format.
			$_player_resolutions = array();
			foreach(preg_split('/[,;\s]+/', (string)$attr['player_resolutions'], -1, PREG_SPLIT_NO_EMPTY) as $_player_resolution)
			{
				$_player_resolution = ltrim(trim($_player_resolution), 'Rr');
				if($_player_resolution !== '' && preg_match('/^[A-Za-z0-9][A-Za-z0-9_-]*$/D', $_player_resolution))
					$_player_resolutions[] = $_player_resolution;
			}
			$attr['player_resolutions'] = implode(',', $_player_resolutions);
			unset($_player_resolutions, $_player_resolution); //260805 Housekeeping.

			//260805 Encode complete JavaScript string literals once; templates receive generated literals instead of shortcode text.
			$player_json_strings = array();
			foreach(array('player_id', 'player_key', 'player_title', 'player_image', 'player_mediaid', 'player_description', 'player_skin', 'player_aspectratio', 'player_stretching', 'player_primary', 'player_startparam') as $_player_json_string_key)
			{
				$_player_json_string = wp_json_encode((string)$attr[$_player_json_string_key], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
				$player_json_strings[$_player_json_string_key] = is_string($_player_json_string) ? $_player_json_string : '""';
			}
			unset($_player_json_string_key, $_player_json_string); //260805 Housekeeping.

			foreach($attr as $key => $value) // Now we need to go through and a `file_` prefix  to certain Attribute keys, for compatibility.
				if(strlen($value) && in_array($key, array('download', 'download_key', 'stream', 'inline', 'storage', 'remote', 'ssl', 'rewrite', 'rewrite_base')))
					$config['file_'.$key] = $value; // Set prefixed config parameter here so we can pass properly in ``$config`` array.
				else if(strlen($value) && !in_array($key, array('file_download', 'player')) && strpos($key, 'player_') !== 0)
					$config[$key] = $value;

			unset($key, $value); // Ditch these now. We don't want these bleeding into Hooks/Filters anyway.

			if(!empty($config) && isset($config['file_download'])) // Looking for a File Download URL?
			{
				if($attr['player_resolutions'] && c_ws_plugin__s2member_utils_conds::pro_is_installed() /* Pro serves SMIL files. */)
				{
					$file_download_extension               = strtolower(ltrim((string)strrchr(basename($config['file_download']), '.'), '.'));
					$file_download_resolution_wo_extension = substr($config['file_download'], 0, -(strlen($file_download_extension) + 1) /* For the dot. */);
					$file_download_wo_resolution_extension = preg_replace('/\-r[0-9]+([^.]*)$/i', '', $file_download_resolution_wo_extension); // e.g., `r720p-HD` is removed here.

					$file_download_resolutions = array(); // Initialize the array of resolutions.
					foreach(preg_split('/[,;\s]+/', $attr['player_resolutions'], -1, PREG_SPLIT_NO_EMPTY) as $_player_resolution)
					{
						$_player_resolution                             = ltrim($_player_resolution, 'Rr'); // Remove R|r prefix.
						$file_download_resolutions[$_player_resolution] = $file_download_wo_resolution_extension.'-r'.$_player_resolution.'.'.$file_download_extension;
					}
					unset($_player_resolution); // Housekeeping.

					$file_download_urls = array(); // Initialize array of all file download urls.
					foreach($file_download_resolutions as $_player_resolution => $_file_download_resolution) // NOTE: these ARE in a specific order.
					{
						$_file_download_config = array_merge($config, array('file_download' => $_file_download_resolution));

						if($file_download_urls) // If this is a ANOTHER resolution, don't count it against the user.
							$_file_download_config = array_merge($_file_download_config, array('check_user' => FALSE, 'count_against_user' => FALSE));

						if(!($file_download_urls[str_replace(array('_', '-'), ' ', $_player_resolution)] = c_ws_plugin__s2member_files::create_file_download_url($_file_download_config, TRUE)))
							return apply_filters('ws_plugin__s2member_sc_get_stream', NULL, get_defined_vars()); // Failure.
					}
					unset($_player_resolution, $_file_download_resolution, $_file_download_config); // Housekeeping.
				}
				else $file_download_urls = array(c_ws_plugin__s2member_files::create_file_download_url($config, TRUE)); // Default behavior.

				if($file_download_urls && $attr['player'] && is_file($template = dirname(dirname(__FILE__)).'/templates/players/'.$attr['player'].'.php') && $attr['player_id'] && $attr['player_path'])
				{
					$template = (is_file(TEMPLATEPATH.'/'.basename($template))) ? TEMPLATEPATH.'/'.basename($template) : $template;
					$template = (is_file(get_stylesheet_directory().'/'.basename($template))) ? get_stylesheet_directory().'/'.basename($template) : $template;
					$template = (is_file(WP_CONTENT_DIR.'/'.basename($template))) ? WP_CONTENT_DIR.'/'.basename($template) : $template;

					if(strpos($attr['player'], 'jwplayer-v7') === 0) // JW Player (new v7).
					{
						$player = trim(c_ws_plugin__s2member_utilities::evl(file_get_contents($template)));

						$_first_file_download_url = array(); // Holds the first one.
						$_last_file_download_url  = array(); // Holds the last one.
						$_uses_rtmp_streamers     = FALSE; // Streamers use RTMP?

						$_total_player_sources   = count($file_download_urls); // Total sources.
						$_player_sources_counter = 1; // Player sources counter; needed by the loop below.

						$player_resolution_aspect_ratio_w = 16; // Default aspect ratio width.
						$player_resolution_aspect_ratio_h = 9; // Default aspect ratio in height.
						if($attr['player_aspectratio'] && preg_match('/^[0-9]+\:[0-9]+$/', $attr['player_aspectratio']))
							list($player_resolution_aspect_ratio_w, $player_resolution_aspect_ratio_h) = explode(':', $attr['player_aspectratio']);
						$player_resolution_aspect_ratio_w = (integer)$player_resolution_aspect_ratio_w; // Force integer value.
						$player_resolution_aspect_ratio_h = (integer)$player_resolution_aspect_ratio_h; // Force integer value.

						// See: <http://wsharks.com/1yzjAl6> and <http://wsharks.com/1yzkhea> regarging the SMIL bitrate hints given here.
						$player_resolution_bitrates = array(2160 => '35000000', 1440 => '10000000', 1080 => '8000000', 720 => '5000000', 640 => '2500001', 480 => '2500000', 360 => '1000000', 320 => '999999', 240 => '500000', 180 => '300000');
						$player_resolution_bitrates = apply_filters('ws_plugin__s2member_sc_get_stream_resolution_bitrates', $player_resolution_bitrates, get_defined_vars());

						$player_resolution_sources_smil_file_id       = md5(serialize($attr).c_ws_plugin__s2member_utils_ip::current()); // Initialize SMIL ID.
						$player_resolution_sources_smil_file_url      = home_url('/s2member-rsf-file.smil?s2member_rsf_file='.urlencode($player_resolution_sources_smil_file_id).'&s2member_rsf_file_ip='.urlencode(c_ws_plugin__s2member_utils_ip::current()));
						$player_resolution_sources_smil_file_url      = c_ws_plugin__s2member_utils_urls::add_s2member_sig($player_resolution_sources_smil_file_url);
						$player_resolution_sources_smil_file_contents = ''; // Initialize player sources SMIL file contents.
						$player_sources                               = array(); //260805 Build source configuration as PHP data before JSON serialization.

						foreach($file_download_urls as $_file_download_url_label => $_file_download_url)
						{
							$_is_first_file_download_url = $_player_sources_counter <= 1;
							$_is_last_file_download_url  = $_player_sources_counter >= $_total_player_sources;

							if($_is_first_file_download_url) // We base this conditional on the first streamer.
								$_uses_rtmp_streamers = stripos($_file_download_url['streamer'], 'rtmp') === 0;

							switch($attr['player'])// See: <http://wsharks.com/1Bd6tKy>
							{
								case 'jwplayer-v7': // New JW Player v7 (very simple).

									//260805 Store source fields as data so the JSON encoder controls all JavaScript syntax.
									$_player_source = array('file' => $_file_download_url['url']);
									if(is_string($_file_download_url_label)) $_player_source['label'] = $_file_download_url_label;
									if($_is_first_file_download_url) $_player_source['default'] = 'true';
									$player_sources[] = $_player_source;

									break; // Break switch loop.

								case 'jwplayer-v7-rtmp': // RTMP w/ downloadable fallback (mobile compatibility).
								case 'jwplayer-v7-rtmp-only': // RTMP streaming only (flash player only).

									if($attr['player_resolutions'] && $_total_player_sources > 1 && $_uses_rtmp_streamers)
									{
										if($_is_first_file_download_url) // The first source is the SMIL file.
										{
											//260805 The generated SMIL URL is serialized as data with the other player sources.
											$_player_source = array('file' => $player_resolution_sources_smil_file_url);
											if($_is_first_file_download_url) $_player_source['default'] = 'true';
											$player_sources[] = $_player_source;
										}
										$_file_download_url['smil']['height'] = (integer)$_file_download_url_label; // e.g., `720p-HD` becomes `720`.
										if(!$_file_download_url['smil']['height']) $_file_download_url['smil']['height'] = 720; // Use a default height if invalid.
										$_file_download_url['smil']['width'] = ceil(($_file_download_url['smil']['height'] / $player_resolution_aspect_ratio_h) * $player_resolution_aspect_ratio_w);

										$_file_download_url['smil']['system-bitrate'] = '1'; // Default value.
										if(!empty($player_resolution_bitrates[$_file_download_url['smil']['height']]))
											$_file_download_url['smil']['system-bitrate'] = $player_resolution_bitrates[$_file_download_url['smil']['height']];

										$player_resolution_sources_smil_file_contents .= '<video src="'.esc_attr($_file_download_url['file']).'"'.
										                                                 ' width="'.esc_attr($_file_download_url['smil']['width']).'"'.
										                                                 ' height="'.esc_attr($_file_download_url['smil']['height']).'"'.
										                                                 ' system-bitrate="'.esc_attr($_file_download_url['smil']['system-bitrate']).'" />';
									}
									else // Build them inline; i.e., don't create a SMIL file in this case; not necessary.
									{
										//260805 Store RTMP source fields as data before serialization.
										$_player_source = array('file' => $_file_download_url['streamer'].'/'.$_file_download_url['prefix'].$_file_download_url['file']);
										if(is_string($_file_download_url_label)) $_player_source['label'] = $_file_download_url_label;
										if($_is_first_file_download_url) $_player_source['default'] = 'true';
										$player_sources[] = $_player_source;
									}
									if($_is_last_file_download_url && $attr['player'] === 'jwplayer-v7-rtmp') // Provide a fallback also.
									{
										//260805 Store the downloadable fallback as data before serialization.
										$player_sources[] = array('file' => $_file_download_url['url']);
									}
									break; // Break switch loop.
							}
							if($_is_first_file_download_url) // Record first one; also run back compat. replacements.
							{
								$_first_file_download_url = $_file_download_url; // Record for use later.
								//260805 Use literal replacement for legacy placeholders in trusted custom player templates.
								$player = str_replace(array('%%streamer%%', '%%prefix%%', '%%file%%', '%%url%%'), array($_file_download_url['streamer'], $_file_download_url['prefix'], $_file_download_url['file'], $_file_download_url['url']), $player);
							}
							if($_is_last_file_download_url) // Record last one; which could be the same as the first one.
							{
								$_last_file_download_url = $_file_download_url; // Record for use later.
							}
							$_player_sources_counter++; // Increment the counter.
						}
						//260805 Serialize the complete source list once, including HTML-safe escaping for the inline script context.
						$player_sources = wp_json_encode($player_sources, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
						if(!is_string($player_sources)) $player_sources = '[]';

						if($player_resolution_sources_smil_file_contents && $_first_file_download_url) // Build SMIL file.
						{
							$player_resolution_sources_smil_file_contents = '<smil>'. // See: <http://wsharks.com/1ruqGVu>
							                                                ' <head><meta base="'.esc_attr($_first_file_download_url['streamer']).'" /></head>'.
							                                                ' <body><switch>'.$player_resolution_sources_smil_file_contents.'</switch></body>'.
							                                                '</smil>';
							set_transient('s2m_rsf_'.$player_resolution_sources_smil_file_id, $player_resolution_sources_smil_file_contents, 86400);
						}
						unset($_first_file_download_url, $_last_file_download_url, $_uses_rtmp_streamers, // Housekeeping.
							$_total_player_sources, $_player_sources_counter, $_is_first_file_download_url, $_is_last_file_download_url,
							$_file_download_url_label, $_file_download_url, $_player_source);

						//260805 Parse flexible attributes as data and substitute only values encoded for their exact output contexts.
						$_player_tracks        = self::sc_get_stream_json_data($attr['player_tracks'], 'array');
						$_player_option_blocks = self::sc_get_stream_json_data($attr['player_option_blocks'], 'object-properties');
						$_player_width        = (strpos($attr['player_width'], '%') !== FALSE) ? wp_json_encode((string)$attr['player_width']) : (string)(integer)$attr['player_width'];
						$_player_height       = $attr['player_aspectratio'] ? '""' : ((strpos($attr['player_height'], '%') !== FALSE) ? wp_json_encode((string)$attr['player_height']) : (string)(integer)$attr['player_height']);
						if(!is_string($_player_width)) $_player_width = '480';
						if(!is_string($_player_height)) $_player_height = '270';

						//260805 strtr() replaces literal placeholders without reprocessing placeholder-like text inside generated values.
						$player = strtr($player, array(
							"'%%player_id%%'"          => $player_json_strings['player_id'],
							'%%player_id%%'            => esc_attr($attr['player_id']),
							'%%player_path%%'          => esc_url($attr['player_path']),
							"'%%player_key%%'"         => $player_json_strings['player_key'],
							"'%%player_title%%'"       => $player_json_strings['player_title'],
							"'%%player_image%%'"       => $player_json_strings['player_image'],
							"'%%player_mediaid%%'"     => $player_json_strings['player_mediaid'],
							"'%%player_description%%'" => $player_json_strings['player_description'],
							'%%player_tracks%%'        => $_player_tracks,
							'%%player_sources%%'       => $player_sources,
							'%%player_controls%%'      => filter_var($attr['player_controls'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false',
							'%%player_width%%'         => $_player_width,
							'%%player_height%%'        => $_player_height,
							"'%%player_aspectratio%%'" => $player_json_strings['player_aspectratio'],
							"'%%player_stretching%%'"  => $player_json_strings['player_stretching'],
							'%%player_autostart%%'     => filter_var($attr['player_autostart'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false',
							'%%player_fallback%%'      => filter_var($attr['player_fallback'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false',
							'%%player_mute%%'          => filter_var($attr['player_mute'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false',
							'%%player_repeat%%'        => filter_var($attr['player_repeat'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false',
							"'%%player_primary%%'"     => $player_json_strings['player_primary'],
							'%%player_option_blocks%%' => $_player_option_blocks,
						));
						unset($_player_tracks, $_player_option_blocks, $_player_width, $_player_height); //260805 Housekeeping.
					}
					else if(strpos($attr['player'], 'jwplayer-v6') === 0) // JW Player (old v6).
					{
						$player = trim(c_ws_plugin__s2member_utilities::evl(file_get_contents($template)));

						$_first_file_download_url = array(); // Holds the first one.
						$_last_file_download_url  = array(); // Holds the last one.
						$_uses_rtmp_streamers     = FALSE; // Streamers use RTMP?

						$_total_player_sources   = count($file_download_urls); // Total sources.
						$_player_sources_counter = 1; // Player sources counter; needed by the loop below.

						$player_resolution_aspect_ratio_w = 16; // Default aspect ratio width.
						$player_resolution_aspect_ratio_h = 9; // Default aspect ratio in height.
						if($attr['player_aspectratio'] && preg_match('/^[0-9]+\:[0-9]+$/', $attr['player_aspectratio']))
							list($player_resolution_aspect_ratio_w, $player_resolution_aspect_ratio_h) = explode(':', $attr['player_aspectratio']);
						$player_resolution_aspect_ratio_w = (integer)$player_resolution_aspect_ratio_w; // Force integer value.
						$player_resolution_aspect_ratio_h = (integer)$player_resolution_aspect_ratio_h; // Force integer value.

						// See: <http://wsharks.com/1yzjAl6> and <http://wsharks.com/1yzkhea> regarging the SMIL bitrate hints given here.
						$player_resolution_bitrates = array(2160 => '35000000', 1440 => '10000000', 1080 => '8000000', 720 => '5000000', 640 => '2500001', 480 => '2500000', 360 => '1000000', 320 => '999999', 240 => '500000', 180 => '300000');
						$player_resolution_bitrates = apply_filters('ws_plugin__s2member_sc_get_stream_resolution_bitrates', $player_resolution_bitrates, get_defined_vars());

						$player_resolution_sources_smil_file_id       = md5(serialize($attr).c_ws_plugin__s2member_utils_ip::current()); // Initialize SMIL ID.
						$player_resolution_sources_smil_file_url      = home_url('/s2member-rsf-file.smil?s2member_rsf_file='.urlencode($player_resolution_sources_smil_file_id).'&s2member_rsf_file_ip='.urlencode(c_ws_plugin__s2member_utils_ip::current()));
						$player_resolution_sources_smil_file_url      = c_ws_plugin__s2member_utils_urls::add_s2member_sig($player_resolution_sources_smil_file_url);
						$player_resolution_sources_smil_file_contents = ''; // Initialize player sources SMIL file contents.
						$player_sources                               = array(); //260805 Build source configuration as PHP data before JSON serialization.

						foreach($file_download_urls as $_file_download_url_label => $_file_download_url)
						{
							$_is_first_file_download_url = $_player_sources_counter <= 1;
							$_is_last_file_download_url  = $_player_sources_counter >= $_total_player_sources;

							if($_is_first_file_download_url) // We base this conditional on the first streamer.
								$_uses_rtmp_streamers = stripos($_file_download_url['streamer'], 'rtmp') === 0;

							switch($attr['player'])// See: <http://wsharks.com/1Bd6tKy>
							{
								case 'jwplayer-v6': // Default w/ a direct URL (very simple).

									//260805 Store source fields as data so the JSON encoder controls all JavaScript syntax.
									$_player_source = array('file' => $_file_download_url['url']);
									if(is_string($_file_download_url_label)) $_player_source['label'] = $_file_download_url_label;
									if($_is_first_file_download_url) $_player_source['default'] = 'true';
									$player_sources[] = $_player_source;

									break; // Break switch loop.

								case 'jwplayer-v6-rtmp': // RTMP w/ downloadable fallback (mobile compatibility).
								case 'jwplayer-v6-rtmp-only': // RTMP streaming only (flash player only).

									if($attr['player_resolutions'] && $_total_player_sources > 1 && $_uses_rtmp_streamers)
									{
										if($_is_first_file_download_url) // The first source is the SMIL file.
										{
											//260805 The generated SMIL URL is serialized as data with the other player sources.
											$_player_source = array('file' => $player_resolution_sources_smil_file_url);
											if($_is_first_file_download_url) $_player_source['default'] = 'true';
											$player_sources[] = $_player_source;
										}
										$_file_download_url['smil']['height'] = (integer)$_file_download_url_label; // e.g., `720p-HD` becomes `720`.
										if(!$_file_download_url['smil']['height']) $_file_download_url['smil']['height'] = 720; // Use a default height if invalid.
										$_file_download_url['smil']['width'] = ceil(($_file_download_url['smil']['height'] / $player_resolution_aspect_ratio_h) * $player_resolution_aspect_ratio_w);

										$_file_download_url['smil']['system-bitrate'] = '1'; // Default value.
										if(!empty($player_resolution_bitrates[$_file_download_url['smil']['height']]))
											$_file_download_url['smil']['system-bitrate'] = $player_resolution_bitrates[$_file_download_url['smil']['height']];

										$player_resolution_sources_smil_file_contents .= '<video src="'.esc_attr($_file_download_url['file']).'"'.
										                                                 ' width="'.esc_attr($_file_download_url['smil']['width']).'"'.
										                                                 ' height="'.esc_attr($_file_download_url['smil']['height']).'"'.
										                                                 ' system-bitrate="'.esc_attr($_file_download_url['smil']['system-bitrate']).'" />';
									}
									else // Build them inline; i.e., don't create a SMIL file in this case; not necessary.
									{
										//260805 Store RTMP source fields as data before serialization.
										$_player_source = array('file' => $_file_download_url['streamer'].'/'.$_file_download_url['prefix'].$_file_download_url['file']);
										if(is_string($_file_download_url_label)) $_player_source['label'] = $_file_download_url_label;
										if($_is_first_file_download_url) $_player_source['default'] = 'true';
										$player_sources[] = $_player_source;
									}
									if($_is_last_file_download_url && $attr['player'] === 'jwplayer-v6-rtmp') // Provide a fallback also.
									{
										//260805 Store the downloadable fallback as data before serialization.
										$player_sources[] = array('file' => $_file_download_url['url']);
									}
									break; // Break switch loop.
							}
							if($_is_first_file_download_url) // Record first one; also run back compat. replacements.
							{
								$_first_file_download_url = $_file_download_url; // Record for use later.
								//260805 Use literal replacement for legacy placeholders in trusted custom player templates.
								$player = str_replace(array('%%streamer%%', '%%prefix%%', '%%file%%', '%%url%%'), array($_file_download_url['streamer'], $_file_download_url['prefix'], $_file_download_url['file'], $_file_download_url['url']), $player);
							}
							if($_is_last_file_download_url) // Record last one; which could be the same as the first one.
							{
								$_last_file_download_url = $_file_download_url; // Record for use later.
							}
							$_player_sources_counter++; // Increment the counter.
						}
						//260805 Serialize the complete source list once, including HTML-safe escaping for the inline script context.
						$player_sources = wp_json_encode($player_sources, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
						if(!is_string($player_sources)) $player_sources = '[]';

						if($player_resolution_sources_smil_file_contents && $_first_file_download_url) // Build SMIL file.
						{
							$player_resolution_sources_smil_file_contents = '<smil>'. // See: <http://wsharks.com/1ruqGVu>
							                                                ' <head><meta base="'.esc_attr($_first_file_download_url['streamer']).'" /></head>'.
							                                                ' <body><switch>'.$player_resolution_sources_smil_file_contents.'</switch></body>'.
							                                                '</smil>';
							set_transient('s2m_rsf_'.$player_resolution_sources_smil_file_id, $player_resolution_sources_smil_file_contents, 86400);
						}
						unset($_first_file_download_url, $_last_file_download_url, $_uses_rtmp_streamers, // Housekeeping.
							$_total_player_sources, $_player_sources_counter, $_is_first_file_download_url, $_is_last_file_download_url,
							$_file_download_url_label, $_file_download_url, $_player_source);

						//260805 Parse flexible attributes as data and substitute only values encoded for their exact output contexts.
						$_player_captions      = self::sc_get_stream_json_data($attr['player_captions'], 'array');
						$_player_option_blocks = self::sc_get_stream_json_data($attr['player_option_blocks'], 'object-properties');
						$_player_width        = (strpos($attr['player_width'], '%') !== FALSE) ? wp_json_encode((string)$attr['player_width']) : (string)(integer)$attr['player_width'];
						$_player_height       = $attr['player_aspectratio'] ? '""' : ((strpos($attr['player_height'], '%') !== FALSE) ? wp_json_encode((string)$attr['player_height']) : (string)(integer)$attr['player_height']);
						if(!is_string($_player_width)) $_player_width = '480';
						if(!is_string($_player_height)) $_player_height = '270';

						//260805 strtr() replaces literal placeholders without reprocessing placeholder-like text inside generated values.
						$player = strtr($player, array(
							"'%%player_id%%'"          => $player_json_strings['player_id'],
							'%%player_id%%'            => esc_attr($attr['player_id']),
							'%%player_path%%'          => esc_url($attr['player_path']),
							"'%%player_key%%'"         => $player_json_strings['player_key'],
							"'%%player_title%%'"       => $player_json_strings['player_title'],
							"'%%player_image%%'"       => $player_json_strings['player_image'],
							"'%%player_mediaid%%'"     => $player_json_strings['player_mediaid'],
							"'%%player_description%%'" => $player_json_strings['player_description'],
							'%%player_captions%%'      => $_player_captions,
							'%%player_sources%%'       => $player_sources,
							'%%player_controls%%'      => filter_var($attr['player_controls'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false',
							"'%%player_skin%%'"        => $player_json_strings['player_skin'],
							"'%%player_stretching%%'"  => $player_json_strings['player_stretching'],
							'%%player_width%%'         => $_player_width,
							'%%player_height%%'        => $_player_height,
							"'%%player_aspectratio%%'" => $player_json_strings['player_aspectratio'],
							'%%player_autostart%%'     => filter_var($attr['player_autostart'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false',
							'%%player_fallback%%'      => filter_var($attr['player_fallback'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false',
							'%%player_mute%%'          => filter_var($attr['player_mute'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false',
							"'%%player_primary%%'"     => $player_json_strings['player_primary'],
							'%%player_repeat%%'        => filter_var($attr['player_repeat'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false',
							"'%%player_startparam%%'"  => $player_json_strings['player_startparam'],
							'%%player_option_blocks%%' => $_player_option_blocks,
						));
						unset($_player_captions, $_player_option_blocks, $_player_width, $_player_height); //260805 Housekeeping.
					}
				}
			}
			unset($player_json_strings, $player_templates, $player_paths); //260805 Housekeeping.
			return apply_filters('ws_plugin__s2member_sc_get_stream', isset($player) ? $player : NULL, get_defined_vars());
		}

		/**
		 * Parses a structured player attribute and returns safe JSON for the existing template placeholder.
		 *
		 * @package s2Member\s2File
		 * @since 260805
		 *
		 * @param mixed  $value Attribute value, optionally base64 encoded.
		 * @param string $container Expected top-level container: `array` or `object-properties`.
		 *
		 * @return string Safe JSON, or the appropriate empty value when invalid.
		 */
		protected static function sc_get_stream_json_data($value = '', $container = 'array')
		{
			$value = trim((string)$value);
			$empty = ($container === 'array') ? '[]' : '';
			if($value === '')
				return $empty;

			//260805 Try the documented plain-text form first, then a canonical strict-base64 form for backward compatibility.
			$candidates = array($value);
			$_base64    = preg_replace('/\s+/', '', $value);
			if($_base64 !== '' && preg_match('/^[A-Za-z0-9+\/]+={0,2}$/D', $_base64))
			{
				$_decoded = base64_decode($_base64, TRUE);
				if($_decoded !== FALSE && rtrim(base64_encode($_decoded), '=') === rtrim($_base64, '='))
					$candidates[] = trim($_decoded);
			}
			unset($_base64, $_decoded); //260805 Housekeeping.

			foreach($candidates as $_candidate)
			{
				//260805 Bound parser work and reject oversized shortcode configuration instead of attempting partial recovery.
				if($_candidate === '' || strlen($_candidate) > 65536)
					continue;

				$_candidate = trim($_candidate);
				if($container === 'array')
					$_input = (substr($_candidate, 0, 1) === '[') ? $_candidate : '['.$_candidate.']';
				else $_input = (substr($_candidate, 0, 1) === '{' && substr($_candidate, -1) === '}') ? $_candidate : '{'.$_candidate.'}';

				$_position = 0;
				$_parsed   = self::sc_parse_stream_data($_input, $_position);
				while(isset($_input[$_position]) && strpos(" \t\r\n\f\v", $_input[$_position]) !== FALSE)
					$_position++;
				if(!$_parsed[0] || $_position !== strlen($_input))
					continue;
				if($container === 'array' && !is_array($_parsed[1]))
					continue;
				if($container === 'object-properties' && !is_object($_parsed[1]))
					continue;

				if($container === 'object-properties')
				{
					//260805 These top-level JW Player settings can select or load executable player/plugin code and are not accepted from post content.
					foreach(array_keys(get_object_vars($_parsed[1])) as $_property)
						if(in_array(strtolower($_property), array('plugins', 'html5player', 'flashplayer', 'flashloader', 'modes', 'base'), TRUE))
							continue 2;
				}

				$_json = wp_json_encode($_parsed[1], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
				if(!is_string($_json))
					continue;

				//260805 Option blocks already sit inside setup object braces, so return only the safely generated object properties there.
				return ($container === 'array') ? $_json : substr($_json, 1, -1);
			}
			return $empty;
		}

		/**
		 * Parses the data-only subset of legacy JavaScript object notation used by player shortcode attributes.
		 *
		 * @package s2Member\s2File
		 * @since 260805
		 *
		 * @param string  $input Input being parsed.
		 * @param integer $position Current byte offset, passed by reference.
		 * @param integer $depth Current nesting depth.
		 *
		 * @return array A `(success, value)` pair.
		 */
		protected static function sc_parse_stream_data($input, &$position, $depth = 0)
		{
			$length = strlen($input);
			while($position < $length && strpos(" \t\r\n\f\v", $input[$position]) !== FALSE)
				$position++;
			if($position >= $length || $depth > 32)
				return array(FALSE, NULL);

			$character = $input[$position];
			if($character === '{')
			{
				$position++;
				$object = new stdClass();
				while(TRUE)
				{
					while($position < $length && strpos(" \t\r\n\f\v", $input[$position]) !== FALSE)
						$position++;
					if($position < $length && $input[$position] === '}')
					{
						$position++;
						return array(TRUE, $object);
					}

					if($position < $length && ($input[$position] === "'" || $input[$position] === '"'))
					{
						$_key = self::sc_parse_stream_data($input, $position, $depth);
						if(!$_key[0] || !is_string($_key[1]))
							return array(FALSE, NULL);
						$key = $_key[1];
					}
					else
					{
						if(!preg_match('/^[A-Za-z_$][A-Za-z0-9_$]*/', substr($input, $position), $_key))
							return array(FALSE, NULL);
						$key       = $_key[0];
						$position += strlen($key);
					}
					if(in_array(strtolower($key), array('__proto__', 'prototype', 'constructor'), TRUE))
						return array(FALSE, NULL);

					while($position < $length && strpos(" \t\r\n\f\v", $input[$position]) !== FALSE)
						$position++;
					if($position >= $length || $input[$position] !== ':')
						return array(FALSE, NULL);
					$position++;

					$_value = self::sc_parse_stream_data($input, $position, $depth + 1);
					if(!$_value[0])
						return array(FALSE, NULL);
					$object->{$key} = $_value[1];

					while($position < $length && strpos(" \t\r\n\f\v", $input[$position]) !== FALSE)
						$position++;
					if($position < $length && $input[$position] === ',')
					{
						$position++;
						continue;
					}
					if($position < $length && $input[$position] === '}')
					{
						$position++;
						return array(TRUE, $object);
					}
					return array(FALSE, NULL);
				}
			}
			if($character === '[')
			{
				$position++;
				$array = array();
				while(TRUE)
				{
					while($position < $length && strpos(" \t\r\n\f\v", $input[$position]) !== FALSE)
						$position++;
					if($position < $length && $input[$position] === ']')
					{
						$position++;
						return array(TRUE, $array);
					}

					$_value = self::sc_parse_stream_data($input, $position, $depth + 1);
					if(!$_value[0])
						return array(FALSE, NULL);
					$array[] = $_value[1];

					while($position < $length && strpos(" \t\r\n\f\v", $input[$position]) !== FALSE)
						$position++;
					if($position < $length && $input[$position] === ',')
					{
						$position++;
						continue;
					}
					if($position < $length && $input[$position] === ']')
					{
						$position++;
						return array(TRUE, $array);
					}
					return array(FALSE, NULL);
				}
			}
			if($character === "'" || $character === '"')
			{
				$quote = $character;
				$string = '';
				$position++;
				while($position < $length)
				{
					$character = $input[$position++];
					if($character === $quote)
					{
						//260805 Reject executable URL schemes even when hidden with whitespace or control characters inside structured data.
						$_scheme = strtolower(preg_replace('/[\x00-\x20]+/', '', $string));
						if(preg_match('/^(?:javascript|vbscript):/i', $_scheme))
							return array(FALSE, NULL);
						return array(TRUE, $string);
					}
					if($character === '\\')
					{
						if($position >= $length)
							return array(FALSE, NULL);
						$_escape = $input[$position++];
						switch($_escape)
						{
							case "'": case '"': case '\\': case '/': $string .= $_escape; break;
							case 'b': $string .= "\x08"; break;
							case 'f': $string .= "\x0C"; break;
							case 'n': $string .= "\n"; break;
							case 'r': $string .= "\r"; break;
							case 't': $string .= "\t"; break;
							case 'v': $string .= "\x0B"; break;
							case "\n": break;
							case "\r": if($position < $length && $input[$position] === "\n") $position++; break;
							case '0':
								if($position < $length && ctype_digit($input[$position])) return array(FALSE, NULL);
								$string .= "\0";
								break;
							case 'x':
								$_hex = substr($input, $position, 2);
								if(strlen($_hex) !== 2 || !ctype_xdigit($_hex)) return array(FALSE, NULL);
								$_unicode = json_decode('"\\u00'.$_hex.'"');
								if(!is_string($_unicode)) return array(FALSE, NULL);
								$string .= $_unicode;
								$position += 2;
								break;
							case 'u':
								$_hex = substr($input, $position, 4);
								if(strlen($_hex) !== 4 || !ctype_xdigit($_hex)) return array(FALSE, NULL);
								$_unicode_escape = '\\u'.$_hex;
								$position += 4;
								if(hexdec($_hex) >= 0xD800 && hexdec($_hex) <= 0xDBFF)
								{
									if(substr($input, $position, 2) !== '\u') return array(FALSE, NULL);
									$_low_hex = substr($input, $position + 2, 4);
									if(strlen($_low_hex) !== 4 || !ctype_xdigit($_low_hex) || hexdec($_low_hex) < 0xDC00 || hexdec($_low_hex) > 0xDFFF) return array(FALSE, NULL);
									$_unicode_escape .= '\\u'.$_low_hex;
									$position += 6;
								}
								$_unicode = json_decode('"'.$_unicode_escape.'"');
								if(!is_string($_unicode)) return array(FALSE, NULL);
								$string .= $_unicode;
								break;
							default: $string .= $_escape; break;
						}
					}
					else
					{
						if(ord($character) < 32)
							return array(FALSE, NULL);
						$string .= $character;
					}
				}
				return array(FALSE, NULL);
			}
			if($character === '-' || ctype_digit($character))
			{
				if(!preg_match('/^-?(?:0|[1-9][0-9]*)(?:\.[0-9]+)?(?:[eE][+\-]?[0-9]+)?/', substr($input, $position), $_number))
					return array(FALSE, NULL);
				$position += strlen($_number[0]);
				$_value = json_decode($_number[0]);
				if(json_last_error() !== JSON_ERROR_NONE || (is_float($_value) && !is_finite($_value)))
					return array(FALSE, NULL);
				return array(TRUE, $_value);
			}
			foreach(array('true' => TRUE, 'false' => FALSE, 'null' => NULL) as $_literal => $_value)
				if(substr($input, $position, strlen($_literal)) === $_literal)
				{
					$position += strlen($_literal);
					return array(TRUE, $_value);
				}
			return array(FALSE, NULL);
		}
	}
}
