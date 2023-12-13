<?php 
class SchoolClass extends DataObject {

    /** @var array  Define the required fields for the SchoolClass table */
    protected static $db = array(
    	'Name' => 'Varchar(100)',
		'WondeID' => 'Varchar(255)',
		'WondeMisID' => 'Int',
		'WondeName' => 'Varchar',
		'WondeDescription' => 'Varchar(255)',
		'WondeSubject' => 'Varchar',
		'WondeCreatedAt' => 'SS_DateTime',
		'WondeUpdatedAt' => 'SS_DateTime',
		'WestID' => 'Varchar(255)',
		'WestDistrictID' => 'Varchar(255)',
		'WestName' => 'Varchar',
		'WestCreatedAt' => 'SS_DateTime',
		'WestLastModified' => 'SS_DateTime',
	);
    
    protected static $has_one = array(
    	'School' => 'School',
		'Staff'  => 'Staff'
	);

	protected static $has_many = array(
		'HomeworkPlaylists' => 'HomeworkPlaylist'
	);
    
    protected static $many_many = array(
    	'Students' => 'Student'
	);
    
    protected static $searchable_fields = array();
    
    protected static $summary_fields = array('Name' => 'Name');

	public function canView($member = null)
	{
		return true;
	}

	public function canDelete($member = null)
	{
		return true;
	}

	public function canEdit($member = null)
	{
		return true;
	}

	public function getCMSFields()
	{
		$fields = parent::getCMSFields();

		$fields->addFieldsToTab('Root.WondeDetails', array(
			new TextField('WondeID'),
			new TextField('WondeMisID'),
			new TextField('WondeName'),
			new HtmlEditorField('WondeDescription'),
			new TextField('WondeSubject'),
			new DatetimeField('WondeCreatedAt'),
			new DatetimeField('WondeUpdatedAt'),
		));

		$fields->addFieldsToTab('Root.WestDetails', array(
			new TextField('WestID'),
			new TextField('WestDistrictID'),
			new TextField('WestName'),
			new DatetimeField('WestCreatedAt'),
			new DatetimeField('WestLastModified'),
		));

		return $fields;
	}
}