<?php
//define('REQUIRED_FILE','../aws_v3/aws-autoloader.php');
// Replace us-west-2 with the AWS region you're using for Amazon SES.
//define('REGION','us-west-2');                                                  

require_once '../../lib/aws_v3/aws-autoloader.php';
use Aws\Ses\SesClient;

class SignupApi
{
	protected static $instance;
	private $digitCount;
	public static function getInstance() 
	{
		if (is_null(static::$instance))
			static::$instance = new SignupApi();

		return static::$instance;
	}	
	protected function __construct() 
	{		
	}
	private function __clone() 
	{
	}
	private function __wakeup() 
	{		
	}
	private function generateVerifyCode()
	{		
		$code = mt_rand(1000, 9999);
		return $code;
	}
	public function sendVerifyMailTo($recipient, $verifyCode)
	{
//		$verifyCode = $this->generateVerifyCode();

		$sender = 'support@apilotus.com';
		$subject = "APILotus account verify code";
		$body = "<Html><body> Verify Code <br> <br> Please use the following verify code for the APILotus account.<br><br>".
                "Verify code:".$verifyCode."<br><br>"."If you didn`t request this code, you can safely ignore this email.".
                "<br><br> Thanks,<br>The APILotus team</body></Html>";
		

		$client = SesClient::factory(array(
	    	'version'=> '2010-12-01',    
	    	'region' => 'us-west-2' ,
	    	'credentials' => array(
	    	'key'       => 'AKIAIOJAJ6TQCC6HKIFQ',
	    	'secret'    => '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')
		));
		

		
		$request = array();
		$request['Source'] = $sender;
		$request['Destination']['ToAddresses'] = array($recipient);
		$request['Message']['Subject']['Data'] = $subject;
		$request['Message']['Body']['Html']['Data'] = $body;

		try {
		     $result = $client->sendEmail($request);
		     print_r($result);
//		     $messageId = $result->get('MessageId');
		     return true;

		} catch (Exception $e) {

		    return false;
		}		
		
	}
}
?>