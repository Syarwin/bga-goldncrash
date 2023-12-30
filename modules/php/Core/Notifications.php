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
      'card' => $balloon->getUiData(),
    ];
    $msg = clienttranslate('With a bomb level ${force}, ${player_name} destroys Zeppelin in column ${displayableColumnId}');
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
      'player2' => $defensivePlayer,
      'balloonDeck' => $defensivePlayer->getCharacter(),
      'columnId' => $columnId,
      'force' => $n,
      'preserve' => ['player2']
    ];
    $msg = clienttranslate(
      'With a bomb level ${force}, ${player_name} failed to destroy Zeppelin in column ${displayableColumnId}'
    );
    static::notifyAll('bombFail', $msg, $data);

    $privateData = [
      'player' => $player,
      'player2' => $defensivePlayer,
      'balloonDeck' => $defensivePlayer->getCharacter(),
      'columnId' => $columnId,
      'force' => $n,
      'card' => $balloon->getUiData(),
      'value' => $balloon->getValue(),
      'preserve' => ['player2']
    ];
    $msg = clienttranslate(
      'With a bomb level ${force}, ${player_name} failed to destroy your Zeppelin in column ${displayableColumnId} of strength ${value}'
    );
    static::notify($defensivePlayer, 'pBombFail', $msg, $privateData);
  }

  /**
   * take in hand the last card of one column
   */
  public static function callBack($card, $columnId, $player)
  {
    $data = [
      'player' => $player,
      'card' => $card,
      'columnId' => $columnId,
    ];

    $msg = clienttranslate('${player_name} calls back the last card from his column ${displayableColumnId}');

    static::notifyAll('callBack', $msg, $data);
  }

  /**
   * clear a full column (triggered when a third card of same color is played in a column)
   * cards are placed in the discard in the same order they were in the column
   */
  public static function clearColumn($columnId, $player)
  {
    $data = [
      'player' => $player,
      'columnId' => $columnId,
    ];

    $msg = clienttranslate('${player_name} empty his column ${displayableColumnId}');

    static::notifyAll('clearColumn', $msg, $data);
  }

  public static function crackSafe($card, $player)
  {
    $data = [
      'player' => $player,
      'card' => $card,
    ];

    $msg = clienttranslate('${player_name} discards his last treasure');

    static::notifyAll('crackSafe', $msg, $data);
  }

  /**
   * move a card from top of a column to discard
   */
  public static function discard($cards, $columnId, $player)
  {
    $data = [
      'player' => $player,
      'card' => $cards,
      'columnId' => $columnId,
      'n' => count($cards),
    ];

    $msg = clienttranslate('${player_name} discard ${n} card(s) from his column ${displayableColumnId}');

    static::notifyAll('discard', $msg, $data);
  }

  /**
   * pick a card from discard or from deck
   */
  public static function draw($player, $cards, $fromDeck)
  {
    $data = [
      'player' => $player,
      'cards' => $cards->toArray(),
      'n' => $cards->count(),
      'fromDeck' => $fromDeck,
    ];

    $msg = $fromDeck
      ? clienttranslate('${player_name} draw ${n} card(s) from his deck')
      : clienttranslate('${player_name} draw ${n} card(s) from his discard pile');

    static::notify(
      $player,
      'pDrawCards',
      $fromDeck
        ? clienttranslate('You draw ${n} card(s) from your deck')
        : clienttranslate('You draw ${n} cards from the discard pile'),
      $data
    );
    unset($data['cards']);
    static::notifyAll('drawCards', $msg, $data);
  }

  public static function move($card, $fromColumnId, $toColumnId, $player)
  {
    $data = [
      'player' => $player,
      'card' => $card,
      'columnId' => $fromColumnId,
      'columnId2' => $toColumnId,
    ];

    $msg = clienttranslate('${player_name} move a card from column ${displayableColumnId} to column ${displayableColumnId2}');

    static::notifyAll('move', $msg, $data);
  }

  public static function observe($nCardsToPutBack, $nCardsToDiscard, $player)
  {
    $data = [
      'player' => $player,
      'nOnTop' => $nCardsToPutBack,
      'nOnBottom' => $nCardsToDiscard,
    ];

    $msg = clienttranslate('${player_name} observe his 2 next cards and replace them : ${nOnTop} on top and ${nOnBottom} on bottom');

    static::notifyAll('observe', $msg, $data);
  }

  /**
   * Move card from hand to a column
   */
  public static function play($card, $player, $columnId)
  {
    $data = [
      'player' => $player,
      'card' => $card,
      'columnId' => $columnId,
    ];

    $msg = clienttranslate('${player_name} play a new card on column ${displayableColumnId}');

    static::notifyAll('playCard', $msg, $data);
  }

  /**
   * flip a card and put it on Treasure
   */
  public static function secure($card, $player)
  {
    $data = [
      'player' => $player,
      'card' => $card,
    ];

    $msg =
      $card->getType() == GUEST
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

    if (isset($data['columnId'])) {
      $data['displayableColumnId'] = $data['columnId'] + 1;
      if (isset($data['columnId2'])) {
        $data['displayableColumnId2'] = $data['columnId2'] + 1;
      }
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
    static::notifyAll('refresh', '', []);
  }
}
