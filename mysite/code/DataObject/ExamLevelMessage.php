<?php

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

class ExamLevelMessage extends DataObject {

    /** @var array  Define the required fields for the ExamLevel table */
    protected static $db = array(
		'UUID' => 'Varchar(50)',
		'Message' => 'HTMLText',
		'MessageStartDate' => 'SS_DateTime',
		'MessageEndDate' => 'SS_DateTime',
		'MessageLinkType' => "Enum('Subjects, Lessons, Quizzes, Information Page')",
		'SubjectsList' => 'Varchar(255)',
		'LessonsList' => 'Varchar(255)',
		'QuizzesList' => 'Varchar(255)',
		'InformationPage' => 'Varchar(255)'
    );
    
    protected static $has_one = array(
        'ExamLevel' => 'ExamLevel',
		'MessageImage' => 'Image',
    );
    
	protected static $indexes = array(
		'UUID' => 'unique("UUID")',
	);

    protected static $summary_fields = array(
        'Message' => 'Message',
		'MessageStartDate' => 'MessageStartDate',
		'MessageEndDate' => 'MessageEndDate'
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

		$lessonsArray = array();
		$quizArray = array();

		foreach ($this->ExamLevel()->Subjects() as $subject) {

			$lessons = Lesson::get()->filter('SubjectArea.SubjectID', $subject->ID)->sort('LessonSortOrder ASC');

			foreach($lessons as $lesson){
				$lessonsArray[$lesson->ID]=$lesson->Title;
			}

			$quizzes = Quiz::get()->filter(['SubjectArea.SubjectID' => $subject->ID])->sort(['QuizSortOrder' => 'asc']);

			foreach($quizzes as $quiz){
				$quizArray[$quiz->ID]=$quiz->Title;
			}
		}

		$fields->addFieldToTab('Root.Main',	HtmlEditorField::create('Message')->setRows(3));
		$fields->addFieldToTab('Root.Main',	UploadField::create('MessageImage'));
		$fields->addFieldToTab('Root.Main',	DateField::create('MessageStartDate'));
		$fields->addFieldToTab('Root.Main',	DateField::create('MessageEndDate'));
		$fields->addFieldToTab('Root.Main', DropdownField::create('MessageLinkType', 'Link Type', $this->dbObject('MessageLinkType')->enumValues()));
		$fields->addFieldToTab('Root.Main', DropdownField::create('SubjectsList', 'Subjects', $this->ExamLevel()->Subjects()->map('ID', 'Title'))->displayIf('MessageLinkType')->isEqualTo('Subjects')->end());
		$fields->addFieldToTab('Root.Main', DropdownField::create('LessonsList', 'Lessons', $lessonsArray)->displayIf('MessageLinkType')->isEqualTo('Lessons')->end());
		$fields->addFieldToTab('Root.Main', DropdownField::create('QuizzesList', 'Quizzes', $quizArray)->displayIf('MessageLinkType')->isEqualTo('Quizzes')->end());
		$fields->addFieldToTab('Root.Main', DropdownField::create('InformationPage', 'Information Page', Page::get()->filter(['ProvideInAPI' => 1])->map('ID', 'Title'))->displayIf('MessageLinkType')->isEqualTo('Information Page')->end());

        return $fields;
    }

    public function getBasic()
	{
		$SubjectLink = "";
		$LessonLink = "";
		$QuizLink = "";
		$internalLink = "";

		if($this->MessageLinkType == 'Subjects'){
			$SubjectLink = $this->SubjectsList ? Subject::get()->byID($this->SubjectsList)->UUID : "";
		}
		if($this->MessageLinkType == 'Lessons'){
			$LessonLink = $this->LessonsList ? Lesson::get()->byID($this->LessonsList)->UUID : "";
		}
		if($this->MessageLinkType == 'Quizzes'){
			$QuizLink = $this->QuizzesList ? Quiz::get()->byID($this->QuizzesList)->UUID : "";
		}

		if($this->MessageLinkType == 'Information Page'){
			$internalLink = $this->InformationPage ? Page::get()->byId($this->InformationPage)->Safename : "";
		}

		$image = $this->MessageImage()->exists() ? $this->MessageImage()->ScaleWidth(720)->AbsoluteLink() : "";

		$lesson = 0;
		$quiz = 0;

		foreach ($this->ExamLevel()->Subjects() as $subject) {
			foreach ($subject->SubjectArea() as $subjectArea) {
				$lesson += $subjectArea->Lessons()->count();
				$quiz += $subjectArea->Quizzes()->count();
			}
		}

		return [
			'id' => $this->UUID,
			'name' => $this->ExamLevel()->Name,
			'lesson' => $lesson,
			'quiz' => $quiz,
			'exam_level_id' => $this->ExamLevel()->UUID,
			'exam_level_name' => $this->ExamLevel()->Name,
			'message' => strip_tags($this->Message),
			'message_subject_link' => $SubjectLink,
			'message_lesson_link' => $LessonLink,
			'message_quiz_link' => $QuizLink,
			'message_information_page' => $internalLink,
			'message_image' => $image,
			'start_date' => !empty($this->MessageStartDate) ? date('d-m-Y', strtotime($this->MessageStartDate)) : null,
			'end_date' => !empty($this->MessageEndDate) ? date('d-m-Y', strtotime($this->MessageEndDate)) : null,
		];
	}



}