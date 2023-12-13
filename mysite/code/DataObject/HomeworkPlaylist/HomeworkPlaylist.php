<?php

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

class HomeworkPlaylist extends DataObject {

    /** @var array  Define the required fields for the Topic table */
    protected static $db = array(
        'UUID' => 'Varchar(50)',
        'Title' => 'Varchar(100)',
        'Active' => 'Boolean',
        'Deadline' => 'SS_DateTime'
    );

	protected static $has_one = array(
		'SchoolClass' => 'SchoolClass',
        'School' => 'School'
	);

    protected static $many_many = array(
        'Lessons' => 'Lesson',
        'Quizzes' => 'Quiz',
        'Students' => 'Student'
    );

    protected static $indexes = array(
        'UUID' => 'unique("UUID")'
    );

    protected static $many_many_extraFields = array(
        'Students' => array('Viewed' => 'Boolean')
    );
    
    protected static $searchable_fields = array();
    
	public function getName()
	{
		return $this->Title;
	}

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
        $viewed = '0';
        if($this->Students()->filter('ID', CurrentUser::getUserID())->first()){
            $viewed = $this->Students()->filter('ID', CurrentUser::getUserID())->first()->Viewed;
        }

        $array = [
            'id' => $this->UUID,
            'name' => $this->Title,
            'class' => $this->SchoolClass()->Name,
            'viewed' => $viewed,
            'lessons' => [],
            'quizzes' => []
        ];

        foreach($this->Lessons() as $lesson){
            if ($lesson->getPlaylistLesson()){
                $array['lessons'][] = $lesson->getPlaylistLesson($this->ID);
            }
        }

        foreach($this->Quizzes() as $quiz){
            if($quiz->getBasic()){
                $array['quizzes'][] = $quiz->getBasic($this->UUID);
            }
        }

        return $array;
    }
}