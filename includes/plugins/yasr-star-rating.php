<?php

function get_star_vote($post_id){
	global $wpdb;
	return $wpdb->get_var(  "SELECT vote FROM wp_yasr_log  where post_id = ".$post_id." order by id asc limit 1" );
}

function get_star_votes($post_id){
	global $wpdb;
	return $wpdb->get_results( 'SELECT count(*) as total,  CAST(AVG(vote) AS DECIMAL(10,1)) as point FROM wp_yasr_log WHERE post_id = '.$post_id, OBJECT );
}


//add this to yasr/lib/yasr-functions.php 449
//$filtered_schema_type = apply_filters( 'yasr_filter_schema_type', $review_choosen );
add_filter( 'yasr_filter_schema_type','yasr_schema_type');
function yasr_schema_type($type){
	global $post;
	if($post->post_type=="product"){
       $type="Product";
	}
    return $type;
}

add_filter( 'yasr_filter_schema_jsonld','yasr_schema');
function yasr_schema($type){
    return "";
}

function yasr_wpml_save($post_id, $rating) {
}
//do_action('yasr_action_on_visitor_vote', 'yasr_wpml_save', $post_id, $rating);