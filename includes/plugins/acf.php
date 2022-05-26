<?php


function my_acf_json_save_point( $path ) {
    $path = get_stylesheet_directory() . '/acf-json';
    return $path;  
}
function my_acf_json_load_point( $paths ) {
    unset($paths[0]);
    $paths[] = get_stylesheet_directory() . '/acf-json';
    return $paths;
}

function acf_main_location($locations){
	if(!empty($locations)){
	   foreach($locations as $location){
	   	  if($location["contact"]["main"]){
	   	  	 return $location["contact"];
	   	  	 break;
	   	  }
	   }
	}
}


/*Set as featured image custom image fields value*/
function acf_set_video_image( $value, $post_id, $field ){
	     //$file = get_template_directory() . '/static/test.txt';
	     if(acf_set_thumbnail_condition($post_id)){

	        if(isset($value) && $value != '') {

                if($field['type'] == 'repeater'){
                    $row_index = 0;
                  	if( have_rows($field['name']) ) :
			 	        while( have_rows($field['name']) ) : the_row();

							 	   $field_name = 'video';
							 	   $field_value = extract_url(get_sub_field($field_name));
							 	   
							 	   $field_video_name = $field['name'].'_'.$row_index.'_'.$field_name;
							 	   $field_image_name = $field['name'].'_'.$row_index.'_'.$field_name.'_image';
							 	   $field_locked_name = $field['name'].'_'.$row_index.'_locked';
							 	   $image_size = 1600;

							 	   $is_locked = get_sub_field('locked');//get_post_meta( $post_id, $field_locked_name, true );

							 	   if(empty($is_locked)){
							 	   	  $is_locked = false;
							 	   }else{
							 	   	  $is_locked = true;
							 	   }  
							 	   $lolo .= $field_video_name.' = '.$field_value;	 
							 	   $lolo .= 'locked='.$is_locked; 
							 	   if(!$is_locked){
				                       $field_attach_id = get_post_meta( $post_id, $field_image_name, true );
				                       if($field_attach_id){
				                          wp_delete_attachment( $field_attach_id, true );
				                       }
				                       ///////$field_value = get_post_meta( $post_id, $field_video_name, true );
				                       $thumbnail_uri = get_video_thumbnail_uri($field_value, $image_size );
									   $attach_id = featured_image_from_url($thumbnail_uri, $post_id, true);
									   update_post_meta($post_id, $field_image_name, $attach_id);
				                       //$lolo .= '$thumbnail_uri='.$thumbnail_uri; 
				                       //$lolo .= '$attach_id='.$attach_id; 
					               }
				 	        // }
					               $row_index++; 
                        endwhile; 
				    endif;

			 	}

	        }
			//file_put_contents($file,$lolo);
	    }
        return $value;
}

function acf_set_thumbnail_condition($post_id){
	$excluded_post_types = array();
	$post_type = get_post_type( $post_id );
	if(in_array($post_type, $excluded_post_types)){
	    return false;
	}else{
	    return true;
	}
}

/*Set as featured image custom image fields value*/
function acf_set_featured_image( $value, $post_id, $field  ){
		if(acf_set_thumbnail_condition($post_id)){
			if($field['type'] == "qtranslate_image"){
			   $languages = qtranxf_getSortedLanguages();
			   $value = qtranxf_use($languages[0], $value, false, false);
			}
		    if($value != '' && $value != null && !empty($value)){
				 delete_post_thumbnail( $post_id);
				 if($field['type'] == 'flexible_content'){
				 	$feature_saved = false;
				 	foreach ($value as $row_index => $row){
						$layout = $row;
					 	if($layout=='images' && !$feature_saved){
				           $field_name = 'image';
				           $field_image_name = $field['name'].'_'.$row_index.'_'.$field_name;
	                       $field_attach_id = get_post_meta( $post_id, $field_image_name, true );
	                       if(empty($field_attach_id)){
	                         $field_value = get_sub_field($field_name);
		                     $attach_id = featured_image_from_url($field_value, $post_id);
	                       }
	                       add_post_meta($post_id, '_thumbnail_id', $field_attach_id);
		                   $feature_saved = true;
					 	}
					 	if($layout=='videos'){
					 	   $field_name = 'video';
					 	   $field_video_name = $field['name'].'_'.$row_index.'_'.$field_name;
					 	   $field_image_name = $field['name'].'_'.$row_index.'_'.$field_name.'_image';
					 	   $field_locked_name = $field['name'].'_'.$row_index.'_locked';
					 	   $image_size = 1600;
					 	   $is_featured = $row_index>0?false:true;

					 	   $is_locked = get_post_meta( $post_id, $field_locked_name, true );
					 	   if(empty($is_locked)){
					 	   	  $is_locked = false;
					 	   }else{
					 	   	  $is_locked = true;
					 	   }
					 	   if(!$is_locked){
		                       $field_attach_id = get_post_meta( $post_id, $field_image_name, true );
		                       if($field_attach_id){
		                          wp_delete_attachment( $field_attach_id, true );
		                       }
		                       $field_value = get_post_meta( $post_id, $field_video_name, true );
		                       $thumbnail_uri = get_video_thumbnail_uri($field_value, $image_size );
							   $attach_id = featured_image_from_url($thumbnail_uri, $post_id, $is_featured);
							   update_post_meta($post_id, $field_image_name, $attach_id);
			               }
		                   $feature_saved = true;
					 	}
				 	}
				 }else{

	                 if($field['type'] == 'repeater'){
	                 	$feature_saved = false;
				 	    foreach ($value as $row_index => $row){
				 	    	    if($field['name']=='video'){
							 	   $field_name = 'video';
							 	   $field_video_name = $field['name'].'_'.$row_index.'_'.$field_name;
							 	   $field_image_name = $field['name'].'_'.$row_index.'_'.$field_name.'_image';
							 	   $field_locked_name = $field['name'].'_'.$row_index.'_locked';
							 	   $image_size = 1600;
							 	   $is_featured = $row_index>0?false:true;

							 	   $is_locked = get_post_meta( $post_id, $field_locked_name, true );
							 	   if(empty($is_locked)){
							 	   	  $is_locked = false;
							 	   }else{
							 	   	  $is_locked = true;
							 	   }
							 	   if(!$is_locked){
				                       $field_attach_id = get_post_meta( $post_id, $field_image_name, true );
				                       if($field_attach_id){
				                          wp_delete_attachment( $field_attach_id, true );
				                       }
				                       $field_value = get_post_meta( $post_id, $field_video_name, true );
				                       $thumbnail_uri = get_video_thumbnail_uri($field_value, $image_size );
									   $attach_id = featured_image_from_url($thumbnail_uri, $post_id, $is_featured);
									   update_post_meta($post_id, $field_image_name, $attach_id);
					               }
				                   $feature_saved = true;
					 	         }
				 	    }

	                 }else{
                         
						 if(is_array($value)){
							$meta_id =add_post_meta($post_id, '_thumbnail_id', $value[0]);
						 }else{
					        $meta_id = add_post_meta($post_id, '_thumbnail_id', $value);
						 }
					}
				}
		    }else{
				delete_post_thumbnail( $post_id );
			};
		};
	    return $value;
}

function create_options_menu($options){
	if(array_iterable($options)){
		$menu_title = $options['title'];
		acf_add_options_page(array(
			'page_title' 	=> $menu_title,
			'menu_title'	=> $menu_title,
			'menu_slug' 	=> sanitize_title($menu_title),
			'capability'	=> 'edit_posts',
			'redirect'		=> true
		));
		$menu_children=$options['children'];
		if($menu_children){	
		   for($i = 0; $i < count($menu_children); $i++){
			   acf_add_options_sub_page(array(
					'page_title' 	=> $menu_children[$i],
					'menu_title'	=> $menu_children[$i],
					'menu_slug' 	=> sanitize_title($menu_children[$i]),
					'parent_slug'	=> sanitize_title($menu_title),
				));
		   }
		}
	}
};


function acf_oembed_data($data, $image_size=0){
	if(!empty($data)){
		$url=extract_url($data);
		$data_parse=parse_video_uri( $url );
		$arr = array(
		       'type' => $data_parse['type'],
			   'id' => str_replace('video/','',$data_parse['id']),
			   'url' => $url."&controls=0",
			   'embed' => $data,
			   'watch' => "https://www.".($data_parse['type']=="vimeo"?"vimeo.com/":"youtube.com/watch?v=").$data_parse['id']
	    );
	    if($image_size>0){
	    	$arr["src"] = get_video_thumbnail_uri( $url, $image_size );
	    }
		return $arr;
	}
}

function acf_oembed_url($data){
	if(!empty($data)){
		return extract_url($data);
	}
}
function acf_oembed_id($data){
	if(!empty($data)){
		$url=extract_url($data);
		$data_parse=parse_video_uri( $url );
		return str_replace('video/','',$data_parse['id']);
	}
}


function acf_map_data($location, $className="", $id="", $icon=""){
	$result = array();
	if($location){
	    $staticMarker = 'color:red%7C' . $location['lat'] . ',' . $location['lng'];
		if(!empty($icon)){
			$staticMarker = "icon:".$icon."%7C".$location['lat'].",".$location['lng'];
		}
		$result = array(
			       'lng' => $location['lng'],
				   'lat' => $location['lat'],
				   'zoom' => $location['zoom'],
				   'icon' => $icon,
			       'src' => 'http://maps.googleapis.com/maps/api/staticmap?center=' . urlencode( $location['lat'] . ',' . $location['lng'] ). '&zoom='.$location['zoom'].'&size=800x800&maptype=roadmap&sensor=false&markers='.$staticMarker.'&key='.$GLOBALS['google_maps_api_key'],
				   'url' => 'http://www.google.com/maps/@'. $location['address'] ,
				   'url_iframe' => 'https://www.google.com/maps/embed/v1/place?key='.$GLOBALS['google_maps_api_key'].'&q='.$location['lat'] . ',' . $location['lng'],
				   'embed' => '<div id="'.$id.'" class="'.$className.' map-google" data-lat="'.$location['lat'].'" data-lng="'.$location['lng'].'" data-zoom="'.$location['zoom'].'" data-icon="'.$icon.'"></div>'
			   );			
	}
	return $result;
}


function service_value($obj,$obj_key){
	/*Available keys
	  name
	  account_id
	  account_name
	  url
	  app_id
	  app_secret
	  app_redirect
	  app_token
	  app_token_secret
	  application_scope
	  app_public_scope
    */
   $services = get_field('services', 'option');
   foreach($services as $key => $service){
      if ( $service['service_name'] === $obj )
	     $val = $services[$key]['service_'.$obj_key];
   }
   return is_array($val)?implode(',',$val):$val;
}



function acf_get_field_key( $field_name, $post_id ) {
	global $wpdb;
	$acf_fields = $wpdb->get_results( $wpdb->prepare( "SELECT ID,post_parent,post_name FROM $wpdb->posts WHERE post_excerpt=%s AND post_type=%s" , $field_name , 'acf-field' ) );
	// get all fields with that name.
	switch ( count( $acf_fields ) ) {
		case 0: // no such field
			return false;
		case 1: // just one result. 
			return $acf_fields[0]->post_name;
	}
	// result is ambiguous
	// get IDs of all field groups for this post
	$field_groups_ids = array();
	$field_groups = acf_get_field_groups( array(
		'post_id' => $post_id,
	) );
	foreach ( $field_groups as $field_group )
		$field_groups_ids[] = $field_group['ID'];
	
	// Check if field is part of one of the field groups
	// Return the first one.
	foreach ( $acf_fields as $acf_field ) {
		if ( in_array($acf_field->post_parent,$field_groups_ids) )
			return $acf_field->post_name;
	}
	return false;
}

/*
function acf_get_field_key($field_name, $post_id = false){
	
	if ( $post_id )
		return get_field_reference($field_name, $post_id);
	
	if( !empty($GLOBALS['acf_register_field_group']) ) {
		
		foreach( $GLOBALS['acf_register_field_group'] as $acf ) :
			
			foreach($acf['fields'] as $field) :
				
				if ( $field_name === $field['name'] )
					return $field['key'];
			
			endforeach;
			
		endforeach;
	}
    return $field_name;
}
*/


function get_contact_forms(){
	$arr = array();
	$forms = get_field("forms", "option");
	foreach($forms as $form){
		$arr[$form["slug"]] = array(
			"id"          => $form["form"],
            "title"       => $form["title"],
            "description" => $form["description"]
		);
	}
	return $arr;
}


/*acf Google Maps key*/
acf_update_setting('google_api_key', $GLOBALS['google_maps_api_key']);

//acf json save & load folders
add_filter('acf/settings/save_json', 'my_acf_json_save_point');
add_filter('acf/settings/load_json', 'my_acf_json_load_point');

if(class_exists('ACFE')){
	/*add_action('acfe/validate_save_post/post_type=product', 'my_acfe_validate_save_page', 10, 2);
	function my_acfe_validate_save_page($post_id, $object){
		//set featured image
		$value = get_field('image', $post_id);
		$field = get_field_object('image', $post_id);
		acf_set_featured_image( $value, $post_id, $field );
		
		//set gallery images
		$value = get_field('product_gallery', $post_id);
		$field = get_field_object('gallery', $post_id);
		delete_post_meta($post_id, '_product_image_gallery');
		if($value != '' && $value != null && !empty($value)){
			$value = join(",", $value);
			add_post_meta($post_id, '_product_image_gallery', $value, true);
		}
	}*/
}else{
	//set featured image
	add_filter('acf/update_value/name=image', 'acf_set_featured_image', 10, 3);            	
}




if (function_exists('icl_get_languages') || function_exists('qtranxf_getSortedLanguages') ){

	add_filter('acf/settings/default_language', 'my_acf_settings_default_language');
	function my_acf_settings_default_language( $language ) {
		if (!function_exists('icl_get_languages')) {
			return qtranxf_getLanguage();
		}else{
			global $sitepress;
			return $sitepress->get_default_language();
		}
	}

	add_filter('acf/settings/current_language', 'my_acf_settings_current_language');
	function my_acf_settings_current_language( $language ) {
		if (!function_exists('icl_get_languages')) {
			return qtranxf_getLanguage();
		}else{
			global $sitepress;
			return  ICL_LANGUAGE_CODE;
		}
	}
	
}