<?php
class Points extends DataObject {

    /** @var array  Define the required fields for the Points table */
    protected static $db = array(
    	'Points' => 'Int',
		'Type'   => "Enum('PointsCompletedQuiz, PointsCompletedLesson, QuizResultPoints, PointsCompletedSubjectArea, PointsCompletedSubject')"
	);

    protected static $has_one = array(
    	'Student' => 'Student',
		'MemberQuizSession' => 'MemberQuizSession',
		'PointQuizBracket' => 'PointQuizBracket',
		'Lesson' => 'Lesson',
		'SubjectArea' => 'SubjectArea',
		'Subject' => 'Subject'
	);

    protected static $has_many = array();

    protected static $searchable_fields = array();

    protected static $summary_fields = array(
    	'Student.Fullname' => 'Name',
		'Created' => 'Date',
		'Points' => 'Points earned',
		'FriendlyType' => 'Reason',
		'Relation' => 'Relation'
	);

	public function getRelation()
	{

		if ($this->MemberQuizSessionID) {
			return $this->MemberQuizSession()->Quiz()->Name;
		} elseif ($this->LessonID) {
			return $this->Lesson()->Name;
		}

	}

	public function getFriendlyType()
	{
		switch($this->Type) {
			case 'PointsCompletedQuiz':
				return 'Completed quiz';
				break;
			case 'PointsCompletedLesson':
				return 'Completed lesson';
				break;
			case 'QuizResultPoints':
				return 'Quiz result';
				break;
		}
	}

	public function onAfterWrite()
	{
		parent::onAfterWrite();

		$total = 0;

		foreach (Points::get()->filter('StudentID', $this->StudentID) as $points) {
			$total += $points->Points;
		}

		$this->Student()->TotalPoints = $total;
		$this->Student()->write();
	}
}
