<?php

/**
 * @author      sites.nilys.com
 * @copyright   2021 sites.nilys.com
 * @license     GPL-3.0+
 * Plugin Name: SitesNilys
 * Description: Mise à jour de vos posts depuis la plateforme sites.nilys.com
 * Version:     1.1.9
 * Text Domain: SitesNilys
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'ABSPATH' ) or die( 'Are you crazyy!' );


class SnPlugin {

	public function __construct() {

		$this->init_constants();
		$this->run_update_checker();

		require_once( SN_PATH .  'includes.php' );

		register_activation_hook( __FILE__, 'sn_install' );
		register_deactivation_hook( __FILE__, 'sn_uninstall' );

        add_action( 'after_delete_post', array( $this, 'after_delete_post' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'sn_load_scripts'));
        // Envoi le num de la nouvelle version
        add_action( 'upgrader_process_complete', array( $this,'sn_send_updated_version'), 10, 2);

    }

    function sn_load_scripts(){
        wp_enqueue_script( 'sn-scripts-js', plugin_dir_url( __FILE__ ) . 'js/sn-scripts.js?v=' . SN_VERSION, array('jquery'));
        wp_enqueue_style( 'sn-styles-css', plugins_url( 'css/sn-styles.css?v=' . SN_VERSION, __FILE__ ) );
    }

	private function init_constants() {

		defined( 'SN_PATH' ) or define( 'SN_PATH', plugin_dir_path( __FILE__ ) );
		defined( 'SN_URL' ) or define( 'SN_URL', plugin_dir_url( __FILE__ ) );
		defined( 'SN_BASE' ) or define( 'SN_BASE', plugin_basename( __FILE__ ) );
		defined( 'SN_ID' ) or define( 'SN_ID', 'SitesNilys' );
		defined( 'SN_ID_LANGUAGES' ) or define( 'SN_ID_LANGUAGES', 'sn-translate' );
		defined( 'SN_VERSION' ) or define( 'SN_VERSION', '1.0' );
		defined( 'SN_NAME' ) or define( 'SN_NAME', 'SitesNilys' );
	}

    function sn_send_updated_version($upgrader_object, $options) {
        $our_plugin = plugin_basename( __FILE__ );
        // If an update has taken place and the updated type is plugins and the plugins element exists
        if( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
            // Iterate through the plugins being updated and check if ours is there
            foreach( $options['plugins'] as $plugin ) {
                if( $plugin == $our_plugin ) {
                    // Your action if it is your plugin
                    $sn_options = maybe_unserialize( get_option( 'sn_options' ) );
                    $data = array(
                        'website_url' => get_site_url()."/",
                        'version' => sn_get_version()
                    );
                    $data_json = json_encode( $data );

                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://sites.nilys.com/api/wordpress-plugin/update-plugin-version',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 2,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => $data_json,
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/json',
                            'Authorization: Bearer '. $sn_options['sn_api_key']
                        ),
                    ));

                    $response = curl_exec( $curl );
                    curl_close( $curl );
                }
            }
        }

    }

	private function run_update_checker() {

		require SN_PATH . '/lib/plugin-update-checker/plugin-update-checker.php';

        $myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
			'https://github.com/JerNilys/SitesNilysPlugin',
			__FILE__
		);

        //Set the branch that contains the stable release.
        $myUpdateChecker->setBranch('master');

    }
}

new SnPlugin();
