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

trait CallBackTrait
{
	public function argCallBack()
	{
		$activePlayer = Players::getActive();

		$cardIds = [];

		for ($i = 0; $i < 3; $i++) {
			$card = $activePlayer->getlastCardOfColumn($i);
			if ($card) {
				$cardIds[] = $card->getId();
			}
		}

		return [
			// 'previousSteps' => Log::getUndoableSteps(),
			// 'previousChoices' => Globals::getChoices(),
			'cardIds' => $cardIds
		];
	}

	public function actCallBack($cardId)
	{
		// get infos
		$player = Players::getActive();
		self::checkAction('actCallBack');
		// $this->addStep();

		$args = $this->getArgs();

		if (!in_array($cardId, $args['cardIds'])) {
			throw new \BgaVisibleSystemException("You can't CallBack this card, $cardId.");
		}

		$player->callBack(Cards::get($cardId));

		$this->finishMove();
	}
}
