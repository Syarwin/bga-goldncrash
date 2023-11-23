{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- GoldnCrash implementation : © Emmanuel Albisser et Timothée Pecatte <emmanuel.albisser@gmail.com et tim.pecatte@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    goldncrash_goldncrash.tpl
    
    This is the HTML template of your game.
    
    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.
    
    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format
    
    See your "view" PHP file to check how to set variables and control blocks
    
    Please REMOVE this comment before publishing your game on BGA
-->

<div id="card_choice">
    <div id="card_choice_hint_card">
        <div id="card_choice_hint_text"></div>
        <div id="card_choice_button"></div>
    </div>
    
</div>



<div id="table_{MY_ID}">
    <div class="board_wrapper">
        <div id="board_{MY_ID}" data-type="{TYPE}" class="board my_board">
            <div class="name" style="color: #{MY_GNCOR}">{MY_NAME}</div>
        </div>
    </div>
    <div id="play_area_{MY_ID}">
        <div>
            <div id="main_table">
                <div id="discard" class="deck empty card">
                </div>
                
                <div id="deck" class="deck card">
                </div>
            </div>
        </div>

        <div id="cards_{MY_ID}" class="whiteblock"></div>
        <div id="hand_{MY_ID}"></div>
    </div>
    
    <div id="main_board"></div>
</div>

<div id="links">

</div>

<div id="tables">
    <!-- BEGIN playerBlock --> 
    <div id="table_{PLAYER_ID}" class="other_table">
        <div id="board_{PLAYER_ID}" data-type="{TYPE}" class="board">
            <div class="name" style="color: #{PLAYER_GNCOR}">{PLAYER_NAME}</div>
        </div>
        <div id="play_area_{PLAYER_ID}">
            <div id="cards_{PLAYER_ID}" class="whiteblock"></div>
            <div id="hand_{PLAYER_ID}"></div>
        </div>
    </div>
    <!-- END playerBlock --> 

</div>

<script type="text/javascript">
    // Javascript HTML templates

    /*
    // Example:
    var jstpl_some_game_item='<div class="my_game_item" id="my_game_item_${MY_ITEM_ID}"></div>';

    */
</script>

{OVERALL_GAME_FOOTER}