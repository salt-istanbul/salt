<?php

//remove cf7 style & scripts
//add_filter( 'wpcf7_load_js', '__return_false' );
//add_filter( 'wpcf7_load_css', '__return_false' );


function cf7_add_post_id(){
    global $post;
    return $post->ID;
}
add_shortcode('CF7_ADD_POST_ID', 'cf7_add_post_id');


function wpcf7_autop_return_false() {
    return false;
} 
add_filter('wpcf7_autop_or_not', 'wpcf7_autop_return_false');


function my_wpcf7_form_elements($html) {
    $text = 'Please Choose...';
    $html = str_replace('<option value="">---</option>', '<option value="">' . $text . '</option>', $html);
    return $html;
}
//add_filter('wpcf7_form_elements', 'my_wpcf7_form_elements');


//Dynamic content shortcodes for Contact Form 7
function cf7_cpt_select_menu ( $tag, $unused ) {  

    $options = (array) $tag['options'];

    foreach ( $options as $option ) {   
        if ( preg_match( '%^posttax:([-0-9a-zA-Z_]+)$%', $option, $matches ) ) {
             $post_tax = $matches[1];
        }
        if ( preg_match( '%^posttype:([-0-9a-zA-Z_]+)$%', $option, $matches ) ) {
             $post_type = $matches[1];
        }
        if ( preg_match( '%^postid:([-0-9a-zA-Z_]+)$%', $option, $matches ) ) {
             $post_id = $matches[1];
        }
    }
    
    //check if post_type is set
    if(!isset($post_tax) && !isset($post_type)) {
        return $tag;
    }

    if(isset($post_tax)){
            $terms = Timber::get_terms($post_tax); 

            if ( ! $terms )  
                return $tag;  

            $terms_sorted = $terms;//sort_terms_hierarchicaly_single($terms,array());
            $added_items = array();
            $group_name = "";

            /*$tag['raw_values'][] = 'General Application';//__('General Application', $GLOBALS['text_domain']);  
            $tag['values'][] = 'General Application';//__('General Application', $GLOBALS['text_domain']);   
            $tag['labels'][] = 'General Application';//__('General Application', $GLOBALS['text_domain']);*/

            foreach ( $terms_sorted as $term ) {
                if(count($added_items)==0){
                   $group_name = $term->name;
                }
                if(count($added_items)>0 && $term->children){
                    $tag['raw_values'][] = "endoptgroup";  
                    $tag['values'][] = "";  
                    $tag['labels'][] = "endoptgroup";
                    $group_name = $term->name;
                }
                $label_name = ($term->children?"optgroup-":"").$term->name;
                if(!in_array($label_name,$added_items)){
                    $tag['raw_values'][] = $term->name;  
                    $tag['values'][] = count($term->children)==0?$term->term_id:$group_name;//count($term->children)==0?$group_name." - ".$term->name:$group_name;  
                    $tag['labels'][] = $label_name;
                    $tag['options'][] = "class:cf7-optgroup";
                    $added_items[] = $label_name;
                }   
            }
    }

     if(isset($post_type)){
       $items = Timber::get_posts('post_type='.$post_type.'&numberposts=-1');
       foreach ( $items as $item ) {
            $tag['raw_values'][] = $item->title;  
            $tag['values'][] = $item->title;  
            $tag['labels'][] = $item->title;
        }
    }
    return $tag;  
}  
add_filter( 'wpcf7_form_tag', 'cf7_cpt_select_menu', 10, 2);







function custom_shortcode_atts_wpcf7_filter( $out, $pairs, $atts ) {
    $my_attr = 'defaults';
    if ( isset( $atts[$my_attr] ) ) {
        $out[$my_attr] = $atts[$my_attr];
    }
    return $out;
}
add_filter( 'shortcode_atts_wpcf7', 'custom_shortcode_atts_wpcf7_filter', 10, 3 );




function get_cf7_forms(){
    $forms = array();
    if (class_exists("WPCF7")) {
        $acf_forms = get_field("forms", "options");
        if($acf_forms){
            foreach($acf_forms as $form){
                $forms[$form["slug"]] = array(
                    "title"       => $form["title"],
                    "description" => $form["description"],
                    "form"        => $form["form"]
                );
            }        
        }
    }
    return $forms;
}
