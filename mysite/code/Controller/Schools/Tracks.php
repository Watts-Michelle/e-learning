<?php

class Tracks_Controller extends School_Controller
{
	private static $allowed_actions = array(
		'all',
		'byID',
		'subject',
		'lesson'
	);

	private static $url_handlers = array(
		'subject//$ID/$OtherID' => 'subject',
		'lesson//$ID' => 'lesson',
		'$ID//$dummy' => 'byID',
		'' => 'all',
	);

	public function init()
	{
		parent::init();

		$this->breadcrumbs = new ArrayList([
			new ArrayData([
				'Name' => 'Tracks List',
				'Link' => '/school/tracks',
			]),
		]);
	}

	public function all()
	{
		$this->title = 'StudyTracks - Tracks List';
		$this->pageTitle = 'Tracks';

		$Tracks = ExamLevel::get();

		$row = $this->breadcrumbs->first();
		$row->Active = 1;

		return $this->renderPage('TracksList', [
			'Tracks' => $Tracks
		]);
	}

	public function byID()
	{
		if (! $this->request->param('ID')) {
			return $this->all();
		}

		$ExamLevel = ExamLevel::get()->filter(['ID' => $this->request->param('ID')])->first();

		$Subjects = $ExamLevel->Subjects();

		$this->pageTitle = $ExamLevel->Name;

		$this->breadcrumbs->add(new ArrayData([
			'Name' => $ExamLevel->Name,
			'Link' => '',
			'Active' => 1
		]));

		return $this->renderPage('TrackScreen', [
			'Subjects' => $Subjects
		]);
	}

	public function subject()
	{
		if (! $this->request->param('ID')) {
			return $this->all();
		}

		$Subject = Subject::get()->filter(['UUID' => $this->request->param('ID')])->first();

		$SubjectAreas = $Subject->SubjectArea();

		$LessonsList = new ArrayList();

		foreach($SubjectAreas as $area){

			$Lessons = $area->Lessons();

			foreach($Lessons as $lesson){

				$LessonsList->push($lesson);
			}
		}

		$this->pageTitle = $Subject->Name;

		$this->breadcrumbs->add(new ArrayData([
			'Name' => $Subject->Name,
			'Link' => '',
			'Active' => 1
		]));

		return $this->renderPage('LessonScreen', [
			'Lessons' => $LessonsList
		]);
	}

	public function lesson()
	{
		if (! $this->request->param('ID')) {
			return $this->all();
		}

		$Lesson = Lesson::get()->filter(['ID' => $this->request->param('ID')])->first();

		$this->pageTitle = $Lesson->Name;

		$this->breadcrumbs->add(new ArrayData([
			'Name' => $Lesson->Name,
			'Link' => '',
			'Active' => 1
		]));

		return $this->renderPage('LessonContentScreen', [
			'Lesson' => $Lesson
		]);
	}

}