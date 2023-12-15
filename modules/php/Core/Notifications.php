<?php

namespace GNC\Core;

use GNC\Managers\Cards;
use GNC\Managers\Players;
use GNC\Helpers\Utils;
use GNC\Core\Globals;

class Notifications
{
  /**
   * To flip matching zeppelin card
   */
  public static function bombPass($player, $columnId, $n, $balloon)
  {
    $data = [
      'player' => $player,
      'columnId' => $columnId,
      'force' => $n,
      'card' => $balloon
    ];
    $msg = clienttranslate('With a bomb level ${force}, ${player_name} destroys Zeppelin in column ${columnId}');
    static::notifyAll('bombPass', $msg, $data);
  }

  /**
   * To inform players that the Bomb attack failed
   * (but give information to owner of the Zeppelin value)
   */
  public static function bombFail($player, $columnId, $n, $balloon, $defensivePlayer)
  {
    $data = [
      'player' => $player,
      'columnId' => $columnId,
      'force' => $n
    ];
    $privateData = [
      'card' => $balloon,
      'columnId' => $columnId,
      'value' => $balloon->getValue()
    ];
    static::notify($defensivePlayer, 'bombcheck', clienttranslate('(Your Zeppelin in column ${columnId) has a strengh of ${value})'), $privateData);
    $msg = clienttranslate('With a bomb level ${force}, ${player_name} failed to destroy Zeppelin in column ${columnId}');
    static::notifyAll('bombPass', $msg, $data);
  }

  /**
   * pick a card from discard or from deck
   */
  public static function draw($player, $cards, $fromDeck)
  {
    $data = [
      'player' => $player,
      'cards' => $cards,
      'n' => $cards->count(),
      'fromDeck' => $fromDeck
    ];

    $msg = ($fromDeck) ? clienttranslate('${player_name} draw ${n} card(s) from his deck')
      : clienttranslate('${player_name} draw ${n} card(s) from his discard pile');

    static::notify($player, 'draw', '', $data);
    unset($data['cards']);
    static::notifyAll('draw', $msg, $data);
  }

  /**
   * Move card from hand to a column
   */
  public static function play($card, $player, $columnId)
  {
    $data = [
      'player' => $player,
      'card' => $card,
      'columnId' => $columnId
    ];

    $msg = clienttranslate('${player_name} play a new card on column ${columnId}');

    static::notifyAll('playCard', $msg, $data);
  }


  /**
   * flip a card and put it on Treasure
   */
  public static function secure($card, $player)
  {
    $data = [
      'player' => $player,
      'card' => $card
    ];

    $msg = ($card->getType() == GUEST)
      ? clienttranslate('${player_name} definitely secure a Guest and all cards under it')
      : clienttranslate('${player_name} secure a new card');

    static::notifyAll('secure', $msg, $data);
  }

  /*************************
   **** GENERIC METHODS ****
   *************************/
  protected static function notifyAll($name, $msg, $data)
  {
    self::updateArgs($data);
    Game::get()->notifyAllPlayers($name, $msg, $data);
  }

  protected static function notify($player, $name, $msg, $data)
  {
    $pId = is_int($player) ? $player : $player->getId();
    self::updateArgs($data);
    Game::get()->notifyPlayer($pId, $name, $msg, $data);
  }

  public static function message($txt, $args = [])
  {
    self::notifyAll('message', $txt, $args);
  }

  public static function messageTo($player, $txt, $args = [])
  {
    $pId = is_int($player) ? $player : $player->getId();
    self::notify($pId, 'message', $txt, $args);
  }

  /*********************
   **** UPDATE ARGS ****
   *********************/

  private static function addDataCoord(&$data, $x, $y)
  {
    $data['x'] = $x;
    $data['y'] = $y;
    $data['displayX'] = $x + 1;
    $data['displayY'] = $y + 1;
  }

  /*
   * Automatically adds some standard field about player and/or card
   */
  protected static function updateArgs(&$data)
  {
    if (isset($data['player'])) {
      $data['player_name'] = $data['player']->getName();
      $data['player_id'] = $data['player']->getId();
      unset($data['player']);
    }

    if (isset($data['player2'])) {
      $data['player_name2'] = $data['player2']->getName();
      $data['player_id2'] = $data['player2']->getId();
      unset($data['player2']);
    }
  }

  //          █████                          █████     ███                     
  //         ░░███                          ░░███     ░░░                      
  //  ██████  ░███████    ██████   ██████   ███████   ████  ████████    ███████
  // ███░░███ ░███░░███  ███░░███ ░░░░░███ ░░░███░   ░░███ ░░███░░███  ███░░███
  //░███ ░░░  ░███ ░███ ░███████   ███████   ░███     ░███  ░███ ░███ ░███ ░███
  //░███  ███ ░███ ░███ ░███░░░   ███░░███   ░███ ███ ░███  ░███ ░███ ░███ ░███
  //░░██████  ████ █████░░██████ ░░████████  ░░█████  █████ ████ █████░░███████
  // ░░░░░░  ░░░░ ░░░░░  ░░░░░░   ░░░░░░░░    ░░░░░  ░░░░░ ░░░░ ░░░░░  ░░░░░███
  //                                                                   ███ ░███
  //                                                                  ░░██████ 
  //                                                                   ░░░░░░  

  public static function cheat()
  {
    static::notifyAll('refresh', "", []);
  }

  public static function invitePlayersToAlpha($name, $message, $data)
  {
    static::notify(Players::getCurrent(), $name, $message, $data);
  }
}
