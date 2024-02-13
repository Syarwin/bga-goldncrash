<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * GoldnCrash implementation : ©  Timothée Pecatte <tim.pecatte@gmail.com>, Emmanuel Albisser <emmanuel.albisser@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * states.inc.php
 *
 * GoldnCrash game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

require_once 'modules/php/constants.inc.php';

$machinestates = [
  // The initial state. Please do not modify.
  ST_GAME_SETUP => [
    'name' => 'gameSetup',
    'description' => '',
    'type' => 'manager',
    'action' => 'stGameSetup',
    'transitions' => [
      '' => ST_PLAYER_TURN,
    ],
  ],

  ST_PLAYER_TURN => [
    'name' => 'playerTurn',
    'description' => clienttranslate('Action ${nAction}: ${actplayer} must draw, play or discard a card'),
    'descriptionmyturn' => clienttranslate('Action ${nAction}: ${you} must draw, play or discard a card'),
    'type' => ACTIVE_PLAYER,
    'args' => 'argPlayerTurn',
    'action' => 'stPlayerTurn',
    'possibleactions' => ['actPlay', 'actDraw', 'actDiscard'],
    'transitions' => [
      'secondTurn' => ST_PLAYER_TURN,
      'secure' => ST_SECURE,
      'move' => ST_MOVE,
      'callBack' => ST_CALL_BACK,
      'observer' => ST_OBSERVE,
      END_TURN => ST_CONFIRM,
    ],
  ],

  ST_CONFIRM => [
    'name' => 'confirm',
    'description' => clienttranslate('${actplayer} must confirm his turn'),
    'descriptionmyturn' => clienttranslate('${you} must confirm your turn'),
    'type' => ACTIVE_PLAYER,
    'args' => 'argConfirm',
    'action' => 'stConfirm',
    'possibleactions' => ['actConfirm', 'actUndo'],
    'transitions' => [
      UNDO => ST_PLAYER_TURN,
      END_TURN => ST_NEXT_PLAYER,
    ],
  ],

  ST_NEXT_PLAYER => [
    'name' => 'nextPlayer',
    'description' => clienttranslate('Next player'),
    'type' => GAME,
    'action' => 'stNextPlayer',
    'transitions' => [
      END_GAME => ST_PRE_END_OF_GAME,
      END_TURN => ST_PLAYER_TURN,
    ],
  ],

  ST_SECURE => [
    'name' => 'secure',
    'description' => clienttranslate('${actplayer} must secure a card from one of his adjacent columns (X ${remainingActions})'),
    'descriptionmyturn' => clienttranslate('${you} must secure a card from one of your adjacent columns (X ${remainingActions})'),
    'type' => ACTIVE_PLAYER,
    'args' => 'argSecure',
    'action' => 'stSecure',
    'possibleactions' => ['actSecure'],
    'transitions' => [
      'secondTurn' => ST_PLAYER_TURN,
      AGAIN => ST_SECURE,
      END_TURN => ST_CONFIRM,
    ],
  ],

  ST_MOVE => [
    'name' => 'move',
    'description' => clienttranslate('${actplayer} must move a card from one of his adjacent columns'),
    'descriptionmyturn' => clienttranslate('${you} must move a card from one of your adjacent columns'),
    'type' => ACTIVE_PLAYER,
    'args' => 'argMove',
    'possibleactions' => ['actMove'],
    'transitions' => [
      'secondTurn' => ST_PLAYER_TURN,
      END_TURN => ST_CONFIRM,
    ],
  ],

  ST_CALL_BACK => [
    'name' => 'callBack',
    'description' => clienttranslate('${actplayer} must call back a card from one of his columns'),
    'descriptionmyturn' => clienttranslate('${you} must call back a card from one of your columns'),
    'type' => ACTIVE_PLAYER,
    'args' => 'argCallBack',
    'possibleactions' => ['actCallBack'],
    'transitions' => [
      'secondTurn' => ST_PLAYER_TURN,
      END_TURN => ST_CONFIRM,
    ],
  ],

  ST_OBSERVE => [
    'name' => 'observe',
    'description' => clienttranslate(
      '${actplayer} can observe the 2 first cards of his Crew deck and replace them on the top or on the bottom'
    ),
    'descriptionmyturn' => clienttranslate(
      '${you} can observe the 2 first cards of your Crew deck and replace them on the top or on the bottom'
    ),
    'type' => ACTIVE_PLAYER,
    'args' => 'argObserve',
    'possibleactions' => ['actObserve'],
    'transitions' => [
      'secondTurn' => ST_PLAYER_TURN,
      END_TURN => ST_NEXT_PLAYER,
    ],
  ],

  ST_PRE_END_OF_GAME => [
    'name' => 'preEndOfGame',
    'type' => GAME,
    'action' => 'stPreEndOfGame',
    'transitions' => ['' => ST_END_GAME],
  ],

  // Final state.
  // Please do not modify (and do not overload action/args methods).
  ST_END_GAME => [
    'name' => 'gameEnd',
    'description' => clienttranslate('End of game'),
    'type' => 'manager',
    'action' => 'stGameEnd',
    'args' => 'argGameEnd',
  ],
];
