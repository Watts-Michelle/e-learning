<?php

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

class Quiz extends DataObject {

    /** @var array  Define the required fields for the Quiz table */
    protected static $db = array(
        'UUID' => 'Varchar(50)',
        'Name' => 'Varchar(100)',
        'QuizSortOrder' => 'Int',
        'QuestionCount' => 'Int(12)',
		'Duration' => 'Int'
    );
    
    protected static $has_one = array(
        'SubjectArea' => 'SubjectArea',
    );
    
    protected static $has_many = array(
        'Questions' => 'Question',
        'MemberQuizSessions' => 'MemberQuizSession',
    );

	protected static $belongs_many_many = array(
		'HomeworkPlaylists' => 'HomeworkPlaylist'
	);

    protected static $indexes = array(
        'UUID' => 'unique("UUID")',
    );

    protected static $summary_fields = array(
        'Name' => 'Name',
		'SubjectArea.Subject.ExamLevel.Name' => 'Exam Level',
		'SubjectArea.Subject.Name' => 'Subject',
		'SubjectArea.Title' => 'Subject Area',
        'TotalQuestionCount' => 'Total Question Count',
		'SessionSize' => 'Session Size',
		'HowSessionCalculated' => 'Reason for Size'
    );

	protected static $searchable_fields = array(
		'Name' => array(
			'title' => 'Name'
		),
		'SubjectAreaID' => array(
			'title' => 'Subject Area'
		),
		'SubjectArea.SubjectID' => array(
			'title' => 'Subject'
		),
		'SubjectArea.Subject.ExamLevelID' => array(
			'title' => 'Exam Level'
		)
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
		return $this->Name;
	}


	public function getTotalQuestionCount()
    {
        return $this->Questions()->count();
    }

    public function getSessionSize()
	{
		$settings = SiteConfig::current_site_config();

		$sessionSize = $this->QuestionCount ?: $settings->DefaultQuestionCount;

		if ($this->getTotalQuestionCount() < $sessionSize || $sessionSize == 0) {
			return $this->getTotalQuestionCount();
		}

		return $sessionSize;
	}

	public function getHowSessionCalculated()
	{
		$settings = SiteConfig::current_site_config();

		if ($this->QuestionCount) {
			$type = 'set in quiz';
			$sessionSize = $this->QuestionCount;
		} else {
			$type = 'limited by default';
			$sessionSize = $settings->DefaultQuestionCount;
		}

		if ($this->getTotalQuestionCount() < $sessionSize || $sessionSize == 0) {
			return 'limited by number of questions';
		}

		return $type;
	}

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('UUID');
        $fields->removeByName('SortOrder');
        $fields->removeByName('MemberQuizSessions');
		$fields->replaceField('SubjectAreaID', DropdownField::create('SubjectAreaID', 'Subject Area', SubjectArea::get()->sort(['Subject.ExamLevel.Name' => 'ASC', 'Subject.Name' => 'ASC', 'Title' => 'ASC'])->map('ID', 'Name'), $this->SubjectAreaID));
		$fields->replaceField('Duration', NumericField::create('Duration', 'Time allowed for test', $this->Duration));
		$fields->makeFieldReadonly('QuestionCount');

		if ($this->ID) {
			$fields->replaceField('Questions', $gf = GridField::create('Questions', 'Questions', $this->Questions(), $gfc = new GridFieldConfig_RecordEditor()));
			$importer = new MyGridFieldImporter('before');
			$quizImporter = new QuizImporter(new Question());
			$quizImporter->setSource(new CsvBulkLoaderSource());
			$quizImporter->setQuiz($this);
			$importer->setLoader($quizImporter);
			$gfc->addComponent($importer);
		}

        return $fields;
    }

    public function getBasic($HomeworkPlaylistUID = null)
    {
		$settings = SiteConfig::current_site_config();

		$session = $this->MemberQuizSessions()->filter('StudentID', CurrentUser::getUserID())->sort('Created DESC')->first();

		$Viewed = '0';
		$Completed = '0';

		if($HomeworkPlaylistUID){
			if($HomeworkPlaylist = HomeworkPlaylist::get()->filter('UUID', $HomeworkPlaylistUID)->first()) {

				$session = $this->MemberQuizSessions()->filter(array('StudentID' => CurrentUser::getUserID(), 'HomeworkPlaylistID' => $HomeworkPlaylist->ID))->sort('Created DESC')->first();

				if($ViewHomeworkPlaylistQuiz = ViewedHomeworkPlaylistQuiz::get()
					->filter(array('HomeworkPlaylistID' => $HomeworkPlaylist->ID, 'QuizID' => $this->ID, 'StudentID' => CurrentUser::getUserID()))
					->first()){
					$Viewed = $ViewHomeworkPlaylistQuiz->Viewed;
				}

				if($CompletedHomeworkPlaylistQuiz = CompletedHomeworkPlaylistQuiz::get()
					->filter(array('HomeworkPlaylistID' => $HomeworkPlaylist->ID, 'QuizID' => $this->ID, 'StudentID' => CurrentUser::getUserID()))
					->first()){
					$Completed = $CompletedHomeworkPlaylistQuiz->Completed;
				}
			}
		}

		$questionsAnswered = 0;

        if ($session) {
        	if (!$session->Completed) {
				$nextQuestionObj = $session->MemberSessionQuestions()->filter('Answered', 0)->sort('Number ASC')->first();
			}

			$questionsAnswered = $session->MemberSessionQuestions()->filter('Answered', 1)->count();
        }

        if (! $sessionSize = $this->getSessionSize()) {
        	return null;
		}

        $array = [
            'id' => $this->UUID,
			'remove_id' => $this->ID,
            'name' => $this->Name,
            'subject_id' => $this->SubjectArea()->Subject()->UUID,
			'subject' => $this->SubjectArea()->Subject()->Name,
			'subject_icon' => $this->SubjectArea()->Subject()->IconID ? $this->SubjectArea()->Subject()->Icon()->AbsoluteURL : null,
            'user_session_id' => isset($session->UUID) && ! empty($nextQuestionObj) ? $session->UUID : null,
			'duration' => (int) $this->Duration ?: (int) $settings->DefaultQuizTime,
            'question_count' => $sessionSize,
            'questions_answered' => $questionsAnswered,
            'current_question' => ! empty($nextQuestionObj) ? $nextQuestionObj->Number : null,
            'current_question_id' => ! empty($nextQuestionObj) ? $nextQuestionObj->Question()->UUID : null,
            'sort_order' => $this->QuizSortOrder,
//			'other_users' => $this->FullResults(),
			'homework_viewed' => $Viewed,
			'homework_completed' => $Completed,
			'homework_playlists' => []
        ];

		// Get all playlists
		foreach(CurrentUser::getUser()->SchoolClasses() as $schoolClass){
			foreach($schoolClass->HomeworkPlaylists() as $homeworkPlaylist){
				foreach($homeworkPlaylist->Quizzes() as $quiz){
					if($quiz->ID == $this->ID){
						if($session = MemberQuizSession::get()->filter(array('StudentID' => CurrentUser::getUserID(), 'HomeworkPlaylistID' => $homeworkPlaylist->ID))->sort('Created DESC')->first()){
							$array['homework_playlists'][] = ['homework_playlist' => $homeworkPlaylist->UUID, 'homework_playlist_session' => $session->UUID];
						} else {
							$array['homework_playlists'][] = ['homework_playlist' => $homeworkPlaylist->UUID];
						}
					}
				}
			}
		}

        return $array;
    }

    public function getWithSession(MemberQuizSession $session = null, $includeQuestions = true, $HomeworkPlaylistUID = null)
    {
        if (! $session) {
            $session = $this->MemberQuizSessions()->filter('StudentID', CurrentUser::getUserID())->sort('Created DESC')->first();
        }

		if($HomeworkPlaylistUID){
			if($HomeworkPlaylist = HomeworkPlaylist::get()->filter('UUID', $HomeworkPlaylistUID)->first()) {
				$session = $this->MemberQuizSessions()->filter(array('StudentID' => CurrentUser::getUserID(), 'HomeworkPlaylistID' => $HomeworkPlaylist->ID))->sort('Created DESC')->first();
			}
		}

		if ($array = $this->getBasic($HomeworkPlaylistUID)) {
			$array['user_session'] = $session->getBasic($includeQuestions);
			return $array;
		}

		return null;
    }

    /** TODO populate once we add scoring */
    public function fullResults()
	{
		$numbers = [
			0 => 0,
			1 => 0,
			2 => 0,
			3 => 0,
			4 => 0,
			5 => 0,
			6 => 0,
			7 => 0,
			8 => 0,
			9 => 0
		];

		$total = 0;

		foreach (MemberQuizSession::get()->filter(['QuizID' => $this->ID, 'Completed' => 1]) as $quizSession) {

			switch (ceil($quizSession->Percentage / 10) * 10) {
				case 10:
					$numbers[0]++;
					$total++;
					break;
				case 20:
					$numbers[1]++;
					$total++;
					break;
				case 30:
					$numbers[2]++;
					$total++;
					break;
				case 40:
					$numbers[3]++;
					$total++;
					break;
				case 50:
					$numbers[4]++;
					$total++;
					break;
				case 60:
					$numbers[5]++;
					$total++;
					break;
				case 70:
					$numbers[6]++;
					$total++;
					break;
				case 80:
					$numbers[7]++;
					$total++;
					break;
				case 90:
					$numbers[8]++;
					$total++;
					break;
				case 100:
					$numbers[9]++;
					$total++;
					break;
			}
		}

		$total = array_sum($numbers);

		return [
			[
				'name' => '0-10%',
				'short_name' => 0,
				'number' => $numbers[0],
				'percentage' => $numbers[0] ? round(($numbers[0]/$total) * 100) : 0
			],
			[
				'name' => '10-20%',
				'short_name' => 10,
				'number' => $numbers[1],
				'percentage' => $numbers[1] ? round(($numbers[1]/$total) * 100) : 0
			],
			[
				'name' => '20-30%',
				'short_name' => 20,
				'number' => $numbers[2],
				'percentage' => $numbers[2] ? round(($numbers[2]/$total) * 100) : 0
			],
			[
				'name' => '30-40%',
				'short_name' => 30,
				'number' => $numbers[3],
				'percentage' => $numbers[3] ? round(($numbers[3]/$total) * 100) : 0
			],
			[
				'name' => '40-50%',
				'short_name' => 40,
				'number' => $numbers[4],
				'percentage' => $numbers[4] ? round(($numbers[4]/$total) * 100) : 0
			],
			[
				'name' => '50-60%',
				'short_name' => 50,
				'number' => $numbers[5],
				'percentage' => $numbers[5] ? round(($numbers[5]/$total) * 100) : 0
			],
			[
				'name' => '60-70%',
				'short_name' => 60,
				'number' => $numbers[6],
				'percentage' => $numbers[6] ? round(($numbers[6]/$total) * 100) : 0
			],
			[
				'name' => '70-80%',
				'short_name' => 70,
				'number' => $numbers[7],
				'percentage' => $numbers[7] ? round(($numbers[7]/$total) * 100) : 0
			],
			[
				'name' => '80-90%',
				'short_name' => 80,
				'number' => $numbers[8],
				'percentage' => $numbers[8] ? round(($numbers[8]/$total) * 100) : 0
			],
			[
				'name' => '90-100%',
				'short_name' => 90,
				'number' => $numbers[9],
				'percentage' => $numbers[9] ? round(($numbers[9]/$total) * 100) : 0
			],
		];
	}
}