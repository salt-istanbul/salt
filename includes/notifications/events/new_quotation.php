<?php

$array = array(
   "post" => array(),
   "user" => array(),
   "transmit" => array(
        "sender" => "{{administrator}}",
        "recipient" => "{{author}}"
   ),
   "carriers" => array(
   		"notification" => "{{data.user.display_name}} has been sent a Quotation for <a href='{{data.post.link}}'>{{data.post.title}}</a>",
         "email" => array(
            "type" => "Bcc",
            "subject" => "New Quotation for {{data.post.title}}",
            "body"    => "Hello,<br>{{data.user.display_name}} has been sent a Quotation for <a href='{{data.post.link}}'>{{data.post.title}}</a>"
         )
   )
);