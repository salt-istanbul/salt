<?php

add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', function () {
    $ids = array();
    if ( class_exists( 'WooCommerce' ) ) {
        $ids[] = get_page_by_path( 'order-received' )->ID;
        $ids[] = get_page_by_path( 'cart' )->ID;
        $ids[] = get_page_by_path( 'checkout' )->ID;
    }
    if(class_exists('Newsletter')){
        $ids[] = get_page_by_path( 'newsletter' )->ID;      
    }
    return $ids;
} );

function yoast_seo_remove_columns( $columns ) {
	/* remove the Yoast SEO columns */
	unset( $columns['wpseo-score'] );
	unset( $columns['wpseo-title'] );
	unset( $columns['wpseo-metadesc'] );
	unset( $columns['wpseo-focuskw'] );
	unset( $columns['wpseo-score-readability'] );
	unset( $columns['wpseo-links'] );
	return $columns;
}

/* remove from posts */
add_filter ( 'manage_edit-post_columns', 'yoast_seo_remove_columns' );
/* remove from pages */
add_filter ( 'manage_edit-page_columns', 'yoast_seo_remove_columns' );
/* remove from woocommerce product post type */
add_filter ( 'manage_edit-product_columns', 'yoast_seo_remove_columns' );


function wpse151723_remove_yoast_seo_posts_filter() {
    global $wpseo_metabox, $wpseo_meta_columns;
    if ( $wpseo_metabox ) {
        remove_action( 'restrict_manage_posts', array( $wpseo_metabox, 'posts_filter_dropdown' ) );
    }
    if ( $wpseo_meta_columns ) {
        remove_action( 'restrict_manage_posts', array( $wpseo_meta_columns , 'posts_filter_dropdown' ) );
        remove_action( 'restrict_manage_posts', array( $wpseo_meta_columns , 'posts_filter_dropdown_readability' ) );
    }    
}
add_action( 'admin_init', 'wpse151723_remove_yoast_seo_posts_filter', 20 );

function fix_yoast_breadcrumb_taxes($links){
	if (array_key_exists('id', $links[count($links)-1])) {
		   $post_id = $links[count($links)-1]['id'];
		   if(count($links)-2>=0){		   
		   if (array_key_exists('term', $links[count($links)-2])) {
		   	  array_pop($links);
		      $taxonomy = $links[count($links)-1]['term']->taxonomy;
		   	  array_pop($links);
			  $args = array(
						    'orderby'           => 'name', 
						    'order'             => 'ASC',
						    'hide_empty'        => false, 
						    'fields'            => 'all',
						    'hierarchical'      => true    
			  );
			  $terms = wp_get_post_terms( $post_id, $taxonomy, $args);
			  $terms_updated=array();
			  $terms = sort_terms_hierarchicaly( $terms, $terms_updated, 0);
			  $terms_updated=array();
			  $terms = sort_terms_hierarchicaly_single($terms, $terms_updated);
			  foreach($terms as $term){
			  	$links[]['term']=$term;
			  }
			  $links[]['id']=$post_id;
		    }
		}
	}
	return $links;
}

/*remove "home page" from breadcrumb*/
function remove_home_from_breadcrumb($links){
	if ($links[0]['url'] == get_site_url()."/") { 
		array_shift($links); 
	}
	return $links;
}

/*fix & add taxonomy hierarch breadcrumb*/
function fix_tax_hierarchy_on_breadcrumb($links){
    return fix_yoast_breadcrumb_taxes($links);
}

/*remove current page/post from breadcrumb*/
function remove_current_from_breadcrumb($links){
	if(count($links)>1){
		$last_item = $links[count($links)-1];
		if(!array_key_exists("term", $last_item)){
			array_pop($links);
		}		
	}
	return $links;
}

function add_parents_to_post_breadcrumb( $links ) {
    if ( !class_exists( 'WooCommerce' ) ) {
    	global $post;
		if ( is_product_category() || is_singular("product")) {
				    	$nodes = get_leafnode_object("product", array(), array(), 0);
	                    if(count($nodes)>0){
	                    	$nodes_list = array();
					        foreach($nodes as $key=>$node){
					        	if($key>0){
						        	$breadcrumb = array(
						        	    'url' => $node->url,
						                'text' => $node->title,
						        	);
						        	array_push($nodes_list,$breadcrumb);
					           }
					        }
					        array_reverse($nodes_list);
					        $links=array_merge($nodes_list, $links);
					        $links=array_unique($links);
					    }
		}
	}
	return $links;
}

//add "brand" to breadcrumb
function add_brand_to_breadcrumb($links){
    $brand_name = get_query_var( 'product_brand' );
    if ( is_product_category() && !is_tax( 'product_brand' ) && !empty($brand_name)) {
                     $brand = array(
                     	'term'=>get_term_by("slug",$brand_name,"product_brand")
                     );
				     $links_temp=array();
				     foreach($links as $link){
				   	    $links_temp[]=$link;
				   	    if(!empty($brand) && array_key_exists('ptarchive',$link) ){
				   	       if($link['ptarchive']=='product'){
				   	         $links_temp[]=$brand;
				   	       }
				   	    }
				     }
				     $links=$links_temp;  
    }
    return $links;
}

/*add single product's category to breadcrumb*/
function add_category_to_breadcrumb($links){	
    if (is_singular('product')) {
					global $post;
					//category
					$product_category = wc_get_product_terms( $post->id, 'product_cat', array( 'fields' => 'all' ) );
					if(is_array($product_category)){
						if(count($product_category)>0){
		                     $product_category=$product_category[0];
		                 }
					}
                    //brand
                    $product_brand = wc_get_product_terms( $post->id, 'product_brand', array( 'fields' => 'all' ) );
                    if(count($product_brand)>0){
                      $product_brand=$product_brand[0];
					}
				    $links_temp=array();
				    foreach($links as $link){ 
				   	    $links_temp[]=$link;
				   	    if(count($product_category)>0 && array_key_exists('term',$link) ){
				   	    	if(array_key_exists('taxonomy',$link['term'])){
				   	    		if($link['term']->taxonomy=='product_brand'){
					   	           $links_temp[]=array(
                                          "text"=>$product_category->name,
                                          "url"=>get_term_link( $product_category->term_id, 'product_cat' )."?product_brand=".$product_brand->slug
					   	           	);   
					   	        }
				   	    	}  
				   	    }
				     }
				     $links=$links_temp;
    }
    return $links;
}


function change_shopping_link_on_breadcrumb($links){
	$term_index = count($links)-1;
	if(isset($links[$term_index]["term_id"])){
		$term = get_term_by('ID', $links[$term_index]["term_id"], "magaza-tipleri");
		if(isset($term->taxonomy)){
			if($term->taxonomy == "magaza-tipleri"){
			   $page = get_page_by_path( $term->slug );
	           $links[$term_index]["url"] = get_permalink($page->ID);
			}
		}
	}
	return $links;
}

function fix_translate_on_breadcrumb($links){
	if(function_exists('qtranxf_getSortedLanguages')){
		foreach($links as $key => $link){
			$text = $link["text"];
			$text_translated = qtranxf_use( qtranxf_getLanguage(), $link["text"], false, false);
			$text_url = qtranxf_convertURL( $link["url"], qtranxf_getLanguage());
			if($text == $text_translated && isset($link["id"])){
	           $text = get_post_field( "post_title", $link["id"]);
	           $text_translated = qtranxf_use( qtranxf_getLanguage(), $text, false, false);
			}
			$links[$key]["text"] = $text_translated;
			$links[$key]["url"] = $text_url;
		}
	}
	return $links;
}



/*remove "home page" from breadcrumb*/
add_filter('wpseo_breadcrumb_links', 'remove_home_from_breadcrumb', 10, 1 ); 

/*fix & add taxonomy hierarch breadcrumb*/
//add_filter('wpseo_breadcrumb_links', 'fix_tax_hierarchy_on_breadcrumb', 10, 1 ); 

/*remove current page/post from breadcrumb*/
add_filter('wpseo_breadcrumb_links', 'remove_current_from_breadcrumb', 10, 1 ); 

//add_filter('wpseo_breadcrumb_links', 'change_shopping_link_on_breadcrumb', 10, 1 ); 

add_filter('wpseo_breadcrumb_links', 'fix_translate_on_breadcrumb', 10, 1 ); 

//add_parents_to_post_breadcrumb
//add_filter( 'wpseo_breadcrumb_links', 'add_parents_to_post_breadcrumb', 10, 1 ); 

if ( class_exists( 'WooCommerce' ) ) {
	/*add "brand" to breadcrumb*/
	// add_filter('wpseo_breadcrumb_links', 'add_brand_to_breadcrumb', 10, 1 ); 
	/*add single product's category to breadcrumb*/
	//add_filter('wpseo_breadcrumb_links', 'add_category_to_breadcrumb', 10, 1 ); 
}
