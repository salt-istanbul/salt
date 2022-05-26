<?php


function get_archive_first_post($post_type){
	$args = array(
	    'post_type' => $post_type,
	    'numberposts' => 1
	);
	$post = Timber::get_post($args);
	if($post){
       return  wp_get_attachment_url( get_post_thumbnail_id($post->id) );
	}else{
	   return "";
	}
}
/*post's categories*/
function post_categories($post_id){
    $post_categories = wp_get_post_categories( $post_id );
	$cats = array();
	foreach($post_categories as $c){
		$cat = get_category( $c );
		$cats[] = array( 'id' => $cat->cat_ID, 'name' => $cat->name, 'slug' => $cat->slug , 'url' => get_category_link($cat->cat_ID));
	}
	return $cats;
}

/*post's tags*/
function post_tags($post_id){
    $post_tags = wp_get_post_tags( $post_id );
	$tags = array();
	foreach($post_tags as $c){
		$tag = get_tag( $c );
		$tags[] = array( 'id' => $tag->term_id, 'name' => $tag->name, 'slug' => $tag->slug , 'url' => get_tag_link($tag->term_id));
	}
	return $tags;
}

function get_thumbnail_from_posts($posts){
	foreach($posts as $post){
		if($post->thumbnail){
			return new TimberImage($post->thumbnail->id);
			exit;
		}
	}
}

function get_parent_top($post){
	if ($post->post_parent)	{
		$ancestors=get_post_ancestors($post->ID);
		$root=count($ancestors)-1;
		return $ancestors[$root];
	} else {
		return $post->ID;
	}	
}

function posts_to_menu($items){
	  $menu_order = count($items);
	  $child_items = array();
	  foreach ( $items as $item ) {
	      $item->title = $item->post_title;
	      $item->url =  get_permalink($item->ID);
	      $item->menu_item_parent = $item->post_parent;
	      $item->post_type = 'nav_menu_item';
	      $item->object = 'custom';
	      $item->type = 'custom';
	      $item->menu_order = ++$menu_order;
	      $child_items []= $item;
	  } 
	  return $child_items ;
}

function post_is_exist($id){
    return is_string( get_post_status( $id ) );
}