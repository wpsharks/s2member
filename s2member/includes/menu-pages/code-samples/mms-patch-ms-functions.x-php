	// Modified for full plugin compatiblity.
	//return new WP_Error( 'user_already_exists', __( 'That username is already activated.' ), $signup);
	return apply_filters('_wpmu_activate_existing_error_', new WP_Error( 'user_already_exists', __( 'That username is already activated.' ), $signup), get_defined_vars());