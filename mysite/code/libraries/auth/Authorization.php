<?php

class Authorization {

	private $server;

	public function __construct() {

		$this->server = new \League\OAuth2\Server\AuthorizationServer;
		$this->server->setSessionStorage(new SessionStorage());
		$this->server->setAccessTokenStorage(new AccessTokenStorage());
		$this->server->setClientStorage(new ClientStorage());
		$this->server->setScopeStorage(new ScopeStorage());
		$this->server->setRefreshTokenStorage(new RefreshTokenStorage);

		$refreshTokenGrant = new \League\OAuth2\Server\Grant\RefreshTokenGrant();
		$refreshTokenGrant->setRefreshTokenTTL(1209600);
		$this->server->addGrantType($refreshTokenGrant);

		$passwordGrant = new \League\OAuth2\Server\Grant\PasswordGrant();
		$facebookGrant = new FacebookGrant();

		//check the username and password combination provided exists
		$passwordGrant->setVerifyCredentialsCallback(function ($username, $password) {

			$user = Student::get()->filterAny(['Email' => $username, 'Username' => $username])->first();

			if ($user) {

				if ($user->Password == null) {
					throw new Exception('We have improved security in the latest release. Please reset your password.', 1008);
				}

				$authed = $user->checkPassword($password);

				//if (! Permission::checkMember($user, 'APP_CAN_LOGIN')) return false;
				if ($user->SchoolID) {
					if ($user->School()->Suspended) {
						throw new Exception('School account suspended', 1006);
					}
				}

				if ($authed->valid()) {
					ConcurrentLoginSecurity::check($user);

					if ($user->Deleted == 1) {
						$user->Deleted = 0;
						$user->Verified = 0;
						$user->VerifiedDate = null;
						$user->VerificationExpiry = null;
						$user->VerificationCode = 0;
						$user->write();
					}

					return $user->ID;
				}
			}

			//user does not exist
			return false;
		});

		//with the facebook grant we actually want to create the user if it doesn't exist
		//A token can still fail the access token check many ways in Mysite\Libraries\FacebookGrant
		$facebookGrant->setVerifyCredentialsCallback(function ($facebookUserID) {

			$user = Student::get()->filter('FacebookUserID', $facebookUserID)->first();

			if (empty($user)) {
				$user = new Student;
				$user->FacebookUserID = $facebookUserID;
				$user->Verified = 1;
				$user->write();
			} else {
				ConcurrentLoginSecurity::check($user);

				if ($user->Deleted == 1) {
					$user->Deleted = 0;
					$user->VerifiedDate = null;
					$user->VerificationExpiry = null;
					$user->VerificationCode = 0;
				}

				$user->Verified = 1;
				$user->write();
			}

			return ['exists' => ! empty($user->FirstName) ? 1 : 0, 'user' => $user];
		});

		$this->server->addGrantType($passwordGrant);
		$this->server->addGrantType($facebookGrant, 'facebook');
		$this->server->setTokenType(new CustomBearer);
	}

	public function login($data) {

		$this->server->getRequest()->request->set('grant_type', isset($data['grant_type']) ? $data['grant_type'] : null);
		$this->server->getRequest()->request->set('client_id', isset($data['client_id']) ? $data['client_id'] : null);
		$this->server->getRequest()->request->set('client_secret', isset($data['client_secret']) ? $data['client_secret'] : null);

		if (isset($data['username'])) $this->server->getRequest()->request->set('username', $data['username']);
		if (isset($data['password'])) $this->server->getRequest()->request->set('password', $data['password']);
		if (isset($data['refresh_token'])) $this->server->getRequest()->request->set('refresh_token', $data['refresh_token']);
		if (isset($data['access_token'])) $this->server->getRequest()->request->set('access_token', $data['access_token']);

		$this->server->setAccessTokenTTL(14400);
		$response = $this->server->issueAccessToken();
		$response['status'] = 'success';

		if (isset($data['username'])) {
			if ($user = Student::get()->filterAny(['Email' => $data['username'], 'Username' => $data['username']])->first()) {
				if ($device = DeviceType::get()->filter('Name', $user->Device)->first()) {
					if ($deviceCampaign = $device->DeviceCampaign()) {
						$campaignStart = date('d-m-Y', strtotime($deviceCampaign->CampaignStartDate));
						$campaignEnd = date('d-m-Y', strtotime($deviceCampaign->CampaignEndDate));
						$memberCreated = date('d-m-Y', strtotime($user->Created));

						if($user->DeviceMessageReturned == 0) {
							if (($memberCreated >= $campaignStart) && ($memberCreated <= $campaignEnd)) {
								$response['device_message'] = strip_tags($deviceCampaign->Message);
								$user->DeviceMessageReturned = 1;
								$user->write();
							} else {
								$response['device_message'] = '';
							}
						} else {
							$response['device_message'] = '';
						}
					}
				}
			}
		}

		if (isset($data['username']) || isset($data['refresh_token'])) {
			$response['exists'] = 1;
		}

		return $response;
	}

}