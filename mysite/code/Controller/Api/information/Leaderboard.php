<?php

class Leaderboard_Controller extends Base_Controller
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
		$ranking = new Ranking(Student::get()->filter(array(
			'Deleted' => 0,
			'Ranking:GreaterThan' => 0
		)));
		$leaders = $ranking->getRows(0, 20);

		$return = [
			'ranking' => $ranking->getPosition($this->appUser),
			'top' => $leaders,
		];

		return (new JsonApi)->formatReturn($return);
	}

}