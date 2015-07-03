<?php

namespace WSIPN\GamesViewerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
	public function getSession($request)
	{
		if (($session = $request->getSession()) === NULL)
			$session = new Session();
		if (!$session->isStarted())
			$session->start();
		return ($session);
	}
	
    public function indexAction(Request $request)
    {
		$session = DefaultController::getSession($request);
		$steam_utility = $this->container->get('wsipn_games_viewer.steam_utility');
		$game_sorter = $this->container->get('wsipn_games_viewer.game_sorter');
		$gl_utility = $this->container->get('wsipn_games_viewer.game_list_utility');
		$steam_id64 = $steam_utility->checkID($request, $session, $this->container->getParameter('steam_api_key'));
		if ($steam_id64 != '')
			$game_list = $game_sorter->getGameList($steam_id64, $this->container->getParameter('steam_api_key'));
        return ($this->render('WSIPNGamesViewerBundle:Default:index.html.twig', array(
			'title'       => 'What should I play now?',
			'login_url'   => $steam_utility->generateUrl($request),
			'steam_id64'  => $steam_id64,
			'avatar'      => ($session->has('avatar')) ? $session->get('avatar') : '',
			'pseudo'      => ($session->has('pseudo')) ? $session->get('pseudo') : '',
			'not_played'  => (isset($game_list)) ? $gl_utility->countGamesByHours($game_list, 0) : -1,
			'one_played'  => (isset($game_list)) ? $gl_utility->countGamesByHours($game_list, 1, 0) : -1,
			'game_count'  => (isset($game_list['response']['game_count'])) ? $game_list['response']['game_count'] : -1,
			'three_games' => (isset($game_list)) ? $gl_utility->pickThreeGames($game_list) : NULL,
		)));
    }
	
	public function showAction(Request $request, $sort)
	{
		$session = DefaultController::getSession($request);
		$steam_utility = $this->container->get('wsipn_games_viewer.steam_utility');
		$game_sorter = $this->container->get('wsipn_games_viewer.game_sorter');
		$steam_id64 = $steam_utility->checkID($request, $session, $this->container->getParameter('steam_api_key'));
		if ($steam_id64 != '')
		{
			if ($request->query->has('force_update') && $request->query->get('force_update') == 1)
				$game_list = $game_sorter->getGameList($steam_id64, $this->container->getParameter('steam_api_key'), true);
			else
				$game_list = $game_sorter->getGameList($steam_id64, $this->container->getParameter('steam_api_key'));
			$game_list = $game_sorter->sortGameList($game_list, $sort);
		}
		
		return ($this->render('WSIPNGamesViewerBundle:Default:show.html.twig', array(
			'title'       => 'What should I play now?',
			'login_url'   => $steam_utility->generateUrl($request),
			'steam_id64'  => $steam_id64,
			'game_list'   => (isset($game_list['response']['games'])) ? $game_list['response']['games'] : NULL,
			'last_update' => (isset($game_list['response']['date'])) ? $game_list['response']['date'] : 0,
			'avatar' => ($session->has('avatar')) ? $session->get('avatar') : '',
			'pseudo'  => ($session->has('pseudo')) ? $session->get('pseudo') : '',
			'sort'        => $sort,
		)));
	}
}
