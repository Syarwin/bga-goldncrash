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

trait PlayCardTrait
{
	public function discardEffectGreen($columnId, $player)
	{
		return ST_OBSERVE;
	}

	public function discardEffectPurple($columnId, $player)
	{
		return ST_CALL_BACK;
	}

	public function discardEffectBlue($columnId, $player)
	{
		return ST_MOVE;
	}

	public function discardEffectRed($columnId, $player)
	{
		$opp = $player->getOpponent();
		$card = Cards::getTopOf($opp->getTreasureName());

		if ($card->getDeck() != GUEST) {
			Cards::move($card->getId(), $opp->getDiscardName());
			Notifications::crackSafe($card, $opp);
		}
	}

	public function discardEffectBrown($columnId, $player)
	{
		$card = Cards::getTopOf($player->getOpponent()->getDiscardName());
		$player->secure($card);
	}

	public function discardEffectYellow($columnId, $player)
	{
		return;
	}
}
