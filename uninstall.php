<?php
// ------------------------------------------------
// if uninstall.php is not called by WordPress, die
// ------------------------------------------------
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

function send_unauthentication( $api_key, $data ) {

    $data_json = json_encode( $data );

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://sites.nilys.com/api/wordpress-plugin/unauthentication',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 2,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $data_json,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer '. $api_key
        ),
    ));

    $response = curl_exec( $curl );
    curl_close( $curl );

    return $response;
}

$checked_options = maybe_unserialize( get_option( 'sn_options' ) );
$data = array(
    'website_url' => get_site_url()."/",
);
send_unauthentication( $checked_options['sn_api_key'], $data );


global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}sn_campaigns" );
delete_option("sn_plugin_version");
delete_option("sn_options");



?>
