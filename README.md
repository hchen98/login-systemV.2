# Login System (V.2)
A complete PHP login system integrate with PhpMyAdmin and with various email APIs such as PHPMailer, SocketLabs, Mailer, and Laravel Swiftmailer
<br><br>
<b>Note</b>: PHPMailer and SocketLabs work fine on GoDaddy server, but not Laravel Swiftmailer


## Get start
- (Choose the one you want to use) 

- install PHPMailer:    ``` composer require phpmailer/phpmailer ```   
- install SocketLabs:   ```composer require socketlabs/email-delivery```
- install Laravel Swiftmailer:     ```composer require "swiftmailer/swiftmailer:^6.0"```

## Usage
1. Send email verification via Symfony Swift Mail:
    * edit your email credentials in /config/constant.php
    * change `require_once 'PHPMailer.php';` to `require_once 'SwiftMailer.php';` or `require_once(SocketLabs.php);` or `require_once(Mailer.php);`
    * change your SMTP server url (the first parameter) `Swift_SmtpTransport('smtp.gmail.com', 465, 'ssl'))`
2. Send email via PHP `mail()`:
   * no changes need


## Note
- `Mailer.php` may not work in some of GoDaddy's server and it is very slow
- SocketLabs is instance email delivery
- PHPMailer maximum delay 1 minute and 15 seconds, but unable to work on computer's localhost
- Laravel Swiftmailer unable work on GoDaddy's server, but works perfectly on computer's localhost
