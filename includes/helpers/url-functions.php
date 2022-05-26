<?php

Function get_host_url(){
    return site_url();	
}

function get_url_domain($url){
	$pieces = parse_url($url);
    $domain = isset($pieces['host']) ? $pieces['host'] : '';
    if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain,    $regs)) {
       return strstr( $regs['domain'], '.', true );
    }
}

function get_host_domain_url(){
	return preg_replace("(^https?://)", "", site_url() );
}

function extract_url($string){
   preg_match_all('/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $string, $result, PREG_PATTERN_ORDER);
   return $result[0][0];
}

function make_hash_url($url,$hash) {
	  if(!empty($url) && $hash){
		 $url=str_replace(get_host_url(),'',$url);
		 $url_hash=explode('/',$url);
		 $url_hash=array_filter($url_hash, function($a){return trim($a)!=="";});
		 $url_hash=array_values($url_hash);
		 $hash = end($url_hash);
		 //array_pop($url_hash);
         //$url_hash = join("/",$url_hash);
		 //$url_hash=$url_hash[0];		 
		 str_replace("#",$hash,"");
		 return get_host_url().'/'.$url_hash[0].'#'.$hash;
	  }else{
		  return $url;  
	  }
}

function make_onepage_url($item, $full_url) {
	  $url=$item->link;
	  $lang='';
	  $hashbang='#';

	  if(!empty($url) && $item->hash_url ){
		  $site_url = site_url();//.'.tr';
		  $url_temp=$url;
		  $multilanguage = false;
		  $lang = "";

		  if(function_exists('qtranxf_getLanguage')){
		  	 $multilanguage = true;
			 $lang = qtranxf_getLanguage();
			 $lang_default = qtranxf_getLanguageDefault();
			 $url_temp = str_replace($lang.'/',"",$url_temp);
		  }

          if(function_exists('icl_get_languages')){
          	 global $sitepress;
          	 $multilanguage = true;
			 $lang = ICL_LANGUAGE_CODE;
			 $lang_default = $sitepress->get_default_language();
			 $url_temp = str_replace($lang.'/',"",$url_temp);
		  }

		  $url_temp = str_replace($site_url,"",$url_temp);
		  $link_arr = explode('/',$url_temp);

		  if(count($link_arr)>0){
			  $link_arr = array_values(array_filter($link_arr, function($a){return trim($a)!=="";}));
			  $url_end = str_replace("#","",end($link_arr));
			  if($multilanguage){
				  if(!empty($lang) && $lang!=$lang_default){
					 $lang=$lang.'/'; 
				  }else{
					 $lang='';  
				  }			  	
			  }
			  $url_end=(!empty($url_end)?$hashbang.$url_end:$hashbang.$item->slug);
              
              $home_page_url = get_home_url();
			  $home_page_id = url_to_postid($home_page_url);

			  if($home_page_id == $item->post_parent){
	              $result = $home_page_url.$url_end;
			  }else{
			      array_pop($link_arr);
	              $link_arr = join("/",$link_arr);
	              $paths = $link_arr;
				  $result = $site_url.'/'.$paths.'/'.$lang.$url_end;
			  }

			  return trim(str_replace(get_host_domain_url().'.',"",$result));
		  }else{
			  $result = ($full_url || !is_front_page() ? $url : '').$hashbang.$item->slug;
			  return trim(str_replace(get_host_domain_url().'.',"",$result));
		  }
	  }else{		  
		  return $url;  
	  }
}
function make_onepage_url_by_id($id,$end_slug) {
	  $url=get_permalink($id);
	  $lang='';
	  $hashbang='#';
	  if(!empty($url)){
		  $site_url = site_url();
		  $url_temp=$url;
		  if(function_exists('qtranxf_getLanguage')){
			 $lang = qtranxf_getLanguage();
			 $url_temp = str_replace($lang.'/',"",$url_temp);
		  }
		  $url_temp = str_replace($site_url,"",$url_temp);
		  $link_arr = explode('/',$url_temp);
		  if(count($link_arr)>0){
			  $link_arr = array_values(array_filter($link_arr, create_function('$a','return trim($a)!=="";')));
			  $url_end = str_replace("#","",($end_slug?end($link_arr):join('/',$link_arr)));
			  if(!empty($lang) && $lang!=qtranxf_getLanguageDefault()){
				 $lang=$lang.'/'; 
			  }else{
				$lang='';  
			  }
			  $url_end=(!empty($url_end)?$hashbang.$url_end:$hashbang.$item->slug);
			  $result = ($full_url || !is_front_page()?site_url().'/'.$lang:'').$url_end;
			  $result = str_replace('http:/',"",$result);
			  return str_replace(get_host_domain_url().'.',"",$result);
		  }else{
			  $result = ($full_url || !is_front_page() ? $url : '').$hashbang.$item->slug ;
			  $result = str_replace('http:/',"",$result);
			  return str_replace(get_host_domain_url().'.',"",$result);
		  }
	  }else{		  
		  return $url;  
	  }
}

function current_url() {
  // Protocol
  if(!empty($_SERVER['HTTPS'])){
     $url = ( 'on' == $_SERVER['HTTPS'] ) ? 'https://' : 'http://';
  }else{
	 $url = 'http://'; 
  }
  $url .= $_SERVER['SERVER_NAME'];

  // Port
  $url .= ( '80' == $_SERVER['SERVER_PORT'] ) ? '' : ':' . $_SERVER['SERVER_PORT'];
  $url .= $_SERVER['REQUEST_URI'];

  return $url;//trailingslashit( $url );
}


function bwp_url_to_postid($url){
	global $wp_rewrite;

	$url = apply_filters('url_to_postid', $url);

	// First, check to see if there is a 'p=N' or 'page_id=N' to match against
	if ( preg_match('#[?&](p|page_id|attachment_id)=(\d+)#', $url, $values) )	{
		$id = absint($values[2]);
		if ( $id )
			return $id;
	}

	// Check to see if we are using rewrite rules
	$rewrite = $wp_rewrite->wp_rewrite_rules();

	// Not using rewrite rules, and 'p=N' and 'page_id=N' methods failed, so we're out of options
	if ( empty($rewrite) )
		return 0;

	// Get rid of the #anchor
	$url_split = explode('#', $url);
	$url = $url_split[0];

	// Get rid of URL ?query=string
	$url_split = explode('?', $url);
	$url = $url_split[0];

	// Add 'www.' if it is absent and should be there
	if ( false !== strpos(home_url(), '://www.') && false === strpos($url, '://www.') )
		$url = str_replace('://', '://www.', $url);

	// Strip 'www.' if it is present and shouldn't be
	if ( false === strpos(home_url(), '://www.') )
		$url = str_replace('://www.', '://', $url);

	// Strip 'index.php/' if we're not using path info permalinks
	if ( !$wp_rewrite->using_index_permalinks() )
		$url = str_replace('index.php/', '', $url);

	if ( false !== strpos($url, home_url()) ) {
		// Chop off http://domain.com
		$url = str_replace(home_url(), '', $url);
	} else {
		// Chop off /path/to/blog
		$home_path = parse_url(home_url());
		$home_path = isset( $home_path['path'] ) ? $home_path['path'] : '' ;
		$url = str_replace($home_path, '', $url);
	}

	// Trim leading and lagging slashes
	$url = trim($url, '/');

	$request = $url;
	// Look for matches.
	$request_match = $request;
	foreach ( (array)$rewrite as $match => $query) {
		// If the requesting file is the anchor of the match, prepend it
		// to the path info.
		if ( !empty($url) && ($url != $request) && (strpos($match, $url) === 0) )
			$request_match = $url . '/' . $request;

		if ( preg_match("!^$match!", $request_match, $matches) ) {
			// Got a match.
			// Trim the query of everything up to the '?'.
			$query = preg_replace("!^.+\?!", '', $query);

			// Substitute the substring matches into the query.
			$query = addslashes(WP_MatchesMapRegex::apply($query, $matches));

			// Filter out non-public query vars
			global $wp;
			parse_str($query, $query_vars);
			$query = array();
			foreach ( (array) $query_vars as $key => $value ) {
				if ( in_array($key, $wp->public_query_vars) )
					$query[$key] = $value;
			}

		// Taken from class-wp.php
		foreach ( $GLOBALS['wp_post_types'] as $post_type => $t )
			if ( $t->query_var )
				$post_type_query_vars[$t->query_var] = $post_type;

		foreach ( $wp->public_query_vars as $wpvar ) {
			if ( isset( $wp->extra_query_vars[$wpvar] ) )
				$query[$wpvar] = $wp->extra_query_vars[$wpvar];
			elseif ( isset( $_POST[$wpvar] ) )
				$query[$wpvar] = $_POST[$wpvar];
			elseif ( isset( $_GET[$wpvar] ) )
				$query[$wpvar] = $_GET[$wpvar];
			elseif ( isset( $query_vars[$wpvar] ) )
				$query[$wpvar] = $query_vars[$wpvar];

			if ( !empty( $query[$wpvar] ) ) {
				if ( ! is_array( $query[$wpvar] ) ) {
					$query[$wpvar] = (string) $query[$wpvar];
				} else {
					foreach ( $query[$wpvar] as $vkey => $v ) {
						if ( !is_object( $v ) ) {
							$query[$wpvar][$vkey] = (string) $v;
						}
					}
				}

				if ( isset($post_type_query_vars[$wpvar] ) ) {
					$query['post_type'] = $post_type_query_vars[$wpvar];
					$query['name'] = $query[$wpvar];
				}
			}
		}

			// Do the query
			$query = new WP_Query($query);
			if ( !empty($query->posts) && $query->is_singular )
				return $query->post->ID;
			else
				return 0;
		}
	}
	return 0;
}

function getSiteSubfolder(){
	$url_site = get_site_url();
	$url = parse_url($url_site);
	//$url_local = $url["scheme"]."://".$url["host"].($url["host"] == "localhost"?":".$url["port"]:"")."/";
	$url_local = $url["host"].($url["host"] == "localhost"?":".$url["port"]:"");//."/";
	$subFolderPath = str_replace($url["scheme"]."://", "", $url_site);
    $subFolderPath = str_replace($url_local, "", $subFolderPath);
	//$subFolderPath = explode("/", str_replace($url_local, "", $url_site));
	$subFolder = "/";
	if($subFolderPath){
       $subFolder = $subFolderPath."/";//"/".$subFolderPath[0]."/";
	}
	return $subFolder;
}


function getUrlEndpoint($url=""){
	if(empty($url)){
		$url = current_url();
	}
    $url_path = parse_url($url)["path"];
    if(!empty($url_path)){
	    $url_path = trim($url_path, "/");
	    $url_path = explode("/",$url_path);
		return end($url_path);    	
    }else{
    	return "";
    }
}

function isLocalhost(){
	$whitelist = array(
	    '127.0.0.1',
	    '::1'
	);
	return in_array($_SERVER['REMOTE_ADDR'], $whitelist);
}

function queryStringJSON(){
	//if(empty($querystring)){
	$querystring = $_SERVER['QUERY_STRING'];
	//}
	$keywords = preg_split("/[\s,=,&]+/", $querystring);
	$arr=array();
	for($i=0;$i<sizeof($keywords);$i++){
	   $value = "";
	   if(isset($keywords[$i+1])){
	   	  $value=$keywords[$i+1];
	   }
	   $arr[$keywords[$i]] = $value;
	   ++$i;
	}
	$obj =(object)$arr;
	//$obj=
	return json_encode($obj);
}


function rel2abs($rel, $base)
{
    /* return if already absolute URL */
    if (parse_url($rel, PHP_URL_SCHEME) != '') return $rel;

    /* queries and anchors */
    if ($rel[0]=='#' || $rel[0]=='?') return $base.$rel;

    /* parse base URL and convert to local variables:
       $scheme, $host, $path */
    extract(parse_url($base));

    /* remove non-directory element from path */
    $path = preg_replace('#/[^/]*$#', '', $path);

    /* destroy path if relative url points to root */
    if ($rel[0] == '/') $path = '';

    /* dirty absolute URL */
    $abs = "$host$path/$rel";

    /* replace '//' or '/./' or '/foo/../' with '/' */
    $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
    for($n=1; $n>0; $abs=preg_replace($re, '/', $abs, -1, $n)) {}

    /* absolute URL is ready! */
    return $scheme.'://'.$abs;
}

function abs2rel(string $base, string $path) {
    if (is_dir($base)) {
        $base = rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ".";
    }
 
    $a = explode(DIRECTORY_SEPARATOR, $base);
    $b = explode(DIRECTORY_SEPARATOR, $path);
 
    $d = [];   // $path push
    $i = count($a)-1;
 
    $sliceEquals = function($a, $b, $j) {
        if ($j >= count($a) || $j >= count($b)) {
            throw new Exception('$j out of range');
        }
        for ($i = $j; $i >= 0; $i--) {
            if (strcmp($b[$i], $a[$i])!==0) {
                return false;
            }
        }
        return true;
    };
         // find a, b are the same index of the array element
    while (array_pop($a)) {
        $i = count($a)-1;
        if (isset($b[$i])) {
            if ($sliceEquals($a, $b, $i)) {
                break;
            }
        }
        array_push($d, "..");
    }
         // start from the first different elements
    for ($i+=1; $i < count($b); $i++) {
        array_push($d, $b[$i]);
    }
    return ".".DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $d);
}