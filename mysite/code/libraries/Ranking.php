<?php

class Ranking
{

	/** @var ArrayList $rankings */
	private $rankings;

	public function __construct(DataList $members, $rerank = false) {

		$this->rankings = $members;

		if ($rerank) {
			$this->rankings = $this->rank($members);
		}
	}

	public function getPosition(Member $member)
	{
		$row = $this->rankings->byID($member->ID);

		if (empty($row)) return $this->rankings->count();

		return $row->Ranking;
	}

	public function getRows($start, $limit = 20) {
		$rows = $this->rankings->sort('Ranking', 'asc')->limit($limit, $start);

		$response = [];

		foreach ($rows as $row) {
			$response[] = (new RankingRow())->getBasic($row);
		}

		return $response;
	}

	/**
	 * @param DataList $members
	 * @return mixed
	 */
	private function rank($members)
	{

		$rank = 1;
		$last_rank = $rank;
		$last_score = 0;

		foreach ($members->sort('TotalPoints', 'desc') as $member) {
			$previous = $member->Ranking;

			if ($member->TotalPoints == $last_score) {
				$member->Ranking = $last_rank;
			} else {
				$member->Ranking = $rank;
				$last_rank = $rank;
			}

			$rank++;

			if ($previous != $member->Ranking) {
				$member->write();
			}
			
			$last_score = $member->TotalPoints;
		};

		return $members;
	}
}