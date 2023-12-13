<?php

class PremiumNotification_Task extends Controller
{

	public function init() {

		parent::init();

		if (php_sapi_name() != "cli") {
			if (!Member::currentUser()) {

				return Security::PermissionFailure($this->controller, 'You must be logged in to access this page');
			}

			if (!Member::currentUser()->inGroups(array('administrators'))) {
				return Security::PermissionFailure($this->controller, 'You must be logged in as admin to access this page');
			}
		}
	}

	public static $allowed_actions = array('index');

	public function index()
	{

		$settings = SiteConfig::current_site_config();

		if (! $pushMessage = $settings->PurchasePushText) {
			return [];
		}

		$orders = Order::get();
		$purchasingStudents = [];

		foreach ($orders as $order) {
			if (! in_array($order->StudentID, $purchasingStudents)) {
				$purchasingStudents[] = $order->StudentID;
			}
		}

		//users who have not purchased, are not part of a school and have not previously been chased
		$studentQuery = Student::get()->filter(['SchoolID' => 0, 'PurchaseChase' => 0])->filter('ID:not', $purchasingStudents)->filter('Created:LessThan', date('Y-m-d H:i:s', strtotime('-10 days')));
		$allStudents = $studentQuery->map('ID', 'UUID')->toArray();

		if (! empty($allStudents)) {
			$ua = new UrbanAirship();
			$ua->sendPushToUser($allStudents, $pushMessage);
		}

		foreach ($studentQuery as $student) {
			$student->PurchaseChase = 1;
			$student->PurchaseChaseSent = date('Y-m-d H:i:s');
			$student->write();
		}

		echo date('Y-m-d H:i:s') . ': Premium notifications sent';
	}

}