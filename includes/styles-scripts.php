<?php

function frontend_header_styles(){
	wp_deregister_style('apss-font-awesome-five');
	wp_deregister_style('apss-font-awesome-four');
	wp_deregister_style('apss-font-opensans');
	//yobro
	wp_deregister_style('font-awesome');
	wp_deregister_style('font-for-body');
    wp_deregister_style('font-for-new');

    wp_register_style('locale', get_stylesheet_directory_uri() . '/static/css/locale-'.$GLOBALS['language'].'.css' , array(),'1.0.0','');
    wp_register_style('header', get_stylesheet_directory_uri() . '/static/css/header.min.css' , array(),'1.0.0','');
    wp_register_style('main', get_stylesheet_directory_uri() . '/static/css/main.css' , array(),'1.0.0','');
    wp_enqueue_style('locale');
	wp_enqueue_style('header');
	wp_enqueue_style('main');

	$load_svg_files = "
	.grayscale,
	.grayscale-hover,
	.color-hover:hover{
	   filter: url(".get_stylesheet_directory_uri() . "/static/css/grayscale.svg#grayscale);
	}
	.no-grayscale,
	.grayscale-hover:hover{
	   filter: url(".get_stylesheet_directory_uri() . "/static/css/grayscale.svg#ungrayscale);
	}";
	wp_add_inline_style( 'main', $load_svg_files ); 	
}
function frontend_header_scripts(){
	wp_deregister_script('jquery');
	wp_register_script ('jquery', get_stylesheet_directory_uri() . '/static/js/min/jquery.min.js', array(), '1.0.0', false);
	wp_enqueue_script('jquery');
    
   if(ENABLE_PRODUCTION){
		$header_files = compile_files_config(true)["js"]["header"];
		foreach($header_files as $key => $file){
			wp_register_script('header-'.$key, $file, array(), '1.0.0', false);
		   wp_enqueue_script('header-'.$key);
		}
	}else{
	    wp_register_script ('header', get_stylesheet_directory_uri() . '/static/js/min/header.min.js', array(), '1.0.0',false);
		wp_enqueue_script('header');	
	}
	wp_register_script('locale', get_stylesheet_directory_uri() . '/static/js/min/locale/'.$GLOBALS['language'].'.js', array(), null, false);
	wp_enqueue_script('locale');	
}
function frontend_footer_scripts(){
    wp_deregister_script( 'wc_additional_variation_images_script');
    if(isset($GLOBALS['google_maps_api_key']) && !empty($GLOBALS['google_maps_api_key'])){
	    wp_register_script('googlemaps','https://maps.googleapis.com/maps/api/js?key='.$GLOBALS['google_maps_api_key'].'&language='.$GLOBALS['language'], array(),null,true);
	    wp_enqueue_script('googlemaps');    	
    }

    if(ENABLE_PRODUCTION){
    	$files = compile_files_config(true);

		$plugins = $files["js"]["plugins"];
    	foreach($plugins as $key => $file){
			 wp_register_script('plugins-'.$key, $file, array(), '1.0.0', true);
		    wp_enqueue_script('plugins-'.$key);
		}

    	$functions = $files["js"]["functions"];
    	foreach($functions as $key => $file){
			wp_register_script('footer-'.$key, $file, array(), '1.0.0', true);
		    wp_enqueue_script('footer-'.$key);
		}

		$main = $files["js"]["main"];
    	foreach($main as $key => $file){
			wp_register_script('main-'.$key, $file, array(), '1.0.0', true);
		    wp_enqueue_script('main-'.$key);
		}

    }else{
	    wp_register_script('plugins', get_stylesheet_directory_uri() . '/static/js/min/plugins.min.js', array( ), null, true);
	    wp_enqueue_script('plugins');
		 wp_register_script('functions', get_stylesheet_directory_uri() . '/static/js/min/functions.min.js', array( ), null, true);
		 wp_enqueue_script('functions');
	    wp_register_script('main', get_stylesheet_directory_uri() . '/static/js/min/main.min.js', array( ), null, true);
	    wp_enqueue_script('main');    	
    }

	$map_style = strip_tags(get_field('google_maps_style', 'option'));
	if($map_style != ''){
		$add_map_style = "var map_style = ".$map_style.";";
		wp_add_inline_script( 'googlemaps', $add_map_style );
    }
    /*$location_main = acf_main_location(get_field_wpml('locations', 'option'));
    if($location_main != ''){
    	$add_location_main = "var location_main = ".json_encode($location_main).";";
		wp_add_inline_script( 'functions', $add_location_main );
    }*/
}
function load_frontend_files() {
    frontend_header_styles();
    frontend_header_scripts();
    frontend_footer_scripts();
}



function admin_header_styles(){
	wp_enqueue_style('fontawesome','https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css' , array(),'5.13.0',''); 
	wp_enqueue_style('admin-addon',get_stylesheet_directory_uri() . '/static/css/admin-addon.css'); 
}
function admin_header_scripts(){
}
function admin_footer_scripts(){
	 wp_register_script ("admin", get_stylesheet_directory_uri() . '/static/js/admin.js', array( 'jquery' ),'1.0.0',true);
	 wp_enqueue_script('admin');	
}
function load_admin_files() {
	admin_header_styles();
   admin_header_scripts();
   admin_footer_scripts();
}