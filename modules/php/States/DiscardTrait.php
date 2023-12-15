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
	public function discardEffectGreen($card, $columnId, $player)
	{
	}

	public function discardEffectPurple($card, $columnId, $player)
	{
	}

	public function discardEffectBlue($card, $columnId, $player)
	{
	}

	public function discardEffectRed($card, $columnId, $player)
	{
	}

	public function discardEffectBrown($card, $columnId, $player)
	{
	}

	public function discardEffectYellow($card, $columnId, $player)
	{
		return;
	}
}
