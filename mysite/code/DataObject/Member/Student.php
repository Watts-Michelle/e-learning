<?php

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

class Student extends Member {

    /** @var array  Define the required fields for the Student table */
    protected static $db = array(
		'UUID' => 'Varchar(50)',
		'Username' => 'Varchar(85)',
		'DateOfBirth' => 'Date',
		'Gender' => "Enum('Male, Female, Other')",
		'TotalPoints' => 'Int(0)',
		'Ranking' => 'Int(0)',
		'QuestionsAnswered' => 'Int(0)',
		'PercentageCorrect' => 'Int(100)',
		'TestsCompleted' => 'Int(0)',
		'LessonsCompleted' => 'Int(0)',
		'HomeworkPlaylistLessonCompleted' => 'Int(0)',
		'PurchaseChase' => 'Boolean',
		'PurchaseChaseSent' => 'SS_Datetime',
		'FacebookUserID' => 'Varchar(255)',
		'LastAccess' => 'SS_Datetime',
		'Device' => 'Varchar(255)',
		'DeviceCampaign' => 'Boolean',
		'DeviceMessageReturned' => 'Boolean',
		'EventsSongCompleted' => 'Int',
		'EventsQuizSuccess' => 'Int',
		'EventsShare' => 'Int',
		'SubscriptionStatus' => "Enum('Unsubscribed, Subscribed')",
		'SubscriptionExpirationDate' => 'SS_DateTime',
		'WondeID' => 'Varchar(255)',
		'WondeUPI' => 'Int',
		'WondeMisID' => 'Int',
		'WondeInitials' => 'Varchar',
		'WondeSurname' => 'Varchar',
		'WondeForename' => 'Varchar',
		'WondeMiddleName' => 'Varchar',
		'WondeLegalSurname' => 'Varchar',
		'WondeLegalForname' => 'Varchar',
		'WondeGender' => 'Varchar',
		'WondeDateOfBirth' => 'SS_DateTime',
		'WondeUpdatedAt' => 'SS_DateTime',
		'WondeCreatedAt' => 'SS_DateTime',
		'WestID' => 'Varchar(255)',
		'WestStudentNumber' => 'Varchar',
		'WestDistrictID' => 'Varchar(255)',
		'WestSurname' => 'Varchar',
		'WestForename' => 'Varchar',
		'WestEmail' => 'Varchar',
		'WestCreatedAt' => 'SS_DateTime',
		'WestLastModified' => 'SS_DateTime',
	);
    
    protected static $has_one = array(
		'Country' => 'Country',
		'ExamLevel' => 'ExamLevel',
		'ExamCountry' => 'ExamCountry',
		'Ethnicity' => 'Ethnicity',
		'School' => 'School',
		'DeviceType' => 'DeviceType',
		'SubscriptionType' => 'SubscriptionType'
	);

	protected static $has_many = array(
		'MemberQuizSessions' => 'MemberQuizSession',
		'Playlists' => 'Playlist',
		'Points' => 'Points',
		'PremiumSubscription' => 'PremiumSubscription',
		'FavouriteLessons' => 'FavouriteLesson',
		'CompletedLessons' => 'CompletedLesson',
		'SubscriptionPurchaseTests' => 'SubscriptionPurchaseTest',
		'CompletedHomeworkPlaylistLessons' => 'CompletedHomeworkPlaylistLesson',
		'CompletedHomeworkPlaylistQuizzes' => 'CompletedHomeworkPlaylistQuiz',
		'ViewedHomeworkPlaylistLessons' => 'ViewedHomeworkPlaylistLesson',
		'ViewedHomeworkPlaylistQuizzes' => 'ViewedHomeworkPlaylistQuiz'
	);

	protected static $indexes = array(
		'UUID' => 'unique("UUID")',
		'Username' => 'unique("Username")',
		'FacebookUserID' => 'unique("FacebookUserID")',
	);

	protected static $belongs_many_many = array(
		'SchoolClasses' => 'SchoolClass',
		'HomeworkPlaylists' => 'HomeworkPlaylist'
	);

	protected static $many_many = array(
		'FavouriteLessons' => 'Lesson',
		'CompletedLessons' => 'Lesson'
	);

	protected static $searchable_fields = array();

	protected static $summary_fields = array(
		'Username' => 'Username',
		'ExamLevel.Name' => 'ExamLevel',
		'ExamCountry.Name' => 'ExamCountry',
		'Ranking' => 'Ranking',
		'TotalPoints' => 'TotalPoints'
	);

	public function getCMSFields()
	{
		$fields = parent::getCMSFields();

		$fields->addFieldToTab('Root.Main', DropdownField::create('SubscriptionStatus', 'Subscription Status', $this->dbObject('SubscriptionStatus')->enumValues())->setEmptyString('(Select one)'), 'Verified');
		$fields->addFieldToTab('Root.Main', DatetimeField::create('SubscriptionExpirationDate', 'Subscription ExpirationDate'), 'Verified');
		$fields->addFieldToTab('Root.Main', DropdownField::create('SubscriptionTypeID', 'Subscription Type', SubscriptionType::get()->map('ID', 'Title'))->setEmptyString('(Select one)'), 'Verified');

		$fields->addFieldsToTab('Root.WondeDetails', array(
			new TextField('WondeID'),
			new TextField('WondeUPI'),
			new TextField('WondeMisID'),
			new TextField('WondeTitle'),
			new TextField('WondeInitials'),
			new TextField('WondeSurname'),
			new TextField('WondeForename'),
			new TextField('WondeMiddleName'),
			new TextField('WondeLegalSurname'),
			new TextField('WondeLegalForname'),
			new TextField('WondeGender'),
			new DatetimeField('WondeDateOfBirth'),
			new DatetimeField('WondeUpdatedAt'),
			new DatetimeField('WondeUpdatedAt'),
		));

		$fields->addFieldsToTab('Root.WestDetails', array(
			new TextField('WestID'),
			new TextField('WestStudentNumber'),
			new TextField('WestDistrictID'),
			new TextField('WestSurname'),
			new TextField('WestForename'),
			new TextField('WestEmail'),
			new DatetimeField('WestCreatedAt'),
			new DatetimeField('WestLastModified'),
		));

		return $fields;
	}

	public function FilterCompletedLessons(){

		$Choice = Session::get('StudentDateFilter');

		if($Choice){

			$FilterDate = date('Y-m-d', strtotime('-'.$Choice.' days'));

			$CompletedLessons = $this->CompletedLessons()->filter(array(
				'Created:GreaterThanOrEqual' => $FilterDate
			))->count();

			return $CompletedLessons;

		}else {

			return $this->CompletedLessons()->Count();

		}
	}

	public function FilterMemberQuizSessions(){

		$Choice = Session::get('StudentDateFilter');

		if($Choice){

			$FilterDate = date('Y-m-d', strtotime('-'.$Choice.' days'));

			$MemberQuizSessions = $this->MemberQuizSessions()->filter(array(
				'Created:GreaterThanOrEqual' => $FilterDate,
				'Completed' => 1
			))->count();

			return $MemberQuizSessions;

		}else {

			return $this->MemberQuizSessions()->filter('Completed', 1)->count();

		}
	}

	public function FilterTotalPoints(){

		$Choice = Session::get('StudentDateFilter');

		if($Choice){

			$FilterDate = date('Y-m-d', strtotime('-'.$Choice.' days'));

			$TotalPoints = Points::get()->filter(array(
				'StudentID' => $this->ID,
				'Created:GreaterThanOrEqual' => $FilterDate
			));

			$total = 0;

			foreach($TotalPoints as $points){
				$total += $points->Points;
			}

			return $total;

		}else {
			return $this->TotalPoints;
		}
	}

	public function FilterPercentageCorrect(){

		$Choice = Session::get('StudentDateFilter');

		if($Choice){

			$FilterDate = date('Y-m-d', strtotime('-'.$Choice.' days'));

			$questionsAnswered = MemberSessionQuestion::get()->filter([
				'MemberQuizSession.StudentID' => $this->ID,
				'Created:GreaterThanOrEqual' => $FilterDate,
				'Answered' => 1
			])->count();

			$questionsCorrect = MemberSessionQuestion::get()->filter([
				'MemberQuizSession.StudentID' => $this->ID,
				'Created:GreaterThanOrEqual' => $FilterDate,
				'Answered' => 1,
				'Correct' => 1
			])->count();

			if ($questionsAnswered == 0) return 100;
			if ($questionsCorrect == 0) return 0;

			$Percentage = ($questionsCorrect / $questionsAnswered) * 100;

			return $Percentage;

		} else {
			return $this->PercentageCorrect;
		}
	}

	public function calculatePercentage()
	{
		$questionsAnswered = MemberSessionQuestion::get()->filter([
			'MemberQuizSession.StudentID' => $this->ID,
			'Answered' => 1
		])->count();

		$questionsCorrect = MemberSessionQuestion::get()->filter([
			'MemberQuizSession.StudentID' => $this->ID,
			'Answered' => 1,
			'Correct' => 1
		])->count();

		if ($questionsAnswered == 0) return 100;
		if ($questionsCorrect == 0) return 0;

		return $this->PercentageCorrect = ($questionsCorrect / $questionsAnswered) * 100;
	}


	public function onBeforeWrite()
	{
		parent::onBeforeWrite();
		$this->TestsCompleted = $this->MemberQuizSessions()->filter('Completed', 1)->count();
		$this->LessonsCompleted = $this->CompletedLessons()->count();
		$this->HomeworkPlaylistLessonsCompleted = $this->CompletedHomeworkPlaylistLessons()->count();
	}

	public function onAfterWrite()
	{
		parent::onAfterWrite();

		if (!$this->UUID) {
			$uuid = Uuid::uuid4();
			$this->UUID = $uuid->toString();
			$this->write();
		}
	}

	public function getBasic()
	{
		$lessons_count = $this->CompletedLessons()->count();

		$config = SiteConfig::current_site_config();

		$FreeSubscriptionExpirationDate = '';
		if(count(Order::get()->filter('StudentID', $this->ID)) > 0){

			$FreeSubscriptionExpirationDate = $config->FreeSubscriptionExpirationDate;
		}

		$user = [
			'id' => $this->UUID,
			'school_id' => $this->School()->ID ? $this->School()->ID : '',
			'firstname' => $this->FirstName,
			'lastname' => $this->Surname,
			'username' => $this->Username ?: $this->Email,
			'email' => $this->Email,
			'date_of_birth' => strtotime($this->DateOfBirth) ?: null,
			'country' => $this->Country()->TwoCharCode,
			'image' => $this->getUserImage(),
			'exam_level' => $this->ExamLevel()->Name,
			'exam_country' => $this->ExamCountry()->Name,
			'ethnicity' => $this->Ethnicity()->Name,
			'gender' => $this->Gender,
			'device' => $this->Device,
			'free_subscription_expiration' => $FreeSubscriptionExpirationDate,
//			'subscription_expiration' => strtotime($this->SubscriptionExpirationDate),
			'results' => [
				'total_points' => $this->TotalPoints ?: 0,
				'questions_answered' => $this->QuestionsAnswered ?: 0,
				'percentage_correct' => $this->PercentageCorrect ?: null,
				'tests_completed' => $this->TestsCompleted ?: 0,
				'lessons_completed' => $this->LessonsCompleted ?: 0,
				'ranking_position' => $this->Ranking,
			],
			'events' => [
				'song_completed' => $this->EventsSongCompleted,
				'quiz_success' => $this->EventsQuizSuccess,
				'share' => $this->EventsShare
			],
			'lessons_listened' => $lessons_count
		];

		return $user;
	}

	public function generateStudents()
	{
		/** @var Member $member */
		foreach (Member::get()->filter('StaffSchoolID', 0) as $member) {

			if (! $member->inGroup('administrators')) {

				$student = new Student;
				$student->ID = $member->ID;
				$student->UUID = $member->UUID;
				$student->Username = $member->Username;
				$student->Gender = $member->Gender;
				$student->DateOfBirth = $member->DateOfBirth;
				$student->TotalPoints = $member->TotalPoints;
				$student->Ranking = $member->Ranking;
				$student->QuestionsAnswered = $member->QuestionsAnswered;
				$student->PercentageCorrect = $member->PercentageCorrect;
				$student->TestsCompleted = $member->TestsCompleted;
				$student->LessonsCompleted = $member->LessonsCompleted;
				$student->CountryID = $member->CountryID;
				$student->ExamLevelID = $member->ExamLevelID;
				$student->ExamCountryID = $member->ExamCountryID;
				$student->EthnicityID = $member->EthnicityID;
				$student->SchoolID = $member->StudentSchoolID;
				$student->write();

				$member->ClassName = 'Student';
				$member->write();
			}
		}
	}

	public function sendSchoolRegistrationEmail($password = null)
	{
		$settings = SiteConfig::current_site_config();

		//create registration email
		$search = array(
			"%%FIRSTNAME%%" => $this->FirstName,
			"%%LASTNAME%%" => $this->Surname,
			"%%EMAIL%%" => $this->Email,
			"%%PASSWORD%%" => $password ?: '***************',
			"%%SCHOOLCLASS%%" => $this->getSchoolClassString(),
			"%%ANDROIDAPPDOWNLOADLINK%%" => $settings->AndroidAppDownloadLink,
			"%%IOSAPPDOWNLOADLINK%%" => $settings->IOSAppDownloadLink,
		);

		$body = str_replace(array_keys($search), array_values($search), $settings->SchoolRegistrationEmailBody);

		$email = new Email();
		$email
			->setFrom($settings->SchoolRegistrationEmailFrom)
			->setTo($this->Email)
			->setSubject($settings->SchoolRegistrationEmailSubject)
			->setTemplate('RegistrationEmail')
			->populateTemplate(array(
				'Content' => $body
			));

		$email->send();
	}

	public function sendFrenchSchoolRegistrationEmail($password = null)
	{
		$settings = SiteConfig::current_site_config();

		//create registration email
		$search = array(
			"%%FIRSTNAME%%" => $this->FirstName,
			"%%LASTNAME%%" => $this->Surname,
			"%%EMAIL%%" => $this->Email,
			"%%PASSWORD%%" => $password ?: '***************',
			"%%SCHOOLCLASS%%" => $this->getSchoolClassString(),
			"%%ANDROIDAPPDOWNLOADLINK%%" => $settings->AndroidAppDownloadLink,
			"%%IOSAPPDOWNLOADLINK%%" => $settings->IOSAppDownloadLink,
		);

		$body = str_replace(array_keys($search), array_values($search), $settings->FrenchSchoolRegistrationEmailBody);

		$email = new Email();
		$email
			->setFrom($settings->FrenchSchoolRegistrationEmailFrom)
			->setTo($this->Email)
			->setSubject($settings->FrenchSchoolRegistrationEmailSubject)
			->setTemplate('RegistrationEmail')
			->populateTemplate(array(
				'Content' => $body
			));

		$email->send();
	}


	public function getSchoolClassString()
	{
		$string = '';

		foreach ($this->SchoolClasses() as $class) {
			$string .= $class->Name . ', ';
		}
	}

	public function generateMissingUsernames()
	{
		increase_time_limit_to(3600);
		increase_memory_limit_to('512M');

		$members = Student::get()->where('Student.Username IS NULL')->sort('Email');

		$usernames = [];
		$unmatched = [];

		foreach ($members as $member) {
			preg_match('/^(.*?)@/', $member->Email, $u);


			if (isset($u[1])) {
				$username = $u[1];

				if (isset($usernames[$username])) {
					$newUsername = $username . $usernames[$username];
					$usernames[$username]++;
					$username = $newUsername;
				} else {
					$usernames[$username] = 1;
				}
			} else {
				$unmatched[] = $member->Email;
			}

			$member->Username = $username;
			$member->write();

		}
	}

	public function canView($member = null)
	{
		$staff = Staff::currentUser();

		if ($staff->Role != 'Teacher') return true;

		if ($this->SchoolClasses()->filter(['StaffID' => $staff->ID])->count()) {
			return true;
		}

		return false;
	}

	public function getUserImage()
	{
		$image = null;

		if ($this->ImageID) {
			$image = $this->Image()->AbsoluteURL;
		}

		if ($image == null && $this->FacebookUserID) {
			$image = 'https://graph.facebook.com/' . $this->FacebookUserID . '/picture?width=300&height=300';
		}

		return $image;
	}
}