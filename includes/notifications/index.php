<?php

//https://github.com/MyIntervals/emogrifier
use Pelago\Emogrifier\CssInliner;
//use Pelago\Emogrifier\HtmlProcessor\CssToAttributeConverter;
//use Pelago\Emogrifier\HtmlProcessor\HtmlNormalizer;

Class Notifications{

	public $user;

    function __construct($user=array(), $debug=0) {
    	global $wpdb;
    	if(isset($user) && !empty($user)){
    		$this->user = $user;
    	}
        $this->events = array();
        $this->debug = $debug;
        $this->debug_output = array();
        $this->html_path = get_stylesheet_directory() . "/includes/notifications/events/";
        $this->html_url = get_stylesheet_directory_uri() . "/includes/notifications/events/";
        $this->css_path = get_stylesheet_directory() . "/static/css/email.css";
        $this->css_url = get_stylesheet_directory_uri() . "/static/css/email.css";
        $table = "notifications";
        if (!$wpdb->get_var("SHOW TABLES LIKE 'wp_".$table."'")) {
		    echo "Table does not exist";
		    $this->create_db($table);
		}
		//get admin
	    $args = array(
           'role'    => 'administrator',
           'number'  => 1
        );
        $administrator = get_users( $args )[0];
	    //$admin_title = get_option('blogname');
	    $this->administrator = $administrator;
	    //add_action('wp_mail_failed', $this->log_mailer_errors(), 10, 1);
    }

    function create_db($table) {
		global $wpdb;
	  	$version = get_option( 'notifications_version', '1.0' );
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . $table;

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			sender_id smallint(5) NOT NULL,
			receiver_id smallint(5) NOT NULL,
			message longtext NOT NULL,
			action text NOT NULL,
			seen smallint(5) NOT NULL,
			alert smallint(5) NOT NULL,
			UNIQUE KEY id (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		
		if ( version_compare( $version, '3.0' ) < 0 ) {
			$sql = "CREATE TABLE $table_name (
			  id mediumint(9) NOT NULL AUTO_INCREMENT,
			  created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			  sender_id smallint(5) NOT NULL,
			  receiver_id smallint(5) NOT NULL,
			  message longtext NOT NULL,
			  action text NOT NULL,
			  seen smallint(5) NOT NULL,
			  alert smallint(5) NOT NULL,
			  post_id smallint(5) NOT NULL,
			  user_id smallint(5) NOT NULL,
			  UNIQUE KEY id (id)
			) $charset_collate;";
			dbDelta( $sql );
		  	update_option( 'notifications_version', '3.0' );
		}
	}

    function load($event){
    	if($event){
    		if(!$this->is_valid($event)){
	    	    $file = dirname(__FILE__)."/events/$event.php";
	    	    $file_obj = pathinfo($file);
				include $file;
			    $this->events[$file_obj["filename"]] = $array;    			
    		}
    	}else{
	    	foreach (glob(dirname(__FILE__)."/events/*.php") as $file){
	        	$file_obj = pathinfo($file);
	        	$event = $file_obj["filename"];
	        	if(!$this->is_valid($event)){
				    include $file;
				    $this->events[$event] = $array;
				}
			}    		
    	}
    }

    Private function is_valid($event){
    	return isset($this->events[$event]);
    }

    function on($event, $data){
    	$status = array();
    	if($this->is_valid($event)){
    		$carriers = array_keys($this->events[$event]["carriers"]);
    		foreach($carriers as $carrier){
    		   $action = "send_".$carrier;
               $status[$carrier] = $this->$action($event, $data);
    		}
    	}else{
    		 $status[$carrier] = "$event not supported";
    	}
    	if($this->debug){
    		$this->debug_output = $status;
    	}
    }

    Private function data_rename($rules=array(), $data=array(), $carrier="", $event=""){
       if(isset($data["post"])){
          $rules["post"] = $data["post"];
       }
       if(isset($data["user"])){
          $rules["user"] = $data["user"];
       }
       if($rules["transmit"]["sender"] == "{{administrator}}"){
       	  $rules["transmit"]["sender"] = $this->administrator->ID;
       }
       if($rules["transmit"]["sender"] == "{{me}}"){
       	  $rules["transmit"]["sender"] = $this->user->ID;
       }
       if($rules["transmit"]["recipient"] == "{{administrator}}"){
       	  $rules["transmit"]["recipient"] = $this->administrator->ID;
       }
       if($rules["transmit"]["recipient"] == "{{me}}"){
       	  $rules["transmit"]["recipient"] = $this->user->ID;
       }
       if($rules["transmit"]["recipient"] == "{{users}}"){
       	  $rules["transmit"]["recipient"] = $data["recipient"];
       }
       if($rules["transmit"]["recipient"] == "{{user}}"){
       	  $rules["transmit"]["recipient"] = isset($data["recipient"])?$data["recipient"]:$data["user"]->ID;
       }
       if($rules["transmit"]["recipient"] == "{{author}}"){
       	  $rules["transmit"]["recipient"] = $data["post"]->author->ID;
       }


       
       switch($carrier){
       	  case "notification" :
       	      $message = $rules["carriers"][$carrier];
              $rules["carriers"][$carrier] = $this->render($message, $data);
       	  break;
       	  case "email" :
       	        $subject = $rules["carriers"][$carrier]["subject"];
       	        $subject = preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, $subject);
		        $rules["carriers"][$carrier]["subject"] = $this->render($subject, $data);

		        $body = $rules["carriers"][$carrier]["body"];
		        if($body == "template"){
		            $template_path = $this->html_path.$event."-parsed.html";
		            if(!file_exists($template_path)){
		              //echo "parsed not found";
		               $body = $this->html_mail($event);
		               $body = str_replace("%7B%7B", "{{", $body);
		               $body = str_replace("%7D%7D", "}}", $body);
		               file_put_contents($template_path, $body);
		            }else{
		              //echo "parsed found";
		           	   $template_url = $this->html_url.$event."-parsed.html";
		           	   $body = file_get_contents($template_url);
		            }
		        }
		        $rules["carriers"][$carrier]["body"] = $this->render($body, $data);
       	  break;
       }
       return $rules;
    }

    Private function send_notification($event, $data){
    	global $wpdb;
    	$status = 0;
    	$rules = $this->events[$event];
    	$data = $this->data_rename($rules, $data, "notification", $event);

    	$transmit = $data["transmit"];
    	$sender_id = $transmit["sender"];
    	$receivers = $transmit["recipient"];
    	if(!is_array($receivers)){
           $receivers = [$receivers]; 
    	}
    	$message = $data["carriers"]["notification"];
    	$post_id = 0;
    	if(isset($data["post"])){
    	   $post_id = $data["post"]->ID;
    	}
    	$user_id = 0;
    	if(isset($data["user"])){
    	   $user_id = $data["user"]->ID;
    	}
    	
    	foreach($receivers as $receiver){
	    	$row = array(
	    		'created_at' => date('Y-m-d H:i:s', strtotime('now')),
	            'sender_id' => $sender_id,
	            'receiver_id' => $receiver,
	            'message' => $message,
	            'action' => $event,
	            'seen' => 0,
	            'alert' => 0,
	            'post_id' => $post_id,
	            'user_id' => $user_id
	        );
	        $status = $wpdb->insert("wp_notifications", $row);
    	}
    	return $status;
    }

    Private function get_users($ids=array(0), $values=array('user_email')){
    	global $wpdb;
    	$values = implode(",",$values);
    	$ids = array_map("intval", $ids);
    	$ids = implode(",", $ids);
        return $wpdb->get_results("SELECT $values FROM wp_users where ID in ($ids)");
    }
    Private function get_users_full($ids=array(0)){
        $args = array(
           'include'  => $ids
        );
        return get_users( $args );
    }

    Private function send_email($event, $data){
        $status = 0;
    	$rules = $this->events[$event];
    	$data = $this->data_rename($rules, $data, "email", $event);

    	$type = $data["carriers"]["email"]["type"];
    	
    	$subject = $data["carriers"]["email"]["subject"];
		$body = $data["carriers"]["email"]["body"];

    	$transmit = $data["transmit"];
    	//$sender_id = $transmit["sender"];
    	$receivers = $transmit["recipient"];
    	if(!is_array($receivers)){
           $receivers = [$receivers]; 
    	}
    	$receivers = $this->get_users($receivers);
    	$receivers = wp_list_pluck( $receivers, "user_email");
    	$headers = $this->send_mail_headers($this->administrator->display_name, $this->administrator->user_email);
    	if(empty($type)){
	        foreach($receivers as $receiver){
	            $status = wp_mail($receiver, $subject, $body, $headers );
		    }
    	}else{
    		foreach($receivers as $receiver){
	    		$headers[] =  $type.': '.$receiver;
	    	}
	    	$status = wp_mail("", $subject, $body, $headers );
    	}
	    return $status;
    }
    function send_mail_headers($from_name, $from_email){
    	$headers = array();
        $headers[] = 'From: '.$from_name.' <'. $from_email.'>';
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$headers[] = 'Reply-To: '.$from_name.' <'. $from_email.'>';
		return $headers;
    }

    function html_mail($event){
		$html = file_get_contents($this->html_url.$event.".html");
		$css = file_get_contents($this->css_url);

		//$html = HtmlNormalizer::fromHtml($html)->render();
		$html = CssInliner::fromHtml($html)->inlineCss($css)->render();
		//$html = CssToAttributeConverter::fromHtml($html)->convertCssToVisualAttributes()->render();
		return $html;
    }

	function log_mailer_errors( $wp_error ){
		  $fn = ABSPATH . '/mail.log'; // say you've got a mail.log file in your server root
		  $fp = fopen($fn, 'a');
		  fputs($fp, "Mailer Error: " . $wp_error->get_error_message() ."\n");
		  fclose($fp);
	}

    Private function render($text, $data){
    	$context = Timber::get_context();
   	    $context["data"] = $data; 
   	    return Timber::compile_string($text, $context);
    }

    function get_notifications($data=array()){

    	$results = array();
    	$where = array(); 
    	if(isset($data['user'])){
           $where[] = "receiver_id = ".$data["user"];
    	}else{
    	   $where[] = "receiver_id = ".$this->user->ID;
    	}
    	if(isset($data['post'])){
           $where[] = "post_id = ".$data["post"];
    	}
    	if(isset($data['seen'])){
           $where[] = "seen = ".$data["seen"];
    	}
    	if(count($where)>0){
    		$where = implode(" AND ", $where);
    	}
    	$query_values = "*";
    	if(isset($data['get_count'])){
           $query_values = "count(*)";
    	}
    	$query = "SELECT * FROM wp_notifications where ".$where;

    	if(isset($data['get_count'])){

    		$paginate = new Paginate();
		    $paginate->query = $query;
		    $results["data"] = $paginate->get_totals();
		    
    	}else{

    		$orderby = "created_at";
		    if(isset($data['orderby'])){
		  		$orderby = $data['orderby'];
		    }

		    $order = "desc";
		   	if(isset($data['order'])){
		   		$order = $data['order'];
		    }

    		if(isset($data['post_per_page']) || isset($data['page'])){
		    	if(isset($data['page'])){
		    		$page = $data['page'];
		    	}else{
		    		$page = 1;
		    	}
		    	if(isset($data['post_per_page'])){
		    		$post_per_page = $data['post_per_page'];
		    	}else{
		    		$post_per_page = 10;
		    	}
		    }

		    $paginate = new Paginate();
		   	$paginate->query = $query;
		   	$paginate->orderby = $orderby;
		   	$paginate->order = $order;
		   	if(isset($page)){
               $paginate->page = $page;
		   	}
		   	if(isset($post_per_page)){
	            $paginate->post_per_page = $post_per_page;
	        }
	    	$results["data"] = $paginate->get_totals();
	    	$results["posts"] = $paginate->get_results();

	    	if(isset($data["set_seen"])){
               //$ids = wp_list_pluck($results["posts"],"id");
               if($results["posts"]){
               	  global $wpdb;
               	  foreach($results["posts"] as $post){
               	      $wpdb->update('wp_notifications', array('seen'=> '1'), array('id'=>$post->id));
               	  }
               }
	    	}
	    }
        return $results;
    }
 

    static function delete_post_notifications($post_id=0){
    	global $wpdb;
    	if($post_id > 0){
    	   	$wpdb->delete( "wp_notifications", array( 'post_id' => $post_id ) ); 	   	  	
    	}
    }
    static function delete_user_notifications($user_id){
    	global $wpdb;
    	if($user_id > 0){
    	   	$wpdb->delete( "wp_notifications", array( 'sender_id' => $user_id ) );
    	   	$wpdb->delete( "wp_notifications", array( 'receiver_id' => $user_id ) );   	  	
    	}
    }
}


Class Paginate{
//Class DbQuery{

	public $query;
	public $page;
	public $post_per_page;
	public $orderby;
	public $order;

    function __construct() {
    	if(isset($post_per_page) && !isset($page)){
    	   $page = 0;
		   $page = isset( $_GET['cpage'] ) ? abs( (int) $_GET['cpage'] ) : abs( (int) $page);    	
		   $page = $page<1 ? 1 : $page;
		   $this->page = $page;
    	}
    	if(isset($page)){
    		$this->page = $page==0?1:$page;
    	}
    	
    	//print_r($this->page);
    }

    function get_totals(){
    	global $wpdb;
    	$query = $this->query;
        $total = $wpdb->get_var( "SELECT COUNT(1) FROM (${query}) AS combined_table" );
        if(isset($this->post_per_page)){
        	$page_total = ceil($total / $this->post_per_page);
        }
        $result = array(
        	"total" => $total
        );
        if(isset($this->post_per_page)){
        	$result["page"] = $this->page;
        	$result["page_total"] = $page_total;
        }
        return $result;
    }

    function get_results($type="post"){
    	if(is_array($this->query)){
    	   $query = $this->query;
    	   $query[$type=="post"?"posts_per_page":"number"] = $this->post_per_page;
    	   $query["paged"] = $this->page;
    	   //print_r($query);
    	   if($type=="post"){
               return new Timber\PostQuery($query);
    	   }else{
    	   	   return new WP_User_Query($query);
    	   }
        }else{
	    	$query = $this->query;
	    	$orderby = $this->orderby;
	    	$order = $this->order;
	        if(isset($this->post_per_page)){
		    	$post_per_page = $this->post_per_page;
		        $offset = ( $this->page * $post_per_page ) - $post_per_page;
		        $query .= " ORDER BY ${orderby} ${order} LIMIT ${offset}, ${post_per_page}";
		    }else{
		    	$query . " ORDER BY ${orderby} ${order}";
		    }
		    global $wpdb;
	        return $wpdb->get_results( $query );        	
        }

    }

}