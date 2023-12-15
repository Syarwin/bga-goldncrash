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
		} else { //check if it IS last turn 
			$player = Players::getActive();
			if (Cards::countInLocation($player->getDeckName()) == 0) {
				Globals::setLastTurn(true);
			}

			//anyway launch new turn
			Globals::newTurn();
			$this->activeNextPlayer();

			Game::transition(END_TURN);
		}
	}
}
