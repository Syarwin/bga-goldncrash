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

trait DiscardTrait
{
  public function discardEffectGreen($columnId, $player)
  {
    return ST_OBSERVE;
  }

  public function discardEffectPurple($columnId, $player)
  {
    return ST_CALL_BACK;
  }

  public function discardEffectBlue($columnId, $player)
  {
    return ST_MOVE;
  }

  public function discardEffectRed($columnId, $player)
  {
    Globals::setCanReset(false);
    $opp = $player->getOpponent();
    $card = Cards::getTopOf($opp->getTreasureName());

    if ($card->getDeck() != GUEST) {
      Cards::move($card->getId(), $opp->getDiscardName());
      $lastTreasure = Cards::getLastTreasure($opp->getCharacter());
      Notifications::crackSafe($card, $opp, $lastTreasure);
    }
  }

  public function discardEffectBrown($columnId, $player)
  {
    $card = Cards::getTopOf($player->getOpponent()->getDiscardName());
    $player->secure($card);
  }

  public function discardEffectYellow($columnId, $player)
  {
    return;
  }
}
