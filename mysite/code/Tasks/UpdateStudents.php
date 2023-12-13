<?php

class UpdateStudents_Task extends Controller
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

	function randomPassword($StringLength = 8){

		$seed = str_split('abcdefghijklmnopqrstuvwxyz'
			.'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
			.'0123456789!@#$%^&*()'); // and any other characters
		shuffle($seed); // probably optional since array_is randomized; this may be redundant
		$rand = '';
		foreach (array_rand($seed, $StringLength) as $k) $rand .= $seed[$k];

		return $rand;
	}

	public function index()
	{

		foreach (Student::get()->filter('SchoolID', 6) as $student) {
			$password = $this->randomPassword(8);
			$student->sendSchoolRegistrationEmail($password);
			echo 'Student: '.$student->ID.' updated';
		}
		return true;
	}

}