<?php

function acceptOnly($arr,$val){
	if(is_array($arr)){
	   foreach($arr as $item){
		  if(trim($item)==$val){
			  return $item;
		  }
	   }
	}
}
function acceptOnlyExist($arr,$val){
	$return_val = false;
	if(is_array($arr)){
	   foreach($arr as $item){
		  if(trim($item)==$val){
			  $return_val = true;
			  break;
		  }
	   }
	}
	return $return_val;
}

function array_insert(&$array, $position, $insert){
    if (is_int($position)) {
        array_splice($array, $position, 0, $insert);
    } else {
        $pos   = array_search($position, array_keys($array));
        $array = array_merge(
            array_slice($array, 0, $pos),
            $insert,
            array_slice($array, $pos)
        );
    }
}

function array_iterable($var){
    return $var !== null 
        && (is_array($var) 
            || $var instanceof Traversable 
            || $var instanceof Iterator 
            || $var instanceof IteratorAggregate
            );
}

function array_search2d_by_field($needle, $haystack, $field) {
    foreach ($haystack as $index => $innerArray) {
        if (isset($innerArray[$field]) && $innerArray[$field] === $needle) {
            return $index;
        }
    }
    return false;
}

 /**
 * @param array $array
 * @param array|string $parents
 * @param string $glue
 * @return mixed
 */
function array_get_value(array &$array, $parents, $glue = '.')
{
    if (!is_array($parents)) {
        $parents = explode($glue, $parents);
    }

    $ref = &$array;

    foreach ((array) $parents as $parent) {
        if (is_array($ref) && array_key_exists($parent, $ref)) {
            $ref = &$ref[$parent];
        } else {
            return null;
        }
    }
    return $ref;
}

/**
 * @param array $array
 * @param array|string $parents
 * @param mixed $value
 * @param string $glue
 */
function array_set_value(array $array, $parents, $value = "", $glue = '.')
{
    if (!is_array($parents)) {
        $parents = explode($glue, (string) $parents);
    }

    $ref = &$array;

    foreach ($parents as $parent) {
        if (isset($ref) && !is_array($ref)) {
            $ref = array();
        }

        $ref = &$ref[$parent];
    }

    $ref = $value;
    return $array;
}

/**
 * @param array $array
 * @param array|string $parents
 * @param string $glue
 */
function array_unset_value(&$array, $parents, $glue = '.')
{
    if (!is_array($parents)) {
        $parents = explode($glue, $parents);
    }

    $key = array_shift($parents);

    if (empty($parents)) {
        unset($array[$key]);
    } else {
        array_unset_value($array[$key], $parents);
    }
}


/*
$exampleArray= [
    [
      "myKey"=>"This is my key",
      "myValue"=>"10"
    ],
    [
      "myKey"=>"Oh!",
      "myValue"=>"11"
    ]
];
if(($key = array_search("Oh!", array_column($exampleArray, 'myKey'))) !== false) {
    unset($exampleArray[$key]);
}*/
function unsetValue(array $array, $value, $strict = TRUE){
    if(($key = array_search($value, $array, $strict)) !== FALSE) {
        unset($array[$key]);
    }
    return $array;
}


function make_array($text, $seperator = ","){
	$arr = array();
	$text = explode($seperator, $text);
	foreach($text as $item ){
		$arr[] = trim($item);
	}
	return $arr;
}

function json_validate($string){
    // decode the JSON data
    $result = json_decode($string);

    // switch and check possible JSON errors
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            $error = ''; // JSON is valid // No error has occurred
            break;
        case JSON_ERROR_DEPTH:
            $error = 'The maximum stack depth has been exceeded.';
            break;
        case JSON_ERROR_STATE_MISMATCH:
            $error = 'Invalid or malformed JSON.';
            break;
        case JSON_ERROR_CTRL_CHAR:
            $error = 'Control character error, possibly incorrectly encoded.';
            break;
        case JSON_ERROR_SYNTAX:
            $error = 'Syntax error, malformed JSON.';
            break;
        // PHP >= 5.3.3
        case JSON_ERROR_UTF8:
            $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
            break;
        // PHP >= 5.5.0
        case JSON_ERROR_RECURSION:
            $error = 'One or more recursive references in the value to be encoded.';
            break;
        // PHP >= 5.5.0
        case JSON_ERROR_INF_OR_NAN:
            $error = 'One or more NAN or INF values in the value to be encoded.';
            break;
        case JSON_ERROR_UNSUPPORTED_TYPE:
            $error = 'A value of a type that cannot be encoded was given.';
            break;
        default:
            $error = 'Unknown JSON error occured.';
            break;
    }

    if ($error !== '') {
        // throw the Exception or exit // or whatever :)
       // exit($error);
    	$result = $error;
    }

    // everything is OK
    return $result;
}

function brToArray($text){
	return explode(PHP_EOL, $text);
}
function br2Nl($text){
	$breaks = array("<br />","<br>","<br/>");
	return str_ireplace($breaks, "\r\n", $text); 
}
function removeNewLine($text){
	return trim(preg_replace('/\s+/', ' ', $text));
}

function remove_element($array, $value) {
 foreach (array_keys($array, $value) as $key) {
    unset($array[$key]);
 }  
  return $array;
}

function array_clone($mixed){
    switch (true) {
        case is_object($mixed):
            return clone $mixed;
        case is_array($mixed):
            return array_map(array($this, __FUNCTION__), $mixed);
        default:
            return $mixed;
    }
}
// text içindeki variable isimlerini array'deki degerlerle replace eder:
// ornek "benim adım %name." , array("name" => "miki")
/*function str_replace_array($text = "", $array = array()){
   return preg_replace_callback('/%(.*?)%/', 
   	        function ($preg) use ($array) {
   	            return isset($array[$preg[1]]) ? $array[$preg[1]] : $preg[0]; 
   	        }, $text); 
}*/