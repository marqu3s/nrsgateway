<?php
/**
 * Created by PhpStorm.
 * User: joao
 * Date: 29/07/16
 * Time: 12:29
 */
namespace marqu3s\nrsgateway;

use yii\helpers\VarDumper;
use Yii;

/**
 * Class SMSService
 *
 * Allows to use the SMS API.
 * Sample JSON: {"to":["34666555444"],"text":"Hi! This is a test message.","from":"John Smith"}
 *
 * @package marqu3s\nrsgateway
 */
class SMSService extends NRSGateway
{
    const ENDPOINT = 'https://dashboard.360nrs.com/api/rest/sms';
    const CHAR_LIMIT_GSM = 160;
    const CHAR_LIMIT_UTF16 = 70;
    const TO_LIMIT_PER_REQUEST = 500;

    /** @var string $msg The message to be sent. Maximum of 160 chars for GSM and 70 for UTF16. */
    public $msg = '';

    /** @var array $to The numbers that will receive the message same message. */
    public $to = [];

    /** @var string $from The sender. Max of 15 digits or 11 characters. */
    public $from = '';

    /** @var string OPTIONAL - The coding. Allowed values are: 'gsm' or 'utf-16' */
    public $coding = 'gsm';

    /** @var string OPTIONAL - The date to send the message. Format: 'YYYYmmddHHiiss' */
    public $fSend = '';

    /** @var int OPTIONAL - Maximum parts to split the message. */
    public $parts = 1;

    /** @var bool OPTIONAL - If true the server will replace accented chars automatically. */
    public $trsec = true;

    /** @var string Campain name. */
    public $campaignName;


    /**
     * Splits the the $to list in chunks of self::TO_LIMIT_PER_REQUEST.
     * Send the message to every element in all chunks.
     * @return array|boolean
     */
    public function send()
    {
        # Check it $this->to is an array.
        if (!is_array($this->to)) {
            return [
                0 => [
                    'result' => [
                        'httpCode' => 400,
                        'header' => '',
                        'body' => '{"error":{"code":101,"description":"$to must be an array"}}'
                    ]
                ]
            ];
        }

        # Check if there are any recipients.
        if (count($this->to) === 0) {
            return [
                0 => [
                     'result' => [
                         'httpCode' => 400,
                         'header' => '',
                         'body' => '{"error":{"code":102,"description":"No valid recipients"}}'
                     ]
                ]
            ];
        }

        # Check if there is a sender.
        if (strlen($this->from) === 0) {
            return [
                0 => [
                    'result' => [
                        'httpCode' => 400,
                        'header' => '',
                        'body' => '{"error":{"code":106,"description":"Sender missing"}}'
                    ]
                ]
            ];
        }

        # Check if there is a message.
        if (strlen($this->msg) === 0) {
            return [
                0 => [
                    'result' => [
                        'httpCode' => 400,
                        'header' => '',
                        'body' => '{"error":{"code":104,"description":"Text message missing"}}'
                    ]
                ]
            ];
        }

        # Send the messages in chunks.
        $arrChunk = array_chunk($this->to, self::TO_LIMIT_PER_REQUEST);
        foreach ($arrChunk as $i => $to) {
            $data = [
                'to' => $to,
                'message' => $this->msg,
                'from' => $this->from,
            ];

            if (!empty($this->campaignName)) {
                $data['campaignName'] = $this->campaignName;
            }

            $arrChunk[$i]['result'] = $this->doSend($data);
        }

        return $arrChunk;
    }


    private function doSend($data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::ENDPOINT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type:application/json", $this->base64AuthHeader
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        curl_close($ch);

        $result = [
            'httpCode' => $httpCode,
            'header' => $header,
            'body' => $body
        ];

        if (YII_DEBUG) {
            Yii::info('SMS SENT TO: ' . VarDumper::dumpAsString($data['to']), __METHOD__);
            Yii::info('API RESPONSE: ' . VarDumper::dumpAsString($result), __METHOD__);
        }

        return $result;
    }
}
