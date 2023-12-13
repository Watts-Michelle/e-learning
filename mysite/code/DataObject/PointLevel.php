<?php 
class PointLevel extends DataObject {

    /** @var array  Define the required fields for the  table */
    protected static $db = array(
    	'Name' => 'Varchar(100)',
		'Description' => 'Varchar(255)',
		'Points' => 'Int'
	);
    
    protected static $has_one = array(
    	'Icon' => 'Image'
	);

    protected static $summary_fields = array(
    	'Name' => 'Name',
		'Description' => 'Description',
		'Points' => 'Points'
	);

	public function getCMSFields()
	{
		$fields = parent::getCMSFields();

		$fields->replaceField('Icon', $uf = new UploadField('Icon'));
		$uf->setFolderName('points/icon');

		return $fields;
	}

	public function getBasic()
	{
		return [
			'title' => $this->Name,
			'description' => $this->Description,
			'points' => (int) $this->Points,
			'icon' => $this->IconID ? $this->Icon()->AbsoluteURL : null,
		];
	}
}