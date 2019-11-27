<?php
require_once './vendor/autoload.php';

// Create the Transport
$transport = (new Swift_SmtpTransport('smtp.example.org', 25))
  ->setUsername('your username')
  ->setPassword('your password')
;

// Create the Mailer using your created Transport
$mailer = new Swift_Mailer($transport);

function test($receiver, $mesg){
  /**
   * send test mail to the given receiver email
   * INPUT: user email and mesg
   * OUTPUT: true-> email send; false-> email send fail
   */

  global $mailer;
  // Create a message
  $message = (new Swift_Message('<SUBJECT>'))
    ->setFrom(['<SENDER EMAIL>' => '<SENDER NAME>'])
    ->setTo(["$receiver", '<OTHER PEOPLE>' => '<OTHER PEOPLE NAME>'])
    ->setBody(" . $mesg . ");

  // Send the message
  $result = $mailer->send($message);
}