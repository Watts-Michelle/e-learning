<?php

class Checks_Controller extends Base_Controller
{

	/** {@inheritdoc} */
	protected $auth = false;

	/** {@inheritdoc} */
	private static $allowed_actions = array(
		'checkUsername',
	);

	/** {@inheritdoc} */
	private static $url_handlers = array(
		'username' => 'checkUsername',
	);

	public function checkUsername(SS_HTTPRequest $request) {

		$username = $request->getVar('username');

		if (! $username) {
			if (! empty($this->requestBody['username'])) {
				$username = $this->requestBody['username'];
			}
		}

		if (! $username) return $this->getResponse()->setStatusCode(400);

		if (Student::get()->filterAny(['Username' => $username])->first()) {
			return $this->getResponse()->setStatusCode(204);
		}

		return $this->getResponse()->setStatusCode(404);
	}

}