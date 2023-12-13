<?php 
class SubscriptionType extends DataObject {

    /** @var array  Define the required fields for the ExamCountry table */
    protected static $db = array(
        'Active' => 'Boolean',
        'Duration' => 'Int',
        'Type' => "Enum('Monthly')",
        'IOSSKU' => 'Varchar(150)',
        'AndroidSKU' => 'Varchar(150)',
    );

    protected static $defaults = array(
        'Active' => 'true',
    );

    protected static $has_many = array(
        'Students' => 'Student'
    );

    protected static $summary_fields = array(
        'Active' => 'Active',
        'Duration' => 'Duration',
        'Type' => 'Type',
        'IOSSKU' => 'IOSSKU',
        'AndroidSKU' => 'AndroidSKU',
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldToTab('Root.Main', DropdownField::create('Type', 'Type', $this->dbObject('Type')->enumValues())->setEmptyString('(Select one)'));
        $fields->addFieldToTab('Root.Main', DropdownField::create('Duration', 'Duration', array_slice(range(0,12), 1, null, true))->setEmptyString('(Select one)'));

        return $fields;
    }

    public function getTitle()
    {
        return $this->Duration.' '.$this->Type;
    }

    public function getBasic()
    {
        $subscription = [
            'duration' => $this->Duration,
            'type' => $this->Type,
            'ios_sku' => $this->IOSSKU,
            'android_sku' => $this->AndroidSKU
        ];

        return $subscription;
    }
}