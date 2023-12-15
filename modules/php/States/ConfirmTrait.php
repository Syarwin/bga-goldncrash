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

trait ConfirmTrait
{
	public function argConfirm()
	{
		$activePlayer = Players::getActive();

		return [];
	}

	public function stConfirm()
	{
		//TODO
		Game::transition(END_TURN);
	}

	public function actConfirm()
	{
		//TODO
	}

	public function actUndo()
	{
		//TODO
	}
}
