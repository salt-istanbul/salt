<?php

function compile_files_config($enable_production=false){
	//setting languages
	$language = strtolower( substr( get_locale(), 0, 2 ) );
	$languages = array();
	if(function_exists('qtranxf_getSortedLanguages')){
		foreach(qtranxf_getSortedLanguages() as $language) {
			array_push($languages, $language);
		}
	}
	if(function_exists('icl_get_languages')) {
		foreach(icl_get_languages('skip_missing=0&orderby=id&order=asc') as $language) {
			array_push($languages, $language["code"]);
		}
	}
	if(count($languages) == 0){
		array_push($languages, $language);
	}

	//setting paths
	$css_path = get_stylesheet_directory() . '/static/css/';
	$css_path_uri = get_stylesheet_directory_uri() . '/static/css/';
	$js_path = get_stylesheet_directory() . '/static/js/';
	$js_path_uri = get_stylesheet_directory_uri() . '/static/js/';

	$plugin_path = $js_path . 'plugins/';
	$plugin_path_uri = $js_path_uri . 'plugins/';

	$node_path = get_home_path() .'node_modules/';
	$node_path_uri = site_url() .'/node_modules/';

	$min_path = $js_path.'min/';
	$min_path_uri = $js_path_uri.'min/';
	$prod_path = $js_path.'production/';
	$prod_path_uri = $js_path_uri.'production/';

	$locale_path = $min_path.'locale/';

	$config = array(
		"js" => $js_path,
		"js_uri" => $js_path_uri,
		"css" => $css_path,
		"min" => $min_path,
		"min_uri" => $min_path_uri,
		"prod" => $prod_path,
		"prod_uri" => $prod_path_uri,
		"plugin" => $plugin_path,
		"plugin_uri" => $plugin_path_uri,
		"locale" => $locale_path,
		"languages" => $languages,
		"language" => $language,
		"node"  => $node_path,
		"node_uri" => $node_path_uri
	);

	if($enable_production){
	   $node_path = $node_path_uri;
	   $plugin_path = $plugin_path_uri;
	   $prod_path = $prod_path_uri;
	}

    

	$header_css = array();
	//$header_css['smartmenu-bs'] = $node_path . 'smartmenus/dist/addons/bootstrap-4/jquery.smartmenus.bootstrap-4.css';
	//$header_css['jquery-slinky'] = $node_path . 'jquery-slinky/dist/slinky.min.css';
	$header_css['ilightbox'] = $plugin_path . 'ilightbox/2.2.4/css/ilightbox.css';
	$header_css['animate.css'] = $node_path . 'animate.css/animate.min.css';
	$header_css['aos'] = 	$node_path . 'aos/dist/aos.css';
	$header_css['swiper'] = $node_path .'swiper/swiper-bundle.min.css';
	$header_css['jarallax'] = 	$node_path . 'jarallax/dist/jarallax.css';
	
	
   /*if(ENABLE_FAVORITES){
       $header_css['toast'] = $node_path .'jquery-toast-plugin/dist/jquery.toast.min.css';
   }
   if(ENABLE_FAVORITES || ENABLE_CART){
       $header_css['simple-scrollbar'] = $node_path .'simple-scrollbar/simple-scrollbar.css';
   }*/



   $jquery_js = array();
   $jquery_js['jquery'] = $node_path . 'jquery/dist/jquery.min.js';




	$header_js = array();
	//$header_js['intl'] = $node_path . 'intl/dist/Intl.min.js';
	//$header_js['modernizr'] = $plugin_path . 'modernizr/2.8.3/modernizr.min.js';
	$header_js['current-device'] = $node_path . 'current-device/umd/current-device.min.js';
	$header_js['enquire'] = $node_path . 'enquire.js/dist/enquire.min.js';
	//$header_js['jquery-ui'] = $plugin_path .'jquery-ui/jquery-ui.min.js';
	
	$header_js['defaults'] = $prod_path .'defaults.js';

	

	$locale = array();
	/*$locale["intl"] = array(
		"file" => $node_path . 'intl/locale-data/jsonp/{lang}.js',
		"exception" => array(
            "tr" => "tr-TR"
		)
	);
	$locale["bootstrap-datepicker"] = array(
		"file" => $node_path . 'bootstrap-datepicker/dist/locales/bootstrap-datepicker.{lang}.min.js',
		"exception" => array(
	       "en" => "en-GB"
		)
	);*/

	$locale_css = array();
	/*$locale_css["bootstrap-rtl"] = array(
		"ar" => $node_path . 'bootstrap-v4-rtl/dist/css/bootstrap-rtl.min.css'
	);*/


	$plugins = array();
	$plugins['bootstrap'] = $node_path . 'bootstrap/dist/js/bootstrap.bundle.min.js';
	//$plugins['masonry-layout'] = $node_path . 'masonry-layout/dist/masonry.pkgd.min.js';
	$plugins['isotope-layout'] = $node_path . 'isotope-layout/dist/isotope.pkgd.min.js';
	//menu
	//$plugins['smartmenus'] = $node_path . 'smartmenus/dist/jquery.smartmenus.min.js';
	//$plugins['smartmenus-bs'] = $node_path . 'smartmenus/dist/addons/bootstrap-4/jquery.smartmenus.bootstrap-4.min.js';
	//$plugins['jquery-slinky'] = $node_path . 'jquery-slinky/dist/slinky.min.js';
	// form & ui
	$plugins['bootbox'] =     $node_path . 'bootbox/dist/bootbox.all.min.js';
	$plugins['swiper'] = $node_path . 'swiper/swiper-bundle.js';

	// image
	$plugins['imagesloaded'] = 	$node_path . 'imagesloaded/imagesloaded.pkgd.min.js';
	$plugins['vanilla-lazyload'] = $node_path . 'vanilla-lazyload/dist/lazyload.min.js';
	//$plugins['lazysizes-bgset'] = 	$node_path . 'lazysizes/plugins/bgset/ls.bgset.min.js';
	//$plugins['lazysizes'] =  $node_path . 'lazysizes/lazysizes.min.js';
	$plugins['ilightbox'] =    $plugin_path . 'ilightbox/2.2.4/js/ilightbox.min.js';
	$plugins['background-check'] =    $plugin_path . 'background-check/background-check.min.js';

	
	$plugins['jarallax'] = 	$node_path . 'jarallax/dist/jarallax.min.js';
	$plugins['jarallax-video'] = 	$node_path . 'jarallax/dist/jarallax-video.min.js';
	
	

	// video
	$plugins['vide'] = 	$node_path . 'vide/dist/jquery.vide.min.js';
	$plugins['vimeo-api'] =  $node_path . '@vimeo/player/dist/player.min.js';
	$plugins['jquery-ui-widget'] =      $plugin_path . 'jquery-video/0.4.0/lib/jquery.ui.widget.min.js';
	$plugins['jquery-video'] = 	$plugin_path . 'jquery-video/0.4.0/src/jquery.dcd.video.js';
	//$plugins['jquery-video'] = 	$plugin_path . 'jquery-video/jquery.video.min.js';
	//$plugins['jquery-video-bg'] = $node_path . 'youtube-background/jquery.youtube-background.min.js';
	
	// utility
	$plugins['jquery-match-height'] = 	$node_path . 'jquery-match-height/dist/jquery.matchHeight-min.js';
	$plugins['hc-sticky'] =  $node_path . 'hc-sticky/dist/hc-sticky.js';
	$plugins['scrollpos-styler'] = 	$node_path . 'scrollpos-styler/scrollPosStyler.min.js';
	$plugins['autosize'] =   $node_path . 'autosize/dist/autosize.min.js';
	//$plugins['jquery-serializejson'] = 	$node_path . 'jquery-serializejson/jquery.serializejson.js';
	//$plugins['conditionize2'] = 	$node_path . 'conditionize2/jquery.conditionize2.min.js';
	//$plugins['jquery-validation'] =      	$node_path . 'jquery-validation/dist/jquery.validate.js';
	//$plugins['disableautofill'] =      	$node_path . 'disableautofill/src/jquery.disableAutoFill.min.js';
	//$plugins['inputmask'] =      	$node_path . 'inputmask/dist/jquery.inputmask.min.js';
	
	$plugins['aos'] = 	$node_path . 'aos/dist/aos.js';
    //plugins['twig'] =  $node_path . 'twig/twig.min.js';
	$plugins['is-in-viewport'] =  $node_path . 'is-in-viewport/lib/isInViewport.min.js';
	$plugins['lodash'] = $node_path .'lodash/lodash.min.js';

	/*if(ENABLE_FAVORITES){
       $plugins['toast'] = $node_path .'jquery-toast-plugin/dist/jquery.toast.min.js';
    }
    if(ENABLE_FAVORITES || ENABLE_CART){
       $plugins['simple-scrollbar'] = $node_path .'simple-scrollbar/simple-scrollbar.min.js';
    }*/


	$css = array(
		"header" => $header_css,
		"locale" => $locale_css
	);

	$js = array(
		"jquery"  => $jquery_js,
	    "header"  => $header_js,
	    "locale"  => $locale,
	    "plugins" => $plugins
	);

	if($enable_production){
		$js["functions"] = array();
		$functions = array_slice(scandir($config["prod"].'functions/'), 2);
		foreach($functions as $file){
			$js["functions"][] = $prod_path_uri.'functions/'.$file;
		}
		$js["main"] = array();
		$main =  array_slice(scandir($config["prod"].'main/'), 2);
		foreach($main as $file){
			$js["main"][] = $prod_path_uri.'main/'.$file;
		}
	}

	$minify = array(
		"config" => $config,
		"css"    => $css,
		"js"     => $js
	);

	return $minify;
}