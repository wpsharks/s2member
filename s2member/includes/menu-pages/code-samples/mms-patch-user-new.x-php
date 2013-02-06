	// Modified for full plugin compatiblity.
	//wpmu_signup_user( $new_user_login, $_REQUEST[ 'email' ], array( 'add_to_blog' => $wpdb->blogid, 'new_role' => $_REQUEST[ 'role' ] ) );
	wpmu_signup_user( $new_user_login, $_REQUEST[ 'email' ], apply_filters( 'add_signup_meta', array( 'add_to_blog' => $wpdb->blogid, 'new_role' => $_REQUEST[ 'role' ] ) ) );