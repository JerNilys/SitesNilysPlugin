<?php
global $sn_plugin_version;
$sn_plugin_version = '1.0';


function get_sql_query()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'sn_campaigns';

    $charset_collate = $wpdb->get_charset_collate();

    return "CREATE TABLE $table_name (
            `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
            `affiliate_campaign_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `offer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `offer_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `website_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
            `affiliate_link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `enable` tinyint(1) NOT NULL,
            `guid` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
}

function sn_create_db_campaigns()
{
    global $sn_plugin_version;
    $sql = get_sql_query();

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    add_option('sn_plugin_version', $sn_plugin_version);
}


function sn_update_db_plugin()
{
    global $sn_plugin_version;
    if (get_site_option('sn_plugin_version') != $sn_plugin_version) {
        sn_update_db_campaigns();
    }
}

function sn_update_db_campaigns()
{
    global $sn_plugin_version;

    $installed_ver = get_option("sn_plugin_version");

    if ($installed_ver != $sn_plugin_version) {

        $sql = get_sql_query();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($sql);

        update_option('sn_plugin_version', $sn_plugin_version);
    }
}

add_action( 'plugins_loaded', 'sn_create_db_campaigns' );
add_action( 'plugins_loaded', 'sn_update_db_plugin' );
