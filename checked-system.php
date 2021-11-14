<?php
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Blocking direct access to plugin      -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
defined( 'ABSPATH' ) or die( 'Are you crazy!' );

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Trick to update plugin database       -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
add_action( 'plugins_loaded', 'checked_update_file_endpoint' );
if ( ! function_exists( 'checked_update_file_endpoint' ) ) {
	function checked_update_file_endpoint() {
		checked_check_file_endpoint();
	}
}

function checked_check_file_endpoint() {

	$checked_options = maybe_unserialize( get_option( 'checked_options' ) );

	if ( empty( $checked_options['checked_file_endpoint'] ) && ! empty( $checked_options['checked_endpoint'] ) ) {

		$random_file = basename( $checked_options['checked_endpoint'] );

		$checked_options['checked_file_endpoint'] = $random_file;

		update_option( 'checked_options', maybe_serialize( $checked_options ) );

        if ( defined( 'ABSPATH' ) ) {
            $previous_file_endpoint = ABSPATH . $random_file;

            if ( file_exists( $previous_file_endpoint ) ) {

                unlink( $previous_file_endpoint );
            }
        }
	}

    if ( ! empty( $checked_options['checked_file_endpoint'] ) ) {

	    $file_endpoint = CHECKED_PATH . $checked_options['checked_file_endpoint'];

	    if ( ! file_exists( $file_endpoint ) ) {

            $content = "<?php require_once '" . CHECKED_PATH . "checked-post-endpoint.php';";

            file_put_contents( $file_endpoint, $content );
        }
    }
}


function checked_check_connection_func() {

	$checked_options = maybe_unserialize( get_option( 'checked_options' ) );

	if ( empty( $checked_options['checked_api_key'] ) || empty( $checked_options['checked_file_endpoint'] ) ) {

		return false;
	}

	$data = array(
		'website_url'     => get_site_url(),
		'plugin_url'      => CHECKED_URL . $checked_options['checked_file_endpoint'],
        'is_posts_editable' => true,
		'test_connection' => true,
        'version' => checked_get_version(),
	);

	$response = checked_curl( $checked_options['checked_api_key'], $data );
	$parsed_response = json_decode($response, true);

    checked_set_connection_status( $parsed_response );
}

add_action( 'checked_check_connection', 'checked_check_connection_func' );

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
