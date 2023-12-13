<?php 
class DeletedSubject extends DataObject {

    /** @var array  Define the required fields for the DeletedSubject table */
    protected static $db = array(
        'SubjectID' => 'Varchar(40)'
    );

    protected static $has_one = array(
        'ExamLevel' => 'ExamLevel'
    );
}