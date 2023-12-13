<?php

class InfoPoints_Controller extends Base_Controller
{

	/** {@inheritdoc} */
	protected $auth = true;

	/** {@inheritdoc} */
	private static $allowed_actions = array(
		'get',
	);

	/** {@inheritdoc} */
	private static $url_handlers = array(
		'' => 'get',
	);

	public function get()
	{
		$return = [
			'points' => $this->getPoints(),
			'levels' => $this->getPointLevels(),
		];

		return (new JsonApi)->formatReturn($return);
	}

	private function getPoints()
	{
		$settings = SiteConfig::current_site_config();

		$array = [];

		$array[] = array(
			'name' => 'Completed lesson',
			'points' => (int) $settings->PointsCompletedLesson,
		);

		$array[] = array(
			'name' => 'Completed test',
			'points' => (int) $settings->PointsCompletedQuiz,
		);

		$array[] = array(
			'name' => 'Completed an entire Subject',
			'points' => (int) $settings->PointsCompletedSubject,
		);

		$array[] = array(
			'name' => 'Completed an entire SubSubject',
			'points' => (int) $settings->PointsCompletedSubjectArea,
		);

		foreach (PointQuizBracket::get() as $pointQuizBracket) {
			$array[] = $pointQuizBracket->getBasic();
		}


		return $array;
	}

	private function getPointLevels()
	{
		$array = [];

		foreach (PointLevel::get()->sort('Points', 'asc') as $pointLevel) {
			$array[] = $pointLevel->getBasic();
		}

		return $array;
	}

}