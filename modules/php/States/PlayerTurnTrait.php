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

trait PlayerTurnTrait
{
  public function stPlayerTurn()
  {
    $this->giveExtraTime(Players::getActiveId());
  }

  public function argPlayerTurn()
  {
    $activePlayer = Players::getActive();
    $columns = Cards::getPlayableColorsInColumn($activePlayer);

    $playablesCard = Cards::getInLocationPId(HAND, $activePlayer->getId());

    $whereToPlay = [];

    foreach ($playablesCard as $cardId => $card) {
      $type = $card->getType();
      $whereToPlay[$cardId] = array_values(array_filter(array_keys($columns), fn ($columnId) => $columns[$columnId][$type]));
    }

    return [
      'nAction' => Globals::getMoveNumber() + 1,
      '_private' => [
        $activePlayer->getId() => [
          'canDraw' => Cards::countInLocation($activePlayer->getDeckName()) > 0,
          'columns' => $columns,
          'playableCardIds' => $whereToPlay,
          'discardableCardIds' => Cards::getDiscardableCards($activePlayer),
        ],
      ],
    ];
  }

  public function actDiscard($cardId)
  {
    // get infos
    $player = Players::getActive();
    self::checkAction('actDiscard');

    $args = $this->getArgs();

    if (!in_array($cardId, $args['_private'][$player->getId()]['discardableCardIds'])) {
      throw new \BgaVisibleSystemException("You can't discard this card, $cardId.");
    }

    $card = Cards::get($cardId);
    $columnId = $card->getColumnId();

    $nextState = $player->discardFromColumn($columnId);

    // $this->checkGetGuest($player, $columnId);

    // //check if the column must be discarded entirely
    // if ($n >= 3) {
    // 	$player->clearColumn($columnId);
    // }

    Globals::setActiveColumn($columnId);
    Globals::setLastAction('discard');

    $this->finishMove($nextState);
  }

  public function actPlay($cardId, $columnId)
  {
    // get infos
    $player = Players::getActive();
    self::checkAction('actPlay');

    $args = $this->getArgs();

    if (!array_key_exists($cardId, $args['_private'][$player->getId()]['playableCardIds'])) {
      throw new \BgaVisibleSystemException("You can't play this card, $cardId.");
    }
    if (!in_array($columnId, $args['_private'][$player->getId()]['playableCardIds'][$cardId])) {
      throw new \BgaVisibleSystemException("You can't play card $cardId in column $columnId.");
    }

    $columnName = $player->getColumnName($columnId);
    Cards::insertOnTop($cardId, $columnName);

    $card = Cards::get($cardId);
    $cardType = $card->getType();
    $n = Cards::getNOfSpecificColor($player, $columnId, $cardType);

    Notifications::play($card, $player, $columnId);

    //activate effect
    $method = 'playEffect' . ucfirst($cardType);
    $nextState = $this->$method($n, $player, $columnId);

    $this->checkGetGuest($player, $columnId);

    //check if the column must be discarded entirely
    if ($n >= 3) {
      $player->clearColumn($columnId);
    }

    Globals::setActiveColumn($columnId);
    Globals::setLastAction('play');

    $this->finishMove($nextState);
  }

  public function checkGetGuest($player, $columnId, $isPlayCardAction = true)
  {
    $guest = $player->getGuest($columnId);

    switch ($guest->getId()) {
      case 1:
        if (Cards::getTotalValue($player->getColumn($columnId)) < 9) {
          return;
        }
        break;
      case 2:
        if (!$isPlayCardAction || Globals::getActiveColumn() != $columnId || Globals::getLastAction() === 'discard') {
          return;
        }
        break;
      case 3:
        if (Cards::getNOfSpecificColor($player, $columnId, GREEN) < 3) {
          return;
        }
        break;
      case 4:
        if ($player->getColumn($columnId)->count() < 5) {
          return;
        }
        break;
      case 5:
        if (Cards::getNColors($player, $columnId) < 4) {
          return;
        }
        break;
      case 6:
        if (Cards::getNOfSpecificColor($player, $columnId, PURPLE) < 3) {
          return;
        }
        break;
      case 7:
        if (Cards::getNOfSpecificColor($player, $columnId, YELLOW) < 2) {
          return;
        }
        break;
      case 8:
        if (Cards::getNOfSpecificColor($player, $columnId, BLUE) < 3) {
          return;
        }
        break;
    }

    $player->secure($guest);
  }

  public function actDraw()
  {
    // get infos
    $pId = Game::get()->getCurrentPlayerId();
    self::checkAction('actDraw');

    $currentPlayer = Players::get($pId);

    $args = $this->getArgs();

    if (!$args['_private'][$pId]['canDraw']) {
      throw new \BgaVisibleSystemException("You can't draw a card, your deck is empty.");
    }

    Cards::draw($currentPlayer);

    $this->finishMove();
  }

  public function finishMove($nextState = 0)
  {
    //if there is an action to complete, do it
    if ($nextState) {
      $this->giveExtraTime(Players::getActiveId());
      Game::goTo($nextState);
    }
    //else play again if it's your first Move,
    elseif (Globals::getMoveNumber() == 0) {
      Globals::setMoveNumber(1);
      Game::transition('secondTurn');
    } else {
      //else end your turn
      Game::transition(END_TURN);
    }
  }
}
