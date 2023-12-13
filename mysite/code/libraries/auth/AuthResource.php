<?php

class AuthResource {

	private $server;
	private $access_token;

	public function __construct() {

		// Set up the OAuth 2.0 resource server
		$sessionStorage = new SessionStorage();
		$accessTokenStorage = new AccessTokenStorage();
		$clientStorage = new ClientStorage();
		$scopeStorage = new ScopeStorage();
		$this->server = new \League\OAuth2\Server\ResourceServer($sessionStorage, $accessTokenStorage, $clientStorage, $scopeStorage);

	}

	/**
	 * @return Member
	 * @throws \League\OAuth2\Server\Exception\AccessDeniedException
	 */
	public function getLoggedInUser() {

		$validRequest = $this->server->isValidRequest(true);

		if (isset($validRequest)) {
			$this->access_token = $this->server->getAccessToken();
			$session = $this->server->getSessionStorage()->getByAccessToken($this->access_token);

			//retrieve the user
			$user = Student::get()->filter(['UUID' => $session->getOwnerId(), 'Deleted' => 0])->first();
		}

		if (empty($user->ID)) throw new League\OAuth2\Server\Exception\AccessDeniedException();

		return $user;
	}

	public function getAccessToken() {
		return $this->access_token;
	}

}


