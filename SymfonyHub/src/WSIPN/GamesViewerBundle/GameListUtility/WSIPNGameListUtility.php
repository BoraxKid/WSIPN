<?php

namespace WSIPN\GamesViewerBundle\GameListUtility;

class WSIPNGameListUtility
{
	public function countGamesByHours($game_list, $max_hours, $min_hours = -1)
	{
		$count = 0;
		foreach ($game_list['response']['games'] as $game)
		{
			if ($game['playtime_forever'] > $min_hours && $game['playtime_forever'] <= $max_hours * 60)
				$count++;
		}
		return ($count);
	}
	
	public function pickThreeGames($game_list)
	{
		foreach ($game_list['response']['games'] as $game)
		{
			if ($game['playtime_forever'] === 0)
				$not_played[] = $game;
		}
		$games[] = $not_played[rand(0, count($not_played))];
		$games[] = $not_played[rand(0, count($not_played))];
		$games[] = $not_played[rand(0, count($not_played))];
		return ($games);
	}
}