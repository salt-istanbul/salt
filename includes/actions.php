<?php

//remove woocommerce default css
//add_filter( 'woocommerce_enqueue_styles', '__return_false' );

function woo_dequeue_select2() {
    if ( class_exists( 'woocommerce' ) ) {
        wp_dequeue_style( 'select2' );
        wp_deregister_style( 'select2' );

        wp_dequeue_script( 'selectWoo');
        wp_deregister_script('selectWoo');
    } 
}
add_action( 'wp_enqueue_scripts', 'woo_dequeue_select2', 100 );

function add_query_vars_filter( $vars ){
  	if($GLOBALS["url_query_vars"]){
  	    $query_vars = $GLOBALS["url_query_vars"];
  	    foreach($query_vars as $query_var){
  		   $vars[] = $query_var;
  		}
  	}
  	return $vars;
}
add_filter( 'query_vars', 'add_query_vars_filter' );


function ns_filter_avatar($avatar, $id_or_email, $size, $default, $alt, $args) {
    $headers = @get_headers( $args['url'] );
    if( ! preg_match("|200|", $headers[0] ) ) {
        return;
    }
    return $avatar; 
}
//add_filter('get_avatar','ns_filter_avatar', 10, 6);



//remove dashicons
function wpdocs_dequeue_dashicon() {
    if (current_user_can( 'update_core' )) {
        return;
    }
    wp_deregister_style('dashicons');
}
add_action( 'wp_enqueue_scripts', 'wpdocs_dequeue_dashicon' );


//remove empty <p> tags
function remove_empty_p( $content ) {
    $content = force_balance_tags( $content );
    $content = preg_replace( '#<p>\s*+(<br\s*/*>)?\s*</p>#i', '<br/>', $content );
    $content = preg_replace( '~\s?<p>(\s|&nbsp;)+</p>\s?~', '<br/>', $content );
    return $content;
}
add_filter('the_content', 'remove_empty_p', 20, 1);



/*add responsive classes to embeds*/
function responsive_embed_oembed_html($html, $url, $attr, $post_id) {
	  if (strpos($url, 'youtube.')||strpos($url, 'youtu.be')||strpos($url, 'vimeo.')||strpos($url, 'dailymotion.')){
	     return '<div class="embed-responsive embed-responsive-16by9">' . $html . '</div>';
	  }else{
		 return $html;  
	  }
}
add_filter('embed_oembed_html', 'responsive_embed_oembed_html', 99, 4);



//add responsive image classes to images who added from text editor
function add_image_responsive_class($content) {
	   global $post;
	   $pattern ="/<img(.*?)class=\"(.*?)\"(.*?)>/i";
	   $replacement = '<img$1class="$2 img-fluid-center lazy" itemprop="image" $3>';
	   $content = preg_replace($pattern, $replacement, $content);
	   /*add imageobject*/
	   $pattern ="/<img(.*?)src=\"(.*?)\"(.*?)>/i";
	   $replacement = '<a href="$2" class="ilightbox"><img$1data-src="$2"$3><span itemtype="http://schema.org/ImageObject" itemscope=""><meta content="$2" itemprop="contentUrl"></span></a>';
	   $content = preg_replace($pattern, $replacement, $content);
	   return $content;
}
add_filter('the_content', 'add_image_responsive_class');



//translate select menu contents
function add_qtrans_to_types($field_value_array) {
	if ( is_array( $field_value_array ) ) {
		foreach ( $field_value_array as $f_key => $f_value ) {
			$field_value_array[$f_key] = __( $f_value );
		}
	} else {
		$field_value_array = __( $field_value_array );
	}
	return $field_value_array;
}
add_filter( 'wpcf_fields_value_display', 'add_qtrans_to_types', 10, 5 );


//remove woocommerce default css
function dequeue_woo_styles( $enqueue_styles ) {
	unset( $enqueue_styles['woocommerce-general'] );	// Remove the gloss
	unset( $enqueue_styles['woocommerce-layout'] );		// Remove the layout
	unset( $enqueue_styles['woocommerce-smallscreen'] );	// Remove the smallscreen optimisation
	unset( $enqueue_styles['woocommerce-inline-inline-css'] );
	return $enqueue_styles;
}


function old_style_name_like_wpse_123298($clauses) {
    remove_filter('term_clauses','old_style_name_like_wpse_123298');
	$pattern = '|(name LIKE )\'{.*?}(.+{.*?})\'|';
	$clauses['where'] = preg_replace($pattern,'$1 \'$2\'',$clauses['where']);
	return $clauses;
}
add_filter('terms_clauses','old_style_name_like_wpse_123298');


//remove image sizes
function remove_default_image_sizes( $sizes ) {
  /* Default WordPress */
  /*unset( $sizes[ 'thumbnail' ]);       // Remove Thumbnail (150 x 150 hard cropped)
  unset( $sizes[ 'medium' ]);          // Remove Medium resolution (300 x 300 max height 300px)
  unset( $sizes[ 'medium_large' ]);    // Remove Medium Large (added in WP 4.4) resolution (768 x 0 infinite height)
  unset( $sizes[ 'large' ]);           // Remove Large resolution (1024 x 1024 max height 1024px)*/
  unset( $sizes[ '1536x1536' ]);
  unset( $sizes[ '2048x2048' ]);
  
  /* With WooCommerce */
  unset( $sizes[ 'woocommerce_thumbnail' ]);
  unset( $sizes[ 'woocommerce_single' ]);
  unset( $sizes[ 'woocommerce_gallery_thumbnail' ]);
  unset( $sizes[ 'user-mini' ]);
  unset( $sizes[ 'shop_thumbnail' ]);  // Remove Shop thumbnail (180 x 180 hard cropped)
  unset( $sizes[ 'shop_catalog' ]);    // Remove Shop catalog (300 x 300 hard cropped)
  unset( $sizes[ 'shop_single' ]);     // Shop single (600 x 600 hard cropped)
  
  return $sizes;
}
add_filter( 'intermediate_image_sizes_advanced', 'remove_default_image_sizes' );



function bootstrap_gallery( $output = '', $atts, $instance ){ //instance : gallery index (start from1)
    if (!isset($atts['columns'])) {
        $columns = 3;
    }else {
        $columns = $atts['columns'];
    }
    $images = explode(',', $atts['ids']);
    if ($columns < 1 || $columns > 12) {
        $columns == 3;
    }
    $col_class = 'col-md-' . 12/$columns;
    $return = '<div class="row row-margin content-gallery">';
    $i = 0;
    foreach ($images as $key => $value) {
        if ($i%$columns == 0 && $i > 0) {
            $return .= '</div><div class="row row-margin content-gallery">';
        }
        $image_attributes = wp_get_attachment_image_src($value, 'full');
        $return .= '
            <div class="'.$col_class.'">
                <div class="gallery-image-wrap">
                    <a data-gallery="gallery" href="'.$image_attributes[0].'" class="ilightbox">
                        <img src="'.$image_attributes[0].'" alt="" class="img-fluid lazyload">
                    </a>
                </div>
            </div>';
        $i++;
    }
    $return .= '</div>';
    return $return;
}
add_filter( 'post_gallery', 'bootstrap_gallery', 10, 4);



//add_filter('request', 'rudr_change_term_request', 1, 1 );
function rudr_change_term_request($query){
    return $query;
}









function custom_rewrite_rules() {
    flush_rewrite_rules();

    add_rewrite_rule( 
        '^brief/([^/]*)/?',
        'index.php?pagename=brief&work-type=$matches[1]',
        'top' 
    );

    add_rewrite_rule( 
        '^suppliers/packages/([^/]*)/?',
        'index.php?pagename=suppliers&action=packages&work-type=$matches[1]',
        'top' 
    );

    add_rewrite_rule( 
        '^suppliers/search/([^/]*)/?',
        'index.php?pagename=suppliers&action=search&work-type=$matches[1]',
        'top' 
    );

    add_rewrite_rule( 
        '^suppliers/([^/]*)/?',
        'index.php?pagename=suppliers&action=$matches[1]',
        'top' 
    );

    add_rewrite_rule( 
        '^ads/create/([^/]*)/([^/]*)/?',
        'index.php?pagename=ads&action=create&work-type=$matches[1]&pack=$matches[2]',
        'top' 
    );    

    add_rewrite_rule( 
        '^ads/create/([^/]*)/?',
        'index.php?pagename=ads&action=create&work-type=$matches[1]',
        'top' 
    );

    add_rewrite_rule( 
        '^ads/search/([^/]*)/?',
        'index.php?pagename=ads&action=search&work-type=$matches[1]',
        'top' 
    );

    add_rewrite_rule( 
        '^ads/edit/([^/]*)/?',
        'index.php?pagename=ads&action=edit&project=$matches[1]',
        'top' 
    );

    add_rewrite_rule( 
        '^ads/([^/]*)/brief/?',
        'index.php?pagename=ads&action=brief&work-type=$matches[1]',
        'top' 
    );

    add_rewrite_rule( 
        '^ads/([^/]*)/?',
        'index.php?pagename=ads&action=$matches[1]',
        'top' 
    );



    add_rewrite_rule( 
        '^projects/([^/]*)/([^/]*)/?',
        'index.php?posttype=projects&projects=$matches[1]&action=$matches[2]',
        'top' 
    );
}
add_action('init', 'custom_rewrite_rules');





/*add_filter( 'login_redirect', function ( $redirect_to, $requested_redirect_to ) {
    if ( ! $requested_redirect_to ) {
        $redirect_to = wp_get_referer();
    }

    return $redirect_to;
}, 10, 2 );*/