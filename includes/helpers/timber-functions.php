<?php



function get_menu($name){
	return new TimberMenu($name);
}
function timber_image($id){
	return  new TimberImage($id);
}
function _get_term($args){
	return Timber::get_term($args);
}
function _get_terms($args){
	return Timber::get_terms($args);
}
function _get_post($args){
	return Timber::get_post($args);
}
function _get_posts($args){
	return Timber::get_posts($args);
}
function _get_field($field, $post_id){
	return get_field($field, $post_id);
}
function _get_option($field){
	return get_field($field, 'option');
}
function _get_option_cpt($field, $post_type){
	return get_field($field, 'cpt_'.$post_type);
}
function _get_widgets($widget){
	return Timber::get_widgets( $widget );
}
function _get_tax_posts($post_type,$taxonomy,$taxonomy_id,$post_count=-1){
  $args=array(
      'post_type' => $post_type,
      'numberposts' => $post_count,
      'tax_query' => array( 
            array(
                'taxonomy' => $taxonomy, // Taxonomy, in my case I need default post categories
                'field'    => 'id',
                'terms'    => $taxonomy_id, // Your category slug (I have a category 'interior')
            )
       )
  );
  return _get_posts($args);
}
function division($a, $b) {
    $c = @(a/b); 
    if($b === 0) {
      $c = null;
    }
    return $c;
}

function get_menu_parent($menu){
	foreach($menu as $item){
		if($item->object=='page'){
			if($item->post_parent>0){
			   return  new Timber\Post($item->post_parent);
			   break;
			}
	    }
	}
}

function get_bs_grid($sizes){
	$class = array();
	if($sizes){
		foreach($sizes as $key=>$size){
			if(isset($size)){
				if($key == "xs"){
				   $key = "-";
				}else{
				   $key = "-".$key."-";
				}
				$count = 12/$size;
				if (is_int($count)) {
		           $class[] = "col".$key.$count; 
				}else{
				   $class[] = "col".$key."1".$size;
				}
			}
		}		
	}
	return implode(" ", $class);
}

function get_bs_grid_gap($sizes){
	$class = array();
	if($sizes){
		foreach($sizes as $key=>$size){
			if(isset($size)){
				if($key == "xs"){
				   $key = "-";
				}else{
				   $key = "-".$key."-";
				}
		        $class[] = "row".$key.$size; 			
			}
		}		
	}

	return implode(" ", $class);
}

function _get_template($post){
	set_query_var('template_post_id', $post->ID );
	$template = get_template_directory().'/'.$post->_wp_page_template;
	if (file_exists($template)) {
	   return load_template($template, 0);
	}	
}

function _addClass($code, $find, $contains='', $class=''){
	if(empty($code)){
		return $code;
	}
	$html = new simple_html_dom();
    $html->load($code);
    $ul = $html->find($find, 0);
    if($ul){
       if($contains){
       	  if($ul->find($contains,0)){
             $ul->class = $class;
       	  }
	    }else{
	      $ul->class = $class;
	    }
    }
    return $html;
}

function pluralize($count, $singular="", $plural="", $null="", $theme=""){
	return trans_plural($singular, $plural, $null, $count, $theme);
}
