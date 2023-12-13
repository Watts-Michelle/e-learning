<?php

/**
 * Created by PhpStorm.
 * User: jonathanlittle
 * Date: 22/09/2016
 * Time: 13:08
 */
class RankingRow
{

	public function getBasic(Member $member)
	{
		return [
			'rank' => (int) $member->Ranking,
			'username' => $member->Username ?: $member->FullName,
			'points' => (int) $member->TotalPoints,
			'me' => CurrentUser::getUserID() == $member->ID ? 1 : 0
		];

	}

}