<?php

add_filter('the_content', 'filter_my_post');
function filter_my_post($content){

    global $wpdb;

    if ( is_singular() and in_the_loop() and is_main_query() ) {
        $post = get_post();
        $slug = $post->post_name;
        $website = get_site_url();
        $sql = "SELECT * FROM {$wpdb->prefix}sn_campaigns WHERE slug = '$slug' AND website_url = '$website'";
        $campaign = $wpdb->get_row($sql);

        // S'il y a une campagne d'affiliation active, alors on remplace le contenu
        if (isset($campaign) and $campaign->enable) {
            $campaign_content = $campaign->content;
            $campaign_content = str_replace('$post_id$', $post->ID, $campaign_content);
            $html = str_get_html($campaign_content);
            foreach($html->find('span[class=ob]') as $span) {
                $prop = 'data-ob';
                $href = $span->$prop;
                $span->$prop = base64_encode($href);
            }

            $campaign_content = (string)$html;
            $nb_replace = get_nb_replace(str_word_count(strip_tags($content)));
            $content = preg_replace( '/' . '<h2>'.'/', "$campaign_content <h2>", $content, $nb_replace);
        }

    }
    return $content;
}

function get_nb_replace($nb_words) {
    if ($nb_words >= 1500) {
        return 6;
    }
    else if ($nb_words >= 1000) {
        return 4;
    }
    else {
        return 2;
    }
}