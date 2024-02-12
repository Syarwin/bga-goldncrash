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
      '_private' => [
        'active' => [
          'cards' => Cards::getTopOf($activePlayer->getDeckName(), 2)->toArray(),
        ],
      ],
    ];
  }

  public function actObserve($cardsToPutBack, $cardsToDiscard)
  {
    // get infos
    $player = Players::getActive();
    self::checkAction('actObserve');

    $possibleCardIds = Cards::getTopOf($player->getDeckName(), 2)->getIds();

    foreach ($cardsToDiscard as $cardId) {
      if (!in_array($cardId, $possibleCardIds)) {
        throw new \BgaVisibleSystemException("You can't 'Observe' this card, $cardId. Should not happen");
      }
      Cards::insertAtBottom($cardId, $player->getDeckName());
    }
    $cardsToPutBack = array_reverse($cardsToPutBack);
    foreach ($cardsToPutBack as $cardId) {
      if (!in_array($cardId, $possibleCardIds)) {
        throw new \BgaVisibleSystemException("You can't 'Observe' this card, $cardId. Should not happen");
      }
      Cards::insertOnTop($cardId, $player->getDeckName());
    }

    Notifications::observe(count($cardsToPutBack), count($cardsToDiscard), $player);

    $this->finishMove();
  }
}
