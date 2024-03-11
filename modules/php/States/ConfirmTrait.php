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
use GNC\Helpers\Log;

//useless now, finally no undo in this game.

trait ConfirmTrait
{
	public function addCheckpoint($state)
	{
		Globals::setChoices(0);
		Globals::setCanReset(true);
		Log::checkpoint($state);
	}

	public function addStep()
	{
		$stepId = Log::step($this->gamestate->state_id());
		Notifications::newUndoableStep(Players::getCurrent(), $stepId);
		Globals::incChoices();
	}

	public function argsConfirmTurn()
	{
		$data = [
			'previousSteps' => Log::getUndoableSteps(),
			'previousChoices' => Globals::getChoices(),
		];
		return $data;
	}

	public function stConfirmTurn()
	{
		if (Globals::getChoices() == 0) {
			$this->actConfirmTurn(true);
		}
	}

	public function actConfirmTurn($auto = false)
	{
		if (!$auto) {
			self::checkAction('actConfirmTurn');
		}
		$this->gamestate->nextState('confirm');
	}


	public function actRestart()
	{
		self::checkAction('actRestart');
		Log::undoTurn();
	}

	public function actUndoToStep($stepId)
	{
		self::checkAction('actRestart');
		Log::undoToStep($stepId);
	}
}
