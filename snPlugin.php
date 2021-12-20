<?php

/**
 * @author      sites.nilys.com
 * @copyright   2021 sites.nilys.com
 * @license     GPL-3.0+
 * Plugin Name: SitesNilys
 * Description: Mise à jour de vos posts depuis la plateforme sites.nilys.com
 * Version:     1.0.5
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

	private function run_update_checker() {

		require SN_PATH . '/lib/plugin-update-checker/plugin-update-checker.php';

        $myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
			'https://github.com/JerNilys/SitesNilysPlugin',
			__FILE__
		);

        //Set the branch that contains the stable release.
        $myUpdateChecker->setBranch('remove-checked');

    }
}

new SnPlugin();
