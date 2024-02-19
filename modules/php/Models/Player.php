<?php

namespace GNC\Models;

use GNC\Core\Game;
use GNC\Core\Notifications;
use GNC\Core\Stats;
use GNC\Core\Preferences;
use GNC\Managers\Players;
use GNC\Managers\Cards;
use GNC\Managers\Cells;

/*
 * Player: all utility functions concerning a player
 */

class Player extends \GNC\Helpers\DB_Model
{
  private $map = null;
  protected $table = 'player';
  protected $primary = 'player_id';
  protected $attributes = [
    'id' => ['player_id', 'int'],
    'no' => ['player_no', 'int'],
    'name' => 'player_name',
    'color' => 'player_color',
    'eliminated' => 'player_eliminated',
    'score' => ['player_score', 'int'],
    'scoreAux' => ['player_score_aux', 'int'],
    'character' => 'character',
  ];

  public function secure($card)
  {
    $card->setFlipped(FLIPPED);
    Cards::insertOnTop($card->getId(), $this->getTreasureName());

    Notifications::secure($card, $this);
  }

  public function callBack($card)
  {
    $columnId = $card->getColumnId();
    $card->setLocation(HAND);

    Notifications::callBack($card, $columnId, $this);
  }

  public function move($card, $toColumnId)
  {
    $fromColumnId = $card->getColumnId();
    $card->setLocation($this->getColumnName($toColumnId));
    Notifications::move($card, $fromColumnId, $toColumnId, $this);
  }

  /**
   * Check if his own balloon explodes
   */
  public function checkBomb($columnId, $n)
  {
    $balloon = $this->getBalloons($columnId);
    $balloonValue = $balloon->getValue();
    if ($balloonValue <= $n) {
      $balloon->setFlipped(NOT_FLIPPED);
      Notifications::bombPass($this->getOpponent(), $columnId, $n, $balloon);
      //if there was a GUEST trash it
      $guest = $this->getGuest($columnId);
      if ($guest) {
        Cards::move($guest->getId(), 'trash');
      }
      return $this->hasLostGame() ? ST_PRE_END_OF_GAME : null;
    } else {
      Notifications::bombFail($this->getOpponent(), $columnId, $n, $balloon, $this);
    }
  }

  public function hasLostGame()
  {
    $balloons = $this->getBalloons();
    foreach ($balloons as $cardId => $balloon) {
      if ($balloon->getFlipped() == FLIPPED) {
        return false;
      }
    }
    return true;
  }

  public function countExplodedBallons()
  {
    $balloons = $this->getBalloons();
    $explodedBalloons = 0;
    foreach ($balloons as $cardId => $balloon) {
      if ($balloon->getFlipped() != FLIPPED) {
        $explodedBalloons++;
      }
    }
    return $explodedBalloons;
  }

  public function countScore()
  {
    $cards = Cards::getInLocation($this->getTreasureName());
    $score = 0;
    foreach ($cards as $cardId => $card) {
      $score += $card->getValue();
    }
    $this->setScore($score);
    Notifications::displayScore($score, $cards, $this);
  }

  public function discardFromColumn($columnId, $n = 1, $withEffect = true)
  {
    $nextState = 0;
    $cards = [];
    for ($i = 0; $i < $n; $i++) {
      $card = Cards::getTopOf($this->getColumnName($columnId));
      if (is_null($card)) {
        break;
      }

      $cards[] = $card;
      Cards::insertOnTop($card->getId(), $this->getDiscardName());
    }
    Notifications::discard($cards, $columnId, $this);

    if ($withEffect) {
      foreach ($cards as $card) {
        $method = 'discardEffect' . ucfirst($card->getType());
        $nextState = Game::get()->$method($columnId, $this);
      }
    }

    return $nextState;
  }

  public function clearColumn($columnId)
  {
    while ($card = Cards::getBottomOf($this->getColumnName($columnId))) {
      Cards::insertOnTop($card->getId(), $this->getDiscardName());
    }
    Notifications::clearColumn($columnId, $this);
  }

  public function getUiData($currentPlayerId = null)
  {
    $data = parent::getUiData();

    return $data;
  }

  public function is($character)
  {
    return $character === $this->getCharacter();
  }

  public function getOpponent()
  {
    $players = Players::getAll();
    foreach ($players as $pId => $player) {
      if ($pId != $this->id) {
        return $player;
      }
    }
  }

  public function getCardsInHand()
  {
    return Cards::getInLocationPId(HAND, $this->id);
  }

  public function getGuests($n = null)
  {
    $location = $this->is(CHAMOURAI) ? GUESTS_CHAMOURAI : GUESTS_POULPIRATE;
    return Cards::getInLocation($location, $n);
  }

  public function getGuest($n)
  {
    return $this->getGuests($n)->first();
  }

  public function getBalloons($n = null)
  {
    $location = $this->is(CHAMOURAI) ? BALLOONS_CHAMOURAI : BALLOONS_POULPIRATE;
    return !is_null($n) ? Cards::getInLocation($location, $n)->first() : Cards::getInLocation($location);
  }

  public function getColumn($n)
  {
    $location = $this->getColumnName($n);
    return Cards::getInLocation($location, null, 'card_state');
  }

  public function getlastCardOfColumn($columnId)
  {
    $location = 'column_' . $columnId . '_' . $this->getCharacter();
    return Cards::getTopOf($location);
  }

  public function getCardsOnTable()
  {
    return Cards::getInLocation('table', $this->id);
  }

  public function getDeckName()
  {
    return 'deck_' . $this->getCharacter();
  }

  public function getDiscardName()
  {
    return 'discard_' . $this->getCharacter();
  }

  public function getTreasureName()
  {
    return 'treasure_' . $this->getCharacter();
  }

  public function getColumnName($n)
  {
    return 'column_' . $n . '_' . $this->getCharacter();
  }

  /*
     █████████                                          ███                  
    ███░░░░░███                                        ░░░                   
   ███     ░░░   ██████  ████████    ██████  ████████  ████   ██████   █████ 
  ░███          ███░░███░░███░░███  ███░░███░░███░░███░░███  ███░░███ ███░░  
  ░███    █████░███████  ░███ ░███ ░███████  ░███ ░░░  ░███ ░███ ░░░ ░░█████ 
  ░░███  ░░███ ░███░░░   ░███ ░███ ░███░░░   ░███      ░███ ░███  ███ ░░░░███
   ░░█████████ ░░██████  ████ █████░░██████  █████     █████░░██████  ██████ 
    ░░░░░░░░░   ░░░░░░  ░░░░ ░░░░░  ░░░░░░  ░░░░░     ░░░░░  ░░░░░░  ░░░░░░  
                                                                             
                                                                             
                                                                             
  */

  public function getPref($prefId)
  {
    return Preferences::get($this->id, $prefId);
  }

  public function getStat($name)
  {
    $name = 'get' . \ucfirst($name);
    return Stats::$name($this->id);
  }
}
