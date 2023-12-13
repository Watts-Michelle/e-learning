<?php
use GuzzleHttp\Client as Guzzle;

class FacebookService {

	private $_access_token;
	private $_baseURL = 'https://graph.facebook.com/';

	public function __construct($access_token = null) {
		$this->_access_token = $access_token;
	}

	public function setAccessToken($token) {
		$this->_access_token = $token;
		return $this;
	}

	public function getAccessToken() {
		return $this->_access_token;
	}

	public function retrieveUser() {

		if (empty($this->_access_token)) throw new \Exception('Missing access token', 1008);

		$call = (new Guzzle())->get($this->_baseURL . 'me?access_token='.$this->_access_token, ['exceptions' => false]);

		return $call->getBody();
	}

	public function retrieveUserDetails($fields = array()) {
		if (empty($this->_access_token)) throw new \Exception('Missing access token', 1008);

		$fields = implode(',', $fields);
		$call = (new Guzzle())->get(
			$this->_baseURL . 'me',
			[
				'query' => ['access_token' => $this->_access_token, 'fields' => $fields],
				'exceptions' => false
			]
		);

		return $call->getBody();
	}

	public function retrieveAppID() {

		if (empty($this->_access_token)) throw new \Exception('Missing access token', 1008);

		$call = (new Guzzle())->get($this->_baseURL . 'app', ['query' => ['access_token' => $this->_access_token], 'exceptions' => false]);
		return $call->getBody();
	}

}