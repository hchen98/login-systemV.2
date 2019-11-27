<?php

//include_once ("./socketlabs-php/InjectionApi/src/includes.php");
// change the vender directory in this project, but please feel free to change it

include_once ("./vendor/socketlabs/email-delivery/InjectionApi/src/includes.php");
//or if using composer: include_once ('./vendor/autoload.php');
use Socketlabs\SocketLabsClient;
use Socketlabs\Message\BasicMessage;
use Socketlabs\Message\EmailAddress;

$serverId = "<YOUR SOCKETLABS ID>";
$injectionApiKey = "<YOUR API KEY>";

function test($receiver, $mesg){
  /**
   * a test func to send message to the given email
   * INPUT: receiver email and message
   * OUTPUT: true-> email is sent without any error; false-> there's an error
   * NOTE: this method should send email without any error, if there's an error, please check your server's setting/ php/ apache configuration file
  */
  global $serverId;
  global $injectionApiKey;

  $client = new SocketLabsClient($serverId, $injectionApiKey);

  $message = new BasicMessage();

  $message->subject = "Sending A Basic Message";
  $message->htmlBody = "$mesg";
  $message->plainTextBody = "This is the Plain Text Body of my message.";

  $message->from = new EmailAddress("<SENDER>");
  $message->addToAddress("$receiver");


  if ($client->send($message))
    return true;
  else
    return false;

}