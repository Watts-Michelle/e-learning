<?php

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

class Answer extends DataObject {

    /** @var array  Define the required fields for the Answer table */
    protected static $db = array(
        'IsCorrect' => 'Boolean',
        'UUID' => 'Varchar(50)',
        'Title' => 'Varchar(255)',
		'FullAnswer' => 'HTMLText'
    );
    
    protected static $has_one = array(
    	'Question' => 'Question'
	);

    protected static $belongs_many_many = array(
        'MemberSessionQuestions' => 'MemberSessionQuestion'
    );

    protected static $indexes = array(
        'UUID' => 'unique("UUID")',
    );

    protected static $searchable_fields = array();

    protected static $summary_fields = array(
        'Title' => 'Answer',
        'IsCorrectAnswer' => 'Correct Answer'
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
        $fields->removeByName('SortOrder');
        $fields->removeByName('UUID');
        $fields->removeByName('MemberSessionQuestions');
        return $fields;
    }

    public function getIsCorrectAnswer()
    {
        return $this->IsCorrect ? 'yes' : 'no';
    }

    public function getBasic()
    {
        $array = [
            'id' => $this->UUID,
			'name' => $this->FullAnswer ? $this->renderWith('QuizHtml', ['Content' => $this->FullAnswer, 'AbsoluteLink' => 'http://' . $_SERVER['SERVER_NAME']])->getValue() : $this->Title,
            'sort_order' => $this->SortOrder,
            'correct' => $this->IsCorrect ? 1 : 0
        ];

        return $array;
    }
}