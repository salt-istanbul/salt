<?

/**/

//remove title
remove_action('woocommerce_single_product_summary','woocommerce_template_single_title', 5);

//remove price
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );

//remove single product gallery
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );

//remove rating
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );

//remove single product's product meta
remove_action('woocommerce_single_product_summary','woocommerce_template_single_meta', 40);

//change position of related prducts
remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
//add_action('woocommerce_after_single_product', 'woocommerce_output_related_products', 9);

//change position of upsells prducts
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
//add_action( 'woocommerce_after_single_product', 'woocommerce_upsell_display', 39 );

//disable additional variation image ajax load
remove_action( 'wc_ajax_wc_additional_variation_images_get_images', '__return_false');

remove_action( 'woocommerce_single_product_summary', 'add-to-cart-form-bundle', 9999);


if(!ENABLE_CART){
  remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
}

/*
Bundled products
Layout: Tabular
Form Locations: Before tabs
Item Grouping : Grouped
*/


//remove all tabs in single product
add_filter( 'woocommerce_product_tabs', 'remove_product_tabs', 9999 );
function remove_product_tabs( $tabs ) {
    //unset( $tabs['additional_information'] );
    $tabs = array();
    return $tabs;
}











// Add custom tab item to single product
//add_filter( 'woocommerce_product_tabs', 'my_shipping_tab' );
function my_shipping_tab( $tabs ) {
    // Adds the new tab
    $tabs['shipping'] = array(
        'title'     => __( 'Shipping & Delivery', 'jewelicon' ),
        'priority'  => 50,
        'callback'  => 'my_shipping_tab_callback'
    );
    return $tabs;
}
//add_action( 'my_shipping_tab_callback', 'my_shipping_tab_callback_func', 10, 2 );
function my_shipping_tab_callback_func() {
	echo 'Sipari??iniz, sat??n alma i??leminiz ger??ekle??tikten sonra 24-48 saat i??inde kargoya teslim edilmektedir. Sipari??inizde ma??aza stoklar??ndan ??r??n mevcut ise 3 ??? 5 g??n i??erisinde kargoya teslim edilecektir. Sipari??inize ??zel teslim s??resini sepet a??amas??nda g??rebilirsiniz. Sipari??iniz kargoya teslim edildikten sonra teslimat s??resi bulundu??unuz b??lgeye g??re 1 ila 3 i?? g??n?? aras??nda de??i??mektedir.E??er sat??n ald??????n??z ??r??nlerden memnun kalmad??ysan??z, ??r??nleri 15 g??n i??erisinde ??cretsiz olarak iade edebilirsiniz. ??ade i??leminizin kabul edilebilmesi i??in, iade etti??iniz ??r??nlerin giyilmemi??/kullan??lmam???? olmas?? ve orijinal ambalaj??nda iade edilmesi gerekmektedir. Hijyenik nedenlerden dolay??; k??pe, i?? giyim ve bikinilerde iade kabul edilmemektedir. En iyi al????veri?? deneyimi i??in, iade etmek istedi??iniz ??r??n??, faturan??z??n arkas??ndaki iade b??l??m??n?? doldurarak, faturas?? ile birlikte ??cretsiz olarak iade adresimize g??nderebilirsiniz.';
}










/**
* WooCommerce: Show only one custom product attribute above Add-to-cart button on single product page.
*/
function isa_woo_get_one_pa(){
  
    // Edit below with the title of the attribute you wish to display
    $desired_att = 'Some Attribute Title';
   
    global $product;
    $attributes = $product->get_attributes();
     
    if ( ! $attributes ) {
        return;
    }
      
    $out = '';
   
    foreach ( $attributes as $attribute ) {
        $name = $attribute->get_name();
        if ( $attribute->is_taxonomy() ) {
          
            // sanitize the desired attribute into a taxonomy slug
            $tax_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '_', $desired_att)));
          
            // if this is desired att, get value and label
            if ( $name == 'pa_' . $tax_slug ) {
              
                $terms = wp_get_post_terms( $product->get_id(), $name, 'all' );
                // get the taxonomy
                $tax = $terms[0]->taxonomy;
                // get the tax object
                $tax_object = get_taxonomy( $tax );
                // get tax label
                if ( isset ( $tax_object->labels->singular_name ) ) {
                    $tax_label = $tax_object->labels->singular_name;
                } elseif ( isset( $tax_object->label ) ) {
                    $tax_label = $tax_object->label;
                    // Trim label prefix since WC 3.0
                    if ( 0 === strpos( $tax_label, 'Product ' ) ) {
                       $tax_label = substr( $tax_label, 8 );
                    }
                }
                  
                foreach ( $terms as $term ) {
       
                    $out .= $tax_label . ': ';
                    $out .= $term->name . '<br />';
                       
                }           
              
            } // our desired att
              
        } else {
          
            // for atts which are NOT registered as taxonomies
              
            // if this is desired att, get value and label
            if ( $name == $desired_att ) {
                $out .= $name . ': ';
                $out .= esc_html( implode( ', ', $attribute->get_options() ) );
            }
        }       
          
      
    }
      
    echo $out;
}
//add_action('woocommerce_single_product_summary', 'isa_woo_get_one_pa');








//custom variation attributes to selectpicker
function wc_dropdown_variation_attribute_options( $args = array() ) {

		$args = wp_parse_args(
			apply_filters( 'woocommerce_dropdown_variation_attribute_options_args', $args ),
			array(
				'options'          => false,
				'attribute'        => false,
				'product'          => false,
				'selected'         => false,
				'name'             => '',
				'id'               => '',
				'class'            => '',
				'show_option_none' => __( 'Choose an option', 'woocommerce' ),
			)
		);

		// Get selected value.
		if ( false === $args['selected'] && $args['attribute'] && $args['product'] instanceof WC_Product ) {
			$selected_key     = 'attribute_' . sanitize_title( $args['attribute'] );
			$args['selected'] = isset( $_REQUEST[ $selected_key ] ) ? wc_clean( wp_unslash( $_REQUEST[ $selected_key ] ) ) : $args['product']->get_variation_default_attribute( $args['attribute'] ); // WPCS: input var ok, CSRF ok, sanitization ok.
		}

		$options               = $args['options'];
		$product               = $args['product'];
		$attribute             = $args['attribute'];
		$name                  = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title( $attribute );
		$id                    = $args['id'] ? $args['id'] : sanitize_title( $attribute );
		$class                 = $args['class'];
		$show_option_none      = (bool) $args['show_option_none'];
		$show_option_none_text = $args['show_option_none'] ? $args['show_option_none'] : __( 'Choose an option', 'woocommerce' ); // We'll do our best to hide the placeholder, but we'll need to show something when resetting options.

		if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
			$attributes = $product->get_variation_attributes();
			$options    = $attributes[ $attribute ];
		}

		$html  = '<select id="' . esc_attr( $id ) . '" class="selectpicker custom-options ' . esc_attr( $class ) . '" name="' . esc_attr( $name ) . '" data-attribute_name="attribute_' . esc_attr( sanitize_title( $attribute ) ) . '" data-show_option_none="' . ( $show_option_none ? 'yes' : 'no' ) . '">';
		$html .= '<option value="">' . esc_html( $show_option_none_text ) . '</option>';

		if ( ! empty( $options ) ) {
			if ( $product && taxonomy_exists( $attribute ) ) {
				// Get terms if this is a taxonomy - ordered. We need the names too.
				$terms = wc_get_product_terms(
					$product->get_id(),
					$attribute,
					array(
						'fields' => 'all',
					)
				);

				foreach ( $terms as $term ) {
					if ( in_array( $term->slug, $options, true ) ) {
						$data_content = "";
						if($attribute == "pa_renk"){
							$color = get_term_meta( $term->term_id, "color", true);
		                    $color_image = get_field("color_image", $attribute."_".$term->term_id, true);//get_term_meta( $key_term, "color_image", true);
		                    if ( $color  || $color_image ) { 
			                   $data_content="<span class='color' style='background-color:".$color.";'></span> ".esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute, $product ) );
			                }    
				        }
						$html .= '<option value="' . esc_attr( $term->slug ) . '" ' . selected( sanitize_title( $args['selected'] ), $term->slug, false ) . ' data-content="'.$data_content.'">' . esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute, $product ) ) . '</option>';
					}
				}
			} else {
				foreach ( $options as $option ) {
					// This handles < 2.4.0 bw compatibility where text attributes were not sanitized.
					$selected = sanitize_title( $args['selected'] ) === $args['selected'] ? selected( $args['selected'], sanitize_title( $option ), false ) : selected( $args['selected'], $option, false );
					$html    .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute, $product ) ) . '</option>';
				}
			}
		}

		$html .= '</select>';

		echo apply_filters( 'woocommerce_dropdown_variation_attribute_options_html', $html, $args ); // WPCS: XSS ok.
}

















//update single product title with color attribute term appendix
//add_filter( 'wp_footer','custom_product_title_script' );
function custom_product_title_script(){
    global $post;

    if( ! is_product() ) return;

    $product = wc_get_product($post->ID);
    if( ! $product->is_type( 'variable' ) ) return;

    $attributes = array('pa_renk','pa_harf');
    foreach($product->get_visible_children( ) as $variation_id ) {
        foreach($product->get_available_variation( $variation_id )['attributes'] as $key => $value_id ){
            $taxonomy = str_replace( 'attribute_', '', $key );
            if( in_array( $taxonomy, $attributes) ){
                $product_variation = wc_get_product($variation_id);
                $data[ $variation_id ]["id"] = $variation_id;
                $data[ $variation_id ][$taxonomy] = get_term_by( 'slug', $value_id, $taxonomy )->name;
                $data[ $variation_id ]["title"] = get_post_meta( $variation_id, 'variation_title', true );
                $data[ $variation_id ]["description"] = '<strong>SKU: '.$product_variation->get_sku().'</strong><br>'.$product_variation->get_description();
            }
        }
    }
    ?>
        <script type="text/javascript">
            (function($){
                var variationsData = <?php echo json_encode($data); ?>,
                    productTitle = $('.product_title').text(),
                    productDesc = variationsData[Object.keys(variationsData)[0]].description;//$('#description-tab').find(".card-body").html(),
                    color = 'pa_renk';
                function update_the_title( productTitle, productDesc, variationsData, color ){
                	var $variations = $( '.woocommerce-variation.single_variation' );
                    console.log(variationsData);
                    console.log($variations)
                    $.each( variationsData, function( index, value ){
                        if( index == $('input.variation_id').val() ){
                            if(!IsBlank(value["title"])){
                               $('.product_title').text(value["title"]);   
                            }else{
                               $('.product_title').text(productTitle+' - '+value[color]);   
                            }
                            $("#description-tab").find(".card-body").html(value["description"]);
                            return false;
                        } else {
                            $('.product_title').text(productTitle);
                            $("#description-tab").find(".card-body").html(productDesc);
                        }
                        //alert(value["variation_title"]);
                    });
                }
                setTimeout(function(){
                    console.log(productDesc);
                    update_the_title( productTitle, productDesc, variationsData, color );
                }, 300);
                $('#pa_renk, #pa_harf').on("change", function(){
                    setTimeout(function(){
                       update_the_title( productTitle, productDesc, variationsData, color );
                    }, 100);
                });
            })(jQuery);
        </script>
    <?php
}






// Move Variation to top of the form & update main price with variation price
//add_action( 'wp_footer', 'ec_child_modify_wc_variation_desc_position' );
function ec_child_modify_wc_variation_desc_position() {
    global $post;
    if( ! is_product() ) return;
    $product = wc_get_product($post->ID);
    if( ! $product->is_type( 'variable' ) ) return;
?>
<script>
	 (function($) {
	 	 var productPrice = $(".product-price").find(".price").html();
	 	 var $form = $( '.variations_form' );
		 $(document).on( 'found_variation', function() {
			 var $variations = $( '.woocommerce-variation.single_variation' );
			 var $price = $variations.find(".price").html();
			 $variations.find(".woocommerce-variation-price").remove();
			 if ( $variations.length > 0 ) {
			     $variations.prependTo($form);
			     $(".product-price").find(".price").html($price);
			 }
		 });
		 $("select#pa_renk").on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
		 	  if(IsBlank($(this).val())){
		 	  	 $(".product-price").find(".price").html(productPrice);//"<?php #echo $product->get_price_html() ?>");
		 	  	 $form.find("select").not($(this)).val("").selectpicker("refresh");
		 	  }
		 });
		 $( ".variations_form" ).on( "woocommerce_variation_select_change", function (e) {
              //console.log(e)
         });
	 })( jQuery );
</script>
<?php }




//show grouped products iamge on list
add_action( 'woocommerce_grouped_product_list_before_label', 'bc_woocommerce_grouped_product_thumbnail' );
function bc_woocommerce_grouped_product_thumbnail( $product ) {
    $attachment_url = wp_get_attachment_image_src($product->get_image_id(), 'thumbnail', false)[0];
    ?>
    <td class="label">
        <img src="<?php echo $attachment_url; ?>" class="img-thumbnail image-fluid" width="60" height="60"  />
    </td>
    <?php
}





/**/
//show all options on variable dropdowns
//add_action( 'woocommerce_variable_add_to_cart', 'action_woocommerce_variable_add_to_cart', 10, 2 );
function action_woocommerce_variable_add_to_cart( $woocommerce_variable_add_to_cart ) { 
    global $product;
        // Enqueue variation scripts
        wp_enqueue_script( 'wc-add-to-cart-variation' );
        // Get Available variations?
        $get_variations = sizeof( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );
        // Load the template
        wc_get_template( 'single-product/add-to-cart/variable.php', array(
            'available_variations' => $get_variations ? $product->get_available_variations() : false,
            'attributes'           => $product->get_variation_attributes(),
            'selected_attributes'  => $product->get_default_attributes(),
             // Selection UX:
             // - 'locking':     Attribute selections in the n-th attribute are constrained by selections in all atributes other than n.
             // - 'non-locking': Attribute selections in the n-th attribute are constrained by selections in all atributes before n.
            'selection_ux'         => apply_filters( 'woocommerce_variation_attributes_selection_ux', '<strong>non-locking</strong>', $product )
        ) );
}; 
//add_action( 'wp_print_scripts', 'wc_deregister_javascript', 100 );
function wc_deregister_javascript() {
    wp_deregister_script( 'wc-add-to-cart-variation' );
    wp_register_script( 'wc-add-to-cart-variation', get_bloginfo( 'stylesheet_directory' ). '/static/js/wc-add-to-cart-variation.js' , array( 'jquery','wp-util' ), false, true );
    wp_enqueue_script('wc-add-to-cart-variation');
}
/*<form class="variations_form cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data' 
	  data-product_id="<?php echo absint( $product->get_id() ); ?>" 
      data-product_variations="<?php echo htmlspecialchars( wp_json_encode( $available_variations ) ) ?>" 
      data-selection_ux="<?php echo $selection_ux; ?>">*/








function filter_get_stock_html( $html, $product ) {
    // Low stock quantity amount
    $low_stock_qty = woo_get_product_low_stock_amount($product);

    $availability = $product->get_availability();

    if ( ! empty( $availability['availability'] ) ) {
        $class = esc_attr( $availability['class'] );
        $avail_text = wp_kses_post( $availability['availability'] );
        $stock_qty = $product->get_stock_quantity();

        if( $stock_qty <= $low_stock_qty ){
            $class .= ' low-stock';
            $avail_text = sprintf(trans('Son %s ??r??n'), $stock_qty);
        }
        ob_start();

        // Make your changes below
        ?>
        <div class="product-stock">
            <div class="stock <?php echo $class; ?>"><?php echo $avail_text; ?></div>
        </div>
        <?php

        $html = ob_get_clean();
    }
    return $html;
}
//add_filter( 'woocommerce_get_stock_html', 'filter_get_stock_html', 10, 2 );