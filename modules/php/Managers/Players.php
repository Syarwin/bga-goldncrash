<?php

namespace GNC\Managers;

use GNC\Core\Game;
use GNC\Core\Globals;
use GNC\Core\Notifications;
use GNC\Core\Stats;
use GNC\Helpers\Utils;

/*
 * Players manager : allows to easily access players ...
 *  a player is an instance of Player class
 */

class Players extends \GNC\Helpers\DB_Manager
{
  protected static $table = 'player';
  protected static $primary = 'player_id';
  protected static function cast($row)
  {
    return new \GNC\Models\Player($row);
  }

  public static function setupNewGame($players, $options)
  {
    $characters = [CHAMOURAI, POULPIRATE];
    shuffle($characters);

    // Create players
    $gameInfos = Game::get()->getGameinfos();
    $colors = $gameInfos['player_colors'];
    $query = self::DB()->multipleInsert([
      'player_id',
      'player_color',
      'player_canal',
      'player_name',
      'player_avatar',
      'character'
    ]);

    $values = [];
    foreach ($players as $pId => $player) {
      $character = array_shift($characters);
      $color = array_shift($colors);

      $values[] = [
        $pId,
        $color,
        $player['player_canal'],
        $player['player_name'],
        $player['player_avatar'],
        $character
      ];
    }

    $query->values($values);

    Game::get()->reattributeColorsBasedOnPreferences($players, $gameInfos['player_colors']);
    Game::get()->reloadPlayersBasicInfos();
  }

  public function getActiveId()
  {
    return Game::get()->getActivePlayerId();
  }

  public function getCurrentId()
  {
    return (int) Game::get()->getCurrentPId();
  }

  public function getAll()
  {
    return self::DB()->get(false);
  }

  /*
   * get : returns the Player object for the given player ID
   */
  public function get($pId = null)
  {
    $pId = $pId ?: self::getActiveId();
    return self::DB()
      ->where($pId)
      ->getSingle();
  }

  public static function getActive()
  {
    return self::get();
  }

  public static function getCurrent()
  {
    return self::get(self::getCurrentId());
  }

  public function getNextId($player = null)
  {
    $player = $player ?? Players::getCurrent();
    $pId = is_int($player) ? $player : $player->getId();
    $table = Game::get()->getNextPlayerTable();
    return $table[$pId];
  }



  /*
   * Return the number of players
   */
  public function count()
  {
    return self::DB()->count();
  }

  /*
   * getUiData : get all ui data of all players
   */
  public static function getUiData($pId)
  {
    return self::getAll()
      ->map(function ($player) use ($pId) {
        return $player->getUiData($pId);
      })
      ->toAssoc();
  }

  /**
   * Get current turn order according to first player variable
   */
  public function getTurnOrder($firstPlayer = null)
  {
    $firstPlayer = $firstPlayer ?? Globals::getFirstPlayer();
    $order = [];
    $p = $firstPlayer;
    do {
      $order[] = $p;
      $p = self::getNextId($p);
    } while ($p != $firstPlayer);
    return $order;
  }

  /**
   * This allow to change active player
   */
  public static function changeActive($pId)
  {
    Game::get()->gamestate->changeActivePlayer($pId);
  }

  /*
  █████████                               ███     ██████   ███                  
 ███░░░░░███                             ░░░     ███░░███ ░░░                   
░███    ░░░  ████████   ██████   ██████  ████   ░███ ░░░  ████   ██████   █████ 
░░█████████ ░░███░░███ ███░░███ ███░░███░░███  ███████   ░░███  ███░░███ ███░░  
 ░░░░░░░░███ ░███ ░███░███████ ░███ ░░░  ░███ ░░░███░     ░███ ░███ ░░░ ░░█████ 
 ███    ░███ ░███ ░███░███░░░  ░███  ███ ░███   ░███      ░███ ░███  ███ ░░░░███
░░█████████  ░███████ ░░██████ ░░██████  █████  █████     █████░░██████  ██████ 
 ░░░░░░░░░   ░███░░░   ░░░░░░   ░░░░░░  ░░░░░  ░░░░░     ░░░░░  ░░░░░░  ░░░░░░  
             ░███                                                               
             █████                                                              
            ░░░░░                                                               
*/

  public static function getCharacterPId($character){
    if (!in_array($character, CHARACTERS)){
      throw new \feException(
        $character . 'is not a correct character in this game.'
    );

    $players = static::getAll();
    foreach ($players as $pId => $player) {
      if ($player->is($character)) {
        return $pId;
      }
    }
    }
  }
}
