<?php

use MatthiasMullie\Minify;

function compile_files(){

  $rules = compile_files_config();

  if (!file_exists($rules["config"]["min"])) {
	    mkdir($rules["config"]["min"], 0777, true);
	}

	// header.min.css
	if($rules["css"]["header"]){
	   $counter=0;
	   foreach($rules["css"]["header"] as $item){
            if ($counter==0) {
						    $minify = new Minify\CSS($item);
						}else{
						    $minify->add($item);
						}
            $counter++;
	   }
	   $minify->minify($rules["config"]["css"].'header.min.css');
	   plugin_assets($rules["config"]["css"].'header.min.css');
	}


	// header.min.js
	if($rules["js"]["jquery"]){
	   $counter=0;
	   foreach($rules["js"]["jquery"] as $item){
            if ($counter==0) {
						    $minify = new Minify\JS($item);
						}else{
						    $minify->add($item);
						}
            $counter++;
	   }
	   $minify->minify($rules["config"]["min"].'jquery.min.js');
	}


	// header.min.js
	if($rules["js"]["header"]){
	   $counter=0;
	   foreach($rules["js"]["header"] as $item){
            if ($counter==0) {
						    $minify = new Minify\JS($item);
						}else{
						    $minify->add($item);
						}
            $counter++;
	   }
	   $minify->minify($rules["config"]["min"].'header.min.js');
	}


	// locale files
	if (!file_exists($rules["config"]["locale"])) {
	    mkdir($rules["config"]["locale"], 0777, true);
	}
	if($rules["js"]["locale"]){
		if($rules["config"]["languages"]){
			foreach($rules["config"]["languages"] as $language) {
				$counter=0;
			   	foreach($rules["js"]["locale"] as $item){
			   		$file = $item["file"];
			   		if(isset($item["exception"][$language])){
	                   $file = str_replace("{lang}", $item["exception"][$language], $file);
			   		}else{
	                   $file = str_replace("{lang}", $language, $file);
			   		}
			   		if ($counter==0) {
					   $minify = new Minify\JS($file);
					}else{
					   $minify->add($file);
					}
					$counter++;
			   	}
			   	$minify->minify($rules["config"]["locale"].$language.'.js');
			}
	    }else{
	    	$counter=0;
	    	foreach($rules["js"]["locale"] as $key => $item){
			   	$file = $item["file"];
			   	if($item["exception"]){
			   		if(isset($item["exception"][$rules["config"]["language"]])){
	                   $file = str_replace("{lang}", $item["exception"][$rules["config"]["language"]], $file);
			   		}
			    }else{
	                $file = str_replace("{lang}", $rules["config"]["language"], $file);
			    }
			    if($counter==0) {
					$minify = new Minify\JS($file);
				}else{
					$minify->add($file);
				}
				$counter++;
			}
			$minify->minify($rules["config"]["locale"].$rules["config"]["language"].'.js');
		}
	}else{
		if($rules["config"]["languages"]){
			foreach($rules["config"]["languages"] as $language) {
		      file_put_contents($rules["config"]["locale"].$language.'.js', "");
		    }
		}else{
			file_put_contents($minify->minify($rules["config"]["locale"].$rules["config"]["language"].'.js'), "");
		}
	}



	// css locale
	/*if (!file_exists($rules["config"]["css"]."locale/")) {
	    mkdir($rules["config"]["locale"]."locale/", 0777, true);
	}*/
	if($rules["css"]["locale"]){
	    foreach($rules["config"]["languages"] as $language) {
	   	    $minify = new Minify\JS(" ");
			foreach($rules["css"]["locale"] as $item){
		    	if(isset($item[$language])){
		    		$minify->add($item[$language]);	
		    	}
			}
			$minify->minify($rules["config"]["css"]."locale-".$language.'.css');
	    }
	}else{
		if($rules["config"]["languages"]){
			foreach($rules["config"]["languages"] as $language) {
		      file_put_contents($rules["config"]["css"]."locale-".$language.'.css', "");
		    }
		}else{
			file_put_contents($rules["config"]["css"]."locale-".$$rules["config"]["language"].'.css', "");
		}
	}


	// functions
	$folder = "functions";
	$minify = false;
	$file_minified = $rules["config"]["min"].$folder.'.min.js';
	$function_files = array_slice(scandir($rules["config"]["prod"].$folder.'/'), 2);
	if(!ENABLE_FILTERS && isset($files["woo-filters.js"])){
		unset($files["woo-filters.js"]);
  }
	if(file_exists($file_minified)){
	    $min_date = filemtime($file_minified);
	    if($function_files){
		    foreach($function_files as $key => $filename){
			    if(filemtime($rules["config"]["prod"].$folder.'/'.$filename) > $min_date){
				    $minify = true;
				    break;
			    }
		    }
	    }
	}else{
		$minify = true;
	}
	if($function_files && $minify){
		foreach($function_files as $key => $filename){
			$file_path = $rules["config"]["prod"].$folder.'/'.$filename;
			if($key == 0){
			   $minifier = new Minify\JS($file_path);
			}else{
			   $minifier->add($file_path);
			}
		}
		$minifier->minify($file_minified);
	}


	// main
	$folder = "main";
	$minify = false;
	$file_minified = $rules["config"]["min"].$folder.'.min.js';
	$main_files = array_slice(scandir($rules["config"]["prod"].$folder.'/'), 2);
	if(file_exists($file_minified)){
	    $min_date = filemtime($file_minified);
	    if($main_files){
		    foreach($main_files as $key => $filename){
			     if(filemtime($rules["config"]["prod"].$folder.'/'.$filename) > $min_date){
				     $minify = true;
				     break;
			     }
		    }
	    }
	}else{
		$minify = true;
	}
	if($main_files && $minify){
		foreach($main_files as $key => $filename){
			$file_path = $rules["config"]["prod"].$folder.'/'.$filename;
			if($key == 0){
			   $minifier = new Minify\JS($file_path);
			}else{
			   $minifier->add($file_path);
			}
		}
		$minifier->minify($file_minified);
	}


	// plugins
	if($rules["js"]["plugins"]){
	   $counter=0;
	   foreach($rules["js"]["plugins"] as $item){
            if ($counter==0) {
			    $minify = new Minify\JS($item);
			}else{
			    $minify->add($item);
			}
            $counter++;
	   }
	   $minify->minify($rules["config"]["min"].'plugins.min.js');
	}
}

function plugin_assets($css_file){
	rmdir($rules["config"]["min"]."assets/");
	$css = file_get_contents($css_file);
	preg_match_all('/url\(([\s])?([\"|\'])?(.*?)([\"|\'])?([\s])?\)/i', $css, $matches, PREG_PATTERN_ORDER);
	if($matches){
		$assets = array();
		foreach($matches[3] as $key => $match){
   	  	    if( substr($match, 0, 5) != "data:"){
		   	  	$file = explode("/", $match);
		   	   	$file = $file[count($file)-1];
		   	  	$assets[] = array(
	                "code" => $matches[0][$key],
	                "url"  => $match,
	                "file" => $file
		   	  	);
		   	}
		}
		if($assets){
		   	mkdir($rules["config"]["min"]."assets/", 0777, true);
		   	foreach($assets as $key => $asset){
		   	  	$copy_file = explode("/node_modules/", $asset["url"])[1];
		   	  	copy( $rules["config"]["node"].$copy_file, $rules["config"]["min"]."assets/".$asset["file"] );
		   	  	$css = str_replace($asset["url"], $rules["config"]["min_uri"]."assets/".$asset["file"], $css);
		   	}
		   	file_put_contents($rules["config"]["css"].'header.min.css', $css);
		}
    }
}

function update_minified_file($file_minified){
	$filtered = slib_compress_script(file_get_contents($file_minified));
	file_put_contents ( $file_minified ,$filtered );
}


function slib_compress_script( $buffer ) {
   return $buffer;
  // JavaScript compressor by John Elliot <jj5@jj5.net>
  $replace = array(
    '#\'([^\n\']*?)/\*([^\n\']*)\'#' => "'\1/'+\'\'+'*\2'", // remove comments from ' strings
    '#\"([^\n\"]*?)/\*([^\n\"]*)\"#' => '"\1/"+\'\'+"*\2"', // remove comments from " strings
    '#/\*.*?\*/#s'            => "",      // strip C style comments
    '#[\r\n]+#'               => "\n",    // remove blank lines and \r's
    '#\n([ \t]*//.*?\n)*#s'   => "\n",    // strip line comments (whole line only)
    '#([^\\])//([^\'"\n]*)\n#s' => "\\1\n",
                                          // strip line comments
                                          // (that aren't possibly in strings or regex's)
    '#\n\s+#'                 => "\n",    // strip excess whitespace
    '#\s+\n#'                 => "\n",    // strip excess whitespace
    '#(//[^\n]*\n)#s'         => "\\1\n", // extra line feed after any comments left
                                          // (important given later replacements)
    '#/([\'"])\+\'\'\+([\'"])\*#' => "/*", // restore comments in strings
    '~//[#@]\s(source(?:Mapping)?URL)=\s*(\S+)~' => '' //remoce source urls (by salthareket)
  );

  $search = array_keys( $replace );
  $script = preg_replace( $search, $replace, $buffer );

  $replace = array(
    "&&\n" => "&&",
    "||\n" => "||",
    "(\n"  => "(",
    ")\n"  => ")",
    "[\n"  => "[",
    "]\n"  => "]",
    "+\n"  => "+",
    ",\n"  => ",",
    "?\n"  => "?",
    ":\n"  => ":",
    ";\n"  => ";",
    "{\n"  => "{",
    //  "}\n"  => "}", (because I forget to put semicolons after function assignments)
    "\n]"  => "]",
    "\n)"  => ")",
    "\n}"  => "}",
    "\n\n" => "\n"
  );

  $search = array_keys( $replace );
  $script = str_replace( $search, $replace, $script );

  return trim( $script );
}
