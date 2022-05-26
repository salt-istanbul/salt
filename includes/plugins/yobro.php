<?php

   // fixes
   // get_users_all_conversation function on yobro-helper.php
   // lines 69, 70

    //add required columns to conversation table
	$table_name = "wp_yobro_conversation";
	$columns = array(
		array(
			"table" => "wp_yobro_conversation",
			"name"  => "project_id",
			"type"  => "bigint(200) NOT NULL DEFAULT 0"
		),
		array(
			"table" => "wp_yobro_conversation",
			"name" => "product_id",
			"type" => "bigint(200) NOT NULL DEFAULT 0"
		),
		array(
			"table" => "wp_yobro_messages",
			"name" => "notification",
		    "type" => "tinytext COLLATE utf8mb4_unicode_520_ci"
		)
	);
	if($columns){
		global $wpdb;
		$database = $wpdb->dbname;;
		foreach($columns as $column){
			$table = $column["table"];
			$column_name = $column["name"];
			$column_type = $column["type"];
			$rows = $wpdb->get_results("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = '$database' and table_name = '$table';");
			$exist = false;
			foreach($rows as $row){
				if($row->COLUMN_NAME == $column_name){
				   $exist = true;
				}				
			}
			if(!$exist){
				$wpdb->query("ALTER TABLE $table ADD $column_name $column_type;");	
			}
		}
	}


	function yobro_notification_messages($action){
		global $wpdb;
    	$yobro_settings = get_option('yo_bro_settings', true);
    	$chat_page_url = wc_get_account_endpoint_url('messages');
    	$user_id = get_current_user_id();
    	$sql_action_query = "";
    	if(isset($action)){
    	   switch($action){
    	   	   case "seen":
                    $sql_action_query = " and messages.seen is null";
    	   	   break;
    	   	   case "notification":
                    $sql_action_query = " and messages.notification is null";
    	   	   break;
    	   }
    	}
    	$sql = "SELECT DISTINCT messages.id, messages.conv_id, messages.sender_id, messages.message, messages.created_at, conversation.project_id, conversation.product_id
					  FROM wp_yobro_messages messages
					  INNER JOIN wp_yobro_conversation conversation
					  ON messages.conv_id = conversation.id 
					  where messages.reciever_id=$user_id". $sql_action_query ."
					  order by messages.created_at ASC limit 1";
		$messages = $wpdb->get_results($sql);
	    $unseen_messages = array();
	    $timeAgo = new Westsworld\TimeAgo();
	    foreach($messages as $message){
	    	$url_query = "?conversationId=".$message->conv_id."&action=error";
	    	$tour_title = "";
	    	if(isset($message->product_id) && $message->product_id == 0 && isset($message->project_id) && $message->project_id == 0){
		       $url_query = "?conversationId=".$message->conv_id;
		       $tour_title = "Message from Bot";
		    }
		    if(isset($message->product_id) && $message->product_id > 0 ){
		       $url_query = "?conversationId=".$message->conv_id."&action=product&project_id=".$message->product_id;
		       $tour_title = get_the_title( $message->product_id );
		    }
		    if(isset($message->project_id) && $message->project_id > 0){
		       $url_query = "?conversationId=".$message->conv_id."&action=project&project_id=".$message->project_id;
		       $tour_title = get_the_title( $message->project_id );
		    }
		    $sender = get_user_by("id", $message->sender_id);
	    	$unseen_messages[] = array(
	    		"id"      => $message->conv_id,
	    		"title"   => $tour_title,
                "sender"  => array(
                	             "image" => get_avatar( $message->sender_id, 32, 'mystery', $sender->display_name),
                	             "name"  => $sender->display_name
                	         ),
                "message" => truncate(strip_tags(encrypt_decrypt($message->message, $message->sender_id, 'decrypt')), 150),
                "url"     => $chat_page_url.$url_query,
                "time"    => $timeAgo->inWordsFromStrings($message->created_at)
	    	);
	    	$wpdb->query("UPDATE wp_yobro_messages SET notification=1 WHERE id=".$message->id);
	    }
	    return $unseen_messages ;
	}



	function yobro_unseen_messages_count($conv_id=0){
		global $wpdb;
		//"select count(*) from wp_yobro_messages where receiver_id=$user_id and not seen = 1"
        $user_id = get_current_user_id();
        $sql = "SELECT COUNT(DISTINCT conv_id) FROM wp_yobro_messages where reciever_id=$user_id and seen is null ".($conv_id>0?" and conv_id=$conv_id":"");
		return $wpdb->get_var($sql);
	}
	function yobro_unseen_project_messages_count($project_id=0){
		global $wpdb;
		//"select count(*) from wp_yobro_messages where receiver_id=$user_id and not seen = 1"
        $user_id = get_current_user_id();
        $sql = "SELECT COUNT(*) 
                   FROM wp_yobro_messages m
                   INNER JOIN wp_yobro_conversation AS c ON (m.conv_id = c.id)
                   where 
                          m.reciever_id=$user_id
                      and m.seen is null 
                      and c.project_id=$project_id";
		return $wpdb->get_var($sql);
	}
    function yobro_unseen_messages(){
    	global $wpdb;
    	$yobro_settings = get_option('yo_bro_settings', true);
    	$chat_page_url = wc_get_account_endpoint_url('messages');//$yobro_settings['chat_page_url'];
    	$user_id = get_current_user_id();
    	//$sql = "SELECT DISTINCT conv_id, sender_id, message, created_at FROM wp_yobro_messages where reciever_id=$user_id and seen is null group by conv_id order by created_at desc";
    	$sql = "SELECT DISTINCT messages.conv_id, messages.sender_id, messages.message, messages.created_at, conversation.project_id, conversation.product_id
					  FROM wp_yobro_messages messages
					  INNER JOIN wp_yobro_conversation conversation
					  ON messages.conv_id = conversation.id 
					  where messages.reciever_id=$user_id and messages.seen is null
					  group by messages.conv_id
					  order by messages.created_at DESC,messages.id asc";

	    $sql_2 = "SELECT DISTINCT messages.conv_id, messages.sender_id, messages.message, messages.created_at
					FROM wp_yobro_messages messages
					     
					where messages.reciever_id=$user_id and messages.seen is null
					group by messages.conv_id
					order by messages.id DESC;";
		

		$sql_2 = "SELECT messages.conv_id, messages.sender_id, messages.message, messages.created_at, conversation.project_id, conversation.product_id
					FROM wp_yobro_messages messages 
					LEFT JOIN wp_yobro_conversation conversation
					     ON (messages.conv_id = conversation.id)
					     WHERE messages.reciever_id=$user_id and messages.seen is null
					group by messages.conv_id
					order by messages.id DESC";

		$sql_2 = "SELECT messages.conv_id, messages.sender_id, messages.message, messages.created_at, conversation.project_id, conversation.product_id
					from (
					   SELECT project_id, product_id, id
					   from wp_yobro_conversation 
					) as conversation, wp_yobro_messages as messages where messages.conv_id = conversation.id and messages.reciever_id=$user_id and messages.seen is null 
					group by messages.conv_id
					order by messages.created_at DESC";

	    $sql_2 = "SELECT messages.conv_id, messages.sender_id, messages.message, messages.created_at, conversation.project_id, conversation.product_id
					FROM      wp_yobro_conversation conversation
					JOIN      (
					              SELECT    id, conv_id, sender_id, message, created_at
					              FROM      wp_yobro_messages 
					              where     reciever_id=$user_id and seen is null
					              GROUP BY  conv_id
					              order by  created_at DESC limit 1
					          ) messages 
					    ON (messages.conv_id = conversation.id) order by messages.conv_id DESC";

	    $sql_2 = "SELECT i1.*
				FROM wp_yobro_messages AS i1 LEFT JOIN wp_yobro_conversation AS i2
				ON (i1.reciever_id=$user_id and i1.seen is null and i1.conv_id = i2.id )
				group by i1.conv_id order by i1.created_at DESC";

	    $messages = $wpdb->get_results($sql);
	    $unseen_messages = array();
	    $timeAgo = new Westsworld\TimeAgo();
	    foreach($messages as $message){
	    	$url_query = "?conversationId=".$message->conv_id."&action=error";
	    	$tour_title = "";
		    if(isset($message->product_id) && $message->product_id > 0 ){
		       $url_query = "?conversationId=".$message->conv_id."&action=product&project_id=".$message->product_id;
		       $tour_title = get_the_title( $message->product_id );
		    }
		    if(isset($message->project_id) && $message->project_id > 0){
		       $url_query = "?conversationId=".$message->conv_id."&action=project&project_id=".$message->project_id;
		       $tour_title = get_the_title( $message->project_id );
		    }
		    $sender = get_user_by("id", $message->sender_id);
	    	$unseen_messages[] = array(
	    		"id"      => $message->conv_id,
	    		"title"   => $tour_title,
                "sender"  => array(
                	             "image" => get_avatar( $message->sender_id, 32, 'mystery', $sender->display_name),
                	             "name"  => $sender->display_name
                	         ),
                "message" => removeUrls(strip_tags(encrypt_decrypt($message->message, $message->sender_id, 'decrypt'))),
                "url"     => $chat_page_url.$url_query,
                "time"    => $timeAgo->inWordsFromStrings($message->created_at)
	    	);
	    }
	    return $unseen_messages ;
	}



    function yobro_first_conversation($project_id, $sender_id, $reciever_id){
    	global $wpdb;
        $sql = "SELECT * FROM wp_yobro_conversation where reciever=$reciever_id and sender=$sender_id ". ($project_id > 0 ?"and project_id = $project_id":"")." order by created_at DESC limit 1";
		return $wpdb->get_results($sql);
    }
    function yobro_first_admin_conversation($project_id, $reciever_id){
    	global $wpdb;
    	$administrator = get_field("site_admin", "option");
	    $admin_id =  $administrator["ID"];
        $sql = "SELECT * FROM wp_yobro_conversation where reciever=$reciever_id and sender=$admin_id and project_id = $project_id order by created_at DESC limit 1";
		return $wpdb->get_results($sql);
    }
    function yobro_has_admin_conversation($project_id, $reciever_id){
    	global $wpdb;
    	$administrator = get_field("site_admin", "option");
	    $admin_id =  $administrator["ID"];
		$sql = "SELECT COUNT(DISTINCT id) FROM wp_yobro_conversation where reciever=$reciever_id and sender=$admin_id and project_id = $project_id order by created_at DESC limit 1";
		return $wpdb->get_var($sql);
    }
    
    function yobro_new_conversation($sender_id, $reciever_id, $message="", $project_id=0){
    	global $wpdb;
		$new_conversation =  \YoBro\App\Conversation::create(array(
	      'sender'   => $sender_id,
	      'reciever' => $reciever_id
	    ));
	    if($project_id > 0){
	    	$wpdb->query("UPDATE wp_yobro_conversation SET project_id=".$project_id." WHERE id=".$new_conversation['id']);
	    }
	    if(!empty($message)){
		    return  \YoBro\App\Message::create(array(
		      'conv_id' => $new_conversation['id'],
		      'sender_id' => $sender_id,
		      'reciever_id' => $reciever_id ,
		      'message' => encrypt_decrypt($message, $sender_id),
		      'attachment_id' => null ,
			  'created_at' => date("Y-m-d H:i:s"),
		    ));	    	
	    }else{
            return $new_conversation['id'];
	    }
    }

    function yobro_send_message($conv_id, $sender_id, $reciever_id, $message){
    	$args = array(
		   "conv_id" => $conv_id,
		   "message" => $message,
		   "sender_id" => $sender_id,
		   "reciever_id" => $reciever_id
		);
		return do_store_message($args);
    }

    function yobro_check_conversation_exist($project_id, $sender_id, $reciever_id, $forced=false){
    	global $wpdb;
    	if($sender_id == $reciever_id && $project_id > 0){
    		$where_user = " reciever=$sender_id or sender=$sender_id ";
    	}else{
            $where_user = "((reciever=$reciever_id and sender=$sender_id )".($forced?" or (reciever=$sender_id and sender=$reciever_id)":"").")";
    	}
        $sql = "SELECT id FROM wp_yobro_conversation where ". $where_user . ($project_id > 0 ?"and project_id = $project_id":"")." order by id ASC limit 1";
		return $wpdb->get_var($sql);
    }

    function yobro_remove_conversation($sender, $reciever, $post_id){
    	global $wpdb;
    	if($sender){
    	    foreach($sender as $sender_id){
    	   	    $conv_id = $wpdb->get_var("select id from wp_yobro_conversation where sender=".$sender_id." and reciever=".$reciever." and project_id=".$post_id);
    	   	    yobro_remove_conversation_by_id($conv_id);
    	    }
    	}
    }
    function yobro_remove_conversation_by_project($post_id){
    	global $wpdb;
    	$conversations = $wpdb->get_results("select id from wp_yobro_conversation where project_id=".$post_id);
    	if($conversations){
    	   $conversations = wp_list_pluck($conversations, "id");
    	   foreach($conversations as $conversation){
    	   	  yobro_remove_conversation_by_id($conv_id);
    	   }
    	}
    }
    function yobro_remove_conversation_by_user($user_id){
    	global $wpdb;
    	$conversations = $wpdb->get_results("select id from wp_yobro_conversation where sender=$user_id or reciever=$user_id");
    	if($conversations){
    	   $conversations = wp_list_pluck($conversations, "id");
    	   foreach($conversations as $conversation){
    	   	  yobro_remove_conversation_by_id($conv_id);
    	   }
    	}
    }
    function yobro_remove_conversation_by_id($conv_id){
    	global $wpdb;
    	if($conv_id > 0){
    	   	$wpdb->delete( "wp_yobro_messages", array( 'conv_id' => $conv_id ) );
    	   	$wpdb->delete( "wp_yobro_conversation", array( 'id' => $conv_id ) );  	   	  	
    	}
    }



    function yobro_has_reciever_conservations($user_id=0){
		global $wpdb;
		if($user_id == 0){
		   $user_id = get_current_user_id();
		}
        $sql = "SELECT COUNT(DISTINCT conv_id) FROM wp_yobro_messages where reciever_id=$user_id";
		return $wpdb->get_var($sql);
	}

	function yobro_get_conversation($conv_id=0){
    	global $wpdb;
    	/*$sql = "Select m.id as id, m.message as message, u.display_name as agent from wp_yobro_messages m, wp_users u where m.conv_id = ".$conv_id;
    	return $wpdb->get_results($sql);*/

    	$sql = "SELECT DISTINCT messages.id, messages.conv_id, messages.sender_id, messages.message, messages.created_at
					  FROM wp_yobro_messages messages
					  where messages.conv_id = $conv_id
					  order by messages.created_at ASC";
		return $wpdb->get_results($sql);
    }

    function yobro_get_conversation_data($conv_id=0){
    	global $wpdb;
    	$sql = "SELECT DISTINCT *
					  FROM wp_yobro_conversation
					  where id = $conv_id limit 1";
		return $wpdb->get_results($sql);
    }

    function yobro_get_reciever_conversations($user_id){
    	global $wpdb;
    	$sql = "Select c.id as conversation_id, t.post_title as title, u.display_name as agent from wp_yobro_conversation c, wp_posts t, wp_users u where t.post_type='project' and c.reciever=".$user_id." and c.project_id = t.ID and c.sender = u.ID";
    	return $wpdb->get_results($sql);
    }
    function yobro_get_sender_conversations($user_id){
    	global $wpdb;
    	$sql = "Select c.id as conversation_id, t.post_title as title, u.display_name as agent from wp_yobro_conversation c, wp_posts t, wp_users u where t.post_type='project' and c.sender=".$user_id." and c.project_id = t.ID and c.sender = u.ID";
    	return $wpdb->get_results($sql);
    }
    function yobro_get_all_conversations($user_id){
    	global $wpdb;
    	$sql = "Select c.id as conversation_id, t.post_title as title from wp_yobro_conversation c, wp_posts t, wp_users u where t.post_type='project' and (c.sender=".$user_id." or c.reciever=".$user_id.") and c.project_id = t.ID and c.sender = u.ID";
    	return $wpdb->get_results($sql);
    }

    function yobro_create_conversation_dropdown($user_id){
    	$chat_page_url = wc_get_account_endpoint_url('messages');
    	$conversations = yobro_get_all_conversations($user_id);
    	$code = "";
    	if($conversations){
    	   $code = "<select class='selectpicker selectpicker-url-update' name='conversations'>";
    	   foreach($conversations as $conversation){
    	      $selected = $conversation->conversation_id == get_query_var("conversationId")?" selected":"";
    	   	  $code .= "<option value='".$chat_page_url."?conversationId=".$conversation->conversation_id."'".$selected.">".$conversation->title."</option>";
    	   }
    	   $code .= "</select>"; 
    	}
    	return $code;
    }



    function get_few_messages_by_conversation($conv_id){
		$current_user_id = get_current_user_id();

	    //get current page's tour chat
	    if(isset($_SESSION["querystring"])){
			$params = $_SESSION["querystring"];
			unset_filter_session('querystring'); 
			$params = json_decode($params, true);
			if(!empty($params)){
			   /*if( isset( $params['tour-plan-offer-id'] ) ){
			   	  $conv_id = yobro_get_offer_conversation($params['tour-plan-offer-id']);
			   }*/
			   if( isset( $params['conversationId'] ) ){
	              $conv_id = $params['conversationId'];
			   }
			}
	    }

		$messages = \YoBro\App\Message::where('conv_id', '=', $conv_id)
			->where('delete_status', '!=', 1)
			->where(function ($query) use ($current_user_id) {
				$query->where('sender_id', $current_user_id)
					->orWhere('reciever_id', $current_user_id);
			})
			->orderBy('id', 'asc')->get()->toArray();
		$total_messages = array();
		if (isset($messages) && !empty($messages)) {
			foreach ($messages as &$message) {
				$message['message'] = encrypt_decrypt($message['message'], $message['sender_id'], 'decrypt');
				if ($message['sender_id'] == get_current_user_id()) {
					$message['owner'] = 'true';
				} else {
					$message['owner'] = 'false';
				}
				// if( bp_core_fetch_avatar( array( 'item_id' => $message['sender_id'], 'type' => 'thumb')) ){
				//   $message['pic'] =  bp_core_fetch_avatar( array( 'item_id' => $message['sender_id'], 'type' => 'thumb', 'html'   => FALSE ));
				// }else{
				//   $message['pic'] =  up_user_placeholder_image();
				// }
				if (get_avatar($message['sender_id'])) {
					$message['pic'] =  get_avatar($message['sender_id']);
				} else {
					$message['pic'] =  up_user_placeholder_image();
				}
				$message['reciever_name'] = get_user_name_by_id($message['reciever_id']) ?  get_user_name_by_id($message['reciever_id']) : 'Untitled';
				$message['sender_name'] = get_user_name_by_id($message['sender_id']) ?  get_user_name_by_id($message['sender_id']) : 'Untitled';
				$message['time'] = $message['created_at'];
				if (isset($message['attachment_id']) && $message['attachment_id'] != null) {
					$message['attachments'] = YoBro\App\Attachment::where('id', '=', $message['attachment_id'])->first();
				}
				if (!isset($total_messages[$message['id']])) {
					$total_messages[$message['id']] = $message;
				}
			}
			return $total_messages;
		} else {
			return array();
		}
	}

    
    function yobro_get_conversation_last_message($conv_id=0, $sender_id=0){
    	global $wpdb;
    	$sql = "SELECT DISTINCT messages.id, messages.conv_id, messages.sender_id, messages.message, messages.created_at
					  FROM wp_yobro_messages messages
					  where messages.conv_id = $conv_id ".
					  ($sender_id>0?" and sender_id=".$sender_id:"").
					  " order by messages.created_at DESC limit 1";
		$results = $wpdb->get_results($sql);
		if(count($results)>0){
			$results=$results[0];
		}
		return $results;
    } 
	function yobro_get_project_conversations($project_id=0, $user_id=0){
		global $wpdb;
	    $sql = "SELECT DISTINCT messages.created_at, messages.conv_id, conversation.project_id, conversation.sender, conversation.reciever, (count(messages.id) - count(messages.seen)) as new_messages, messages.message, messages.created_at
					FROM 
						wp_yobro_messages messages 
					INNER JOIN 
						wp_yobro_conversation conversation 
					ON 
						messages.conv_id = conversation.id 
					where 
						(conversation.reciever=$user_id or conversation.sender=$user_id) ".($project_id>0?" and conversation.project_id=$project_id ":"").
					"group by 
						messages.conv_id 
					order by new_messages DESC, messages.created_at DESC";
		$results =  $wpdb->get_results($sql);
        $output = array();
		foreach($results as $result){
			$sender_id = $user_id==$result->sender?$result->reciever:$result->sender;
			$sender = new User($sender_id);
			$message = yobro_get_conversation_last_message($result->conv_id, $sender_id);
            $item = array(
	    		"id"      => $result->conv_id,
	    		"project_id"      => $result->project_id,
                "sender"  => array(
                	             "id"    => $sender_id,
                	             "image" => $sender->get_avatar_url,
                	             "name"  => $sender->get_title
                ),
                "message" => "",
                //"url"     => $chat_page_url.$url_query,
                "new_messages" => 0,
                "time"    => $result->created_at
	    	);
	    	if($message){
	    		$item["sender"] = array(
                	             "id"    => $message->sender_id,
                	             "image" => $sender->get_avatar_url,
                	             "name"  => $sender->get_title
                );
                $item["message"] = removeUrls(strip_tags(encrypt_decrypt($message->message, $message->sender_id, 'decrypt')));
                $item["new_messages"] = $result->new_messages;
                $item["time"] = $message->created_at;
	    	}
            $output[] = $item;
		}
		return $output;	
	}



    /*
	# BEGIN WordPressRewriteEngine On
	RewriteBase /
	RewriteRule ^index.php$ – [L]
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule . /index.php [L]# END WordPress
	*/
	function add_custom_htaccess( $rules ){
		$folder = getSiteSubfolder();
		$rules = <<<EOF
		    <IfModule mod_rewrite.c>
					RewriteEngine On
					RewriteBase ${folder}
					RewriteRule ^index\.php$ - [L]
					RewriteRule ^list-data$ ${folder}\/ajax\/(.+?)\/?$ [QSA,L]
					RewriteCond %{REQUEST_FILENAME} !-f
					RewriteCond %{REQUEST_FILENAME} !-d
					RewriteRule . ${folder}index.php [L]
					RewriteCond %{REQUEST_METHOD} POST
					RewriteCond %{REQUEST_URI} ^${folder}wp-admin/
					RewriteCond %{QUERY_STRING} action=up_asset_upload
					RewriteRule (.*) ${folder}index.php?ajax=query&method=message_upload [L,R=307]
			</IfModule>
		EOF;
	   return $rules;
	}
	add_filter('mod_rewrite_rules', 'add_custom_htaccess');