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

trait ObserveTrait
{
	public function argObserve()
	{
		$activePlayer = Players::getActive();

		return [
			'cards' => Cards::getTopOf($activePlayer->getDeckName(), 2)->toArray()
		];
	}

	public function actObserve($cardId)
	{
		// // get infos
		// $player = Players::getActive();
		// self::checkAction('actObserve');

		// $args = $this->getArgs();

		// if (!in_array($cardId, $args['cardIds'])) {
		// 	throw new \BgaVisibleSystemException("You can't Observe this card, $cardId.");
		// }

		// $player->Observe(Cards::get($cardId));

		// Game::transition(END_TURN);
	}
}
