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
    'name' => __('API', CHECKED_ID_LANGUAGES),
    'type' => 'heading',
));
// ----------------------------------------
$dashboardTab->createOption(array(
    'id' => 'checked_api_key',
    'name' => __('API key', CHECKED_ID_LANGUAGES),
    'type' => 'text',
    'desc' => __('Fill in your key (API)', CHECKED_ID_LANGUAGES),
    'unit' => checked_get_connection_status()
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
if (!function_exists("checked_admin_notice_error")) {
    function checked_admin_notice_error()
    {
        $checked_options = TitanFramework::getInstance('checked');
        $checked_class = 'notice notice-error';

        $menu_name = CHECKED_NAME;
        $checked_current_options = maybe_unserialize(get_option('checked_options'));

        if (!empty($checked_current_options['checked_menu_name'])) {
            $menu_name = $checked_current_options['checked_menu_name'];
        }

        $checked_message = strtoupper($menu_name) . ': ' . sprintf(__('Fill in all <a href="%s">dashboard options</a>', CHECKED_ID_LANGUAGES), get_admin_url(get_current_blog_id(), 'admin.php?page=SitesNilys&tab=dashboard'));

        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($checked_class), $checked_message);
    }
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
//     Check if options are not empty    -=
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
if (empty($checked_options->getOption('checked_api_key'))) {
    add_action('admin_notices', 'checked_admin_notice_error');
}
?>
