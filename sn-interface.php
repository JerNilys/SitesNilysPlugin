<?php
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Blocking direct access to plugin      -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
defined('ABSPATH') or die('Are you crazy!');


add_action( 'tf_create_options', 'checked_create_options' );
function checked_create_options() {

    remove_filter( 'admin_footer_text', 'addTitanCreditText' );

    /***************************************************************
     * Launch options framework instance
     ***************************************************************/
    $checked_options = TitanFramework::getInstance( 'checked' );
    /***************************************************************
     * Create option menu item
     ***************************************************************/
    $checked_panel = $checked_options->createAdminPanel( array(
        'menu_title' => "SitesNilys",
//        'name'       => '<a href="https://sites.nilys.com/"><img src="https://sites.nilys.com/images/favicon/mstile-144x144.png" alt="SitesNilys" style="width: 250px;"></a>',
        'name' => '<a></a>',
        'icon'       => 'dashicons-upload',
        'id'         => CHECKED_ID,
        'capability' => 'manage_options',
        'desc'       => '',
    ) );

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
    // Create settings panel tabs              -=
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
    $dashboardTab = $checked_panel->createTab( array(
        'name' => __( 'Settings', CHECKED_ID_LANGUAGES ),
        'id'   => 'dashboard',
    ) );

    $checkedOptionFile = CHECKED_PATH .'dashboard.php';
    if (file_exists($checkedOptionFile))
        require_once($checkedOptionFile);

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
    // Launch options framework instance     -=
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
    $dashboardTab->createOption( array(
        'type'      => 'save',
        'save'      => __( 'Save', CHECKED_ID_LANGUAGES ),
        'use_reset' => false,
    ) );

//    create_missing_files_after_update();

} // END checked_create_options

function create_missing_files_after_update( $upgrader_object, $options ) {
    // The path to our plugin's main file
    $our_plugin = plugin_basename( __FILE__ );
    // If an update has taken place and the updated type is plugins and the plugins element exists
    if( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
        // Iterate through the plugins being updated and check if ours is there
        foreach( $options['plugins'] as $plugin ) {
            if( $plugin == $our_plugin ) {
                $checked_options = TitanFramework::getInstance( 'checked' );

                // Création des fichiers s'ils existent (supprimés lors d'une mise à jour du plugin)
                $previous_file_update_db = CHECKED_PATH . $checked_options->getOption( 'sn_update_campaigns_rows' );
                if ( !file_exists( $previous_file_update_db ) && $previous_file_update_db != CHECKED_PATH) {
                    $content = "<?php require_once '" . CHECKED_PATH . "sn-update-campaigns-rows.php';";
                    file_put_contents( $previous_file_update_db, $content );
                }
                $previous_file_redirect = CHECKED_PATH . $checked_options->getOption( 'sn_redirect_file' );
                if ( !file_exists( $previous_file_redirect ) && $previous_file_redirect != CHECKED_PATH) {
                    $content = "<?php require_once '" . CHECKED_PATH . "sn-redirect-affiliate-link.php';";
                    file_put_contents( $previous_file_redirect, $content );
                }

            }
        }
    }
}
add_action( 'upgrader_process_complete', 'create_missing_files_after_update', 10, 2 );

function checked_save_options( $container, $activeTab, $options ) {

    if ( empty( $activeTab ) ) {

        return;
    }

    $checked_options = maybe_unserialize( get_option( 'checked_options' ) );

    if ( empty( $checked_options['sn_api_key'] ) ||
        empty( $checked_options['sn_redirect_file'] ) ||
        empty( $checked_options['sn_update_campaigns_rows'] ) ) {
        return;
    }

    $data = array(
        'website_url' => get_site_url()."/",
        'path_plugin_update_db'  => CHECKED_URL . $checked_options['sn_update_campaigns_rows'],
        'path_plugin_redirect_file'  => CHECKED_URL . $checked_options['sn_redirect_file'],
        'version' => checked_get_version(),
    );

    $response = checked_curl( $checked_options['sn_api_key'], $data );

    $parsed_response = json_decode($response, true);

    checked_set_connection_status( $parsed_response );
}

add_action( 'tf_save_admin_checked', 'checked_save_options', 10, 3 );

function checked_curl( $api_key, $data ) {

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

function checked_get_connection_status() {

    $connection_statuses = array(
        'fail'                => __( 'Unsuccessful connection!', CHECKED_ID_LANGUAGES ),
        'ok'                  => __( 'Login successful!', CHECKED_ID_LANGUAGES ),
        'blocked_by_firewall' => __( 'A firewall seems to block the connection of the plugin !', CHECKED_ID_LANGUAGES ),
        'wrong_token'         => __( 'The connection failed. Check that you have added your site to SitesNilys.', CHECKED_ID_LANGUAGES ),
    );

    $checked_options = maybe_unserialize( get_option( 'checked_options' ) );

    if ( ! empty( $checked_options['checked_connection_status'] ) && array_key_exists( $checked_options['checked_connection_status'], $connection_statuses ) ) {

        if ( 'ok' === $checked_options['checked_connection_status'] ) {

            return checked_get_styled_status( $connection_statuses[ $checked_options['checked_connection_status'] ] );
        } else {

            return checked_get_styled_status( $connection_statuses[ $checked_options['checked_connection_status'] ], false );
        }
    }

    return checked_get_styled_status( $connection_statuses['fail'], false );
}

function checked_get_styled_status( $message, $is_successful = true ) {

    if ( $is_successful ) {

        return '<span style="color: #00FF00;"><span style="font-size: 25px; vertical-align: middle;">&#10003;</span>' . $message . '</span>';
    }

    return '<span style="color: #FF0000;"><span style="font-size: 25px; vertical-align: middle;">&#10005;</span>' . $message . '</span>';
}

function checked_pre_save_admin( $container, $activeTab, $options ) {

    $checked_options = TitanFramework::getInstance( 'checked' );

    $api_key = $checked_options->getOption( 'sn_api_key' );

    if ( empty( $api_key )) {

        checked_redirect_to_form();
        exit();
    }


    $random_file_update_db = checked_random3() . '.php';
    $previous_file_update_db = CHECKED_PATH . $checked_options->getOption( 'sn_update_campaigns_rows' );
    $container->owner->setOption( 'sn_update_campaigns_rows', $random_file_update_db );

    $new_file_update_db = CHECKED_PATH . $random_file_update_db;

    if ( !file_exists( $previous_file_update_db ) || $previous_file_update_db == CHECKED_PATH) {
        $content = "<?php require_once '" . CHECKED_PATH . "sn-update-campaigns-rows.php';";

        file_put_contents( $new_file_update_db, $content );
    } else {
        rename( $previous_file_update_db, $new_file_update_db );
    }

    $random_file_redirect = checked_random3() . '.php';
    $previous_file_redirect = CHECKED_PATH . $checked_options->getOption( 'sn_redirect_file' );

    $container->owner->setOption( 'sn_redirect_file', $random_file_redirect );
    $new_file_redirect = CHECKED_PATH . $random_file_redirect;

    if ( !file_exists( $previous_file_redirect ) || $previous_file_redirect == CHECKED_PATH) {
        $content = "<?php require_once '" . CHECKED_PATH . "sn-redirect-affiliate-link.php';";
        file_put_contents( $new_file_redirect, $content );
    } else {
        rename( $previous_file_redirect, $new_file_redirect );
    }
}

add_action( 'tf_pre_save_admin_checked', 'checked_pre_save_admin', 10, 3 );

function checked_redirect_to_form() {

    $url = wp_get_referer();
    $url = add_query_arg( 'page', urlencode( CHECKED_ID ), $url );
    $url = add_query_arg( 'tab', urlencode( 'dashboard' ), $url );

    wp_redirect( esc_url_raw( $url ) );
}


function checked_set_connection_status( $parsed_response ) {

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

    $checked_options = maybe_unserialize( get_option( 'checked_options' ) );
    $checked_options['checked_connection_status'] = $connection_status;
    update_option( 'checked_options', maybe_serialize( $checked_options ) );
}

