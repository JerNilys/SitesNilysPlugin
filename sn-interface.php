<?php
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Blocking direct access to plugin      -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
defined('ABSPATH') or die('Are you crazy!');


add_action( 'tf_create_options', 'sn_create_options' );
function sn_create_options() {

    remove_filter( 'admin_footer_text', 'addTitanCreditText' );

    /***************************************************************
     * Launch options framework instance
     ***************************************************************/
    $sn_options = TitanFramework::getInstance( 'sn' );
    /***************************************************************
     * Create option menu item
     ***************************************************************/
    $sn_panel = $sn_options->createAdminPanel( array(
        'menu_title' => "SitesNilys",
        'name' => '<a></a>',
        'icon'       => 'dashicons-external',
        'id'         => SN_ID,
        'capability' => 'manage_options',
        'desc'       => '',
    ) );

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
    // Create settings panel tabs              -=
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
    $dashboardTab = $sn_panel->createTab( array(
        'name' => __( 'Settings', SN_ID_LANGUAGES ),
        'id'   => 'dashboard',
    ) );

    $snOptionFile = SN_PATH .'dashboard.php';
    if (file_exists($snOptionFile))
        require_once($snOptionFile);

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
    // Launch options framework instance     -=
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
    $dashboardTab->createOption( array(
        'type'      => 'save',
        'save'      => __( 'Save', SN_ID_LANGUAGES ),
        'use_reset' => false,
    ) );

    create_missing_files_after_update();

} // END sn_create_options

function create_missing_files_after_update() {
    $sn_options = TitanFramework::getInstance( 'sn' );

    // Création des fichiers s'ils existent (supprimés lors d'une mise à jour du plugin)
    $previous_file_update_db = SN_PATH . $sn_options->getOption( 'sn_update_campaigns_rows' );
    if ( !file_exists( $previous_file_update_db ) && $previous_file_update_db != SN_PATH) {
        $content = "<?php require_once '" . SN_PATH . "sn-update-campaigns-rows.php';";
        file_put_contents( $previous_file_update_db, $content );
    }
    $previous_file_redirect = SN_PATH . $sn_options->getOption( 'sn_redirect_file' );
    if ( !file_exists( $previous_file_redirect ) && $previous_file_redirect != SN_PATH) {
        $content = "<?php require_once '" . SN_PATH . "sn-redirect-affiliate-link.php';";
        file_put_contents( $previous_file_redirect, $content );
    }
}

function sn_save_options( $container, $activeTab, $options ) {

    if ( empty( $activeTab ) ) {

        return;
    }

    $sn_options = maybe_unserialize( get_option( 'sn_options' ) );

    if ( empty( $sn_options['sn_api_key'] ) ||
        empty( $sn_options['sn_redirect_file'] ) ||
        empty( $sn_options['sn_update_campaigns_rows'] ) ) {
        return;
    }

    $data = array(
        'website_url' => get_site_url()."/",
        'path_plugin_update_db'  => SN_URL . $sn_options['sn_update_campaigns_rows'],
        'path_plugin_redirect_file'  => SN_URL . $sn_options['sn_redirect_file'],
        'path_plugin_insert_post_file'  => SN_URL . 'sn-insert-post.php',
        'version' => sn_get_version(),
    );

    $response = sn_curl( $sn_options['sn_api_key'], $data );

    $parsed_response = json_decode($response, true);

    sn_set_connection_status( $parsed_response );
}

add_action( 'tf_save_admin_sn', 'sn_save_options', 10, 3 );

function sn_curl( $api_key, $data ) {

    $data_json = json_encode( $data );

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://sites.nilys.com/api/wordpress-plugin/check-api-key',
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

function sn_get_connection_status() {

    $connection_statuses = array(
        'fail'                => __( 'Unsuccessful connection!', SN_ID_LANGUAGES ),
        'ok'                  => __( 'Login successful!', SN_ID_LANGUAGES ),
        'blocked_by_firewall' => __( 'A firewall seems to block the connection of the plugin !', SN_ID_LANGUAGES ),
        'wrong_token'         => __( 'The connection failed. Check that you have added your site to SitesNilys.', SN_ID_LANGUAGES ),
    );

    $sn_options = maybe_unserialize( get_option( 'sn_options' ) );

    if ( ! empty( $sn_options['sn_connection_status'] ) && array_key_exists( $sn_options['sn_connection_status'], $connection_statuses ) ) {

        if ( 'ok' === $sn_options['sn_connection_status'] ) {

            return sn_get_styled_status( $connection_statuses[ $sn_options['sn_connection_status'] ] );
        } else {

            return sn_get_styled_status( $connection_statuses[ $sn_options['sn_connection_status'] ], false );
        }
    }

    return sn_get_styled_status( $connection_statuses['fail'], false );
}

function sn_get_styled_status($message, $is_successful = true ) {

    if ( $is_successful ) {

        return '<span style="color: #00FF00;"><span style="font-size: 25px; vertical-align: middle;">&#10003;</span>' . $message . '</span>';
    }

    return '<span style="color: #FF0000;"><span style="font-size: 25px; vertical-align: middle;">&#10005;</span>' . $message . '</span>';
}

function sn_pre_save_admin( $container, $activeTab, $options ) {

    $sn_options = TitanFramework::getInstance( 'sn' );

    $api_key = $sn_options->getOption( 'sn_api_key' );

    if ( empty( $api_key )) {

        sn_redirect_to_form();
        exit();
    }


    $random_file_update_db = sn_random3() . '.php';
    $previous_file_update_db = SN_PATH . $sn_options->getOption( 'sn_update_campaigns_rows' );
    $container->owner->setOption( 'sn_update_campaigns_rows', $random_file_update_db );

    $new_file_update_db = SN_PATH . $random_file_update_db;

    if ( !file_exists( $previous_file_update_db ) || $previous_file_update_db == SN_PATH) {
        $content = "<?php require_once '" . SN_PATH . "sn-update-campaigns-rows.php';";

        file_put_contents( $new_file_update_db, $content );
    } else {
        rename( $previous_file_update_db, $new_file_update_db );
    }

    $random_file_redirect = sn_random3() . '.php';
    $previous_file_redirect = SN_PATH . $sn_options->getOption( 'sn_redirect_file' );

    $container->owner->setOption( 'sn_redirect_file', $random_file_redirect );
    $new_file_redirect = SN_PATH . $random_file_redirect;

    if ( !file_exists( $previous_file_redirect ) || $previous_file_redirect == SN_PATH) {
        $content = "<?php require_once '" . SN_PATH . "sn-redirect-affiliate-link.php';";
        file_put_contents( $new_file_redirect, $content );
    } else {
        rename( $previous_file_redirect, $new_file_redirect );
    }
}

add_action( 'tf_pre_save_admin_sn', 'sn_pre_save_admin', 10, 3 );

function sn_redirect_to_form() {

    $url = wp_get_referer();
    $url = add_query_arg( 'page', urlencode( SN_ID ), $url );
    $url = add_query_arg( 'tab', urlencode( 'dashboard' ), $url );

    wp_redirect( esc_url_raw( $url ) );
}


function sn_set_connection_status( $parsed_response ) {

    if ( ! empty( $parsed_response['status'] ) ) {

        if ( $parsed_response['status'] === "ok" ) {

            $connection_status = 'ok';
        } else if ( ! empty( $parsed_response['code'] ) ) {

            $connection_status = $parsed_response['code'];
        } else {

            $connection_status = 'fail';
        }

    } else {

        $connection_status = 'fail';
    }

    $sn_options = maybe_unserialize( get_option( 'sn_options' ) );
    $sn_options['sn_connection_status'] = $connection_status;
    update_option( 'sn_options', maybe_serialize( $sn_options ) );
}

