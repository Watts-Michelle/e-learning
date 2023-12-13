<?php 
class SchoolAllowedDomain extends DataObject {

    /** @var array  Define the required fields for the SchoolAllowedDomain table */
    protected static $db = array(
    	'Domain' => 'Varchar(100)'
	);
    
    protected static $has_one = array(
    	'School' => 'School'
	);

	protected static $summary_fields = array(
		'Domain' => 'Domain',
	);

	public function check($email)
	{
		if (preg_match('/\@' . str_replace('.', '\.', $this->Domain) . '/', $email)) {
			return true;
		}

		return false;
	}
}