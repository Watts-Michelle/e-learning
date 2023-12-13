<?php

class Country extends DataObject {

    public static $db = array(
        'Name' => 'Varchar(100)',
        'TwoCharCode' => 'Varchar(2)'
    );

    public static $has_many = array(
        'Students' => 'Student'
    );

    public function getTitle() {
        return $this->Name;
    }

    protected static $summary_fields = array(
        'Name' => 'Name',
        'TwoCharCode' => 'Two Character Code'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('Members');
        return $fields;
    }

}