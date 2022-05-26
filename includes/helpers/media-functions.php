<?php

function get_embed_video_data($url){
	$dimensions = array(
       "width"  => "100%",
       "height" => "100%"
	);
	$video = parse_video_uri( $url );
	if ( $video['type'] == 'youtube' ){
        $api_url = 'https://noembed.com/embed?url=' . urlencode($url);	
	}
	if ($video['type'] == 'vimeo'){
		$api_url = 'https://vimeo.com/api/oembed.json?url=' . urlencode($url);	
	}
	$response = json_decode(file_get_contents($api_url));
	if( is_wp_error( $response ) ) {
		return $dimensions;
	} else {
		$response = json_decode(json_encode($response), true);
        return array(
        	"width"  => $response["width"],
        	"height" => $response["height"]
        );
	}
}

function set_embed_lazy($code){
    if(empty($code)){
		return $code;
	}
	$code = str_replace("<iframe ", "<iframe class='lazy' ", $code);
	$code = str_replace("src=", "data-src=", $code);
	return $code;
}

/* Pull apart OEmbed video link to get thumbnails out*/
function get_video_thumbnail_uri( $video_uri, $image_size ) {
	$thumbnail_uri = '';
	$video = parse_video_uri( $video_uri );		
	
	// get youtube thumbnail
	if ( $video['type'] == 'youtube' )
		$thumbnail_uri = 'http://img.youtube.com/vi/' . $video['id'] . '/maxresdefault.jpg';
	
	// get vimeo thumbnail
	if( $video['type'] == 'vimeo' )
		$thumbnail_uri = get_vimeo_thumbnail_uri( $video['id'], $image_size );

	// get default/placeholder thumbnail
	if( empty( $thumbnail_uri ) || is_wp_error( $thumbnail_uri ) )
		$thumbnail_uri = ''; 
	
	//return thumbnail uri
	return $thumbnail_uri;
}


/* Parse the video uri/url to determine the video type/source and the video id */
function parse_video_uri( $url ) {
	
	// Parse the url 
	$parse = parse_url( $url );

	// Set blank variables
	$video_type = '';
	$video_id = '';
	
	// Url is http://youtu.be/xxxx
	if ( $parse['host'] == 'youtu.be' ) {
		$video_type = 'youtube';
		$video_id = ltrim( $parse['path'],'/' );	
	}
	
	// Url is http://www.youtube.com/watch?v=xxxx 
	// or http://www.youtube.com/watch?feature=player_embedded&v=xxx
	// or http://www.youtube.com/embed/xxxx
	if ( ( $parse['host'] == 'youtube.com' ) || ( $parse['host'] == 'www.youtube.com' ) ) {
	
		$video_type = 'youtube';
		parse_str( $parse['query'], $output);

		if ( !empty( $output["feature"] ) ){
			$video_id = explode( 'v=', $parse['query'] ) ;
			$video_id = end( $video_id );
		}
			
		if ( strpos( $parse['path'], 'embed' ) == 1 ){
			$video_id = explode( '/', $parse['path'] );
			$video_id = end( $video_id );
		}
	}

	// Url is http://www.vimeo.com
	if ( ( $parse['host'] == 'vimeo.com' ) || ( $parse['host'] == 'www.vimeo.com' ) || ( $parse['host'] == 'player.vimeo.com' )  ) {
		$video_type = 'vimeo';
		$video_id = ltrim( $parse['path'],'/' );					
	}
	$host_names = explode(".", $parse['host'] );
	$rebuild = ( ! empty( $host_names[1] ) ? $host_names[1] : '') . '.' . ( ! empty($host_names[2] ) ? $host_names[2] : '');
	
	if ( !empty( $video_type ) ) {
		$video_array = array(
			'type' => $video_type,
			'id' => $video_id
		);
		return $video_array;
	} else {
		return false;
	}
}


/* Takes a Vimeo video/clip ID and calls the Vimeo API v2 to get the large thumbnail URL.*/
function get_vimeo_thumbnail_uri( $clip_id="", $image_size=0 ) {
	//$vimeo_api_uri = 'http://vimeo.com/api/v2/' . $clip_id . '.php';
	$vimeo_api_uri = 'https://vimeo.com/api/oembed.json?url=https%3A//vimeo.com/' . $clip_id;
	$vimeo_response = json_decode(file_get_contents($vimeo_api_uri));//wp_remote_get( $vimeo_api_uri );
	if( is_wp_error( $vimeo_response ) ) {
		return $vimeo_response;
	} else {
		$url = $vimeo_response->thumbnail_url;
		if(!empty($image_size)){
			$url = str_split($url, stripos($url,'_'));
			return $url[0].'_'.$image_size.'.jpg';
		}else{
		   return $url;
		}
	}
	
}

function featured_image_from_url($image_url, $post_id, $featured=false, $name="", $name_addition=true){
		  $upload_dir = wp_upload_dir(); // Set upload folder
		  $image_data = file_get_contents($image_url); // Get image data
		  $filename   = basename($image_url); // Create image file name
		  
		  $info = pathinfo($image_url);
		  //dirname   = File Path
		  //basename  = Filename.Extension
		  //extension = Extension
		  //filename  = Filename

		  if(!empty($name)){
		  	$info['filename'] = $name;
		  }
		  $name_addition_text = "";
		  if($name_addition){
             $name_addition_text = '-'.$post_id.'-'.get_random_number(111,999);
		  }
		  
		  // Check folder permission and define file location
		  if( wp_mkdir_p( $upload_dir['path'] ) ) {
			  $file = $upload_dir['path'] . '/' . $info['filename'].$name_addition_text.'.'.$info['extension'];
		  } else {
			  $file = $upload_dir['basedir'] . '/' . $info['filename'].$name_addition_text.'.'.$info['extension'];
		  }
		  
		  // Create the image  file on the server
		  file_put_contents( $file, $image_data );
		  
		  // Check image file type
		  $wp_filetype = wp_check_filetype( $filename, null );
		  
		  // Set attachment data
		  $attachment = array(
			  'post_mime_type' => $wp_filetype['type'],
			  'post_title'     => sanitize_file_name( $filename ),
			  'post_content'   => '',
			  'post_status'    => 'inherit'
		  );
		  
		  // Create the attachment
		  $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
		  
		  // Include image.php
		  require_once(ABSPATH . 'wp-admin/includes/image.php');
		  
		  // Define attachment metadata
		  $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
		  
		  // Assign metadata to attachment
		  wp_update_attachment_metadata( $attach_id, $attach_data );
		  
		  // And finally assign featured image to post
		  if($featured){
		     set_post_thumbnail( $post_id, $attach_id );
		  }
		  return  $attach_id;
}

function insert_attachment($file_handler,$post_id,$setthumb='false') {

  // check to make sure its a successful upload
  //if ($_FILES[$file_handler]['error'] !== UPLOAD_ERR_OK) __return_false();

	//print_r($file_handler);

  require_once(ABSPATH . "wp-admin" . '/includes/image.php');
  require_once(ABSPATH . "wp-admin" . '/includes/file.php');
  require_once(ABSPATH . "wp-admin" . '/includes/media.php');

  $attach_id = media_handle_upload( $file_handler, $post_id );

  if ($setthumb) update_post_meta($post_id,'_thumbnail_id',$attach_id);
  return $attach_id;
}

function _get_all_image_sizes() {
    global $_wp_additional_image_sizes;
    $default_image_sizes = get_intermediate_image_sizes();
    foreach ( $default_image_sizes as $size ) {
        $image_sizes[ $size ][ 'width' ] = intval( get_option( "{$size}_size_w" ) );
        $image_sizes[ $size ][ 'height' ] = intval( get_option( "{$size}_size_h" ) );
        $image_sizes[ $size ][ 'crop' ] = get_option( "{$size}_crop" ) ? get_option( "{$size}_crop" ) : false;
    }
    if ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) ) {
        $image_sizes = array_merge( $image_sizes, $_wp_additional_image_sizes );
    }
    return $image_sizes;
}



function inline_svg($url="", $class=""){
	$svg = "";
	if(!empty($url)){
		$svg = file_get_contents($url);
		if(!empty($class)){
			$svg = str_replace("<svg ", "<svg class='".$class."' ", $svg);
		}
	}
    return $svg;
}

function media_placeholder($data=array(), $type="", $obj=array(), $class=""){
	$id = "media-".unique_code(8);
	if(!isset($obj["parallax"])){
		$obj["parallax"] = 0;
	}
	$code = "";
	switch($type){

		case "image" :
		   if(!$obj["parallax"]){
               $height = $data["height"] / $data["width"] * 100;
               $code .= "<div class='media-placeholder viewport loading $class' id='$id'>";
	           $code .= "<img class='img-fluid lazy' data-src='".$data["url"]."' />";
		       $code .= "<style>.media-placeholder#".$id.":before{padding-top:$height%;}</style>";
			   $code .= "</div>";
		   }else{
	           $code .= "<div class='viewport loading $class jarallax' id='$id' data-speed='".$obj["parallax_speed"]."'>";
	           $code .= "<img class='img-fluid lazy jarallax-img' data-src='".$data["url"]."' />";
			   $code .= "</div>";
		   }
		break;

		case "video" :
		   if(!$obj["parallax"]){
			   $height = $data["height"] / $data["width"] * 100;
			   $code .= "<div class='media-placeholder video bg-video-file viewport loading $class' id='$id' data-video-bg='".$data["url"]."' data-video-poster='".$obj["poster_frame"]."' data-video-options='loop:".(is_true($obj["options"]["loop"])?"true":"false").", autoplay :".(is_true($obj["options"]["autoplay"])?"true":"false").", muted: ".(is_true($obj["options"]["muted"])?"true":"false").", position: center center, className: video-bg' data-index='$id' data-user-action='".(is_true($obj["options"]["buttons"]["play"])?"true":"false")."' data-autoplay='".(is_true($obj["options"]["autoplay"])?"true":"false")."'>";
			   if($obj["options"]["buttons"]["play"]){
	               $code .= "<a href='#' class='btn-play-toggle ".($obj["options"]["buttons"]["play"]["type"]=="icon"?"btn-play-icon":"fs-2 fw-bold btn btn-outline-light btn-lg rounded-pill btn-extend")."'>";
	               switch($obj["options"]["buttons"]["play"]["type"]){
	               	   case "text" :
	               	   case "text-icon" :
	               	      $code .= $obj["options"]["buttons"]["play"]["text"]."<i class='fa fa-play ms-2'></i>";
	               	   break;
	               	   case "icon-text" :
	               	      $code .= "<i class='fa fa-play me-2'></i> ".$obj["options"]["buttons"]["play"]["text"];
	               	   break;
	               	   case "icon" :
	               	      $code .= "<i class='fa fa-play'></i>";
	               	   break;
	               }
	               $code .= "</a>";
			   }
	           //$code .= "<video class='video lazy' controls='0'><source data-src='".$data["url"]."' type='".$data["mime_type"]."'></video>";
			   $code .= "<style>.media-placeholder#".$id.":before{padding-top:$height%;}.media-placeholder#".$id." .video-bg{background-image:url(".$obj["poster_frame"].")!important;}</style>";
			   $code .= "</div>";
		   }else{
		   	   $code .= "<div class='video viewport $class jarallax' id='$id' data-jarallax data-video-src='mp4:".$data["url"]."' data-speed='".$obj["parallax_speed"]."'>";
			   $code .= "</div>";
		   }

		break;


		case "video2" :
		   $height = $data["height"] / $data["width"] * 100;
		   $code .= "<div class='media-placeholder viewport loading $class' id='$id'>";
           $code .= "<video class='video lazy' controls='0'><source data-src='".$data["url"]."' type='".$data["mime_type"]."'></video>";
		   $code .= "<style>.media-placeholder#$id:before{padding-top:$height%;}</style>";
		   $code .= "</div>";
		break;

		case "video_embed2" :
		   $data = acf_oembed_data($data);
		   $dimensions = get_embed_video_data($data["url"]);
		   $height = $dimensions["height"] / $dimensions["width"] * 100;
		   $code .= "<div class='media-placeholder viewport loading $class' id='$id'>";
           $code .= set_embed_lazy($data["embed"]);
		   $code .= "<style>.media-placeholder#$id:before{padding-top:$height%;}</style>";
		   $code .= "</div>";
           
           $code  = "<div class='media-placeholder viewport loading $class' id='$id'>";
		   $code .= '<div class="video" data-vbg="'.$data["watch"].'" data-vbg-play-button="true"></div>';
		   $code .= "<style>.media-placeholder#$id:before{padding-top:$height%;}</style>";
		   $code .= "</div>";
		break;

		case "video_embed" :
		   $data = acf_oembed_data($data);
		   if(!$obj["parallax"]){
			   $dimensions = get_embed_video_data($data["url"]);
			   $height = str_replace(",",".",trim($dimensions["height"] / $dimensions["width"] * 100));
	           $code  = "<div class='media-placeholder video bg-video viewport loading $class' id='".$id."' data-url='".$data["watch"]."' data-type='".$data["type"]."' data-code='".$data["id"]."' data-width='".$dimensions["width"]."' data-height='".$dimensions["height"]."' data-user-action='".(is_true($obj["options"]["buttons"]["play"])?"true":"false")."' data-autoplay='".(is_true($obj["options"]["autoplay"])?"true":"false")."' data-rel='0' data-responsive='false'><div></div>";
	            $code .= "<style>.media-placeholder#".$id.":before{padding-top:".$height."%;}</style>";
	           if($obj["options"]["buttons"]["play"]){
	               $code .= "<a href='#' class='btn-play-toggle ".($obj["options"]["buttons"]["play"]["type"]=="icon"?"btn-play-icon":"fs-2 fw-bold btn btn-outline-light btn-lg rounded-pill btn-extend")."'>";
	               switch($obj["options"]["buttons"]["play"]["type"]){
	               	   case "text" :
	               	   case "text-icon" :
	               	      $code .= $obj["options"]["buttons"]["play"]["text"]."<i class='fa fa-play ms-2'></i>";
	               	   break;
	               	   case "icon-text" :
	               	      $code .= "<i class='fa fa-play me-2'></i> ".$obj["options"]["buttons"]["play"]["text"];
	               	   break;
	               	   case "icon" :
	               	      $code .= "<i class='fa fa-play'></i>";
	               	   break;
	               }
	               $code .= "</a>";
			   }
			   $code .= "</div>";
			}else{
			   $code  = "<div class='video viewport $class jarallax' id='$id' data-jarallax data-video-src='".$data["watch"]."'>";
			   $code .= "</div>";
			}
		break;
	}
	return $code;
}



function enable_mime_types( $upload_mimes ) {
	$upload_mimes['svg'] = 'image/svg+xml';
	$upload_mimes['svgz'] = 'image/svg+xml';
	$upload_mimes['webp'] = 'image/webp';
	return $upload_mimes;
}
add_filter('mime_types', 'enable_mime_types', 10, 1 );
add_filter( 'upload_mimes', 'enable_mime_types', 10, 1 );


function webp_is_displayable($result, $path) {
    if ($result === false) {
        $displayable_image_types = array( IMAGETYPE_WEBP );
        $info = @getimagesize( $path );
        if (empty($info)) {
            $result = false;
        } elseif (!in_array($info[2], $displayable_image_types)) {
            $result = false;
        } else {
            $result = true;
        }
    }
    return $result;
}
add_filter('file_is_displayable_image', 'webp_is_displayable', 10, 2);




function get_orientation($w=0, $h=0){
	if ( $w == $h ) {
        return 'square';
    }else{
    	if ( $w > $h ) {
	        return 'landscape';
	    } else {
	        return 'portrait';
	    }
	}
}

function add_orientation_class( $attr, $attachment_id ) {
    $metadata = get_post_meta( $attachment_id, '_wp_attachment_metadata', true);
    if ( empty($metadata['width']) || empty($metadata['height'])) {
        return $attr;
    }
    if ( !isset($attr['class'])) {
        $attr['class'] = '';
    }
    $attr['class'] .= ' '.get_orientation($metadata['width'], $metadata['height']);
    return $attr;
}

function add_orientation_class_filter( $attr, $attachment ) {
    return add_orientation_class( $attr, $attachment->ID);
}
add_filter( 'wp_get_attachment_image_attributes', 'add_orientation_class_filter', 10, 2 );

function get_attachment_id_by_url($image_url) {
    global $wpdb;
    $prefix = $wpdb->prefix;
    $attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM " . $prefix . "posts" . " WHERE guid='" . $image_url . "';"));
    return $attachment[0];
}
