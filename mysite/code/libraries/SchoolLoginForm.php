<?php

class SchoolLoginForm extends MemberLoginForm {

	public function dologin($data) {
		parent::dologin($data);

		if ($member = Member::currentUser()) {
			if ($member->ClassName == 'Staff') {
				$this->controller->response->removeHeader('Location');
				$destination = '/school';
				$this->controller->redirect($destination);
			}
		}
	}

	/**
	 * Forgot password form handler method.
	 * Called when the user clicks on "I've lost my password".
	 * Extensions can use the 'forgotPassword' method to veto executing
	 * the logic, by returning FALSE. In this case, the user will be redirected back
	 * to the form without further action. It is recommended to set a message
	 * in the form detailing why the action was denied.
	 *
	 * @param array $data Submitted data
	 */
	public function forgotPassword($data) {
		// Ensure password is given
		if(empty($data['Email'])) {
			$this->sessionMessage(
				_t('Member.ENTEREMAIL', 'Please enter an email address to get a password reset link.'),
				'bad'
			);

			$this->controller->redirect('Security/lostpassword');
			return;
		}

		// Find existing member
		$member = Member::get()->filter("Email", $data['Email'])->first();

		// Allow vetoing forgot password requests
		$results = $this->extend('forgotPassword', $member);
		if($results && is_array($results) && in_array(false, $results, true)) {
			return $this->controller->redirect('Security/lostpassword');
		}

		if($member) {
			$token = $member->generateAutologinTokenAndStoreHash();

			if($member->School()->French){
				$e = MyMember_ForgotPasswordFrenchEmail::create();
			} else {
				$e = MyMember_ForgotPasswordEmail::create();
			}
			$e->populateTemplate($member);
			$e->populateTemplate(array(
				'PasswordResetLink' => Security::getPasswordResetLink($member, $token)
			));
			$e->setTo($member->Email);
			$e->send();

			$this->controller->redirect('Security/passwordsent/' . urlencode($data['Email']));
		} elseif($data['Email']) {
			// Avoid information disclosure by displaying the same status,
			// regardless wether the email address actually exists
			$this->controller->redirect('Security/passwordsent/' . rawurlencode($data['Email']));
		} else {
			$this->sessionMessage(
				_t('Member.ENTEREMAIL', 'Please enter an email address to get a password reset link.'),
				'bad'
			);

			$this->controller->redirect('Security/lostpassword');
		}
	}

}
