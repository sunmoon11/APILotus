<?php
// Replace path_to_sdk_inclusion with the path to the SDK as described in 
// http://docs.aws.amazon.com/aws-sdk-php/v3/guide/getting-started/basic-usage.html
define('REQUIRED_FILE','../../lib/aws_v3/aws-autoloader.php');

// Replace us-west-2 with the AWS region you're using for Amazon SES.
define('REGION','us-west-2');

require_once REQUIRED_FILE;
use Aws\Ses\SesClient;
use Aws\Sns\SnsClient;

class AlertMethodApi {
    protected static $instance;

    public static function getInstance() {

        if( is_null( static::$instance ) ) {

            static::$instance = new AlertMethodApi();

        }

        return static::$instance;
    }
    protected function __construct() {

    }

    private function __clone() {

    }

    private function __wakeup() {

    }
    public function sendEmail($sender, $recipients, $subject, $body)
    {
        // $body = $this->convertToHtml($body);
        if ($recipients == array())
            return;
        $client = SesClient::factory(array(
            'version'=> '2010-12-01',
            'region' => REGION ,
            'credentials' => array(
                'key'       => 'AKIAIOJAJ6TQCC6HKIFQ',
                'secret'    => '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')
        ));
        $ret = array();
        foreach ($recipients as $recipient)
        {
            $request = array();
            $request['Source'] = $sender;
            $request['Destination']['ToAddresses'] = array($recipient);
            $request['Message']['Subject']['Data'] = $subject;
            $request['Message']['Body']['Html']['Data'] = $body;

            try {
                $result = $client->sendEmail($request);
                $messageId = $result->get('MessageId');
                $ret[] = "Email sent! Message ID: $messageId";

            } catch (Exception $e) {
                $ret[] = "The email was not sent. Error message: ";
                // echo($e->getMessage()."\n");
            }
        }
        return $ret;
    }
    public function sendSMS($phoneNumbers, $message)
    {
        $client = SnsClient::factory(array(
            'version'=> '2010-03-31',
            'region' => REGION ,
            'credentials' => array(
                'key'       => 'AKIAIOJAJ6TQCC6HKIFQ',
                'secret'    => '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')
        ));

        $msgattributes = [
            'AWS.SNS.SMS.SenderID' => [
                'DataType' => 'String',
                'StringValue' => 'APILotus',
            ],
            'AWS.SNS.SMS.SMSType' => [
                'DataType' => 'String',
                'StringValue' => 'Promotional',
            ]
        ];
        $ret = array();
        foreach ($phoneNumbers as $phoneNumber)
        {
            $payload = array(
                'Message' => $message,
                'PhoneNumber' => '+'.$phoneNumber,
                'MessageAttributes' => $msgattributes
            );
            $ret[] = $client->publish($payload);
        }

        return $ret;
    }
    public function convertToHtml($content)
    {
        $htmlText = '<html><body>';
        if($content['fromDate'] == '' || $content['toDate'] == '')
            $htmlText = $htmlText.'There is no data !';
        else
            $htmlText = $htmlText.'Date Range : '.$content['fromDate'].' to '.$content['toDate'].'<br /><br />';
        foreach ($content['status'] as $data)
        {
            if($data[3] == 1)
                $htmlText = $htmlText.'['.$data[0].'] Step1 :'.$data[1].' ('.$data[2].')'.'<br />';
            else if($data[3] == 2)
                $htmlText = $htmlText.'['.$data[0].'] Step2 :'.$data[1].' ('.$data[2].')'.'<br />';

            if($data[3] == 7 || $data[3] == 8)
                $htmlText = $htmlText.'['.$data[0].'] '.$data[2].' Step1 Sales :'.$data[1].' ('.$data[4].')'.'<br />';

        }
        $htmlText = $htmlText.'</body></html>';

        return $htmlText;
    }
    public function convertToText($content, $type)
    {
        $text = '';

        if($type == 1 || $type == 2)
            $text = 'Rebill Report'."\r\n";
        if($type == 7 || $type == 8)
            $text = 'Step1 Sales Away From Cap Alert'."\r\n";
        if($type == 9)
            $text = 'Take Rate Alert'."\r\n";
        if($type == 10)
            $text = 'Tablet Take Rate Alert'."\r\n";

        if($content['fromDate'] == '' || $content['toDate'] == '')
            $text = $text.'There is no data !';
        else
            $text = $text.'Date Range : '.$content['fromDate'].' to '.$content['toDate']."\r\n";

        foreach ($content['status'] as $data)
        {
            if($data[3] == 1)
                $text = $text.'['.$data[0].'] Step1 :'.$data[1].' ('.$data[2].')'."\r\n";
            else if($data[3] == 2)
                $text = $text.'['.$data[0].'] Step2 :'.$data[1].' ('.$data[2].')'."\r\n";

            if($data[3] == 7 || $data[3] == 8)
                $text = $text.'['.$data[0].'] '.$data[2].' Step1 Sales :'.$data[1].' ('.$data[4].')'."\r\n";
            if($data[3] == 9)
                $text = $text.'['.$data[0].'] Take Rate :'.$data[1].' ('.$data[2].')'."\r\n";
            if($data[3] == 10)
                $text = $text.'['.$data[0].'] Tablet Take Rate :'.$data[1].' ('.$data[2].')'."\r\n";
        }

        return $text;
    }
}
?>
