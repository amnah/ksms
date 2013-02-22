<?php

/**
 * KSmsTest - Unit test for KSms
 * @author amnah
 * @link https://github.com/amnah
 */
class KSmsTest extends PHPUnit_Framework_TestCase{

    /**
     * Set up before class - include the class file
     * @static
     *
     */
    public static function setUpBeforeClass() {
        require_once dirname(__FILE__) . "/KSms.php";
    }

    /**
     * Test carriers
     * @test
     */
    public function testCarriers() {

        // get carriers
        $ksms = new KSms();
        $carriers = $ksms->getCarriers();
        $this->assertTrue(is_array($carriers));
        $this->assertArrayHasKey("AT&T", $carriers);
        $this->assertArrayHasKey("Sprint", $carriers);
        $this->assertArrayHasKey("T-Mobile", $carriers);
        $this->assertArrayHasKey("Verizon", $carriers);
    }

    public function testSplit() {

        $message = "abcdabcdabcdabcd";

        $ksms = new KSms();
        $messages = $ksms->splitMessage($message, 4);
        $this->assertGreaterThan(2, count($messages));
    }

    public function testSend() {

        // setup test numbers
        $testNumbers = array(
            array("T-Mobile", "0123456789"),
        );

        // test numbers
        $ksms = new KSms();
        foreach ($testNumbers as $testNumber) {
            $carrier = $testNumber[0];
            $number = $testNumber[1];

            $result = $ksms->send($carrier, $number, "subject1", "message1");
            $this->assertGreaterThan(0, $result);
        }

        // test invalid carrier
        $result = $ksms->send("random", "123456", "subject2", "message2 - supposed to fail");
        $this->assertEquals(0, $result);

        // test long message
        $message = "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.";

        // test split messages, should be 4
        $to = reset($testNumbers);
        $result = $ksms->send($to[0], $to[1], "Split4", $message, true);
        $this->assertGreaterThan(3, $result);

        // test long message without split
        $result = $ksms->send($to[0], $to[1], "Full message", $message);
        $this->assertEquals(1, $result);
    }
}
