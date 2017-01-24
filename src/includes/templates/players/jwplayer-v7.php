<?php
// @codingStandardsIgnoreFile
if(!defined('WPINC')) // MUST have WordPress.
	exit("Do not access this file directly.");
?>

<div id="%%player_id%%" class="s2member-jwplayer-v7"></div>
<script type="text/javascript" src="%%player_path%%"></script>
<script type="text/javascript">
	if(typeof jwplayer.key !== 'string' || !jwplayer.key)
		jwplayer.key = '%%player_key%%';

	jwplayer('%%player_id%%').setup
		({
			playlist:
				[{
					title: '%%player_title%%',
					image: '%%player_image%%',

					mediaid: '%%player_mediaid%%',
					description: '%%player_description%%',

					tracks: %%player_tracks%%,

					sources: %%player_sources%%
				}],

			controls: %%player_controls%%,
			stretching: '%%player_stretching%%',
			width: %%player_width%%,
			height: %%player_height%%,
			aspectratio: '%%player_aspectratio%%',

			mute: %%player_mute%%,
			autostart: %%player_autostart%%,
			fallback: %%player_fallback%%,
			primary: '%%player_primary%%',
			repeat: %%player_repeat%%,

			%%player_option_blocks%%
		});
</script>
