<?php

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

/** TODO add handling for multiple required answers */
class Question extends DataObject {

    /** @var array  Define the required fields for the Question table */
    protected static $db = array(
        'Title' => 'Varchar(255)',
		'FullQuestion' => 'HTMLText',
        'AcceptMultipleAnswers' => 'Boolean',
        'UUID' => 'Varchar(50)',
    );
    
    protected static $has_one = array(
        'Quiz' => 'Quiz'
    );
    
    protected static $has_many = array(
        'Answers' => 'Answer',
        'MemberSessionQuestions' => 'MemberSessionQuestion'
    );

    protected static $indexes = array(
        'UUID' => 'unique("UUID")',
    );

    protected static $searchable_fields = array();

    protected static $summary_fields = array(
        'Title' => 'Question',
        'Quiz.Name' => 'Quiz',
        'AnswerCount' => 'Answers'
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
        $fields->removeByName('UUID');
        $fields->removeByName('MemberSessionQuestions');
        return $fields;
    }

    public function getAnswerCount()
    {
        return $this->Answers()->count();
    }

    public function getBasic()
    {
        $array = [
            'id' => $this->UUID,
            'name' => $this->FullQuestion ? $this->renderWith('QuizHtml', ['Content' => $this->FullQuestion, 'AbsoluteLink' => 'http://' . $_SERVER['SERVER_NAME']])->getValue() : $this->Title,
            'accept_multiple' => $this->AcceptMultipleAnswers,
            'question_number' => $this->SortOrder,
            'user_correct' => null,
            'answer' => [],
            'user_answer' => [],
            'user_points' => null,
        ];
        
        foreach ($this->Answers()->sort('RAND()') as $answer) {
            $array['answer'][] = $answer->getBasic();
        }

        return $array;
    }
}