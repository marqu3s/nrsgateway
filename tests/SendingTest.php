<?php
/**
 * Created by PhpStorm.
 * User: joao
 * Date: 29/07/16
 * Time: 12:23
 */
namespace tests;

use marqu3s\nrsgateway\SMSService;
use PHPUnit\Framework\TestCase;

/**
 * Class SendingTest
 * @package tests
 */
class SendingTest extends TestCase
{
    /**
     * USE YOUR REAL CREDENTIALS HERE TO TEST THE AUTHENTICATION.
     * WARNING: This test will send at least one real text message to the phone number specified in @see $realPhoneNumber.
     */

    /**
     * @var string $username The username to acess the NRS Gateway services.
     */
    private $username = '';

    /**
     * @var string $password The password to acess the NRS Gateway services.
     */
    private $password = '';

    /**
     * @var string $realPhoneNumber A real phone number to test the receiving of a text message.
     */
    private $realPhoneNumber = '';


    /**
     * Input data for the tests.
     * @return array
     */
    public function inputDataSendingTests()
    {
        return [
            ['miuser',        'mipass',        $this->realPhoneNumber, 'Test message.', 'PHPUnit', 401],
            [$this->username, $this->password, '',                     'Test message.', 'PHPUnit', 400],
            [$this->username, $this->password, $this->realPhoneNumber, '',              'PHPUnit', 400],
            [$this->username, $this->password, $this->realPhoneNumber, 'Test message.', '',        400],
            [$this->username, $this->password, $this->realPhoneNumber, 'Test message.', 'PHPUnit', 202],
        ];
    }

    /**
     * WARNING: This test will send at least one real text message to the phone number specified in @see $realPhoneNumber.
     * @dataProvider inputDataSendingTests
     */
    public function testSending($username, $password, $phoneNumber, $message, $from, $expectedResult)
    {
        $nrs = new SMSService($username, $password);
        $nrs->to = [$phoneNumber];
        $nrs->from = $from;
        $nrs->msg = $message;
        $result = $nrs->send();

        # Since we are using only one recipient, the sender will have only one chunk, hence index 0 bellow.
        # Get the sending results associated with chunk 0.
        $result = $result[0]['result'];
        $this->assertEquals($expectedResult, $result['httpCode'], $result['body']);
    }
}
