KSms
====

Library for sending free sms messages

**Uses swiftmailer to send emails to carrier addresses (e.g., number@tmomail.net)**

## Usage

```php
require_once "KSms.php";

// set up parameters
$ksms = new KSms();

// uncomment if you need to use smtp
/*
$ksms->transportType = "smtp";
$ksms->transportOptions = array(
	"host"       => "smtp.gmail.com",
	"username"   => "xxx@gmail.com", // or email@googleappsdomain.com
	"password"   => "yyy",
	"port"       => "465",
	"encryption" => "ssl",
);
*/

// send email
$carrier = "T-Mobile"; // see valid carriers in KSms::getCarriers()
$number = "0123456789";
$subject = "Subject";
$message = "Message";
$ksms->send($carrier, $number, $subject, $message);
```
