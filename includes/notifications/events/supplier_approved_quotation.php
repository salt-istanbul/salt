<?php

$array = array(
   "post" => array(),
   "transmit" => array(
      "sender" => "{{administrator}}",
      "recipient" => "{{user}}"
   ),
   "carriers" => array(
   		"notification" => "Your quotation is approvedd for <a href='{{data.post.link}}'>{{data.post.title}}</a>",
   		"email" => array(
              "type" => "",
   		     "subject" => "Your Quotation is approved | {{data.post.title}}",
   		     "body" => "Your quotation is approvedd for <a href='{{data.post.link}}'>{{data.post.title}}</a>"
   		)
   )
);