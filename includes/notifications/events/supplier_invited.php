<?php

$array = array(
   "post" => array(),
   "transmit" => array(
      "sender" => "{{administrator}}",
      "recipient" => "{{users}}"
   ),
   "carriers" => array(
   		"notification" => "You are invited to <a href='{{data.post.link}}'>{{data.post.title}}</a>",
   		"email" => array(
              "type" => "Bcc",
   		     "subject" => "You are Inited to Project",
   		     "body" => "Hello,<br>You are invited to <a href='{{data.post.link}}'>{{data.post.title}}</a>"
   		)
   )
);