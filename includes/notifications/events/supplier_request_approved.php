<?php

$array = array(
   "user" => array(),
   "transmit" => array(
      "sender" => "{{administrator}}",
      "recipient" => "{{user}}"
   ),
   "carriers" => array(
   		"notification" => "Your profile upgrade request accepted",
   		"email" => array(
              "type" => "Bcc",
   		     "subject" => "Your account upgraded to Supplier",
   		     "body" => "Hello,<br>Your profile upgrade request accepted"
   		)
   )
);