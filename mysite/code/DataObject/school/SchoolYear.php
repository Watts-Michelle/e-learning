<?php 
class SchoolYear extends DataObject {

    /** @var array  Define the required fields for the SchoolClass table */
    protected static $db = array(
		'WondeID' => 'Varchar(255)',
		'WondeMisID' => 'Int',
		'WondeName' => 'Varchar',
		'WondeCode' => 'Varchar',
		'WondeType' => 'Varchar',
		'WondeDescription' => 'Varchar(255)',
		'WondeNotes' => 'Varchar(255)',
		'WondeCreatedAt' => 'SS_DateTime',
		'WondeUpdatedAt' => 'SS_DateTime'
	);
    
    protected static $has_one = array(
    	'School' => 'School',
		'ExamLevel'  => 'ExamLevel'
	);

    protected static $summary_fields = array(
		'School' => 'School',
		'ExamLevel' => 'ExamLevel'
	);

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
			new TextField('WondeNotes'),
			new DatetimeField('WondeCreatedAt'),
			new DatetimeField('WondeUpdatedAt'),
		));

		return $fields;
	}
}