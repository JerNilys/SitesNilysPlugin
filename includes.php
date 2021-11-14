<?php
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Blocking direct access to plugin      -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
defined('ABSPATH') or die('Are you crazy!');

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Load plugin translations              -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
add_action( 'plugins_loaded', 'checked_translate_load_textdomain', 1 );
if ( ! function_exists( 'checked_translate_load_textdomain' ) ) {
	function checked_translate_load_textdomain() {
		$path = basename( dirname( __FILE__ ) ) . '/languages/';
		load_plugin_textdomain( CHECKED_ID_LANGUAGES, false, $path );
	}
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Load plugin files                     -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
if ( ! function_exists( 'is_plugin_active' ) )
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Include Titan Framework               -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
$titan_check_framework_install = 'titan-framework/titan-framework.php';
// --- Check if plugin titan framework is installed
if (is_plugin_active($titan_check_framework_install)) {
	require_once(WP_CONTENT_DIR . '/plugins/titan-framework/titan-framework-embedder.php');
} else {
	require_once(CHECKED_PATH . 'lib/titan-framework/titan-framework-embedder.php');
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Initialize plugin SQL Debug Mode      -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
defined('_CHECKED_DEBUG') or define('_CHECKED_DEBUG', false);

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Initialize plugin Files               -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
$file = CHECKED_PATH. "utils/functions.php";
if (file_exists($file)) require_once($file);

$checkedFiles = ['system', 'interface'];
foreach ($checkedFiles as $checkedFile) {
	$file = CHECKED_PATH . 'checked-' . $checkedFile . '.php';
    if (file_exists($file)) require_once($file);
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( ! function_exists( 'checked_get_version' ) ) {

    function checked_get_version( $checked_infos = 'Version' ) {
        $plugin_data = get_plugin_data( CHECKED_PATH . 'checked.php' );
        $plugin_version = $plugin_data[ "$checked_infos" ];

        return $plugin_version;
    }
}
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=


// Ici il faudrait plutot faire un include du fichier ou y'aura la creation de la DB
function sn_create_db_campaigns() {
//    echo "Ok pour la création 1";
    global $wpdb;
    global $sn_plugin_version;

    $table_name = $wpdb->prefix . 'sn_campaigns';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
            `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
            `affiliate_campaign_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `offer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `offer_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
            `affiliate_link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `enable` tinyint(1) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    add_option( 'sn_plugin_version', $sn_plugin_version );
//    echo "Ok pour la création";
}


function sn_update_db_plugin() {
    global $sn_plugin_version;
//    echo "bon la ca passe";
    if ( get_site_option( 'sn_plugin_version' ) != $sn_plugin_version ) {
        sn_update_db_campaigns();
    }
}

function sn_update_db_campaigns() {
    global $wpdb;
    global $sn_plugin_version;

    $table_name = $wpdb->prefix . 'sn_campaigns';
    $installed_ver = get_option( "sn_plugin_version" );


    $charset_collate = $wpdb->get_charset_collate();
    if ( $installed_ver != $sn_plugin_version ) {

        $sql = "CREATE TABLE $table_name (
            `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
            `affiliate_campaign_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `offer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `offer_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
            `affiliate_link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `enable` tinyint(1) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
//        echo $sql;

        dbDelta( $sql );
//        echo $sql;

        update_option( 'sn_plugin_version', $sn_plugin_version );
    }
}

