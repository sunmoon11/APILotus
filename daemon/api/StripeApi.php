<?php
//$INC_DIR = $_SERVER["DOCUMENT_ROOT"]."/lib/";
$INC_DIR = $_SERVER["DOCUMENT_ROOT"]."/lib/";

require_once $INC_DIR.'stripe4/init.php';
class StripeApi
{
	protected static $instance;

	// test api key
	private static $PUBLISH_KEY = "pk_test_kyxgtxD5BcPyVARUJbiRgQCN";
	private static $SECRET_KEY = "sk_test_urqGJEwsK9T6FVUYjjGrdgDz";

	// live api key
	//private $PUBLISH_KEY = "pk_live_y00zEB5AHQZO1u9ln9Sv3Kdg";
	//private $SECRET_KEY = "sk_live_TxFKaBs7wyKYswNesAbVNwqL";

	private $BASIC_PLAN_ID = 'basic_plan';


	public static function getInstance() 
	{
		\Stripe\Stripe::setApiKey(static::$SECRET_KEY);
		
		if (is_null(static::$instance))
			static::$instance = new StripeApi();

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

    public function createCustomer($email, $tokenID)
    {
		$customer = \Stripe\Customer::create(array(
			'email' => $email,
		  	'description' => 'Customer for '.$email.' on APILotus',
		  	'source' => $tokenID
		));

		return $customer->id;
    }

	public function createSubscription($customerID)
    {
		$subscription = \Stripe\Subscription::create(array(
			'customer' => $customerID,
			'plan' => $this->BASIC_PLAN_ID
		));

		return $subscription->id;
    }

    public function createCard($customerID, $tokenID)
    {
		$customer = \Stripe\Customer::retrieve($customerID);
		$card = $customer->sources->create(array('source' => $tokenID));

		return $card->id;
    }

    public function retrieveCard($customerID, $cardID)
    {
		$customer = \Stripe\Customer::retrieve($customerID);
		$card = $customer->sources->retrieve($cardID);

		return $card;
    }

    public function updateCard($customerID, $cardID, $expiryMonth, $expiryYear)
    {
    	$customer = \Stripe\Customer::retrieve($customerID);
		$card = $customer->sources->retrieve($cardID);
		$card->exp_month = $expiryMonth;
		$card->exp_year = $expiryYear;
		$card->save();
    }

    public function deleteCard($customerID, $cardID)
    {
    	$customer = \Stripe\Customer::retrieve($customerID);
		$card = $customer->sources->retrieve($cardID)->delete();

		return $card->deleted;
    }

    public function retrieveCustomer($customerID)
    {
		$customer = \Stripe\Customer::retrieve($customerID);
		
		return $customer;
    }

    public function retrieveSubscription($subscriptionID)
    {
		$subscription = \Stripe\Subscription::retrieve($subscriptionID);

		return $subscription;
    }

	public function cancelSubscription($subscriptionID)
    {
		$subscription = \Stripe\Subscription::retrieve($subscriptionID);
		$subscription->cancel();
    }

    public function listInvoices($customerID, $subscriptionID, $limit)
    {
    	$invoices = \Stripe\Invoice::all(array(
    		'customer' => $customerID,
    		'subscription' => $subscriptionID,
    		'limit' => $limit
    	));

    	return $invoices;
    }

    public function retrievePlan()
    {
		$plan = \Stripe\Plan::retrieve($this->BASIC_PLAN_ID);
		
		return $plan;
    }
}


?>