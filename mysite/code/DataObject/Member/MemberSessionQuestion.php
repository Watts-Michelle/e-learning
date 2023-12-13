<?php 
class MemberSessionQuestion extends DataObject {

	private $created;

    /** @var array  Define the required fields for the MemberSessionQuestion table */
    protected static $db = array(
        'Correct' => 'Boolean',
        'Answered' => 'Boolean',
        'TimeTaken' => 'Int',
        'Number' => 'Int',
        'PointsEarned' => 'Int'
    );
    
    protected static $has_one = array(
        'Question' => 'Question',
        'MemberQuizSession' => 'MemberQuizSession'
    );
    
    protected static $many_many = array(
        'Answers' => 'Answer'
    );
    
    protected static $searchable_fields = array();
    
    protected static $summary_fields = array();

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->makeFieldReadonly('Correct');
        $fields->makeFieldReadonly('Answered');
        $fields->makeFieldReadonly('TimeTaken');
        $fields->makeFieldReadonly('Number');
        $fields->makeFieldReadonly('PointsEarned');
        return $fields;
    }
    
    public function getBasic()
    {
        $question = $this->Question()->getBasic();

        $answers = [];

        foreach ($this->Answers() as $answer) {
            $answers[] = $answer->getBasic();
        }

        $question['question_number'] = $this->Number;
        $question['user_correct'] = $this->Answered ? $this->Correct : null;
        $question['user_answer'] = $answers;

        return $question;
    }

    public function onBeforeWrite()
	{
		if (! $this->ID) {
			$this->created = true;
		}

		parent::onBeforeWrite();
	}

    public function onAfterWrite()
    {
		$questions = $this->MemberQuizSession()->MemberSessionQuestions();

        if (! $this->Number) {

            //we can just take the count as this question was just added
            $this->Number = $questions->Count();
            $this->write();
			return parent::onAfterWrite();
        }

        if (! $this->created) {

        	//this section allows us to calculate the amount of time a quiz took based on the last question answered
			$timeTaken = 0;

			foreach ($questions as $question) {
				$timeTaken += $question->TimeTaken;
			}

			$this->MemberQuizSession()->TimeTaken = $timeTaken;
			$this->MemberQuizSession()->write();

			if ($this->MemberQuizSession()->StudentID) {
				$this->MemberQuizSession()->Student()->QuestionsAnswered = MemberSessionQuestion::get()->filter([
					'MemberQuizSession.StudentID' => $this->MemberQuizSession()->StudentID,
					'Answered' => 1
				])->count();

				$this->MemberQuizSession()->Student()->write();
			}

			$this->MemberQuizSession()->Student()->calculatePercentage();
			$this->MemberQuizSession()->Student()->write();
		}
    }

    public function answer($answers, $correctAnswers = 1)
    {
        $correct = false;
		$correctCount = 0;
        foreach ($answers as $answer) {
            if ($answer->IsCorrect) {
                $correctCount++;
            }
            $this->Answers()->add($answer);
        }

        if ($correctCount == $correctAnswers) {
        	$correct = true;
		}
        
        $this->Answered = true;
        $this->Correct = $correct;

		$this->write();

		$this->MemberQuizSession()->updateCorrect();
		$this->MemberQuizSession()->isComplete();
    }
}