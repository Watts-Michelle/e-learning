<?php

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

class Playlist extends DataObject {

    /** @var array  Define the required fields for the Playlist table */
    protected static $db = array(
        'Name' => 'Varchar(100)',
        'UUID' => 'Varchar(50)'
    );
    
    protected static $has_one = array(
        'Student' => 'Student'
    );
    
    protected static $many_many = array(
        'Lessons' => 'Lesson'
    );

    protected static $indexes = array(
        'UUID' => 'unique("UUID")',
    );
    
    protected static $searchable_fields = array();
    
    protected static $summary_fields = array();

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        if (!$this->UUID) {
            $uuid = Uuid::uuid4();
            $this->UUID = $uuid->toString();
            $this->write();
        }
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('UUID');
        return $fields;
    }

    public function getBasic()
    {
        $array = [
            'id' => $this->UUID,
            'name' => $this->Name,
            'lesson_count' => $this->Lessons()->count(),
            'lesson' => []
        ];

        foreach ($this->Lessons() as $lesson) {
			$lessonObj = $lesson->getBasic();

			if ($lessonObj) {
				$array['lesson'][] = $lessonObj;
			}
		}

        return $array;
    }
}