<?php

use Respect\Validation\Validator as v;

/**
 * Handle actions related to the logged in user
 *
 * Currently only logout is supported
 *
 * @package Studytracks
 * @subpackage Controllers
 * @author Jonathan Little <jonathan@flipsidegroup.com>
 */
class User_Controller extends Base_Controller {

	/** {@inheritdoc} */
	protected $auth = true;

	protected $unverified = array(
		'api/user/me/verify'
	);

	/** {@inheritdoc} */
	private static $allowed_actions = array(
		'logout',
		'logoutAll',
		'create',
		'currentUser',
		'purchaseEndpoints',
		'associateChannel',
		'verifyAccount',
		'eventSong',
		'eventQuiz',
		'eventShare',
		'subscription'
	);

	/** {@inheritdoc} */
	private static $url_handlers = array(
		'me/purchase' => 'purchaseEndpoints',
		'me/associate' => 'associateChannel',
		'me/verify' => 'verifyAccount',
		'me/subscription' => 'subscription',
		'me/event/song' => 'eventSong',
		'me/event/quiz' => 'eventQuiz',
		'me/event/share' => 'eventShare',
		'me' => 'currentUser',
		'logout/all' => 'logoutAll',
		'logout' => 'logout',
	);

	public function currentUser(SS_HTTPRequest $request) {

		switch ($request->httpMethod()) {
			case 'POST':
				return $this->update($request);
				break;
			case 'GET':
				return $this->get($request);
				break;
			case 'DELETE':
				return $this->delete($request);
				break;
		}
	}

	public function subscription(SS_HTTPRequest $request)
	{
		if($request->isPOST()){
			return $this->updateSubscription($request);
		}

		return $this->handleError(404, 'Request must be POST.');
	}

	public function updateSubscription(SS_HTTPRequest $request)
	{
		date_default_timezone_set('Europe/London');

		$data = $this->getBody($request);

		$User = CurrentUser::getUser();

		$subscription = '';

		$CurrentDate = empty($User->SubscriptionExpirationDate) ? strtotime(date('Y-m-d h:i:s', time())) : strtotime($User->SubscriptionExpirationDate);

		if (empty($data['ios_sku']) && empty($data['android_sku'])) return $this->handleError(2009, 'Need to receive a single SKU.');

		if (!empty($data['ios_sku']) && !empty($data['android_sku'])) return $this->handleError(2009, 'Need to receive a single SKU.');

		if (isset($data['android_sku']) && v::stringType()->validate($data['android_sku'])) {
			$subscription = SubscriptionType::get()->filter(array('Active' => true, 'AndroidSKU' => $data['android_sku']))->first();
		}

		if (isset($data['ios_sku']) && v::stringType()->validate($data['ios_sku'])) {
			$subscription = SubscriptionType::get()->filter(array('Active' => true, 'IOSSKU' => $data['ios_sku']))->first();
		}

		$subscription->Students()->add($User);

		$ExpirationDate = date("Y-m-d h:i:s", strtotime("+".$subscription->Duration." month", $CurrentDate));
		$User->SubscriptionExpirationDate = $ExpirationDate;
		$User->SubscriptionStatus = 'Subscribed';
		$User->write();

		return (new JsonApi)->formatReturn([$User->SubscriptionExpirationDate]);
	}

	public function purchaseEndpoints(SS_HTTPRequest $request)
	{
		if ($request->isGET()) {
			return $this->purchaseStatus();
		} elseif ($request->isPOST()) {
			return $this->purchase();
		}

		return $this->handleError(404, 'Request must be GET or POST.');
	}

	public function verifyAccount(SS_HTTPRequest $request)
	{
		if ($request->isPOST()) {
			return $this->createVerificationCode();
		} else if ($request->isPUT()) {
			return $this->checkVerificationCode();
		}

		return $this->handleError(404, 'Request must be PUT or POST.');
	}

	/**
	 * Increment the Songs Completed count for Shared for the user
	 *
	 * @return SS_HTTPResponse
	 * @throws O_HTTPResponse_Exception
	 */
	public function eventSong(SS_HTTPRequest $request) {
		if ($request->httpMethod() != 'POST') {
			return $this->httpError(405, 'Method Not Allowed');
		}

		// Increment the corrent event counter in the Student record
		$this->appUser->EventsSongCompleted++;
		$this->appUser->write();

		// Return the full strudent record
		return $this->get($request);
	}

	/**
	 * Increment the Quiz > 80% count for Shared for the user
	 *
	 * @return SS_HTTPResponse
	 * @throws O_HTTPResponse_Exception
	 */
	public function eventQuiz(SS_HTTPRequest $request) {
		if ($request->httpMethod() != 'POST') {
			return $this->httpError(405, 'Method Not Allowed');
		}

		// Increment the corrent event counter in the Student record
		$this->appUser->EventsQuizSuccess++;
		$this->appUser->write();

		// Return the full strudent record
		return $this->get($request);
	}

	/**
	 * Increment the Event count for Shared for the user
	 *
	 * @return SS_HTTPResponse
	 * @throws O_HTTPResponse_Exception
	 */
	public function eventShare(SS_HTTPRequest $request) {
		if ($request->httpMethod() != 'POST') {
			return $this->httpError(405, 'Method Not Allowed');
		}

		// Increment the corrent event counter in the Student record
		$this->appUser->EventsShare++;
		$this->appUser->write();

		// Return the full strudent record
		return $this->get($request);
	}

	public function associateChannel(SS_HTTPRequest $request) {

		if (! $request->isPOST()) return $this->handleError(404, 'Request must be POST.');

		$class = new AssociateChannelForm($this->requestBody);
		$notification_data = $class->process();

		try {
			$ua = new UrbanAirship();
			$ua->associate($this->appUser->UUID, $notification_data['ChannelID'], $notification_data['Platform']);
		} catch(\Exception $e) {
			return $this->handleError(2133, 'device id not correct');
		}

		return (new JsonApi)->formatReturn([]);
	}


	/**
	 * Delete a single access token to log a user out
	 *
	 * @return SS_HTTPResponse
	 * @throws O_HTTPResponse_Exception
	 */
	public function logout(SS_HTTPRequest $request) {
		if (! $request->isDELETE()) $this->handleError(404, 'Request must be DELETE.');

		/** @var OauthAccessToken $token */
		$token = OauthAccessToken::get()->filter(['AccessToken' => $this->authServer->getAccessToken()])->first();

		$sessionStorage = new SessionStorage();
		$sessionStorage->removeAccessToken($token);
		return (new JsonApi)->formatReturn([]);
	}

	/**
	 * Delete all of a user's access tokens to log them out everywhere
	 *
	 * @return SS_HTTPResponse
	 * @throws O_HTTPResponse_Exception
	 */
	public function logoutAll(SS_HTTPRequest $request, $force = false) {
		if (! $request->isDELETE() && ! $force) return $this->handleError(404, 'Request must be DELETE.');
		$sessionStorage = new SessionStorage();
		$sessionStorage->removeAll($this->appUser->ID);
		return (new JsonApi)->formatReturn([]);
	}

	private function checkVerificationCode()
	{
		if ($this->appUser->VerificationCode !== $this->requestBody['code']) {
			return $this->handleError(1005, 'Verification code incorrect');
		} else if ($this->appUser->VerificationExpiry < date('Y-m-d H:i:s')) {
			return $this->handleError(1004, 'Verification code expired');
		}

		$this->appUser->Verified = 1;
		$this->appUser->VerifiedDate = date('Y-m-d H:i:s');
		$this->appUser->write();

		return (new JsonApi)->formatReturn([]);
	}

	private function createVerificationCode()
	{
		$this->generateVerificationCode($this->appUser, true);
		return (new JsonApi)->formatReturn([]);
	}

	private function purchase()
	{
		if (empty($this->requestBody['purchase'])) return $this->handleError(2009, 'Need to receive an array of purchases');

		if (! is_array($this->requestBody['purchase'])) {
			$this->requestBody['purchase'] = [$this->requestBody['purchase']];
		}

		foreach ($this->requestBody['purchase'] as $purchase) {
			$body = new PurchaseForm($purchase);
			$data = $body->process();

			$androidSubjectSKU = Subject::get()->map('ID', 'AndroidSKU')->toArray();
			$iOSSubjectSKU = Subject::get()->map('ID', 'IOSSKU')->toArray();
			$androidSubjectGroupSKU = SubjectGrouping::get()->map('ID', 'AndroidSKU')->toArray();
			$iOSSubjectGroupSKU = SubjectGrouping::get()->map('ID', 'IOSSKU')->toArray();

			$androidSKU = array_merge($androidSubjectSKU, $androidSubjectGroupSKU);
			$iOSSKU = array_merge($iOSSubjectSKU, $iOSSubjectGroupSKU);

			if (empty($data['IOSSKU']) && empty($data['AndroidSKU'])) return $this->handleError(2006, 'Provide one of iOS or Android SKU');
			if (!empty($data['IOSSKU']) && !empty($data['AndroidSKU'])) return $this->handleError(2005, 'Only provide an iOS or Android SKU, never both');

			if (!empty($data['IOSSKU']) && !in_array($data['IOSSKU'], $iOSSKU)) return $this->handleError(2007, 'Not a valid iOS SKU');
			if (!empty($data['AndroidSKU']) && !in_array($data['AndroidSKU'], $androidSKU)) return $this->handleError(2008, 'Not a valid Android SKU');

			$data['StudentID'] = $this->appUser->ID;

			try {
				$order = new Order;
				$order->update($data);
				$order->write();
			} catch (SS_DatabaseException $e) {
				return $this->handleError(2009, 'Order number already exists');
			}
		}

		return (new JsonApi)->formatReturn([]);
	}

	private function purchaseStatus()
	{
		$subjects = Subject::get()->filter(['ExamLevelID' => $this->appUser->ExamLevelID, 'Live' => 1])->sort('SubjectSortOrder ASC');

		$returnData = [
			'subject' => [],
			'subject_group' => [],
		];

		foreach ($subjects as $subject) {
			if ($subjectData = $subject->getPurchase()) {
				$returnData['subject'][] = $subjectData;
			}
		}

		$subjectGroups = SubjectGrouping::get()->filter('ExamLevelID', $this->appUser->ExamLevelID);

		foreach ($subjectGroups as $subjectGroup) {
			$returnData['subject_group'][] = $subjectGroup->getPurchase();
		}

		return (new JsonApi)->formatReturn($returnData);
	}

	private function get(SS_HTTPRequest $request) {
		return (new JsonApi)->formatReturn(['user' => $this->appUser->getBasic()]);
	}

	private function update(SS_HTTPRequest $request) {

		if (preg_match('/^multipart\/form-data/', $request->getHeader('Content-Type'))) {
			//upload image
			$image = $this->performUpload($_FILES['image']);

			if (! empty($image)) {
				$data['ImageID'] = $image->ID;
			}
		} elseif (preg_match('/^application\/json/', $request->getHeader('Content-Type'))) {

			$body = new UpdateUserForm($this->requestBody);
			$data = $body->process();

			$examLevel = isset($data['ExamLevel']) ? ExamLevel::get()->filter(['Name' => $data['ExamLevel'], 'Live' => 1])->first() : null;
			$examCountry = isset($data['ExamCountry']) ? ExamCountry::get()->filter('Name', $data['ExamCountry'])->first() : null;
			$country = isset($data['Country']) ? Country::get()->filter('TwoCharCode', $data['Country'])->first() : null;
			$ethnicity = isset($data['Ethnicity']) ? Ethnicity::get()->filter('Name', $data['Ethnicity'])->first() : null;

			$device = isset($data['Device']) ? $data['Device'] : null;

			unset($data['ExamLevel']);
			unset($data['ExamCountry']);
			unset($data['Country']);
			unset($data['Ethnicity']);
			unset($data['DeviceType']);

			if ($examLevel) {
				$data['ExamLevelID'] = $examLevel->ID;
			}

			if ($country) {
				$data['CountryID'] = $country->ID;
			}

			if ($examCountry) {
				$data['ExamCountryID'] = $examCountry->ID;
			}

			if ($ethnicity) {
				$data['EthnicityID'] = $ethnicity->ID;
			}

			if ($device) {
				$data['Device'] = $device;
			}

			if (! empty($data['Email'])) {
				$data['Email'] = strtolower($data['Email']);
			}

			if (isset($data['Password']) && empty($data['Password'])) {
				return $this->handleError(2001, 'Password field was provided with an empty string.');
			}

			if (isset($data['Email'])) {

				if (empty($data['Email'])) {
					unset($data['Email']);
				}

				if (Member::get()->filterAny(['Email' => $data['Email']])->where("ID != " . $this->appUser->ID)->first()) {
					return $this->handleError(2002);
				}
			}

			if (isset($data['Username'])) {
				if (Student::get()->filterAny(['Username' => $data['Username']])->where("Student.ID != " . $this->appUser->ID)->first()) {
					return $this->handleError(2004);
				}
			}

		} else {
			return $this->handleError(2003);
		}

		try {
			$this->appUser->update($data);
			$this->appUser->write();
		} catch (Exception $e) {
			return $this->handleError(5000, $e->getMessage(), 400);
		}

		return (new JsonApi)->formatReturn($this->appUser->getBasic());
	}

	private function delete(SS_HTTPRequest $request) {
		$this->appUser->Deleted = 1;
		$this->appUser->Verified = 0;
		$this->appUser->write();
		$this->logoutAll($request, true);

		return (new JsonApi)->formatReturn([]);
	}


	private function performUpload($uploadfile) {

		if ( ! $type = $this->validator($uploadfile)) {
			return $this->handleError(2001, 'Image file not valid.');
		}

		//save image to server
		$upload = new Upload;
		$upload->load($uploadfile, 'users/' . $this->appUser->UUID . '/' . $type);

		$file = $upload->getFile();

		return $file;
	}

	private function validator($uploadfile) {
		//validate image is a png or jpg and smaller than 1mb
		$validator = new Upload_Validator();
		$validator->setTmpFile($uploadfile);
		$validator->setAllowedMaxFileSize(1000000);
		$validator->setAllowedExtensions(array('png', 'jpg', 'jpeg'));

		if ($valid = $validator->validate()) {
			return 'image';
		}

		return false;
	}
}