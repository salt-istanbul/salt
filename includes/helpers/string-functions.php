<?php

function trans( $text, $theme="" ) {
	if($theme==""){
		global $text_domain;
	}else{
		$text_domain = $theme;
	}
	return __($text, $text_domain);
}

function trans_plural($singular="", $plural="", $null="", $count=1, $theme=""){
	if($theme==""){
		global $text_domain;
	}else{
		$text_domain = $theme;
	}
	if($count == 0 && !empty($null)){
        return $null;
	}else{
		$pluralized = _n( $singular, $plural, $count, $text_domain );
	    return str_replace('{}', $count, $pluralized);
	}
}

function trans_default( $text, $textPlural="", $theme="" ) {
	if($theme==""){
		global $text_domain;
	}else{
		$text_domain = $theme;
	}
	if(function_exists('qtranxf_getLanguage')){
		$lang = qtranxf_getLanguage();
	    $lang_default = qtranxf_getLanguageDefault();
	}
	if(function_exists('icl_get_languages')){
		global $sitepress;
		$lang = ICL_LANGUAGE_CODE;
		$lang_default = $sitepress->get_default_language();
	}
	if($lang == "tr"){//$lang_default){
		return __($text, $text_domain);
	}else{
		return __($textPlural, $text_domain);
	}
}
function printf_array($text, $arr){
    return call_user_func_array('sprintf', array_merge((array)$text, $arr));
} 
function trans_arr($text, $arr){
	if(count($arr)>0){
		return printf_array(trans($text), $arr); 
	}else{
		return $text;
	}
}
function trans_multiple($arr){
	if(is_iterable($arr)){
		$arr_temp=array();
		foreach($arr as $item){
           $arr_temp[] = trans($item);
		}
		$arr = $arr_temp;
	}
	return $arr;
}
function trans_static($text){
	$pattern = "/\{{(.*?)\}}/ms";
	preg_match_all($pattern, $text, $matches);
	if(count($matches)>0){
	    $matches = $matches[1];
		$translates = trans_multiple($matches);
		for($i=0;$i<count($matches);$i++){
			$matches[$i] = "{{".$matches[$i]."}}";
			if(strpos($matches[$i], "function:")>-1){
				$function = str_replace("function:", "", str_replace("}}", "",str_replace("{{", "", $matches[$i])));
                $translates[$i] = call_user_func($function);
			}
		}	  
	    return str_replace( $matches, $translates, $text); 
	}else{
	    return $text;
	}
}
function trans_lang( $text, $domain = 'default', $the_locale = 'en_US' ){
    global $locale;
    $old_locale = $locale;
    $locale = $the_locale;
    $translated = __( $text, $domain );
    $locale = $old_locale;
    return $translated;
}
/*function trans_plural($singular, $plural, $count, $replace_count, $theme=""){
	if($theme==""){
		global $text_domain;
	}else{
		$text_domain = $theme;
	}
	return sprintf( _n( $singular, $plural, $count, $text_domain ), $replace_count );
}*/
function trans_predefined($text){
	if(isset($GLOBALS["lang_predefined"])){
	   $values = $GLOBALS["lang_predefined"];
	   if(isset($values[$text])){
          $text = $values[$text];
	   }
	   return $text;
	}
}
function uppertr($text){
	if(function_exists('qtranxf_getLanguage')){
	   if(qtranxf_getLanguage()=="tr"){
	     $text =  str_replace('i','İ',$text); 
	   }
	}
	if(function_exists('icl_get_languages')){
	   if(ICL_LANGUAGE_CODE=="tr"){
	     $text =  str_replace('i','İ',$text); 
	   }
	}
	return mb_convert_case($text, MB_CASE_UPPER, "UTF-8");	
}
function lowertr($text){
	if(function_exists('qtranxf_getLanguage')){
	   if(qtranxf_getLanguage()=="tr"){
	     $text = str_replace('I','ı',$text);
	   }
	}
	if(function_exists('icl_get_languages')){
	   if(ICL_LANGUAGE_CODE=="tr"){
	     $text = str_replace('I','ı',$text);
	   }
	}
    return mb_convert_case($text, MB_CASE_LOWER, "UTF-8");
}
function ucwordstr($text) {
	if(function_exists('qtranxf_getLanguage')){
	   if(qtranxf_getLanguage()=="tr"){
	     $text = str_replace(array(' I',' ı', ' İ', ' i'),array(' I',' I',' İ',' İ'),' '.$text);
	   }
	}
    return ltrim(mb_convert_case($text, MB_CASE_TITLE, "UTF-8"));
}  
function ucfirsttr($text) {
    $metin = in_array(crc32($text[0]),array(1309403428, -797999993, 957143474)) ? array(uppertr(substr($text,0,2)),substr($text,2)) : array(uppertr($text[0]),substr($text,1));
    return $text[0].$text[1];
} 

function ptobr($text){
       $paragraphs = array("<p>","</p>","[p-filter]");
       $noparagraphs = array("","<br>","");
       $text = str_replace( $paragraphs, $noparagraphs, $text );
       return preg_replace('/(<br>)+$/', '', $text);
}

function stripTagsByClass($array_of_id_or_class, $text){
   $name = implode('|', $array_of_id_or_class);
   $regex = '#<(\w+)\s[^>]*(class|id)\s*=\s*[\'"](' . $name .
            ')[\'"][^>]*>.*</\\1>#isU';
   return(preg_replace($regex, '', $text));
}

function truncate($text, $chars = 25) {
    if (strlen($text) <= $chars) {
        return $text;
    }
    $text = $text." ";
    $text = substr($text,0,$chars);
    $text = substr($text,0,strrpos($text,' '));
    $text = $text."...";
    return $text;
}

function truncate_middle($text, $chars = 25) {
	if (strlen($text) <= $chars) {
        return $text;
    }
	$separator = '...';
	$separatorlength = strlen($separator) ;
	$maxlength = $chars - $separatorlength;
	$start = $maxlength / 2 ;
	$trunc =  strlen($text) - $maxlength;
	return substr_replace($text, $separator, $start, $trunc);
}

function removeUrls($text=""){
	$text = preg_replace('/\b((https?|ftp|file):\/\/|www\.)[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i', ' ', $text);
    return $text;
}