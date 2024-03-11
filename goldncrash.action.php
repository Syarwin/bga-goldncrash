<?php

use GNC\Core\CheatModule;

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * GoldnCrash implementation : ©  Timothée Pecatte <tim.pecatte@gmail.com>, Emmanuel Albisser <emmanuel.albisser@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 *
 * goldncrash.action.php
 *
 * GoldnCrash main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/goldncrash/goldncrash/myAction.html", ...)
 *
 */

class action_goldncrash extends APP_GameAction
{
  // Constructor: please do not modify
  public function __default()
  {
    if (self::isArg('notifwindow')) {
      $this->view = 'common_notifwindow';
      $this->viewArgs['table'] = self::getArg('table', AT_posint, true);
    } else {
      $this->view = 'goldncrash_goldncrash';
      self::trace('Complete reinitialization of board game');
    }
  }

  public function actConfirmTurn()
  {
    self::setAjaxMode();
    $this->game->actConfirmTurn();
    self::ajaxResponse();
  }

  public function actRestart()
  {
    self::setAjaxMode();
    $this->game->actRestart();
    self::ajaxResponse();
  }

  public function actUndoToStep()
  {
    self::setAjaxMode();
    $stepId = self::getArg('stepId', AT_posint, false);
    $this->game->actUndoToStep($stepId);
    self::ajaxResponse();
  }


  public function actPlay()
  {
    self::setAjaxMode();

    // Retrieve arguments
    // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
    $cardId = self::getArg('cardId', AT_posint, true);
    $columnId = self::getArg('columnId', AT_posint, true);

    // Then, call the appropriate method in your game logic, like "playCard" or "myAction"
    $this->game->actPlay($cardId, $columnId);

    self::ajaxResponse();
  }

  public function actDiscard()
  {
    self::setAjaxMode();

    // Retrieve arguments
    // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
    $cardId = self::getArg('cardId', AT_posint, true);

    // Then, call the appropriate method in your game logic, like "playCard" or "myAction"
    $this->game->actDiscard($cardId);

    self::ajaxResponse();
  }

  public function actSecure()
  {
    self::setAjaxMode();

    // Retrieve arguments
    // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
    $cardId = self::getArg('cardId', AT_posint, true);

    // Then, call the appropriate method in your game logic, like "playCard" or "myAction"
    $this->game->actSecure($cardId);

    self::ajaxResponse();
  }

  public function actMove()
  {
    self::setAjaxMode();

    // Retrieve arguments
    // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
    $cardId = self::getArg('cardId', AT_posint, true);
    $columnId = self::getArg('columnId', AT_posint, true);

    // Then, call the appropriate method in your game logic, like "playCard" or "myAction"
    $this->game->actMove($cardId, $columnId);

    self::ajaxResponse();
  }

  public function actCallBack()
  {
    self::setAjaxMode();

    // Retrieve arguments
    // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
    $cardId = self::getArg('cardId', AT_posint, true);

    // Then, call the appropriate method in your game logic, like "playCard" or "myAction"
    $this->game->actCallback($cardId);

    self::ajaxResponse();
  }

  public function actDraw()
  {
    self::setAjaxMode();

    // Retrieve arguments
    // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method

    // Then, call the appropriate method in your game logic, like "playCard" or "myAction"
    $this->game->actDraw();

    self::ajaxResponse();
  }

  public function actPass()
  {
    self::setAjaxMode();

    $this->game->actPass();

    self::ajaxResponse();
  }

  public function actObserve()
  {
    self::setAjaxMode();
    $cardsToPutBack = self::getArg('cardsToPutBack', AT_json, true);
    $cardsToDiscard = self::getArg('cardsToDiscard', AT_json, true);
    $this->validateJSonAlphaNum($cardsToPutBack, 'cardsToPutBack');
    $this->validateJSonAlphaNum($cardsToDiscard, 'cardsToDiscard');

    $this->game->actObserve($cardsToPutBack, $cardsToDiscard);
    self::ajaxResponse();
  }

  //   █████████  █████   █████ ██████████   █████████   ███████████
  //  ███░░░░░███░░███   ░░███ ░░███░░░░░█  ███░░░░░███ ░█░░░███░░░█
  // ███     ░░░  ░███    ░███  ░███  █ ░  ░███    ░███ ░   ░███  ░
  //░███          ░███████████  ░██████    ░███████████     ░███
  //░███          ░███░░░░░███  ░███░░█    ░███░░░░░███     ░███
  //░░███     ███ ░███    ░███  ░███ ░   █ ░███    ░███     ░███
  // ░░█████████  █████   █████ ██████████ █████   █████    █████
  //  ░░░░░░░░░  ░░░░░   ░░░░░ ░░░░░░░░░░ ░░░░░   ░░░░░    ░░░░░
  //
  //
  //

  public function cheat()
  {
    self::setAjaxMode();
    $data = self::getArg('data', AT_json, true);
    $this->validateJSonAlphaNum($data, 'data');

    CheatModule::actCheat($data);
    self::ajaxResponse();
  }

  public function loadBugSQL()
  {
    self::setAjaxMode();
    $reportId = (int) self::getArg('report_id', AT_int, true);
    $this->game->loadBugSQL($reportId);
    self::ajaxResponse();
  }

  public function validateJSonAlphaNum($value, $argName = 'unknown')
  {
    if (is_array($value)) {
      foreach ($value as $key => $v) {
        $this->validateJSonAlphaNum($key, $argName);
        $this->validateJSonAlphaNum($v, $argName);
      }
      return true;
    }
    if (is_int($value)) {
      return true;
    }
    $bValid = preg_match("/^[_0-9a-zA-Z- ]*$/", $value) === 1;
    if (!$bValid) {
      throw new BgaSystemException("Bad value for: $argName", true, true, FEX_bad_input_argument);
    }
    return true;
  }
}
