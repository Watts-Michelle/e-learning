<?php

class RankUsers_Task extends Controller
{

	public function init() {

		parent::init();

		if (php_sapi_name() != "cli") {
			if (!Member::currentUser()) {

				return Security::PermissionFailure($this->controller, 'You must be logged in to access this page');
			}

			if (!Member::currentUser()->inGroups(array('administrators'))) {
				return Security::PermissionFailure($this->controller, 'You must be logged in as admin to access this page');
			}
		}
	}

	public static $allowed_actions = array('index');

	public function index()
	{
		set_time_limit(180);
		// new Ranking(Student::get()->filter('Deleted', 0), true);

		// Update the ranking table

		// Stage one - get all Students ordered by score
		$query = new SQLSelect();
		$query->setFrom('"Student", "Member"');
		$query->setSelect(array('ID' => '"Student".ID' ));
		$query->selectField('"Student".Ranking', 'Ranking');
		$query->selectField('"Student".TotalPoints', 'TotalPoints');
		$query->addWhere(array('"Member".Deleted = ?' => 0 ));
		$query->addWhere('"Member".ID = "Student".ID');
		$query->setOrderBy('"Student".TotalPoints', 'DESC');

		$members = $query->execute();

		$rank = 1;
		$last_rank = $rank;
		$last_score = 0;

		foreach ($members as $member) {
			$previous = $member['Ranking'];

			if ($member['TotalPoints'] == $last_score) {
				$member['Ranking'] = $last_rank;
			} else {
				$member['Ranking'] = $rank;
				$last_rank = $rank;
			}

			$rank++;

			if ($previous != $member['Ranking']) {
				$update = SQLUpdate::create('Student');
				$update->addWhere(array('ID' => $member['ID']));
				$update->assign('Ranking', $member['Ranking']);
				$update->execute();
			}

			$last_score = $member['TotalPoints'];
		};

		echo date('Y-m-d H:i:s') . ': Leaderboard updated';
	}

}