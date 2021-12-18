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
        $this->checked_options = TitanFramework::getInstance('sn');
    }

    public function run()
    {
        if (isset($_GET['id']) and isset($_GET['post_id'])) {
            global $wpdb;

            $guid = $_GET['id'];
            $post_id = $_GET['post_id'];
            $sql = "SELECT * FROM {$wpdb->prefix}sn_campaigns WHERE guid = '$guid'";
            $campaign = $wpdb->get_row($sql);
            if (isset($campaign)) {
                $affiliate_link = $campaign->affiliate_link;
                $affiliate_link = str_replace('$post_id', $post_id, $affiliate_link);
                wp_redirect($affiliate_link);
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
