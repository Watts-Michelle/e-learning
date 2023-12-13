<?php

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

class ExamLevel extends DataObject {

    /** @var array  Define the required fields for the ExamLevel table */
    protected static $db = array(
		'UUID' => 'Varchar(50)',
        'Name' => 'Varchar(255)',
		'Live' => 'Boolean',
    );
    
    protected static $has_one = array(
        'ExamCountry' => 'ExamCountry',
    );
    
    protected static $has_many = array(
        'Subjects' => 'Subject',
        'Students' => 'Student',
		'ExamLevelMessages' => 'ExamLevelMessage',
		'SchoolYears' => 'SchoolYear'
    );

	protected static $indexes = array(
		'UUID' => 'unique("UUID")',
	);

    protected static $summary_fields = array(
        'Name' => 'Name',
        'ExamCountry.Name' => 'Country'
    );

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
        $fields->removeByName('Students');

		$fields->addFieldToTab('Root.Subjects', GridField::create('Subjects', 'Subjects', Subject::get()->filter('ExamLevelID', $this->ID), $gfc = new GridFieldConfig_RecordEditor()));
		$gfc->addComponent(new GridFieldSortableRows('SubjectSortOrder'));

        return $fields;
    }

    public function getBasic()
	{

		$lesson = 0;
		$quiz = 0;

		foreach ($this->Subjects() as $subject) {
			foreach ($subject->SubjectArea() as $subjectArea) {
				$lesson += $subjectArea->Lessons()->count();
				$quiz += $subjectArea->Quizzes()->count();
			}
		}

		return [
			'id' => $this->UUID,
			'name' => $this->Name,
			'lesson' => $lesson,
			'quiz' => $quiz,
		];
	}
}