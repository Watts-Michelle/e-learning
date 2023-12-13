<?php

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

class MemberQuizSession extends DataObject {

    /** @var array  Define the required fields for the MemberQuizSession table */
    protected static $db = array(
        'Completed' => 'Boolean',
        'UUID' => 'Varchar(50)',
        'CompletedDate' => 'SS_Datetime',
        'TimeTaken' => 'Int',
        'TotalCorrect' => 'Int',
		'Percentage' => 'Decimal(5,2)'
    );
    
    protected static $has_one = array(
        'Quiz' => 'Quiz',
        'Student' => 'Student',
		'HomeworkPlaylist' => 'HomeworkPlaylist'
    );
    
    protected static $has_many = array(
        'MemberSessionQuestions' => 'MemberSessionQuestion',
		'Points' => 'Points'
    );

    protected static $indexes = array(
        'UUID' => 'unique("UUID")',
    );

    protected static $searchable_fields = array();

    protected static $summary_fields = array();

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        if ( ! $this->UUID) {
            $uuid = Uuid::uuid4();
            $this->UUID = $uuid->toString();
            $this->write();
        } else {
			$this->Student()->calculatePercentage();
			$this->Student()->write();
		}

        if ($this->MemberSessionQuestions()->Count() == 0) {
            $this->generateQuestions();
        }
    }

    public function getPercentageCorrect()
    {
    	$quizSession = MemberQuizSession::get()->byID($this->ID);
		$total = $quizSession->TotalCorrect;
		$possible = $this->MemberSessionQuestions()->Count();
        return  ($total / $possible) * 100;
    }
    
    public function getBasic($includeQuestions = true)
    {
        $array = [
            'id' => $this->UUID,
            'created' => strtotime($this->Created),
            'complete' => $this->Completed ? 1 : 0,
            'complete_date' => $this->CompletedDate ? strtotime($this->CompletedDate) : null,
            'time_taken' => $this->TimeTaken,
            'question_count' => $this->MemberSessionQuestions()->Count(),
            'comparison' => null,
            'correct' => $this->MemberSessionQuestions()->filter('Correct', 1)->count(),
            'question' => [],
			'points_earned' => 0
        ];

		if ($includeQuestions) {
			foreach ($this->MemberSessionQuestions()->sort('Number ASC') as $sessionQuestion) {
				$array['question'][] = $sessionQuestion->getBasic();
			}
		} else {
			unset($array['question']);
		}

        foreach (Points::get()->filter(['MemberQuizSessionID' => $this->ID]) as $points) {
        	$array['points_earned'] += $points->Points;
		}
        
        return $array;
    }

    private function generateQuestions()
    {
        /** @var Quiz $quiz */
        $quiz = $this->Quiz();

        $questions = $quiz->Questions()->sort('RAND()')->limit($quiz->QuestionCount);

        foreach ($questions as $question) {
            $memberQuestion = new MemberSessionQuestion;
            $memberQuestion->QuestionID = $question->ID;
            $memberQuestion->MemberQuizSessionID = $this->ID;
            $memberQuestion->write();
        }

    }

    public function isComplete($force = false)
	{
		if ($this->Completed) return true;

		$complete = true;
		$earlyFinish = false;

		if (! $force) {
			if ($this->MemberSessionQuestions()->count() == 0) return false;

			//find questions without an answer
			foreach ($this->MemberSessionQuestions() as $questions) {
				if (!$questions->Answers()->Count()) {
					$complete = false;
					break;
				}
			}
		} else {
			if ($this->MemberSessionQuestions()->filter(['Answered' => 0])->count() > 0) {
				$earlyFinish = true;
			}
		}

		if ($complete) {
			$this->Completed = true;
			$this->CompletedDate = date('Y-m-d H:i:s');
			$this->Percentage = $this->getPercentageCorrect();

			if ($earlyFinish) {
				$this->TimeTaken = $this->Quiz()->Duration;
			}

			$this->write();
			$this->assignPoints();
		}

		return $complete;
	}

	public function assignPoints()
	{
		if (! $this->isComplete()) return false;

		if ($this->MemberSessionQuestions()->count() != $this->MemberSessionQuestions()->filter('Answered', 1)->count()) {
			return false;
		}

		$settings = SiteConfig::current_site_config();

		if (! Points::get()->filter(['MemberQuizSessionID' => $this->ID, 'Type' => 'PointsCompletedQuiz'])->first()) {

			$completionPoints = new Points;
			$completionPoints->MemberQuizSessionID = $this->ID;
			$completionPoints->StudentID = $this->StudentID;
			$completionPoints->Points = $settings->PointsCompletedQuiz;
			$completionPoints->Type = 'PointsCompletedQuiz';

			if ($settings->DoublePoints) {
				$completionPoints->Points = $completionPoints->Points * 2;
			}

			$completionPoints->write();

		}

		if (! Points::get()->filter(['MemberQuizSessionID' => $this->ID, 'Type' => 'QuizResultPoints'])->first()) {

			$bracket = PointQuizBracket::get()->filter(['From:LessThanOrEqual' => $this->Percentage, 'To:GreaterThanOrEqual' => $this->Percentage])->first();

			if ($bracket) {

				$abilityPoints = new Points;
				$abilityPoints->MemberQuizSessionID = $this->ID;
				$abilityPoints->StudentID = $this->StudentID;
				$abilityPoints->Points = $bracket->Points;
				$abilityPoints->PointQuizBracketID = $bracket->ID;
				$abilityPoints->Type = 'QuizResultPoints';

				if ($settings->DoublePoints) {
					$abilityPoints->Points = $abilityPoints->Points * 2;
				}

				$abilityPoints->write();
			}
		}

	}

	public function updateCorrect()
	{
		$this->TotalCorrect = $this->MemberSessionQuestions()->filter('Correct', 1)->Count();
		$this->write();
	}

}