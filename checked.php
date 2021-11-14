<?php

/**
 * @author      sites.nilys.com
 * @copyright   2021 sites.nilys.com
 * @license     GPL-3.0+
 * Plugin Name: SitesNilys
 * Description: Mise à jour de vos posts depuis la plateforme sites.nilys.com
 * Version:     1.1.1
 * Text Domain: SitesNilys
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined( 'ABSPATH' ) or die( 'Are you crazyy!' );
global $sn_plugin_version;
$sn_plugin_version = '1.2';

class Checked {

	public function __construct() {

		$this->init_constants();
		$this->run_update_checker();

		require_once( CHECKED_PATH .  'includes.php' );

		register_activation_hook( __FILE__, 'checked_install' );
		register_deactivation_hook( __FILE__, 'checked_uninstall' );
//		echo "debut";
        add_action( 'plugins_loaded', 'sn_create_db_campaigns' );
//        echo "fin create";
        add_action( 'plugins_loaded', 'sn_update_db_plugin' );
//        echo "fin update";
//        echo "constructeur";


        add_action( 'after_delete_post', array( $this, 'after_delete_post' ) );

	}


	private function init_constants() {

		defined( 'CHECKED_PATH' ) or define( 'CHECKED_PATH', plugin_dir_path( __FILE__ ) );
		defined( 'CHECKED_URL' ) or define( 'CHECKED_URL', plugin_dir_url( __FILE__ ) );
		defined( 'CHECKED_BASE' ) or define( 'CHECKED_BASE', plugin_basename( __FILE__ ) );
		defined( 'CHECKED_ID' ) or define( 'CHECKED_ID', 'SitesNilys' );
		defined( 'CHECKED_ID_LANGUAGES' ) or define( 'CHECKED_ID_LANGUAGES', 'checked-translate' );
		defined( 'CHECKED_VERSION' ) or define( 'CHECKED_VERSION', '1.0' );
		defined( 'CHECKED_NAME' ) or define( 'CHECKED_NAME', 'SitesNilys' );
	}

	private function run_update_checker() {

		require CHECKED_PATH . '/lib/plugin-update-checker/plugin-update-checker.php';

        $myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
			'https://github.com/SitesNilys/wp-plugin',
			__FILE__,
			'checked'
		);

        //Set the branch that contains the stable release.
        $myUpdateChecker->setBranch('master');

        //Optional: If you're using a private repository, specify the access token like this:
        $myUpdateChecker->setAuthentication('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');
    }
}

new Checked();
