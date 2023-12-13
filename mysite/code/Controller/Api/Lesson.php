<?php

/**
 * Handle actions related to the logged in user
 *
 * @package Studytracks
 * @subpackage Controllers
 * @author Jonathan Little <jonathan@flipsidegroup.com>
 */
class Lesson_Controller extends Base_Controller
{

	/** {@inheritdoc} */
	protected $auth = true;

	/** {@inheritdoc} */
	private static $allowed_actions = array(
		'status',
	);

	/** {@inheritdoc} */
	private static $url_handlers = array(
		'/$ID//$dummy' => 'status',
	);

	public function status(SS_HTTPRequest $request)
	{
		if (! $request->isPOST()) return $this->handleError(404);

		$id = $request->param('ID');

		if (empty($id)) return $this->handleError(404);

		$lesson = Lesson::get()->filter(['UUID' => $id])->first();
		$pointsEarned = 0;

		if (empty($lesson)) return $this->handleError(404);

		if (isset($this->requestBody['favourite'])) {
			if ($this->requestBody['favourite']) {
				if (! FavouriteLesson::get()->filter(['StudentID' => $this->appUser->ID, 'LessonID' => $lesson->ID])->first()) {
					$fl = new FavouriteLesson;
					$fl->StudentID = $this->appUser->ID;
					$fl->LessonID = $lesson->ID;
					$fl->write();
				}
			} else {
				if ($fl = FavouriteLesson::get()->filter(['StudentID' => $this->appUser->ID, 'LessonID' => $lesson->ID])->first()) {
					$fl->delete();
				}
			}
		}

		if (isset($this->requestBody['completed'])) {
			if ($this->requestBody['completed']) {
				$cl = new CompletedLesson();
				$cl->StudentID = $this->appUser->ID;
				$cl->LessonID = $lesson->ID;
				$cl->write();

				$pointsEarned = $lesson->assignPoints($this->appUser);
			} else {
				if ($cl = CompletedLesson::get()->filter(['StudentID' => $this->appUser->ID, 'LessonID' => $lesson->ID])->first()) {
					$cl->delete();
				}
			}
		}

		return (new JsonApi)->formatReturn(['points_earned' => $pointsEarned]);
	}

}