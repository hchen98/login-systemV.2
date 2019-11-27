<?php

// all necessary things for the PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

function test($receiver_email, $mesg)
{
  /**
   * send test mail to the given receiver email
   * INPUT: user email and mesg
   * OUTPUT: true-> email send; false-> email send fail
   */

  global $mail;

  try {
    $mail->IsSMTP(); // use SMTP.
    $mail->SMTPDebug = 0;
    $mail->SMTPAuth = false; // disable SMTP authentication.
    $mail->Host = "localhost"; // GoDaddy support said to use localhost
    $mail->Port = 25;   // use port 25 in GoDaddy's hosting server
    $mail->SMTPSecure = 'none';   // this will still be STMP secure even though we set none

    $mail->SMTPOptions = array(
      'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
      )
    );

    $mail->AddAddress($receiver_email);
    $mail->SetFrom('<YOUR EMAIL>', '<YOUR NAME>');    // name is optional
    $mail->Subject = '<YOUR SUBJECT>';
    $mail->MsgHTML("
    <!DOCTYPE html>
      <html lang=\"en\">
        <head>
          <meta charset=\"UTF-8\">
          <title>Verify email</title>
        </head>
        <body>
        <div class=\"wrapper\">
          <p>
          ' . $mesg . '
          </p>
        </div>
      </body>
    </html>
    
    ");

    if ($mail->Send())
      return true;
    else
      return false;

  } catch (Exception $e) {
    return false;
    // $mail->ErrorInfo
  }
}
