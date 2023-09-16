<?php

if (ini_get('max_execution_time') < 300) {
    ini_set('max_execution_time', 300);
}

require_once dirname(__FILE__) . '/../../../wp-load.php';
require_once dirname(__FILE__) . '/lib/titan-framework/titan-framework-embedder.php';

class Insert_Post_Endpoint
{

    private $sn_options;

    public function __construct()
    {
        $this->sn_options = TitanFramework::getInstance('sn');
    }

    public function run()
    {

        if (!$this->is_bearer_token_valid()) {

            wp_send_json(array(
                'status' => false,
                'code' => 'incorrect_api_key',
                'message' => __('Incorrect API key.', SN_ID_LANGUAGES),
                'version' =>sn_get_version(),
            ));

            return;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);


        if (empty($data['post_title']) || empty($data['post_content'])) {

            wp_send_json(array(
                'status' => false,
                'code' => 'incorrect_post',
                'message' => __('Post title and content can\'t be empty.', SN_ID_LANGUAGES)
            ));

            return;
        }

        $post_status = $data['post_status'];

        $post_title = preg_replace('/%e2%80%89/', '', $data['post_title']);

        $post_data = array(
            'post_title' => $post_title,
            'post_content' => $data['post_content'],
            'post_status' => empty($post_status) ? 'publish' : $post_status,
            'post_author' => 1,
            'post_name' => $data['post_name'],
            'post_date' => date($data['post_date']),
        );

        if (!empty($data['id'])) {
            $post_id  = $data['id'];
        } else {
            $post_id = wp_insert_post($post_data, true);
        }

        if (empty($post_id) || is_wp_error($post_id)) {

            $message = empty($post_id) ? "post_id is empty" : $post_id->get_error_message();
            wp_send_json(array(
                'status' => false,
                'code' => 'error_update_post',
                'message' => $message
            ));

            return;
        }

        // Les images
        $html = str_get_html($data['post_content']);
        $existing_media = get_attached_media('image', $post_id);
        foreach($html->find('img') as $img) {
            $prop = 'src';
            $src = $img->$prop;
            $alt = 'alt';
            $filename = $this->get_image_slug($img->$alt) . '.jpeg'  ?? basename($src);
            $new_image_url = $this->save_media($src, $filename, $post_id, $existing_media, null);
            $img->$prop = $new_image_url;
        }

        $post_data['post_content'] = (string)$html;
        $post_data['ID'] = $post_id;
        remove_filter('content_save_pre', 'wp_filter_post_kses');
        remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
        $post_id = wp_update_post($post_data, true);
        add_filter('content_save_pre', 'wp_filter_post_kses');
        add_filter('content_filtered_save_pre', 'wp_filter_post_kses');

        // L'image à la une
        if (!empty($data['post_thumbnail'])) {
            $featured_image_url = $data['post_thumbnail'];
            $slug = $this->get_image_slug($post_data['post_title']);
            $featured_image_url_filename = $slug . '.jpeg';
            $this->save_media($featured_image_url, $featured_image_url_filename, $post_id, $existing_media, true);
        }

        // Les catégories
        $post_category = !empty($data['post_categories']) ? $data['post_categories'] : [];
        wp_set_post_terms( $post_id, $post_category, 'category' );


        // Meta title et Meta desc
        $is_yoast_active = self::is_yoast_active();
        if (!empty($data['meta_title'])) {

            add_post_meta($post_id, '_sn_meta_title', $data['meta_title']);

            if ($is_yoast_active) {

                update_post_meta($post_id, '_yoast_wpseo_title', $data['meta_title']);
            }
        }

        if (!empty($data['meta_description'])) {

            add_post_meta($post_id, '_sn_meta_description', $data['meta_description']);

            if ($is_yoast_active) {

                update_post_meta($post_id, '_yoast_wpseo_metadesc', $data['meta_description']);
            }
        }

        wp_send_json(array(
            'status' => true,
            'code' => $post_data['post_status'] === 'publish' ? 'publish_success' : 'publish_pending',
            'message' => __('Success', SN_ID_LANGUAGES),
            'publish_status' => $post_data['post_status'],
            'article_url' => get_permalink($post_id),
            'post_id' => $post_id,
            'post_content' => $post_data['post_content']
        ));
    }

    public function is_yoast_active()
    {

        require_once(ABSPATH . 'wp-admin/includes/plugin.php');

        if (is_plugin_active('wordpress-seo/wp-seo.php')) {

            return true;
        }

        return false;
    }

    private function get_image_slug($image_alt) {
        $slug = str_replace(' ', '-', $image_alt);
        $slug = str_replace('\'', '-', $slug);
        $slug = str_replace('?', '', $slug);
        $slug = str_replace('!', '', $slug);
        $slug = str_replace(':', '', $slug);
        $slug = str_replace('.', '', $slug);
        $unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
        $slug = strtr($slug, $unwanted_array );
        return $slug;
    }


    private function save_media($image_url, $filename, $post_id, $existing_media, $thumbnail) {

        foreach($existing_media as $media){
            $oldMediaId= $media->ID;
            $fileMedia = get_attached_file($oldMediaId, true);
            $oldMediaFilename = pathinfo($fileMedia,PATHINFO_BASENAME);

            if ($filename == $oldMediaFilename) {
                wp_delete_attachment($oldMediaId, true);
            }

        }

        $upload_dir = wp_upload_dir();
        $image_data = file_get_contents( $image_url );

        if ( wp_mkdir_p( $upload_dir['path'] ) ) {
            $file = $upload_dir['path'] . '/' . $filename;
        }
        else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }

        file_put_contents( $file, $image_data );
        $wp_filetype = wp_check_filetype( $filename, null );

        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name( $filename ),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attach_id = wp_insert_attachment( $attachment, $file, $post_id);
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
        wp_update_attachment_metadata( $attach_id, $attach_data );

        // Set thumbnail / Featured image
        if (isset($thumbnail)) {
            set_post_thumbnail( $post_id, $attach_id );
        }

        return wp_get_attachment_url($attach_id);
    }

    private function is_bearer_token_valid()
    {

        $bearer_token = $this->get_bearer_token();

        $apy_key = $this->sn_options->getOption('sn_api_key');

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
}

$endpoint = new Insert_Post_Endpoint();
$endpoint->run();
