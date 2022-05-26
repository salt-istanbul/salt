<?php

$array = array(
   "user" => array(),
   "transmit" => array(
      "sender" => "{{administrator}}",
      "recipient" => "{{me}}"
   ),
   "carriers" => array(
   		"notification" => "You requested to be a supplier.",
   		"email" => array(
              "type" => "Bcc",
   		     "subject" => "About your supplier request",
   		     "body" => "Hello {{data.user.display_name}},<br> your supplier request has been sent. We'll notify you when your upgrade is completed."
   		)
   )
);