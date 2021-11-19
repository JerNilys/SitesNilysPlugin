<?php

if (ini_get('max_execution_time') < 300) {
    ini_set('max_execution_time', 300);
}

require_once dirname(__FILE__) . '/../../../wp-load.php';
require_once dirname(__FILE__) . '/lib/titan-framework/titan-framework-embedder.php';
require_once dirname(__FILE__) . '/utils/images.php';

class Checked_Post_Endpoint
{

    private $checked_options;

    public function __construct()
    {
        $this->checked_options = TitanFramework::getInstance('checked');
    }

    public function run()
    {

        if (!$this->is_bearer_token_valid()) {

            wp_send_json(array(
                'status' => false,
                'code' => 'incorrect_api_key',
                'message' => __('Incorrect API key.', CHECKED_ID_LANGUAGES)
            ));

            return;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!empty($data['test_connection'])) {

            wp_send_json(array(
                'status' => true,
                'code' => 'success_connection',
                'plugin_url' => plugin_dir_url(__FILE__) . $this->checked_options->getOption('checked_file_endpoint'),
                'message' => __('Success connection', CHECKED_ID_LANGUAGES),
                'version' => checked_get_version(),
            ));
        } else if (isset($data['id']) and isset($data['affiliate_campaign_name']) and isset($data['offer_name']) and isset($data['offer_url'])
            and isset($data['slug']) and isset($data['content']) and isset($data['affiliate_link']) and isset($data['enable']) and isset($data['deleted']) and isset($data['website_url']) and isset($data['guid'])) {
            global $wpdb;

            if ($data['deleted'] == false) {
                // Si la ligne n'est pas supprimée, on insère la ligne ou on la modifie si elle existe déjà.
                $sql = "INSERT INTO {$wpdb->prefix}sn_campaigns VALUES (%s,%s,%s,%s,%s,%s,%s,%d,%s,%s) ON DUPLICATE KEY UPDATE affiliate_campaign_name = %s, offer_name = %s, offer_url = %s, slug = %s, content = %s, affiliate_link = %s, enable = %d, website_url = %s";

                $sql = $wpdb->prepare($sql,
                    $data['id'], $data['affiliate_campaign_name'], $data['offer_name'], $data['offer_url'], $data['slug'], $data['content'], $data['affiliate_link'], $data['enable'], $data['website_url'], $data['guid'],
                    $data['affiliate_campaign_name'], $data['offer_name'], $data['offer_url'], $data['slug'], $data['content'], $data['affiliate_link'], $data['enable'], $data['website_url']);
            }
            else {
                $sql = "DELETE FROM {$wpdb->prefix}sn_campaigns WHERE id = %s";
                $sql = $wpdb->prepare($sql, $data['id']);
            }

            $wpdb->query($sql);

            wp_send_json(array(
                'status' => true,
                'code' => 'success',
                'message' => __('Success', CHECKED_ID_LANGUAGES),
            ));
        }
        else {
            wp_send_json(array(
                'status' => false,
                'code' => 'incorrect_data',
                'message' => __('Wrong data.', CHECKED_ID_LANGUAGES)
            ));
        }
    }

    private function is_bearer_token_valid()
    {

        $bearer_token = $this->get_bearer_token();

        $apy_key = $this->checked_options->getOption('checked_api_key');

        return (!empty($bearer_token) && !empty($apy_key) && $bearer_token === $apy_key);
    }

    /**
     * Get access token from header
     * */
    private function get_bearer_token()
    {

        $headers = $this->get_authorization_header();

        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {

                return $matches[1];
            }
        }


        return null;
    }

    /**
     * Get header Authorization
     * */
    private function get_authorization_header()
    {

        $headers = null;

        if (isset($_SERVER['Authorization'])) {

            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI

            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {

            $requestHeaders = apache_request_headers();

            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));

            if (isset($requestHeaders['Authorization'])) {

                $headers = trim($requestHeaders['Authorization']);
            }
        }

        return $headers;
    }

    public function is_yoast_active()
    {

        require_once(ABSPATH . 'wp-admin/includes/plugin.php');

        if (is_plugin_active('wordpress-seo/wp-seo.php')) {

            return true;
        }

        return false;
    }
}

$endpoint = new Checked_Post_Endpoint();
$endpoint->run();
