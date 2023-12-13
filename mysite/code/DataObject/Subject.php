<?php

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

class Subject extends DataObject {

    /** @var array  Define the required fields for the Subject table */
    protected static $db = array(
        'Name' => 'Varchar(100)',
		'UUID' => 'Varchar(50)',
		'AndroidSKU' => 'Varchar(200)',
		'IOSSKU' => 'Varchar(200)',
		'CompletionPoints' => 'Int(0)',
		'SubjectSortOrder' => 'Int',
		'Live' => 'Boolean',
		'EventID' => 'Int'
    );
    
    protected static $has_one = array(
        'Icon' => 'Image',
        'Image' => 'Image',
		'ExamLevel' => 'ExamLevel'
	);

	protected static $has_many = array(
		'SubjectArea' => 'SubjectArea',
		//'SAAndroidUniqueSKU' => ['type' => 'unique', 'value' => '"AndroidSKU"'],
		//'SAIOSUniqueSKU' => ['type' => 'unique', 'value' => '"IOSSKU"'],
		'PremiumSubscription' => 'PremiumSubscription'
	);

	protected static $belongs_many_many = array(
		'SubjectGrouping' => 'SubjectGrouping'
	);

	protected static $indexes = array(
		'UUID' => 'unique("UUID")',
		'ASKU' => 'unique("AndroidSKU")',
		'ISKU' => 'unique("IOSSKU")',
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

	public function getTitle()
	{
		return $this->ExamLevel()->Name . ' - ' . $this->Name;
	}

	public function getCMSFields()
	{
		$fields = parent::getCMSFields();

		$fields->replaceField('Icon', $uf = new UploadField('Icon'));
		$uf->setFolderName('subject/icon');

		$fields->replaceField('Image', $uf = new UploadField('Image'));
		$uf->setFolderName('subject/image');

		$fields->addFieldToTab('Root.Lessons', GridField::create('Lessons', 'Lessons', Lesson::get()->filter('SubjectArea.SubjectID', $this->ID)->sort('LessonSortOrder ASC'), $gfc = new GridFieldConfig_RecordEditor(1000)));
		$gfc->addComponent(new GridFieldSortableRows('LessonSortOrder'));

		$fields->addFieldToTab('Root.SubjectArea', GridField::create('SubjectArea', 'Subject Areas', $this->SubjectArea(), $gfc = new GridFieldConfig_RecordEditor(1000)));
		$gfc->addComponent(new GridFieldSortableRows('SortOrder'));

		$fields->addFieldToTab('Root.Quizzes', GridField::create('Quizzes', 'Quizzes', Quiz::get()->filter('SubjectArea.SubjectID', $this->ID), $gfc = new GridFieldConfig_RecordEditor(1000)));
		$gfc->addComponent(new GridFieldSortableRows('QuizSortOrder'));

		$fields->removeByName('UUID');
		$fields->removeByName('PremiumSubscription');

		return $fields;
	}

	public function getBasic()
	{
		date_default_timezone_set('Europe/London');

		$subjectArray = $this->SubjectArea();
		if (! $subjectArray->count()) return false;

		$examLevel = ExamLevel::get()->byID($this->ExamLevelID);

		$image = $this->Image()->exists() ? $this->Image()->ScaleWidth(500)->AbsoluteLink() : null;

		$CurrentUser = CurrentUser::getUser();

		if(strtotime($CurrentUser->SubscriptionExpirationDate) <= strtotime(date('Y-m-d h:i:s', time()))){
			$premimum = 1;
		} else if($CurrentUser->DeviceCampaign == 1){
			$premimum = 1;
		} else {
			$premimum = $this->getHasSubscription($CurrentUser) ? 1 : 0;
		}

		$array = [
			'id' => $this->UUID,
			'event_id' => $this->EventID,
			'name' => $this->Name,
			'level' => $examLevel->Name,
			'subject' => $this->Name,
			'subject_icon' => $this->IconID ? $this->Icon()->AbsoluteURL : null,
			'subject_image' => $image,
			'image' => $image,
			'sort_order' => $this->SubjectSortOrder,
			'premium' => $premimum,
			'ios_sku' => $this->IOSSKU,
			'android_sku' => $this->AndroidSKU,
			'last_updated' => strtotime($this->LastEdited),
			'lesson' => [],
			'quiz' => [],
		];

		foreach (Lesson::get()->filter(['SubjectArea.SubjectID' => $this->ID])->sort(['LessonSortOrder' => 'asc']) as $lesson) {
			$lessonObj = $lesson->getBasic();

			if ($lessonObj) {
				$array['lesson'][] = $lessonObj;
			}
		}

		foreach (Quiz::get()->filter(['SubjectArea.SubjectID' => $this->ID])->sort(['QuizSortOrder' => 'asc']) as $quiz) {
			if ($quizObj = $quiz->getBasic()) {
				$array['quiz'][] = $quizObj;
			}
		}

		$array['lesson_count'] = count($array['lesson']);
		$array['quiz_count'] = count($array['quiz']);

		if (! $array['lesson_count'] && ! $array['quiz_count']) {
			return false;
		}

		return $array;
	}

	public function getPurchase()
	{
		if(CurrentUser::getUser()->DeviceCampaign == 1){
			$premimum = 1;
		} else {
			$premimum = $this->getHasSubscription(CurrentUser::getUser()) ? 1 : 0;
		}

		return [
			'id' => $this->UUID,
			'name' => $this->Name,
//			'premium' => $this->getHasSubscription(CurrentUser::getUser()) ? 1 : 0,
			'premium' => $premimum,
			'ios_sku' => $this->IOSSKU,
			'android_sku' => $this->AndroidSKU,
		];
	}

	public function getHasSubscription(Member $user)
	{
		return $user->SchoolID || $this->PremiumSubscription()->filter('StudentID', $user->ID)->first();
	}

	public function assignPoints(Student $member)
	{
		if (! $member->CompletedLessons()->filter('Lesson.SubjectArea.SubjectID', $this->ID)->first()) return false;

		$points = 0;

		if (! $member->Points()->filter(['SubjectID' => $this->ID, 'Type' => 'PointsCompletedSubject'])->first()) {
			$points = $this->pointsForCompletion();
			$completionPoints = new Points;
			$completionPoints->SubjectID = $this->ID;
			$completionPoints->StudentID = $member->ID;
			$completionPoints->Points = $points;
			$completionPoints->Type = 'PointsCompletedSubject';
			$completionPoints->write();
		}

		return $points;
	}

	private function pointsForCompletion()
	{
		$settings = SiteConfig::current_site_config();

		if ($this->CompletionPoints) {
			$points = $this->CompletionPoints;
		} else {
			$points = $settings->PointsCompletedSubject;
		}

		if ($settings->DoublePoints) {
			$points = $points * 2;
		}

		return $points;
	}
}