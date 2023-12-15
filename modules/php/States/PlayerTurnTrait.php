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
			$whereToPlay[$cardId] = array_filter(array_keys($columns), fn ($columnId) => $columns[$columnId][$type]);
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
		// get infos
		$player = Players::getActive();
		self::checkAction('actPlay');

		$args = $this->getArgs();

		if (!array_key_exists($cardId, $args['_private'][$player->getId()]['playableCardIds'])) {
			throw new \BgaVisibleSystemException("You can't play this card, $cardId.");
		}
		if (!in_array($columnId, $args['_private'][$player->getId()]['playableCardIds'][$cardId])) {
			throw new \BgaVisibleSystemException("You can't play card $cardId in column $columnId.");
		}

		$columnName = $player->getColumnName($columnId);
		Cards::insertOnTop($cardId, $columnName);

		$cardType = Cards::get($cardId)->getType();
		$n = Cards::getNOfSpecificColor($player, $columnId, $cardType);

		//activate effect
		$method = 'playEffect' . ucfirst($cardType);
		$nextState = $this->$method($n, $player, $columnId);

		$this->checkGetGuest($player, $columnId);

		//check if the column must be discarded entirely
		if ($n >= 3) {
			$player->clearColumn($columnId);
		}

		Globals::setActiveColumn($columnId);

		$this->finishMove($nextState);
	}

	public function checkGetGuest($player, $columnId)
	{
		$guest = $player->getGuest($columnId);

		switch ($guest->getId()) {
			case 1:
				if (Cards::getTotalValue($player->getColumn($columnId)) < 9) return;
				break;
			case 2:
				if (Globals::getActiveColumn() != $columnId) return;
				break;
			case 3:
				if (Cards::getNOfSpecificColor($player, $columnId, GREEN) < 3) return;
				break;
			case 4:
				if ($player->getColumn($columnId)->count() < 5) return;
				break;
			case 5:
				if (Cards::getNColors($player, $columnId) < 4) return;
				break;
			case 6:
				if (Cards::getNOfSpecificColor($player, $columnId, PURPLE) < 3) return;
				break;
			case 7:
				if (Cards::getNOfSpecificColor($player, $columnId, YELLOW) < 2) return;
				break;
			case 8:
				if (Cards::getNOfSpecificColor($player, $columnId, BLUE) < 3) return;
				break;
		}

		$player->secure($guest);
	}

	public function actDraw()
	{
		// get infos
		$pId = Game::get()->getCurrentPlayerId();
		self::checkAction('actDraw');

		$currentPlayer = Players::get($pId);

		$args = $this->getArgs();

		if (!$args['_private'][$pId]['canDraw']) {
			throw new \BgaVisibleSystemException("You can't draw a card, your deck is empty.");
		}

		Cards::draw($currentPlayer);

		$this->finishMove();
	}

	public function finishMove($nextState = 0)
	{
		//if there is an action to complete, do it
		if ($nextState) Game::goTo($nextState);
		//else play again if it's your first Move,
		else if (Globals::getMoveNumber() == 0) {
			Globals::setMoveNumber(1);
			Game::transition('secondTurn');
		} else { //else end your turn
			Game::transition(END_TURN);
		}
	}
}
