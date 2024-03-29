<?php

namespace GNC\Core;

use GoldnCrash;

/*
 * Game: a wrapper over table object to allow more generic modules
 */

class Game
{
  public static function get()
  {
    return GoldnCrash::get();
  }

  public static function getStateId()
  {
    static::get()->gamestate->state_id();
  }

  public static function isStateId($stateId)
  {
    return static::get()->gamestate->state_id() == $stateId;
  }


  public static function transition($transition = '')
  {
    static::get()->gamestate->nextState($transition);
  }

  public static function goTo($nextState)
  {
    static::get()->gamestate->jumpToState($nextState);
  }

  /**
   * check if the action is one of the possible actions in this state
   */
  public static function isPossibleAction($action)
  {
    static::get()->gamestate->checkPossibleAction($action);
  }

  /**
   * check if the current player is active and can perform this action
   */
  public static function checkAction($action)
  {
    static::get()->checkAction($action);
  }
}
