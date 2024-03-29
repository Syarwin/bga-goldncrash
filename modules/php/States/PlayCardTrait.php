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
	public function playEffectGreen($n, $player, $columnId)
	{
		// Globals::setCanReset(false);
		Cards::draw($player, $n);
	}

	public function playEffectPurple($n, $player, $columnId)
	{
		Cards::draw($player, $n, false);
	}

	public function playEffectBlue($n, $player, $columnId)
	{
		$player->getOpponent()->discardFromColumn($columnId, $n, false);
	}

	public function playEffectRed($n, $player, $columnId)
	{
		// Globals::setCanReset(false);
		return $player->getOpponent()->checkBomb($columnId, $n);
	}

	public function playEffectBrown($n, $player, $columnId)
	{
		Globals::setRemainingActions($n);
		return ST_SECURE;
	}

	public function playEffectYellow($n, $player, $columnId)
	{
		return;
	}
}
