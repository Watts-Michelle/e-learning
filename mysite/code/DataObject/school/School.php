<?php 
class School extends DataObject {

    /** @var array  Define the required fields for the School table */
    protected static $db = array(
    	'Name' => 'Varchar(100)',
    	'StudentCap' => 'Int',
    	'StaffCap' => 'Int',
		'BackgroundColour' => 'Color',
		'Suspended' => 'Boolean',
		'SuspensionReason' => 'Varchar(255)',
		'SuspensionDate' => 'SS_Datetime',
		'French' => 'Boolean',
		'WondeID' => 'Varchar(255)',
		'WondeUrn' => 'Int',
		'WondePhaseOfEducation' => 'Varchar(255)',
		'WondeLACode' => 'Int',
		'WondeTimezone' => 'Varchar',
		'WondeMis' => 'Varchar',
		'WondeAddressLine1' => 'Varchar(255)',
		'WondeAddressLine2' => 'Varchar(255)',
		'WondeAddressLineTown' => 'Varchar(255)',
		'WondeAddressPostcode' => 'Varchar(255)',
		'WondeAllowsWriteback' => 'Boolean',
		'WondeHasTimetables' => 'Boolean',
		'WondeHasLessonAttendance' => 'Boolean',
		'WestID' => 'Varchar(255)',
		'WestDistrictID' => 'Varchar(255)',
		'WestStateID' => 'Varchar(255)',
		'WestSchoolNumber' => 'Varchar(255)',
		'WestLowGrade' => 'Varchar',
		'WestHighGrade' => 'Varchar',
		'WestAddressLine1' => 'Varchar(255)',
		'WestAddressLineCity' => 'Varchar(255)',
		'WestAddressLineState' => 'Varchar(255)',
		'WestAddressZip' => 'Varchar(255)',
	);
    
    protected static $has_one = array(
		'SuspensionUser' => 'Member',
    	'Logo' => 'Image',
		'ExamCountry' => 'ExamCountry'
	);
    
    protected static $has_many = array(
    	'Students' => 'Student',
    	'Staff' => 'Staff',
		'SchoolClasses' => 'SchoolClass',
		'SchoolAllowedDomains' => 'SchoolAllowedDomain',
		'HomeworkPlaylists' => 'HomeworkPlaylist',
		'SchoolYears' => 'SchoolYear'
	);
    
    protected static $searchable_fields = array();
    
    protected static $summary_fields = array(
    	'Name' => 'Name',
		'IsSuspended' => 'Suspended'
	);


	private static $better_buttons_actions = array (
		'SendStaffEmail',
	);

	/**
	 * Button to send out staff emails to Wonde employees.
	 * Only visible if school is a Wonde school.
	 * @return mixed
	 */
	public function getBetterButtonsUtils()
	{
		$fields = parent::getBetterButtonsUtils();

		if($this->WondeID) {

			if (!$this->SendStaffEmail) {

				$fields->push(BetterButtonCustomAction::create('SendStaffEmail', 'Send Staff Email'));
			}
		}

		return $fields;
	}

	public function SendStaffEmail()
	{
		$this->SendStaffEmail = true;
		$this->write();

		$WondeController = new Wonde_Controller();
		$WondeController->sendStaffRegistrationEmails($this->WondeID);
	}

	public function getStaffCount()
	{
		return $this->Staff()->count();
	}

	public function getStudentCount()
	{
		return $this->Students()->count();
	}

	public function canAddStudent()
	{
		if ($this->Students()->count() < $this->StudentCap || $this->StudentCap == 0) {
			return true;
		}
 
		return false;
	}

	public function canAddStaff()
	{
		if ($this->Staff()->count() < $this->StaffCap || $this->StaffCap == 0) {
			return true;
		}

		return false;
	}

	public function getIsSuspended()
	{
		return $this->Suspended ? 'yes' : 'no';
	}

	public function onBeforeWrite()
	{
		$suspended = false;

		if ($this->ID) {
			$current = School::get()->byID($this->ID);

			if ($current->Suspended) {
				$suspended = true;
			}
		}

		if ($this->Suspended == 0 && $suspended == true) {
			$this->SuspensionUserID = 0;
			$this->SuspensionDate = null;
		} elseif ($this->Suspended == 1 && $suspended == false) {
			$this->SuspensionUserID = Member::currentUserID();
			$this->SuspensionDate = date('Y-m-d H:i:s');
		}

		parent::onBeforeWrite();
	}

	public function getCMSFields()
	{
		$fields = parent::getCMSFields();

		$fields->addFieldToTab('Root.Main', CheckboxField::create('French', 'French School?'), 'Name');

		$fields->replaceField('Logo', $uf = new UploadField('Logo'));
		$uf->setFolderName('school/logo');
		$fields->addFieldToTab('Root.Main', ReadonlyField::create('SuspensiosnUserID', 'Suspended by', $this->SuspensionUserID ? $this->SuspensionUser()->FirstName . ' ' . $this->SuspensionUser()->Surname : 'not suspended'), 'SuspensionDate');
		$fields->makeFieldReadonly('SuspensionDate');
		$fields->removeByName('SuspensionUserID');

		$fields->addFieldsToTab('Root.WondeDetails', array(
			new TextField('WondeID'),
			new TextField('WondeUrn'),
			new TextField('WondePhaseOfEducation'),
			new TextField('WondeLACode'),
			new TextField('WondeTimezone'),
			new TextField('WondeMis'),
			new TextField('WondeAddressLine1'),
			new TextField('WondeAddressLine2'),
			new TextField('WondeAddressLineTown'),
			new TextField('WondeAddressPostcode'),
			new TextField('WondeAllowsWriteback'),
			new TextField('WondeHasTimetables'),
			new TextField('WondeHasLessonAttendance'),
		));

		$fields->addFieldsToTab('Root.WestDetails', array(
			new TextField('WestID'),
			new TextField('WestDistrictID'),
			new TextField('WestStateID'),
			new TextField('WestSchoolNumber'),
			new TextField('WestLowGrade'),
			new TextField('WestHighGrade'),
			new TextField('WestAddressLine1'),
			new TextField('WestAddressLineCity'),
			new TextField('WestAddressLineState'),
			new TextField('WestAddressZip'),
		));

		return $fields;
	}
}