Ksms
====

Library for sending free sms messages (using carrier email addresses). 

**Uses swiftmailer for sending emails.**

== Usage

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
$ksms->send($carrier, $number, "subject1", "message1");
```