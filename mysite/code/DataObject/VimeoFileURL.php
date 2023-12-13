<?php 
class VimeoFileURL extends DataObject {

    /** @var array  Define the required fields for the VimeoFileURLs table */
    protected static $db = array(
    	'Quality' => 'Varchar(10)',
		'Height' => 'Int',
		'Width' => 'Int',
		'Link' => 'Varchar(255)',
		'Size' => 'Int'
	);
    
    protected static $has_one = array(
    	'VimeoFile' => 'VimeoFile'
	);

    protected static $has_many = array();

    protected static $searchable_fields = array();

    protected static $summary_fields = array(
    	'VimeoFile.Lesson.Name' => 'Lesson',
		'Height' => 'Height',
	);
}