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

/**This help to console log differently on studio or on production */
var isDebug =
  window.location.host == "studio.boardgamearena.com" ||
  window.location.hash.indexOf("debug") > -1;
var debug = isDebug ? console.info.bind(window.console) : function () {};

define([
  "dojo",
  "dojo/_base/declare",
  "dojo/fx/easing",
  "ebg/core/gamegui",
  "ebg/counter",
  "ebg/stock",
  g_gamethemeurl + "modules/js/Core/game.js",
  g_gamethemeurl + "modules/js/Core/modal.js",
  g_gamethemeurl + "modules/js/card.js",
  g_gamethemeurl + "modules/js/Utils/cheatModule.js",
], function (
  dojo,
  declare,
  easing,
  gamegui,
  counter,
  stock,
  game,
  modal,
  card
) {
  return declare(
    "bgagame.goldncrash",
    [customgame.game, goldncrash.cheatModule],
    {
      constructor() {
        debug("goldncrash constructor");

        this._activeStates = ["giveCard"];
        this._notifications = [
          // ['completeHand', 1000],
          // ['completeOtherHand', 1000, (notif) => notif.args.player_id == this.player_id],
        ];

        // Fix mobile viewport (remove CSS zoom)
        this.default_viewport = "width=800";

        // this._settingsSections = [];
        this._settingsConfig = {};
      },

      /*
  █████████  ██████████ ███████████ █████  █████ ███████████ 
 ███░░░░░███░░███░░░░░█░█░░░███░░░█░░███  ░░███ ░░███░░░░░███
░███    ░░░  ░███  █ ░ ░   ░███  ░  ░███   ░███  ░███    ░███
░░█████████  ░██████       ░███     ░███   ░███  ░██████████ 
 ░░░░░░░░███ ░███░░█       ░███     ░███   ░███  ░███░░░░░░  
 ███    ░███ ░███ ░   █    ░███     ░███   ░███  ░███        
░░█████████  ██████████    █████    ░░████████   █████       
 ░░░░░░░░░  ░░░░░░░░░░    ░░░░░      ░░░░░░░░   ░░░░░        
                                                             
                                                             
                                                             
        */

      setup(gamedatas) {
        debug("setup", gamedatas);

        //create decks as bga stock + deckInfos
        this.counters = [];
        // this.counters['deck'] = this.addCounterOnDeck('deck', gamedatas.cards.deck_count);

        // Setting up player boards
        for (const playerId in gamedatas.players) {
          const player = gamedatas.players[playerId];

          // add a player panel
        }

        //   this.updatePlayerOrdering();

        //add general tooltips

        // add shortcut and navigation

        //add cheat block if cheatModule is active
        if (gamedatas.cheatModule) {
          this.cheatModuleSetup();
        }

        this.inherited(arguments);
        debug("Ending game setup");
      },

      /**
  █████████  ███████████   █████████   ███████████ ██████████  █████████ 
 ███░░░░░███░█░░░███░░░█  ███░░░░░███ ░█░░░███░░░█░░███░░░░░█ ███░░░░░███
░███    ░░░ ░   ░███  ░  ░███    ░███ ░   ░███  ░  ░███  █ ░ ░███    ░░░ 
░░█████████     ░███     ░███████████     ░███     ░██████   ░░█████████ 
 ░░░░░░░░███    ░███     ░███░░░░░███     ░███     ░███░░█    ░░░░░░░░███
 ███    ░███    ░███     ░███    ░███     ░███     ░███ ░   █ ███    ░███
░░█████████     █████    █████   █████    █████    ██████████░░█████████ 
 ░░░░░░░░░     ░░░░░    ░░░░░   ░░░░░    ░░░░░    ░░░░░░░░░░  ░░░░░░░░░  
                                                              
 */

      onEnteringStateCall(args) {
        this.moveCaller(args.caller.id);
        if (this.player_id != this.getActivePlayerId()) return;

        args.callablePlayers.forEach((player) => {
          this.addPrimaryActionButton("btn_" + player.id, player.name, () =>
            this.openCardsChoices(player)
          );
          // $('btn_'+player.id).style.color = "#" + player.color; illisible
        });
        Object.entries(args.uncallableCards).forEach(([id, card]) => {
          dojo
            .query(
              '#card_choice > [data-card-color="' +
                card.color +
                '"][data-card-value=' +
                card.value +
                "]"
            )
            .addClass("hidden");
        });
      },

      /*
     █████  █████ ███████████ █████ █████        █████████ 
    ░░███  ░░███ ░█░░░███░░░█░░███ ░░███        ███░░░░░███
     ░███   ░███ ░   ░███  ░  ░███  ░███       ░███    ░░░ 
     ░███   ░███     ░███     ░███  ░███       ░░█████████ 
     ░███   ░███     ░███     ░███  ░███        ░░░░░░░░███
     ░███   ░███     ░███     ░███  ░███      █ ███    ░███
     ░░████████      █████    █████ ███████████░░█████████ 
      ░░░░░░░░      ░░░░░    ░░░░░ ░░░░░░░░░░░  ░░░░░░░░░  
  */

      displayTickedCells(cells) {
        cells.forEach((cell) => {
          this.displayTickedCell(cell);
        });
      },

      // onLeavingState: this method is called each time we are leaving a game state.
      //                 You can use this method to perform some user interface changes at this moment.
      //

      // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
      //                        action status bar (ie: the HTML links in the status bar).
      //

      /*
   █████████             █████     ███                             
  ███░░░░░███           ░░███     ░░░                              
 ░███    ░███   ██████  ███████   ████   ██████  ████████    █████ 
 ░███████████  ███░░███░░░███░   ░░███  ███░░███░░███░░███  ███░░  
 ░███░░░░░███ ░███ ░░░   ░███     ░███ ░███ ░███ ░███ ░███ ░░█████ 
 ░███    ░███ ░███  ███  ░███ ███ ░███ ░███ ░███ ░███ ░███  ░░░░███
 █████   █████░░██████   ░░█████  █████░░██████  ████ █████ ██████ 
░░░░░   ░░░░░  ░░░░░░     ░░░░░  ░░░░░  ░░░░░░  ░░░░ ░░░░░ ░░░░░░  
                                                                   
                                                                   
                                                                   
*/

      // onTickCell(e){
      //   debug("onTickCell", e)
      //   const divId = e.currentTarget.id;
      //   if (!$(divId).classList.contains('clickable')) return false;
      //   this.takeAction('tick', {
      //     'cell' : divId
      //   });

      // },

      /*
 ██████   █████    ███████    ███████████ █████ ███████████  █████████ 
░░██████ ░░███   ███░░░░░███ ░█░░░███░░░█░░███ ░░███░░░░░░█ ███░░░░░███
 ░███░███ ░███  ███     ░░███░   ░███  ░  ░███  ░███   █ ░ ░███    ░░░ 
 ░███░░███░███ ░███      ░███    ░███     ░███  ░███████   ░░█████████ 
 ░███ ░░██████ ░███      ░███    ░███     ░███  ░███░░░█    ░░░░░░░░███
 ░███  ░░█████ ░░███     ███     ░███     ░███  ░███  ░     ███    ░███
 █████  ░░█████ ░░░███████░      █████    █████ █████      ░░█████████ 
░░░░░    ░░░░░    ░░░░░░░       ░░░░░    ░░░░░ ░░░░░        ░░░░░░░░░  
                                                                                                               
*/

      // notif_completeHand(n){
      //   debug('notif_completeHand', n);
      //   n.args.cardIds.forEach(cardId => {
      //     this.addCardToHand(cardId, this.player_id, true);
      //   });

      //   this.counters['deck'].toValue(n.args.deck);
      //   this.counters['discard'].toValue(n.args.discard);
      // },

      /*
 ██████   ██████    ███████    █████   █████ ██████████  █████████ 
░░██████ ██████   ███░░░░░███ ░░███   ░░███ ░░███░░░░░█ ███░░░░░███
 ░███░█████░███  ███     ░░███ ░███    ░███  ░███  █ ░ ░███    ░░░ 
 ░███░░███ ░███ ░███      ░███ ░███    ░███  ░██████   ░░█████████ 
 ░███ ░░░  ░███ ░███      ░███ ░░███   ███   ░███░░█    ░░░░░░░░███
 ░███      ░███ ░░███     ███   ░░░█████░    ░███ ░   █ ███    ░███
 █████     █████ ░░░███████░      ░░███      ██████████░░█████████ 
░░░░░     ░░░░░    ░░░░░░░         ░░░      ░░░░░░░░░░  ░░░░░░░░░  
*/

      moveCard(cardId, fromPlayerId, toPlayerId = null, fromHand = true) {
        const toDiv = toPlayerId
          ? this.getDestinationDiv(cardId, toPlayerId)
          : "discard";

        if (fromHand) this.cardsCounters[fromPlayerId].incValue(-1);

        //move from visible hand to visible table
        if (this.player_id != fromPlayerId && fromHand) {
          debug(
            "flipandreplace launched with ",
            dojo.query("#hand_" + fromPlayerId + " > .card")[0]
          );
          this.flipAndReplace(
            dojo.query("#hand_" + fromPlayerId + " > .card")[0],
            this.card_tpl(cardId),
            500
          ).then(() => {
            const elemId = "card_" + cardId;
            //the card will leave a hand, no need of margin right
            $(elemId).style.marginRight = 0;

            if (toDiv == "discard") this.moveToDiscard(elemId);
            else this.genericMove(elemId, toDiv);
          });
        } else {
          const elemId = "card_" + cardId;
          //the card will leave a hand, no need of margin right
          $(elemId).style.marginRight = 0;

          if (toDiv == "discard") this.moveToDiscard(elemId);
          else this.genericMove(elemId, toDiv);
        }
      },

      /*
 ███████████ ██████████ ██████   ██████ ███████████  █████         █████████   ███████████ ██████████  █████████ 
░█░░░███░░░█░░███░░░░░█░░██████ ██████ ░░███░░░░░███░░███         ███░░░░░███ ░█░░░███░░░█░░███░░░░░█ ███░░░░░███
░   ░███  ░  ░███  █ ░  ░███░█████░███  ░███    ░███ ░███        ░███    ░███ ░   ░███  ░  ░███  █ ░ ░███    ░░░ 
    ░███     ░██████    ░███░░███ ░███  ░██████████  ░███        ░███████████     ░███     ░██████   ░░█████████ 
    ░███     ░███░░█    ░███ ░░░  ░███  ░███░░░░░░   ░███        ░███░░░░░███     ░███     ░███░░█    ░░░░░░░░███
    ░███     ░███ ░   █ ░███      ░███  ░███         ░███      █ ░███    ░███     ░███     ░███ ░   █ ███    ░███
    █████    ██████████ █████     █████ █████        ███████████ █████   █████    █████    ██████████░░█████████ 
   ░░░░░    ░░░░░░░░░░ ░░░░░     ░░░░░ ░░░░░        ░░░░░░░░░░░ ░░░░░   ░░░░░    ░░░░░    ░░░░░░░░░░  ░░░░░░░░░  
                                                                                                                 
                                                                                                                 
                                                                                                                 
        */

      // semi generic
      tplPlayerPanel(player) {
        return `<div id='goldncrash-player-infos_${player.id}' class='player-infos'>
        <div class='icons' data-player_no="${player.no}" data-player_id="${player.id}"></div>
        <div class='cards-counter counter' id='card-counter-${player.id}'>0</div>
        <div class='nuggets-counter counter' id='nuggets-counter-${player.id}'>0</div>
      </div>`;
      },

      getTickHelpText(action) {
        switch (action) {
          case FLAG:
            return _("Claim a new region by ticking this flag");
          case FREE_CARD:
            return _("Ticking this leg allows you to play a free card");
          case SALOON:
            return _("Ticking this leg allows you to check a new saloon box");
          case WANTED:
            return _("Ticking this leg allows you to check a new wanted box");
          case TIPI:
            return _("Ticking this leg allows you to check a new tipi box");
          case DISCOVERY:
            return _("Ticking this leg allows you to claim a new region");
          case NUGGETS:
            return _("Ticking this leg allows you to play a nugget action");
          case CARDS:
            return _("Ticking this leg allows you to play a card action");
          case CHECKS:
            return _("Ticking this leg allows you to play a check action");
          case POINTS:
            return _("Ticking this leg gives you points as indicated");
          case NOTHING:
            return _("Ticking this leg has no direct effect");
          case REWARD:
            return _("Complete this region and you will get this reward");

          default:
            return _("ERROR");
        }
      },

      /*
   █████████  ██████████ ██████   █████ ██████████ ███████████   █████   █████████   █████████ 
  ███░░░░░███░░███░░░░░█░░██████ ░░███ ░░███░░░░░█░░███░░░░░███ ░░███   ███░░░░░███ ███░░░░░███
 ███     ░░░  ░███  █ ░  ░███░███ ░███  ░███  █ ░  ░███    ░███  ░███  ███     ░░░ ░███    ░░░ 
░███          ░██████    ░███░░███░███  ░██████    ░██████████   ░███ ░███         ░░█████████ 
░███    █████ ░███░░█    ░███ ░░██████  ░███░░█    ░███░░░░░███  ░███ ░███          ░░░░░░░░███
░░███  ░░███  ░███ ░   █ ░███  ░░█████  ░███ ░   █ ░███    ░███  ░███ ░░███     ███ ███    ░███
 ░░█████████  ██████████ █████  ░░█████ ██████████ █████   █████ █████ ░░█████████ ░░█████████ 
  ░░░░░░░░░  ░░░░░░░░░░ ░░░░░    ░░░░░ ░░░░░░░░░░ ░░░░░   ░░░░░ ░░░░░   ░░░░░░░░░   ░░░░░░░░░  
                                                                                               
                                                                                               
                                                                                               
*/
      //place each player board in good order.
      // myUpdatePlayerOrdering(elementName, container) {
      //     let index = 0;
      //     for (let i in this.gamedatas.playerorder) {
      //       const playerId = this.gamedatas.playerorder[i];
      //       dojo.place(elementName + '_' + playerId, container, index);
      //       index++;
      //     }
      //   },

      /*
       *   Create and place a counter in a div container
       */
      addCounterOnDeck(containerId, initialValue) {
        const counterId = containerId + "_deckinfo";
        const div = `<div id="${counterId}" class="deckinfo">0</div>`;
        dojo.place(div, containerId);
        const counter = this.createCounter(counterId, initialValue);
        if (initialValue) $(containerId).classList.remove("empty");
        return counter;
      },

      /**
       * This method can be used instead of addActionButton, to add a button which is an image (i.e. resource). Can be useful when player
       * need to make a choice of resources or tokens.
       */
      addImageActionButton(
        id,
        handler,
        tooltip,
        classes = null,
        bcolor = "blue"
      ) {
        if (classes) classes.push("shadow bgaimagebutton");
        else classes = ["shadow bgaimagebutton"];

        // this will actually make a transparent button id color = blue
        this.addActionButton(id, "", handler, "customActions", false, bcolor);
        // remove border, for images it better without
        dojo.style(id, "border", "none");
        // but add shadow style (box-shadow, see css)
        dojo.addClass(id, classes.join(" "));
        dojo.removeClass(id, "bgabutton_blue");
        // you can also add additional styles, such as background
        if (tooltip) {
          dojo.attr(id, "title", tooltip);
        }
        return $(id);
      },

      /*
       *
       * To add div in logs
       *
       */

      getTokenDiv(key, args) {
        // debug('getTokenDiv', key, args);
        // ... implement whatever html you want here, example from sharedcode.js
        var token_id = args[key];
        switch (key) {
          case "value":
          case "value2":
            var valueDiv = "<div class='value value-" + token_id + "'></div>";
            return valueDiv;

          case "color":
            var valueDiv = "<div class='color color-" + token_id + "'></div>";
            return valueDiv;

          default:
            return token_id;
        }
      },

      genericMove(elemId, newContainerId, fastMode = false, position = null) {
        const el = $(elemId);

        if (this.isFastMode() || (fastMode && this.isCurrentPlayerActive())) {
          if (position == "first") $(newContainerId).prepend(el);
          else $(newContainerId).appendChild(el);
          return;
        }

        const first = el.getBoundingClientRect();

        // Now set the element to the last position.
        if (position == "first") $(newContainerId).prepend(el);
        else $(newContainerId).appendChild(el);

        const last = el.getBoundingClientRect();

        const invertY = first.top - last.top;
        const invertX = first.left - last.left;

        el.style.transform = `translate(${invertX}px, ${invertY}px)`;

        setTimeout(function () {
          el.classList.add("animate-on-transforms");
          el.style.transform = "";
        }, 50);

        // setTimeout(function() {
        el.addEventListener("transitionend", () => {
          el.classList.remove("animate-on-transforms");
        });
        // }, 20);
      },
    }
  );
});
