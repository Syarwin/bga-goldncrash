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

trait TurnTrait
{
	public function argPlayerTurn()
	{
		$activePlayer = Players::getActive();

		return [
			'player_name' => $activePlayer->getName()
		];
	}

	public function actDrawPlay()
	{
		// get infos
		$pId = Game::get()->getCurrentPlayerId();
		self::checkAction('actDraw');

		$currentPlayer = Players::get($pId);

		$args = $this->getArgs();

		if (!$args['canDraw']) {
			throw new \BgaVisibleSystemException("You can't draw a card, your deck is empty.");
		}

		$card = Cards::pickOneForLocationPId($currentPlayer->getDeckName(), 'hand', $pId);

		Notifications::draw($currentPlayer, $card);

		$this->gamestate->nextState(END_TURN);
	}
}
