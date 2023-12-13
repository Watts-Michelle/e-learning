<?php

class AndroidReceiptValidation  {

	protected $receipt;

	public function __construct($receipt = NULL)
	{
		if ($receipt) {
			$this->decodeReceipt($receipt);
		}
	}

	private function decodeReceipt($receipt)
	{
		$this->receipt = json_decode($receipt);
	}

	public function validateReceipt($purchaseToken, $packageName, $subscriptionId)
	{
		try {
			$client = new Google_Client();

			$client->setApplicationName("StudyTracks");


			$client->setAuthConfig(GOOGLE_SERVICE_KEY_JSON);

			$user_to_impersonate = 'georgestudytracks@gmail.com';
			$client->setSubject($user_to_impersonate);

			$service = new Google_Service_AndroidPublisher($client);

			// use the purchase token to make a call to Google to get the subscription info
			$subscription = $service->purchases_subscriptions->get($packageName, $subscriptionId, $purchaseToken);

		} catch (Google_Auth_Exception $e) {

			// if the call to Google fails, throw an exception
			throw new Exception('Error validating transaction', 500);
		}
	}
}