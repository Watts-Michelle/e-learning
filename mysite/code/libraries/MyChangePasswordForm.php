<?php
/**
 * Standard Change Password Form
 * @package framework
 * @subpackage security
 */
class MyChangePasswordForm extends ChangePasswordForm {

	/**
	 * Change the password
	 *
	 * @param array $data The user submitted data
	 * @return SS_HTTPResponse
	 */
	public function doChangePassword(array $data) {
		if($member = Member::currentUser()) {
			// The user was logged in, check the current password
			if(empty($data['OldPassword']) || !$member->checkPassword($data['OldPassword'])->valid()) {
				$this->clearMessage();
				$this->sessionMessage(
					_t('Member.ERRORPASSWORDNOTMATCH', "Your current password does not match, please try again"),
					"bad"
				);
				// redirect back to the form, instead of using redirectBack() which could send the user elsewhere.
				return $this->controller->redirect($this->controller->Link('changepassword'));
			}
		}

		if(!$member) {
			if(Session::get('AutoLoginHash')) {
				$member = Member::member_from_autologinhash(Session::get('AutoLoginHash'));
			}

			// The user is not logged in and no valid auto login hash is available
			if(!$member) {
				Session::clear('AutoLoginHash');
				return $this->controller->redirect($this->controller->Link('login'));
			}
		}

		// Check the new password
		if(empty($data['NewPassword1'])) {
			$this->clearMessage();
			$this->sessionMessage(
				_t('Member.EMPTYNEWPASSWORD', "The new password can't be empty, please try again"),
				"bad");

			// redirect back to the form, instead of using redirectBack() which could send the user elsewhere.
			return $this->controller->redirect($this->controller->Link('changepassword'));
		}
		else if($data['NewPassword1'] == $data['NewPassword2']) {
			$isValid = $member->changePassword($data['NewPassword1']);
			if($isValid->valid()) {

				// Clear locked out status
				$member->LockedOutUntil = null;
				$member->FailedLoginCount = null;
				$member->write();

				// TODO Add confirmation message to login redirect
				Session::clear('AutoLoginHash');

				if ($member->ClassName == 'Student') {
					return $this->controller->redirect('Reset/PasswordComplete');
				} else {
					return $this->controller->redirect('Security/login');
				}
			} else {
				$this->clearMessage();
				$this->sessionMessage(
					_t(
						'Member.INVALIDNEWPASSWORD',
						"We couldn't accept that password: {password}",
						array('password' => nl2br("\n".Convert::raw2xml($isValid->starredList())))
					),
					"bad",
					false
				);

				// redirect back to the form, instead of using redirectBack() which could send the user elsewhere.
				return $this->controller->redirect($this->controller->Link('changepassword'));
			}

		} else {
			$this->clearMessage();
			$this->sessionMessage(
				_t('Member.ERRORNEWPASSWORD', "You have entered your new password differently, try again"),
				"bad");

			// redirect back to the form, instead of using redirectBack() which could send the user elsewhere.
			return $this->controller->redirect($this->controller->Link('changepassword'));
		}
	}

}

