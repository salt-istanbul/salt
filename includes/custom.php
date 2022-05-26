<?php
function lang_predefined(){
    $GLOBALS["lang_predefined"] = array(
           //days
           "monday" => "Pazartesi",
           "tuesday" => "Salı",
           "wednesday" => "Çarşamba",
           "thursday" => "Perşembe",
           "friday" => "Cuma",
           "saturday" => "Cumartesi",
           "sunday" => "Pazar",
           "follow_us_on_instagram" => trans("Bizi <strong>Instagram</strong>'da takip edin!"),
           "not_found" => trans('İçerik bulunamadı.'),
           "autocomplete_not_found" => trans('"Ara" buttonuna tıklayarak detaylı arama yapabilirsiniz.'),
           "time_to_opening" => trans('%H saat %M dakika %S saniye sonra açılıyoruz!'),
           "time_to_closing" => trans('Kapanmamıza %H saat %M dakika %S saniye var.'),
           "not_found" => trans('Aradığınız kriterde içerik bulunamadı.'),
           "newsletter" => array(
               "name_error" => trans("Lütfen adınızı yazınız."),
               "surname_error" => trans("Lütfen soyadınızı yazınız."),
               "email_error"   => trans("Lütfen geçerli bir e-mail adresi giriniz."),
               "privacy_error" => trans("Lütfen Kişisel Verilerin Korunması Politika'mızı kabul ettiğinizi onaylayınız.")
           )

    );    
}

// class in twig example with "class_salt"
// {% set projects =  {"function": "ads", "action":"search", "work_type": data.work_type, "expertise": data.expertise, "user_id": user.id}|class_salt %}

class Salt{

    public $user;

    function __construct($user=array()) {
    	if($user){
           $this->user = new User($user);
    	}else{
           $this->user = new User(wp_get_current_user());
    	}
    }

    Private function response(){
    	return array(
			"error"       => false,
			"message"     => '',
			"description" => '',
		    "data"        =>  "",
			"resubmit"    => false,
			"redirect"    => "",
			"refresh"     => false,
			"html"        => "",
			"template"    => ""
		);
    }

    function init(){

    	add_action('wp', [ $this, 'update_online_users_status' ]);
    	add_action('wp_logout', [ $this, 'update_online_users_status_logout' ], 10, 1);
        
        if(ENABLE_ECOMMERCE){
    	   add_action('template_redirect', [ $this,'user_not_activated' ]);
        }
    	
    	/*add_action( 'user_register', array( $this, 'send_activation' ), 10, 1 );
    	add_action( 'woocommerce_created_customer', array( $this, 'send_activation' ), 10, 1 );
    	add_action( 'register_new_user', array( $this, 'send_activation' ), 10, 1 );*/
    	//add_filter('acf/settings/row_index_offset', '__return_zero');//'__return_zero');

        //hide fields from admin profile page
		//add_action( 'init', 'hide_admin_shipping_details' );
		//add_action('admin_head', 'hide_yoast_profile');
		//add_action('admin_head','hide_personal_options');
		//add_filter('user_contactmethods', 'hide_contact_methods');
		//add_action( 'personal_options', array ( 'hide_biography', 'start' ) );
		remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );

		
		add_filter('acf/update_value/name=map_url', [ $this, 'acf_map_embed_update'], 10, 3);
		add_action('acf/update_value', [ $this, 'acf_map_lat_lng'], 99, 3 ); 


		//post types save event
	    //	add_action('save_post', 'slide_published', 100, 3);
		//add_action('save_post', 'product_published', 100, 3);
		//add_action('save_post_product', 'product_published', 100, 3);
        //add_action('publish_post', 'product_published', 100, 3);
        //add_action( 'wp_insert_post_data', 'product_pre_update', 10, 2 );


		// unseen tour count to admin menu
        //add_action( 'load-post.php', 'custom_content_conversion' );


        // user
        add_action( 'user_register', [ $this, 'user_register_hook'], 10, 1 );
        add_action( 'edit_user_profile_update', [ $this,'user_before_update_hook'] );
        add_action( 'profile_update', [ $this,'user_after_update_hook'], 10, 2 );
        
        
        //add_action( 'wp_enqueue_scripts', 'ajax_request_js' );
        //add_action('admin_init', 'ajax_request_js');

        //delete
        add_action( 'before_delete_post', [ $this,'on_post_delete'], 10, 1 );
        add_action( 'delete_user', [ $this,'on_user_delete'], 10 );
        
        //scripts
        if(!is_admin()){
	        add_action( 'wp_enqueue_scripts', 'site_config_js' );
	        add_action('admin_init', 'site_config_js');        	
        }

    }



    static function newsletter($action="", $email=""){
    	global $wpdb;
    	if(!isset($email)){
           $email = $this->user->user_email;
    	}
        switch($action){
        	case "unsubscribe" :
                 $wpdb->update('{$wpdb->prefix}newsletter', array('status'=>'U'), array('email'=>$email));
        	     break;

        	case "subscribe" :
        	    $error = false;
			    //$user = get_user_by( "ID", $user_id );
				//$count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}newsletter WHERE email = %s", $email ) );	
			    if( $this->newsletter("exist", $email) ) {
			    	$error = true;
			    } elseif( !defined( 'NEWSLETTER_VERSION' ) ) {
			        $error = true;
			    } else {
				    $token =  wp_generate_password( rand( 10, 50 ), false );
				    $wpdb->insert( $wpdb->prefix . 'newsletter', array(
				                'email'         => $this->user->user_email,
				                //'sex'         => $fields['nx'],
				                'name'          => $this->user->first_name,
				                'surname'       => $this->user->last_name,
				                'status'        => "C",
				                //'list_1'        => $fields['list_1'],
			                    //'http_referer'  => $fields['nhr'],
			                    'token'         => $token,
			                    'wp_user_id'    => $this->user->ID
				    ));
			        $opts = get_option('newsletter');
			        $opt_in = (int) $opts['noconfirmation'];
			        if ($opt_in == 0) {
			            $newsletter = Newsletter::instance();
			            $user = NewsletterUsers::instance()->get_user( $wpdb->insert_id );
			            NewsletterSubscription::instance()->mail($user->email, $newsletter->replace($opts['confirmation_subject'], $user), $newsletter->replace($opts['confirmation_message'], $user));
			        }
			        if ($opt_in == 1) {
			            $newsletter = Newsletter::instance();
			            $user = NewsletterUsers::instance()->get_user( $wpdb->insert_id );
			            NewsletterSubscription::instance()->mail($user->email, $newsletter->replace($opts['confirmed_subject'], $user), $newsletter->replace($opts['confirmed_message'], $user));
			        }
			    }
        	    break;

            case "exist" :
                 return $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}newsletter WHERE email = %s", $email ) );
                 break;

            case "status" :
                $status = $wpdb->get_var( $wpdb->prepare("SELECT status FROM {$wpdb->prefix}newsletter WHERE email = %s", $email ) );
	            return $status=="C"?true:false;
                break;

        }
    }

    function login($vars=array(), $callback="", $role=""){
	    $response = $this->response();
		$info = array();
	    $info['user_login'] = $vars['username'];
	    $info['user_password'] = $vars['password'];
	    $info['remember'] = true;

	    if(isset($vars["role"])){
	    	$role = $vars["role"];
	    }

        if(isset($role)){
		    $user_data = get_user_by( 'email', $info['user_login'] );
		    if($user_data){
		       if(!in_array($role, $user_data->roles)){
		       	  $response["error"] = true;
		          $response["message"] = 'Please use your '.$role.' account.';
		       }
		    }        	
        }

        if(!$response["error"]){
		    $user_signon = wp_signon( $info, false );
		    if ( is_wp_error($user_signon) ){
		    	//print_r($user_signon);
		        $response["error"] = true;
		        $response["message"] = 'Wrong username or password.';
		    } else {
		        $response["message"] = 'Login successful.';
		        wp_set_current_user($user_signon->ID);
		        wp_set_auth_cookie($user_signon->ID);
		        $this->user = $user_signon;
		    }        	
        }

		if(!$response["error"] && isset($callback)){
		    if($callback == "publish_brief"){
				$context = Timber::get_context();
	            $context['user'] = $this->user;
	            $context['vars'] = $vars;
	            $context['newsletter'] = $this->newsletter("status", $this->user->user_email);
	            $response["resubmit"] = true;
		        $response["html"] = Timber::compile( 'partials/form-user.twig', $context );
			}
		}
	    return $response;
    }

    function lost_password($vars=array(), $callback=""){
    	global $wpdb;
    	$error = false;
    	$message = "";
		$user_login = $vars['user_login'];

	    //check_ajax_referer( 'ajax-forgot-nonce', 'security' );
		
		if( empty( $user_login ) ) {
			$error = true;
			$message = 'Enter an username or e-mail address.';
		} else {
			if(is_email( $user_login )) {
				if( email_exists($user_login) ){
					$get_by = 'email';
				}else{
					$error = true;
					$message = 'There is no user registered with that email address.';					
				}	
			}else if (validate_username( $user_login )) {
				if( username_exists($user_login) ) {
					$get_by = 'login';
				}else{
				    $error = true;
				    $message = 'There is no user registered with that username.';					
				}
			}else{
				$error = true;
				$message = 'Invalid username or e-mail address.';				
			}
		}	
		
		if(empty ($error)) {
		    $random_password = wp_generate_password();
			$user = get_user_by( $get_by, $user_login );	
			$update_user = wp_update_user( array ( 'ID' => $user->ID, 'user_pass' => $random_password ) );
				
			// if  update user return true then lets send user an email containing the new password
			if( $update_user ) {
				
				$from = 'info@ping90.com'; // Set whatever you want like mail@yourdomain.com
				
				if(!(isset($from) && is_email($from))) {		
					$sitename = strtolower( $_SERVER['SERVER_NAME'] );
					if ( substr( $sitename, 0, 4 ) == 'www.' ) {
						$sitename = substr( $sitename, 4 );					
					}
					$from = 'admin@'.$sitename; 
				}
				
				$to = $user->user_email;
				$subject = 'Your new password';
				$sender = 'From: '.get_option('name').' <'.$from.'>' . "\r\n";
				
				$message = 'Your new password is: '.$random_password;
					
				$headers[] = 'MIME-Version: 1.0' . "\r\n";
				$headers[] = 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				$headers[] = "X-Mailer: PHP \r\n";
				$headers[] = $sender;
					
				$mail = wp_mail( $to, $subject, $message, $headers );
				if( $mail ){
					$message = 'Check your email address for you new password.';
				}else{
					$error = true;
					$message = 'System is unable to send you mail containg your new password.';	
				}					
			} else {
				$error = true;
				$message = 'Oops! Something went wrong while updaing your account.';
			}
		}	
		$data = array(
			"error"   => $error,
			"message" => __($message),
			"data"    =>  "",
			"resubmit" => false,
			"redirect" => "",
			"html"    => ""
		);
		echo json_encode($data);
    }

    function register($vars=array(), $callback="", $role="client" ){
        $error = false;
        $message = "";
        $user_name = stripcslashes($vars['email_new']);
        $first_name = stripcslashes($vars['first_name_new']);
        $last_name = stripcslashes($vars['last_name_new']);
        $email = stripcslashes($vars['email_new']);
        $password = $vars['password_new'];
        $nice_name = strtolower($vars['email_new']);
        $billing_country = $vars['country_new'];
        $billing_phone = stripcslashes(isset($vars['phone_new'])?$vars['phone_new']:"");
        $newsletter = isset($vars['newsletter'])?true:false;
        $user_data = array(
        	'user_login' => $user_name,
        	'first_name' => $first_name,
        	'last_name' => $last_name,
        	'user_email' => $email,
        	'user_pass' => $password,
        	'user_nicename' => $nice_name,
        	'display_name' => $first_name.' '.$last_name,
        	//'billing_country' => $billing_country,
        	//'billing_phone' => $billing_phone,
        	'role' => $role
        );
        $user_id = wp_insert_user($user_data);
        if (!is_wp_error($user_id)) {
        	update_user_meta( $user_id, 'billing_first_name', $first_name );
        	update_user_meta( $user_id, 'billing_last_name', $last_name );
        	update_user_meta( $user_id, 'billing_email', $email );
        	update_user_meta( $user_id, 'billing_country', $billing_country );
        	update_user_meta( $user_id, 'billing_phone', $billing_phone );
        	$this->user = get_user_by("ID", $user_id);
			$vars["customer_access"] = "logged";
			$message = 'We have created an account for you.';
			if($newsletter){
				$newsletter_subscribe = new Salt();
				$this->newsletter('subscribe');
			}

			$this->send_activation($user_id);

        } else {
			if (isset($user_id->errors['empty_user_login'])) {
				$error = true;
				$message = $user_id->errors['empty_user_login'][0];
			} elseif (isset($user_id->errors['existing_user_login'])) {
				$error = true;
				$message = $user_id->errors['existing_user_login'][0];
			}elseif (isset($user_id->errors['existing_user_email'])) {
				$error = true;
				$message = $user_id->errors['existing_user_email'][0];
			}else {
				$error = true;
				$message = 'Error Occured please fill up the sign up form carefully.';
			}
		}
		$data = array(
			"error"   => $error,
			"message" => $message,
			"data"    =>  "",
			"resubmit" => true,
			"redirect" => "",
			"html"    => ""
		);
		if(!$error && isset($callback)){
			$data["message"] = "";
			if($callback == "publish_brief"){
				$context = Timber::get_context();
				$context['user'] = $this->user;
				$context['vars'] = $vars;
				$data["html"] = Timber::compile( 'partials/form-user.twig', $context );
			}
		}
		return $data;
    }

    static function send_activation($user_id){
    	$user = get_user_by( 'id', $user_id );
		$code = md5(time());
        $string = array('id'=>$user_id, 'code'=>$code);

	    global $wpdb;    
	    $wpdb->update( 
	        'wp_users',   
	         array( 'user_activation_key' => $code ),       
	         array( 'ID' => $user_id )
	    );

	    $activation_link = add_query_arg( array( 'key' => base64_encode(serialize($string)) ), wc_get_account_endpoint_url( 'profile' ));
	    $from_name = get_bloginfo( 'name' );
	    $from_email = get_bloginfo( 'admin_email' );
	    $headers = array();
        $headers[] = 'From: '.$from_name.' <'. $from_email.'>';
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$headers[] = 'Reply-To: '.$from_name.' <'. $from_email.'>';
	    $mail = wp_mail( $user->user_email, 'Ping90 Activation', 'Activation link : ' . $activation_link, $headers );
	    return $mail;
    }

    static function user_exist($vars=array(), $callback=""){
    	$email = $vars["email"];
    	if(isset($vars["exclude"])){
    	   if($vars["exclude"] == $email){
    	      return false;
    	   }
    	}
    	$exists = email_exists($email);
		if ( $exists ){
		  return "That E-mail is already registered.";
		}else{
		  return false;//"That E-mail doesn't belong to any registered users on this site";
		}
    }

    static function nickname_exist($vars=array(), $callback=""){
    	global $wpdb;
    	$nickname = $vars["nickname"];
    	if(isset($vars["exclude"])){
    	   if($vars["exclude"] == $nickname){
    	      return false;
    	   }
    	}
    	$exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->users as users, $wpdb->usermeta as meta WHERE users.ID = meta.user_id AND meta.meta_key = 'nickname' AND meta.meta_value = %s AND users.ID <> %d", $nickname, get_current_user_id() ) );
		if ( $exists ){
		  return "That user name is already registered.";
		}else{
		  return false;//"That E-mail doesn't belong to any registered users on this site";
		}
    }

    static function user_is_online($user_id) {
		  $logged_in_users = get_transient('users_online');
		  // online, if (s)he is in the list and last activity was less than 15 minutes ago
		  return isset($logged_in_users[$user_id]) && ($logged_in_users[$user_id] > (current_time('timestamp') - (15 * 60)));
	}

	function user_not_activated() {
	    if (is_user_logged_in() && !$this->user->get_status() && WC()->query->get_current_endpoint() != "profile") {
	        wp_safe_redirect(wc_get_account_endpoint_url('profile'));
	        exit();
	    }
	}

	function update_online_users_status(){
	    if(is_user_logged_in()){

		    // get the online users list
			if(($logged_in_users = get_transient('users_online')) === false) $logged_in_users = array();

		    $current_user = wp_get_current_user();
			$current_user = $current_user->ID;  
			$current_time = current_time('timestamp');

		    if(!isset($logged_in_users[$current_user]) || ($logged_in_users[$current_user] < ($current_time - (15 * 60)))){
				$logged_in_users[$current_user] = $current_time;
				set_transient('users_online', $logged_in_users, 30 * 60);
		    }
		}
	}
	function update_online_users_status_logout($user_id=0){
		if($user_id > 0){
			if(($logged_in_users = get_transient('users_online')) === false) $logged_in_users = array();
			unset($logged_in_users[$user_id]);
			set_transient('users_online', $logged_in_users, 30 * 60);
		}
	}

    function notification($event="", $data=array()){
    	if(ENABLE_NOTIFICATIONS){
	    	$user = $this->user;
	    	$notifications = new Notifications($user, false);
			$notifications->load($event);
			$notifications->on($event, $data);
			if($notifications->debug){
				print_r($notifications->debug_output);
			}
		}
    }
    function notification_count(){
    	if(ENABLE_NOTIFICATIONS){
	    	$user = $this->user;
	    	$data = ["get_count" => true, "seen" => 0];
	    	$notifications = new Notifications($user);
			return $notifications->get_notifications($data)["data"]["total"];
		}else{
			return 0;
		}
    }

    /*
    RewriteCond %{REQUEST_METHOD} POST
	RewriteCond %{REQUEST_URI} ^/zitango/wp-admin/
	RewriteCond %{QUERY_STRING} action=up_asset_upload
	RewriteRule (.*) /zitango/index.php?ajax=query&method=message_upload [L,R=307]
	*/

    function send_message_upload($uploaded_file){
			$upload_dir = wp_upload_dir();
			$upload_path = $upload_dir['basedir'];
		    $image_data = file_get_contents( $uploaded_file["tmp_name"]);
		    $filename = basename($uploaded_file['name']);

		    $file_type = wp_check_filetype($filename, null );

		    $base_name = unique_code(12);

		    $filename = $base_name.".".$file_type["ext"];
		    $filename_thumb = $base_name."-thumb.".$file_type["ext"];

		    if(wp_mkdir_p($upload_dir['path']))
				$file = $upload_dir['path'] . '/' . $filename;
			else
				$file = $upload_dir['basedir'] . '/' . $filename;
			file_put_contents($file, $image_data);

			$image_temp_url = $upload_dir['url'].'/'. $filename;
		    $image_temp_url = str_replace("wp-content/uploads", "assets", $image_temp_url);

			$response = array(
                "url" => $image_temp_url
			);
			if(strpos($file_type["type"], "image")>-1){
				$image = wp_get_image_editor(  $upload_dir['basedir'] . '/' .$filename);
				if ( ! is_wp_error( $image ) ) {
				    $image->resize( 200, 200, true );
				    $image->save( $upload_dir['basedir'] . '/' . $filename_thumb );
				}
				$response["thumbnail_url"] = str_replace("wp-content/uploads", "assets", $upload_dir['url'].'/'.$filename_thumb);
			}
			return $response;
    }


	// autologin after registration
	function user_register_hook( $user_id ) {
        
	}
	function user_after_update_hook($user_id, $old_user_data){
		/*$old_user_email = $old_user_data->data->user_email;
	    $user = get_userdata( $user_id );
	    if(is_admin()){
	    	$profile_upgrade = get_field("profile_upgrade", "user_".$user_id);
	    }*/
	}
	function user_before_update_hook($user_id) {
		
	}




    function on_post_delete( $post_id ){
	}
	function on_user_delete($user_id){
	}

    
    // Acf
	function acf_map_embed_update( $value, $post_id, $field ) {
	    if(strpos($value, "<iframe ") !== false){
	        $value = preg_replace('/\\\\/', '', $value);
	        $value = get_iframe_src( $value );
	    }
	    return $value;
	}
	function acf_map_lat_lng( $value, $post_id, $field ) {
		if( 'google_map' === $field['type'] && 'map' === $field['name'] ) {
			update_post_meta( $post_id, 'lat', $value['lat'] );
			update_post_meta( $post_id, 'lng', $value['lng'] );
		}
		if( 'lat' === $field['name'] ) {
			update_post_meta( $post_id, 'lat', $value['lat'] );
		}
		if( 'lng' === $field['name'] ) {
			update_post_meta( $post_id, 'lng', $value['lng'] );
		}
		return $value;
	}


}














function get_site_config(){

	$enable_favorites =  boolval(ENABLE_FAVORITES);
	$enable_search_history =  boolval(ENABLE_SEARCH_HISTORY);

    if ($enable_favorites) {
        $favorites_obj = new Favorites();
        $favorites_obj->update();
        $favorites = $favorites_obj->favorites;
        $favorites = json_encode($favorites, JSON_NUMERIC_CHECK);
        $GLOBALS["favorites"] = $favorites;
    }

	if($enable_search_history){
	    $search_history_obj = new SearchHistory();
	    $search_history = $search_history_obj->format();	
	}

    //pagination type
    $paging_method = get_option("paging_method");//get_field("paging_method", "option");
    $ajax = false;
    $pagination_type = "paged";
    if($paging_method == "ajax_paged" || $paging_method == "ajax_scroll"|| $paging_method == "ajax_load_more"){
        $ajax = true;
    }
    if($paging_method == "ajax_scroll"){
        $pagination_type = "scroll";
    }
    if($paging_method == "ajax_load_more"){
       $pagination_type = "load_more";
    }
    $pagination_count = get_option("paging_count");//get_field("paging_count", "option");
    
    $config = array(
        "ajax"               => $ajax,
        "pagination_type"    => $pagination_type,
        "pagination_count"   => $pagination_count,
        "enable_membership"  => boolval(ENABLE_MEMBERSHIP),
        "enable_favorites"   => $enable_favorites,
        "enable_search_history" => $enable_search_history,
        "enable_cart"        => boolval(ENABLE_CART),
        "enable_filters"     => boolval(ENABLE_FILTERS),
        "enable_chat"     => boolval(ENABLE_CHAT),
        "enable_notifications"     => boolval(ENABLE_NOTIFICATIONS),
        "enable_ecommerce"     => boolval(ENABLE_ECOMMERCE),
        "base_urls"          => $GLOBALS['base_urls']
    );
    if ($enable_favorites) {
       $config["favorites"] = json_decode($favorites, true);
    }
    if ($enable_search_history) {
       $config["search_history"] = json_decode($search_history, true);
    }
    return $config;  
}

function site_config_js(){
	    wp_enqueue_script( 'site_config_vars', get_stylesheet_directory_uri() . '/static/js/ajax.js', array(), '1.0', false );
		$args = get_site_config();

        $args["dictionary"] = $GLOBALS["lang_predefined"];
		wp_localize_script( 'site_config_vars', 'site_config', $args);

        $args = array(
                'url'           =>     home_url().'/',//.qtranxf_getLanguage(),
                'ajax_nonce'    =>     wp_create_nonce( 'ajax' ),
                'assets_url'    =>     get_stylesheet_directory_uri(),
                'title'         =>     ''
        );
        if(class_exists("Redq_YoBro")){
            $user = wp_get_current_user();
            $conversations = yobro_get_all_conversations($user->ID);
            if($conversations){
                $args["conversations"] = $conversations;
            }
        }
        wp_localize_script( 'site_config_vars', 'ajax_request_vars', $args);
}










function get_available_cities($post_type='post', $meta_key='city', $country = '', $selected=''){
	if(empty($country)){
	   $country = wc_get_base_country();
	}
	global $wpdb; 
	$query = "SELECT m.meta_value as city  FROM wp_posts p, wp_postmeta m
			    WHERE p.ID = m.post_id 
			    AND m.meta_key = %s
			    AND p.post_type = %s AND p.post_status = 'publish' group by city order by city ASC";

	$result = $wpdb->prepare($query, [$meta_key, $post_type]);
	$result = $wpdb->get_results($result);
	$output = array();
	foreach($result as $city){
		$output[$city->city] = get_state_by_code($country, $city->city);
	}
	return $output;
}

function get_available_districts($post_type='post', $city = ''){
	global $wpdb; 
	$query = "SELECT m2.meta_value as district FROM wp_posts p, wp_postmeta m, wp_postmeta m2
			    WHERE 
			        m.post_id = p.ID
			    AND m.meta_key = 'city'
			    AND m.meta_value = %s
			    AND m2.post_id  = p.ID
			    AND m2.meta_key  = 'district'
			    AND p.post_type = %s AND p.post_status = 'publish' group by district order by district ASC";

	$result = $wpdb->prepare($query, [$city, $post_type]);
	$result = $wpdb->get_results($result);
	$output = array();
	foreach($result as $district){
		$output[$district->district] = $district->district;
	}
	return $output;
}

function get_posts_by_district($post_type='post', $city = '', $district=''){
	global $wpdb; 
	$query = "SELECT p.*  FROM wp_posts p, wp_postmeta m, wp_postmeta m2
			    WHERE 
			        m.post_id = p.ID
			    AND m.meta_key = 'city'
			    AND m.meta_value = %s
			    AND m2.post_id  = p.ID
			    AND m2.meta_key  = 'district'
			    AND m2.meta_value = %s
			    AND p.post_type = %s AND p.post_status = 'publish' order by p.post_title ASC";
	$result = $wpdb->prepare($query, [$city, $district, $post_type]);
	return Timber::get_posts($wpdb->get_results($result));
}

function get_locations_order_by_city($post_type='post'){
	$args = array(
		   'post_type' => $post_type,
           'posts_per_page' => -1,
           'meta_key' => 'city',
           'orderby' => 'meta_value', 
           'order' => 'ASC'
    );
    $args = array(
    	'post_type' => $post_type,
        'posts_per_page' => -1,
    	'meta_query' => array(
	        'relation' => 'AND',
	        'city' => array(
	            'key' => 'city'
	        ),
	        'district' => array(
	            'key' => 'district'
	        )
	    ),
	    'orderby' => array( 
	        'city' => 'ASC',
	        'district' => 'ASC'
	    )
    );
    return Timber::get_posts($args);
}



// force to set default role to a new registred user
function my_new_customer_data($new_customer_data){
	 $new_customer_data['role'] = get_option( 'default_role' );
	 return $new_customer_data;
}
//add_filter( 'woocommerce_new_customer_data', 'my_new_customer_data');




function orderby_tax_clauses( $clauses, $wp_query ) {
    global $wpdb;
    $taxonomies = get_taxonomies();
    foreach ($taxonomies as $taxonomy) {
        if ( isset( $wp_query->query['orderby'] ) && $taxonomy == $wp_query->query['orderby'] ) {
            $clauses['join'] .=<<<SQL
LEFT OUTER JOIN {$wpdb->term_relationships} ON {$wpdb->posts}.ID={$wpdb->term_relationships}.object_id
LEFT OUTER JOIN {$wpdb->term_taxonomy} USING (term_taxonomy_id)
LEFT OUTER JOIN {$wpdb->terms} USING (term_id)
SQL;
            $clauses['where'] .= " AND (taxonomy = '{$taxonomy}' OR taxonomy IS NULL)";
            $clauses['groupby'] = "object_id";
            $clauses['orderby'] = "GROUP_CONCAT({$wpdb->terms}.name ORDER BY name ASC) ";
            $clauses['orderby'] .= ( 'ASC' == strtoupper( $wp_query->get('order') ) ) ? 'ASC' : 'DESC';
        }
    }
    return $clauses;
}