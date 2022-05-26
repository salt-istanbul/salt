<?php

$array = array(
   "post" => array(),
   "user" => array(),
   "transmit" => array(
        "sender" => "{{administrator}}",
        "recipient" => "{{user}}"
   ),
   "carriers" => array(
   		"notification" => "You sent a quotation for <a href='{{data.post.link}}'>{{data.post.title}}</a>",
   )
);