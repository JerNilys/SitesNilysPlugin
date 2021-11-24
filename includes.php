<?php
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Blocking direct access to plugin      -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
defined('ABSPATH') or die('Are you crazy!');

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Load plugin translations              -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
add_action('plugins_loaded', 'checked_translate_load_textdomain', 1);
if (!function_exists('checked_translate_load_textdomain')) {
    function checked_translate_load_textdomain()
    {
        $path = basename(dirname(__FILE__)) . '/languages/';
        load_plugin_textdomain(CHECKED_ID_LANGUAGES, false, $path);
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
    require_once(CHECKED_PATH . 'lib/titan-framework/titan-framework-embedder.php');
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Initialize plugin SQL Debug Mode      -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
defined('_CHECKED_DEBUG') or define('_CHECKED_DEBUG', false);

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Initialize plugin Files               -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
$file = CHECKED_PATH . "utils/functions.php";
if (file_exists($file)) require_once($file);

$checkedFiles = ['system', 'interface'];
foreach ($checkedFiles as $checkedFile) {
    $file = CHECKED_PATH . 'checked-' . $checkedFile . '.php';
    if (file_exists($file)) require_once($file);
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

if (!function_exists('get_plugin_data')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if (!function_exists('checked_get_version')) {

    function checked_get_version($checked_infos = 'Version')
    {
        $plugin_data = get_plugin_data(CHECKED_PATH . 'checked.php');
        $plugin_version = $plugin_data["$checked_infos"];

        return $plugin_version;
    }
}
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

// Create / Update table campaigns
require_once(CHECKED_PATH . 'sn-campaigns-table.php');

// Replace content of post
require_once(CHECKED_PATH . 'sn-replace-content-post.php');
