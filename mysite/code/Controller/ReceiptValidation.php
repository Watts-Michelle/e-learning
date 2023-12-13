<?php

class ReceiptValidation  {

	public $SANDBOX_URL = 'https://sandbox.itunes.apple.com/verifyReceipt';

	public $PRODUCTION_URL = 'https://buy.itunes.apple.com/verifyReceipt';

	protected $receipt;

	protected $endpoint;

	public function __construct($endpoint, $receipt = NULL)
	{
		$this->setEndPoint($endpoint);

		if ($receipt) {
			$this->setReceipt($receipt);
		}
	}

	public function getReceipt()
	{
		return $this->receipt;
	}

	public function setReceipt($receipt) {

		if (strpos($receipt, '{') !== false) {

			$this->receipt = base64_encode($receipt);

		} else {

			$this->receipt = $receipt;
		}
	}

	public function getEndpoint()
	{
		return $this->endpoint;
	}

	public function setEndPoint($endpoint)
	{
		$this->endpoint = $endpoint;
	}

	public function validateReceipt()
	{
		$response = $this->makeRequest();

		$decoded_response = $this->decodeResponse($response);

		if (!isset($decoded_response->status) || $decoded_response->status != 0) {
			throw new Exception('Invalid receipt. Status code: ' . (!empty($decoded_response->status) ? $decoded_response->status : 'N/A'));
		}

		if (!is_object($decoded_response)) {
			throw new Exception('Invalid response data');
		}

		return $decoded_response;
	}

	// TO DO: Need a new password when the itunes is setup.
	private function encodeRequest()
	{
		return json_encode(array('receipt-data' => $this->getReceipt(), 'password' => AppSharedSecret));
	}

	private function decodeResponse($response)
	{
		return json_decode($response);
	}

	private function makeRequest()
	{
		$ch = curl_init($this->endpoint);

		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->encodeRequest());

		$response = curl_exec($ch);
		$errno    = curl_errno($ch);
		$errmsg   = curl_error($ch);

		curl_close($ch);

		if ($errno != 0) {
			throw new Exception($errmsg, $errno);
		}

		return $response;
	}
}