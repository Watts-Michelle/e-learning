<?php 
class Ethnicity extends DataObject {

    /** @var array  Define the required fields for the Ethnicity table */
    protected static $db = array(
    	'Name' => 'Varchar(100)'
	);

	public function canDelete($member = null) {
		return false;
	}

	public function canEdit($member = null) {
		if (! $this->ID) return true;
		return false;
	}

}