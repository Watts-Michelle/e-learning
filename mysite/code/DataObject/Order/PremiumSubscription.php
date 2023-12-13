<?php 
class PremiumSubscription extends DataObject {

    /** @var array  Define the required fields for the PremiumSubscription table */
    protected static $db = array(
		'Active' => 'Boolean'
	);
    
    protected static $has_one = array(
    	'Student'  => 'Student',
		'Subject' => 'Subject',
		'Order'   => 'Order'
	);

}