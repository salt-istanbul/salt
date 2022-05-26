<?php

$array = array(
   "post" => array(),
   "transmit" => array(
        "sender" => "{{administrator}}",
        "recipient" => "{{me}}"
   ),
   "carriers" => array(
   		"notification" => "You are created a new project <a href='{{data.post.link}}'>{{data.post.title}}</a>",
         "email" => array(
            "type" => "Bcc",
            "subject" => "New Project {{data.post.title|html_entity_decode}}",
            "body"    => "template"//"You are created a new project <a href='{{data.post.link}}'>{{data.post.title}}</a>"
         )
   )
);