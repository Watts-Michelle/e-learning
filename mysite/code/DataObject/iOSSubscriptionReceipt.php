<?php 
class iOSSubscriptionReceipt extends DataObject {

    /** @var array  Define the required fields for the ExamCountry table */
    protected static $db = array(
        'Receipt' => 'Varchar',
    );

    protected static $has_one = array(
        'Student' => 'Student'
    );

}