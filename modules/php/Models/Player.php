<?php

namespace GNC\Models;

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

  public function getUiData($currentPlayerId = null)
  {
    $data = parent::getUiData();

    return $data;
  }

  public function is($character)
  {
    return $character === $this->getCharacter();
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
    return Cards::bindCard(Cards::getInLocation($location, $n)->first());
  }

  public function getColumn($n)
  {
    $location = 'column_' . $n . '_' . $this->getCharacter();
    return Cards::getInLocation($location, $n);
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
    return 'discard' . $this->getCharacter();
  }

  public function getTreasureName()
  {
    return 'treasure_' . $this->getCharacter();
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
