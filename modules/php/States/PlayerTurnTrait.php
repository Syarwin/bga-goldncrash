<?php

namespace GNC\States;

use PDO;
use GNC\Core\Game;
use GNC\Core\Globals;
use GNC\Core\Notifications;
use GNC\Core\Engine;
use GNC\Core\Stats;
use GNC\Managers\Cards;
use GNC\Helpers\Log;
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

    $isOneCardPlayable = false;

    foreach ($playablesCard as $cardId => $card) {
      $type = $card->getType();
      $wToP = array_values(array_filter(array_keys($columns), fn ($columnId) => $columns[$columnId][$type]));
      $whereToPlay[$cardId] = $wToP;
      if (!empty($wToP)) {
        $isOneCardPlayable = true;
      }
    }

    $discardableCards = Cards::getDiscardableCards($activePlayer);
    $canDraw = Cards::countInLocation($activePlayer->getDeckName()) > 0;

    return [
      // 'previousSteps' => Log::getUndoableSteps(),
      // 'previousChoices' => Globals::getChoices(),
      'nAction' => Globals::getMoveNumber() + 1,
      '_private' => [
        $activePlayer->getId() => [
          'canDraw' => $canDraw,
          'columns' => $columns,
          'playableCardIds' => $whereToPlay,
          'discardableCardIds' => $discardableCards,
          'mustPass' => empty($discardableCards) && !$isOneCardPlayable && !$canDraw
        ],
      ],
    ];
  }

  public function actPass()
  {
    $args = $this->getArgs();
    $player = Players::getActive();
    self::checkAction('actPass');

    if (!$args['_private'][$player->getId()]['mustPass']) {
      throw new \BgaVisibleSystemException("You can't pass now.");
    }

    Game::transition(END_TURN);
  }

  public function actDiscard($cardId)
  {
    // get infos
    $player = Players::getActive();
    self::checkAction('actDiscard');
    // $this->addStep();

    $args = $this->getArgs();

    if (!in_array($cardId, $args['_private'][$player->getId()]['discardableCardIds'])) {
      throw new \BgaVisibleSystemException("You can't discard this card, $cardId.");
    }

    $card = Cards::get($cardId);
    $columnId = $card->getColumnId();

    $nextState = $player->discardFromColumn($columnId);

    Globals::setActiveColumn($columnId);
    Globals::setLastAction('discard');

    $this->finishMove($nextState);
  }

  public function actPlay($cardId, $columnId)
  {
    // get infos
    $player = Players::getActive();
    self::checkAction('actPlay');
    // $this->addStep();

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
    if (is_null($guest)) {
      return;
    }

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
    // Globals::setCanReset(false);

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

      // if (!Globals::getCanReset()) {
      //   $this->addCheckpoint($nextState);
      // }

      Game::goTo($nextState);
    }
    //else play again if it's your first Move,
    elseif (Globals::getMoveNumber() == 0) {
      Globals::setMoveNumber(1);

      // if (!Globals::getCanReset()) {
      //   $this->addCheckpoint(ST_PLAYER_TURN);
      // }

      Game::transition('secondTurn');
    } else {

      // if (!Globals::getCanReset()) {
      //   $this->addCheckpoint(ST_NEXT_PLAYER);
      // }

      //else end your turn
      Game::transition(END_TURN);
    }
  }
}
