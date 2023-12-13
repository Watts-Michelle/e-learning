<?php

class Quiz_Controller extends Base_Controller
{
	/** {@inheritdoc} */
	protected $auth = true;

	/** {@inheritdoc} */
	private static $allowed_actions = array(
		'handler',
		'results'
	);

	/** {@inheritdoc} */
	private static $url_handlers = array(
		'/results//$dummy' => 'results',
		'/$ID//$dummy' => 'handler',
	);

	public function handler(SS_HTTPRequest $request)
	{
		$id = $request->param('ID');
		if (! $id) return $this->handleError(404);

		$userSessionID = $request->getVar('session_id');

		if($request->getVar('playlist_id')){
			$HomeworkPlaylistUID = $request->getVar('playlist_id');
		}

		$session = null;

		$quiz = Quiz::get()->filter(['UUID' => $id])->first();
		if (! $quiz) return $this->handleError(404);

		if($userSessionID && isset($HomeworkPlaylistUID)){
			if($HomeworkPlaylist = HomeworkPlaylist::get()->filter('UUID', $HomeworkPlaylistUID)->first()) {
				$session = $this->appUser->MemberQuizSessions()->filter(['QuizID' => $quiz->ID, 'UUID' => $userSessionID, 'HomeworkPlaylistID' => $HomeworkPlaylist->ID])->first();
			}
		}

		if ($userSessionID) {
			$session = $this->appUser->MemberQuizSessions()->filter(['QuizID' => $quiz->ID, 'UUID' => $userSessionID])->first();
		}

		if ($request->isGET()) {
			if(isset($HomeworkPlaylistUID)) {
				return $this->get($quiz, $session, $HomeworkPlaylistUID);
			} else {
				return $this->get($quiz, $session);
			}
		}

		if ($request->isPost()) {
			if(isset($HomeworkPlaylistUID)){
				return $this->createSession($quiz, $HomeworkPlaylistUID);
			} else {
				return $this->createSession($quiz);
			}
		}

		if ($request->isPUT()) {
			if ($userSessionID && empty($session)) return $this->handleError(4002, null, 404);
			if (empty($session)) return $this->handleError(4001);
			return $this->answer($quiz, $session);
		}
	}

	public function results(SS_HTTPRequest $request)
	{
		$sessions = $this->appUser->MemberQuizSessions()->filter('Completed', 1)->sort('LastEdited DESC');

		$result = [
			'quiz_sessions' => []
		];

		foreach ($sessions as $session) {
			if (! empty($session->Quiz()->UUID)) {
				$result['quiz_sessions'][] = $session->Quiz()->getWithSession($session, false);
			}
		}

		return (new JsonApi)->formatReturn($result);
	}

	/**
	 * POST a new session request
	 *
	 * @param Quiz $quiz
	 * @return bool
	 * @throws O_HTTPResponse_Exception
	 */
	private function createSession(Quiz $quiz, $HomeworkPlaylistUID = null)
	{
		if($HomeworkPlaylistUID){

			$HomeworkPlaylist = HomeworkPlaylist::get()->filter('UUID', $HomeworkPlaylistUID)->first();

			if (! $quizSession = $this->appUser->MemberQuizSessions()->filter(['QuizID' => $quiz->ID, 'Completed' => 0, 'HomeworkPlaylistID' => $HomeworkPlaylist->ID])->sort('Created DESC')->first()) {
				$quizSession = new MemberQuizSession;
				$quizSession->StudentID = $this->appUser->ID;
				$quizSession->HomeworkPlaylistID = $HomeworkPlaylist->ID;
				$quizSession->QuizID = $quiz->ID;
				$quizSession->write();
			}

		} else {

			if (! $quizSession = $this->appUser->MemberQuizSessions()->filter(['QuizID' => $quiz->ID, 'Completed' => 0])->sort('Created DESC')->first()) {
				$quizSession = new MemberQuizSession;
				$quizSession->StudentID = $this->appUser->ID;
				$quizSession->QuizID = $quiz->ID;
				$quizSession->write();
			}
		}

		return $this->get($quiz, $quizSession);
	}

	/**
	 * GET either the currently active session or the specified quiz or an empty quiz
	 *
	 * @param Quiz $quiz
	 * @param MemberQuizSession|null $session
	 *
	 * @return SS_HTTPResponse|JsonApi
	 */
	private function get(Quiz $quiz, MemberQuizSession $session = null, $HomeworkPlaylistUID = null)
	{
		if ($session) {

			if($HomeworkPlaylistUID){
				return (new JsonApi)->formatReturn(['quiz' => $quiz->getWithSession($session, null, $HomeworkPlaylistUID)]);
			} else {
				return (new JsonApi)->formatReturn(['quiz' => $quiz->getWithSession($session)]);
			}

		} else {

			$session = $quiz->MemberQuizSessions()->filter('MemberID', CurrentUser::getUserID())->sort('Created DESC')->first();

			if ($session) {
				$nextQuestionObj = $session->MemberSessionQuestions()->filter('Answered', 0)->sort('Number ASC')->first();
			}

			if (!empty($nextQuestionObj)) {

				if($HomeworkPlaylistUID){
					return (new JsonApi)->formatReturn(['quiz' => $quiz->getWithSession($session, null, $HomeworkPlaylistUID)]);
				} else {
					return (new JsonApi)->formatReturn(['quiz' => $quiz->getWithSession($session)]);
				}

			} else {

				if($HomeworkPlaylistUID) {
					return (new JsonApi)->formatReturn(['quiz' => $quiz->getBasic($HomeworkPlaylistUID)]);
				} else {
					return (new JsonApi)->formatReturn(['quiz' => $quiz->getBasic()]);
				}
			}
		}
	}

	/**
	 * PUT an answer into a quiz
	 *
	 * @param Quiz $quiz
	 * @param MemberQuizSession|null $session
	 * @return SS_HTTPResponse
	 * @throws O_HTTPResponse_Exception
	 */
	private function answer(Quiz $quiz, MemberQuizSession $session)
	{
		if (! $session) return $this->handleError(404);

		$body = $this->requestBody;

		if ((empty($body['question']) || ! is_array($body['question'])) && empty($body['completed'])) return $this->handleError(4003);

		$newArray = [];

		$questionList = $session->MemberSessionQuestions();

		$questionNumbers = [];

		if (! empty($body['question']) && is_array($body['question'])) {
			foreach ($body['question'] as $question) {
				if (isset($question['id'])) {
					$questionObj = $questionList->filter('Question.UUID', $question['id'])->first();

					if (!$questionObj) {
						return $this->handleError(4004, 'Question ' . $question['id'] . ' not found in the user\'s session');
					}

					$questionObj->TimeTaken = isset($question['user_session_time_taken']) ? $question['user_session_time_taken'] : 0;

					if (isset($question['answer'])) {
						if (!$questionObj->Question()->AcceptMultipleAnswers && count($question['answer']) > 1) {
							return $this->handleError(4005, 'Multiple answers not allowed for ' . $question['id']);
						}

						$answers = $questionObj->Question()->Answers()->filter('UUID', $question['answer']);

						if ($answers->count() == 0) {
							return $this->handleError('No answers found for question ' . $question['id']);
						}

						$questionNumbers[] = $questionObj->Number;
						$newArray[] = ['question' => $questionObj, 'answers' => $answers, 'correct_answers' => $questionObj->Question()->Answers()->filter('IsCorrect', 1)->count()];
					}
				}
			}

			//Stop users answering questions when previous questions have not been answered
			sort($questionNumbers);
			$answeredQuestions = [];

			foreach ($questionList as $question) {
				if ($question->Answers()->count()) {
					$answeredQuestions[] = $question->Number;
				}
			}

			if (count($answeredQuestions)) {
				$maxQuestion = max($answeredQuestions);
			} else {
				$maxQuestion = 0;
			}

			foreach ($questionNumbers as $key => $number) {
				if ($number <= $maxQuestion) continue;

				if ($number > ($maxQuestion + 1)) {
					$missing = $questionList->filter(['Number' => $maxQuestion+1])->first();
					return $this->handleError(4006, 'Missing answer to a previous question: ' . $missing->Question()->UUID);
				}

				$maxQuestion = $number;
			}

			foreach ($newArray as $row) {
				/** @var MemberSessionQuestion $question */
				$question = $row['question'];
				$question->answer($row['answers'], $row['correct_answers']);
				$question->write();
			}
		}

		$force = false;

		if (isset($body['completed']) && $body['completed'] != 0) {
			$force = true;
		}

		$session->isComplete($force);

		return (new JsonApi)->formatReturn(['quiz' => $quiz->getWithSession(MemberQuizSession::get()->byID($session->ID))]);
	}

}