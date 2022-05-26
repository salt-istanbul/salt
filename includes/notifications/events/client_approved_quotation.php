<?php

$array = array(
   "post" => array(),
   "transmit" => array(
      "sender" => "{{administrator}}",
      "recipient" => "{{me}}"
   ),
   "carriers" => array(
   		"notification" => "You approved <a href='{{data.user.link}}'>{{data.user.get_title}}</a>'s quotation for <a href='{{data.post.link}}'>{{data.post.title}}</a>",
   		"email" => array(
              "type" => "",
   		     "subject" => "[Approved Quotation] {{data.post.title}}",
   		     "body" => "Hello,<br>You approved <a href='{{data.user.link}}'>{{data.user.get_title}}</a>'s quotation for <a href='{{data.post.link}}'>{{data.post.title}}</a>"
   		)
   )
);