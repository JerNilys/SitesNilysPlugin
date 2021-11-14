<?php
// ------------------------------------------------
// if uninstall.php is not called by WordPress, die
// ------------------------------------------------
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// --------------
// Delete Options
// --------------
$options = ['checked_api_key'
		   ,'checked_website_url'
		   ,'checked_website_plugin_url'
		   ,'checked_endpoint'
		   ,'checked_file_endpoint'
		   ,'checked_db_version'
		   ,'checked_status'
		   ,'checked_is_connected'
//		   ,'checked_options'
];
// --------------
foreach ( $options as $option ) {
	if ( get_option( $option ) ) {
		delete_option( $option );
	}
}

?>
