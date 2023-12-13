<?php

class Subscription_Controller extends Base_Controller
{
	/** {@inheritdoc} */
	protected $auth = true;

	public $SANDBOX_URL = 'https://sandbox.itunes.apple.com/verifyReceipt';

	public $PRODUCTION_URL = 'https://buy.itunes.apple.com/verifyReceipt';

	/** {@inheritdoc} */
	private static $allowed_actions = array(
		'get',
		'validateIOS',
		'validateAndroid',
	);

	/** {@inheritdoc} */
	private static $url_handlers = array(
		'' => 'get',
		'validateIOS' => 'validateIOS',
		'validateAndroid' => 'validateAndroid',
	);

	public function get(SS_HTTPRequest $request)
	{
		$SubscriptionTypesData = ['subscription_types' => []];

		$SubscriptionTypes = SubscriptionType::get()->filter('Active', true)->Sort('Created ASC');

		foreach($SubscriptionTypes as $subscriptionType){

			if ($data = $subscriptionType->getBasic()) {

				$SubscriptionTypesData['subscription_types'][] = $data;
			}
		}

		return (new JsonApi)->formatReturn($SubscriptionTypesData);
	}

	public function validateIOS(SS_HTTPRequest $request)
	{
		$data = $this->getBody($request);

		$currentUser = CurrentUser::getUser();

		$receipt = isset($data['receipt']) ? $data['receipt'] : '' ;

		$endpoint = isset($data['sandbox']) && $data['sandbox'] == 'true' ? $this->SANDBOX_URL : $this->PRODUCTION_URL;

		try {
			$rv = new ReceiptValidation($endpoint, $receipt);

			$info = $rv->validateReceipt();

			$latestReceiptInfo = end($info->latest_receipt_info)->expires_date;

			$date = date('Y-m-d H:i:s', strtotime($latestReceiptInfo));

			$currentUser->SubscriptionExpirationDate = $date;
			$currentUser->write();

			$iOSSubscriptionReceipt = iOSSubscriptionReceipt::create();
			$iOSSubscriptionReceipt->Receipt = $receipt;
			$iOSSubscriptionReceipt->StudentID = $currentUser->ID;
			$iOSSubscriptionReceipt->write();

			return (new JsonApi)->formatReturn(['expires_date' => $date]);

		} catch (Exception $ex) {

			return $this->handleError(2009, $ex->getMessage());
		}
	}

	public function validateandroid(SS_HTTPRequest $request)
	{
		$data = $this->getBody($request);

		$currentUser = CurrentUser::getUser();

		$Subscription = SubscriptionPurchaseTest::create();

		if (isset($data['orderId'])) {
			$Subscription->OrderId = $data['orderId'];
		}

		if (isset($data['packageName'])) {
			$Subscription->PackageName = $data['packageName'];
		}

		if (isset($data['productId'])) {
			$Subscription->ProductId = $data['productId'];
		}

		if (isset($data['purchaseTime'])) {
			$Subscription->PurchaseTime = $data['purchaseTime'];
		}

		if (isset($data['purchaseState'])) {
			$Subscription->PurchaseState = $data['purchaseState'];
		}

		if (isset($data['developerPayload'])) {
			$Subscription->DeveloperPayload = $data['developerPayload'];
		}

		if (isset($data['purchaseToken'])) {
			$Subscription->PurchaseToken = $data['purchaseToken'];
		}

		if (isset($data['autoRenewing'])) {
			$Subscription->AutoRenewing = $data['autoRenewing'];
		}

		$currentUser->SubscriptionPurchaseTests()->add($Subscription);

		$currentUser->write();

		return (new JsonApi)->formatReturn(['test' => $Subscription->PurchaseToken]);
	}
}