<?php
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Blocking direct access to plugin      -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
defined('ABSPATH') or die('Are you crazy!');

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// Create tab's dashboard                -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
// ----------------------------------------
$dashboardTab->createOption(array(
    'name' => __('API', SN_ID_LANGUAGES),
    'type' => 'heading',
));
// ----------------------------------------
$dashboardTab->createOption(array(
    'id' => 'sn_api_key',
    'name' => __('API key', SN_ID_LANGUAGES),
    'type' => 'text',
    'desc' => __('Fill in your key (API)', SN_ID_LANGUAGES),
    'unit' => sn_get_connection_status()
));
$dashboardTab->createOption(array(
    'id' => 'sn_update_campaigns_rows',
    'type' => 'text',
    'hidden' => true
));
$dashboardTab->createOption(array(
    'id' => 'sn_redirect_file',
    'type' => 'text',
    'hidden' => true
));
// ----------------------------------------
if (!function_exists("sn_admin_notice_error")) {
    function sn_admin_notice_error()
    {
        $sn_options = TitanFramework::getInstance('sn');
        $sn_class = 'notice notice-error';

        $menu_name = SN_NAME;
        $sn_current_options = maybe_unserialize(get_option('sn_options'));

        if (!empty($sn_current_options['sn_menu_name'])) {
            $menu_name = $sn_current_options['sn_menu_name'];
        }

        $sn_message = strtoupper($menu_name) . ': ' . sprintf(__('Fill in all <a href="%s">dashboard options</a>', SN_ID_LANGUAGES), get_admin_url(get_current_blog_id(), 'admin.php?page=SitesNilys&tab=dashboard'));

        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($sn_class), $sn_message);
    }
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
//     Check if options are not empty    -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
if (empty($sn_options->getOption('sn_api_key'))) {
    add_action('admin_notices', 'sn_admin_notice_error');
}
?>
