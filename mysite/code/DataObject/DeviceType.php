<?php 
class DeviceType extends DataObject {

    /** @var array  Define the required fields for the ExamCountry table */
    protected static $db = array(
        'Name' => 'Varchar(255)',
    );

    protected static $has_one = array(
        'DeviceCampaign' => 'DeviceCampaign'
    );

    protected static $has_many = array(
        'Students' => 'Student'
    );

    public function getTitle() {
        return $this->Name;
    }

    protected static $summary_fields = array(
        'Name' => 'Name',
    );

}