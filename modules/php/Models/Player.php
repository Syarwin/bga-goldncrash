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

  /**
   * TODO Check if his own balloon explodes
   */
  public function checkBomb($columnId, $n)
  {
    $balloon = $this->getBalloons($columnId);
    $balloonValue = $balloon->getValue();
    if ($balloonValue <= $n) {
      $balloon->setFlipped(NOT_FLIPPED);
      Notifications::bombPass($this->getOpponent(), $columnId, $n, $balloon);
    } else {
      Notifications::bombFail($this->getOpponent(), $columnId, $n, $balloon, $this);
    }
    //TODO Notif
  }

  public function discardFromColumn($columnId, $n = 1, $withEffect = false)
  {
    $nextState = 0;
    for ($i = 0; $i < $n; $i++) {
      $card = Cards::getTopOf($this->getColumnName($columnId));
      Cards::insertOnTop($card->getId(), $this->getDiscardName());
      if ($withEffect) {
        $method = 'discardEffect' . ucfirst($card->getType());
        $nextState = Game::get()->$method($card, $columnId, $this);
      }
    }
    //TODO Notif
    return $nextState;
  }

  public function clearColumn($columnId)
  {
    while (true) {
      $card = Cards::getBottomOf($this->getColumnName($columnId));
      Cards::insertOnTop($card->getId(), $this->getDiscardName());
    }

    //TODO Notif
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
      if ($pId != $this->id) return $player;
    }
  }

  public function getCardsInHand($isCurrent = true)
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
    return Cards::getInLocation($location, $n)->first();
  }

  public function getColumn($n)
  {
    $location = $this->getColumnName($n);
    return Cards::getInLocation($location);
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
