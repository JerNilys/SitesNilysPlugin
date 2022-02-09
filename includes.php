<?php
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Blocking direct access to plugin      -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
defined('ABSPATH') or die('Are you crazy!');

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Load plugin translations              -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
add_action('plugins_loaded', 'sn_translate_load_textdomain', 1);
if (!function_exists('sn_translate_load_textdomain')) {
    function sn_translate_load_textdomain()
    {
        $path = basename(dirname(__FILE__)) . '/languages/';
        load_plugin_textdomain(SN_ID_LANGUAGES, false, $path);
    }
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Load plugin files                     -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
if (!function_exists('is_plugin_active'))
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Include Titan Framework               -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
$titan_check_framework_install = 'titan-framework/titan-framework.php';
// --- Check if plugin titan framework is installed
if (is_plugin_active($titan_check_framework_install)) {
    require_once(WP_CONTENT_DIR . '/plugins/titan-framework/titan-framework-embedder.php');
} else {
    require_once(SN_PATH . 'lib/titan-framework/titan-framework-embedder.php');
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Initialize plugin SQL Debug Mode      -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
defined('_SN_DEBUG') or define('_SN_DEBUG', false);

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Initialize plugin Files               -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
$file = SN_PATH . "utils/functions.php";
if (file_exists($file)) require_once($file);

$snFiles = ['interface'];
foreach ($snFiles as $snFile) {
    $file = SN_PATH . 'sn-' . $snFile . '.php';
    if (file_exists($file)) require_once($file);
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

if (!function_exists('get_plugin_data')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if (!function_exists('sn_get_version')) {

    function sn_get_version($sn_infos = 'Version')
    {
        $plugin_data = get_plugin_data(SN_PATH . 'snPlugin.php');
        $plugin_version = $plugin_data["$sn_infos"];

        return $plugin_version;
    }
}
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

// Create / Update table campaigns
require_once(SN_PATH . 'sn-campaigns-table.php');

// Replace content of post
require_once(SN_PATH . 'lib/simple_html_dom.php');
require_once(SN_PATH . 'sn-replace-content-post.php');
