<?php
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Blocking direct access to plugin      -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
defined( 'ABSPATH' ) or die( 'Are you crazy!' );


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
