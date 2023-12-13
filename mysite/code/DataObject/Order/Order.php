<?php 
class Order extends DataObject {

    /** @var array  Define the required fields for the Order table */
    protected static $db = array(
    	'OrderNumber' => 'Varchar(150)',
		'IOSSKU' => 'Varchar(150)',
		'AndroidSKU' => 'Varchar(150)',
		'Platform' => "Enum('ios, android')"
	);
    
    protected static $has_one = array(
    	'Student' => 'Student'
	);
    
    protected static $has_many = array(
    	'PremiumSubscription' => 'PremiumSubscription'
	);

	protected static $indexes = array(
		'OrderNumber' => 'unique("OrderNumber")',
	);

	private $idVal;

	public function onBeforeWrite()
	{
		parent::onBeforeWrite();

		if ($this->ID) {
			$this->idVal = $this->ID;
		}

		if ($this->IOSSKU) {
			$this->Platform = 'ios';
		} else {
			$this->Platform = 'android';
		}
	}

	public function onAfterWrite()
	{
		parent::onAfterWrite();

		if (! $this->idVal) {

			if ($this->Platform == 'ios') {
				$this->generateFromSKU($this->IOSSKU, 'IOS');
			} else {
				$this->generateFromSKU($this->AndroidSKU, 'Android');
			}

		}

	}

	private function generateFromSKU($sku, $type)
	{
		if ($subjectArea = Subject::get()->filter($type . 'SKU', $sku)->first()) {
			$this->generatePremium($subjectArea);
		} else {
			$subjectGroup = SubjectGrouping::get()->filter($type . 'SKU', $sku)->first();

			if (empty($subjectGroup)) {
				$this->delete();
				throw new Exception('SKU not found');
			}

			foreach ($subjectGroup->Subjects() as $subjectArea) {
				$this->generatePremium($subjectArea);
			}
		}
	}

	private function generatePremium(Subject $subjectArea)
	{
		$premium = new PremiumSubscription();
		$premium->Active = 1;
		$premium->StudentID = CurrentUser::getUserID();
		$premium->SubjectID = $subjectArea->ID;
		$premium->OrderID = $this->ID;
		$premium->write();
	}

}