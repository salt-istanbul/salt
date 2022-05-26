<?php


//add_action('wp_login', 'add_custom_cookie_admin');
function add_custom_cookie_admin() {
    setcookie('logged_in', 1, 3 * DAYS_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN ); // expire in a day
}
//add_action('wp_logout', 'remove_custom_cookie_admin');
function remove_custom_cookie_admin() {
	unset( $_COOKIE['logged_in'] );
    setcookie('logged_in', '', time() - 3600);
}


function login_required(){
    if( !is_user_logged_in() ) {
        if($_SESSION){
           $_SESSION['referer_url'] = current_url();
        }
        wp_redirect(wc_get_account_endpoint_url('my-account'));
        die;
    }
}



add_action( 'init', 'verify_user_code');
function verify_user_code(){
    if(isset($_GET['key'])){
        if(isBase64Encoded($_GET['key'])){
            $data = unserialize(base64_decode($_GET['key']));
            $user_id = $data["id"];
            if($user_id == get_current_user_id()){
                $user = get_user_by( 'id', $user_id );
                $isActivated = get_user_meta($user_id , 'user_status', 1);
                if($isActivated){?>
                   <script>
                        var activation_code_response = "Your account is already verified!";
                        var activation_code_status = true;
                   </script>
                <?php
                }else{
                    $code = $user->user_activation_key;
                    if($code == $data['code']){
                        update_user_meta($user_id, 'user_status', 1);
                        ?>
                           <script>
                               var activation_code_response = "Your account is verified!"
                               var activation_code_status = true;
                           </script>
                        <?php
                    }else{
                        ?>
                           <script>
                               var activation_code_response = "Your activation code is invalid or expired!";
                               var activation_code_status = false;
                           </script>
                        <?php
                    }            
                }
            }else{
                ?>
                   <script>
                       var activation_code_response = "1 Your activation code is invalid!";
                       var activation_code_status = false;
                   </script>
                <?php
            }
        }else{
            ?>
               <script>
                   var activation_code_response = "2Your activation code is invalid!";
                   var activation_code_status = false;
               </script>
            <?php
        }
        add_action( 'wp_head', 'verify_user_code_response' );
    }
}

function verify_user_code_response(){
    ?>
    <script>
    $( document ).ready(function() {
        if(typeof activation_code_response !== "undefined"){
            _alert(activation_code_response);
            if(activation_code_status){
               removeQueryString("key");
            }
        }
    });
    </script>
    <?php
}

function verify_user_code_____(){
    if(isset($_GET['key'])){
        if(isBase64Encoded($_GET['key'])){
            $data = unserialize(base64_decode($_GET['key']));
            $user_id = $data["id"];
            if($user_id == get_current_user_id()){
                $user = get_user_by( 'id', $user_id );
                $isActivated = get_user_meta($user_id , 'user_status', true);
                if($isActivated){?>
                   <script>
                       $( document ).ready(function() {
                           _alert("Your account is already verified!");
                       });
                   </script>
                <?php
                }else{
                    $code = $user->user_activation_key;
                    if($code == $data['code']){
                        update_user_meta($user_id, 'user_status', 1);
                        ?>
                           <script>
                               $( document ).ready(function() {
                                   _alert("Your account is verified!");
                               });
                           </script>
                        <?php
                        //wc_add_notice( __( '<strong>Success:</strong> Your account has been activated! ', 'text-domain' )  );
                    }else{
                        //echo "no ok";
                        //wc_add_notice( __( '<strong>Error:</strong> Your activation code is invalid or expired! ', 'text-domain' )  );
                        ?>
                           <script>
                               $( document ).ready(function() {
                                   _alert("Your activation code is invalid or expired!");
                               });
                           </script>
                        <?php
                    }            
                }
            }else{
                ?>
                   <script>
                       $( document ).ready(function() {
                           _alert("Your activation code is invalid!");
                       });
                   </script>
                <?php
            }
        }else{
            ?>
               <script>
                   $( document ).ready(function() {
                       _alert("Your activation code is invalid!");
                   });
               </script>
            <?php
        }
    }
}




// when user login, we will check whether this guy email is verify
/*add_filter('wp_authenticate_user', 'wp_authenticate_user', 10, 2);
function wp_authenticate_user( $userdata ) {
        $isActivated = get_user_meta($userdata->ID, 'user_status', true);
        if ( !$isActivated ) {
                $text = '<strong>ERROR:</strong> Your account has to be activated before you can login. You can resend by clicking <a href="/sign-in/?u='.$userdata->ID.'">here</a>';
                add_action( 'wp_head', function($text){
                ?>
                       <script>
                           $( document ).ready(function() {
                               _alert("<?php echo $text; ?>");
                           });
                       </script>
                    <?php                    
                });
        $userdata = new WP_Error(
                                'inkfool_confirmation_error',
                                __( '<strong>ERROR:</strong> Your account has to be activated before you can login. You can resend by clicking <a href="/sign-in/?u='.$userdata->ID.'">here</a>', 'inkfool' )
                                );
        }
        return $userdata;
}*/






// start global session for saving the referer url
function start_session() {
    if(!session_id()) {
        session_start();
    }
}
add_action('init', 'start_session', 1);
function login_redirect() {
    if (isset($_SESSION['referer_url'])) {
    	$url = $_SESSION['referer_url'];
        session_write_close();
        session_destroy();
        wp_redirect($url);
    } else {
        wp_redirect(wc_get_account_endpoint_url('dashboard'));
    }
}
add_filter('woocommerce_login_redirect', 'login_redirect', 1100, 2);






//redirect user edit account page after registration
function wc_redirect_to_account_details( $redirect ) {
    $redirect = wc_get_account_endpoint_url('edit-account');
    return $redirect;
}
add_filter( 'woocommerce_registration_redirect', 'wc_redirect_to_account_details' );



/*
add_action( 'register_new_user',
    function($user_id){
       $salt = new Salt();
       $salt->send_activation($user_id);
    }, 10, 1 );

add_action( 'woocommerce_created_customer',
    function($user_id){
       $salt = new Salt();
       $salt->send_activation($user_id);
    }, 10, 1 );
*/

add_action( 'woocommerce_login_form_bottom', 'woo_social_media_login', 10, 2);
function woo_social_media_login($button_only) {
    $button = '<a href="'.get_site_url().'/wp-login.php?loginSocial=facebook&redirect='.get_site_url().'/?login-social=facebook" class="btn btn-social-facebook btn-md btn-reverse" data-plugin="nsl" data-action="connect" data-redirect="current" data-provider="facebook" data-popupwidth="475" data-popupheight="300">' .
                    '<span><i class="fab fa-facebook-square"></i> Login with Facebook</span>' .
                '</a>';
    if($button_only){
        echo $button;
    }else{
        echo '<div class="card-footer">'.
                        '<div class="seperator-or text-light">OR</div>' .
                        '<div class="text-center">'.
                            $button .
                        '</div>' .
             '</div>';        
    }           
}




/*
add_action('init', function() {
    if (!isset($_COOKIE['my_cookie'])) {
        setcookie('my_cookie', 1);
    }
});

wp_set_auth_cookie( int $user_id, true);*/

/* remove my account menu */
remove_action(
	'woocommerce_account_navigation',
	'woocommerce_account_navigation'
);



//modify account menu
add_filter ( 'woocommerce_account_menu_items', 'misha_remove_my_account_links' );
function misha_remove_my_account_links( $menu_links ){
    unset( $menu_links['edit-address'] ); // Addresses
    unset( $menu_links['dashboard'] ); // Dashboard
    unset( $menu_links['payment-methods'] ); // Payment Methods
    unset( $menu_links['orders'] ); // Orders
    unset( $menu_links['downloads'] ); // Downloads
    unset( $menu_links['edit-account'] ); // Account details
    unset( $menu_links['customer-logout'] ); // Logout
    if( is_user_logged_in() ) {
	    $user = wp_get_current_user();
	    switch($user->roles[0]){
	    	case "client" :
                unset( $menu_links['expertise'] ); // Orders
	    	break;
	    }
	}
    return $menu_links;
}


// Add new endpoint page inside "my account" page
add_filter ( 'woocommerce_account_menu_items', 'salt_my_favorites_link', 40 );
function salt_my_favorites_link( $menu_links ){
	$menu_order = array("profile", "expertise", "messages", "notifications", "security", "supplier-requests");//, "verification", "login-method");
	$menu_ordered = array();
	if( is_user_logged_in() ) {
	    $user = wp_get_current_user();
	    switch($user->roles[0]){

            case "administrator" :
               //$menu_links["orders"] = "Payments";
                $menu_links = array_slice( $menu_links, 0, 4, true ) 
                + array( 'profile' => trans('Profile') )
                + array( 'messages' => trans('Messages') )
                + array( 'notifications' => trans('Notifications') )
                + array( 'security' => trans('Security') )
                + array( 'supplier-requests' => trans('Supplier Requests') )
                //+ array( 'login-method' => trans('Login Method') )
                + array_slice( $menu_links, 4, NULL, true );
            break;
	    	
	    	case "client" :
	    	    //$menu_links["orders"] = "Payments";
		    	$menu_links = array_slice( $menu_links, 0, 4, true ) 
				+ array( 'profile' => trans('Profile') )
                + array( 'messages' => trans('Messages') )
				+ array( 'notifications' => trans('Notifications') )
                + array( 'security' => trans('Security') )
				//+ array( 'verification' => trans('Verification') )
				//+ array( 'login-method' => trans('Login Method') )
				+ array_slice( $menu_links, 4, NULL, true );
	    	break;

	    	case "supplier" :
		    	$menu_links = array_slice( $menu_links, 0, 4, true ) 
                + array( 'profile' => trans('Profile') )
                + array( 'expertise' => trans('Expertise') )
                + array( 'messages' => trans('Messages') )
				+ array( 'notifications' => trans('Notifications') )
                + array( 'security' => trans('Security') )
				//+ array( 'verification' => trans('Verification') )
				//+ array( 'login-method' => trans('Login Method') )
				+ array_slice( $menu_links, 4, NULL, true );
	    	break;
	    }
	    foreach ($menu_order as $order) {
	    	if(isset($menu_links[$order])){
	    		$menu_ordered[$order] = $menu_links[$order];
	    	}
	    }
	    $menu_links = $menu_ordered;
	}
    return $menu_links;
}

add_action( 'init', 'salt_add_endpoint' );
function salt_add_endpoint() {
    add_rewrite_endpoint( 'not-activated', EP_PAGES );
	add_rewrite_endpoint( 'profile', EP_PAGES );
	add_rewrite_endpoint( 'expertise', EP_PAGES );
    add_rewrite_endpoint( 'messages', EP_PAGES );
	add_rewrite_endpoint( 'notifications', EP_PAGES );
    add_rewrite_endpoint( 'my-projects', EP_PAGES );
    add_rewrite_endpoint( 'supplier-requests', EP_PAGES );
	//add_rewrite_endpoint( 'verification', EP_PAGES );
    //add_rewrite_endpoint( 'login-method', EP_PAGES );
}

add_filter("woocommerce_get_query_vars", function ($vars) {
    foreach (["not-activated", "profile", "expertise", "messages", "notifications", "security", "my-projects", "supplier-requests"] as $e) {
        $vars[$e] = $e;
    }
    return $vars;
});

add_action( 'woocommerce_account_not-activated_endpoint', 'salt_not_activated_endpoint_content' );
function salt_not_activated_endpoint_content() {
    $templates = array("woo/my-account/not-activated.twig");
    $context = Timber::get_context();
    $context['type'] = "not-activated"; 
    $context['title'] = trans("Activation");
    $context['description'] = trans("Make sure you completed your profile fully. We will watch you with the right client requests according to your profile info.");
    Timber::render($templates , $context);
    // Go to Settings > Permalinks and just push "Save Changes" button.
}

add_action( 'woocommerce_account_profile_endpoint', 'salt_profile_endpoint_content' );
function salt_profile_endpoint_content() {
    if($GLOBALS["user"]->get_status()){
        $templates = array("woo/my-account/profile.twig");
    }else{
        $templates = array("woo/my-account/not-activated.twig");
    }
    $context = Timber::get_context();
    $context['type'] = "profile"; 
    $context['title'] = trans("Profile");
    $context['description'] = trans("Make sure you completed your profile fully. We will watch you with the right client requests according to your profile info.");
	Timber::render($templates , $context);
	// Go to Settings > Permalinks and just push "Save Changes" button.
}

add_action( 'woocommerce_account_expertise_endpoint', 'salt_expertise_endpoint_content' );
function salt_expertise_endpoint_content() {
    $user_id = $GLOBALS["user"]->id;
    if($GLOBALS["user"]->role == "client"){
        wp_safe_redirect( wc_get_account_endpoint_url( 'profile' ) );
        exit();
    }
    $templates = array("woo/my-account/expertise.twig");
    $context = Timber::get_context();
    $context['type'] = "expertise"; 
    $context['title'] = trans("Expertise");
    $context['description'] = trans("Ping90 search engine will list you in the client searches according to your choices and information you put on this page.");

    $expertises = get_terms_for_user($user_id, 'expertise');
    $context['expertises'] = $expertises;

    $args = array(
          'taxonomy' => 'packs',
          'hide_empty' => false,
          'parent'     => 0,
          'orderby' => 'menu_order',
          'order' => 'ASC',
          'meta_query' => array(
                               array(
                                  'key'       => 'pack_type',
                                  'value'     => 'easy',
                                  'compare'   => '='
                               )
          )
    );
    $packs = Timber::get_terms($args);
    $user_packs = get_field("pack_rates", "user_".$user_id);
    $work_types = Timber::get_terms("work-type");
    $pack_activity = array();
    $packs_filtered = array();
    foreach($packs as $pack){
       foreach($work_types as $work_type){
          if($work_type->term_id == $pack->work_type){
             if(!isset($packs_filtered[$work_type->slug])){
                $packs_filtered[$work_type->slug] = array();
                $pack_activity[$work_type->slug] = false;
             }
             $selected = false;
             $price = "";
             if($user_packs){
                foreach($user_packs as $user_pack){
                    if($user_pack["pack"] == $pack->term_id){
                       $selected = true;
                       $price = $user_pack["price"];
                       $pack_activity[$work_type->slug] = true;
                       continue;
                    }
                }
             }
             //print_r(wp_list_pluck( $expertises, 'term_id' ));
             if(!$selected && in_array($pack->expertise, wp_list_pluck( $expertises, 'term_id' ))){
                $selected = true;
             }
             $packs_filtered[$work_type->slug][] = array(
                "id" => $pack->term_id,
                "name" => $pack->name,
                "slug" => $pack->slug,
                "work_type" => $work_type->slug,
                "pack_type" => "easy",
                "description" => $pack->description,
                "expertise" => $pack->expertise,
                "selected" => $selected,
                "price" => $price
             );
          }
          if(isset($packs_filtered[$work_type->slug])){
              $price_sort = array_column($packs_filtered[$work_type->slug], 'price');
              array_multisort($price_sort, SORT_DESC, $packs_filtered[$work_type->slug]);            
          }
       }   
    }
    $context['work_types'] = $work_types;
    $context['packs'] = $packs_filtered;//json_encode($packs_filtered, JSON_NUMERIC_CHECK);
    $context['pack_activity'] = $pack_activity;
    //$salt = new Salt();
    //$user_packs = $salt->suppliers(["action" => "packs"]);
    //$context["user_packs"] = $user_packs["data"];
    Timber::render($templates , $context);
    // Go to Settings > Permalinks and just push "Save Changes" button.
}

add_action( 'woocommerce_account_messages_endpoint', 'salt_messages_endpoint_content' );
function salt_messages_endpoint_content() {
    $user_id = $GLOBALS["user"]->id;
    $templates = array("woo/my-account/messages.twig");
    $context = Timber::get_context();
    $context['type'] = "messages"; 
    $context['title'] = trans("Messages");
    $context['description'] = trans("Description text here.");
    Timber::render($templates , $context);
    // Go to Settings > Permalinks and just push "Save Changes" button.
}

add_action( 'woocommerce_account_notifications_endpoint', 'salt_notifications_endpoint_content' );
function salt_notifications_endpoint_content() {
    $user_id = $GLOBALS["user"]->id;
    $templates = array("woo/my-account/notifications.twig");
    $context = Timber::get_context();
    $context['type'] = "notifications"; 
    $context['title'] = trans("Notifications");
    $context['description'] = trans("Description text here.");
    Timber::render($templates , $context);
    // Go to Settings > Permalinks and just push "Save Changes" button.
}

add_action( 'woocommerce_account_security_endpoint', 'salt_security_endpoint_content' );
function salt_security_endpoint_content() {
    $templates = array("woo/my-account/security.twig");
    $context = Timber::get_context();
    $context['type'] = "security"; 
    $context['title'] = trans("Security");
    $context['description'] = trans("Make sure you completed your profile fully. We will watch you with the right client requests according to your profile info.");
    Timber::render($templates , $context);
    // Go to Settings > Permalinks and just push "Save Changes" button.
}

add_action( 'woocommerce_account_my-projects_endpoint', 'salt_my_projects_endpoint_content' );
function salt_my_projects_endpoint_content() {
    $templates = array("woo/my-account/my-projects.twig");
    $context = Timber::get_context();
    $context['type'] = "my-projects"; 
    $context['title'] = trans("My Projects");
    $context['description'] = trans("Description text here.");
    Timber::render($templates , $context);
    // Go to Settings > Permalinks and just push "Save Changes" button.
}

add_action( 'woocommerce_account_supplier-requests_endpoint', 'salt_supplier_requests_endpoint_content' );
function salt_supplier_requests_endpoint_content() {
    $context = Timber::get_context();
    $context['type'] = "supplier-requests"; 
    $context['title'] = trans("Supplier Requests");

        if(isset($GLOBALS["user"]->roles["administrator"])){
            $template = "woo/my-account/supplier-requests.twig";

            $no = 20;
            $paged = 1;
            $path = $_SERVER['REQUEST_URI'];
            if(strpos($path, '/page/')){
                $path = explode("/page/", $path);
                if(count($path)>1){
                   $paged = str_replace('/', '' , $path[1]);
                }
            }

            if($paged == 1){
               $offset = 0;  
            }else {
               $offset = ($paged-1)*$no;
            }
            $args = array( 
                        'role' => 'client',
                        'number' => $no, 
                        'offset' => $offset,
                        'meta_query' => array(
                              array(
                                'key' => 'profile_upgrade',
                                'value' => 'requested',
                                'compare' => 'LIKE'
                              )
                        )
            );
            $users = new WP_User_Query($args);
            $total_user = $users->total_users;  
            $total_pages=ceil($total_user/$no);
            /*$pagination = paginate_links(array(  
                  'base' => get_pagenum_link(1) . '%_%',  
                  'format' => '?paged=%#%',  
                  'current' => $paged,  
                  'total' => $total_pages,  
                  'prev_text' => 'Previous',  
                  'next_text' => 'Next',
                  'type'     => 'list',
            ));*/
            $context['paged'] = $paged;
            $context['total_pages'] = $total_pages;
            $context['users'] = $users->get_results();
        }else{
            $template = "no-authorization-sm.twig";
            $icon = "<i class='fa fa-user-slash'></i>";
            $title = "No Access!";
            $message = "You have not any access to view this page.";
            $context["icon"] = $icon;
            $context["title"] = $title;
            $context["message"] = $message;
        }

    $templates = array($template);    
    Timber::render($templates , $context);
    // Go to Settings > Permalinks and just push "Save Changes" button.
}



/*add_action( 'woocommerce_account_verification_endpoint', 'salt_verification_endpoint_content' );
function salt_verification_endpoint_content() {
    $templates = array("woo/my-account/verification.twig");
    $context = Timber::get_context();
    $context['type'] = "verification"; 
    $context['title'] = trans("Verification");
    $context['description'] = trans("Description text here.");
    Timber::render($templates , $context);
    // Go to Settings > Permalinks and just push "Save Changes" button.
}

add_action( 'woocommerce_account_login-method_endpoint', 'salt_login_method_endpoint_content' );
function salt_login_method_endpoint_content() {
    $templates = array("woo/my-account/login-method.twig");
    $context = Timber::get_context();
    $context['type'] = "login-method"; 
    $context['title'] = trans("Login Method");
    $context['description'] = trans("Description text here.");
    Timber::render($templates , $context);
    // Go to Settings > Permalinks and just push "Save Changes" button.
}
*/


//redirect dashboard page to profile page
add_action('template_redirect', 'misha_redirect_to_orders_from_dashboard' );
function misha_redirect_to_orders_from_dashboard(){
	if( is_user_logged_in() ) {
		if( is_account_page() && empty( WC()->query->get_current_endpoint()) ){
			wp_safe_redirect( wc_get_account_endpoint_url( 'profile' ) );
			exit;
		}
	}
}

//add_action('template_redirect', 'misha_redirect_to_orders_from_dashboard' );
/*function misha_redirect_to_orders_from_dashboard(){
	if( is_account_page() && empty( WC()->query->get_current_endpoint() ) ){
		if( is_user_logged_in() ) {
		    $user = wp_get_current_user();
		    switch($user->roles[0]){
		    	case "customer" :
		    	   $endpoint = "my-trips";
		    	   break;
		    	case "agent" :
		    	   $endpoint = "requests";
		    	   break;
		    	case "administrator" :
	               $endpoint = "edit-account";
		    	   break;
		    }
	    }
		wp_safe_redirect( wc_get_account_endpoint_url( $endpoint ) );
		exit;
	}
}*/














//add_action( 'woocommerce_account_my-favorites_endpoint', 'salt_my_favorites_endpoint_content' );
function salt_my_favorites_endpoint_content() {
	$favorites = json_decode(get_user_meta(get_current_user_id(), 'wpcf_favorites', true));
	$templates = array("woo/my-favorites.twig");
    $context = Timber::get_context();
    $context['type'] = "favorites"; 
    $context['title'] = trans("Favorites");	
	$context['favorites'] = $favorites;
	Timber::render($templates , $context);
	// Go to Settings > Permalinks and just push "Save Changes" button.
}

//add_action( 'woocommerce_account_my-trips_endpoint', 'salt_my_trips_endpoint_content' );
function salt_my_trips_endpoint_content() {
	$templates = array("woo/my-trips.twig");
    $context = Timber::get_context();
    $context['type'] = "my-trips"; 
     if(!isset($GLOBALS["user"]->roles["administrator"])){
       $context['title'] = trans("My Trips");
	}else{
       $context['title'] = trans("Requests");
	}
    //pagination
    $page = 1;
    $page_var = get_query_var($context['type']);
    if(!empty($page_var)){
       $page = filter_var($page_var, FILTER_SANITIZE_NUMBER_INT);
       if(!is_numeric($page)){
       	  $page = 1;
       }
    }
    //status
	$tour_status = get_query_var("tour-status");
    if (empty($tour_status)){
    	$tour_status = "on-hold";
    }
    $args = array(
    	"post_type" => "tour-plan",
    	"meta_key"  => "tour_plan_status",
    	"meta_value" => $tour_status,
    	//"author"     => $GLOBALS["user"]->id,
    	"paged"      => $page,
    	"meta_query" => array(
                    'relation' => 'OR',
                 	array(
	                    "key" => "tour_cancellation",
	                    'value'   => "deleted",
	                    'compare' => '!='
                    ),
                    array( 
                        'key' => 'tour_cancellation', 
                        'compare' => 'NOT EXISTS'
                    )
        )
    );
    if(!isset($GLOBALS["user"]->roles["administrator"])){
       $args["author"] = $GLOBALS["user"]->id;
    }
    //print_r($args);
    $context['tour_status'] = $tour_status;	
    $context['posts'] = new Timber\PostQuery($args);
	Timber::render($templates , $context);
	// Go to Settings > Permalinks and just push "Save Changes" button.
}

//add_action( 'woocommerce_account_my-reviews_endpoint', 'salt_my_reviews_endpoint_content' );
function salt_my_reviews_endpoint_content() {
	$comments = getCommentRating(array(
	        "type" => "customer",
	        "id"   => get_current_user_id(),
	        "number" => 1
	));
	$templates = array("woo/my-reviews.twig");
    $context = Timber::get_context();
    $context['type'] = "reviews"; 
    $context['title'] = trans("My Reviews");	
	$context['comments'] = $comments;
	Timber::render($templates , $context);
	// Go to Settings > Permalinks and just push "Save Changes" button.
}

//add_action( 'woocommerce_account_requests_endpoint', 'salt_requests_endpoint_content' );
function salt_requests_endpoint_content() {
	$templates = array("woo/my-trips.twig");
    $context = Timber::get_context();
    $context['type'] = "requests"; 
    $context['title'] = trans("Requests");	
    //pagination
    $page = 1;
    $page_var = get_query_var($context['type']);
    if(!empty($page_var)){
       $page = filter_var($page_var, FILTER_SANITIZE_NUMBER_INT);
       if(!is_numeric($page)){
       	  $page = 1;
       }
    }
    //status
	$tour_status = get_query_var("tour-status");
    if (empty($tour_status)){
    	$tour_status = "processing";
    }

    $field_value = sprintf( '^%1$s$|s:%2$u:"%1$s";', $GLOBALS["user"]->id, strlen( $GLOBALS["user"]->id ) );
    $args = array(
    	"post_type" => "tour-plan",
    	"meta_query" => array(
                 	array(
	                    "key" => "tour_plan_agents",
	                    'value'   => $field_value,
	                    'compare' => 'REGEXP'
                    ),
                    array(
	                    "key" => "tour_plan_status",
	                    'value'   => $tour_status,
	                    'compare' => '='
                    ) 
        ),
    	"paged"      => $page
    );
    $context['tour_status'] = $tour_status;	
    $context['posts'] = new Timber\PostQuery($args);
	Timber::render($templates , $context);
	// Go to Settings > Permalinks and just push "Save Changes" button.
}










/*
add_filter( 'woocommerce_account_menu_items', 'bbloomer_remove_address_my_account', 999 );
function bbloomer_remove_address_my_account( $items ) {
	unset($items['edit-address']);
	return $items;
}
 
// -------------------------------
// 2. Second, print the ex tab content into an existing tab (edit-account in this case)
 
add_action( 'woocommerce_account_edit-account_endpoint', 'woocommerce_account_edit_address' );
*/




//get My Account page titles
function wpb_woo_endpoint_title( $title, $id ) {
    if ( is_wc_endpoint_url( 'downloads' ) && in_the_loop() ) { // add your endpoint urls
        $title = "Download MP3s"; // change your entry-title
    }
    elseif ( is_wc_endpoint_url( 'orders' ) && in_the_loop() ) {
        $title = "My Orders";
    }
    elseif ( is_wc_endpoint_url( 'edit-account' ) && in_the_loop() ) {
        $title = "Change My Details";
    }
    return $title;
}
add_filter( 'the_title', 'wpb_woo_endpoint_title', 10, 2 );












/*
* WooCommerce login/register sayfasındaki kayıt ol formuna yeni alanlar ekler
*/
function register_extra_fields_to_form_start() {
	?>
    	<div class="col-md-6">
    		<div class="form-group form-group-xs">
				<label class="form-label d-none" for="reg_billing_first_name"><?php _e( 'First name', 'woocommerce' ); ?><span class="required">*</span></label>
				<input type="text" class="form-control form-control-lg--" name="billing_first_name" placeholder="<?php _e( 'First name', 'woocommerce' ); ?>" id="reg_billing_first_name" value="<?php if ( ! empty( $_POST['billing_first_name'] ) ) esc_attr_e( $_POST['billing_first_name'] ); ?>" required/>
			</div>
		</div>

    	<div class="col-md-6">
    		<div class="form-group form-group-xs">
				<label class="form-label d-none" for="reg_billing_last_name"><?php _e( 'Last name', 'woocommerce' ); ?><span class="required">*</span></label>
		    	<input type="text" class="form-control form-control-lg--" name="billing_last_name" placeholder="<?php _e( 'Last name', 'woocommerce' ); ?>"  id="reg_billing_last_name" value="<?php if ( ! empty( $_POST['billing_last_name'] ) ) esc_attr_e( $_POST['billing_last_name'] ); ?>" required/>
		    </div>
		</div>
	<?php
}
//add_action( 'woocommerce_register_form_start', 'register_extra_fields_to_form_start' );
/**
* Yeni eklenen alanlar doldurulmadığında kullanıcıya uyarı verilmesi sağlanır.
*/
function validate_extra_fields_to_form_start( $username, $email, $validation_errors ) {
	if ( isset( $_POST['billing_first_name'] ) && empty( $_POST['billing_first_name'] ) ) {
		$validation_errors->add( 'billing_first_name_error', __( 'Please Write your first name.', 'woocommerce' ) );
	}
	if ( isset( $_POST['billing_last_name'] ) && empty( $_POST['billing_last_name'] ) ) {
		$validation_errors->add( 'billing_last_name_error', __( 'Please write your last name.', 'woocommerce' ) );
	}
}
//add_action( 'woocommerce_register_post', 'validate_extra_fields_to_form_start', 10, 3 );
/**
* Extra eklenen alanların panel tarafına kayıt etmesini sağlar.
*/
function save_extra_fields_to_form_start( $customer_id ) {
	if ( isset( $_POST['billing_first_name'] ) ) {
		// WordPress default first name field.
		update_user_meta( $customer_id, 'first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
		// WooCommerce billing first name.
		update_user_meta( $customer_id, 'billing_first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
	}
	if ( isset( $_POST['billing_last_name'] ) ) {
		// WordPress default last name field.
		update_user_meta( $customer_id, 'last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
		// WooCommerce billing last name.
		update_user_meta( $customer_id, 'billing_last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
	}
}
//add_action( 'woocommerce_created_customer', 'save_extra_fields_to_form_start' );







function register_extra_fields_to_form_end() {
    ?>
        <div class="col-md-6">
            <div class="form-group form-group-xs">
                <select class="selectpicker bootstrap-select-lg-" name="billing_country" data-size="10" data-live-search="true" data-required>
                    <?php
                    $countries = get_countries();
                    if($countries){
                        foreach($countries as $continent){ ?>
                            <optgroup label="<?php echo $continent["name"]; ?>">
                                <?php
                                foreach($continent["countries"] as $country){ ?>

                                    <option value="<?php echo $country["slug"]; ?>" <?php if($country["slug"] == "US"){?>selected<?php } ?>>
                                       <?php echo $country["name"]; ?>
                                    </option>
                                <?php
                            }
                        }?>
                        </optgroup>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group form-group-xs">
                <input type="text" class="form-control form-control-lg--" name="billing_phone" placeholder="<?php _e( 'Phone', 'woocommerce' ); ?>"  id="billing_phone" value="<?php if ( ! empty( $_POST['billing_phone'] ) ) esc_attr_e( $_POST['billing_phone'] ); ?>" required/>
            </div>
        </div>

    <?php
}
//add_action( 'woocommerce_register_form_start', 'register_extra_fields_to_form_end' );
/**
* Yeni eklenen alanlar doldurulmadığında kullanıcıya uyarı verilmesi sağlanır.
*/
function validate_extra_fields_to_form_end( $username, $email, $validation_errors ) {
    if ( isset( $_POST['billing_country'] ) && empty( $_POST['billing_country'] ) ) {
        $validation_errors->add( 'billing_country_error', __( 'Please choose your country.', 'woocommerce' ) );
    }
    if ( isset( $_POST['billing_phone'] ) && empty( $_POST['billing_phone'] ) ) {
        $validation_errors->add( 'billing_phone_error', __( 'Please write your phone number.', 'woocommerce' ) );
    }
}
//add_action( 'woocommerce_register_post', 'validate_extra_fields_to_form_end', 10, 3 );
/**
* Extra eklenen alanların panel tarafına kayıt etmesini sağlar.
*/
function save_extra_fields_to_form_end( $customer_id ) {
    if ( isset( $_POST['billing_country'] ) ) {
        // WordPress default first name field.
        //update_user_meta( $customer_id, 'first_name', sanitize_text_field( $_POST['billing_country'] ) );
        // WooCommerce billing first name.
        update_user_meta( $customer_id, 'billing_country', sanitize_text_field( $_POST['billing_country'] ) );
    }
    if ( isset( $_POST['billing_phone'] ) ) {
        // WordPress default last name field.
        //update_user_meta( $customer_id, 'last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
        // WooCommerce billing last name.
        update_user_meta( $customer_id, 'billing_phone', sanitize_text_field( $_POST['billing_phone'] ) );
    }
}
//add_action( 'woocommerce_created_customer', 'save_extra_fields_to_form_end' );















// Add the custom field "favorite_color"
//add_action( 'woocommerce_edit_account_form', 'add_favorite_color_to_edit_account_form' );
function add_favorite_color_to_edit_account_form() {
    $user = wp_get_current_user();
    //print_r(get_user_locale($user));
    if ( in_array( 'corporate', (array) $user->roles ) ) {
    ?>
    <div class="card-module card mb-4">
    	<div class="card-header">
    		<h3 class="card-title">
    			 <?php _e('Kurumsal Bilgiler'); ?>
    		</h3>
        </div>
        <div class="card-body">
		    <div class="row">
		    	<div class="col-sm-6">
				    <div class="form-group form-group-sm">
				        <label for="billing_company"><?php _e( 'Şirketin Ticari Ünvanı', 'woocommerce' ); ?></label>
				        <input type="text" class="form-control" name="billing_company" id="billing_company" placeholder="Şirketin Ticari Ünvanı" value="<?php echo esc_attr( $user->billing_company ); ?>" required>
				    </div>
				</div>
				<div class="col-sm-6">
				    <div class="form-group form-group-sm">
				        <label class="label-form" for="corporate_type"><?php _e( 'Corporate Type', 'woocommerce' ); ?></label>
				        <select class="selectpicker" name="corporate_type" id="corporate_type" data-show-reset="true" data-title="<?php _e( 'Corporate Type', 'woocommerce' ); ?>">
				        	<option value="limited-anonim" data-show=".firma-appendix" <?php if($user->corporate_type=='limited-anonim'){ ?>selected<?php } ?>><?php _e( 'Limited/Anonim', 'woocommerce' ); ?></option>
				        	<option value="sahis" data-show=".sahis-appendix" <?php if($user->corporate_type=='sahis'){ ?>selected<?php } ?>><?php _e( 'Şahıs', 'woocommerce' ); ?></option>
				        </select>
				    </div>
				</div>
				<div class="col-sm-6 firma-appendix collapse">
				    <div class="form-group form-group-sm">
				        <label for="corporate_tax_no"><?php _e( 'Vergi Kimlik No', 'woocommerce' ); ?></label>
				        <input type="text" class="form-control" name="corporate_tax_no" id="corporate_tax_no" placeholder="Vergi Kimlik No" value="<?php echo esc_attr( $user->corporate_tax_no ); ?>" />
				    </div>
				</div>
				<div class="col-sm-6 sahis-appendix collapse">
				    <div class="form-group form-group-sm">
				        <label for="corporate_id_no"><?php _e( 'TC Kimlik No', 'woocommerce' ); ?></label>
				        <input type="text" class="form-control" name="corporate_id_no" id="corporate_id_no" placeholder="TC Kimlik No" value="<?php echo esc_attr( $user->corporate_id_no ); ?>" />
				    </div>
				</div>
				<div class="col-sm-6">
				    <div class="form-group form-group-sm">
				        <label for="corporate_register_number"><?php _e( 'Ticari Sicil No', 'woocommerce' ); ?></label>
				        <input type="text" class="form-control" name="corporate_register_number" id="corporate_register_number" placeholder="Ticari Sicil No" value="<?php echo esc_attr( $user->corporate_register_number ); ?>" />
				        <small class="text-muted">Şirketinizin Ticaret odası Ticari Sicil Belgesinde bulunan Ticaret sicil numarasını giriniz.</small>
				    </div>
				</div>
			</div>
		</div>
	</div>
    <?php
    }

    if ( in_array( 'administrator', (array) $user->roles ) || in_array( 'customer', (array) $user->roles )) {
    ?>
    <div class="card-module card mb-4">
    	<div class="card-header">
    		<h3 class="card-title">
    			 <?php _e('Personal Details'); ?>
    		</h3>
        </div>

        <div class="card-body">
		    <div class="row">
		    	<div class="col-sm-6">
		    		<div class="form-group">
		                <label class="form-label form-label-md mb-2"><?php echo trans("Birthdate") ?></label>
		                <div class="input-group date">
		                    <input type="text" id="birthdate" name="birthdate" class="form-control form-control-date datepicker" value="<?php echo esc_attr( $user->birthdate ); ?>" placeholder="aa.gg.yyyy" aria-label="Birthdate" aria-describedby="Birthdate" data-error="Zorunlu alan" autocomplete="off" readonly required>
		                    <div class="input-group-append input-group-addon">
		                        <span class="input-group-text" id="birthdate-addon"><span class="far fa-calendar"></span></span>
		                    </div>
		                </div>
		            </div>
				</div>
				<div class="col-sm-6">
				    <div class="form-group form-group-sm">
				        <label class="form-label form-label-md mb-2" for="gender"><?php echo trans("Gender") ?></label>
				        <select class="selectpicker" name="gender" id="gender" data-title="<?php echo trans("Gender") ?>">
				        	<option value="Man" <?php if($user->gender=='Man'){ ?>selected<?php } ?>><?php echo trans("Man") ?></option>
				        	<option value="Woman" <?php if($user->gender=='Woman'){ ?>selected<?php } ?>><?php echo trans("Woman") ?></option>
				        	<option value="Other" <?php if($user->gender=='Other'){ ?>selected<?php } ?>><?php echo trans("Other") ?></option>
				        	<option value="Prefer not to say" <?php if($user->gender=='Prefer not to say'){ ?>selected<?php } ?>><?php echo trans("Prefer not to say") ?></option>
				        </select>
				    </div>
				</div>
				<!--<div class="col-sm-6">
				    <div class="form-group form-group-sm">
				        <label class="form-label form-label-md mb-2" for="billing_mobile_phone"><?php _e( 'Mobile phone', 'woocommerce' ); ?></label>
				        <input type="text" class="form-control" name="billing_mobile_phone" id="billing_mobile_phone" value="<?php echo esc_attr( $user->billing_mobile_phone ); ?>" />
				    </div>
				</div>

				<div class="col-sm-6">
				    <div class="form-group form-group-sm">
				        <label class="form-label" for="language"><?php echo trans("Language") ?></label>
				        <select class="selectpicker" name="language" id="language" data-title="<?php echo trans("Language") ?>">
				        	<option value="tr" <?php if($user->language=='tr'){ ?>selected<?php } ?>><?php echo trans("Türkçe") ?></option>
				        	<option value="en" <?php if($user->language=='en'){ ?>selected<?php } ?>><?php echo trans("İngilizce") ?></option>
				        </select>
				    </div>
				</div>-->
			</div>
		</div>
	</div>
    <?php
    }
}
//add_action( 'woocommerce_save_account_details', 'save_favorite_color_account_details', 12, 1 );
function save_favorite_color_account_details( $user_id ) {
	$user = get_user_by('id', $user_id);
	//print_r($_POST);
    if ( in_array( 'corporate', (array) $user->roles ) ) {
	    if( isset( $_POST['billing_company'] ) )
	        update_user_meta( $user_id, 'billing_company', sanitize_text_field( $_POST['billing_company'] ) );

	    if( isset( $_POST['corporate_type'] ) )
	        update_user_meta( $user_id, 'corporate_type', sanitize_text_field( $_POST['corporate_type'] ) );

	    if( isset( $_POST['corporate_tax_no'] ) )
	        update_user_meta( $user_id, 'corporate_tax_no', sanitize_text_field( $_POST['corporate_tax_no'] ) );

	    if( isset( $_POST['corporate_id_no'] ) )
	        update_user_meta( $user_id, 'corporate_id_no', sanitize_text_field( $_POST['corporate_id_no'] ) );

	    if( isset( $_POST['corporate_register_number'] ) )
	        update_user_meta( $user_id, 'corporate_register_number', sanitize_text_field( $_POST['corporate_register_number'] ) );
    }

    if ( in_array( 'administrator', (array) $user->roles )  || in_array( 'customer', (array) $user->roles )) {
    	if( isset( $_POST['birthdate'] ) )
	        update_user_meta( $user_id, 'birthdate', sanitize_text_field( $_POST['birthdate'] ) );

	    if( isset( $_POST['gender'] ) )
	        update_user_meta( $user_id, 'gender', sanitize_text_field( $_POST['gender'] ) );

	    if( isset( $_POST['billing_mobile_phone'] ) )
	        update_user_meta( $user_id, 'billing_mobile_phone', sanitize_text_field( $_POST['billing_mobile_phone'] ) );

	    /*if( isset( $_POST['language'] ) )
	        update_user_meta( $user_id, 'language', sanitize_text_field( $_POST['language'] ) );*/
    }
}
//disable "Display Name" require in edit account page
//add_filter('woocommerce_save_account_details_required_fields', 'wc_save_account_details_required_fields' );
function wc_save_account_details_required_fields( $required_fields ){
    unset( $required_fields['account_display_name'] );
    unset( $required_fields['password_1'] );
    unset( $required_fields['password_2'] );
    return $required_fields;
}











function woocommerce_form_field( $key, $args, $value = null ) {
		$defaults = array(
			'type'              => 'text',
			'label'             => '',
			'description'       => '',
			'placeholder'       => '',
			'maxlength'         => false,
			'required'          => false,
			'autocomplete'      => false,
			'id'                => $key,
			'class'             => array(),
			'label_class'       => array(),
			'input_class'       => array(),
			'container_class'   => array(),
			'return'            => false,
			'options'           => array(),
			'custom_attributes' => array(),
			'validate'          => array(),
			'default'           => '',
			'autofocus'         => '',
			'priority'          => '',
		);

		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'woocommerce_form_field_args', $args, $key, $value );

		if ( $args['required'] ) {
			$args['class'][] = 'validate-required';
			$required        = '&nbsp;<abbr class="required" title="' . esc_attr__( 'required', 'woocommerce' ) . '">*</abbr>';
		} else {
			$required = '&nbsp;<span class="optional">(' . esc_html__( 'optional', 'woocommerce' ) . ')</span>';
		}

		if ( is_string( $args['label_class'] ) ) {
			$args['label_class'] = array( $args['label_class'] );
		}

		if ( is_string( $args['container_class'] ) ) {
			$args['container_class'] = array( $args['container_class'] );
		}

		if ( is_null( $value ) ) {
			$value = $args['default'];
		}

		// Custom attribute handling.
		$custom_attributes         = array();
		$args['custom_attributes'] = array_filter( (array) $args['custom_attributes'], 'strlen' );

        if($args['required']){
            $args['custom_attributes']["required"] = "";
        }

		if ( $args['maxlength'] ) {
			$args['custom_attributes']['maxlength'] = absint( $args['maxlength'] );
		}

		if ( ! empty( $args['autocomplete'] ) ) {
			$args['custom_attributes']['autocomplete'] = $args['autocomplete'];
		}

		if ( true === $args['autofocus'] ) {
			$args['custom_attributes']['autofocus'] = 'autofocus';
		}

		if ( $args['description'] ) {
			$args['custom_attributes']['aria-describedby'] = $args['id'] . '-description';
		}

		if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
			foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		if ( ! empty( $args['validate'] ) ) {
			foreach ( $args['validate'] as $validate ) {
				$args['class'][] = 'validate-' . $validate;
			}
		}

		$field           = '';
		$label_id        = $args['id'];
		$sort            = $args['priority'] ? $args['priority'] : '';
		$field_container = '';
		if(count($args['container_class'])>0){
		   $field_container .= '<div class="'.implode( ' ', $args['container_class'] ).'">';
		}
		$field_container .= '<div class="form-group form-row-- %1$s" id="%2$s" data-priority="' . esc_attr( $sort ) . '">%3$s</div>';
		if(count($args['container_class'])>0){
		   $field_container .= '</div>';
		}

		switch ( $args['type'] ) {
			case 'country':
				$countries = 'shipping_country' === $key ? WC()->countries->get_shipping_countries() : WC()->countries->get_allowed_countries();

				if ( 1 === count( $countries ) ) {

					$field .= '<strong>' . current( array_values( $countries ) ) . '</strong>';

					$field .= '<input type="hidden" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="' . current( array_keys( $countries ) ) . '" ' . implode( ' ', $custom_attributes ) . ' class="country_to_state" readonly="readonly" />';

				} else {

					$field = '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="country_to_state country_select ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . '><option value="">' . esc_html__( 'Select a country&hellip;', 'woocommerce' ) . '</option>';

					foreach ( $countries as $ckey => $cvalue ) {
						$field .= '<option value="' . esc_attr( $ckey ) . '" ' . selected( $value, $ckey, false ) . '>' . $cvalue . '</option>';
					}

					$field .= '</select>';

					$field .= '<noscript><button type="submit" name="btn btn-base btn-sm btn-extend woocommerce_checkout_update_totals" value="' . esc_attr__( 'Update country', 'woocommerce' ) . '">' . esc_html__( 'Update country', 'woocommerce' ) . '</button></noscript>';

				}

				break;
			case 'state':
				/* Get country this state field is representing */
				$for_country = isset( $args['country'] ) ? $args['country'] : WC()->checkout->get_value( 'billing_state' === $key ? 'billing_country' : 'shipping_country' );
				$states      = WC()->countries->get_states( $for_country );

				if ( is_array( $states ) && empty( $states ) ) {

					$field_container = '<p class="form-group form-row-- %1$s" id="%2$s" style="display: none">%3$s</p>';

					$field .= '<input type="hidden" class="hidden" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="" ' . implode( ' ', $custom_attributes ) . ' placeholder="' . esc_attr( $args['placeholder'] ) . '" readonly="readonly" data-input-classes="' . esc_attr( implode( ' ', $args['input_class'] ) ) . '"/>';

				} elseif ( ! is_null( $for_country ) && is_array( $states ) ) {

					$field .= '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="state_select ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . ' data-placeholder="' . esc_attr( $args['placeholder'] ? $args['placeholder'] : esc_html__( 'Select an option&hellip;', 'woocommerce' ) ) . '"  data-input-classes="' . esc_attr( implode( ' ', $args['input_class'] ) ) . '">
						<option value="">' . esc_html__( 'Select an option&hellip;', 'woocommerce' ) . '</option>';

					foreach ( $states as $ckey => $cvalue ) {
						$field .= '<option value="' . esc_attr( $ckey ) . '" ' . selected( $value, $ckey, false ) . '>' . $cvalue . '</option>';
					}

					$field .= '</select>';

				} else {

					$field .= '<input type="text" class="form-control input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="' . esc_attr( $value ) . '"  placeholder="' . esc_attr( $args['placeholder'] ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" ' . implode( ' ', $custom_attributes ) . ' data-input-classes="' . esc_attr( implode( ' ', $args['input_class'] ) ) . '"/>';

				}

				break;
			case 'textarea':
				$field .= '<textarea name="' . esc_attr( $key ) . '" class="form-control input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" ' . ( empty( $args['custom_attributes']['rows'] ) ? ' rows="2"' : '' ) . ( empty( $args['custom_attributes']['cols'] ) ? ' cols="5"' : '' ) . implode( ' ', $custom_attributes ) . '>' . esc_textarea( $value ) . '</textarea>';

				break;
			case 'checkbox':
				$field = '<label class="checkbox ' . implode( ' ', $args['label_class'] ) . '" ' . implode( ' ', $custom_attributes ) . '>
						<input type="' . esc_attr( $args['type'] ) . '" class="input-checkbox ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="1" ' . checked( $value, 1, false ) . ' /> ' . $args['label'] . $required . '</label>';

				break;
			case 'text':
			case 'password':
			case 'datetime':
			case 'datetime-local':
			case 'date':
			case 'month':
			case 'time':
			case 'week':
			case 'number':
			case 'email':
			case 'url':
			case 'tel':

                if($key == "billing_postcode" || $key == "shipping_postcode"){
                    $custom_attributes[] = "data-remote='postcode_validation'";
                    $custom_attributes[] = "data-remote-param='postcode'";
                    $custom_attributes[] = "data-remote-objs='".json_encode(array("country"=>"billing_country"))."'";
                }
				$field .= '<input type="' . esc_attr( $args['type'] ) . '" class="form-control input-text-- ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" ' . implode( ' ', $custom_attributes ) . ' />';

				break;
			case 'select':
				$field   = '';
				$options = '';

				if ( ! empty( $args['options'] ) ) {
					foreach ( $args['options'] as $option_key => $option_text ) {
						if ( '' === $option_key ) {
							// If we have a blank option, select2 needs a placeholder.
							if ( empty( $args['placeholder'] ) ) {
								$args['placeholder'] = $option_text ? $option_text : __( 'Choose an option', 'woocommerce' );
							}
							$custom_attributes[] = 'data-allow_clear="true"';
						}
						$options .= '<option value="' . esc_attr( $option_key ) . '" ' . selected( $value, $option_key, false ) . '>' . esc_attr( $option_text ) . '</option>';
					}

					$field .= '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="select ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . ' data-placeholder="' . esc_attr( $args['placeholder'] ) . '">
							' . $options . '
						</select>';
				}

				break;
			case 'radio':
				$label_id .= '_' . current( array_keys( $args['options'] ) );

				if ( ! empty( $args['options'] ) ) {
					foreach ( $args['options'] as $option_key => $option_text ) {
						$field .= '<input type="radio" class="input-radio ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="' . esc_attr( $option_key ) . '" name="' . esc_attr( $key ) . '" ' . implode( ' ', $custom_attributes ) . ' id="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '"' . checked( $value, $option_key, false ) . ' />';
						$field .= '<label for="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '" class="radio ' . implode( ' ', $args['label_class'] ) . '">' . $option_text . '</label>';
					}
				}

				break;
		}

		if ( ! empty( $field ) ) {
			$field_html = '';

			if ( $args['label'] && 'checkbox' !== $args['type'] ) {
				$field_html .= '<label for="' . esc_attr( $label_id ) . '" class="form-label form-label-md mb-2' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . $args['label'] . $required . '</label>';
			}

			$field_html .= '<span class="woocommerce-input-wrapper">' . $field;
			//$field_html .= $field;

			if ( $args['description'] ) {
				$field_html .= '<span class="description" id="' . esc_attr( $args['id'] ) . '-description" aria-hidden="true">' . wp_kses_post( $args['description'] ) . '</span>';
			}

			$field_html .= '</span>';

			$container_class = esc_attr( implode( ' ', $args['class'] ) );
			$container_id    = esc_attr( $args['id'] ) . '_field';
			$field           = sprintf( $field_container, $container_class, $container_id, $field_html );
		}

		/**
		 * Filter by type.
		 */
		$field = apply_filters( 'woocommerce_form_field_' . $args['type'], $field, $key, $args, $value );

		/**
		 * General filter on form fields.
		 *
		 * @since 3.4.0
		 */
		$field = apply_filters( 'woocommerce_form_field', $field, $key, $args, $value );

		if ( $args['return'] ) {
			return $field;
		} else {
			echo $field; // WPCS: XSS ok.
		}
}








add_filter( 'woocommerce_default_address_fields', 'customising_checkout_fields', 1000, 1 );
function customising_checkout_fields( $address_fields ) {
    return $address_fields;
}

add_filter('woocommerce_checkout_fields', 'custom_checkout_billing_fields', 1000, 1);
function custom_checkout_billing_fields( $fields ) {
    return $fields;
}

add_filter('woocommerce_billing_fields', 'custom_billing_fields', 1000, 1);
function custom_billing_fields( $fields ) {
    return $fields;
}







//remove second adress fields from forms
add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );
add_filter( 'woocommerce_billing_fields' , 'custom_override_billing_fields' );
add_filter( 'woocommerce_shipping_fields' , 'custom_override_shipping_fields' );
function custom_override_checkout_fields( $fields ) {
  unset($fields['billing']['billing_address_2']);
  unset($fields['shipping']['shipping_address_2']);
  return $fields;
}
function custom_override_billing_fields( $fields ) {
  unset($fields['billing_address_2']);
  unset($fields['billing_email']);
  return $fields;
}
function custom_override_shipping_fields( $fields ) {
  unset($fields['shipping_address_2']);
  return $fields;
}







//dashboard page
//add_action('woocommerce_account_dashboard', 'salt_custom_dashboard');
function salt_custom_dashboard(){

	$user = wp_get_current_user();

	//cart control
	/*$cart_count =  woo_get_cart_count();
	if($cart_count>0){
		echo "<div class='alert alert-warning'>".wp_kses_post( sprintf( _n( 'Sepetinizde bekleyen %1$s adet ürün var!', 'Sepetinizde bekleyen %1$s adet ürün var!', "zitango" ), $cart_count ) )."</div>";
	}*/

	$vertical = false;

	//menu content
	$endpoint_banned = array("dashboard", "customer-logout", "agents");

	if($vertical){
        echo "<div class='account-dashboard-list card-merged'>";	
	}else{
	    echo "<div class='row row-margin'>";	
	}
	
	foreach ( wc_get_account_menu_items() as $endpoint => $label ){
		if(!in_array($endpoint, $endpoint_banned)){
			$url = wc_get_account_endpoint_url( $endpoint );

			if(!$vertical){
               echo "<div class='col-12 col-sm-6'>";
			}

			echo "<div class='card-account-dashboard-item card card-module card-module-solid h-100'>";
			     echo "<div class='card-header header-flex'><h3 class='title card-title'><a href='".$url."' class='btn-loading-page'>".$label ."</a></h3>";
			           if($vertical){
                          echo "<div class='action'><a href='".$url."' class='btn-loading-page btn btn-outline-danger btn-extend'>Edit</a></div>";
			           } 
			     echo "</div>";
			     echo "<div class='card-body'>";
				switch($endpoint){

					/*  completed badge-success
						processing badge-info
						on-hold badge-warning
						pending badge-default
						cancelled badge-danger
						refunded badge-danger
						failed badge-danger
					*/

					case "orders":
					    $order_statuses_default = wc_get_order_statuses();
					    $order_statuses = array();
					    $class = "";
					    foreach(array_keys($order_statuses_default) as $key=>$order_status){
					    	if($order_status == "wc-completed"){
					    	   $class = "success";
					    	}else if($order_status == "wc-processing"){
	                           $class = "info";
					    	}else if($order_status == "wc-on-hold"){
	                           $class = "warning";
					    	}else if($order_status == "wc-pending"){
	                           $class = "primary";
					    	}else if($order_status == "wc-cancelled"){
	                           $class = "danger";
					    	}else if($order_status == "wc-refunded"){
	                           $class = "danger";
					    	}else if($order_status == "wc-failed"){
	                           $class = "danger";
					    	}
					    	$order_statuses[] = array(
					    		"name"  => $order_status,
					    		"count" => 0,
					    		"class" => $class
					    	);
					    }
					    $customer_orders = get_posts( array(
					        'numberposts' => 5,
					        'meta_key'    => '_customer_user',
					        'meta_value'  => $user->ID,
					        'post_type'   => wc_get_order_types(),
					        'post_status' => array_keys($order_statuses_default ),
					    ) );
					    if(count($customer_orders)>0){
						    foreach($customer_orders as $order){
						    	foreach($order_statuses as $key=>$order_status){
						    		if($order->post_status == $order_status["name"]){
						    		   $order_statuses[$key]["count"] = $order_statuses[$key]["count"]+1;
						    		}
						    	}
						    }
						    echo "<ul class='list-statuses-dashboard list-statuses-active list-group'>";
						    foreach($order_statuses as $order_status){
	                          if($order_status["count"]>0){
	                          	echo "<li class='list-group-item d-flex justify-content-between align-items-center ".($order_status["count"]>0?"active":"")."'>";
	                          	    echo "<a href='".$url."' class='btn-loading-page'></a>";
	                          	    echo $order_statuses_default[$order_status["name"]];
	                          	    echo "<span class='badge badge-".$order_status["class"]." badge-pill'>".$order_status["count"]."</span>";
	                          	echo "</li>";
	                          }
						    }
						    echo "</ul>";
						    //echo "<a href='".$url."' class='btn btn-base btn-base-outline btn-sm btn-extend'>Listeyi Gör</a>";
					    }else{
	                        echo "<div class='content-centered'><div class='content-block'>";
	                                echo "<i class='far fa-calendar'></i>You don't buy any tour yet.";
						    echo "</div></div>";  
					    }
					break;

					case "my-favorites":
					    $favorites_count = 0;
					    $favorites = json_decode(get_user_meta($user->ID, 'wpcf_favorites',true));
					    if($favorites){
					       $favorites_count = count($favorites);
					    }
					    $plural = "";
					    if($favorites_count>1){
					    	$plural = "s";
					    }
					    echo "<a href='".$url."' class='btn-loading-page'><span>View your favorites</span></a>";
					    echo "<div class='content-centered'><div class='content-block'>";
					         if($favorites_count>0){
                                echo "<i class='count'>".$favorites_count."</i>tour".$plural." in your favorite list!";
					         }else{
                                echo "<i class='far fa-heart'></i>Favorite list is empty!";
					         }
					    echo "</div></div>";    
					break;

					case "my-reviews":
					    $comments = getCommentRating(array(
						    "type" => "customer",
						    "id"   => get_current_user_id(),
						    "number" => -1
						));
					    $plural = "";
					    if($comments["count"] > 1){
					    	$plural = "s";
					    }
					    echo "<a href='".$url."' class='btn-loading-page'><span>View your reviews</span></a>";
					    echo "<div class='content-centered'><div class='content-block'>";
					         if($comments["count"] > 0){
                                echo "<i class='count'>".$comments["count"]."</i>review".$plural."!";
					         }else{
                                echo "<i class='fa fa-comment-slash'></i>No reviews!";
					         }
					    echo "</div></div>";    
					break;

					case "messages":
					    $messages_count = yobro_unseen_messages_count();
					    $plural = "";
					    if($messages_count>1){
					    	$plural = "s";
					    }
					    echo "<a href='".$url."' class='btn-loading-page'><span>Go to inbox</span></a>";
					    echo "<div class='content-centered'><div class='content-block'>";
					         if($messages_count>0){
                                echo "<i class='count'>".$messages_count."</i>new message".$plural."!";
					         }else{
                                echo "<i class='far fa-envelope'></i>No new message!";
					         }
					    echo "</div></div>";    
					break;

					case "my-trips":
					    $tour_statuses = array("on-hold", "processing", "completed", "cancelled", "failed");
					    $tour_statuses_arr = array();
					    foreach($tour_statuses as $status){
					    	$arr = tour_plan_status_view($status);
					    	$arr["count"] = 0;
					    	$arr["slug"] = $status;
					    	$tour_statuses_arr[$status] = $arr;
					    }

					    $args = array(
						    'post_type' => 'tour-plan',
						    //'author' => $user->ID,
					        //'fields' => 'ids',
                            //'no_found_rows' => true,
						    'meta_query' => array(
						        array(
						            'key'     => 'tour_plan_status',
						            'value'   => $tour_statuses,
						            'compare' => 'IN',
						        ),
						    ),
						);
						if ( !in_array( 'administrator', (array) $user->roles ) ) {
                           $args["author"] = $user->ID;
						}
						$query = new WP_Query( $args );
						$tour_plan_count = $query->found_posts;
						$tour_plans      = $query->posts;

						$active_plan_count = 0;


						if($tour_plan_count == 0){

						    echo "<div class='content-centered'><div class='content-block'>";
	                            echo "<i class='far fa-calendar'></i>No active tours!";
						    echo "</div></div>";  

						}else{

							foreach($tour_statuses_arr as $key=>$tour_status){
								foreach($tour_plans as $tour_plan){
							    	if($tour_plan->tour_plan_status == $key){
							    	    $tour_statuses_arr[$key]["count"] = $tour_statuses_arr[$key]["count"]+1;
							    		if($key == "on-hold" || $key == "processing" || $key == "completed"){
	                                       $active_plan_count++;
							    		}
							    	}
							    }
						    }

						    /*if($active_plan_count > 0){
						    	$plural = $active_plan_count>1?"s":"";
                                echo "<div class='content-centered'><div class='content-block'>";
		                            echo "<i class='count'>".$active_plan_count."</i>active tour plan".$plural."!.<div class='content-block-container'></div>";
							    echo "</div></div>"; 
						    }*/

						    echo "<ul class='list-statuses-dashboard list-statuses-active list-group'>";
						    foreach($tour_statuses_arr as $key=>$tour_status){
						    	if($tour_status["count"]>0){
		                          	echo "<li class='list-group-item d-flex justify-content-between align-items-center ".($tour_status["count"]>0?"active":"")."'>";
		                          	    if($tour_status["count"]>0){
		                          	    	echo "<a href='".wc_get_account_endpoint_url( $endpoint )."?tour-status=".$key."' class='btn-loading-page'></a>";	                          	    	
		                          	    }
				                        echo "<span>";
					                        echo  $tour_status["title"];
					                        echo "<small>".$tour_status["description"]."</small>";
				                        echo "</span>";
			                          	echo "<span class='badge badge-".$tour_status["class_status"]." badge-pill'>".$tour_status["count"]."</span>";
		                          	echo "</li>";
	                            }
						    }
						    echo "</ul>";

						}
					break;

					case "requests":
					    $tour_statuses = array("on-hold", "processing","completed","cancelled","failed");
					    $tour_statuses_arr = array();
					    foreach($tour_statuses as $status){
					    	$arr = tour_plan_status_view($status);
					    	$arr["count"] = 0;
					    	$arr["slug"] = $status;
					    	$tour_statuses_arr[$status] = $arr;
					    }

					    $field_value = sprintf( '^%1$s$|s:%2$u:"%1$s";', $user->ID, strlen( $user->ID ) );
				    	$args = array(
				                 "post_type" => "tour-plan",
				                 "meta_query" => array(
				                 	array(
					                    "key" => "tour_plan_agents",
					                    'value'   => $field_value,
					                    'compare' => 'REGEXP'
				                    ),
				                    array(
					                    "key" => "tour_plan_status",
					                    'value'   => $tour_statuses,
					                    'compare' => 'IN'
				                    ) 
				                 )
				    	);

						$query = new WP_Query( $args );
						$tour_plan_count = $query->found_posts;
						$tour_plans      = $query->posts;

						$active_plan_count = 0;

						if($tour_plan_count == 0){

						    echo "<div class='content-centered'><div class='content-block'>";
	                            echo "<i class='far fa-calendar'></i>No requests!";
						    echo "</div></div>";  

						}else{

							foreach($tour_statuses_arr as $key=>$tour_status){
								foreach($tour_plans as $tour_plan){
							    	if($tour_plan->tour_plan_status == $key){
							    	    $tour_statuses_arr[$key]["count"] = $tour_statuses_arr[$key]["count"]+1;
							    		if($key == "on-hold" || $key == "processing" || $key == "completed"){
	                                       $active_plan_count++;
							    		}
							    	}
							    }
						    }

						    echo "<ul class='list-statuses-dashboard list-statuses-active list-group'>";
						    foreach($tour_statuses_arr as $key=>$tour_status){
						    	if($tour_status["count"]>0){
		                          	echo "<li class='list-group-item d-flex justify-content-between align-items-center ".($tour_status["count"]>0?"active":"")."'>";
		                          	    if($tour_status["count"]>0){
		                          	    	echo "<a href='".wc_get_account_endpoint_url( $endpoint )."?tour-status=".$key."' class='btn-loading-page'></a>";	                          	    	
		                          	    }
				                        echo "<span>";
					                        echo  $tour_status["title"];
					                        echo "<small>".$tour_status["description"]."</small>";
				                        echo "</span>";
			                          	echo "<span class='badge badge-".$tour_status["class_status"]." badge-pill'>".$tour_status["count"]."</span>";
		                          	echo "</li>";
	                            }
						    }
						    echo "</ul>";

						}
					break;

					case "edit-account" :
					    if ( in_array( 'agent', (array) $user->roles ) ) {
						    $arr = array(
						    	array(
                                   'label' => "Agent",
                                   'value' => $user->display_name,
                                   'column' => "col-sm-6"
                               ),
                               array(
                                   'label' => "Authorized person",
                                   'value' => $user->first_name." ".$user->last_name,
                                   'column' => "col-sm-6"
                               ),
                               array(
                                   'label' => "E-mail",
                                   'value' => $user->user_email,
                                   'column' => "col-sm-6"
                               )/*,
                               array(
                                   'label' => "Address",
                                   'value' => $user->billing_address_1." ".$user->billing_address_2."<br>".$user->billing_city." ".$user->billing_postcode." ".get_country_by_code($user->billing_country)." ".get_state_by_code($user->billing_country, $user->billing_state),
                                   'column' => "col-12"
                               )*/
					    	);
					    }
					    if ( in_array( 'customer', (array) $user->roles ) || in_array( 'administrator', (array) $user->roles ) ) {
					    	$arr = array(
                               array(
                                   'label' => "Name & Last name",
                                   'value' => $user->first_name." ".$user->last_name,
                                   'column' => "col-sm-6"
                               ),
                               array(
                                   'label' => "E-mail",
                                   'value' => $user->user_email,
                                   'column' => "col-sm-6"
                               )/*,
                               array(
                                   'label' => "Address",
                                   'value' => $user->billing_address_1." ".$user->billing_address_2."<br>".$user->billing_city." ".$user->billing_postcode." ".get_country_by_code($user->billing_country)." ".get_state_by_code($user->billing_country, $user->billing_state),
                                   'column' => "col-12"
                               )*/
					    	);
					    }
					    	echo "<a href='".$url."' class='btn-loading-page'><span>Edit your account</span></a>";
					    	echo "<div class='row'>";
					    	foreach($arr as $item){
					    		echo "<div class='".$item["column"]."'><div class='form-group form-group-md'>";
					    		    echo "<div class='form-control-readonly'>";
						    		    echo "<label class='form-label form-label-md mb-0 text-muted'>".$item["label"]."</label>";
						    		    echo "<div class='form-control-readonly'>".$item["value"]."</div>";
					    		    echo "</div>";
					    		echo "</div></div>";
					    	}
					    	echo "</div>";
					break;

					case "edit-address" :
	                    if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) {
							$get_addresses = apply_filters(
								'woocommerce_my_account_get_addresses',
								array(
									'billing'  => __( 'Billing address', 'woocommerce' ),
									'shipping' => __( 'Shipping address', 'woocommerce' ),
								),
								$user->ID
							);
						} else {
							$get_addresses = apply_filters(
								'woocommerce_my_account_get_addresses',
								array(
									'billing' => __( 'Billing address', 'woocommerce' ),
								),
								$user->ID
							);
						}
					    if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) :
							echo '<div class="row">';
						endif;

						foreach ( $get_addresses as $name => $address_title ) :
						    $address = wc_get_account_formatted_address( $name );

						    if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) :
							echo '<div class="col-sm-6">';
								echo '<div class="card">';
									echo '<div class="card-header">';
										echo '<h3 class="title card-title">'.esc_html( $address_title ).'</h3>';
									echo '</div>';
									echo '<div class="card-body">';
									endif;
										echo '<address>';
												echo $address ? wp_kses_post( $address ) : esc_html_e( 'You have not set up this type of address yet.', 'woocommerce' );
										echo '</address>';
										echo '<a href="'.  esc_url( wc_get_endpoint_url( 'edit-address', $name ) ).'" class="btn-loading-page"><span>'.($address ? esc_html__( 'Edit', 'woocommerce' ) : esc_html__( 'Add', 'woocommerce' )).'</span></a>';
								    
								    if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) :
								    echo '</div>';
								echo '</div>';
							echo '</div>';
							endif;

						endforeach;

						if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) :
							echo '</div>';
						endif;
					break;

				}
			    echo "</div>";
			echo "</div>";

			if(!$vertical){
				echo "</div>";
			}

		}
	};
	echo "</div>";
}
