<?php 
class PointQuizBracket extends DataObject {

    /** @var array  Define the required fields for the  table */
    protected static $db = array(
    	'From' => 'Int',
		'To' => 'Int',
		'Points' => 'Int'
	);
    
    protected static $has_one = array(
    	'SiteConfig' => 'SiteConfig'
	);
    
    protected static $has_many = array();

    protected static $summary_fields = array(
		'From', 'To', 'Points'
	);

	public function getCMSFields()
	{
	    $fields = parent::getCMSFields();
	 	$fields->removeByName('SiteConfigID');
	    return $fields;
	}

	public function getTitle()
	{
		return $this->From . '%' . ' - ' . $this->To . '%';
	}

	public function getBasic()
	{
		return [
			'name' => $this->gettingFriendlyName(),
			'points' => (int) $this->Points
		];
	}

	private function gettingFriendlyName()
	{
		if ($this->From == 100) {
			return 'Perfect 100% on a test';
		} else {
			return 'Scoring between ' . $this->From . '% and ' . $this->To . '% on a test';
		}
	}
}