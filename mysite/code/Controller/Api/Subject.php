<?php

/**
 * Handle actions related to the logged in user
 *
 * @package Studytracks
 * @subpackage Controllers
 * @author Jonathan Little <jonathan@flipsidegroup.com>
 */
class Subject_Controller extends Base_Controller
{

	/** {@inheritdoc} */
	protected $auth = true;

	/** {@inheritdoc} */
	private static $allowed_actions = array(
		'get',
	);

	/** {@inheritdoc} */
	private static $url_handlers = array(
		'' => 'get',
	);

	public function get(SS_HTTPRequest $request)
	{
		if (! $request->isGET()) return $this->handleError(404);

		/** This will be used in Phase 3!!! */
		//$subjects = SubjectArea::get()->filter(['ExamLevelID' => $this->appUser->ExamLevelID])->sort('SortOrder ASC');

		$subjects = Subject::get()->filter(['ExamLevelID' => $this->appUser->ExamLevelID, 'Live' => 1])->sort('SubjectSortOrder ASC');

		$deletedSubjects = DeletedSubject::get()->filter(['ExamLevelID' => $this->appUser->ExamLevelID]);
		$deletedLessons = DeletedLesson::get();

		$returnData = [
			'subject' => [],
			'deleted_lesson' => [],
			'deleted_subject' => [],
			'subject_group' => [],
		];
		
		foreach ($subjects as $subject) {
			if ($subjectData = $subject->getBasic()) {
				$returnData['subject'][] = $subjectData;
			}
		}
		
		foreach ($deletedLessons as $lesson) {
			$returnData['deleted_lesson'][] = $lesson->Name;
		}
		
		foreach ($deletedSubjects as $subject) {
			$returnData['deleted_subject'][] = $subject->Name;
		}

		$subjectGroups = SubjectGrouping::get()->filter('ExamLevelID', $this->appUser->ExamLevelID);

		foreach ($subjectGroups as $subjectGroup) {
			$returnData['subject_group'][] = $subjectGroup->getBasic();
		}

		return (new JsonApi)->formatReturn($returnData);
	}

}