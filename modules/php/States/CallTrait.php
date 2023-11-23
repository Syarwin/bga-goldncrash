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

trait CallTrait
{
	public function argCall()
	{
		$activePlayer = Players::getActive();

		$uncallableCards = $activePlayer->getCardsOnTable();

		$callablePlayers = [];
		$players = Players::getAll()->toArray();

		foreach ($players as $player) {
			if ($player == $activePlayer) continue;
			if ($player->getCardsInHand(false) > 0) $callablePlayers[] = $player;
		}

		return [
			'caller' => $activePlayer,
			'uncallableCards' => $uncallableCards,
			'callablePlayers' => $callablePlayers
		];
	}

	public function actCall($pId2, $color, $value, $pId = null)
	{
		// get infos
		if (!$pId) {
			$pId = Game::get()->getCurrentPlayerId();
			self::checkAction(ACT_CALL);
		}

		$currentPlayer = Players::get($pId);
		$calledPlayer = Players::get($pId2);

		$args = $this->getArgs();

		if (!in_array($calledPlayer, $args['callablePlayers'])) {
			throw new \BgaVisibleSystemException("You can't call this player.");
		}

		foreach ($args['uncallableCards'] as $id => $card) {
			if ($card->getColor() == $color && $card->getValue() == $value)
				throw new \BgaVisibleSystemException("You can't ask this card.");
		}

		Notifications::call($currentPlayer, $calledPlayer, $color, $value);

		Globals::setCalledPlayer($pId2);
		Globals::setCalledValue($value);
		Globals::setCalledColor($color);


		$this->gamestate->nextState('');
	}
}
