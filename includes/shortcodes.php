<?php

function get_option_shortcode( $atts ) {
    $a = shortcode_atts( array(
       'field' => ""
    ), $atts );
    return get_field($a["field"],"option");
}
add_shortcode( 'get_option', 'get_option_shortcode' );


function get_page_url_shortcode( $atts ) {
    $a = shortcode_atts( array(
       'path' => ""
    ), $atts );
    return get_permalink(get_page_by_path($a["path"]));
}
add_shortcode( 'get_page_url', 'get_page_url_shortcode' );
