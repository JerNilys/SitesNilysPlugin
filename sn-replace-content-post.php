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

        if ($campaign->enable) {
            $nb_replace = get_nb_replace(str_word_count(strip_tags($content)));
            $content_to_replace = $campaign->content;
            $content = preg_replace( '/' . '<h2>'.'/', "$content_to_replace <h2>", $content, $nb_replace);
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