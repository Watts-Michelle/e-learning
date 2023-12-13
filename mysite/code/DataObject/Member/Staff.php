<?php 
class Staff extends Member {

    /** @var array  Define the required fields for the Staff table */
    protected static $db = array(
    	'Role' => "Enum('Admin,Staff,Teacher')",
		'WondeID' => 'Varchar(255)',
		'WondeUPI' => 'Varchar(255)',
		'WondeMisID' => 'Int',
		'WondeTitle' => 'Varchar',
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
		'WestDistrictID' => 'Varchar(255)',
		'WestSurname' => 'Varchar',
		'WestForename' => 'Varchar',
		'WestEmail' => 'Varchar',
		'WestCreatedAt' => 'SS_DateTime',
		'WestLastModified' => 'SS_DateTime',
	);
    
    protected static $has_one = array(
    	'School' => 'School',
	);
    
    protected static $has_many = array(
		'SchoolClass' => 'SchoolClass',
	);
    
    protected static $searchable_fields = array();
    
    protected static $summary_fields = array();

	public function generateStaff()
	{
		foreach (Member::get()->filter('StaffSchoolID:GreaterThan', 0) as $member) {

			$staff = new Staff;
			$staff->ID = $member->ID;
			$staff->SchoolID = $member->StaffSchoolID;
			$staff->Role = $member->SchoolRoleID;
			$staff->write();

			$member->ClassName = 'Staff';
			$member->write();
		}
	}

	public function getCMSFields()
	{
		$fields = parent::getCMSFields();

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
			new TextField('WondeRole'),
		));

		return $fields;
	}
}