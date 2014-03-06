	// Modified for full plugin compatiblity.
	//if ( empty( $active_plugins ) || defined( 'WP_INSTALLING' ) )
	if ( empty( $active_plugins ) || ( defined( 'WP_INSTALLING' ) && !preg_match("/\/wp-activate\.php/", $_SERVER["REQUEST_URI"]) ) )
	return $plugins;