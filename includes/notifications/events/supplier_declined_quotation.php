<?php

$array = array(
   "post" => array(),
   "transmit" => array(
      "sender" => "{{administrator}}",
      "recipient" => "{{users}}"
   ),
   "carriers" => array(
   		"notification" => "Your quotation for <a href='{{data.post.link}}'>{{data.post.title}}</a> is declined.",
   		"email" => array(
              "type" => "Bcc",
   		     "subject" => "Your quotation is declined",
   		     "body" => "Hello,<br>Your quotation for <a href='{{data.post.link}}'>{{data.post.title}}</a> is declined."
   		)
   )
);