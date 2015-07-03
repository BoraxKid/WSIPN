<?php

namespace WSIPN\GamesViewerBundle\SteamUtility;

use Symfony\Component\HttpFoundation\Request;

class WSIPNSteamUtility
{
	public function generateUrl($request)
	{
		$return_url = (($request->isSecure()) ? 'https' : 'http') . '://' . $request->getHost() . $request->getScriptName() . $request->getPathInfo();
	
		$params = array(
			'openid.ns'			=> 'http://specs.openid.net/auth/2.0',
			'openid.mode'		=> 'checkid_setup',
			'openid.return_to'	=> $return_url,
			'openid.realm'		=> (($request->isSecure()) ? 'https' : 'http') . '://' . $request->getHost(),
			'openid.identity'	=> 'http://specs.openid.net/auth/2.0/identifier_select',
			'openid.claimed_id'	=> 'http://specs.openid.net/auth/2.0/identifier_select',
		);
		
		return ('https://steamcommunity.com/openid/login?' . http_build_query($params, '', '&'));

	}
	
	public function getSteamID($request)
	{
		/**/
		if ($request->query->has('openid_assoc_handle') && $request->query->has('openid_signed') && $request->query->has('openid_sig'))
		{
			$params = array(
				'openid.assoc_handle'	=> $request->query->get('openid_assoc_handle'),
				'openid.signed'			=> $request->query->get('openid_signed'),
				'openid.sig'			=> $request->query->get('openid_sig'),
				'openid.ns'				=> 'http://specs.openid.net/auth/2.0',
			);
			$signed = explode(',', $request->query->get('openid_signed'));
			foreach ($signed as $item)
			{
				$val = $request->query->get('openid_' . str_replace('.', '_', $item));
				$params['openid.' . $item] = get_magic_quotes_gpc() ? stripslashes($val) : $val;
			}
			$params['openid.mode'] = 'check_authentication';
			$data = http_build_query($params);
			$context = stream_context_create(array(
				'http'	=> array(
					'method'	=> 'POST',
					'header'	=> "Accept-language: en\r\n".
								   "Content-type: application/x-www-form-urlencoded\r\n" .
								   "Content-Length: " . strlen($data) . "\r\n",
					'content'	=> $data
				),
			));
			$result = file_get_contents('https://steamcommunity.com/openid/login', false, $context);
			
			preg_match("#^http://steamcommunity.com/openid/id/([0-9]{17,25})#", $request->query->get('openid_claimed_id'), $matches);
			$steamID64 = is_numeric($matches[1]) ? $matches[1] : 0;
			
			return (preg_match("#is_valid\s*:\s*true#i", $result) == 1 ? $steamID64 : '');
		}
		return ('');
		/**/
		return ('76561197989442376');
	}
	
	public function checkID($request, $session, $key)
	{
		$tmp = WSIPNSteamUtility::getSteamID($request);
		$steam_id64 = '';
		if (($steam_id64 = (($session->has('steam_id')) ? $session->get('steam_id') : '')) == '' && $tmp != '')
		{
			$session->set('steam_id', $tmp);
			$steam_id64 = $tmp;
		}
		if ($request->query->has('logout') && $request->query->get('logout') == 1)
		{
			$session->clear();
			$steam_id64 = '';
		}
		if ($steam_id64 != '' && !$session->has('pseudo'))
		{
			if (($user_info = file_get_contents('http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=' . $key . '&steamids=' . $steam_id64)) === false)
				throw new \Exception('Unable to retrieve Steam account informations');
			$decode = json_decode($user_info, true);
			$session->set('pseudo', $decode['response']['players'][0]['personaname']);
			$session->set('avatar', $decode['response']['players'][0]['avatarfull']);
		}
		return ($steam_id64);
	}
}