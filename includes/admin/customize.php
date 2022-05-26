<?php
if ( function_exists( 'yasr_fs' ) ) {
		function plt_hide_yet_another_stars_rating_menus() {
			//Hide "Yet Another Stars Rating".
			remove_menu_page('yasr_settings_page');
			//Hide "Yet Another Stars Rating → Settings".
			remove_submenu_page('yasr_settings_page', 'yasr_settings_page');
			//Hide "Yet Another Stars Rating → Stats".
			remove_submenu_page('yasr_settings_page', 'yasr_stats_page');
			//Hide "Yet Another Stars Rating → Contact Us".
			remove_submenu_page('yasr_settings_page', '#');
			//Hide "Yet Another Stars Rating → Upgrade".
			remove_submenu_page('yasr_settings_page', 'yasr_settings_page-pricing');
		}
		add_action('admin_menu', 'plt_hide_yet_another_stars_rating_menus', 1000000000);

		function plt_hide_yet_another_stars_rating_metaboxes() {
			$screen = get_current_screen();
			if ( !$screen ) {
				return;
			}
			//Hide the "YASR" meta box.
			remove_meta_box('yasr_metabox_overall_rating', $screen->id, 'side');
			//Hide the "Yet Another Stars Rating" meta box.
			remove_meta_box('yasr_metabox_below_editor_metabox', $screen->id, 'normal');
		}
		add_action('add_meta_boxes', 'plt_hide_yet_another_stars_rating_metaboxes', 20);

		function plt_hide_yet_another_stars_rating_dashboard_widgets() {
			$screen = get_current_screen();
			if ( !$screen ) {
				return;
			}
			//Remove the "Recent Ratings" widget.
			remove_meta_box('yasr_widget_log_dashboard', 'dashboard', 'normal');
			//Remove the "Your Ratings" widget.
			remove_meta_box('yasr_users_dashboard_widget', 'dashboard', 'normal');
		}
		add_action('wp_dashboard_setup', 'plt_hide_yet_another_stars_rating_dashboard_widgets', 20);
}



add_filter( 'manage_pages_columns', 'table_template_columns', 10, 1 );
add_action( 'manage_pages_custom_column', 'table_template_column', 10, 2 );
function table_template_columns( $columns ) {
    $custom_columns = array(
        'col_template' => 'Template'
    );
    $columns = array_merge( $columns, $custom_columns );
    return $columns;
}
function table_template_column( $column, $post_id ) {
    if ( $column == 'col_template' ) {
        echo basename( get_page_template() );
    }
}



//rename media file name to sanitized post title
function custom_upload_filter( $file ) {
    if ( ! isset( $_REQUEST['post_id'] ) ) {
        return $file;
    }
    $id           = intval( $_REQUEST['post_id'] );
    $parent_post  = get_post( $id );
    $post_name    = sanitize_title( $parent_post->post_title );
    //$file['name'] = $post_name . '-' . $file['name'];
    $file['name'] = 'img-'. $post_name . '.' . mime2ext($file['type']);
    return $file;
}
//add_filter( 'wp_handle_upload_prefilter', 'custom_upload_filter' );



function acf_load_city_choices( $field ) {
	global $post;
    $field['choices'] = array();
    $cities = get_states(wc_get_base_country());
    if( is_array($cities) ) {
        foreach( $cities as $key => $city ) {
            $field['choices'][ $key ] = $city;
        }
    }
    return $field;
}
//add_filter('acf/load_field/name=city', 'acf_load_city_choices');

function acf_admin_head(){
    global $post;

	    $district = get_post_meta( $post->ID, "district", true);
		?>
		<script type="text/javascript">
			jQuery(function($){
				$('select').on('change', function() {
					var field_name = $(this).closest(".acf-field").data("name");
					switch(field_name){
						case "city" :
						   var obj = $(".acf-field[data-name='district']").find("select");
						       obj.prop("disabled", true);
						   var city = this.value;
							$.post(ajax_request_vars.url+"?ajax=query", { method : "get_districts", vars : { city : city } })
				            .fail(function() {
				                alert( "error" );
				            })
				            .done(function( response ) {
				            	response = $.parseJSON(response);	
				            	obj.empty().val(null).trigger('change');
				            	for(var i=0;i<response.length;i++){
				            		var selected = i==0?true:false;
				            		if("<?php echo $district;?>" == response[i]){
	                                   selected = true;
				            		}
				            		var district = response[i];
					            	var newOption = new Option(district, district, selected, selected);
								    obj.append(newOption);	            		
				            	}
				            	obj.trigger('change').prop("disabled", false);
				            });
						break;
	                }
				}).trigger("change");
			});
		</script>    	

<?php
}
//add_action('acf/input/admin_head', 'acf_admin_head');




/**
 * Add ACF thumbnail columns to Linen Category custom taxonomy
 */
function add_thumbnail_columns($columns) {
    $columns['image'] = __('Thumbnail');
    $new = array();
    foreach($columns as $key => $value) {
        if ($key=='name') // Put the Thumbnail column before the Name column
            $new['image'] = 'Thumbnail';
        $new[$key] = $value;
    }
    return $new;
}
//add_filter('manage_edit-product-color_columns', 'add_thumbnail_columns');

/**
 * Output ACF thumbnail content in Linen Category custom taxonomy columns
 */
function thumbnail_columns_content($content, $column_name, $term_id) {
    if ('image' == $column_name) {
        $term = get_term($term_id);
        $linen_thumbnail_var = get_field('image', $term);
        $content = '<img src="'.$linen_thumbnail_var.'" width="60" />';
    }
    return $content;
}
//add_filter('manage_product-color_custom_column' , 'thumbnail_columns_content', 10, 3);





function wps_add_role() {
    add_role( 'supplier', 'Supplier', 
             array(
                  'read'
                  )
    );
    add_role( 'client', 'Client', 
             array(
                  'read'
                  )
    );
}
//add_action( 'init', 'wps_add_role' );

function wps_remove_role() {
    remove_role( 'client' );
    remove_role( 'supplier' );
    remove_role( 'editor' );
    //remove_role( 'author' );
    remove_role( 'contributor' );
    //remove_role( 'subscriber' );
    remove_role( 'wpseo_manager' );
    remove_role( 'wpseo_editor' );
    remove_role( 'translator' );
    remove_role( 'shop_manager' );
    remove_role( 'translator' );
}
add_action( 'init', 'wps_remove_role' );














add_filter( 'cfw_get_billing_checkout_fields', 'remove_checkout_fields', 100 );

function remove_checkout_fields( $fields ) {
	unset( $fields['billing_company'] );
	unset( $fields['billing_city'] );
	unset( $fields['billing_postcode'] );
	unset( $fields['billing_country'] );
	unset( $fields['billing_state'] );
	unset( $fields['billing_address_1'] );
	unset( $fields['billing_address_2'] );
	return $fields;
}

// Set billing address fields to not required
add_filter( 'woocommerce_checkout_fields', 'unrequire_checkout_fields' );

function unrequire_checkout_fields( $fields ) {
	$fields['billing']['billing_company']['required']   = false;
	$fields['billing']['billing_city']['required']      = false;
	$fields['billing']['billing_postcode']['required']  = false;
	$fields['billing']['billing_country']['required']   = false;
	$fields['billing']['billing_state']['required']     = false;
	$fields['billing']['billing_address_1']['required'] = false;
	$fields['billing']['billing_address_2']['required'] = false;
	return $fields;
}


add_filter( 'woocommerce_customer_meta_fields', 'hide_shipping_billing' );
function hide_shipping_billing( $show_fields ) {
    unset( $show_fields['shipping'] );
    //unset( $show_fields['billing'] );
    return $show_fields;
}









// Callback function to insert 'styleselect' into the $buttons array
function my_mce_buttons_2( $buttons ) {
    array_unshift( $buttons, 'styleselect' );
    return $buttons;
}
// Register our callback to the appropriate filter
add_filter('mce_buttons_2', 'my_mce_buttons_2');

// Callback function to filter the MCE settings
function my_mce_before_init_insert_formats( $init_array ) {  
    // Define the style_formats array
    $style_formats = array(  
        // Each array child is a format with it's own settings
        array(  
            'title' => 'btn-primary',  
            'selector' => 'a',  
            'classes' => 'btn btn-primary btn-extended'             
        ),
        array(  
            'title' => 'btn-secondary',  
            'selector' => 'a',  
            'classes' => 'btn btn-secondary btn-extended'             
        )
    );  
    // Insert the array, JSON ENCODED, into 'style_formats'
    $init_array['style_formats'] = json_encode( $style_formats );  

    return $init_array;  

} 
// Attach callback to 'tiny_mce_before_init' 
add_filter( 'tiny_mce_before_init', 'my_mce_before_init_insert_formats' );



/**
 * Registers an editor stylesheet for the theme.
 */
function wpdocs_theme_add_editor_styles() {
    add_editor_style( 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.1/css/bootstrap.min.css' );
}
add_action( 'admin_init', 'wpdocs_theme_add_editor_styles' );






//Packs Admin List
function packs_column_header( $columns ){
    $columns['work_type'] = 'Work Type';
    $columns['pack_type'] = 'Pack Type';
    $columns['expertise'] = 'Related Expertise';
    return $columns;
}
add_filter( "manage_edit-packs_columns", 'packs_column_header', 10);
function add_packs_column_content($content, $column_name, $term_id){
    $term = get_term($term_id, 'packs');
    switch ($column_name) {
        case 'work_type':
            $content = get_field('work_type', 'packs_'.$term_id);
            $args = array(
                    'taxonomy' => 'work-type',
                    'include' => $content,
                    'hide_empty'  => false
            );
            $terms = get_terms($args);
            $terms = wp_list_pluck($terms, "name");
            /*if($content == "providers"){
               $content = "<strong style='background-color:gray;color:white;display:inline-block;padding:4px 8px;border-radius:8px;'>".$content."</strong>";
            }
            if($content == "consultants"){*/
               $content = "<strong style='background-color:navy;color:white;display:inline-block;padding:4px 8px;border-radius:8px;'>".$terms[0]."</strong>";
            /*}*/
            break;
        case 'pack_type':
            $content = get_field('pack_type', 'packs_'.$term_id);
            if($content == "easy"){
               $content = "<strong style='background-color:white;color:gray;border:1px solid gray;display:inline-block;padding:4px 8px;border-radius:8px;'>".$content."</strong>";
            }
            if($content == "custom"){
               $content = "<strong style='background-color:red;color:white;display:inline-block;padding:4px 8px;border-radius:8px;'>".$content."</strong>";
            }
            break;
        case 'expertise' :
            $content = get_field('expertise', 'packs_'.$term_id);
            if($content){
                $args = array(
                    'taxonomy' => 'expertise',
                    'include' => $content,
                    'hide_empty'  => false
                );
                $terms = get_terms($args);
                $terms = wp_list_pluck($terms, "name");
                $content = "";
                foreach($terms as $term){
                    $content .= "<strong style='background-color:green;color:white;display:inline-block;padding:4px 8px;border-radius:8px;margin-right:4px;margin-bottom:5px;'>".$term."</strong>";
                }
            }
            break;
        default:
            break;
    }
    return $content;
}
add_filter('manage_packs_custom_column', 'add_packs_column_content',10,3);

function register_work_type_column_for_packs_sortable($columns) {
  $columns['work_type'] = 'work_type';
  $columns['pack_type'] = 'pack_type';
  $columns['expertise'] = 'expertise';
  return $columns;
}
add_filter('manage_edit-packs_sortable_columns', 'register_work_type_column_for_packs_sortable');







function add_supplier_columns($columns) {
    $new = array();
    foreach($columns as $key => $value) {
        switch ($key){
            case "title" :
               $new['project'] = __('Project');
               $new[$key] = $value;
            break;

            case "author":
               $new['author'] = __('Supplier');
            break;

            default:
               $new[$key] = $value;
            break;
        }
    }
    return $new;
}
add_filter('manage_supplier_posts_columns', 'add_supplier_columns');

function supplier_columns_content($column, $post_id) {
    if ( $column == 'project') {
        $parent = get_post_parent($post_id);
        echo "<strong style='display:block;'>".$parent->post_name."</strong>";
        echo "by <a href=''>".get_user_by("id", $parent->post_author)->display_name."</a>";
    }
}
add_filter('manage_supplier_posts_custom_column' , 'supplier_columns_content', 10, 2);














add_action( 'woocommerce_register_form_start', 'display_account_registration_field' );
add_action( 'woocommerce_edit_account_form_start', 'display_account_registration_field' );
function display_account_registration_field() {
    $user  = wp_get_current_user();
    $value = isset($_POST['billing_continent']) ? esc_attr($_POST['billing_continent']) : $user->billing_continent;
    $continents = get_continents();
    ?>
    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label for="reg_billing_continent"><?php _e( 'Continent', 'woocommerce' ); ?> <span class="required">*</span></label>
    <select name="billing_continent" id="reg_billing_continent">
        <?php
        foreach($continents as $continent){
            ?>
            <option value="<?php echo $continent["slug"];?>" <?php if($continent["slug"]==$value){?>selected<?php } ?>><?php echo $continent["name"];?></option>
            <?php
        }
        ?>
    </select>
    </p>
    <div class="clear"></div>
    <?php
}

// registration Field validation
add_filter( 'woocommerce_registration_errors', 'account_registration_field_validation', 10, 3 );
function account_registration_field_validation( $errors, $username, $email ) {
    if ( isset( $_POST['billing_continent'] ) && empty( $_POST['billing_continent'] ) ) {
        $errors->add( 'billing_continent_error', __( '<strong>Error</strong>: Continent is required!', 'woocommerce' ) );
    }
    return $errors;
}

// Save registration Field value
add_action( 'woocommerce_created_customer', 'save_account_registration_field' );
function save_account_registration_field( $customer_id ) {
    if ( isset( $_POST['phone_code'] ) ) {
        update_user_meta( $customer_id, 'billing_phone_code', sanitize_text_field( $_POST['phone_code'] ) );
    }
    if ( isset( $_POST['billing_continent'] ) ) {
        update_user_meta( $customer_id, 'billing_continent', sanitize_text_field( $_POST['billing_continent'] ) );
    }
}

// Save Field value in Edit account
add_action( 'woocommerce_save_account_details', 'save_my_account_billing_continent', 10, 1 );
function save_my_account_billing_continent( $user_id ) {
    if ( isset( $_POST['phone_code'] ) ) {
        update_user_meta( $customer_id, 'billing_phone_code', sanitize_text_field( $_POST['phone_code'] ) );
    }
    if( isset( $_POST['billing_continent'] ) )
        update_user_meta( $user_id, 'billing_continent', sanitize_text_field( $_POST['billing_continent'] ) );
}

add_filter('woocommerce_admin_billing_fields', 'add_woocommerce_admin_billing_fields');
function add_woocommerce_admin_billing_fields($billing_fields) {
    $billing_fields['billing_continent'] = array( 'label' => __('Continent', 'woocommerce') );
    $billing_fields['billing_phone_code'] = array( 'label' => __('Phone Code', 'woocommerce') );

    return $billing_fields;
}

// Display field in admin user billing fields section
add_filter( 'woocommerce_customer_meta_fields', 'admin_user_custom_billing_field', 10, 1 );
function admin_user_custom_billing_field( $args ) {
    $options = array();
    $continents = get_continents();
    $options[""] = __( 'Choose a continent' );
    foreach($continents as $continent){
        $options[$continent["slug"]] = $continent["name"];
    }
    ksort($options);
    $args['billing']['fields']['billing_continent'] = array(
        'type'          => 'select',
        'label'         => __( 'Continent', 'woocommerce' ),
        'description'   => '',
        'custom_attributes'   => array('maxlength' => 6),
        'options' => $options
    );
    $args['billing']['fields']['billing_phone_code'] = array(
        'type'          => 'text',
        'label'         => __( 'Phone Code', 'woocommerce' ),
        'description'   => '',
        'custom_attributes'   => array('maxlength' => 6)
    );
    return $args;
}