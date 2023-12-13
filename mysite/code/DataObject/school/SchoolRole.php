<?php 
class SchoolRole extends DataObject {

    /** @var array  Define the required fields for the SchoolRole table */
    protected static $db = array(
    	'Name' => 'Varchar(100)'
	);
    
    protected static $has_many = array(
    	'Staff' => 'Staff'
	);
}