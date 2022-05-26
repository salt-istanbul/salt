<?php

/*give slug get id*/
function idToSlug($page_slug) {
    $page = get_page_by_path($page_slug);
    if ($page) {
        return $page->ID;
    } else {
        return null;
    }
}

function get_leafnode_object($object, $leaf_node, $menu_items, $menu_item_parent){
	if(count($menu_items)==0){
		$menu = wp_get_nav_menu_object( 'main' );
	    $menu_items = wp_get_nav_menu_items($menu->term_id);
    }
    if($menu_items){

    	foreach($menu_items as $key=>$item){

    		if($object != ""){
    		    if($item->object == $object){
	                array_push($leaf_node, $item);
	                if($item->menu_item_parent > 0){
	                   return get_leafnode_object("", $leaf_node, $menu_items, $item->menu_item_parent);
	                   break;
	                }else{
	               	   return $leaf_node;
	               	   break;
	                }
	    		}
    		}else{
                if($menu_item_parent>0){
	    	        if($item->ID == $menu_item_parent){
	    	      	    array_push($leaf_node, $item);
	    	      	    if($item->menu_item_parent > 0){
		                   return get_leafnode_object("", $leaf_node, $menu_items, $item->menu_item_parent);
		                   break;
		                }else{
		               	  return $leaf_node;
		               	   break;
		                }
	    	        }
    	        }
    		}
    	}
    }
}


function qtranslatePostOrder($posts, $order = "asc"){
        $found_posts = array();
        foreach ( $posts as $k=>$post ) {
            $found_posts[ sanitize_title($post->title) ] = $post;
        }
        if($order=="asc"){
           ksort($found_posts);
        }else{
           krsort($found_posts);
        }
        $posts=array();
        foreach ($found_posts as $k=>$post) {
            $posts[] = $post;
        }
        return $posts;
}

function qtranslateTermOrder($terms, $order = "asc"){
	    $found_posts = array();
        foreach ( $terms as $k=>$term ) {
            $found_posts[ sanitize_title($term->name) ] = $term;
        }
        if($order=="asc"){
           ksort($found_posts);
        }else{
           krsort($found_posts);
        }
        $terms=array();
        foreach ($found_posts as $k=>$term) {
            $terms[] = $term;
        }
        return $terms;
}

function _custom_nav_menu_item( $title, $url, $order, $parent = 0 ){
	  $item = new stdClass();
	  $item->ID = 1000000 + $order + $parent;
	  $item->db_id = $item->ID;
	  $item->title = $title;
	  $item->url = $url;
	  $item->menu_order = $order;
	  $item->menu_item_parent = $parent;
	  $item->type = '';
	  $item->object = '';
	  $item->object_id = '';
	  $item->classes = array();
	  $item->target = '';
	  $item->attr_title = '';
	  $item->description = '';
	  $item->xfn = '';
	  $item->status = '';
	  return $item;
}

function wp_query_addition($args, $vars){
	if(isset($vars["taxonomy"])){
		if(!isset($args["tax_query"])){
			$args["tax_query"] = array();
		}
		$args["tax_query"]["relation"] = "AND";
		foreach($vars["taxonomy"] as $key => $tax){
			$tax_field = "slug";
			if(is_array($tax)){
				if(is_numeric($tax[0]) || ctype_digit($tax[0])){
					$tax_field = "term_id";
				}
			}
			$args["tax_query"][] = array(
				'taxonomy' => $key,
				'field'    => $tax_field,
				'terms'    => $tax,
				'operator' => 'IN',
				'include_children' => 0
			);                
		}
	}
	if(isset($vars["meta"])){
		if(!isset($args["meta_query"])){
			$args["meta_query"] = array();
		}
		$args["meta_query"]["relation"] = "AND";
		foreach($vars["meta"] as $key => $meta){
			$args["meta_query"][] = array(
				array(
					'key' => $key,
					'value' => $meta,
					'compare' => '='
				)
			);                
		}
	}
	return $args;
}

function get_page_url($slug){
	return get_permalink( get_page_by_path( $slug ) );
}