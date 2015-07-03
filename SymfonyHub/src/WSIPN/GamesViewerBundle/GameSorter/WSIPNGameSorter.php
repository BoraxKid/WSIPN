<?php

namespace WSIPN\GamesViewerBundle\GameSorter;

class WSIPNGameSorter
{
	public function getGameList($id, $key, $force_update = false)
	{
		if ($id !== '')
		{
			if (($force_update === false) && (file_exists('gamelists/' . $id . '.json') !== false))
				$game_list = file_get_contents('gamelists/' . $id . '.json');
			else if (!($game_list = file_get_contents('http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key=' . $key . '&steamid=' . $id . '&format=json&include_appinfo=1&include_played_free_games=1')))
				throw new \Exception('Unable to retrieve your Game library from Steam');
			$array = json_decode($game_list, true);
			if (!isset($array['response']['date']))
				$array['response']['date'] = time();
			file_put_contents('gamelists/' . $id . '.json', json_encode($array, JSON_PRETTY_PRINT));
			return ($array);
		}
		return (NULL);
	}
	
	public function ascAppid($a, $b)
	{
		return ($a['appid'] > $b['appid']);
	}
	
	public function descAppid($a, $b)
	{
		return ($a['appid'] < $b['appid']);
	}
	
	public function ascPlaytime($a, $b)
	{
		return ($a['playtime_forever'] > $b['playtime_forever']);
	}
	
	public function descPlaytime($a, $b)
	{
		return ($a['playtime_forever'] < $b['playtime_forever']);
	}
	
	public function ascName($a, $b)
	{
		return ($a['name'] > $b['name']);
	}
	
	public function descName($a, $b)
	{
		return ($a['name'] < $b['name']);
	}
	
	public function sortGameList($array, $sort = 'asc_playtime')
	{
		if ($sort === 'asc_playtime')
			usort($array['response']['games'], array($this, 'ascPlaytime'));
		if ($sort === 'desc_playtime')
			usort($array['response']['games'], array($this, 'descPlaytime'));
		if ($sort === 'asc_name')
			usort($array['response']['games'], array($this, 'ascName'));
		if ($sort === 'desc_name')
			usort($array['response']['games'], array($this, 'descName'));
		if ($sort === 'asc_appid')
			usort($array['response']['games'], array($this, 'ascAppid'));
		if ($sort === 'desc_appid')
			usort($array['response']['games'], array($this, 'descAppid'));
		return ($array);
	}
}