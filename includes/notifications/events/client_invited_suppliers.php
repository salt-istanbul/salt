<?php

$array = array(
   "post" => array(),
   "transmit" => array(
      "sender" => "{{administrator}}",
      "recipient" => "{{me}}"
   ),
   "carriers" => array(
   		"notification" => "You are invited {{data.users|length}} suppliers to <a href='{{data.post.link}}'>{{data.post.title}}</a>",
   		"email" => array(
              "type" => "",
   		     "subject" => "Invited Suppliers",
   		     "body" => "Hello,<br>You are invited {{data.users|length}} suppliers to <a href='{{data.post.link}}'>{{data.post.title}}</a>"
   		)
   )
);