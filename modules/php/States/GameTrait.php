<?php

namespace GNC\States;

use PDO;
use GNC\Core\Game;
use GNC\Core\Globals;
use GNC\Core\Notifications;
use GNC\Core\Engine;
use GNC\Core\Stats;
use GNC\Managers\Cards;
use GNC\Managers\Players;
use GNC\Models\Player;

trait GameTrait
{
	public function stNextPlayer()
	{
		//first check if it was last turn -> end
		if (Globals::getLastTurn()) {
			Game::transition(END_GAME);
			return;
		} else { //check if it IS last turn 
			$player = Players::getActive();
			if (Cards::countInLocation($player->getDeckName()) == 0) {
				Globals::setLastTurn(true);
				Notifications::lastTurn($player);
			}

			//anyway launch new turn
			Globals::newTurn();
			$this->activeNextPlayer();

			Game::transition(END_TURN);
		}
	}

	public function stPreEndOfGame()
	{
		$players = Players::getAll();

		foreach ($players as $pId => $player) {
			if ($player->hasLostGame()) {
				$player->setScore(1);
				$player->getOpponent()->setScore(0);
				Notifications::message(clienttranslate('${player_name} losts his last Zeppelin, game is over'), ['player' => $player]);
				Game::transition();
				return;
			}
		}

		foreach ($players as $pId => $player) {
			$player->countScore();
		}
		Game::transition();
	}
}
