<?php
function push_message($error, $message){
	$response = array(
        "error" => $error,
        "message" => $message
	);
	echo json_encode($response);
}

/*function get_attachment_id_by_title( $title ) {
	  $attachment = get_page_by_title($title, OBJECT, 'attachment');
	  if ( $attachment ){
	    return $attachment->ID;
	  }
}*/

function ji_get_term_id($name, $taxonomy){
	$terms = new WP_Term_Query( array( 'name' => $name, 'taxonomy' => $taxonomy, 'hide_empty' => 0 ) );
    if($terms->terms){
       return $terms->terms[0]->term_id;
    }else{
       return "";
    }
}

function get_product_id( $data ) {
	$id = "";
    if(!empty($data["post_id"])){
        $id = $data["post_id"];
    }else{
		global $wpdb;
		$id = $wpdb->get_var( $wpdb->prepare( "SELECT key1.post_id FROM $wpdb->postmeta key1 INNER JOIN $wpdb->posts key2 ON key1.post_id = key2.ID AND key2.post_status ='publish' WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $data["sku"] ) );
		//if ( $product_id ) return new WC_Product( $product_id );
		//return null;    	
    }
    return $id;
}

$attribute_check = array();

function ji_product_attributes($attribute, $column_value, $is_variation ){
	$attributes_order = array("pa_renk", "pa_beden", "pa_cinsiyet", "pa_tas", "pa_materyal");
	$attribute_id = wc_attribute_taxonomy_id_by_name($attribute);
	$terms = explode(",",$column_value);
	$term_list = array();
	
	foreach($terms as $key=>$term){
		if(!empty($term)){
			$term_id = ji_get_term_id($term, $attribute);
			if(empty($term_id)){

                try{
			        if(!in_array($term, $attribute_check)){
				        $term_new = $GLOBALS["woo_api"]->post( 'products/attributes/'.$attribute_id.'/terms', array("name" => $term) );
						$term_list[] = $term_new->name;
						$attribute_check[] = $term;			        	
			        }
		        } catch ( Exception  $e ) {
					$message = $e->getMessage();
				    return array(   "error" => true,
		                            "message" => $message
					);
				}


			}else{
				$term_list[] = $term;
			}
		}
	}
	$attribute_item = array(
		"id" => $attribute_id,
		"slug" => $attribute,
		"position" => array_search($attribute, $attributes_order),
		"variation" => $is_variation,
		"visible" => true,
		"options" => $term_list
	);
	return $attribute_item;
}

function ji_update_variable_attributes( $product, $_product_variations ){
	foreach($product["attributes"] as $key=>$attribute){
		$arr=array();
		foreach($_product_variations as $variation){
			foreach($variation["attributes"] as $var_attribute){
				if($var_attribute["id"] ==  $attribute["id"]){
		            if(!in_array($var_attribute["option"], $arr)){
						$arr[] = $var_attribute["option"];
					}
				}
			}
		}
		$product["attributes"][$key]["position"] = $key;
		if($arr){
			$product["attributes"][$key]["variation"] = true;//count($arr)>1?true:false;
			$product["attributes"][$key]["options"] = $arr;			
		}
		if(count($arr)>1){
		   $product["default_attributes"][] = array(
		   	    "id" => $product["attributes"][$key]["id"],
		   	    "option" => $product["attributes"][$key]["options"][0]
		   );
		}
	}
	//print("<pre>".print_r($_product_variations, true)."</pre>");
	//print("<pre>".print_r($product, true)."</pre>");
	return $product;
}

function import_excel($tmpfname){

	   $loopContinue = true;

      $excelReader = PHPExcel_IOFactory::createReaderForFile($tmpfname);
      $excelObj = $excelReader->load($tmpfname);
      $worksheet = $excelObj->getSheet(0);
      $lastRow = $worksheet->getHighestRow();

      $row = 1;
		$lastColumn = $worksheet->getHighestColumn();
		$lastColumn++;
		for ($column = 'A'; $column != $lastColumn; $column++) {
		    $cell = $worksheet->getCell($column.$row);
		    $column_name = $cell->getValue();


		    //Sku	Ürün Kodu	Ürün Varyasyon Kodu	Marka	Cinsiyet	Kategori	Ürün Adı	Ürün Adı EN	Açıklama	Açıklama EN	Jewelicon Satış Fiyatı	Jewelicon PSF	Canlı	Pileksi	KUTU	Stok

		   switch($column_name){

		    	case "Id":
		    	    $column_name = "id";
		    	    break;

		    	case "Sku":
		    	    $column_name = "sku";
		    	    break;

		      case "Tedarikçi Kodu":
		    	    $column_name = "supplier_code";//supplier_code
		    	    break;

		    	/*case "Ürün Varyasyon Kodu":
		    	    $column_name = "supplier_variation_code";//supplier_variation_code
		    	    break;*/
		    	   
		    	case "Marka":
		    	    $column_name = "brands";
		    	    break;

		    	case "Kategori":
		    	    $column_name = "categories";
		    	    break;

		    	case "Etiket":
		    	    $column_name = "tags";
		    	    break;

		    	case "Renk":
		    	    $column_name = "pa_renk";
		    	    break;

		    	case "Beden":
		    	    $column_name = "pa_beden";
		    	    break;

		    	case "Materyal":
		    	    $column_name = "pa_materyal";
		    	    break;

		    	case "Taş":
		    	    $column_name = "pa_tas";
		    	    break;

		    	case "Taş Rengi":
		    	    $column_name = "tas_renk";
		    	    break;

		    	case "Cinsiyet":
		    	    $column_name = "pa_cinsiyet";
		    	    break;

		    	case "Ürün Adı":
		    	    $column_name = "name";
		    	    break;

		    	case "Ürün Adı EN":
		    	    $column_name = "name_en";
		    	    break;

		    	case "Açıklama":
		    	    $column_name = "description";
		    	    break;

		    	case "Açıklama EN":
		    	    $column_name = "description_en";
		    	    break;

		    	case "Jewelicon Satış Fiyatı":
		    	    $column_name = "sale_price";
		    	    break;

		    	case "Jewelicon PSF":
		    	    $column_name = "regular_price";
		    	    break;

		    	case "Canlı":
		    	    $column_name = "canli";
		    	    break;

		    	case "Pleksi":
		    	    $column_name = "pleksi";
		    	    break;

		    	case "Kutu":
		    	    $column_name = "kutu";
		    	    break;

		    	case "Stok":
		    	    $column_name = "stock_quantity";
		    	    break;    
  
		    	default:
		    	    $column_name = "";
		    	    break;
		   }

		   $column_names[] = $column_name;
		}

        $data = [];
        $variables = array();
        $variable_ids = array();

        for ($row = 2; $row <= $lastRow; $row++) {

        	if(!$loopContinue){
        		break ;
        	}

        	$column_counter=0;
        	$image_position = 0;
        	$product_tmp = array();
        	$attributes = array();

        	$image_list = array();
        	
        	$product_tmp["attributes"] = array();
        	$product_tmp["categories"] = array();
        	for ($column = 'A'; $column != $lastColumn; $column++) {
	        		$column_name = $column_names[$column_counter];

	        		if($column_name == "supplier_code" && empty($worksheet->getCell($column.$row)->getValue()) ){
	                   $loopContinue = false;
	                   break ;
	        		}

	        		//if($column_name == "sku")

	        		if(!empty($column_name)){
	        			$column_value = trim($worksheet->getCell($column.$row)->getValue());

		                $product_tmp[$column_name] = $column_value;
	                    
	                    $is_variation = true;
		                /*$is_variation = false;
		                if(isset($product_tmp["sku_variation"])){
		                	if(!empty($product_tmp["sku_variation"])){
			                   $is_variation = true;
			                }	                	
		                }*/

		                if(!empty($column_value)){

			                	switch($column_name){

								    	case "canli":
								    	case "pleksi":
								    	case "kutu":
								    	    //$image_list = array();
								    	    if(!empty($column_value)){
									    	    $images = explode(",", $column_value);
									    	    if($images){
								                    foreach($images as $image){
								                    	if(!empty($image)){
									                    	$image = str_replace("/assets/", "/wp-content/uploads/", $image);
									                    	$attachment_id = attachment_url_to_postid($image);
									                    	//$attachment_id = get_attachment_id_by_title(basename($image));
									                    	if($attachment_id){
									                    		update_field( 'product_image_type', $column_name, $attachment_id );
					                                            $image_list[] = array(
									                               "id" => $attachment_id,
									                               "position" => $image_position
										                   	    );
										                   	    $image_position++;
									                    	}/*else{
						                                        $image_list[] = array(
									                               "src" => $image,
									                               "position" => $key+1
										                   	    );			                    		
									                    	}*/
								                    	}
								                    }						    	    	
									    	    }
								    	    } 
								    	    //$product_tmp[$column_name] = $image_list;	
								    	    break;

								    	case "brands":
								    	    $terms = explode(",",$column_value);
						                    $term_list = array();
						                    foreach($terms as $key=>$term){
						                    	if(!empty($term)){
						                    		$term_id =  ji_get_term_id($term, "product_brand");
							                    	if(empty($term_id)){
							                    		$term_list[] = $GLOBALS["woo_api"]->post( 'products/brands', array("name" => $term) )->id;
							                    	}else{
							                    		$term_list[] = $term_id;
							                    	}
						                    	}
						                    }
						                    $product_tmp[$column_name] = $term_list;
								    	    break;

								    	case "categories":
								    	    $terms = explode(",",$column_value);
						                    $term_list = array();
						                    foreach($terms as $key=>$term){
						                    	if(!empty($term)){
						                    		$term_id = ji_get_term_id($term, "product_cat");
							                    	if(empty($term_id)){
							                    		$term_list[] = array("id" => $GLOBALS["woo_api"]->post( 'products/categories', array("name" => $term) )->id);
							                    	}else{
							                    		$term_list[] = array("id" => $term_id);
							                    	}
						                    	}
						                    }
						                    $product_tmp[$column_name] = $term_list;
								    	    break;

								    	case "tags":
								    	    $terms = explode(",",$column_value);
						                    $term_list = array();
						                    foreach($terms as $key=>$term){
						                    	if(!empty($term)){
						                    		$term_id = ji_get_term_id($term, "product_tag");
							                    	if(empty($term_id)){
							                    		$term_list[] = array("id" => $GLOBALS["woo_api"]->post( 'products/tags', array("name" => $term) )->id);
							                    	}else{
							                    		$term_list[] = array("id" => $term_id);
							                    	}
						                    	}
						                    }
						                    $product_tmp[$column_name] = $term_list;
								    	    break;

								    	case "pa_renk":
						                    $product_tmp["attributes"][] = ji_product_attributes($column_name, $column_value, $is_variation);
						                    $renk = explode(" ", $column_value);
						                    $renk_code = "";
						                    foreach($renk as $renk_word){
			                                   $renk_code .= $renk_word[0];
						                    }
						                    $product_tmp["renk"] = $renk_code;
								    	    break;

								    	case "pa_materyal":
								    	    $product_tmp["attributes"][] = ji_product_attributes($column_name, $column_value, false);
								    	    break;

								    	case "pa_tas":
								    	    $product_tmp["attributes"][] = ji_product_attributes($column_name, $column_value, false);
								    	    break;

								    	case "pa_beden":
								    	    $product_tmp["attributes"][] = ji_product_attributes($column_name, $column_value, $is_variation);//$is_variation);
								    	    break;

								    	case "pa_cinsiyet":
								    	    $product_tmp["attributes"][] = ji_product_attributes($column_name, $column_value, false);
								    	    break;
			                            
			                            // for creating sku
								    	case "tas_renk":
								    	    $product_tmp[$column_name] = $column_value;
								    	    $renk = explode(" ", $column_value);
						                    $renk_code = "";
						                    foreach($renk as $renk_word){
			                                   $renk_code .= $renk_word[0];
						                    }
								    	    $product_tmp[$column_name."_code"] = $renk_code;
								    	    break;

								    	case "stock_quantity" :
								    	    $product_tmp["stock_quantity"] = $column_value;
								    	    echo $product_tmp["name_tr"]." - ".$column_value."<br>";
								    	break;
								   }

					    }
	        		}
	            $column_counter++;
	        }
	        $product_tmp["images"] = $image_list;

	        if(empty($product_tmp["supplier_code"])){
              continue;
	        }

	        if(!empty($product_tmp["id"])){
	        	//$supplier_code = get_field("supplier_code", $product_tmp["Id"]);
	        	//$product_tmp["sku"] = strtoupper("JWL-".$product_tmp["supplier_code"].$product_tmp["renk"].$product_tmp["tas_renk_code"]);
	        	//update_field( 'supplier_code', $product_tmp["supplier_code"], $product_tmp["Id"] );
	        	update_post_meta( $product_tmp["id"], '_supplier_code', $product_tmp["supplier_code"]);
	        }else{
	        	//$supplier_code = $product_tmp["supplier_code"];
	        	$product_tmp["sku"] = strtoupper("JWL-".$product_tmp["supplier_code"].$product_tmp["renk"]);//.$product_tmp["tas_renk_code"]);
	        	
	        }
	        $supplier_code = substr_replace($product_tmp["sku"] ,"", 0-(strlen($product_tmp["renk"])));//.$product_tmp["tas_renk_code"])));
	        //$supplier_code = str_replace("JWL-","", $product_tmp["sku"]);
	       


	        //$supplier_code_cat = substr($supplier_code, 0, 3);
	        //$supplier_code = "JWL-".$supplier_code_cat.preg_replace('/[^0-9]/', '', $supplier_code);

	        if(empty($product_tmp["sku"])){
	           
	        }
	        //$product_tmp["sku_variation"] = strtoupper($product_tmp["supplier_code"].$product_tmp["renk"].$product_tmp["tas_renk_code"]);
            

	        //floatval($supplier_code);

	        //$product_tmp["attributes"] = $attributes;
	        //$product_type = "simple";
	        //$_product_id = $product_tmp["sku_2"];//$product_tmp["sku"];
	        //$_parent_product_id = 0;
	        
	        //if($product_tmp["sku"] != $product_tmp["sku_variation"]){
	        //if(!empty($product_tmp["sku_variation"])){
               $product_type = "product_variation";
               $_product_id = $product_tmp["sku"];//$product_tmp["sku_variation"];
               $_parent_product_id = $supplier_code;//$product_tmp["supplier_code"];
               if(!in_array($_parent_product_id, $variable_ids)){
	                $variables_args = array(
	               	                      "id" => $_parent_product_id,
	               	                      "name" => "[:tr]".$product_tmp["name"]."[:en]".$product_tmp["name_en"]."[:]",
	               	                      "description" => "[:tr]".$product_tmp["description"]."[:en]".$product_tmp["description_en"]."[:]",
	               	                      "brands" => $product_tmp["brands"],
	        			                  "categories" => $product_tmp["categories"],
	        			                  "tags" => array(),
	        			                  "attributes" => $product_tmp["attributes"],
	        			                  //"tas_renk" => $product_tmp["tas_renk"],
	        			                  "supplier_code" => $product_tmp["supplier_code"],
	        			                  "post_id" => $product_tmp["id"]
	               	);
	                if(isset($product_tmp["tags"])){
	                   $variables_args["tags"] = $product_tmp["tags"];
	                }

	                $variables[] = $variables_args;

	                $variable_ids[] = $_parent_product_id;
               }
	        //}

	        $product = array(
	        	   "_product_id" => $_product_id,
	        	   "_parent_product_id" => $_parent_product_id,
        			"sku" => $_product_id,
        			"type" => $product_type,
        			"name" => "[:tr]".$product_tmp["name"]."[:en]".$product_tmp["name_en"]."[:]",
        			"sale_price" => $product_tmp["sale_price"],
        			"regular_price" => $product_tmp["regular_price"],
        			"images" => array(),
        			"description" => "[:tr]".$product_tmp["description"]."[:en]".$product_tmp["description_en"]."[:]",
        			"stock_quantity" => $product_tmp["stock_quantity"],
        			"brands" => $product_tmp["brands"],
        			"categories" => $product_tmp["categories"],
        			"tags" => array(),
        			"attributes" => $product_tmp["attributes"],
        			//"tas_renk" => $product_tmp["tas_renk"],
	        		"supplier_code" => $product_tmp["supplier_code"],
	        		"post_id" => $product_tmp["id"]
        	);

        	if(isset($product_tmp["images"])){
        	   $product["images"] = $product_tmp["images"];
        	}
        	if(isset($product_tmp["tags"])){
        	   $product["tags"] = $product_tmp["tags"];
        	}

        	if($product_type=="product_variation"){
        		$attrs = array();
        		foreach($product["attributes"] as $attribute){
        			if($attribute["variation"]){
        			   $attrs[] = array(
                            'id'     => $attribute["id"],
						    'option' => $attribute["options"][0]
        			   );
        			}
        		}
        		$product["attributes"] = $attrs;
        	}
	        $data[] = $product;
        }

        //print_r($row);

        //$variables = array_unique($variables);
        foreach($variables as $variable){
        	$product = array(
	        	    "_product_id" => $variable["id"],
	        	    "_parent_product_id" => 0,
        			"sku" => $variable["id"],
        			"type" => "variable",
        			"name" => $variable["name"],
        			"description" => $variable["description"],
        			"brands" => $variable["brands"],
        			"categories" => $variable["categories"],
        			"tags" => $variable["tags"],
        			"attributes" => $variable["attributes"],
        			"default_attributes" => array(),
        			//"tas_renk" => $variable["tas_renk"],
        			"supplier_code" => $variable["supplier_code"],
        			"post_id" => $variable["id"]
        	);
	        $data[] = $product;
        }
        //print("<pre>".print_r($data, true)."</pre>");
        return $data;

        //echo unicode_decode(json_encode($data));
       //exit;
}


function import_and_update_by_excel($tmpfname){

	   $loopContinue = true;

      $excelReader = PHPExcel_IOFactory::createReaderForFile($tmpfname);
      $excelObj = $excelReader->load($tmpfname);
      $worksheet = $excelObj->getSheet(0);
      $lastRow = $worksheet->getHighestRow();

      $row = 1;
		$lastColumn = $worksheet->getHighestColumn();
		$lastColumn++;
		for($column = 'A'; $column != $lastColumn; $column++) {
		   $cell = $worksheet->getCell($column.$row);
		   $column_name = $cell->getValue();
		   $column_names[] = $column_name;
		}

      $data = [];
      for ($row = 2; $row <= $lastRow; $row++) {

	        	if(!$loopContinue){
	        		break ;
	        	}

	        	$column_counter=0;
	        	$product_tmp = array();
	
	        	for ($column = 'A'; $column != $lastColumn; $column++) {
		        	$column_name = $column_names[$column_counter];
		        	if(!empty($column_name)){
	        			$column_value = trim($worksheet->getCell($column.$row)->getValue());
		            $product_tmp[$column_name] = $column_value;
		         }
		         $column_counter++;
		      }

		      $product_tmp["sale_price"] = "";
		      if(!empty($product_tmp["discount"])){
		         $product_tmp["sale_price"] = $product_tmp["price"]-(($product_tmp["price"]/100)*$product_tmp["discount"]);
		      }

				$product = array(
				        	   "sku" =>  $product_tmp["sku"],
			        			"sale_price" => $product_tmp["sale_price"],
			        			"regular_price" => $product_tmp["price"],
			        			"discount" => $product_tmp["discount"],
			        			"installment_discount" => $product_tmp["installment_discount"]
			   );
			   if(!empty($product_tmp["title_tr"])){
			   	$product["name"] = "[:tr]".$product_tmp["title_tr"]."[:en]".$product_tmp["title_en"]."[:]";
			   }
		      $data[] = $product;
      }
      return $data;
}

function create_table($product_data){
	$product_count = count($product_data);
	$product_load_count = 0;
	$table = "<div class='card-products card'><div class='card-header'><h3 class='card-title'>Ürünler</h3></div><div class='card-body'><table class='table-products table table-sm'>";
	foreach ( $product_data as $k => $product ){
	   $simple_image_count = 0;
	   $variation_image_count = 0;
	   $product_load_count++;
	   if(!isset($product["variations"])){
	   	  if(isset($product["images"])){
	   	  	 if(count($product["images"])>0){
	   	  	 	$simple_image_count = count($product["images"])." images";
	   	  	 }else{
	   	  	 	$simple_image_count = "<span class='text-danger font-weight-bold'>No Image</span>";
	   	  	 }
	   	  }else{
	   	  	 $simple_image_count = "<span class='text-danger font-weight-bold'>No Image</span>";
	   	  }
	   }else{
			  if(isset($variation["images"])){
			    if(count($variation["images"])>0){
			   	  	$variation_image_count = count($variation["images"])." images";
			   	}else{
			   		$variation_image_count = "<span class='text-danger font-weight-bold'>No Image</span>";
			   	}
			  }else{
			  	$variation_image_count = "";
			  }
	   }

	   $stroke = "";
	   if(!empty($product["sale_price"])){
	   	$stroke = ' style="text-decoration:line-through"';
	   }
      
      $name = "";
	   if(!empty($product["name"])){
	   	$name = qtranxf_use( "tr", $product["name"], false,false);
	   }

       $table .= "<tr id='".$product["sku"]."' class='table-secondary text-bold'><td class='status'>Beklemede</td><td>".($k+1)."</td><td>".$product["sku"]."</td><td>".$name."</td><td ".$stroke.">".$product["regular_price"]."</td><td>".$product["sale_price"]."</td><td></td></tr>";
       
       if(isset($product["variations"])){
       	   foreach ( $product["variations"] as $m => $variation ){
       	   	  $product_count++;
       	   	  $product_load_count++;
       	      $table .= "<tr id='".$variation["sku"]."' class='table-secondary'><td class='status'>Beklemede</td><td>".($k+1)."-".($m+1)."</td><td>".$variation["sku"]."</td><td>".qtranxf_use( "tr", $variation["name"], false, false).'</td><td>'.$variation_image_count."</td><td></td><td>".$variation["stock_quantity"]."</td></tr>";
       	   }
       }
	}
	$table .= "</table></div></div>";
	$table = "<div class='alert alert-success text-center'>Toplam <b>".$product_count."</b> ürün. Yaklaşık yükleme süresi <b>".(($product_load_count*3)/60)."</b> dakika.</div>".$table;
	echo $table;
}

function wc_api_update_product($product){

		try {   

				if ( isset( $product['variations'] ) ) {
					$_product_variations = $product['variations'];
					unset($product['variations']);
					$product = ji_update_variable_attributes( $product, $_product_variations );
				};

				$product_id = get_product_id($product);
				if(!empty($product_id)){
					unset($product["manage_stock"]);
					unset($product["in_stock"]);
					unset($product["stock_quantity"]);
					$wc_product = $GLOBALS["woo_api"]->put( 'products/'.$product_id, $product );
					$bundle_id = get_bundle_product_id($wc_product->id);
			    	if($bundle_id){
			         update_bundled_product_price($bundle_id);
			    	}
			    	update_field( 'discount', $product["discount"], $wc_product->id);
			    	update_field( 'installment_discount', $product["installment_discount"], $wc_product->id);
				}

				return array(  "error" => false,
                           "id" => $wc_product->id,
                           "status" => !empty($product_id)?"Güncellendi":"Eklendi"
				);

		} catch ( Exception  $e ) {
			$message = $e->getMessage();
		    return array(   "error" => true,
                          "message" => $message
			);
		}
}

function wc_api_create_product($product){

		try {   

				if ( isset( $product['variations'] ) ) {
					$_product_variations = $product['variations'];
					unset($product['variations']);
					$product = ji_update_variable_attributes( $product, $_product_variations );
				};

				$product_id = get_product_id($product);
				if(!empty($product_id)){
					unset($product["manage_stock"]);
					unset($product["in_stock"]);
					unset($product["stock_quantity"]);
					$wc_product = $GLOBALS["woo_api"]->put( 'products/'.$product_id, $product );
					update_post_meta( $wc_product->id , '_supplier_code', $product["supplier_code"] );
					//update_post_meta( $wc_product->id , '_stone_color', $product['stone_color'] );
				}else{
					$wc_product = $GLOBALS["woo_api"]->post( 'products', $product );
					add_post_meta( $wc_product->id , '_supplier_code', $product["supplier_code"], true );
					//add_post_meta( $wc_product->id , '_stone_color', $product['stone_color'] );
				}

				foreach ( $product["attributes"] as $attribute ){
					$options=array();
					foreach ( $attribute["options"] as $option ){
						$options[] = ji_get_term_id($option, $attribute["slug"]);
					}
					wp_set_post_terms( $wc_product->id, $options, $attribute["slug"]);
			    }
			    unset($_product_variations);

				return array(  "error" => false,
                               "id" => $wc_product->id,
                               "status" => !empty($product_id)?"Güncellendi":"Eklendi"
				);

		} catch ( Exception  $e ) {
			$message = $e->getMessage();
		    return array(   "error" => true,
                            "message" => $message
			);
		}
}

function wc_api_create_product_variation($variation, $product_id){

		try {

				        $product_variation_id = get_product_id($variation);
						if(!empty($product_variation_id)){
							unset($variation["manage_stock"]);
					        unset($variation["in_stock"]);
					        unset($variation["stock_quantity"]);
							$wc_variation = $GLOBALS["woo_api"]->put( 'products/'. $product_id .'/variations/'.$product_variation_id, $variation );
							update_post_meta( $wc_variation->id , '_supplier_code', $variation["supplier_code"] );
							//update_post_meta( $wc_variation->id , '_stone_color', $variation['stone_color'] );
						}else{
							$wc_variation = $GLOBALS["woo_api"]->post( 'products/'. $product_id .'/variations', $variation );
							add_post_meta( $wc_variation->id , '_supplier_code', $variation["supplier_code"], true );
					        //add_post_meta( $wc_variation->id , '_stone_color', $variation['stone_color'] );
						}

						if(count($variation['images'])>1){
							$attachments = array();
							foreach($variation['images'] as $key_img=>$image){
								//if($key_img > 0){
									//if(array_key_exists("id", $image)){
                                        $attachments[] = $image["id"];
									//}/*else{
										//$attachments[] = featured_image_from_url($image["src"], $wc_variation->id, false);
									//}
								//}
							}
							if( $attachments){
								if(!empty($product_variation_id)){
	                                update_post_meta($wc_variation->id, '_wc_additional_variation_images',implode(', ', $attachments));
								}else{
								    add_post_meta($wc_variation->id, '_wc_additional_variation_images',implode(', ', $attachments));
								}								
							}
						}	

				return  array(
                               "error" => false,
                               "id" => $wc_variation->id,
                               "status" => !empty($product_variation_id)?"Güncellendi":"Eklendi"
				);

		} catch ( Exception $e ) {
			$message = $e->getMessage();
		    return array(   "error" => true,
                            "message" => $message
			);
		}
}

/**
 * Merge products and variations together. 
 * Used to loop through products, then loop through product variations.
 *
 * @param  array $product_data
 * @param  array $product_variations_data
 * @return array
*/
function merge_products_and_variations( $product_data = array(), $product_variations_data = array() ) {
	foreach ( $product_data as $k => $product ) :
		//echo $product["type"]." > ".$product['_product_id']."  -  ";
		foreach ( $product_variations_data as $k2 => $product_variation ) :
			if(isset($product['_product_id'])) :
				if ( $product_variation['_parent_product_id'] == $product['_product_id'] ) :

					// Unset merge key. Don't need it anymore
					unset($product_variation['_parent_product_id']);
					//array_splice($product_variation, 0, 1);

					$product_data[$k]['variations'][] = $product_variation;
					
					if(isset($product_variation["images"])){
						$product_data[$k]['images'] = $product_variation["images"];					
					}else{
						$product_data[$k]['images'] = array();
					}

				endif;
				
		    endif;
		endforeach;

		// Unset merge key. Don't need it anymore
		unset($product_data[$k]['_product_id']);
        //array_splice($product_data[$k], 0, 1);
	endforeach;

	return $product_data;
}

/**
 * Get products from JSON and make them ready to import according WooCommerce API properties. 
 *
 * @param  array $json
 * @param  array $added_attributes
 * @return array
*/
function get_products_and_variations_from_json( $json ) {

	$product = array();
	$product_variations = array();

	foreach ( $json as $key => $pre_product ){

		switch($pre_product['type']){

		    	case "simple":
						//$product[$key]['_product_id'] = (string) $pre_product['product_id'];
		    	        $product[$key]['type'] = (string) $pre_product['type'];
		    	        $product[$key]['sku'] = (string) $pre_product['sku'];
		    	        if(!empty($pre_product['name'])){
		    	           $product[$key]['name'] = (string) $pre_product['name'];
		    	        }
		    	        if(!empty($pre_product['description'])){
                           $product[$key]['description'] = (string) $pre_product['description'];
		    	        }
						if(!empty($pre_product['regular_price'])){
						   $product[$key]['regular_price'] = (string) $pre_product['regular_price'];
						}
						if(!empty($pre_product['sale_price'])){
						   $product[$key]['sale_price'] = (string) $pre_product['sale_price'];
					    }
						
						if(count($pre_product['brands'])>0){
							$product[$key]['brands']=$pre_product['brands'];
						}

						if(count($pre_product['attributes'])>0){
							$product[$key]['attributes']=$pre_product['attributes'];
						}

						if(count($pre_product['categories'])>0){
							$product[$key]['categories'] = $pre_product['categories'];
						}
						if(isset($pre_product['tags'])){
							if(count($pre_product['tags'])>0){
								$product[$key]['tags'] = $pre_product['tags'];
							}
						}

						$product[$key]['manage_stock'] = 1;//(bool) $pre_product['manage_stock'];
						if ( $pre_product['stock_quantity'] > 0 ) :
							$product[$key]['in_stock'] = (bool) true;
							$product[$key]['stock_quantity'] = (int) $pre_product['stock_quantity'];
						else :
							$product[$key]['in_stock'] = (bool) false;
							$product[$key]['stock_quantity'] = (int) 0;
						endif;
			            
			            if(!empty($pre_product['images'])){
							$product[$key]['images'] = $pre_product['images'];
						}

						//$product[$key]['stone_color'] = $pre_product['tas_renk'];
						$product[$key]['supplier_code'] = $pre_product['supplier_code'];

			    break;

			    case "variable":
					    $product[$key]['_product_id'] = (string) $pre_product['_product_id'];

						$product[$key]['type'] = (string) $pre_product['type'];
						$product[$key]['name'] = (string) $pre_product['name'];
						$product[$key]['description'] = (string) $pre_product['description'];
						$product[$key]['sku'] = (string) $pre_product['sku'];

						if(isset($pre_product['brands'])){
							$product[$key]['brands']=$pre_product['brands'];
						}

						if(isset($pre_product['attributes'])){
							$product[$key]['attributes']=$pre_product['attributes'];
						}

						if(isset($pre_product['categories'])){
							$product[$key]['categories'] = $pre_product['categories'];
						}

						if(isset($pre_product['tags'])>0){
							$product[$key]['tags'] = $pre_product['tags'];
						}

						//$product[$key]['stone_color'] = $pre_product['tas_renk'];
						$product[$key]['supplier_code'] = $pre_product['supplier_code'];

			            /*if(isset($pre_product['images'])){
			            	if(!empty($pre_product['images'])){
							  $product[$key]['images'] = $pre_product['images'];
						    }
						}*/

			    break;

			    case "product_variation":
			            $product_variations[$key]['_parent_product_id'] = (string) $pre_product['_parent_product_id'];
			            //$product_variations[$key]['type'] = (string) $pre_product['type'];
			            $product_variations[$key]['name'] = (string) $pre_product['name'];
						$product_variations[$key]['description'] = (string) $pre_product['description'];
						$product_variations[$key]['regular_price'] = (string) $pre_product['regular_price'];
						$product_variations[$key]['sale_price'] = (string) $pre_product['sale_price'];
						$product_variations[$key]['sku'] = (string) $pre_product['sku'];

						if(isset($pre_product['name'])){
							$product_variations[$key]['meta_data'] = array(
								array(
									'key' => 'variation_title',
	                                'value' => $pre_product['name']
								)
							);
						}

						if(isset($pre_product['attributes'])){
							$product_variations[$key]['attributes']=$pre_product['attributes'];
						}

						// Stock
						$product_variations[$key]['manage_stock'] = 1;//(bool) $pre_product['manage_stock'];

						if ( $pre_product['stock_quantity'] > 0 ) :
							$product_variations[$key]['in_stock'] = (bool) true;
							$product_variations[$key]['stock_quantity'] = (int) $pre_product['stock_quantity'];
						else :
							$product_variations[$key]['in_stock'] = (bool) false;
							$product_variations[$key]['stock_quantity'] = (int) 0;
						endif;
			            
			            if(!empty($pre_product['images'])){
							$product_variations[$key]['image'] = $pre_product['images'][0];
							$product_variations[$key]['images'] = $pre_product['images'];
						}

						//$product_variations[$key]['stone_color'] = $pre_product['tas_renk'];
						$product_variations[$key]['supplier_code'] = $pre_product['supplier_code'];


			    break;
		}
	};		

	$data['products'] = $product;
	$data['product_variations'] = $product_variations;

	return $data;
}	

/**
 * Get attributes and terms from JSON.
 * Used to import product attributes.
 *
 * @param  array $json
 * @return array
*/
function get_attributes_from_json( $json ) {
	$product_attributes = array();

	foreach( $json as $key => $pre_product ) :
		if ( !empty( $pre_product['attribute_name'] ) && !empty( $pre_product['attribute_value'] ) ) :
			$product_attributes[$pre_product['attribute_name']]['terms'][] = $pre_product['attribute_value'];
		endif;
	endforeach;		

	return $product_attributes;

}

/**
 * Parse JSON file.
 *
 * @param  string $file
 * @return array
*/
function parse_json( $file ) {
	$json = json_decode( file_get_contents( $file ), true );

	if ( is_array( $json ) && !empty( $json ) ) :
		return $json;	
	else :
		die( 'An error occurred while parsing ' . $file . ' file.' );

	endif;
}

/**
 * Print status message.
 *
 * @param  string $message
 * @return string
*/
function status_message( $message ) {
	echo $message . "\r\n";
}