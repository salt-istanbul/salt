<?php

//namespace YoBro\App;
use YoBro\App\Message;
use YoBro\App\Attachment;

/*
header('Content-Type: text/html');
send_nosniff_header();

//Disable caching
header('Cache-Control: no-cache');
header('Pragma: no-cache');
*/

$unique = "ajax";

add_action($unique . "_query", "query", 10, 1);
add_action($unique . "_nopriv_query", "query", 10, 1);

function no_data($data){
    echo json_encode([]);
}

function ajax_security($data){
    $response = [
        "error" => false,
        "message" => "",
        "data" => "",
        "resubmit" => false,
        "redirect" => "",
        "html" => "",
    ];
    if(!isset($data['_wpnonce'])){
        $nonce = isset( $_SERVER['HTTP_X_CSRF_TOKEN'] ) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : '';
        if(empty($nonce)){
           $response["error"] = true;
           $response["message"] = 'Security reason...';
           echo(json_encode($response));
           die;
        }        
    }else{
        $nonce = $data['_wpnonce'];
    }
    $nonce = wp_verify_nonce( $nonce, 'ajax' );
    switch ( $nonce ) {
        case 1:
            //echo 'Nonce is less than 12 hours old';
            break;
        case 2:
            //echo 'Nonce is between 12 and 24 hours old';
            break;
        default:
            $response["error"] = true;
            $response["message"] = 'Nonce is invalid';
            echo(json_encode($response));
            exit;
    }
}

function query($data){

    if (!is_iterable($data)) {
        exit();
    }

    if($data["method"] != "message_upload"){
       ajax_security($data);        
    }

    $lang = strtolower( substr( get_locale(), 0, 2 ) );;
    if (function_exists("qtranxf_getSortedLanguages")) {
        $lang = qtranxf_getLanguage();
    }

    $method = isset($data["method"]) ? $data["method"] : "";
    $terms = isset($data["terms"]) ? $data["terms"] : "";
    //$template = isset($data["template"]) ? $data["template"] : "ajax/archive";
    $id = isset($data["id"]) ? $data["id"] : "";
    $vars = isset($data["vars"]) ? $data["vars"] : "";
    $keyword = trim(isset($data["keyword"]) ? $data["keyword"] : "");
    $template = isset($vars["template"]) ? $vars["template"] : "";
    //$page = isset($data["page"]) ? $data["page"] : 0;
    /*$count = isset($data["count"]) ? $data["count"] : 10;
		 $term = isset($data["term"]) ? $data["term"] : "category";

		 $pagination=isset($data["pagination"])? ($data["pagination"]=== 'true'? true: false) : false;*/

    /*if(isset($_SERVER['HTTP_REFERER'])){
			 $_SERVER['REQUEST_URI'] = str_replace('http://'.$_SERVER['HTTP_HOST'],'',$_SERVER['HTTP_REFERER']);
			 $url_parts=explode('?method=',$_SERVER['REQUEST_URI']);
			 $_SERVER['REQUEST_URI'] = $url_parts[0];
			 if($method=='author-most-readed' || $method=='most-readed'){
				 $_SERVER['REQUEST_URI'].='?method='.$method;
			 }                   
		}*/

    ///add_action( 'wp_ajax_'.$method, 'wpdocs_action_function' );

         //print_r(check_ajax_referer( $method."-security", 'method' ));

    if (isset($data["upload"])) {
        //$vars=$data;
    }

    //print_r($vars);

    if ($vars) {
        foreach ($vars as $key => $var) {
            if (!isset($var)) {
                $vars[$key] = "";
            }
        }
    }

    if (isset($vars["lang"])) {
        $lang = $vars["lang"];
    }

    $error = false;
    $message = "";
    $redirect_url = "";
    //$data = "";
    $html = "";

    $response = [
        "error" => false,
        "message" => "",
        "data" => "",
        "resubmit" => false,
        "redirect" => "",
        "html" => "",
    ];

    $output = [];

    switch ($method) {
        case "site_config":
            echo json_encode(get_site_config());
            die();
            break;

        case "login":
            $salt = new Salt();
            echo json_encode($salt->login($vars));
            die();
            break;

        case "lost_password":
            $salt = new Salt();
            echo $salt->lost_password($vars);
            die();
            break;

        case "user_exist":
            $salt = new Salt();
            $status = $salt->user_exist($vars);
            $error = false;
            $message = "";
            if ($status) {
                $error = true;
                $message = $status;
            }
            $output = [
                "error" => $error,
                "message" => $message,
            ];
            echo json_encode($output);
            die();
            break;

        case "nickname_exist":
            $salt = new Salt();
            $status = $salt->nickname_exist($vars);
            $error = false;
            $message = "";
            if ($status) {
                $error = true;
                $message = $status;
            }
            $output = [
                "error" => $error,
                "message" => $message,
            ];
            echo json_encode($output);
            die();
            break;

        case "color_by_name":
            $output = [];
            if (!empty($keyword)) {
                $args = [
                    "post_type" => ["product", "product_variation"],
                    "order" => "ASC",
                    "orderby" => "title",
                    "posts_per_page" => 10,
                    "numberposts" => 10,
                    //'nopaging' => true,
                    "s" => $keyword,
                ];

                $posts = get_posts($args);
                if (!$posts) {
                    $posts = wc_get_products_by_variation_sku($keyword);
                }
                foreach ($posts as $post) {
                    $product = wc_get_product($post->ID);
                    $type = $product->get_type();
                    if ($type == "variable") {
                        $default_attributes = iconic_get_default_attributes(
                            $product
                        );
                        $variation_id = iconic_find_matching_product_variation(
                            $product,
                            $default_attributes
                        );
                        $image = get_the_post_thumbnail_url(
                            $variation_id,
                            "shop_thumbnail"
                        );
                        if (!$image) {
                            $image = get_the_post_thumbnail_url(
                                $post->post_parent->ID,
                                "shop_thumbnail"
                            );
                        }
                    } else {
                        $image = get_the_post_thumbnail_url(
                            $product->get_id(),
                            "shop_thumbnail"
                        );
                    }
                    $post_item = [
                        "id" => $post->ID,
                        "name" => $product->get_name(),
                        "url" => get_permalink($product->get_id()),
                        "image" => $image,
                        "price" => $product->get_price_html(),
                        //"category" => get_the_terms($post->ID, "product_cat")[0]->name
                    ];
                    $output[] = $post_item;
                }
            }
            echo json_encode($output);
            die();
            break;

        case "color_by_code":
            $output = [];
            $args = [
                "post_type" => "product",
                "order" => "ASC",
                "orderby" => "meta_value",
                "posts_per_page" => 10,
                "numberposts" => 10,
                "nopaging" => true,
                "meta_query" => [
                    [
                        "key" => "code",
                        "value" => "^" . $keyword,
                        "compare" => "REGEXP",
                    ],
                ],
            ];
            foreach (get_posts($args) as $post) {
                $post_item = [
                    "id" => $post->ID,
                    "name" => $post->code,
                    "url" => get_permalink($post),
                    "image" => get_the_post_thumbnail_url(
                        $post,
                        "woocommerce_gallery_thumbnail"
                    ),
                    "price" => $post->get_price_html(),
                ];
                $output[] = $post_item;
            }
            echo json_encode($output);
            die();
            break;

        case "favorites_add":
            $id = $vars["id"];
            $favorites = new Favorites();
            $favorites->add($id);
            $GLOBALS["favorites"] = $favorites;
            $favorite_count = get_post_meta($id, "wpcf_favorites_count", true);
            $button_text = trans("Remove");
            $feedback_text = "";
            if (!empty($favorite_count)) {
                $feedback_text =
                    "<span>" .
                    sprintf(
                        trans("%s kperson's favorite tour."),
                        $favorite_count
                    ) .
                    "</span>";
            }
            $html = $button_text . $feedback_text;
            $data = [
                "error" => false,
                "message" =>
                    "<b class='d-block'>" .
                    get_the_title($id) .
                    "</b> added to your favorites.",
                "data" => $favorites->favorites,
                "html" => $html,
            ];
            echo json_encode($data);
            die();
            break;

        case "favorites_remove":
            $id = $vars["id"];
            $favorites = new Favorites();
            $favorites->remove($id);
            $GLOBALS["favorites"] = $favorites;
            $favorite_count = get_post_meta($id, "wpcf_favorites_count", true);
            $button_text = trans("Add");
            $feedback_text = "";
            if (!empty($favorite_count)) {
                $feedback_text =
                    "<span>" .
                    sprintf(
                        trans("%s person's favorite tour."),
                        $favorite_count
                    ) .
                    "</span>";
            }
            $html = $button_text . $feedback_text;
            $data = [
                "error" => false,
                "message" =>
                    "<b class='d-block'>" .
                    get_the_title($id) .
                    "</b> removed from your favorites.",
                "data" => $favorites->favorites,
                "html" => $html,
            ];
            echo json_encode($data);
            //echo html_entity_decode(json_encode($response));
            die();
            break;

        case "favorites_get":
            global $wp_query;
            $favorites = new Favorites();
            $favorites = $favorites->favorites;
            if (!$template) {
                $template = "woo/dropdown/archive.twig";
            }
            $templates = [$template . ".twig"];
            $context = Timber::get_context();
            $context["type"] = "favorites";

            $posts = [];
            if ($favorites) {
                $args = [
                    "post_type" => "product",
                    "posts_per_page" => -1,
                    "post__in" => $favorites,
                    /*'meta_query' => array(
						    	'relation' => "or",
						        array(
						            'key' => '_stock_status',
						            'value' => 'instock'
						        ),
						        array(
						            'key' => '_stock_status',
						            'value' => 'outofstock'
						        ),
						    )*/
                ];
                $posts = Timber::get_posts($args);
            }
            if (
                count($posts) != count($favorites) &&
                isset($GLOBALS["user"]->ID)
            ) {
                $ids = wp_list_pluck($posts, "ID");
                update_user_meta(
                    $GLOBALS["user"]->ID,
                    "wpcf_favorites",
                    unicode_decode(json_encode($ids, JSON_NUMERIC_CHECK))
                );
            }
            $post_count = count($posts);
            $context["posts"] = $posts;
            break;

        case "search_terms_add":
            $search_history_obj = new SearchHistory();
            $search_history_obj->add($keyword);
            die();
            break;

        case "search_terms_remove":
            echo userSearchTermsRemove(wp_get_current_user());
            die();
            break;

        case "get_messages_count":
            $data = [
                "error" => false,
                "message" => "",
                "data" => [
                    "count" => yobro_unseen_messages_count(),
                ],
            ];
            echo json_encode($data);
            die();
            break;
        


        case "send_profile_message" :
            $sender_id = get_current_user_id();
            $reciever_id = $vars["id"];
            $project = isset($vars["project"])?$vars["project"]:0;
            $message = $vars["message"];
            if($message){
                $conversation = yobro_new_conversation($sender_id, $reciever_id, $message, $project);
                $url = $GLOBALS['base_urls']["account"];
                if($project){
                    $url = get_permalink($project);
                }
                $response["redirect"] = $url."messages/?conversationId=".$conversation->conv_id."&chat=".$reciever_id;
            }else{
                $response["error"] = true;
                $response["message"] = "Please write a message";
            }
            echo json_encode($response);
            die();
        break;

        /*case "get_notifications":
            $data = [
                "error" => false,
                "message" => "",
                "data" => [
                    "count" => yobro_unseen_messages_count(),
                    "notifications" => yobro_notification_messages(
                        "notification"
                    ),
                ],
            ];
            echo json_encode($data); //json_encode(yobro_notification_messages());
            die();
            break;*/

        case "get_messages":
            $messages = yobro_unseen_messages("messages");
            $templates = [$template . ".twig"];
            $context = Timber::get_context();
            $context["type"] = "messages";
            $context["posts"] = $messages;
            $data = [
                "error" => false,
                "message" => "",
                "data" => [
                    "count" => yobro_unseen_messages_count(),
                ],
            ];
            if (!$template) {
                $template = "woo/dropdown/archive";
                $templates = [$template . ".twig"];
            }
            break;

        case "message_upload":
            $new_message = json_decode(
                stripslashes_deep(html_entity_decode($_POST["details"])),
                true
            );
            $allFiles = $_FILES;
            if (isset($allFiles) && !empty($allFiles)) {
                $uploaded_files = [];
                foreach ($allFiles as $key => $singleFile) {
                    $s3 = new Salt();
                    $uploaded_files = [];
                    foreach ($allFiles as $key => $singleFile) {
                        /*$uploaded_files[$key]['url'] = $s3->send_message_upload($singleFile, 'false');
									if(strpos($singleFile['type'], 'image') !== false){
										$uploaded_files[$key]['thumbnail_url'] = $s3->send_message_upload($singleFile, 'true');
									}*/
                        $uploaded_files[$key] = $s3->send_message_upload(
                            $singleFile
                        );
                        $uploaded_files[$key]["type"] = $singleFile["type"];
                        $uploaded_files[$key]["size"] = $singleFile["size"];
                    }
                    try {
                        $new_attachment = Attachment::create([
                            "type_t" => null,
                            "conv_id" => $new_message["conv_id"],
                            "url" => json_encode($uploaded_files),
                            "size" => null,
                        ]);
                    } catch (Exception $e) {
                        $error = [
                            "status_code" => 400,
                            "message" => $e->messages(),
                        ];
                        echo json_encode($error);
                    }
                    apply_filters("yobro_new_uploaded_assets", $new_attachment);
                    if (isset($new_attachment)) {
                        $new_message["attachment_id"] = $new_attachment["id"];
                        $stored_message = do_store_message($new_message);
                        $stored_message["attachments"] = $new_attachment;
                        apply_filters(
                            "yobro_message_with_attachments",
                            $stored_message
                        );
                        echo json_encode($stored_message);
                    }
                }
            }
            die();
            break;

        //{"conv_id":3,"reciever_id":1,"sender_id":34,"message":""}

        case "map_modal":
            $contacts = get_field("contacts", "option");
            $class = "";
            $attr = "";
            if (isset($vars["index"])) {
                $contacts = $contacts[$vars["index"]]["contact"];
            }
            if ($contacts["map_embed"]) {
                $class .= " map-google-embed";
                $attr .= ' data-embed-url="' . $contacts["map_url"] . '"';
            }
            $html =
                '<div class="map-google ' . $class . '" ' . $attr . "></div>";
            $output = [
                "error" => false,
                "message" => "",
                "data" => [
                    "title" => get_bloginfo("name"),
                    "content" => $html,
                ],
                "html" => "",
            ];
            echo json_encode($output);
            die();
            break;

        case "page_modal":
            if (isset($vars["id"])) {
                if (!is_numeric($vars["id"])) {
                    switch ($vars["id"]) {
                        case "privacy-policy":
                            $post = get_post(
                                get_option("wp_page_for_privacy_policy")
                            );
                            break;
                        case "terms-conditions":
                            $post = get_post(wc_terms_and_conditions_page_id());
                            break;
                        default:
                            global $wpdb;
                            $post_id = $wpdb->get_var(
                                $wpdb->prepare(
                                    "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type= %s AND post_status = 'publish'",
                                    $vars["id"],
                                    "page"
                                )
                            );
                            $post = get_post($post_id);
                            break;
                    }
                } else {
                    $post = get_post($vars["id"]);
                }
            }
            $error = true;
            $message = "Content not found";
            $post_data = [];
            if ($post) {
                $error = false;
                $message = "";
                $post_data = [
                    "title" => qtranxf_use(
                        $lang,
                        $post->post_title,
                        false,
                        false
                    ),
                    "content" => qtranxf_use(
                        $lang,
                        wpautop($post->post_content, true),
                        false,
                        false
                    ),
                ];
            }
            $output = [
                "error" => $error,
                "message" => $message,
                "data" => $post_data,
                "html" => "",
            ];
            echo json_encode($output);
            die();
            break;

        case "form_modal":
            $output = [
                "error" => false,
                "message" => "",
                "data" => [
                    "title" => $vars["title"],
                    "content" => do_shortcode(
                        '[contact-form-7 id="' . $vars["id"] . '"]'
                    ),
                ],
                "html" => "",
            ];
            echo json_encode($output);
            die();
            break;
        
        case "template_modal":
            $error = true;
            $message = "";
            $html = "";
            if (isset($vars["template"])) {
                $error = false;
                $template = $vars["template"];
                $templates = [$template . ".twig"];
                $data = $vars; //["data"];
                $context = Timber::get_context();
                $context["data"] = $data;
                $html = Timber::compile($templates, $context);
            }
            $output = [
                "error" => $error,
                "message" => $message,
                "html" => "",
            ];
            if(isset($vars["title"])){
                $output["data"] = array(
                    "title" => $vars["title"],
                    "body" => $html
                );
            }else{
                $output["data"] = array(
                    "content" => $html
                );
            }
            echo json_encode($output);
            die();
            break;

        case "twig_render":
            $template = $vars["template"];
            $templates = [$template . ".twig"];
            $data = $vars["data"];
            $context = Timber::get_context();
            $context["data"] = $data;
            echo Timber::compile($templates, $context);
            die();
            break;

        case "get_country_options":
            echo json_encode(
                get_countries(
                    $vars["continent"],
                    $vars["selected"],
                    isset($vars["all"])?$vars["all"]:false
                )
            );
            die();
            break;

        case "get_city_options":
            echo json_encode(get_cities($vars["country"], $vars["selected"]));
            die();
            break;

        case "get_states":
            $woo_countries = new WC_Countries();
            $states = $woo_countries->get_states($vars["id"]);
            echo json_encode($states);
            die();
            break;

        case "get_phone_code":
            $woo_countries = new WC_Countries();
            $states = $woo_countries->get_country_calling_code($vars["id"]);
            echo json_encode($states);
            die();
            break;

        case "get_districts":
            echo json_encode(get_districts($vars["city"]));
            die();
            break;

        case "get_available_districts":
            echo json_encode(
                get_available_districts($vars["post_type"], $vars["city"])
            );
            die();
            break;

        case "get_posts_by_city":
            echo json_encode(
                get_posts_by_city($vars["post_type"], $vars["city"])
            );
            die();
            break;

        case "get_posts_by_district":
            $data = [];
            $template = $vars["post_type"] . "/archive-ajax";
            $data = get_posts_by_district(
                $vars["post_type"],
                $vars["city"],
                $vars["district"]
            );
            $templates = [$template . ".twig"];
            $context = Timber::get_context();
            $context["vars"] = $vars;
            $context["data"] = $data;
            $data = [
                "error" => false,
                "message" => "",
                "data" => $data,
                "html" => "",
            ];
            break;

        case "get_nearest_locations":
            //$geolocation = new Geolocation_Query($vars);
            $locations = GeoLocation_Query(
                $vars["lat"],
                $vars["lng"],
                $vars["post_type"],
                $vars["distance"],
                $vars["limit"]
            );
            $template = $vars["template"];
            $templates = [$template . ".twig"];
            $context = Timber::get_context();
            $context["data"] = $locations;
            $context["nearest_location"] = true;
            $data = [
                "error" => false,
                "message" => "",
                "data" => "",
                "html" => "",
            ];
            break;





        case "wc_order_list":
            $order_number = $vars["order_number"];
            woocommerce_order_details_table($order_number);
            die();
            break;

        case "pay_now":
            $salt = new Salt();
            $offer_id = $vars["offer_id"];
            $salt->remove_cart_content();
            $salt->update_product_price($offer_id);

            //add tour
            $product_id = get_field("product_id", $offer_id);
            $salt->add_to_cart($product_id);
            //add additional items
            $additional_items = get_field("additional_items", $offer_id);
            if ($additional_items) {
                foreach ($additional_items as $item) {
                    for ($i = 1; $i < $item["item_quantity"] + 1; $i++) {
                        $salt->add_to_cart($item["item_product_id"]);
                    }
                }
            }

            $redirect_url = woo_checkout_url();
            $output = [
                "error" => false,
                "message" => "Pay Now",
                "data" => "",
                "html" => "",
                "redirect" => $redirect_url,
            ];
            echo json_encode($output);
            die();
            break;
        
        case "comment_modal":
        case "comment_product_modal":
            $modal = "";
            $data = [];
            $template = "tour-plan/comment-modal";

            $comment = new Timber\Comment(intval($vars["id"]));

            $title = $comment->comment_title;
            $comments = json_decode($comment->comment_content);
            $author = $comment->comment_author;
            //$rating = '<div class="star-rating-readonly-ui" data-stars="5" data-value="'.$comment->rating.'"></div>';
            $image = wp_get_attachment_image_url(
                $comment->comment_image,
                "medium_large"
            );
            $tour_plan_id = $comment->meta("comment_tour");
            $tour_plan_offer_id = get_field(
                "tour_plan_offer_id",
                $tour_plan_id
            );
            $agent_id = get_post_field("post_author", $tour_plan_offer_id);
            $agent = get_user_by("id", $agent_id);

            $destinations = get_terms(
                "taxonomy=destinations&include=" .
                    join(",", $comment->meta("comment_destination"))
            );
            $destinations = wp_list_pluck($destinations, "name");
            /*$destination_list = '<ul class="list-inline mb-0">';
                    foreach($destination as $item){
                       $destination_list .= '<li class="list-inline-item mb-2"><div class="btn btn-warning btn-unlinked rounded-pill">'.$item.'</div></li>';
                    }   
                    $destination_list .= '</ul>';*/

            $templates = [$template . ".twig"];
            $context = Timber::get_context();
            $context["title"] = $title;
            $context["comments"] = $comments;
            $context["author"] = $author;
            $context["image"] = $image;
            $context["agent"] = $agent;
            $context["destinations"] = $destinations;
            $context["vars"] = $vars;
            $data = [
                "error" => false,
                "message" => "",
                "data" => $data,
                "html" => "",
            ];
            break;

        case "comment_product":
            $salt = new Salt();
            echo $salt->comment_product($vars);
            die();
            break;

        case "comment_product_detail":
            $salt = new Salt();
            echo $salt->comment_product_detail($vars);
            die();
            break;

        case "get_cart":
            global $woocommerce;
            $cart = woo_get_cart_object();
            $templates = [$template . ".twig"];
            $context = Timber::get_context();
            $context["type"] = "cart";
            $context["cart"] = $cart;
            $data = [
                "error" => false,
                "message" => "",
                "data" => [
                    "count" => $woocommerce->cart->get_cart_contents_count(),
                ],
            ];
            if (!$template) {
                $template = "woo/dropdown/archive";
                $templates = [$template . ".twig"];
            }
            break;

        case "wc_cart_quantity_update":
            global $woocommerce;
            $woocommerce->cart->set_quantity($vars["key"], $vars["count"]);
            //$woocommerce->cart->get_cart_contents_count();
            echo json_encode(woo_get_cart_object());
            die();
            break;

        case "wc_cart_item_remove":
            global $woocommerce;
            $woocommerce->cart->remove_cart_item($vars["key"]);
            //echo json_encode(woo_get_cart_object());
            $cart = woo_get_cart_object();
            $templates = [$template . ".twig"];
            $context = Timber::get_context();
            $context["type"] = "cart";
            $context["cart"] = $cart;
            $data = [
                "error" => false,
                "message" => "",
                "data" => [
                    "count" => $woocommerce->cart->get_cart_contents_count(),
                ],
            ];
            if (empty($template)) {
                $template = "woo/dropdown/archive";
                $templates = [$template . ".twig"];
            }
            //echo json_encode($data);
            //die;
            break;

        case "wc_cart_clear":
            global $woocommerce;
            $woocommerce->cart->empty_cart();
            die();
            break;

        case "wc_modal_page_template":
            global $woocommerce;
            $context = Timber::get_context();

            $context["date"] = date("d.m.Y");

            $content = apply_filters(
                "the_content",
                get_post_field("post_content", $id)
            );

            $customer_data = $woocommerce->cart->get_customer();
            $shipping_data = $customer_data->shipping;
            $customer = [
                "name" =>
                    $customer_data->first_name .
                    " " .
                    $customer_data->last_name,
                "shipping_address" =>
                    $shipping_data["address_1"] .
                    " " .
                    $shipping_data["city"] .
                    " " .
                    $shipping_data["state"] .
                    " " .
                    $shipping_data["postcode"] .
                    " " .
                    $shipping_data["country"],
                "phone" => $customer_data->billing["phone"],
                "email" => $customer_data->email,
                "ip" => $_SERVER["REMOTE_ADDR"],
            ];
            $context["customer"] = $customer;

            $cart = [];
            $discount_total = 0;
            $tax_total = 0;
            $items = $woocommerce->cart->get_cart();
            foreach ($items as $item => $values) {
                $_product = wc_get_product($values["data"]->get_id());
                $getProductDetail = wc_get_product($values["product_id"]);
                //$price = get_post_meta($values['product_id'] , '_price', true);
                //echo "Regular Price: ".get_post_meta($values['product_id'] , '_regular_price', true)."<br>";
                //echo "Sale Price: ".get_post_meta($values['product_id'] , '_sale_price', true)."<br>";
                $tax = $values["line_subtotal_tax"];
                $regular_price = $_product->get_regular_price();
                //$sale_price = $_product->get_sale_price();
                //$discount = ($regular_price - $sale_price);// * $values['quantity'];
                //$discount_total += $discount;

                $tax_total += $tax;

                $cart_item = [
                    "image" => $getProductDetail->get_image("thumbnail"),
                    "title" => $_product->get_title(),
                    "price" => woo_get_currency_with_price(
                        get_post_meta($values["variation_id"], "_price", true)
                    ),
                    "quantity" => $values["quantity"],
                    "tax" => woo_get_currency_with_price($tax),
                    "total_price" => woo_get_currency_with_price(
                        $values["line_subtotal"]
                    ),
                ];
                $cart[] = $cart_item;
            }
            $context["cart"] = $cart;
            $context["total_tax"] = woo_get_currency_with_price($tax_total);
            //$context["shipping_price"] = $woocommerce->cart->get_cart_shipping_total();
            //$context["discount_price"] = woo_get_currency_with_price($discount_total);
            $context["total"] = woo_get_currency_with_price(
                $woocommerce->cart->total
            );

            Timber::render_string($content, $context);
            die();
            break;

        case "get_products":
            if (isset($vars["kategori"])) {
                $page_type = "product_cat";
            }
            if (isset($vars["keyword"])) {
                $page_type = "search";
                $GLOBALS["keyword"] = $vars["keyword"];
                add_filter("posts_where", "sku_where");
            }

            $templates = [$template . ".twig"];
            $context = Timber::get_context();

            //$query = new WP_Query();

            $query = [];
            $query_response = category_queries_ajax($query, $vars);
            $query = $query_response["query"];
            $GLOBALS["query_vars"] = woo_sidebar_filter_vars($vars); //$query_response["query_vars"];
            $data["query_vars"] = $GLOBALS["query_vars"];

            $closure = function ($sql) {
                //$role = array_keys($GLOBALS["user"]->roles)[0];

                //print_r($GLOBALS['query_vars']);
                // remove single quotes around 'mt1.meta_value'
                //print_r($sql);
                // $sql = str_replace("CAST(mt2.meta_value AS SIGNED)","CAST(mt2.meta_value-(mt2.meta_value/2) AS SIGNED)", $sql);// 50% indirim
                return str_replace("'mt2.meta_value'", "mt2.meta_value", $sql);
            };
            add_filter("posts_request", $closure);

            query_posts($query);
            //$query = new WP_Query($args);
            //$posts = new WP_Query( $query );

            remove_filter("posts_request", spl_object_hash($closure));

            $posts = Timber::get_posts();

            $context["posts"] = $posts; //_new;

            //$queried_object = get_queried_object();
            if (ENABLE_FAVORITES) {
                $context["favorites"] = $GLOBALS["favorites"];
            }

            $context["pagination_type"] =
                $GLOBALS["site_config"]["pagination_type"];

            if (isset($GLOBALS["query_vars"])) {
                $query_vars = $GLOBALS["query_vars"];
            }

            global $wp_query;
            $post_count = $wp_query->found_posts;
            $page_count = $wp_query->max_num_pages;
            $page = $wp_query->query_vars["paged"];
            $context["post_count"] = $post_count;
            $context["page_count"] = $page_count;
            $context["page"] = $page;

            //if(array_key_exists( "pagination", $context['posts'] )){
            $context["pagination"] = Timber::get_pagination(); //$context['posts']->pagination;//Timber::get_pagination();
            //}
            //$context['pagination'] = Timber::get_pagination();
            //print_r($context['posts']);

            //$context['page_count'] = 1;//Timber::get_pagination(array(),$context['posts']);//floor(abs(Timber::get_pagination()["total"])/$GLOBALS['ajax_product_count']);//Timber::get_pagination();
            //echo $page;//json_encode($query_args);
            //echo json_encode(get_posts($query_args));
            //die;

            if ($vars["product_filters"] && ENABLE_FILTERS) {
                $data["sidebar"] = Timber::compile(
                    "woo/sidebar-product-filter.twig",
                    woo_sidebar_filters(
                        $context,
                        $page_type,
                        500,
                        $query,
                        $vars
                    )
                );
            }

            wp_reset_postdata();
            wp_reset_query();
            break;

        case "woo_get_product_variation_thumbnails":
            global $woocommerce;
            $images = woo_get_product_variation_thumbnails(
                $vars["product_id"],
                $vars["attr"],
                $vars["attr_value"],
                $vars["size"]
            );
            $context["post"] = wc_get_product($vars["product_id"]);
            $context = Timber::get_context();
            $context["images"] = $images;
            $templates = [$template . ".twig"];
            //die;
            break;

        case "wc_api_update_product":
            echo json_encode(wc_api_update_product($vars["data"]));
            die();
            break;

        case "wc_api_create_product":
            echo json_encode(wc_api_create_product($vars["data"]));
            die();
            break;

        case "wc_api_create_product_variation":
            echo json_encode(
                wc_api_create_product_variation($vars["data"], $vars["id"])
            );
            die();
            break;

        case "wc_api_filter_products":
            $args = ["tag" => "103,63"];
            echo json_encode($GLOBALS["woo_api"]->get("products", $args)); //$vars["filters"]));
            die();
            break;

        case "custom_track_product_view":
            custom_track_product_view_js($vars["post_id"]);
            die();
            break;

        case "salt_recently_viewed_products":
            $data = [];
            $template = $vars["ajax"]["template"];
            $data = Timber::get_posts(salt_recently_viewed_products());
            $templates = [$template];
            $context = Timber::get_context();
            //print_r($vars);
            $context["vars"] = $vars;
            $context["vars"]["posts"] = $data;
            $data = [
                "error" => false,
                "message" => "",
                "data" => $data,
                "html" => "",
            ];
            break;

        

        case "autocomplete_terms":
            $response = [];
            $response["results"] = [];
            if (isset($vars["type"])) {
                $taxonomy = taxonomy_exists($vars["type"]);
                $post_type = post_type_exists($vars["type"]);
                if (!isset($vars["response-type"])) {
                    $vars["response-type"] = "select2";
                }
                if (!isset($vars["count"])) {
                    $vars["count"] = 10;
                }
                if (!isset($vars["page"])) {
                    $vars["page"] = 1;
                }
                $offset = ($vars["page"] - 1) * $vars["count"];
                if ($taxonomy) {
                    $args = [
                        "taxonomy" => $vars["type"],
                        "hide_empty" => false,
                        "number" => $vars["count"],
                        "offset" => $offset,
                        "fields" => "id=>name",
                    ];
                    if (isset($vars["selected"])) {
                        $args["exclude"] = $vars["selected"];
                    }
                    if (!empty($keyword)) {
                        $args["search"] = $keyword;
                        $total_terms = wp_count_terms($args);
                    } else {
                        $total_terms = wp_count_terms($vars["type"]);
                    }
                    $total_pages = ceil($total_terms / $vars["page"]);
                    $terms = get_terms($args);
                }
                if ($post_type) {
                    $args = [
                        "post_type" => $vars["type"],
                        "numberposts" => $count,
                        "offset" => $offset,
                        "fields" => "id=>title",
                    ];
                    if (!empty($keyword)) {
                        $args["search"] = $keyword;
                        $total_terms = wp_count_posts($args);
                    } else {
                        $total_terms = wp_count_posts($vars["type"]);
                    }
                    $total_pages = ceil($total_terms / $vars["page"]);
                    $terms = get_posts($args);
                }
                switch ($vars["response-type"]) {
                    case "select2":
                        if ($taxonomy) {
                            foreach ($terms as $key => $term) {
                                $response["results"][] = [
                                    "id" => $key,
                                    "text" => $term,
                                ];
                            }
                        }
                        if ($post_type) {
                            foreach ($terms as $key => $term) {
                                $response["results"][] = [
                                    "id" => $term->ID,
                                    "text" => $term->post_title,
                                ];
                            }
                        }
                        if ($vars["page"] < $total_pages && $terms) {
                            $response["pagination"]["more"] = true;
                        } else {
                            $response["pagination"]["more"] = false;
                        }
                        break;
                }
                $data = $response;
            } else {
                $error = true;
                $message = "Please provide a type";
            }
            $output = [
                "error" => $error,
                "message" => $message,
                "data" => $data,
                "html" => $html,
                "redirect" => $redirect_url,
            ];
            echo json_encode($output);
            die();
            break;

        case "update_profile_photo":
            $user = new WP_User(get_current_user_id());
            $files = $_FILES["profile_photo_main"];
            if (!empty($files) && $user->ID > 0) {
                $attachments = [];
                foreach ($files["name"] as $key => $value) {
                    if ($files["name"][$key]) {
                        $file = [
                            "name" => $files["name"][$key],
                            "type" => $files["type"][$key],
                            "tmp_name" => $files["tmp_name"][$key],
                            "error" => $files["error"][$key],
                            "size" => $files["size"][$key],
                        ];
                        $attachments[] = $file;
                    }
                }

                foreach ($attachments as $file) {
                    if (is_uploaded_file($file["tmp_name"])) {
                        $remove_these = [" ", "", '\"', "\\", "\/"];

                        $newname = str_replace(
                            $remove_these,
                            "",
                            $file["name"]
                        );
                        $newname = time() . "-" . $newname;
                        $uploads = wp_upload_dir();
                        $upload_path = "{$uploads["path"]}/$newname";
                        move_uploaded_file($file["tmp_name"], $upload_path);
                        $upload_file_url = "{$uploads["url"]}/$newname";

                        $wp_filetype = wp_check_filetype(
                            basename($upload_path),
                            null
                        );
                        $attachment = [
                            "guid" => $upload_file_url,
                            "post_mime_type" => $wp_filetype["type"],
                            "post_title" => preg_replace(
                                '/\.[^.]+$/',
                                "",
                                basename($upload_path)
                            ),
                            "post_content" => "",
                            "post_status" => "inherit",
                        ];
                        $attachment_id = wp_insert_attachment(
                            $attachment,
                            $upload_path,
                            0
                        );

                        if (is_wp_error($attachment_id)) {
                            $json["error"] = "Error.";
                        } else {
                            //delete current
                            $profile_image = get_field(
                                "profile_image",
                                "user_" . $user->ID
                            ); //get_user_meta($user->ID, 'profile_image', true);
                            if ($profile_image) {
                                /*$profile_image = json_decode($profile_image);
								                if (isset($profile_image->attachment_id)) {
								                    wp_delete_attachment($profile_image->attachment_id, true);
								                }*/
                                wp_delete_attachment($profile_image, true);
                            }

                            if (!function_exists("wp_crop_image")) {
                                include ABSPATH . "wp-admin/includes/image.php";
                            }

                            //Generate attachment in the media library
                            $attachment_file_path = get_attached_file(
                                $attachment_id
                            );
                            $data = wp_generate_attachment_metadata(
                                $attachment_id,
                                $attachment_file_path
                            );

                            //Get the attachment entry in media library
                            $image_full_attributes = wp_get_attachment_image_src(
                                $attachment_id,
                                "full"
                            );
                            $image_thumb_attributes = wp_get_attachment_image_src(
                                $attachment_id,
                                "smallthumb"
                            );

                            $arr = [
                                "attachment_id" => $attachment_id,
                                "url" => $image_full_attributes[0],
                                "thumb" => $image_thumb_attributes[0],
                            ];

                            //Save the image in the user metadata
                            update_post_meta(
                                $attachment_id,
                                "_wp_attachment_wp_user_avatar",
                                $user->ID
                            );
                            update_field(
                                "profile_image",
                                $attachment_id,
                                "user_" . $user->ID
                            );

                            $response["message"] = "Image has been uploaded";
                            $response["data"] = $arr["thumb"];
                        }
                    }
                }
            } else {
                $response["error"] = true;
                $response["message"] = "Error";
            }
            echo json_encode($response);
            die();
            break;

        case "update_profile":
            $salt = new Salt();
            $response = $salt->update_profile($vars);
            echo json_encode($response);
            die();
            break;

        case "ads":
            $salt = new Salt();
            $response = $salt->ads($vars);
            if($vars["action"] == "search" || $vars["action"] == "search_my_ads"){
                $context = Timber::get_context();
                $context["projects"] = $response["posts"];
                $response["html"] = Timber::compile("ads/archive.twig", $context);
                /*$response["data"] = array(
                    "count" => $response["data"]->found_posts
                );
                if(isset($vars["posts_per_page"])){
                    $response["ajax"]
                }
                $response["data"] = "";*/
            }
            echo json_encode($response);
            die();                
            break;

        case "suppliers":
            $salt = new Salt();
            $response = $salt->suppliers($vars);
            if($vars["action"] == "search"){
                $context = Timber::get_context();
                if(isset($response["pack"])){
                   $context["pack"] = $response["pack"];
                }
                if(isset($response["project"])){
                   $context["project"] = $response["project"];
                }
                $context["users"] = $response["posts"];//->get_results();
                $response["html"] = Timber::compile("suppliers/archive.twig", $context);
                unset($response["posts"]);
            }
            echo json_encode($response);
            die();                
        break;

        case "form_find_type":
            $findType = $vars["findType"];
            $packId = $vars["packId"];
            if (empty($findType) || empty($packId)) {
                $error = true;
                $message = "Error occured...";
            } else {
                $salt = new Salt();
                $user = $salt->user;
                $work_type_id = get_field("work_type", "packs_" . $packId);
                $work_type = get_term_by("term_id", $work_type_id, "work-type");
                $expertise_ids = get_field("expertise", "packs_" . $packId);
                switch ($findType) {
                    case "publish":
                        $template = "ads/modal/publish.twig";
                        $pack = new Timber\Term($packId); //get_term_by( "id", $packId, "packs");
                        $context = Timber::get_context();
                        $context["expertise"] = $expertise_ids;
                        $context["pack"] = $pack;
                        $context["user"] = $user;
                        $context["work_type"] = $work_type;
                        $html = Timber::compile($template, $context);
                        break;
                    case "request":
                        $project = $vars["project"];
                        $filters = array();
                        $args = [
                            "taxonomy" => "expertise",
                            "include" => $expertise_ids,
                            "hide_empty" => false,
                        ];
                        $expertises = get_terms($args);
                        $expertises = wp_list_pluck($expertises, "term_id");
                        $filters["project"] = $project;
                        $filters["expertise"] = implode(",", $expertises);
                        $filters["work_type"] = $work_type_id;
                        $redirect_url =
                            $GLOBALS["base_urls"]["suppliers"] .
                            "search/" .
                            $work_type->slug .
                            "?filters=" .
                            json_encode($filters);
                        break;
                }
            }
            $output = [
                "error" => $error,
                "message" => $message,
                "data" => "",
                "html" => $html,
                "redirect" => $redirect_url,
            ];
            echo json_encode($output);
            die();
            bteak;

        case "publish_brief":
            $salt = new Salt();
            echo $salt->publish_brief($vars);
            die();
        break;

        case "get_notifications" :
            $user = array();
            if(!isset($vars["user"])){
                $user = wp_get_current_user();
            }
            $notifications = new Notifications($user);
            $result = $notifications->get_notifications($vars);
            if(isset($result["posts"])){
                $template = "partials/notifications/archive.twig";
                $context = Timber::get_context();
                $context["posts"] = $result["posts"];
                $response["html"] = Timber::compile($template, $context);                
            }
            $response["data"] = array_map("intval", $result["data"]); 
            echo json_encode($response);
            die();
        break;

        case "send_activation" :
            echo Salt::send_activation($vars["id"]);
            die();
        break;
    }

    if (isset($template)) {
        $context["ajax_call"] = true;
        $context["ajax_method"] = $method;
        if (isset($templates)) {
            $data["html"] = Timber::compile($templates, $context);
        }
        if (isset($page)) {
            $data["page"] = $page;
        }
        if (isset($page_count)) {
            $data["page_count"] = $page_count;
        }
        if (isset($post_count)) {
            $data["post_count"] = $post_count;
        }
        if (isset($query_vars)) {
            $data["query_vars"] = $query_vars;
        }
        echo json_encode($data);
        /*Timber::render( $templates, $context );*/
    }
}
