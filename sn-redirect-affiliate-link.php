<?php

if (ini_get('max_execution_time') < 300) {
    ini_set('max_execution_time', 300);
}

require_once dirname(__FILE__) . '/../../../wp-load.php';
require_once dirname(__FILE__) . '/lib/titan-framework/titan-framework-embedder.php';
require_once dirname(__FILE__) . '/utils/images.php';

class SnRedirectAffiliateLink
{

    private $checked_options;

    public function __construct()
    {
        $this->checked_options = TitanFramework::getInstance('checked');
    }

    public function run()
    {
        if (isset($_GET['id'])) {
            global $wpdb;

            $guid = $_GET['id'];
            $sql = "SELECT * FROM {$wpdb->prefix}sn_campaigns WHERE guid = '$guid'";
            $campaign = $wpdb->get_row($sql);
            if (isset($campaign)) {

                wp_redirect($campaign->affiliate_link);
            }

        }
        wp_send_json(array(
            'status' => false,
            'code' => 'incorrect_data',
            'message' => __('Incorrect data.', CHECKED_ID_LANGUAGES)
        ));
    }
}


$endpoint = new SnRedirectAffiliateLink();
$endpoint->run();
