<?php

/**
 * KSms - Simple extension to send text messages to people using email
 * @author amnah
 * @link https://github.com/amnah
 */
class KSms {

    /**
     * Transport for mailing
     * Valid choices: "php" or "smtp"
     * @var string
     */
    public $transportType = "smtp";

    /**
     * Transport options for swift mailer transport (used only for smtp)
     * @var array
     */
    public $transportOptions = array(
        "host"       => "smtp.gmail.com",
        "username"   => "xxx@gmail.com", // or email@googleappsdomain.com
        "password"   => "yyy",
        "port"       => "465",
        "encryption" => "ssl",
    );

    /**
     * Variable to separate prefix and suffix in the carriers email address
     * Format:
     *      {prefix}{number}{suffix}
     * You'll need to search online to get the proper email addresses
     * The prefix is for the rare weird ones, like the "+48" in this:
     *      Polkomtel       Poland      +48number@text.plusgsm.pl
     * Would be:
     *      "Polkomtel" => "+48//text.plusgsm.pl",
     * @link http://en.wikipedia.org/wiki/List_of_SMS_gateways
     * @link http://www.tech-recipes.com/rx/939/sms_email_cingular_nextel_sprint_tmobile_verizon_virgin/
     * @link http://www.tech-recipes.com/rx/2819/sms_email_us_cellular_suncom_powertel_att_alltel/
     * @var string
     */
    public $prefixSuffixSeparator = "//";

    /**
     * Split length. This is to handle sms limits (160 characters)
     * (Be sure to leave some padding so we can add in a counter)
     *
     * Note: Depending on the type of phone the user has, you may not need to split messages
     *       Thus, the split variable is false by default
     *       Better yet, just keep the damn text message short
     * @var int
     */
    public $splitLength = 150;


    // --------------------------------- end configuration -------------------------------- //

    /**
     * Swiftmailer object
     * @var Swift_Mailer
     */
    protected $_mailer;

    /**
     * Get list of carriers (Check wikipedia for a more complete list)
     * Use $this->prefixSuffixSeparator if needed, though typically you won't need it
     * @return array
     */
    public static function getCarriers() {
        return array(
            "AT&T" => "txt.att.net",
            "Boost Mobile" => "myboostmobile.com",
            "Cingular" => "cingularme.com",
            "Metro PCS" => "mymetropcs.com",
            "Nextel" => "messaging.nextel.com",
            "Sprint" => "messaging.sprintpcs.com",
            "T-Mobile" => "tmomail.net",
            "Verizon" => "vtext.com",
            "Virgin Mobile" => "vmobl.com",

            "Polkomtel" => "+48//text.plusgsm.pl", // example of prefix suffix separator
        );
    }

    /**
     * Sends text message
     * @param string $carrier
     * @param string $phoneNumber
     * @param string $subject
     * @param string $message
     * @param bool $split
     * @return int The number of text messages sent
     */
    public function send($carrier, $phoneNumber, $subject, $message, $split = false) {

        // check for valid carrier
        $carriers = $this->getCarriers();
        if (empty($carriers[$carrier])) {
            return 0;
        }

        // clean up the phone number, removing dashes and spaces
        $phoneNumber = str_replace(array(" ", "-"), "", $phoneNumber);

        // calculate prefix and suffix. if separator isn't in there, then it's just the suffix
        list($prefix, $suffix) = strpos($carriers[$carrier], $this->prefixSuffixSeparator) !== false
            ? explode($this->prefixSuffixSeparator, $carriers[$carrier])
            : array("", $carriers[$carrier]);

        // calculate to address
        $to = "{$prefix}{$phoneNumber}@{$suffix}";

        // calculate messages. this will typically only result in an array of count 1
        $messages = $split
            ? $this->splitMessage($message, $this->splitLength)
            : array($message);

        // send emails
        $successCount = 0;
        foreach ($messages as $message) {
            $successCount += $this->_mail($to, $subject, $message);
        }

        return $successCount;
    }

    /**
     * Sends email in order to send text message
     * @param string $to
     * @param string $subject
     * @param string $message
     * @return int
     */
    protected function _mail($to, $subject, $message) {

        // set up swiftmailer if needed
        if (!$this->_mailer) {

            // include swiftmailer
            require_once dirname(__FILE__) . "/swift/swift_required.php";

            // set transport to php
            if (strtolower($this->transportType) == "php") {
                $transport = Swift_MailTransport::newInstance();
            }
            // set transport to smtp
            elseif (strtolower($this->transportType) == "smtp") {
                $transport = Swift_SmtpTransport::newInstance();

                // set options by calling method calls, ie setHost()
                foreach ($this->transportOptions as $option => $value) {
                    $methodName = "set" . ucfirst($option);
                    $transport->$methodName($value);
                }
            }

            // create mailer object
            $this->_mailer = Swift_Mailer::newInstance($transport);
        }

        // set up message and send
        $mailer = $this->_mailer;
        $message = Swift_Message::newInstance($subject)
            ->setFrom("not@used.com") // used only because of swiftmailer
            ->setTo($to)
            ->setBody($message);

        return $mailer->send($message);
    }

    /**
     * Split up messages and add counter at end
     * @param string $message
     * @param int $splitLength
     * @return array
     */
    public function splitMessage($message, $splitLength) {
        // split up
        $messages = $this->mb_wordwrap_array($message, $splitLength);

        // add counter to each message if needed
        $total = count($messages);
        if ($total > 1) {
            $count = 1;

            foreach ($messages as $key => $currentMessageWrapped) {
                $messages[$key] = "$currentMessageWrapped ($count/$total)";
                $count++;
            }
        }

        return $messages;
    }

    /**
     * Wordwrap with UTF-8 supports, returns as array.
     * @link http://us2.php.net/manual/en/function.wordwrap.php#104811
     * @param string $string
     * @param int $width
     * @return array
     */
    public function mb_wordwrap_array($string, $width) {
        if (($len = mb_strlen($string, 'UTF-8')) <= $width) {
            return array($string);
        }

        $return = array();
        $last_space = FALSE;
        $i = 0;

        do {
            if (mb_substr($string, $i, 1, 'UTF-8') == ' ') {
                $last_space = $i;
            }

            if ($i > $width) {
                $last_space = ($last_space == 0) ? $width : $last_space;

                $return[] = trim(mb_substr($string, 0, $last_space, 'UTF-8'));
                $string = mb_substr($string, $last_space, $len, 'UTF-8');
                $len = mb_strlen($string, 'UTF-8');
                $i = 0;
            }

            $i++;
        }
        while ($i < $len);

        $return[] = trim($string);

        return $return;
    }
}