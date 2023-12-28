<?php
/*
* Game Constants
*/

const CHAMOURAI = 'Chamourai';
const GUEST = 'guest';
const BALLOON = 'balloon';
const BROWN = 'brown';
const PURPLE = 'purple';
const GREEN = 'green';
const POULPIRATE = 'Poulpirate';
const YELLOW = 'Yellow';
const BLUE = 'Blue';
const RED = 'Red';

const CHARACTERS = [CHAMOURAI, POULPIRATE];

//Possible locations
const HAND = 'hand';
const COLUMN_0_POULPIRATE = 'column_0_' . POULPIRATE;
const COLUMN_1_POULPIRATE = 'column_1_' . POULPIRATE;
const COLUMN_2_POULPIRATE = 'column_2_' . POULPIRATE;
const COLUMN_0_CHAMOURAI = 'column_0_' . CHAMOURAI;
const COLUMN_1_CHAMOURAI = 'column_1_' . CHAMOURAI;
const COLUMN_2_CHAMOURAI = 'column_2_' . CHAMOURAI;
const DECK_POULPIRATE = 'deck_' . POULPIRATE;
const DECK_CHAMOURAI = 'deck_' . CHAMOURAI;
const DECK_GUEST = 'deck_' . GUEST;
const TREASURE_POULPIRATE = 'treasure_' . POULPIRATE;
const TREASURE_CHAMOURAI = 'treasure_' . CHAMOURAI;
const DISCARD_POULPIRATE = 'discard_' . POULPIRATE;
const DISCARD_CHAMOURAI = 'discard_' . CHAMOURAI;

const GUESTS_POULPIRATE = 'guest_' . POULPIRATE; //state 0, 1, 2 for column
const GUESTS_CHAMOURAI = 'guest_' . CHAMOURAI; //state 0, 1, 2 for column
const BALLOONS_POULPIRATE = 'balloon_' . POULPIRATE; //state 0, 1, 2 for column
const BALLOONS_CHAMOURAI = 'balloon_' . CHAMOURAI; //state 0, 1, 2 for column

const ADJACENT_COLUMNS = [
	0 => [1],
	1 => [0, 2],
	2 => [1],
];

/*
 * State constants
 */
const ST_GAME_SETUP = 1;

const ST_PLAYER_TURN = 2;
const ST_CONFIRM = 3;
const ST_NEXT_PLAYER = 4;

//after play card
const ST_SECURE = 5;

//after discard card
const ST_MOVE = 10;
const ST_CALL_BACK = 11;
const ST_OBSERVE = 12;



const ST_PRE_END_OF_GAME = 98;
const ST_END_GAME = 99;


/****
 * Cheat Module
 */

const OPTION_DEBUG = 103;
const OPTION_DEBUG_OFF = 0;
const OPTION_DEBUG_ON = 1;

/******************
 ****** STATS ******
 ******************/

// const STAT_GNCLECTED_CRISTAL = 11;
// const STAT_WATER_SOURCES_POINTS = 12;
// const STAT_ANIMALS_POINTS = 13;
// const STAT_BIOMES_POINTS = 14;
// const STAT_SPORES_POINTS = 15;
// const STAT_ALIGNMENTS = 16;
// const STAT_END_STEP_ACTIVATIONS = 17;
// const STAT_END_ROUND_ACTIVATIONS = 18;

// const STAT_NAME_GNCLECTED_CRISTAL = 'collectedCristal';
// const STAT_NAME_WATER_SOURCES_POINTS = 'waterSourcePoints';
// const STAT_NAME_ANIMALS_POINTS = 'animalsPoints';
// const STAT_NAME_BIOMES_POINTS = 'biomesPoints';
// const STAT_NAME_SPORES_POINTS = 'sporePoints';
// const STAT_NAME_ALIGNMENTS = 'alignments';
// const STAT_NAME_END_STEP_ACTIVATIONS = 'endStepActivations';
// const STAT_NAME_END_ROUND_ACTIVATIONS = 'endRoundActivations';

/*
*  ██████╗ ███████╗███╗   ██╗███████╗██████╗ ██╗ ██████╗███████╗
* ██╔════╝ ██╔════╝████╗  ██║██╔════╝██╔══██╗██║██╔════╝██╔════╝
* ██║  ███╗█████╗  ██╔██╗ ██║█████╗  ██████╔╝██║██║     ███████╗
* ██║   ██║██╔══╝  ██║╚██╗██║██╔══╝  ██╔══██╗██║██║     ╚════██║
* ╚██████╔╝███████╗██║ ╚████║███████╗██║  ██║██║╚██████╗███████║
*  ╚═════╝ ╚══════╝╚═╝  ╚═══╝╚══════╝╚═╝  ╚═╝╚═╝ ╚═════╝╚══════╝
*                                                               
*/

const FLIPPED = 1;
const NOT_FLIPPED = 0;
const VISIBLE = 0;

const GAME = "game";
const MULTI = "multipleactiveplayer";
const PRIVATESTATE = "private";
const ACTIVE_PLAYER = "activeplayer";

//transitions

const END_TURN = 'endTurn';
const END_GAME = 'endGame';
const UNDO = 'undo';
const PASS = 'pass';
const AGAIN = 'again';
