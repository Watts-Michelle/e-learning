<?php

class UserCreate_Controller extends Base_Controller
{

	/** {@inheritdoc} */
	protected $auth = false;

	/** {@inheritdoc} */
	private static $allowed_actions = array(
		'createUser',
	);

	/** {@inheritdoc} */
	private static $url_handlers = array(
		'' => 'createUser',
	);

	public function createUser(SS_HTTPRequest $request) {

		if (! $request->isPOST()) return $this->handleError(404, 'Must be a POST request');

		$body = new CreateUserForm($this->requestBody);
		$data = $body->process();

		if (! empty($data['DateOfBirth'])) {
			$data['DateOfBirth'] = date('Y-m-d', $data['DateOfBirth']);
		}

		$examLevel = ExamLevel::get()->filter(['Name' => $data['ExamLevel'], 'Live' => 1])->first();
		$examCountry = ExamCountry::get()->filter('Name', $data['ExamCountry'])->first();
		$country = isset($data['Country']) ? Country::get()->filter('TwoCharCode', $data['Country'])->first(): null;
		$ethnicity = isset($data['Ethnicity']) ? Ethnicity::get()->filter('Name', $data['Ethnicity'])->first() : null;

		unset($data['ExamLevel']);
		unset($data['ExamCountry']);
		unset($data['Country']);
		unset($data['Ethnicity']);

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

		if (! empty($data['Email'])) {
			$data['Email'] = strtolower($data['Email']);
		}

		if (Member::get()->filter(['Email' => $data['Email']])->first()) {
			return $this->handleError(2002);
		}

		if (Student::get()->filterAny(['Username' => $data['Username'], 'Email' => $data['Username']])->first()) {
			return $this->handleError(2004);
		}

		$password = $data['Password'];
		unset($data['Password']);

		$worstStudent = Student::get()->sort('Ranking', 'desc')->first();

		$message = '';

		try {
			$member = new Student;
			$member->Ranking = $worstStudent->TotalPoints == 0 ? $worstStudent->Ranking : $worstStudent->Ranking + 1;
			$member->update($data);
			$member->write();

			if($device = DeviceType::get()->filter('Name', $member->Device)->first()){
				if($deviceCampaign = $device->DeviceCampaign()){
					$campaignStart = date('d-m-Y', strtotime($deviceCampaign->CampaignStartDate));
					$campaignEnd = date('d-m-Y', strtotime($deviceCampaign->CampaignEndDate));
					$memberCreated = date('d-m-Y', strtotime($member->Created));

					if(($memberCreated >= $campaignStart) && ($memberCreated <= $campaignEnd)){
						$member->DeviceCampaign = 1;
						$message = $deviceCampaign->Message;
					}
				}
			}

			//for some reason passwords are being encrypted twice, this fixes the passwords...
			$member->Password = $password;
			$member->write();

			$this->generateVerificationCode($member);
		} catch (ValidationException $e) {
			throw new O_HTTPResponse_Exception($e->getMessage(), 400, 2001);
		}

		if($examCountry->Name == 'France'){
			$member->sendFrenchRegistrationEmail();
		} else {
			$member->sendRegistrationEmail();
		}

		return (new JsonApi)->formatReturn([
			'user' => $member->getBasic(),
			'message' => ($message) ? strip_tags($message) : '',
		]);
	}

}