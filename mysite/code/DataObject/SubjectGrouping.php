<?php 
class SubjectGrouping extends DataObject {

    /** @var array  Define the required fields for the SubjectGrouping table */
    protected static $db = array(
    	'Name' => 'Varchar(100)',
    	'AndroidSKU' => 'Varchar(200)',
    	'IOSSKU' => 'Varchar(200)'
	);
    
    protected static $has_one = array(
    	'ExamLevel' => 'ExamLevel'
	);
    
    protected static $many_many = array(
    	'Subjects' => 'Subject'
	);
    
    protected static $searchable_fields = array();
    
    protected static $summary_fields = array(
    	'Name' => 'Name',
		'ExamLevel.Name' => 'Exam Level'
	);

	protected static $indexes = array(
		'AGSKU' => 'unique("AndroidSKU")',
		'IGSKU' => 'unique("IOSSKU")',
	);

	public function getBasic()
	{
		$subjects = [];

		foreach ($this->Subjects() as $subject) {
			$subjects[] = $subject->UUID;
		}

		$array = [
			'ios_sku' => $this->IOSSKU,
			'android_sku' => $this->AndroidSKU,
			'subjects' => $subjects
		];

		return $array;
	}

	public function getPurchase()
	{

		$count = 0;
		$premium = 0;

		foreach ($this->Subjects() as $subject) {
			$count++;
			if ($subject->getHasSubscription(CurrentUser::getUser())) $premium++;
		}

		return [
			'ios_sku' => $this->IOSSKU,
			'android_sku' => $this->AndroidSKU,
			'name' => $this->Name,
			'premium' => $count == $premium ? 1 : 0,
		];
	}
}