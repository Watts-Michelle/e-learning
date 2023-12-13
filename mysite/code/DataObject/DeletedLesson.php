<?php 
class DeletedLesson extends DataObject {

    /** @var array  Define the required fields for the DeletedLesson table */
    protected static $db = array('LessonID' => 'Varchar(40)');
    
}