/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * goldncrash implementation : ©  Timothée Pecatte <tim.pecatte@gmail.com>, Emmanuel Albisser <emmanuel.albisser@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * goldncrash.js
 *
 * goldncrash user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

var isDebug =
  window.location.host == "studio.boardgamearena.com" ||
  window.location.hash.indexOf("debug") > -1;
var debug = isDebug ? console.info.bind(window.console) : function () {};

define([
  "dojo",
  "dojo/_base/declare",
  "ebg/core/gamegui",
  "ebg/counter",
  g_gamethemeurl + "modules/js/Core/game.js",
  g_gamethemeurl + "modules/js/Core/modal.js",
], function (dojo, declare) {
  const CHAMOURAI = "CHAMOURAI";
  const POULPIRATE = "POULPIRATE";
  const GUEST = "GUEST";
  const BALLOON = "BALLOON";

  const BROWN = "BROWN";
  const PURPLE = "PURPLE";
  const GREEN = "GREEN";
  const YELLOW = "YELLOW";
  const BLUE = "BLUE";
  const RED = "RED";

  return declare("bgagame.goldncrash", [customgame.game], {
    constructor() {
      this._activeStates = ["playerTurn"];
      this._notifications = [
        ["playCard", 1200],
        ["secure", 2000],
        ["drawCards", null, (notif) => notif.args.player_id == this.player_id],
        ["pDrawCards", null],
        ["bombPass", 2000],
        ["bombFail", 2000, (notif) => notif.args.player_id2 == this.player_id],
        ["pBombFail", 3000],
        ["discard", null],
        ["crackSafe", 1200],
        ["move", 1200],
        ["clearColumn", null],
        //  ['confirmSetupObjectives', 1200],
        //  ['clearTurn', 200],
        //  ['refreshUI', 200],
      ];

      this._fakeCardCounter = -1;

      // Fix mobile viewport (remove CSS zoom)
      this.default_viewport = "width=740";
      this.cardStatuses = {};
    },
    notif_midMessage(n) {},

    getSettingsSections() {
      return {
        layout: _("Layout"),
        playerBoard: _("Player Board/Panel"),
        gameFlow: _("Game Flow"),
        other: _("Other"),
      };
    },

    getSettingsConfig() {
      return {};
    },

    /**
     * Setup:
     *	This method set up the game user interface according to current game situation specified in parameters
     *	The method is called each time the game interface is displayed to a player, ie: when the game starts and when a player refreshes the game page (F5)
     *
     * Params :
     *	- mixed gamedatas : contains all datas retrieved by the getAllDatas PHP method.
     */
    setup(gamedatas) {
      debug("SETUP", gamedatas);
      // Create a new div for "subtitle"
      dojo.place("<div id='pagesubtitle'></div>", "maintitlebar_content");

      this.setupInfoPanel();
      this.setupPlayers();
      this.setupCards();
      this.inherited(arguments);
    },

    setupPlayers() {
      // Change No so that it fits the current player order view
      let currentNo = Object.values(this.gamedatas.players).reduce(
        (carry, player) => (player.id == this.player_id ? player.no : carry),
        0
      );
      let nPlayers = Object.keys(this.gamedatas.players).length;
      this.forEachPlayer(
        (player) =>
          (player.order = (player.no + nPlayers - currentNo) % nPlayers)
      );
      this.orderedPlayers = Object.values(this.gamedatas.players).sort(
        (a, b) => a.order - b.order
      );
      this.bottomPId = this.orderedPlayers[0].id;
      this.topPId = this.orderedPlayers[1].id;

      // Add player board and player panel
      this._counters = {};
      this.orderedPlayers.forEach((player, i) => {
        let pos = this.getPos(player.id);
        $(`${pos}-player`).dataset.character = player.character;
        // Panels
        this.place(
          "tplPlayerPanel",
          player,
          `overall_player_board_${player.id}`
        );

        this._counters[player.id] = {};
        this._counters[player.id]["deckCount"] = this.createCounter(
          `deck-counter-${pos}`
        );
        this._counters[player.id]["handCount"] = this.createCounter(
          `counter-${player.id}-hand`
        );
      });
    },

    tplPlayerPanel(player) {
      return `<div class='player-info'>
        <div class='hand-counter-wrapper'>
          <span id='counter-${player.id}-hand'>0</span>
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 296.664 296.664">
            <path d="M 58.355,226.748 V 69.414 c 0,-1.709 0.294,-3.391 0.526,-5.039 L 13.778,79.057 C 3.316,82.455 -2.42,93.797 0.979,104.258 l 48.639,149.633 c 2.738,8.428 10.639,13.816 19.075,13.816 2.035,0 4.109,-0.315 6.143,-0.975 l 12.796,-4.211 C 71.066,259.213 58.355,244.242 58.355,226.748 Z" />
            <path d="M 91.098,203.275 139.715,53.673 c 0.491,-1.512 1.078,-3.342 1.746,-4.342 H 94.688 c -11,0 -20.333,9.082 -20.333,20.082 v 157.334 c 0,11 9.333,20.584 20.333,20.584 h 15.969 C 94.061,239.332 85.361,220.932 91.098,203.275 Z" />
            <path d="M 282.848,79.057 180.134,45.684 c -2.034,-0.662 -4.102,-0.975 -6.138,-0.975 -8.436,0 -16.326,5.387 -19.064,13.814 l -48.617,149.633 c -3.399,10.463 2.379,21.803 12.841,25.203 l 102.713,33.373 c 2.034,0.66 4.102,0.975 6.138,0.975 8.436,0 16.326,-5.389 19.064,-13.816 L 295.689,104.258 C 299.088,93.797 293.31,82.455 282.848,79.057 Z" />
          </svg>
        </div>
      </div>`;
    },

    getPos(pId) {
      return this.bottomPId == pId ? "bottom" : "top";
    },

    getCPos(character) {
      return $(`bottom-player`).dataset.character.toUpperCase() ==
        character.toUpperCase()
        ? "bottom"
        : "top";
    },

    onLoadingComplete() {
      this.updateLayout();
      this.inherited(arguments);
    },

    onScreenWidthChange() {
      if (this.settings) this.updateLayout();
    },

    onAddingNewUndoableStepToLog(notif) {
      if (!$(`log_${notif.logId}`)) return;
      let stepId = notif.msg.args.stepId;
      $(`log_${notif.logId}`).dataset.step = stepId;
      if ($(`dockedlog_${notif.mobileLogId}`))
        $(`dockedlog_${notif.mobileLogId}`).dataset.step = stepId;

      if (this.gamedatas && this.gamedatas.gamestate) {
        let state = this.gamedatas.gamestate;
        if (state.private_state) state = state.private_state;

        if (
          state.args &&
          state.args.previousSteps &&
          state.args.previousSteps.includes(parseInt(stepId))
        ) {
          this.onClick($(`log_${notif.logId}`), () => this.undoToStep(stepId));

          if ($(`dockedlog_${notif.mobileLogId}`))
            this.onClick($(`dockedlog_${notif.mobileLogId}`), () =>
              this.undoToStep(stepId)
            );
        }
      }
    },

    undoToStep(stepId) {
      this.stopActionTimer();
      this.checkAction("actRestart");
      this.takeAction("actUndoToStep", { stepId }, false);
    },

    notif_clearTurn(n) {
      debug("Notif: restarting turn", n);
      this.cancelLogs(n.args.notifIds);
    },

    notif_refreshUI(n) {
      debug("Notif: refreshing UI", n);
      this.clearPossible();
      //  ['cards', 'meeples', 'players', 'tiles'].forEach((value) => {
      //    this.gamedatas[value] = n.args.datas[value];
      //  });
      //  this.setupMeeples();
    },

    onUpdateActionButtons(stateName, args) {
      //        this.addPrimaryActionButton('test', 'test', () => this.testNotif());
      this.inherited(arguments);
    },

    testNotif() {},

    clearPossible() {
      dojo.empty("pagesubtitle");
      this.inherited(arguments);
    },

    onEnteringState(stateName, args) {
      debug("Entering state: " + stateName, args);
      if (this.isFastMode() && ![].includes(stateName)) return;

      if (args.args && args.args.descSuffix) {
        this.changePageTitle(args.args.descSuffix);
      }

      if (
        this._activeStates.includes(stateName) &&
        !this.isCurrentPlayerActive()
      )
        return;

      // Call appropriate method
      var methodName =
        "onEnteringState" +
        stateName.charAt(0).toUpperCase() +
        stateName.slice(1);
      if (this[methodName] !== undefined) this[methodName](args.args);
    },

    onEnteringStatePlayerTurn(publicArgs) {
      let args = publicArgs._private;

      if (args.canDraw) {
        this.addPrimaryActionButton("btnDraw", _("Draw"), () =>
          this.takeAction("actDraw", {})
        );
      }

      Object.keys(args.playableCardIds).forEach((cardId) => {
        let columns = args.playableCardIds[cardId];
        if (columns.length)
          this.onClick(`card-${cardId}`, () =>
            this.clientState(
              "playerTurnChooseColumn",
              _("Where do you want to play that card?"),
              { cardId, columns }
            )
          );
      });

      let selectedCard = null;
      Object.values(args.discardableCardIds).forEach((cardId) => {
        this.onClick(`card-${cardId}`, () => {
          if (selectedCard !== null)
            $(`card-${selectedCard}`).classList.remove("selected");
          selectedCard = cardId;
          $(`card-${selectedCard}`).classList.add("selected");
          this.addPrimaryActionButton("btnConfirm", _("Confirm discard"), () =>
            this.takeAction("actDiscard", { cardId: selectedCard })
          );
        });
      });
    },

    onEnteringStatePlayerTurnChooseColumn(args) {
      this.addCancelStateBtn();
      $(`card-${args.cardId}`).classList.add("selected");

      let pos = this.getPos(this.player_id);
      let selectedColumn = null;
      args.columns.forEach((col) => {
        this.onClick(`column-${pos}-${col}`, () => {
          if (selectedColumn != null)
            $(`column-${pos}-${selectedColumn}`).classList.remove("selected");
          selectedColumn = col;
          $(`column-${pos}-${selectedColumn}`).classList.add("selected");
          this.addPrimaryActionButton("btnConfirm", _("Confirm"), () =>
            this.takeAction("actPlay", {
              cardId: args.cardId,
              columnId: selectedColumn,
            })
          );
        });
      });
    },

    onEnteringStateSecure(args) {
      let selectedCard = null;
      args.cardIds.forEach((cardId) => {
        this.onClick(`card-${cardId}`, () => {
          if (selectedCard !== null)
            $(`card-${selectedCard}`).classList.remove("selected");
          selectedCard = cardId;
          $(`card-${selectedCard}`).classList.add("selected");
          this.addPrimaryActionButton("btnConfirm", _("Confirm secure"), () =>
            this.takeAction("actSecure", { cardId: selectedCard })
          );
        });
      });
    },

    onEnteringStateMove(args) {
      Object.keys(args.cardIds).forEach((cardId) => {
        let columns = args.cardIds[cardId];
        if (columns.length)
          this.onClick(`card-${cardId}`, () =>
            this.clientState(
              "moveChooseColumn",
              _("Where do you want to move that card?"),
              { cardId, columns }
            )
          );
      });
    },

    onEnteringStateMoveChooseColumn(args) {
      this.addCancelStateBtn();
      $(`card-${args.cardId}`).classList.add("selected");

      let pos = this.getPos(this.player_id);
      args.columns.forEach((col) => {
        this.onClick(`column-${pos}-${col}`, () => {
          this.takeAction("actMove", {
            cardId: args.cardId,
            columnId: col,
          });
        });
      });

      // let selectedColumn = null;
      // args.columns.forEach((col) => {
      //   this.onClick(`column-${pos}-${col}`, () => {
      //     if (selectedColumn != null) $(`column-${pos}-${selectedColumn}`).classList.remove('selected');
      //     selectedColumn = col;
      //     $(`column-${pos}-${selectedColumn}`).classList.add('selected');
      //     this.addPrimaryActionButton('btnConfirm', _('Confirm'), () =>
      //       this.takeAction('actMove', {
      //         cardId: args.cardId,
      //         columnId: selectedColumn,
      //       })
      //     );
      //   });
      // });
    },

    onEnteringStateObserve(publicArgs) {
      let bottom = [],
        top = [];
      let args = publicArgs._private;
      let updateStatus = () => {
        args.cards.forEach((card) => {
          let o = $(`card-${card.id}`);
          delete o.dataset.nbr;
          delete o.dataset.pos;
        });

        top.forEach((cardId, i) => {
          let o = $(`card-${cardId}`);
          o.dataset.nbr = i + 1;
          o.dataset.pos = _("TOP");
        });

        bottom.forEach((cardId, i) => {
          let o = $(`card-${cardId}`);
          o.dataset.nbr = i + 1;
          o.dataset.pos = _("BOTTOM");
        });

        $("btnConfirm").classList.toggle(
          "disabled",
          top.length + bottom.length != 2
        );
      };

      args.cards.forEach((card) => {
        let cardId = card.id;
        this.addCard(card, $("pending-deck-cards"));
        this.onClick(`card-${cardId}`, () => {
          this.multipleChoiceDialog(
            _("Where do you want to place that card?"),
            [_("Top"), _("Bottom")],
            (choice) => {
              let onTop = choice == 0;
              top = top.filter((v) => v != cardId);
              bottom = bottom.filter((v) => v != cardId);
              if (onTop) top.push(cardId);
              else bottom.push(cardId);

              updateStatus();
            }
          );
        });
      });

      this.addPrimaryActionButton("btnConfirm", _("Confirm"), () => {
        this.takeAction("actObserve", {
          cardsToPutBack: JSON.stringify(top),
          cardsToDiscard: JSON.stringify(bottom),
        });
      });
      updateStatus();
    },

    onLeavingStateObserve() {
      dojo.empty("pending-deck-cards");
    },

    ////////////////////////////////
    //    ____              _
    //   / ___|__ _ _ __ __| |___
    //  | |   / _` | '__/ _` / __|
    //  | |__| (_| | | | (_| \__ \
    //   \____\__,_|_|  \__,_|___/
    ////////////////////////////////

    setupCards() {
      // TODO : clear cards to refresh UI
      this.forEachPlayer((player) => {
        let cards = this.gamedatas.cards[player.id];
        cards.hand.forEach((card) => this.addCard(card));
        cards.ballons.forEach((card) => this.addCard(card));
        cards.guests.forEach((card) => {
          if (card) this.addCard(card);
        });
        cards.discard.forEach((card) => this.addCard(card));
        cards.columns.forEach((column) =>
          column.forEach((card) => this.addCard(card))
        );
        if (cards.lastTreasure) {
          cards.lastTreasure.type = "BACK";
          this.addCard(cards.lastTreasure);
        }

        this._counters[player.id]["deckCount"].toValue(cards.nDeck);
        this._counters[player.id]["handCount"].toValue(cards.nHand);
      });
    },

    addCard(card, location = null) {
      let isBack = card.type == "BACK";
      card.uid = card.uid || card.id;
      if (card.uid == -1) card.uid = this._fakeCardCounter--;
      else {
        card = Object.assign(card, this.getCardData(card));
      }

      if (isBack) {
        card.type = "BACK";
      }

      if ($("card-" + card.uid)) return;

      let o = this.place(
        "tplCard",
        card,
        location == null ? this.getCardContainer(card) : location
      );
      let tooltipDesc = this.getCardTooltip(card);
      if (tooltipDesc != null) {
        this.addCustomTooltip(o.id, tooltipDesc);
      }

      return o;
    },

    getCardTooltip(card) {
      card.uid = card.id + "tooltip";

      let desc = "";
      if (card.type == RED) {
        desc = `<div class='play-effect'>
          <h4>${_("Play effect: BOMB")}</h4>
          <p>
            ${_(
              "Target the Zeppelin of the opposite column of your opponent. The opponent checks if the Zeppelin resists the bombing by looking at the robustness value on the other side of the card."
            )} <br />
            ${_(
              "If the robustness value is lower than or equal to the number of cards opposite column: the Zeppelin card is flipped face Destroyed up. If an Esteemed Guest was on this Zeppelin, put it back in the box."
            )} <br />
            ${_(
              "Otherwise, nothing happens. The player who was just attacked simply states that the Zeppelin resisted the attack, and the Zeppelin card remains with the Undamaged face up."
            )}
          </p>
        </div>
        <div class='discard-effect'>
          <h4>${_("Discard effect: CRACK THE SAFE")}</h4>
          <p>
            ${_(
              "Place the card from the top of your opponent’s Treasure in their discard pile."
            )}
          </p>
        </div>`;
      }
      if (card.type == BLUE) {
        desc = `<div class='play-effect'>
          <h4>${_("Play effect: BOARD")}</h4>
          <p>
            ${_(
              "Discard the last card of the opposite column of your opponent, without applying its discard effect."
            )}
          </p>
        </div>
        <div class='discard-effect'>
          <h4>${_("Discard effect: MANOEUVRE")}</h4>
          <p>
            ${_(
              "Move the last card of one of your columns to an adjacent column but do not trigger its play effect."
            )}
          </p>
        </div>`;
      }
      if (card.type == PURPLE) {
        desc = `<div class='play-effect'>
          <h4>${_("Play effect: FISH")}</h4>
          <p>
            ${_(
              "Take the first card in your discard pile and add it to your hand."
            )}
          </p>
        </div>
        <div class='discard-effect'>
          <h4>${_("Discard effect: CALL BACK")}</h4>
          <p>
            ${_(
              "Take the last card in one of your columns and add it to your hand"
            )}
          </p>
        </div>`;
      }
      if (card.type == GREEN) {
        desc = `<div class='play-effect'>
          <h4>${_("Play effect: REINFORCE")}</h4>
          <p>
            ${_(
              "Draw the first card of the Crew deck and add it to your hand."
            )}
          </p>
        </div>
        <div class='discard-effect'>
          <h4>${_("Discard effect: OBSERVE")}</h4>
          <p>
            ${_(
              "Look at the 2 first cards of your Crew deck and choose, for each card, if you leave it on the top or the bottom of your deck in the order of your choice."
            )}
          </p>
        </div>`;
      }
      if (card.type == BROWN) {
        desc = `<div class='play-effect'>
          <h4>${_("Play effect: SECURE")}</h4>
          <p>
            ${_(
              "Place face down the last card of one of your adjacent columns in your Treasure."
            )}
          </p>
        </div>
        <div class='discard-effect'>
          <h4>${_("Discard effect: LOOT")}</h4>
          <p>
            ${_(
              "Place face down the top card of your opponent’s discard pile in your Treasure."
            )}
          </p>
        </div>`;
      }
      if (card.type == YELLOW) {
        desc = `<div class='play-effect'><h4>${_("No play effect")}</h4></div>
        <div class='discard-effect'>
        <h4>Cannot be discarded</h4>`;
      }
      if (card.type == GUEST) {
        let guestDescs = {
          1: _("you have 9 Gold in the column"),
          2: _("you have played 2 cards in this column in the same turn"),
          3: _("you have played or moved the 3rd green card in this column"),
          4: _("you have played or moved the 5th card in this column"),
          5: _(
            "you have played or moved the 4th card of a different type in this column"
          ),
          6: _("you have played or moved the 3rd purple card in this column"),
          7: _("you have played or moved the 2nd yellow card in this column"),
          8: _("you have played or moved the 3rd blue card in this column"),
        };
        desc = `<div>
          <h4>${_("Esteemed Guest")}</h4>
          <p>
            ${_(
              "When you meet the requirements indicated on their card, secure them:place them immediately in your Treasure."
            )} <br/>
            <b>${_(
              "This card cannot be discarded from your Treasure, nor the cards underneath it."
            )}</b>
          </p>
          <h4>${_("Secure this Esteemed Guest as soon as :")}</h4>
          <p>
            ${guestDescs[card.id]}
          </p>
        </div>`;
      }

      return `<div class='card-tooltip'>
        ${this.tplCard(card)}
        <div class='card-desc'>
          <h4 class='card-id'>Id: ${card.id}</h4>
          ${desc}
        </div>
      </div>  
      `;
    },

    tplCard(card) {
      let uid = card.uid || card.id;
      let horizontal = [BALLOON].includes(card.type);

      return `<div id="card-${uid}" class="goldncrash-card ${
        card.id < 0 ? "fake" : ""
      } ${horizontal ? "horizontal" : ""}">
        <div class='card-inner' data-id="${card.id}" 
            data-type="${card.type}" data-deck="${card.deck}" data-value="${
        card.value
      }"></div>
      </div>`;
    },

    getCardContainer(card) {
      let t = card.location.split("_");
      if (card.location == "hand") {
        return $(`hand-${this.getPos(card.playerId)}`);
      }
      if (t[0] == "guest") {
        return $(`guest-${this.getCPos(t[1])}-${card.state}`);
      }
      if (t[0] == "balloon") {
        return $(`zeppelin-${this.getCPos(t[1])}-${card.state}`);
      }
      if (t[0] == "discard") {
        return $(`discard-${this.getCPos(t[1])}`);
      }
      if (t[0] == "treasure") {
        return $(`chest-${this.getCPos(t[1])}`);
      }
      if (t[0] == "column") {
        return $(`column-${this.getCPos(t[2])}-${t[1]}`);
      }

      console.error("Trying to get container of a card", card);
      return "game_play_area";
    },

    getCardData(card) {
      let cardId = card.id;
      if (card.flipped && card.id == 0) {
        let t = card.location.split("_");
        let deck = t[1].toUpperCase();
        return {
          id: 0,
          uid: `balloon-${deck}-${card.state}`,
          type: BALLOON,
          deck,
          value: "back",
        };
      }

      const CARD_DATAS = {
        1: [1, GUEST, GUEST, 5],
        2: [2, GUEST, GUEST, 4],
        3: [3, GUEST, GUEST, 5],
        4: [4, GUEST, GUEST, 6],
        5: [5, GUEST, GUEST, 5],
        6: [6, GUEST, GUEST, 5],
        7: [7, GUEST, GUEST, 5],
        8: [8, GUEST, GUEST, 5],
        9: [9, POULPIRATE, RED, 1],
        10: [10, POULPIRATE, RED, 1],
        11: [11, POULPIRATE, RED, 1],
        12: [12, POULPIRATE, RED, 2],
        13: [13, POULPIRATE, RED, 2],
        14: [14, POULPIRATE, RED, 3],
        15: [15, POULPIRATE, GREEN, 1],
        16: [16, POULPIRATE, GREEN, 1],
        17: [17, POULPIRATE, GREEN, 1],
        18: [18, POULPIRATE, GREEN, 2],
        19: [19, POULPIRATE, GREEN, 2],
        20: [20, POULPIRATE, GREEN, 3],
        21: [21, POULPIRATE, PURPLE, 1],
        22: [22, POULPIRATE, PURPLE, 1],
        23: [23, POULPIRATE, PURPLE, 1],
        24: [24, POULPIRATE, PURPLE, 2],
        25: [25, POULPIRATE, PURPLE, 2],
        26: [26, POULPIRATE, PURPLE, 3],
        27: [27, POULPIRATE, BROWN, 1],
        28: [28, POULPIRATE, BROWN, 1],
        29: [29, POULPIRATE, BROWN, 1],
        30: [30, POULPIRATE, BROWN, 2],
        31: [31, POULPIRATE, BROWN, 2],
        32: [32, POULPIRATE, BROWN, 3],
        33: [33, POULPIRATE, BLUE, 1],
        34: [34, POULPIRATE, BLUE, 1],
        35: [35, POULPIRATE, BLUE, 1],
        36: [36, POULPIRATE, BLUE, 2],
        37: [37, POULPIRATE, BLUE, 2],
        38: [38, POULPIRATE, BLUE, 3],
        39: [39, POULPIRATE, YELLOW, 4],
        40: [40, POULPIRATE, YELLOW, 4],
        41: [41, POULPIRATE, YELLOW, 4],
        42: [42, POULPIRATE, YELLOW, 4],
        43: [43, POULPIRATE, YELLOW, 4],
        44: [44, POULPIRATE, YELLOW, 6],
        45: [45, CHAMOURAI, RED, 1],
        46: [46, CHAMOURAI, RED, 1],
        47: [47, CHAMOURAI, RED, 1],
        48: [48, CHAMOURAI, RED, 2],
        49: [49, CHAMOURAI, RED, 2],
        50: [50, CHAMOURAI, RED, 3],
        51: [51, CHAMOURAI, GREEN, 1],
        52: [52, CHAMOURAI, GREEN, 1],
        53: [53, CHAMOURAI, GREEN, 1],
        54: [54, CHAMOURAI, GREEN, 2],
        55: [55, CHAMOURAI, GREEN, 2],
        56: [56, CHAMOURAI, GREEN, 3],
        57: [57, CHAMOURAI, PURPLE, 1],
        58: [58, CHAMOURAI, PURPLE, 1],
        59: [59, CHAMOURAI, PURPLE, 1],
        60: [60, CHAMOURAI, PURPLE, 2],
        61: [61, CHAMOURAI, PURPLE, 2],
        62: [62, CHAMOURAI, PURPLE, 3],
        63: [63, CHAMOURAI, BROWN, 1],
        64: [64, CHAMOURAI, BROWN, 1],
        65: [65, CHAMOURAI, BROWN, 1],
        66: [66, CHAMOURAI, BROWN, 2],
        67: [67, CHAMOURAI, BROWN, 2],
        68: [68, CHAMOURAI, BROWN, 3],
        69: [69, CHAMOURAI, BLUE, 1],
        70: [70, CHAMOURAI, BLUE, 1],
        71: [71, CHAMOURAI, BLUE, 1],
        72: [72, CHAMOURAI, BLUE, 2],
        73: [73, CHAMOURAI, BLUE, 2],
        74: [74, CHAMOURAI, BLUE, 3],
        75: [75, CHAMOURAI, YELLOW, 4],
        76: [76, CHAMOURAI, YELLOW, 4],
        77: [77, CHAMOURAI, YELLOW, 4],
        78: [78, CHAMOURAI, YELLOW, 4],
        79: [79, CHAMOURAI, YELLOW, 4],
        80: [80, CHAMOURAI, YELLOW, 6],
        81: [81, POULPIRATE, BALLOON, 1],
        82: [82, POULPIRATE, BALLOON, 2],
        83: [83, POULPIRATE, BALLOON, 3],
        84: [84, CHAMOURAI, BALLOON, 1],
        85: [85, CHAMOURAI, BALLOON, 2],
        86: [86, CHAMOURAI, BALLOON, 3],
      };
      if (CARD_DATAS[cardId] == undefined) {
        console.error("Unknown card:", cardId, card);
      }
      return {
        id: cardId,
        deck: CARD_DATAS[cardId][1],
        type: CARD_DATAS[cardId][2],
        value: CARD_DATAS[cardId][3],
      };
    },

    notif_playCard(n) {
      debug("Notif: play a card", n);

      let card = n.args.card;
      if (!$(`card-${card.id}`)) {
        this.addCard(card, this.getVisibleTitleContainer());
      }

      let pos = this.getPos(n.args.player_id);
      let counter = "handCount";
      this._counters[n.args.player_id][counter].incValue(-1);
      this.slide(`card-${card.id}`, $(`column-${pos}-${n.args.columnId}`));
    },

    notif_move(n) {
      debug("Notif: move a card", n);
      let pos = this.getPos(n.args.player_id);
      this.slide(
        `card-${n.args.card.id}`,
        $(`column-${pos}-${n.args.columnId2}`)
      );
    },

    notif_discard(n) {
      debug("Notif: discard cards", n);

      let pos = this.getPos(n.args.player_id);
      Promise.all(
        n.args.cards.map((card, i) =>
          this.wait(100 * i).then(() =>
            this.slide(`card-${card.id}`, $(`discard-${pos}`))
          )
        )
      ).then(() => this.notifqueue.setSynchronousDuration(100));
    },

    notif_clearColumn(n) {
      debug("Notif: clear column cards", n);

      let pos = this.getPos(n.args.player_id);
      let cards = [
        ...$(`column-${pos}-${n.args.columnId}`).querySelectorAll(
          ".goldncrash-card"
        ),
      ];
      Promise.all(
        cards.map((card, i) =>
          this.wait(100 * i).then(() => this.slide(card, $(`discard-${pos}`)))
        )
      ).then(() => this.notifqueue.setSynchronousDuration(100));
    },

    notif_secure(n) {
      debug("Notif: secure a card", n);

      let card = n.args.card;
      let pos = this.getPos(n.args.player_id);

      let oCard = $(`card-${card.id}`);
      let oCard2 = oCard.cloneNode(true);
      let inner = oCard2.querySelector(".card-inner");
      inner.dataset.type = "BACK";
      inner.dataset.value = "";
      oCard.id += "old";

      this.flipAndReplace(oCard, oCard2).then(() => {
        this.slide(`card-${card.id}`, $(`chest-${pos}`));
        $(`card-${card.id}_animated`).style.marginTop = "0px";
        $(`card-${card.id}_animated`).style.transform = `rotate(-90deg)`;
      });
    },

    notif_crackSafe(n) {
      debug("Notif: Crack safe", n);

      let card = n.args.card;
      let pos = this.getPos(n.args.player_id);
      let oCard = $(`card-${card.id}`);

      let lastTreasure = n.args.lastTreasure;
      if (lastTreasure && !$(`card-${lastTreasure.id}`)) {
        lastTreasure.type = "BACK";
        this.addCard(lastTreasure);
        oCard.insertAdjacentElement(
          "beforebegin",
          $(`card-${lastTreasure.id}`)
        );
      }

      oCard.id += "old";
      this.addCard(card, this.getVisibleTitleContainer());
      let oCard2 = $(`card-${card.id}`);

      this.flipAndReplace(oCard, oCard2).then(() => {
        this.slide(`card-${card.id}`, $(`discard-${pos}`), { rotate: true });
        $(`card-${card.id}_animated`).style.transform = `rotate(0deg)`;
      });
    },

    notif_drawCards(n) {
      debug("Notif: drawing cards", n);

      let counter = "handCount";
      let nCards = n.args.n;
      if (n.args.fromDeck)
        this._counters[n.args.player_id]["deckCount"].incValue(-nCards);
      if (this.isFastMode()) {
        this._counters[this.player_id][counter].incValue(nCards);
        return;
      }

      let deck =
        this.gamedatas.players[n.args.player_id].character.toUpperCase();
      Promise.all(
        Array.from(Array(nCards), (x, i) => i).map((i) => {
          return this.wait(100 * i).then(() => {
            let source = n.args.fromDeck
              ? $(`deck-${this.getPos(n.args.player_id)}`)
              : $(`discard-${this.getPos(n.args.player_id)}`);
            let o = this.addCard({ uid: -1, deck, type: "BACK" }, source);
            return this.slide(o, `player_board_${n.args.player_id}`, {
              duration: 1000,
              destroy: true,
              phantom: false,
            });
          });
        })
      ).then(() => {
        this._counters[n.args.player_id][counter].incValue(nCards);
        this.notifqueue.setSynchronousDuration(100);
      });
    },

    notif_pDrawCards(n) {
      debug("Notif: private drawing cards", n);

      if (n.args.fromDeck)
        this._counters[this.player_id]["deckCount"].incValue(
          -n.args.cards.length
        );

      let counter = "handCount";
      if (this.isFastMode()) {
        n.args.cards.forEach((card) => {
          this.addCard(card);
        });
        this._counters[this.player_id][counter].incValue(n.args.cards.length);
        // if (n.args.pilfering) this._counters[n.args.pilfering][counter].incValue(-n.args.cards.length);
        return;
      }

      Promise.all(
        n.args.cards.map((card, i) => {
          return this.wait(100 * i).then(() => {
            this.addCard(card);
            let container = this.getCardContainer(card);
            // let source = n.args.pilfering ? $(`counter-${n.args.pilfering}-${counter}`) :  $(`deck-${this.getPos(this.player_id)}`);
            let source = n.args.fromDeck
              ? $(`deck-${this.getPos(this.player_id)}`)
              : $(`discard-${this.getPos(this.player_id)}`);

            return this.slide(`card-${card.id}`, container, {
              from: source,
              duration: 1000,
            });
          });
        })
      ).then(() => {
        this._counters[this.player_id][counter].incValue(n.args.cards.length);
        // if (n.args.pilfering) this._counters[n.args.pilfering][counter].incValue(-n.args.cards.length);

        this.notifqueue.setSynchronousDuration(100);
      });
    },

    notif_bombPass(n) {
      debug("Notif: bomb success", n);

      let elem = `<div id='bomb-animation'>
      ${n.args.force}
      <div class="icon-container icon-container-bomb">
        <div class="goldncrash-icon icon-bomb"></div>
      </div>
    </div>`;
      $("page-content").insertAdjacentHTML("beforeend", elem);

      let target = $(
        `card-balloon-${n.args.card.deck.toUpperCase()}-${n.args.columnId}`
      );
      this.slide("bomb-animation", target, {
        from: $(`column-${this.getPos(n.args.player_id)}-${n.args.columnId}`),
        destroy: true,
        phantom: false,
        duration: 1200,
      }).then(() => {
        this.addCard(n.args.card);
        this.flipAndReplace(target, `card-${n.args.card.id}`);

        let guest = $(
          `guest-${this.getCPos(n.args.card.deck)}-${n.args.columnId}`
        ).querySelector(".goldncrash-card");
        if (guest) this.fadeOutAndDestroy(guest);
      });
    },

    notif_bombFail(n) {
      debug("Notif: bomb fail", n);
      if (this.isFastMode()) return;

      let elem = `<div id='bomb-animation'>
      ${n.args.force}
      <div class="icon-container icon-container-bomb">
        <div class="goldncrash-icon icon-bomb"></div>
      </div>
    </div>`;
      $("page-content").insertAdjacentHTML("beforeend", elem);

      let target = $(
        `card-balloon-${n.args.balloonDeck.toUpperCase()}-${n.args.columnId}`
      );
      this.slide("bomb-animation", target, {
        from: $(`column-${this.getPos(n.args.player_id)}-${n.args.columnId}`),
        destroy: true,
        phantom: false,
        duration: 1200,
      }).then(() => {
        target.classList.add("shake-it");
        this.wait(900).then(() => target.classList.remove("shake-it"));
      });
    },

    notif_pBombFail(n) {
      debug("Notif: bomb fail", n);
      if (this.isFastMode()) return;

      let elem = `<div id='bomb-animation'>
      ${n.args.force}
      <div class="icon-container icon-container-bomb">
        <div class="goldncrash-icon icon-bomb"></div>
      </div>
    </div>`;
      $("page-content").insertAdjacentHTML("beforeend", elem);

      let target = $(
        `card-balloon-${n.args.balloonDeck.toUpperCase()}-${n.args.columnId}`
      );
      this.addCard(n.args.card, target);
      this.wait(300).then(() =>
        $(`card-${n.args.card.id}`).classList.add("fade-in")
      );

      this.slide("bomb-animation", target, {
        from: $(`column-${this.getPos(n.args.player_id)}-${n.args.columnId}`),
        destroy: true,
        phantom: false,
        duration: 1200,
      }).then(() => {
        target.classList.add("shake-it");
        this.wait(900).then(() => {
          target.classList.remove("shake-it");
          $(`card-${n.args.card.id}`).classList.remove("fade-in");
          this.wait(800).then(() => $(`card-${n.args.card.id}`).remove());
        });
      });
    },

    ////////////////////////////////////////////////////////////
    // _____                          _   _   _
    // |  ___|__  _ __ _ __ ___   __ _| |_| |_(_)_ __   __ _
    // | |_ / _ \| '__| '_ ` _ \ / _` | __| __| | '_ \ / _` |
    // |  _| (_) | |  | | | | | | (_| | |_| |_| | | | | (_| |
    // |_|  \___/|_|  |_| |_| |_|\__,_|\__|\__|_|_| |_|\__, |
    //                                                 |___/
    ////////////////////////////////////////////////////////////

    /**
     * Replace some expressions by corresponding html formating
     */
    formatIcon(name, n = null, lowerCase = true) {
      let type = lowerCase ? name.toLowerCase() : name;
      const NO_TEXT_ICONS = [];
      let noText = NO_TEXT_ICONS.includes(name);
      let text = n == null ? "" : `<span>${n}</span>`;
      return `${
        noText ? text : ""
      }<div class="icon-container icon-container-${type}">
             <div class="goldncrash-icon icon-${type}">${
        noText ? "" : text
      }</div>
           </div>`;
    },

    formatString(str) {
      const ICONS = [];

      ICONS.forEach((name) => {
        const regex = new RegExp("<" + name + ":([^>]+)>", "g");
        str = str.replaceAll(regex, this.formatIcon(name, "<span>$1</span>"));
        str = str.replaceAll(
          new RegExp("<" + name + ">", "g"),
          this.formatIcon(name)
        );
      });
      str = str.replace(/\*\*([^\*]+)\*\*/g, "<b>$1</b>");

      return str;
    },

    /**
     * Format log strings
     *  @Override
     */
    format_string_recursive(log, args) {
      try {
        if (log && args && !args.processed) {
          args.processed = true;

          log = this.formatString(_(log));
        }
      } catch (e) {
        console.error(log, args, "Exception thrown", e.stack);
      }

      return this.inherited(arguments);
    },

    //////////////////////////////////////////////////////
    //  ___        __         ____                  _
    // |_ _|_ __  / _| ___   |  _ \ __ _ _ __   ___| |
    //  | || '_ \| |_ / _ \  | |_) / _` | '_ \ / _ \ |
    //  | || | | |  _| (_) | |  __/ (_| | | | |  __/ |
    // |___|_| |_|_|  \___/  |_|   \__,_|_| |_|\___|_|
    //////////////////////////////////////////////////////

    setupInfoPanel() {
      dojo.place(this.tplInfoPanel(), "player_boards", "first");
      let chk = $("help-mode-chk");
      dojo.connect(chk, "onchange", () => this.toggleHelpMode(chk.checked));
      this.addTooltip("help-mode-switch", "", _("Toggle help/safe mode."));

      this._settingsModal = new customgame.modal("showSettings", {
        class: "goldncrash_popin",
        closeIcon: "fa-times",
        title: _("Settings"),
        closeAction: "hide",
        verticalAlign: "flex-start",
        contentsTpl: `<div id='goldncrash-settings'>
              <div id='goldncrash-settings-header'></div>
              <div id="settings-controls-container"></div>
            </div>`,
      });
    },

    tplInfoPanel() {
      return `
    <div class='player-board' id="player_board_config">
      <div id="player_config" class="player_board_content">
        <div class="player_config_row">
          <div id="show-scores">
             <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
               <g class="fa-group">
                 <path class="fa-secondary" fill="currentColor" d="M0 192v272a48 48 0 0 0 48 48h352a48 48 0 0 0 48-48V192zm324.13 141.91a11.92 11.92 0 0 1-3.53 6.89L281 379.4l9.4 54.6a12 12 0 0 1-17.4 12.6l-49-25.8-48.9 25.8a12 12 0 0 1-17.4-12.6l9.4-54.6-39.6-38.6a12 12 0 0 1 6.6-20.5l54.7-8 24.5-49.6a12 12 0 0 1 21.5 0l24.5 49.6 54.7 8a12 12 0 0 1 10.13 13.61zM304 128h32a16 16 0 0 0 16-16V16a16 16 0 0 0-16-16h-32a16 16 0 0 0-16 16v96a16 16 0 0 0 16 16zm-192 0h32a16 16 0 0 0 16-16V16a16 16 0 0 0-16-16h-32a16 16 0 0 0-16 16v96a16 16 0 0 0 16 16z" opacity="0.4"></path>
                 <path class="fa-primary" fill="currentColor" d="M314 320.3l-54.7-8-24.5-49.6a12 12 0 0 0-21.5 0l-24.5 49.6-54.7 8a12 12 0 0 0-6.6 20.5l39.6 38.6-9.4 54.6a12 12 0 0 0 17.4 12.6l48.9-25.8 49 25.8a12 12 0 0 0 17.4-12.6l-9.4-54.6 39.6-38.6a12 12 0 0 0-6.6-20.5zM400 64h-48v48a16 16 0 0 1-16 16h-32a16 16 0 0 1-16-16V64H160v48a16 16 0 0 1-16 16h-32a16 16 0 0 1-16-16V64H48a48 48 0 0 0-48 48v80h448v-80a48 48 0 0 0-48-48z"></path>
               </g>
             </svg>
          </div>
 
          <div id="help-mode-switch">
            <input type="checkbox" class="checkbox" id="help-mode-chk" />
            <label class="label" for="help-mode-chk">
              <div class="ball"></div>
            </label><svg aria-hidden="true" focusable="false" data-prefix="fad" data-icon="question-circle" class="svg-inline--fa fa-question-circle fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><g class="fa-group"><path class="fa-secondary" fill="currentColor" d="M256 8C119 8 8 119.08 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 422a46 46 0 1 1 46-46 46.05 46.05 0 0 1-46 46zm40-131.33V300a12 12 0 0 1-12 12h-56a12 12 0 0 1-12-12v-4c0-41.06 31.13-57.47 54.65-70.66 20.17-11.31 32.54-19 32.54-34 0-19.82-25.27-33-45.7-33-27.19 0-39.44 13.14-57.3 35.79a12 12 0 0 1-16.67 2.13L148.82 170a12 12 0 0 1-2.71-16.26C173.4 113 208.16 90 262.66 90c56.34 0 116.53 44 116.53 102 0 77-83.19 78.21-83.19 106.67z" opacity="0.4"></path><path class="fa-primary" fill="currentColor" d="M256 338a46 46 0 1 0 46 46 46 46 0 0 0-46-46zm6.66-248c-54.5 0-89.26 23-116.55 63.76a12 12 0 0 0 2.71 16.24l34.7 26.31a12 12 0 0 0 16.67-2.13c17.86-22.65 30.11-35.79 57.3-35.79 20.43 0 45.7 13.14 45.7 33 0 15-12.37 22.66-32.54 34C247.13 238.53 216 254.94 216 296v4a12 12 0 0 0 12 12h56a12 12 0 0 0 12-12v-1.33c0-28.46 83.19-29.67 83.19-106.67 0-58-60.19-102-116.53-102z"></path></g></svg>
          </div>
 
          <div id="show-settings">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
              <g>
                <path class="fa-secondary" fill="currentColor" d="M638.41 387a12.34 12.34 0 0 0-12.2-10.3h-16.5a86.33 86.33 0 0 0-15.9-27.4L602 335a12.42 12.42 0 0 0-2.8-15.7 110.5 110.5 0 0 0-32.1-18.6 12.36 12.36 0 0 0-15.1 5.4l-8.2 14.3a88.86 88.86 0 0 0-31.7 0l-8.2-14.3a12.36 12.36 0 0 0-15.1-5.4 111.83 111.83 0 0 0-32.1 18.6 12.3 12.3 0 0 0-2.8 15.7l8.2 14.3a86.33 86.33 0 0 0-15.9 27.4h-16.5a12.43 12.43 0 0 0-12.2 10.4 112.66 112.66 0 0 0 0 37.1 12.34 12.34 0 0 0 12.2 10.3h16.5a86.33 86.33 0 0 0 15.9 27.4l-8.2 14.3a12.42 12.42 0 0 0 2.8 15.7 110.5 110.5 0 0 0 32.1 18.6 12.36 12.36 0 0 0 15.1-5.4l8.2-14.3a88.86 88.86 0 0 0 31.7 0l8.2 14.3a12.36 12.36 0 0 0 15.1 5.4 111.83 111.83 0 0 0 32.1-18.6 12.3 12.3 0 0 0 2.8-15.7l-8.2-14.3a86.33 86.33 0 0 0 15.9-27.4h16.5a12.43 12.43 0 0 0 12.2-10.4 112.66 112.66 0 0 0 .01-37.1zm-136.8 44.9c-29.6-38.5 14.3-82.4 52.8-52.8 29.59 38.49-14.3 82.39-52.8 52.79zm136.8-343.8a12.34 12.34 0 0 0-12.2-10.3h-16.5a86.33 86.33 0 0 0-15.9-27.4l8.2-14.3a12.42 12.42 0 0 0-2.8-15.7 110.5 110.5 0 0 0-32.1-18.6A12.36 12.36 0 0 0 552 7.19l-8.2 14.3a88.86 88.86 0 0 0-31.7 0l-8.2-14.3a12.36 12.36 0 0 0-15.1-5.4 111.83 111.83 0 0 0-32.1 18.6 12.3 12.3 0 0 0-2.8 15.7l8.2 14.3a86.33 86.33 0 0 0-15.9 27.4h-16.5a12.43 12.43 0 0 0-12.2 10.4 112.66 112.66 0 0 0 0 37.1 12.34 12.34 0 0 0 12.2 10.3h16.5a86.33 86.33 0 0 0 15.9 27.4l-8.2 14.3a12.42 12.42 0 0 0 2.8 15.7 110.5 110.5 0 0 0 32.1 18.6 12.36 12.36 0 0 0 15.1-5.4l8.2-14.3a88.86 88.86 0 0 0 31.7 0l8.2 14.3a12.36 12.36 0 0 0 15.1 5.4 111.83 111.83 0 0 0 32.1-18.6 12.3 12.3 0 0 0 2.8-15.7l-8.2-14.3a86.33 86.33 0 0 0 15.9-27.4h16.5a12.43 12.43 0 0 0 12.2-10.4 112.66 112.66 0 0 0 .01-37.1zm-136.8 45c-29.6-38.5 14.3-82.5 52.8-52.8 29.59 38.49-14.3 82.39-52.8 52.79z" opacity="0.4"></path>
                <path class="fa-primary" fill="currentColor" d="M420 303.79L386.31 287a173.78 173.78 0 0 0 0-63.5l33.7-16.8c10.1-5.9 14-18.2 10-29.1-8.9-24.2-25.9-46.4-42.1-65.8a23.93 23.93 0 0 0-30.3-5.3l-29.1 16.8a173.66 173.66 0 0 0-54.9-31.7V58a24 24 0 0 0-20-23.6 228.06 228.06 0 0 0-76 .1A23.82 23.82 0 0 0 158 58v33.7a171.78 171.78 0 0 0-54.9 31.7L74 106.59a23.91 23.91 0 0 0-30.3 5.3c-16.2 19.4-33.3 41.6-42.2 65.8a23.84 23.84 0 0 0 10.5 29l33.3 16.9a173.24 173.24 0 0 0 0 63.4L12 303.79a24.13 24.13 0 0 0-10.5 29.1c8.9 24.1 26 46.3 42.2 65.7a23.93 23.93 0 0 0 30.3 5.3l29.1-16.7a173.66 173.66 0 0 0 54.9 31.7v33.6a24 24 0 0 0 20 23.6 224.88 224.88 0 0 0 75.9 0 23.93 23.93 0 0 0 19.7-23.6v-33.6a171.78 171.78 0 0 0 54.9-31.7l29.1 16.8a23.91 23.91 0 0 0 30.3-5.3c16.2-19.4 33.7-41.6 42.6-65.8a24 24 0 0 0-10.5-29.1zm-151.3 4.3c-77 59.2-164.9-28.7-105.7-105.7 77-59.2 164.91 28.7 105.71 105.7z"></path>
              </g>
            </svg>
          </div>
        </div>
      </div>
    </div>
    `;
    },

    updatePlayerOrdering() {
      this.inherited(arguments);
      dojo.place("player_board_config", "player_boards", "first");
    },

    updateLayout() {
      if (!this.settings) return;
      return; // TODO
      const ROOT = document.documentElement;

      const WIDTH =
        $("goldncrash-main-container").getBoundingClientRect()["width"] - 5;
      const BOARD_WIDTH = 1510;
      const BOARD_SIZE = (WIDTH * this.settings.boardSizes) / 100;
      let boardScale = BOARD_SIZE / BOARD_WIDTH;
      ROOT.style.setProperty("--goldncrashBoardScale", boardScale);
    },
  });
});
