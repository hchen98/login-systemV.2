<?php

/**
 * This is none Symfony Swift Mailer method.
 * Please note, some of the shared hostings are not allowed to use Swift Mailer method to send email(s)...e.g., GoDaddy
 * This file will use mail() method instead
 */

// use 'ob_start()' if you need to output something before the function 'header()', else the code will not work.
//ob_start();

ini_set('display_errors', 1);
error_reporting(E_ALL | E_STRICT);

require_once 'vendor/autoload.php';
require_once 'config/constants.php';

function test($userEmail, $mesg){
  /**
   * send test mail to the given receiver email
   * INPUT: user email and mesg
   * OUTPUT: true-> email send; false-> email send fail
   */

  $subject = 'XYZ - Verify your email';
  $from = ''; //your email address, not the user's email address!g

  $header = 'MIME-Version: 1.0' . "\r\n";
  $header .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
  $header .= 'From: ' . $from . "\r\n" .
    'Reply-To: ' . $from . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

  $body = '<!DOCTYPE html>';
  $body .= '<html lang="en">';
  $body .= '<head>';
  $body .= '<meta charset="UTF-8">';
  $body .= '<title>Title</title>';
  $body .= '</head>';
  $body .= '<body>';
  $body .= '<div class="wrapper">';
  $body .= '<p>';
  $body .= "$mesg";
  $body .= '</p>';
  $body .= '</div>';
  $body .= '</body>';
  $body .= '</html>';


  if (mail($userEmail, $subject, $body, $header))
    return true;
  else
    return false;

}


// make sure to close 'ob' whenever you are using it.
//ob_clean();
?>
