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

trait PlayerTurnTrait
{
	public function argPlayerTurn()
	{
		$activePlayer = Players::getActive();
		$columns = Cards::getPlayableColorsInColumn($activePlayer);

		$playablesCard = Cards::getInLocationPId(HAND, $activePlayer->getId());

		$whereToPlay = [];

		foreach ($playablesCard as $cardId => $card) {
			$type = $card->getType();
			$whereToPlay[$cardId] = array_filter([1, 2, 3], fn ($columnId) => $columns[$columnId][$type]);
		}

		return [
			'_private' => [
				$activePlayer->getId() => [
					'canDraw' => Cards::countInLocation($activePlayer->getDeckName()) > 0,
					'columns' => $columns,
					'playableCardIds' => $whereToPlay,
					'discardableCardIds' => Cards::getDiscardableColumn($activePlayer)
				]
			]
		];
	}

	public function actDiscard($cardId, $columnId)
	{
	}

	public function actPlay($cardId, $columnId)
	{
	}

	public function actDraw()
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
