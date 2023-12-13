<?php

class HomeworkPlaylistClass_Controller extends School_Controller
{
	// TO DO: Check that the user is the teacher and they can update playlists!!

	protected $activePage = 'Homework';

	/** {@inheritdoc} */
	private static $allowed_actions = array(
		'allHomework',
		'editHomework',
		'deleteHomework',
		'inactiveHomework',
		'activeHomework',
		'createHomework',
		'viewLesson',
		'filterLessons',
		'filterSelectedLessons',
		'filterQuizzes',
		'filterSelectedQuizzes',
		'editTracks',
		'editQuizzes',
		'createHomeworkPlaylistForm',
		'editHomeworkPlaylistForm',
	);

	/** {@inheritdoc} */
	private static $url_handlers = array(
		'lesson/$ID/$SchoolClassID' => 'viewLesson',  						// View a single lesson page.
		'create/$SchoolClassID' => 'createHomework',  						// Create homework playlist page. 'Create Homework' button with class ID passed.

		'filterTracks/$ID' => 'filterLessons',  							// Filter lessons - ajax call.
		'filterSelectedTracks/$ID' => 'filterSelectedLessons',				// Filter Selected Lessons - ajax call.

		'filterQuizzes/$ID' => 'filterQuizzes',								// Filter lessons - ajax call.
		'filterSelectedQuizzes/$ID' => 'filterSelectedQuizzes',				// Filter lessons - ajax call.

		'delete/$ID' => 'deleteHomework',									// Delete entire playlist.
		'edit/$SchoolClassID/$ID' => 'editHomework',  						// Edit homework playlist page.

		'edit_tracks/$SchoolClassID/$ID' => 'editTracks',
		'edit_quizzes/$SchoolClassID/$ID' => 'editQuizzes',

		'inactive/$ID' => 'inactiveHomework',   							// Mark homework playlist as inactive.
		'active/$ID' => 'activeHomework',      								// Mark homework playlist as active.

		'createplaylist/$SchoolClassID' => 'createHomeworkPlaylistForm',	// Submit edit homework playlist page.
		'editPlaylist/$ID' => 'editHomeworkPlaylistForm',					// Submit edit homework playlist page.

		'$SchoolClassID' => 'allHomework',									// 'Add Homework' button with class ID passed.
	);

	public function init()
	{
		parent::init();

		$this->breadcrumbs = new ArrayList([
			new ArrayData([
				'Name' => '',
				'Link' => '',
			]),
		]);
	}

	/**
	 * View a list of Homework Playlists.
	 *
	 * @return HTMLText
	 */
	public function allHomework()
	{
		$this->breadcrumbs = new ArrayList([
			new ArrayData([
				'Name' => 'School',
				'Link' => ''
			]),
		]);

		$this->pageTitle = 'Homeworks list';
		$this->title = 'Homeworks list';

		$list = new ArrayList();

		// TO DO: Filter homeworks not belonging to teachers class!
		if($Playlists = HomeworkPlaylist::get()->filter('SchoolID', Member::currentUser()->School()->ID)){

			foreach($Playlists as $playlist){

				$list->push($playlist);
			}
		}

		return $this->renderPage('ViewHomework', [
			'Status' => 'view',
			'SchoolClassID' => $this->request->param('SchoolClassID'),
			'HomeworkPlaylists' => $list,
		]);
	}

	/**
	 * Edit homework playlist page.
	 *
	 * @param SS_HTTPRequest $request
	 * @return HTMLText
	 */
	public function editHomework(SS_HTTPRequest $request)
	{
		$this->breadcrumbs->add(new ArrayData([
			'Name' => 'Edit Homework',
			'Link' => '',
			'Active' => 1
		]));

		$SelectedQuizzes = HomeworkPlaylist::get()->byID($this->request->param('ID'))->Quizzes();

		$SelectedLessons = HomeworkPlaylist::get()->byID($this->request->param('ID'))->Lessons();

		return $this->renderPage('ViewHomework', [
			'Status' => 'edit',
			'SchoolClassID' => $this->request->param('SchoolClassID'),
			'ID' => $this->request->param('ID'),
			'SelectedLessons' => $SelectedLessons,
			'SelectedQuizzes' => $SelectedQuizzes,
			'HomeworkPlaylist' => HomeworkPlaylist::get()->byID($this->request->param('ID'))
		]);
	}

	/**
	 * Edit Homework Playlist tracks screen.
	 *
	 * @param SS_HTTPRequest $request
	 * @return HTMLText
	 */
	public function editTracks(SS_HTTPRequest $request)
	{
		$this->breadcrumbs->add(new ArrayData([
			'Name' => 'Edit Homework',
			'Link' => '',
			'Active' => 1
		]));

		$Lessons = new ArrayList();

		if($examLevels = Member::currentUser()->School()->ExamCountry()->ExamLevels()) {
			foreach ($examLevels as $examLevel) {
				foreach($examLevel->Subjects() as $subject){
					foreach($subject->SubjectArea() as $subjectArea){
						foreach($subjectArea->Lessons() as $lesson){
							$Lessons->push($lesson);
						}
					}
				}
			}
		}

		$SelectedLessons = HomeworkPlaylist::get()->byID($this->request->param('ID'))->Lessons();

		foreach($Lessons as $key => $lesson){

			foreach($SelectedLessons as $selectedLesson){

				if($lesson->UUID == $selectedLesson->UUID){

					$Lessons->remove($lesson);
				}
			}
		}

		return $this->renderPage('ViewHomework', [
			'Status' => 'editTracks',
			'SchoolClassID' => $this->request->param('SchoolClassID'),
			'ID' => $this->request->param('ID'),
			'SelectedLessons' => $SelectedLessons,
			'Lessons' => $Lessons,
			'HomeworkPlaylist' => HomeworkPlaylist::get()->byID($this->request->param('ID'))
		]);
	}

	/**
	 * Edit Homework Playlist quizzes screen.
	 *
	 * @param SS_HTTPRequest $request
	 * @return HTMLText
	 */
	public function editQuizzes(SS_HTTPRequest $request)
	{
		$this->breadcrumbs->add(new ArrayData([
			'Name' => 'Edit Homework',
			'Link' => '',
			'Active' => 1
		]));

		$Quizzes = new ArrayList();

		if($examLevels = Member::currentUser()->School()->ExamCountry()->ExamLevels()) {
			foreach ($examLevels as $examLevel) {
				foreach($examLevel->Subjects() as $subject){
					foreach($subject->SubjectArea() as $subjectArea){
						foreach($subjectArea->Quizzes() as $quiz){
							$Quizzes->push($quiz);
						}
					}
				}
			}
		}

		$SelectedQuizzes = HomeworkPlaylist::get()->byID($this->request->param('ID'))->Quizzes();

		foreach($Quizzes as $key => $quiz){

			foreach($SelectedQuizzes as $selectedQuiz){

				if($quiz->UUID == $selectedQuiz->UUID){

					$Quizzes->remove($quiz);
				}
			}
		}

		return $this->renderPage('ViewHomework', [
			'Status' => 'editQuizzes',
			'SchoolClassID' => $this->request->param('SchoolClassID'),
			'ID' => $this->request->param('ID'),
			'SelectedQuizzes' => $SelectedQuizzes,
			'Quizzes' => $Quizzes,
			'HomeworkPlaylist' => HomeworkPlaylist::get()->byID($this->request->param('ID'))
		]);
	}

	/**
	 * Delete homework playlist.
	 *
	 * @param SS_HTTPRequest $request
	 * @return bool|SS_HTTPResponse
	 */
	public function deleteHomework(SS_HTTPRequest $request)
	{
		$HomeworkPlaylist = HomeworkPlaylist::get()->byID($this->request->param('ID'));
		$HomeworkPlaylist->delete();

		return $this->redirectBack();
	}

	/**
	 * Set homework playlist to active.
	 *
	 * @param SS_HTTPRequest $request
	 * @return bool|SS_HTTPResponse
	 */
	public function activeHomework(SS_HTTPRequest $request)
	{
		$Homework = HomeworkPlaylist::get()->byID($this->request->param('ID'));
		$Homework->Active = true;
		$Homework->write();

		return $this->redirectBack();
	}

	/**
	 * Set homework playlist to inactive.
	 *
	 * @param SS_HTTPRequest $request
	 * @return bool|SS_HTTPResponse
	 */
	public function inactiveHomework(SS_HTTPRequest $request)
	{
		$Homework = HomeworkPlaylist::get()->byID($this->request->param('ID'));
		$Homework->Active = false;
		$Homework->write();

		return $this->redirectBack();
	}

	/**
	 * Create homework playlist form submission.
	 *
	 * @return HTMLText
	 */
	public function createHomework()
	{
		$this->breadcrumbs->add(new ArrayData([
			'Name' => 'Create Homework',
			'Link' => '',
			'Active' => 1
		]));

		$Lessons = new ArrayList();

		// get lessons: current user(staff)->school->examcountry->examlevels->subjects->SubjectAreas->lessons
		if($examLevels = Member::currentUser()->School()->ExamCountry()->ExamLevels()) {
			foreach ($examLevels as $examLevel) {
				foreach($examLevel->Subjects() as $subject){
					foreach($subject->SubjectArea() as $subjectArea){
						foreach ($subjectArea->Lessons() as $lesson){
							$Lessons->push($lesson);
						}
					}
				}
			}
		}

		$Quizzes = new ArrayList();

		if($examLevels = Member::currentUser()->School()->ExamCountry()->ExamLevels()) {
			foreach ($examLevels as $examLevel) {
				foreach($examLevel->Subjects() as $subject){
					foreach($subject->SubjectArea() as $subjectArea){
						foreach ($subjectArea->Quizzes() as $quiz){
							$Quizzes->push($quiz);
						}
					}
				}
			}
		}

		return $this->renderPage('ViewHomework', [
			'Status' => 'create',
			'SchoolClassID' => $this->request->param('SchoolClassID'),
			'Lessons' => $Lessons,
			'Quizzes' => $Quizzes
		]);
	}

	/**
	 * Submit edit homework playlist form.
	 *
	 * @param SS_HTTPRequest $request
	 * @return SS_HTTPResponse
	 */
	public function createHomeworkPlaylistForm(SS_HTTPRequest $request)
	{
		// TO DO: check user is assigned a to a school else do nothing! Or don't show button!

		$School = School::get()->byID(Member::currentUser()->School()->ID);

		$HomeworkPlaylist = HomeworkPlaylist::create();

		/* Assign homework title. */
		$HomeworkPlaylistTitle = isset($_POST['HomeworkPlaylistTitle'])? $_POST['HomeworkPlaylistTitle'] : '';
		$HomeworkPlaylist->Title = $HomeworkPlaylistTitle;

		/* Assign homework to a school class. */
		$SchoolClassID = isset($_POST['SchoolClassID'])? $_POST['SchoolClassID'] : '';
		$HomeworkPlaylist->SchoolClassID = $SchoolClassID;

		$DeadlineMonth = isset($_POST['homework_deadline_month'])? $_POST['homework_deadline_month'] : '';
		$DeadlineDay = isset($_POST['homework_deadline_day'])? $_POST['homework_deadline_day'] : '';
		$DeadlineYear = isset($_POST['homework_deadline_year'])? $_POST['homework_deadline_year'] : '';

		$Deadline = $DeadlineDay.'-'.$DeadlineMonth.'-'.$DeadlineYear;
		$HomeworkPlaylist->Deadline = $Deadline;

		$HomeworkPlaylist->SchoolID = $School->ID;
		$HomeworkPlaylist->write();

		$School->HomeworkPlaylists()->add($HomeworkPlaylist);

		foreach(SchoolClass::get()->byID($SchoolClassID)->Students() as $student){
			$HomeworkPlaylist->Students()->add($student);
		}

		if(isset($_POST['lessonID'])) {

			foreach ($_POST['lessonID'] as $LessonID) {

				$lesson = Lesson::get()->filter('UUID', $LessonID)->first();
				$HomeworkPlaylist->Lessons()->add($lesson);
			}
		}

		if(isset($_POST['quizID'])) {

			foreach ($_POST['quizID'] as $QuizID) {

				$quiz = Quiz::get()->filter('UUID', $QuizID)->first();
				$HomeworkPlaylist->Quizzes()->add($quiz);
			}
		}

		return $this->redirect('school/homework/'.$this->request->param('SchoolClassID'));
	}

	public function editHomeworkPlaylistForm(SS_HTTPRequest $request)
	{
		// TO DO: check user is assigned a to a school else do nothing! Or don't show button!

		$HomeworkPlaylist = HomeworkPlaylist::get()->byID($this->request->param('ID'));
		$HomeworkPlaylist->Title = $_POST['HomeworkPlaylistTitle'];
		$HomeworkPlaylist->write();

		if(isset($_POST['lessonID'])) {

			foreach ($_POST['lessonID'] as $LessonID) {

				$lesson = Lesson::get()->filter('UUID', $LessonID)->first();
				$HomeworkPlaylist->Lessons()->add($lesson);
			}
		}

		if(isset($_POST['removeLessonID'])) {

			foreach ($_POST['removeLessonID'] as $LessonID) {

				$lesson = Lesson::get()->filter('UUID', $LessonID)->first();
				$HomeworkPlaylist->Lessons()->remove($lesson);
			}
		}

		if(isset($_POST['quizID'])) {

			foreach ($_POST['quizID'] as $QuizID) {

				$quiz = Quiz::get()->filter('UUID', $QuizID)->first();
				$HomeworkPlaylist->Quizzes()->add($quiz);
			}
		}

		if(isset($_POST['removeQuizID'])) {

			foreach ($_POST['removeQuizID'] as $QuizID) {

				$quiz = Quiz::get()->filter('UUID', $QuizID)->first();
				$HomeworkPlaylist->Quizzes()->remove($quiz);
			}
		}

		return $this->redirect('school/homework/'.$_POST['SchoolClassID']);
	}

	/**
	 * Filter lessons according to ajax response.
	 *
	 * @param SS_HTTPRequest $request
	 * @return HTMLText
	 */
	public function filterLessons(SS_HTTPRequest $request)
	{
		$String = $request->getVar('string');

		// There has been no search.
		if($String == ''){

			$Lessons = new ArrayList();

			if($examLevels = Member::currentUser()->School()->ExamCountry()->ExamLevels()) {
				foreach ($examLevels as $examLevel) {
					foreach($examLevel->Subjects() as $subject){
						foreach($subject->SubjectArea() as $subjectArea){
							foreach ($subjectArea->Lessons() as $lesson){
								$Lessons->push($lesson);
							}
						}
					}
				}
			}
		}

		// Need to remove selected lessons from lessons search as homework playlist is being edited.
		if($this->request->param('ID')){

			$Lessons = new ArrayList();

			if($examLevels = Member::currentUser()->School()->ExamCountry()->ExamLevels()) {
				foreach ($examLevels as $examLevel) {
					foreach($examLevel->Subjects() as $subject){
						foreach($subject->SubjectArea() as $subjectArea){

							foreach($subjectArea->Lessons()->filterAny(array(
								'Name:PartialMatch' => $String,
								'Content:PartialMatch' => $String )) as $lesson){
									$Lessons->push($lesson);
							}
						}
					}
				}
			}

			$SelectedLessons = HomeworkPlaylist::get()->byID($this->request->param('ID'))->Lessons();

			foreach($Lessons as $key => $lesson){

				foreach($SelectedLessons as $selectedLesson){

					if($lesson->UUID == $selectedLesson->UUID){

						$Lessons->remove($lesson);
					}
				}
			}

		// Perform lessons search.
		} else {

			$Lessons = new ArrayList();

			if($examLevels = Member::currentUser()->School()->ExamCountry()->ExamLevels()) {
				foreach ($examLevels as $examLevel) {
					foreach($examLevel->Subjects() as $subject){
						foreach($subject->SubjectArea() as $subjectArea){
							foreach($subjectArea->Lessons()->filterAny(array(
								'Name:PartialMatch' => $String,
								'Content:PartialMatch' => $String )) as $lesson){
								$Lessons->push($lesson);
							}
						}
					}
				}
			}
		}

		return $this->renderWith('Lessons', array(
			'Lessons' => $Lessons
		));
	}

	public function filterSelectedLessons(SS_HTTPRequest $request)
	{
		$String = $request->getVar('string');

		// There has been no search.
		if($String == ''){
			$SelectedLessons = HomeworkPlaylist::get()->byID($this->request->param('ID'))->Lessons();
		} else {
			$SelectedLessons = HomeworkPlaylist::get()->byID($this->request->param('ID'))->Lessons()
									->filterAny(array('Name:PartialMatch' => $String,'Content:PartialMatch' => $String));
		}

		return $this->renderWith('Lessons', array(
			'Lessons' => $SelectedLessons
		));
	}

	public function filterQuizzes(SS_HTTPRequest $request)
	{
		$String = $request->getVar('string');

		// There has been no search.
		if($String == ''){

			$Quizzes = new ArrayList();

			if($examLevels = Member::currentUser()->School()->ExamCountry()->ExamLevels()) {
				foreach ($examLevels as $examLevel) {
					foreach($examLevel->Subjects() as $subject){
						foreach($subject->SubjectArea() as $subjectArea){
							foreach ($subjectArea->Quizzes() as $quiz){
								$Quizzes->push($quiz);
							}
						}
					}
				}
			}
		}

		// Need to remove selected quizzes from quiz search as homework playlist is being edited.
		if($this->request->param('ID')){

			$Quizzes = new ArrayList();

			if($examLevels = Member::currentUser()->School()->ExamCountry()->ExamLevels()) {
				foreach ($examLevels as $examLevel) {
					foreach($examLevel->Subjects() as $subject){
						foreach($subject->SubjectArea() as $subjectArea){
							foreach($subjectArea->Quizzes()->filterAny(array('Name:PartialMatch' => $String)) as $quiz){
								$Quizzes->push($quiz);
							}
						}
					}
				}
			}

			$SelectedQuizzes = HomeworkPlaylist::get()->byID($this->request->param('ID'))->Quizzes();

			foreach($Quizzes as $key => $quiz){

				foreach($SelectedQuizzes as $selectedQuiz){

					if($quiz->UUID == $selectedQuiz->UUID){

						$Quizzes->remove($quiz);
					}
				}
			}

		// Perform quiz search.
		} else {

			$Quizzes = new ArrayList();

			if($examLevels = Member::currentUser()->School()->ExamCountry()->ExamLevels()) {
				foreach ($examLevels as $examLevel) {
					foreach($examLevel->Subjects() as $subject){
						foreach($subject->SubjectArea() as $subjectArea){
							foreach($subjectArea->Quizzes()->filterAny(array('Name:PartialMatch' => $String )) as $quiz){
								$Quizzes->push($quiz);
							}
						}
					}
				}
			}
		}

		return $this->renderWith('Quizzes', array(
			'Quizzes' => $Quizzes
		));
	}

	public function filterSelectedQuizzes(SS_HTTPRequest $request)
	{
		$String = $request->getVar('string');

		// There has been no search.
		if($String == ''){
			$SelectedQuizzes = HomeworkPlaylist::get()->byID($this->request->param('ID'))->Quizzes();
		} else {
			$SelectedQuizzes = HomeworkPlaylist::get()->byID($this->request->param('ID'))->Quizzes()
				->filterAny(array('Name:PartialMatch' => $String));
		}

		return $this->renderWith('Quizzes', array(
			'Quizzes' => $SelectedQuizzes
		));
	}

	/**
	 * View a single lesson page.
	 *
	 * @return HTMLText
	 */
	public function viewLesson()
	{
		$Lesson = Lesson::get()->byID($this->request->param('ID'));

		$Class = SchoolClass::get()->byID($this->request->param('SchoolClassID'));

		$this->pageTitle = $Lesson->Name;

		$this->breadcrumbs->add(new ArrayData([
			'Name' => 'Homeworks',
			'Link' => '',
			'Active' => 1
		]));

		return $this->renderPage('LessonContentScreen', [
			'Lesson' => $Lesson
		]);
	}

}