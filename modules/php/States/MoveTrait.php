<?php

namespace GNC\States;

use PDO;
use GNC\Core\Game;
use GNC\Core\Globals;
use GNC\Core\Notifications;
use GNC\Core\Engine;
use GNC\Core\Stats;
use GNC\Helpers\Log;
use GNC\Managers\Cards;
use GNC\Managers\Players;
use GNC\Models\Player;

trait MoveTrait
{
	public function argMove()
	{
		$activePlayer = Players::getActive();

		$cards = [];

		for ($i = 0; $i < 3; $i++) {
			$card = $activePlayer->getlastCardOfColumn($i);
			if ($card) {
				$cards[$card->getId()] = ADJACENT_COLUMNS[$i];
			}
		}

		return [
			'cardIds' => $cards,
			'previousSteps' => Log::getUndoableSteps(),
			'previousChoices' => Globals::getChoices(),
		];
	}

	public function actMove($cardId, $columnId)
	{
		// get infos
		$player = Players::getActive();
		self::checkAction('actMove');
		// $this->addStep();

		$args = $this->getArgs();

		if (!array_key_exists($cardId, $args['cardIds']) && !in_array($columnId, $args['cardIds'][$cardId])) {
			throw new \BgaVisibleSystemException("You can't Move this card, $cardId, on column $columnId.");
		}

		$card = Cards::get($cardId);
		$player->move($card, $columnId);

		//check if the column must be discarded entirely
		if (Cards::getNOfSpecificColor($player, $columnId, $card->getType()) >= 3) {
			$player->clearColumn($columnId);
		}

		$this->finishMove();
	}
}
