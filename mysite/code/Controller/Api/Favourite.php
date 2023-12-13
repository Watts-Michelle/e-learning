<?php

class Favourite_Controller extends Base_Controller
{

	/** {@inheritdoc} */
	protected $auth = true;

	/** {@inheritdoc} */
	private static $allowed_actions = array(
		'get',
		'delete'
	);

	/** {@inheritdoc} */
	private static $url_handlers = array(
		'/$ID//$dummy' => 'delete',
		'//$dummy' => 'handle',
	);

	public function handle(SS_HTTPRequest $request)
	{
		if ($request->isGET()) {
			return $this->get($request);
		}

		if ($request->isPOST()) {
			return $this->add($request);
		}

		if ($request->isDELETE()) {
			return $this->delete($request, $request->param('ID'));
		}
	}
	
	public function get(SS_HTTPRequest $request)
	{
		$favourites = $this->appUser->FavouriteLessons();
		
		$return = ['lesson' => []];
		
		foreach ($favourites as $lesson) {
			$lessonObj = $lesson->Lesson()->getBasic();

			if ($lessonObj) {
				$return['lesson'][] = $lessonObj;
			}
		}

		return (new JsonApi)->formatReturn($return);
	}

	public function add(SS_HTTPRequest $request)
	{
		$lesson = $this->getLesson();

		if (! FavouriteLesson::get()->filter(['StudentID' => $this->appUser->ID, 'LessonID' => $lesson->ID])->first()) {
			$fl = new FavouriteLesson;
			$fl->StudentID = $this->appUser->ID;
			$fl->LessonID = $lesson->ID;
			$fl->write();
		}

		return (new JsonApi)->formatReturn([]);
	}

	public function delete(SS_HTTPRequest $request, $id = null)
	{
		if (! $request->isDELETE()) {
			return $this->handle($request);
		}

		if (! $id) {
			$id = $request->param('ID');

			if (! $id) {
				$body = $this->requestBody;

				if (empty($body['id'])) {
					return $this->handleError(4007);
				}

				$id = $body['id'];
			}
		}

		$lesson = $this->getLesson($id);

		if ($fl = FavouriteLesson::get()->filter(['StudentID' => $this->appUser->ID, 'LessonID' => $lesson->ID])->first()) {
			$fl->delete();
		}

		return (new JsonApi)->formatReturn([]);
	}

	private function getLesson($id = null) {

		if (! $id) {
			$body = $this->requestBody;

			if (empty($body['id'])) {
				return $this->handleError(4007);
			}

			$id = $body['id'];
		}

		$lesson = Lesson::get()->filter(['UUID' => $id])->first();

		if (! $lesson) {
			$this->handleError(4008);
		}

		return $lesson;
	}
	
}