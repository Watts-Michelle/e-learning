<?php

/**
 * createSchool task needs to be run before all others. Followed by createClasses and createStaff.
 *
 * Class Wonde_Controller
 */

class Wonde_Controller extends Base_Controller
{
	// TO DO: Check updated time!

	protected $auth = false;

	private static $allowed_actions = array(
		'createSchool',
		'createClasses',
		'createStaff',
		'createStudents',
		'sendStudentRegistrationEmails'
	);

	private static $url_handlers = array(
		'create/schools' => 'createSchool',
		'create/classes' => 'createClasses',
		'create/staff' => 'createStaff',
		'create/students' => 'createStudents',
		'sendEmails' => 'sendStudentRegistrationEmails'
	);

//	public function init() {
//
//		parent::init();
//
//		if (php_sapi_name() != "cli") {
//			if (!Member::currentUser()) {
//
//				return Security::PermissionFailure($this->controller, 'You must be logged in to access this page');
//			}
//
//			if (!Member::currentUser()->inGroups(array('administrators'))) {
//				return Security::PermissionFailure($this->controller, 'You must be logged in as admin to access this page');
//			}
//		}
//	}

	/**
	 * Create School
	 *
	 * @return SS_HTTPResponse
	 */
	public function createSchool()
	{
		ini_set('max_execution_time', 0);

		$client = new \Wonde\Client(WondeToken);

		foreach($client->schools->all() as $WondeSchool){

			if(School::get()->filter('WondeID', $WondeSchool->id)->first()){
				$School = School::get()->filter('WondeID', $WondeSchool->id)->first();
			} else {
				$School = School::create();
			}

			$config = SiteConfig::current_site_config();

			$ExamCountry = ExamCountry::get()->filter('name', 'United Kingdom')->first();

			$School->WondeID = $WondeSchool->id;
			$School->Name = $WondeSchool->name;
			$School->StudentCap = $config->SchoolStudentCap;
			$School->StaffCap = $config->SchoolStaffCap;
			$School->ExamCountryID = $ExamCountry->ID;
			$School->WondeUrn = $WondeSchool->urn;
			$School->WondePhaseOfEducation = $WondeSchool->phase_of_education;
			$School->WondeLACode = $WondeSchool->la_code;
			$School->WondeTimezone = $WondeSchool->timezone;
			$School->WondeMis = $WondeSchool->mis;
			$School->WondeAddressLine1 = $WondeSchool->address->address_line_1;
			$School->WondeAddressLine2 = $WondeSchool->address->address_line_2;
			$School->WondeAddressLineTown = $WondeSchool->address->address_town;
			$School->WondeAddressPostcode = $WondeSchool->address->address_postcode;
			$School->WondeAllowsWriteback = $WondeSchool->extended->allows_writeback;
			$School->WondeHasTimetables = $WondeSchool->extended->has_timetables;
			$School->WondeHasLessonAttendance = $WondeSchool->extended->has_lesson_attendance;

			$School->write();
		}

		return (new JsonApi)->formatReturn([$client->schools->all()]);
//		echo date('Y-m-d H:i:s') . ': Create school task run successfully.';
	}

	/**
	 * Create Classes
	 *
	 * @return SS_HTTPResponse
	 */
	public function createClasses()
	{
		ini_set('max_execution_time', 0);

		$client = new \Wonde\Client(WondeToken);

		foreach($client->schools->all() as $WondeSchool) {

			$School = $client->school($WondeSchool->id);

			// If school exists do stuff.
			if($CurrentSchool = School::get()->filter('WondeID', $WondeSchool->id)->first()) {

				foreach ($School->classes->all() as $WondeClass) {

					if (SchoolClass::get()->filter('WondeID', $WondeClass->id)->first()) {
						$Class = SchoolClass::get()->filter('WondeID', $WondeClass->id)->first();
					} else {
						$Class = SchoolClass::create();
					}

					$Class->Name = $WondeClass->name;
					$Class->SchoolID = $CurrentSchool->ID;
					$Class->WondeID = $WondeClass->id;
					$Class->WondeMisID = $WondeClass->mis_id;
					$Class->WondeName = $WondeClass->name;
					$Class->WondeDescription = $WondeClass->description;
					$Class->WondeSubject = $WondeClass->subject;
					$Class->WondeCreatedAt = $WondeClass->created_at->date;
					$Class->WondeUpdatedAt = $WondeClass->updated_at->date;

					$Class->write();
				}

			} else {
				echo date('Y-m-d H:i:s') . ': You need to create the school first!';
			}
		}
		echo date('Y-m-d H:i:s') . ': Create classes task run successfully.';
	}

	/**
	 * Create Staff
	 *
	 * @return SS_HTTPResponse
	 */
	public function createStaff()
	{
		ini_set('max_execution_time', 0);

		$client = new \Wonde\Client(WondeToken);

		foreach($client->schools->all() as $WondeSchool) {

			$School = $client->school($WondeSchool->id);

			// If school exists then do stuff.
			if($CurrentSchool = School::get()->filter('WondeID', $WondeSchool->id)->first()) {

				// Get all classes for this school.
				foreach ($School->classes->all(['employees']) as $WondeClass) {

					// If class exists do stuff.
					if ($CurrentClass = SchoolClass::get()->filter('WondeID', $WondeClass->id)->first()) {

						foreach ($WondeClass->employees->data as $WondeStaff) {

							// Filter out staff who aren't main teachers.
							if ($WondeStaff->meta->is_main_teacher == true) {

								if (Staff::get()->filter('WondeID', $WondeStaff->id)->first()) {
									$Staff = Staff::get()->filter('WondeID', $WondeStaff->id)->first();
								} else {
									$Staff = Staff::create();
								}

								$Staff->SchoolID = $CurrentSchool->ID;
								$Staff->FirstName = $WondeStaff->forename;
								$Staff->Surname = $WondeStaff->surname;
								$Staff->Role = 'Teacher';
								$Staff->WondeID = $WondeStaff->id;
								$Staff->WondeUPI = $WondeStaff->upi;
								$Staff->WondeMisID = $WondeStaff->mis_id;
								$Staff->WondeTitle = $WondeStaff->title;
								$Staff->WondeInitials = $WondeStaff->initials;
								$Staff->WondeSurname = $WondeStaff->surname;
								$Staff->WondeForename = $WondeStaff->forename;
								$Staff->WondeMiddleName = $WondeStaff->middle_names;
								$Staff->WondeLegalSurname = $WondeStaff->legal_surname;
								$Staff->WondeLegalForname = $WondeStaff->legal_forename;
								$Staff->WondeGender = $WondeStaff->gender;
								$Staff->WondeDateOfBirth = $WondeStaff->date_of_birth->date;
								$Staff->WondeUpdatedAt = $WondeStaff->updated_at->date;
								$Staff->WondeCreatedAt = $WondeStaff->created_at->date;

								$Staff->write();

								$Staff->SchoolClass()->add($CurrentClass);

								$CurrentClass->StaffID = $Staff->ID;
								$CurrentClass->write();
							}
						}

					} else {
						echo date('Y-m-d H:i:s') . ': You need to create classes first!';
					}
				}

				$this->sendStaffRegistrationEmails($CurrentSchool->WondeID);

			} else {

				echo date('Y-m-d H:i:s') . ': You need to create the school first!';
			}
		}
		echo date('Y-m-d H:i:s') . ': Create staff task run successfully.';
	}

	/**
	 * Send staff registration emails.
	 *
	 * @param $SchoolID
	 * @return SS_HTTPResponse
	 */
	public function sendStaffRegistrationEmails($SchoolID)
	{
		ini_set('max_execution_time', 0);

		$client = new \Wonde\Client(WondeToken);

		$School = $client->school($SchoolID);

		foreach ($School->employees->all(['contact_details']) as $WondeEmployee) {

			if ($Staff = Staff::get()->filter('WondeID', $WondeEmployee->id)->first()) {

				if($Staff->Verified === false) {

					$Staff->Verified = true;
					$Staff->Email = $WondeEmployee->contact_details->data->emails->email;
					$Staff->write();

					$Staff->Password = $this->randomPassword(10);
					$Staff->write();

					// Send staff email.
				}
			}
		}

		$email = new Email();
		$email
			->setFrom('admin@studytracks.com')
			->setTo('michelle@flipsidegroup.com')
			->setSubject('registration')
			->setBody('this is a test!');

		$email->send();

		return (new JsonApi)->formatReturn([]);
	}

	/**
	 * Create students
	 *
	 * @return SS_HTTPResponse
	 */
	public function createStudents()
	{
		ini_set('max_execution_time', 0);

		$client = new \Wonde\Client(WondeToken);

		$response = [];

		foreach($client->schools->all() as $WondeSchool) {

			$School = $client->school($WondeSchool->id);

			// If school exists then do stuff.
			if($CurrentSchool = School::get()->filter('WondeID', $WondeSchool->id)->first()) {

				// Get students
				foreach ($School->students->all(['classes', 'year']) as $WondeStudent) {

					$response[] = $WondeStudent;

					if (Student::get()->filter('WondeID', $WondeStudent->id)->first()) {
						$Student = Student::get()->filter('WondeID', $WondeStudent->id)->first();
					} else {
						$Student = Student::create();
					}

					$ExamCountry = ExamCountry::get()->filter('name', 'United Kingdom')->first();

					$Student->FirstName = $WondeStudent->forename;
					$Student->Surname = $WondeStudent->surname;
					$Student->DateOfBirth = $WondeStudent->date_of_birth->date;
					$Student->Gender = $WondeStudent->gender;
					$Student->WondeID = $WondeStudent->id;
					$Student->WondeUPI = $WondeStudent->upi;
					$Student->WondeMisID = $WondeStudent->mis_id;
					$Student->WondeInitials = $WondeStudent->initials;
					$Student->WondeSurname = $WondeStudent->surname;
					$Student->WondeForename = $WondeStudent->forename;
					$Student->WondeMiddleName = $WondeStudent->middle_names;
					$Student->WondeLegalSurname = $WondeStudent->legal_surname;
					$Student->WondeLegalForname = $WondeStudent->legal_forename;
					$Student->WondeGender = $WondeStudent->gender;
					$Student->WondeDateOfBirth = $WondeStudent->date_of_birth->date;
					$Student->WondeUpdatedAt = $WondeStudent->updated_at->date;
					$Student->WondeCreatedAt = $WondeStudent->created_at->date;
					$Student->SchoolID = $CurrentSchool->ID;
					$Student->ExamCountryID = $ExamCountry->ID;

//					$Student->write();

					$this->assignStudentsClass($WondeStudent->classes->data, $Student->ID);
				}

			} else {
				echo date('Y-m-d H:i:s') . ': You need to create the school first!';
			}

//			$this->sendStudentRegistrationEmails($CurrentSchool->WondeID);

			echo date('Y-m-d H:i:s') . ': Create students task run successfully.';
//			return (new JsonApi)->formatReturn([$School->students->all(['classes', 'year'])]);
		}
		echo date('Y-m-d H:i:s') . ': Create students task run successfully.';

//		return (new JsonApi)->formatReturn([$response]);
	}

	public function assignStudentsClass(array $Classes, $StudentID)
	{
		if($Student = Student::get()->byID($StudentID)) {

			foreach ($Classes as $class) {

				if ($class = SchoolClass::get()->filter('WondeID', $class->id)->first()) {

					var_dump('SchoolStudent: ' . $StudentID . 'Has been added to school class: ' . $class->ID);

					$Student->SchoolClasses()->add($class);
					$class->Students()->add($Student);

				} else {
					echo('Failed');
				}
			}
		} else {
			echo 'Student '.$StudentID .' does not exist.';
		}
	}

	/**
	 * Send students registration emails.
	 *
	 * @param $SchoolID
	 * @return SS_HTTPResponse
	 */
	public function sendStudentRegistrationEmails($SchoolID)
	{
		ini_set('max_execution_time', 0);

		$client = new \Wonde\Client(WondeToken);

		$School = $client->school($SchoolID);

		foreach ($School->students->all(['contact_details']) as $WondeStudent) {

			if ($Student = Student::get()->filter('WondeID', $WondeStudent->id)->first()) {

				if($Student->Verified === false) {

					$Student->Verified = true;
					$Student->Email = $WondeStudent->contact_details->data->emails->email;
					$Student->write();

					$Student->Password = $this->randomPassword(10);
					$Student->write();

					// Send staff email.
				}
			}
		}

		$email = new Email();
		$email
			->setFrom('admin@studytracks.com')
			->setTo('michelle@flipsidegroup.com')
			->setSubject('registration')
			->setBody('this is a test!');

		$email->send();

		return (new JsonApi)->formatReturn([$School->students->all(['contact_details'])]);
	}

	/**
	 * Generate password
	 *
	 * @param int $StringLength
	 * @return string
	 */
	public function randomPassword($StringLength = 8){

		$seed = str_split('abcdefghijklmnopqrstuvwxyz'
			.'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
			.'0123456789!@#$%^&*()'); // and any other characters
		shuffle($seed);
		$rand = '';
		foreach (array_rand($seed, $StringLength) as $k) $rand .= $seed[$k];

		return $rand;
	}
}