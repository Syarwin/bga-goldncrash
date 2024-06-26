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

trait SecureTrait
{
  public function stSecure()
  {
  }

  public function argSecure()
  {
    $activePlayer = Players::getActive();
    $activeColumn = Globals::getActiveColumn();

    $columnIds = ADJACENT_COLUMNS[$activeColumn];

    $cardIds = [];

    foreach ($columnIds as $columnId) {
      $card = $activePlayer->getlastCardOfColumn($columnId);
      if ($card) {
        $cardIds[] = $card->getId();
      }
    }

    return [
      'cardIds' => $cardIds,
      'remainingActions' => Globals::getRemainingActions(),
      'mustPass' => !$cardIds
      // 'previousSteps' => Log::getUndoableSteps(),
      // 'previousChoices' => Globals::getChoices(),
    ];
  }

  public function actSecure($cardId)
  {
    // get infos
    $player = Players::getActive();
    self::checkAction('actSecure');
    // $this->addStep();

    $args = $this->getArgs();

    if (!in_array($cardId, $args['cardIds'])) {
      throw new \BgaVisibleSystemException("You can't secure this card, $cardId.");
    }

    $player->secure(Cards::get($cardId));

    $remainingActions = Globals::getRemainingActions() - 1;

    if ($remainingActions > 0) {
      Globals::setRemainingActions($remainingActions);
      Game::transition(AGAIN);
    } else {
      $this->finishMove();
    }
  }
}
