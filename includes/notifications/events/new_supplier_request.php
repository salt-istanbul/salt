<?php

$array = array(
   "user" => array(),
   "transmit" => array(
      "sender" => "{{me}}",
      "recipient" => "{{administrator}}"
   ),
   "carriers" => array(
   		"notification" => "{{data.user.display_name}} wants to be a supplier.",
   		"email" => array(
              "type" => "Bcc",
   		     "subject" => "{{data.user.display_name}} wants to be a supplier",
   		     "body" => "Hello,<br>{{data.user.display_name}} wants to be a supplier."
   		)
   )
);