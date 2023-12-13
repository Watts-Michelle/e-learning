<?php

class HomeworkPlaylist_Controller extends Base_Controller
{
	/** {@inheritdoc} */
	protected $auth = true;

	/** {@inheritdoc} */
	private static $allowed_actions = array(
		'all',
		'byID',
		'completeStatus',
		'completeQuizStatus',
		'viewedStatus',
		'viewPlaylist',
		'viewedQuiz'
	);

	/** {@inheritdoc} */
	private static $url_handlers = array(
		'$playlistUID/viewed' => 'viewPlaylist',
		'$playlistUID/lesson/$lessonUID/complete' => 'completeStatus',
		'$playlistUID/quiz/$quizUID/complete' => 'completeQuizStatus',
		'$playlistUID/lesson/$lessonUID/viewed' => 'viewedStatus',
		'$playlistUID/quiz/$quizUID/viewed' => 'viewedQuiz',
		'' => 'all',
		'$UID' => 'byID'
	);

	/**
	 * Get Current Users Homework Playlists
	 *
	 * @param SS_HTTPRequest $request
	 * @return SS_HTTPResponse
	 */
	public function all(SS_HTTPRequest $request)
	{
		if (! $request->isGet()) return $this->handleError(404, 'Must be a GET request.');

		$CurrentUser = CurrentUser::getUser();

		$playlists = [ 'homework_playlists' => []];

		if($SchoolClasses = $CurrentUser->School()->SchoolClasses()){

			foreach($SchoolClasses as $key => $schoolClass){

				if($student = $schoolClass->Students()->filter('StudentID', $CurrentUser->ID)->first()){

					foreach($schoolClass->HomeworkPlaylists()->filter('Active', true) as $playlist){

						$playlists['homework_playlists'][] = $playlist->getBasic();
					}
				}
			}
		}

		return (new JsonApi)->formatReturn($playlists);
	}

	/**
	 * Get Specific Homework Playlist
	 *
	 * @param SS_HTTPRequest $request
	 * @return bool|SS_HTTPResponse
	 * @throws O_HTTPResponse_Exception
	 */
	public function byID(SS_HTTPRequest $request)
	{
		if (!($request->isGET() )) return $this->handleError(404, 'Must be a GET request');

		if(! $HomeworkPlaylist = HomeworkPlaylist::get()->filter('UUID', $request->param('UID'))->first()){
			return $this->handleError(404, 'There is no homework playlist that matches the UUID provided.');
		}

		return (new JsonApi)->formatReturn(['homework_playlist' => $HomeworkPlaylist->getBasic()]);
	}

	/**
	 * Complete Homework Playlist Lesson
	 *
	 * @param SS_HTTPRequest $request
	 * @return bool|SS_HTTPResponse
	 * @throws O_HTTPResponse_Exception
	 */
	public function completeStatus(SS_HTTPRequest $request)
	{
		if (! $request->isPUT()) return $this->handleError(404, 'Must be a PUT request.');

		if (! $request->param('playlistUID')) {
			return $this->handleError(404, 'That playlist UUID does not exist.');
		}

		if (! $request->param('lessonUID')) {
			return $this->handleError(404, 'That lesson UUID does not exist.');
		}

		$HomeworkPlaylist = HomeworkPlaylist::get()->filter('UUID', $request->param('playlistUID'))->first();

		$Lesson = Lesson::get()->filter('UUID', $request->param('lessonUID'))->first();

		// Prevent duplicate records of Completed Homework Lessons.
		if(empty($HomeworkCompletedLesson = CompletedHomeworkPlaylistLesson::get()
											->filter(array('HomeworkPlaylistID' => $HomeworkPlaylist->ID,'LessonID' => $Lesson->ID,'StudentID' => $this->appUser->ID))
											->first())){

			$HomeworkCompletedLesson = CompletedHomeworkPlaylistLesson::create();
			$HomeworkCompletedLesson->StudentID = $this->appUser->ID;
			$HomeworkCompletedLesson->LessonID = $Lesson->ID;
			$HomeworkCompletedLesson->HomeworkPlaylistID = $HomeworkPlaylist->ID;
			$HomeworkCompletedLesson->Completed = true;
			$HomeworkCompletedLesson->write();
		}

		return (new JsonApi)->formatReturn([]);
	}

	public function completeQuizStatus(SS_HTTPRequest $request)
	{
		if (! $request->isPUT()) return $this->handleError(404, 'Must be a PUT request.');

		if (! $request->param('playlistUID')) {
			return $this->handleError(404, 'Requires a playlist UUID.');
		}

		if (! $request->param('quizUID')) {
			return $this->handleError(404, 'Requires a quiz UUID.');
		}

		$HomeworkPlaylist = HomeworkPlaylist::get()->filter('UUID', $request->param('playlistUID'))->first();

		$Quiz = Quiz::get()->filter('UUID', $request->param('quizUID'))->first();

		// Prevent duplicate records of Completed Homework Lessons.
		if(empty($HomeworkCompletedQuiz = CompletedHomeworkPlaylistQuiz::get()
			->filter(array('HomeworkPlaylistID' => $HomeworkPlaylist->ID, 'QuizID' => $Quiz->ID, 'StudentID' => $this->appUser->ID))
			->first())){

			$HomeworkCompletedQuiz = CompletedHomeworkPlaylistQuiz::create();
			$HomeworkCompletedQuiz->StudentID = $this->appUser->ID;
			$HomeworkCompletedQuiz->QuizID = $Quiz->ID;
			$HomeworkCompletedQuiz->HomeworkPlaylistID = $HomeworkPlaylist->ID;
			$HomeworkCompletedQuiz->Completed = true;
			$HomeworkCompletedQuiz->write();
		}

		return (new JsonApi)->formatReturn([]);
	}

	public function viewedStatus(SS_HTTPRequest $request)
	{
		if (! $request->isPUT()) return $this->handleError(404, 'Must be a PUT request.');

		if (! $request->param('playlistUID')) {
			return $this->handleError(404, 'That playlist UUID does not exist.');
		}

		if (! $request->param('lessonUID')) {
			return $this->handleError(404, 'That lesson UUID does not exist.');
		}

		$HomeworkPlaylist = HomeworkPlaylist::get()->filter('UUID', $request->param('playlistUID'))->first();

		$Lesson = Lesson::get()->filter('UUID', $request->param('lessonUID'))->first();

		// Prevent duplicate records of Completed Homework Lessons.
		if(empty($HomeworkViewedLesson = ViewedHomeworkPlaylistLesson::get()
			->filter(array('HomeworkPlaylistID' => $HomeworkPlaylist->ID,'LessonID' => $Lesson->ID,'StudentID' => $this->appUser->ID))
			->first())){

			$HomeworkViewedLesson = ViewedHomeworkPlaylistLesson::create();
			$HomeworkViewedLesson->StudentID = $this->appUser->ID;
			$HomeworkViewedLesson->LessonID = $Lesson->ID;
			$HomeworkViewedLesson->HomeworkPlaylistID = $HomeworkPlaylist->ID;
			$HomeworkViewedLesson->Viewed = true;
			$HomeworkViewedLesson->write();
		}

		return (new JsonApi)->formatReturn([]);
	}

	public function viewedQuiz(SS_HTTPRequest $request)
	{
		if (! $request->isPUT()) return $this->handleError(404, 'Must be a PUT request.');

		if (! $request->param('playlistUID')) {
			return $this->handleError(404, 'That playlist UUID does not exist.');
		}

		if (! $request->param('quizUID')) {
			return $this->handleError(404, 'That quiz UUID does not exist.');
		}

		$HomeworkPlaylist = HomeworkPlaylist::get()->filter('UUID', $request->param('playlistUID'))->first();

		$Quiz = Quiz::get()->filter('UUID', $request->param('quizUID'))->first();

		// Prevent duplicate records of Completed Homework Lessons.
		if(empty($HomeworkViewedQuiz = ViewedHomeworkPlaylistQuiz::get()
			->filter(array('HomeworkPlaylistID' => $HomeworkPlaylist->ID,'QuizID' => $Quiz->ID,'StudentID' => $this->appUser->ID))
			->first())){

			$HomeworkViewedQuiz = ViewedHomeworkPlaylistQuiz::create();
			$HomeworkViewedQuiz->StudentID = $this->appUser->ID;
			$HomeworkViewedQuiz->QuizID = $Quiz->ID;
			$HomeworkViewedQuiz->HomeworkPlaylistID = $HomeworkPlaylist->ID;
			$HomeworkViewedQuiz->Viewed = true;
			$HomeworkViewedQuiz->write();
		}

		return (new JsonApi)->formatReturn([]);
	}

	public function viewPlaylist(SS_HTTPRequest $request)
	{
		if (! $request->isPUT()) return $this->handleError(404, 'Must be a PUT request.');

		if (! $request->param('playlistUID')) {
			return $this->handleError(404, 'That playlist UUID does not exist.');
		}

		$HomeworkPlaylist = HomeworkPlaylist::get()->filter('UUID', $request->param('playlistUID'))->first();

		$HomeworkPlaylist->Students()->add(CurrentUser::getUserID(), array('Viewed' => true));

		return (new JsonApi)->formatReturn([]);
	}
}