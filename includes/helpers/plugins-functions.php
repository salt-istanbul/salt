<?php

function lazySizes($sizes){
	$code='data-bgset="';
	$sizes_count=count($sizes);
	$counter=0;
	foreach($sizes as $key=>$size){	
	    if ($counter%3 == 0){
		    $code.=$sizes[$key].' '.$sizes[$key.'-width'].'w '.($counter<$sizes_count-3?', ':'');
	    }
	    $counter++;
	};
	$code.='" data-sizes="auto"';
	return $code;
}

function lazySizesResponsive($sizes){
    $code='data-bgset="';
    $imgs = array();
    foreach($sizes as $key=>$size){	
    	    if($key != 'lg'){
		    $imgs[]=$sizes[$key].' [--'.$key.']';
	        }
	};
	$imgs[]=$sizes['lg'];
	return 'data-bgset="'.join(' | ', $imgs).'"';
}

function lazySizesImageResponsive($sizes){
	$upload_dir = wp_upload_dir()["url"]."/";
    $code='data-srcset="';
    $imgs = array();
    $sizes_new = array(
        'xs' => '480w',
        'sm' => '767w',
        'sm_ls' => '767w',
        'md' => '991w',
        'lg' => '1199w',
        'xl' => '1200w'
    );
    foreach($sizes as $key=>$size){	
    	    if($key != 'lg'){
    	       if(is_array( $sizes[$key])){
    	          if(array_key_exists("file", $sizes[$key])){
                     $imgs[] = $upload_dir.$sizes[$key]["file"].' '.$sizes_new[$key];//.' [--'.$key.']'; 
    	          }	
    	       }else{
    	       	  $sizes[$key] = str_replace($upload_dir, "", $sizes[$key]);
    	       	  $sizes[$key] = $upload_dir . $sizes[$key];
   		          $imgs[]=$sizes[$key].' '.$sizes_new[$key];//.' [--'.$key.']';    	       	
    	       }
	        }
	};
	if(array_key_exists("file", $sizes["lg"])){
      $imgs[]=$upload_dir.$sizes['lg']["file"];		
	}else{
   	  $imgs[]=$sizes['lg'];		
	}

	return 'data-srcset="'.join(',', $imgs).'"';
}

function lazySizesPictureResponsive($sizes=array(), $class=""){
	$upload_dir = wp_upload_dir()["url"]."/";
    $code = "<picture class='bg-cover ".$class."'><!--[if IE 9]><video style='display: none'><![endif]-->";
	foreach($sizes as $key=>$size){
	    if(is_array($sizes[$key])){
	        if(array_key_exists("file", $sizes[$key])){
	            $img=$upload_dir.$sizes[$key]["file"];	
            }else{
                $img=$sizes[$key];	
            }
		}else{
			if(strpos($sizes[$key], $upload_dir)>-1){
			   $img = $sizes[$key];
			}else{
			   $img=$upload_dir.$sizes[$key];
			}
		}
		//$query = explode("_", $key);
	    //$code .= "<source data-srcset='".$img."' media='(".$query[0]."-width: ".$query[1]."px)' />";
	    $code .= "<source data-srcset='".$img."' media='--".$key."' />";
	}
    $code .= "<!--[if IE 9]></video><![endif]-->";
    $code .= '<img src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" data-src="'.$img.'" class="img-fluid lazyload" alt="" />';
    $code .= '</picture>';
    return  $code;
}

function posts_to_slider($posts){
    $slides = array();
	if($posts){
	   $slider_settings = array(
		   	'navigation' => 1,
		   	'navigation_thumbs' => 1,
		   	'autoplay'   => 1,
		   	'delay'      => 4,
		   	'effect'     => 'slide'
	   );
	   foreach($posts as $post){
	      $slides[] = array(
	           "media" => array(
	               "slide_type" => "image",
	               "image"      => array(
	                   "desktop" => get_field("desktop", $post->ID),
	                   "mobile"  => get_field("mobile", $post->ID),
	               ),
	                "add_filter" => 0,
                    "filter_color" => "#be1e2d",
                    "add_opacity" => 1,
                    "opacity_value" => 50,
                    "opacity_color" => "#be1e2d",
                    "color"         => ""
	           ),
	           "content" => array(
                    "title" => "<ul class='list-label list-label-md  list-label-extended list-inline'><li class='list-inline-item'><div class='label'>".$post->category."</div></li></ul>".$post->post_title,
                    "title_color" => "#ffffff",
                    "title_bg_color" => "",
                    "description" => Timber\Twig::time_ago($post->post_date_gmt),//Salt::timeago($post->post_date_gmt),
                    "description_color" => "rgba(255,255,255,.8)",
                    "description_bg_color" => "",
                    "bg_color" => "",
                    "bg_color_opacity" => 100,
                    "align_hr" => "center",
                    "align_vr" => "center",
                    "text_align" => "center",
                    "class_name" => ""
	           ),
	           "url" => array(
                    "url_type" => "multiple",
                    "url_out" => "", 
                    "url_in" => "",
                    "add_button" => 0,
                    "text" => "",
                    "button_bg_color" => "",
                    "button_text_color" => "",
                    "buttons" => array(
                    	array(
                    		"url" => get_permalink($post->ID),
                    		"text" => __("Devamı", $GLOBALS["text_domain"]),
                    		"style" => array(
                    			"size" => "btn-lg",
                    			"class" => "light",
                    			"outline" => 1
                    		)
                    	)
                    )
                )
	       );
	   }
	}
	return  array(
			     "slider" => $slides,
			     "settings" => $slider_settings
		    );
}

function dateEstToPst($date){
	$time = new DateTime($date, new DateTimeZone('America/New_York'));
	$time->setTimezone(new DateTimeZone('America/Los_Angeles'));
	return $time->format('h:i a');
}

function get_blog_categories(){
	$args = array(
		'taxonomy' =>  'category',
	    'hide_empty' => false,
	    'exclude' => 1
	);
	return Timber::get_terms($args);	
}
function get_blog_tags(){
	$args = array(
	   'number' => 20,
	   'post_type' => 'post',
	   'echo' => false
	);
    return wp_tag_cloud($args);	
}

function updateSearchRank($id, $type){
	if($type == "post"){
		$value = get_post_meta( $id, 'wpcf_search_rank', true );
	    $value = empty($value)||$value==null?0:$value;
	    update_post_meta($id, 'wpcf_search_rank', $value + 1 ); 		
	}else{
		$value = get_term_meta( $id, 'wpcf_search_rank', true );
	    $value = empty($value)||$value==null?0:$value;
	    update_term_meta($id, 'wpcf_search_rank', $value + 1 ); 	
	}
}

function getTermsBySearchRank($count=5){
	$taxonomy = "destinations";
	$args = array(
		'taxonomy'			=> $taxonomy,
		'hide_empty'        => false,
		'number'            => $count,
		'order'             => 'DESC',
		'orderby'           => 'meta_value_num',
		'meta_query'		=> array(
			'relation'		=> 'OR',
			array(
				'key'	    => 'wpcf_search_rank',
				'value'	    => '0',
				'compare'	=> '>'
			)
		)
	);
    $term_query = new WP_Term_Query($args);
    return $term_query->terms;
}

function dateIsPast($date){
	$result = false;
	if(!is_object($date)){
        $date = strtotime($date);
	}else{
		$date = date_timestamp_get($date);
	}
    if(intval($date) < intval(time())) {
      $result = true;
    }


	/*print_r($date);

	$result = false;
	
	$date = strtotime($date);  
    //converts seconds into a specific format  
    //$date = date ("Y/d/m H:i", $sec);  
	//$date = new DateTime($date);
	print_r($date);

	//print_r(now());
    $now = new DateTime();
    echo "-----";
    print_r($now);
    echo "-----";
	if($date < $now) {
	    $result = true;
	}*/
	return $result;
}
function datesHasWeekend($start, $end) {
	if(!is_object($start)){
        $start = new DateTime($start);
	}
	if(!is_object($end)){
        $end = new DateTime($end);
	}
    return $start->diff($end)->format('%a') + $start->format('w') >= 6;
}
function datesWeekendDays($start, $end){
	if(!is_object($start)){
        $start = new DateTime($start);
	}
	if(!is_object($end)){
        $end = new DateTime($end);
	}
	$end->modify('+1 day');
    $interval = $end->diff($start);
	// total days
	$days = $interval->days;
	// create an iterateable period of date (P1D equates to 1 day)
	$period = new DatePeriod($start, new DateInterval('P1D'), $end);
	// best stored as array, so you can add more than one
	//$holidays = array('2012-09-07');
	foreach($period as $dt) {
	    $curr = $dt->format('D');
	    // substract if Saturday or Sunday
	    if ($curr != 'Sat' && $curr != 'Sun') {
	        $days--;
	    }
	    // (optional) for the updated question
	    /*elseif (in_array($dt->format('Y-m-d'), $holidays)) {
	        $days--;
	    }*/
	}
	return $days;
}


function class_salt($vars=array()){
	$salt = new Salt();
	$output = "";
	if(isset($vars["function"])){
		$function = $vars["function"];
	    unset($vars["function"]);
	    $output = $salt->$function($vars);
	}
    if(isset($vars["var"])){
		$var = $vars["var"];
	    unset($vars["var"]);
	    $output = $salt->$var;
	}
	return $output;
}

function paginate($paged, $total_pages){
    echo '<nav class="pagination-container pagination-builtin">' .paginate_links(array(  
                  'base' => get_pagenum_link(1) . '%_%',  
                  'format' => '?paged=%#%',  
                  'current' => $paged,  
                  'total' => $total_pages,  
                  'prev_text' => '',  
                  'next_text' => '',
                  'type'     => 'list',
                )).'</nav>';
}


function get_all_languages($native=false){
	require_once ABSPATH . 'wp-admin/includes/translation-install.php';
    $translations = wp_get_available_translations();
    $languages = array();
    foreach($translations as $key=>$lang){
    	$languages[$key] = $lang[$native?"native_name":"english_name"];
    }
    return $languages;
}

function get_timezones($field=array()){
	$utc = new DateTimeZone('UTC');
    $dt = new DateTime('now', $utc);
    $fieldValue = "";
    if($field){
	    $fieldValue = trim($field['value']);
		if(!$fieldValue && $field['default_time_zone']){
			$fieldValue = trim($field['default_time_zone']);
		}    	
    }
	$timezones_filtered = array();
	$timezones = \DateTimeZone::listIdentifiers();
	foreach ($timezones as $tz) {
        $current_tz = new \DateTimeZone($tz);
        $transition = $current_tz->getTransitions($dt->getTimestamp(), $dt->getTimestamp());
        $abbr = $transition[0]['abbr'];
        $is_selected = $fieldValue === trim($tz) ? ' selected="selected"' : '';
        $timezones_filtered[$tz] = $tz . ' (' . $abbr . ')';
    }
    return $timezones_filtered;
}

function change_user_login($user_id, $user_login=""){
	if(!empty($user_id) && !empty($user_login)){
		global $wpdb;
		$wpdb->update(
		    $wpdb->users, 
		    ['user_login' => $user_login], 
		    ['ID' => $user_id]
		);
	}
}

function secure_string($string, $base){
   $ajax_nonce = wp_create_nonce( $string . "-" . $base );
}

function isBase64Encoded_old(string $s) : bool{
        if ((bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s) === false) {
            return false;
        }
        $decoded = base64_decode($s, true);
        if ($decoded === false) {
            return false;
        }
        $encoding = mb_detect_encoding($decoded);
        if (! in_array($encoding, ['UTF-8', 'ASCII'], true)) {
            return false;
        }
        return $decoded !== false && base64_encode($decoded) === $s;
}

function isBase64Encoded($data){
    if (preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $data)) {
       return true;
    } else {
       return false;
    }
}