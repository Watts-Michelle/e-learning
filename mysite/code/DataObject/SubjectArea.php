<?php

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

class SubjectArea extends DataObject {

    /** @var array  Define the required fields for the Topic table */
    protected static $db = array(
        'UUID' => 'Varchar(50)',
        'Title' => 'Varchar(100)',
        'SortOrder' => 'Int',
		'CompletionPoints' => 'Int(0)',
    );
    
    protected static $has_one = array(
        'Image' => 'Image',
        'Subject' => 'Subject',
    );
    
    protected static $has_many = array(
        'Quizzes' => 'Quiz',
        'Lessons' => 'Lesson',
    );

    protected static $indexes = array(
        'UUID' => 'unique("UUID")'
    );
    
    protected static $searchable_fields = array();
    
    protected static $summary_fields = array(
        'Title' => 'Title',
        'Subject.Title' => 'Subject',
        'Subject.ExamLevel.Title' => 'Exam Level',
        'Subject.ExamLevel.ExamCountry.Name' => 'Country'
    );

	public function onBeforeWrite()
	{
		parent::onBeforeWrite();

		if ($this->SKU) {
			if (SubjectArea::get()->filter('SKU', $this->SKU)->first()) throw new Exception('Duplicate SKU');
			if (SubjectGrouping::get()->filter('SKU', $this->SKU)->first()) throw new Exception('Duplicate SKU with group');
		}


	}

	public function getName()
	{
		return $this->Subject()->Title . ' - ' . $this->Title;
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
        $fields->removeByName('SortOrder');

		$fields->replaceField('Image', $uf = new UploadField('Image'));
		$uf->setFolderName('subject/image');

		$fields->replaceField('Lessons', GridField::create('Lessons', 'Lessons', Lesson::get()->filter('SubjectArea.SubjectID', $this->ID)->sort('LessonSortOrder ASC'), $gfc = new GridFieldConfig_RecordEditor(1000)));
		$gfc->addComponent(new GridFieldSortableRows('LessonSortOrder'));

		$fields->replaceField('Quizzes', GridField::create('Quizzes', 'Quizzes', Quiz::get()->filter('SubjectArea.SubjectID', $this->ID), $gfca = new GridFieldConfig_RecordEditor(1000)));
		$gfca->addComponent(new GridFieldSortableRows('QuizSortOrder'));

        return $fields;
    }

    public function getBasic()
    {
        $array = [
            'id' => $this->UUID,
            'name' => $this->Title,
            'image' => $this->ImageID ? $this->Image()->AbsoluteURL : null,
            'level' => $this->ExamLevel()->Name,
            'subject' => $this->Subject()->Name,
            'subject_icon' => $this->Subject()->IconID ? $this->Subject()->Icon()->AbsoluteURL : null,
			'subject_image' => $this->Subject()->ImageID ? $this->Subject()->Image()->ScaleWidth($this->Subject()->Image()->getWidth() - 2)->AbsoluteLink() : null,
            'sort_order' => $this->SortOrder,
			'last_updated' => strtotime($this->LastEdited),
            'lesson' => [],
            'quiz' => [],
        ];

        foreach ($this->Lessons() as $lesson) {
            $lessonObj = $lesson->getBasic();

			if ($lessonObj) {
				$array['lesson'][] = $lessonObj;
			}
        }

        foreach ($this->Quizzes() as $quiz) {
        	if ($quizObj = $quiz->getBasic())
            $array['quiz'][] = $quizObj;
        }

        $array['lesson_count'] = count($array['lesson']);
        $array['quiz_count'] = count($array['quiz']);

        return $array;
    }

	public function assignPoints(Student $member)
	{
		if (! $member->CompletedLessons()->filter('SubjectAreaID', $this->ID)->first()) return false;

		$points = 0;

		if (! $member->Points()->filter(['SubjectAreaID' => $this->ID, 'Type' => 'PointsCompletedSubjectArea'])->first()) {

			$points = $this->pointsForCompletion();

			$completionPoints = new Points;
			$completionPoints->SubjectAreaID = $this->ID;
			$completionPoints->StudentID = $member->ID;
			$completionPoints->Points = $points;
			$completionPoints->Type = 'PointsCompletedSubjectArea';
			$completionPoints->write();
		}

		$completed = 0;
		$lessonCount = SubjectArea::get()->filter('SubjectID', $this->SubjectID)->count();

		foreach (SubjectArea::get()->filter('SubjectID', $this->SubjectID) as $subjectArea) {
			if ($member->Points()->filter(['SubjectAreaID' => $subjectArea->ID, 'Type' => 'PointsCompletedSubjectArea'])->first()) {
				$completed++;
			}
		}

		if ($completed == $lessonCount) {
			$points += $this->Subject()->assignPoints($member);
		}

		return $points;
	}

	private function pointsForCompletion()
	{
		$settings = SiteConfig::current_site_config();

		if ($this->CompletionPoints) {
			$points = $this->CompletionPoints;
		} else {
			$points = $settings->PointsCompletedSubjectArea;
		}

		if ($settings->DoublePoints) {
			$points = $points * 2;
		}

		return $points;
	}

}