<?php

$array = array(
   "user" => array(),
   "transmit" => array(
      "sender" => "{{administrator}}",
      "recipient" => "{{users}}"
   ),
   "carriers" => array(
   		"notification" => "Your profile upgrade request declined",
   		"email" => array(
              "type" => "Bcc",
   		     "subject" => "Your profile upgrade request declined",
   		     "body" => "Hello,<br>Your profile upgrade request declined."
   		)
   )
);