<?php

namespace GNC\Managers;

use GNC\Helpers\Utils;
use GNC\Helpers\Collection;
use GNC\Core\Notifications;
use GNC\Core\Stats;

/* Class to manage all the Cards for Gnc */

class Cards extends \GNC\Helpers\Pieces
{
    protected static $table = 'cards';
    protected static $prefix = 'card_';
    protected static $autoIncrement = false;
    protected static $autoremovePrefix = false;
    protected static $customFields = ['extra_datas', 'player_id', 'flipped'];

    protected static function cast($row)
    {
        $data = self::getCards()[$row['card_id']];
        return new \GNC\Models\Card($row, $data);
    }

    public static function draw($player, $n = 1, $fromDeck = true)
    {
        $deck = ($fromDeck) ? $player->getDeckName() : $player->getDiscardName();

        $n = min($n, static::countInLocation($deck));

        $cards = static::pickForLocationPId($n, $deck, 'hand', $player->getId());

        Notifications::draw($player, $cards, $fromDeck);
    }

    public static function getUiData($currentPlayerId)
    {
        $data = [];
        foreach (Players::getAll() as $pId => $player) {
            $isCurrent = $player->id == $currentPlayerId;

            $character = $player->getCharacter();
            $discard = 'discard_' . $character;
            $deck = 'deck_' . $character;
            $treasure = 'treasure_' . $character;
            $data[$pId] = [
                'hand' => $isCurrent ? $player->getCardsInHand($isCurrent)->toArray() : [],
                'nHand' => $player->getCardsInHand($isCurrent)->count(),
                'discard' => static::getInLocation($discard)->toArray(),
                'lastDiscard' => static::getTopOf($discard),
                'nTreasure' => static::countInLocation($treasure),
                'lastTreasure' => static::bindCard(static::getLastTreasure($character)),
                'nDeck' => static::countInLocation($deck),
                'guests' => [$player->getGuest(0), $player->getGuest(1), $player->getGuest(2)],
                'ballons' => [static::bindCard($player->getBalloons(0)), static::bindCard($player->getBalloons(1)), static::bindCard($player->getBalloons(2))],
                'columns' => [$player->getColumn(0)->toArray(), $player->getColumn(1)->toArray(), $player->getColumn(2)->toArray()],
            ];
        }
        return $data;
    }

    public static function getPlayableColorsInColumn($player)
    {
        $result = [];
        $columnIds = static::getLowestColumns($player);

        foreach ($columnIds as $columnId) {
            $result[$columnId] = [
                RED => true,
                BLUE => $player->getOpponent()->getColumn($columnId)->count() > 0,
                PURPLE => static::countInLocation($player->getDiscardName()) > 0,
                GREEN => static::countInLocation($player->getDeckName()) > 0,
                BROWN => static::isAdjacentAvailable($player, $columnId),
                YELLOW => true,
            ];
        }
        return $result;
    }

    public static function getDiscardableColumn($player)
    {
        $result = [];
        for ($i = 0; $i < 3; $i++) {
            $card = $player->getLastCardOfColumn($i);

            //if there is no card, pass
            if (!$card) {
                continue;
            }
            $type = $card->getType();
            switch ($type) {
                case RED:
                    if (!is_null(static::getLastTreasure($player->getCharacter())) && static::getLastTreasure($player->getCharacter())->getDeck() != GUEST) {
                        $result[$i] = $card->getId();
                    }
                    break;
                case BLUE:
                case PURPLE:
                    //need to have at least 2 cards in all columns
                    if ($player->getColumn(0)->count() + $player->getColumn(1)->count() + $player->getColumn(2)->count() >= 2) {
                        $result[$i] = $card->getId();
                    }
                    break;
                case GREEN:
                    if (static::countInLocation($player->getDeckName()) > 2) {
                        $result[$i] = $card->getId();
                    }
                    break;
                case BROWN:
                    if (static::countInLocation($player->getOpponent()->getDiscardName()) > 1) {
                        $result[$i] = $card->getId();
                    }
                    break;
                    //yellow can't be discarded
            }
        }
        return $result;
    }

    //check if there are n available cards in adjacents
    public static function isAdjacentAvailable($player, $columnId, $n = 1)
    {
        $adjacentColumns = [
            0 => [1],
            1 => [0, 2],
            2 => [1]
        ];

        $nCards = 0;
        foreach ($adjacentColumns[$columnId] as $column) {
            $nCards += $player->getColumn($column)->count();
        }
        return $nCards >= $n;
    }

    public static function getLowestColumns($player)
    {
        $columnSizes = [];
        for ($i = 0; $i < 3; $i++) {
            $columnSizes[$i] = $player->getColumn($i)->count();
        }
        $max = max(array_values($columnSizes));
        $lowests = array_keys(array_filter($columnSizes, fn ($value) => $value != $max));
        return $lowests ? $lowests : [0, 1, 2]; //if all columns are max, return all
    }

    public static function getNOfSpecificColor($player, $columnId, $color)
    {
        return $player->getColumn($columnId)->filter(fn ($card) => $card->getType() == $color)->count();
    }

    public static function getNColors($player, $columnId)
    {
        return $player->getColumn($columnId)->countDifferent(fn ($card) => $card->getType());
    }

    public static function getTotalValue($cards)
    {
        $result = 0;
        foreach ($cards as $cardId => $card) {
            $result += $card->getValue();
        }
        return $result;
    }

    public static function bindCard($card, $forced = false)
    {
        if (is_null($card)) {
            return $card;
        }

        if ($card->getFlipped() == 1 || $forced) {
            $card->id = 0;
            $card->value = "back";
        }
        return $card;
    }

    protected static function getLastTreasure($character)
    {
        return static::getTopOf('treasure_' . $character);
    }

    /* Creation of the Cards */
    public static function setupNewGame($players, $options)
    {
        $cards = [];
        // Create the deck
        foreach (self::getCards() as $id => $card) {
            if ($card['type'] == BALLOON) {
                $cards[] = [
                    'id' => $id,
                    'location' => 'balloon_' . $card['deck'],
                    'flipped' => 1,
                ];
            } else {
                $cards[] = [
                    'id' => $id,
                    'location' => 'deck_' . $card['deck'],
                ];
            }
        }

        self::create($cards);

        foreach ([DECK_CHAMOURAI, DECK_POULPIRATE, GUEST, BALLOONS_CHAMOURAI, BALLOONS_POULPIRATE] as $deck) {
            static::shuffle($deck);
        }

        foreach ($players as $pId => $player) {
            $character = Players::get($pId)->getCharacter();
            //pick 3 guests for each player, 1 for each column
            static::pickForLocation(3, DECK_GUEST, 'guest_' . $character);
            static::shuffle('guest_' . $character);
            //pick 5 cards for discard
            static::pickForLocation(5, 'deck_' . $character, 'discard_' . $character);
            //pick 5 cards for hand
            static::pickForLocationPId(5, 'deck_' . $character, HAND, $pId);
        }
    }

    public function getCards()
    {
        $f = function ($data) {
            return [
                'card_id' => $data[0],
                'deck' => $data[1],
                'type' => $data[2],
                'value' => $data[3],
            ];
        };

        return [
            1 => $f([1, GUEST, GUEST, 5]),
            2 => $f([2, GUEST, GUEST, 4]),
            3 => $f([3, GUEST, GUEST, 5]),
            4 => $f([4, GUEST, GUEST, 6]),
            5 => $f([5, GUEST, GUEST, 5]),
            6 => $f([6, GUEST, GUEST, 5]),
            7 => $f([7, GUEST, GUEST, 5]),
            8 => $f([8, GUEST, GUEST, 5]),
            9 => $f([9, POULPIRATE, RED, 1]),
            10 => $f([10, POULPIRATE, RED, 1]),
            11 => $f([11, POULPIRATE, RED, 1]),
            12 => $f([12, POULPIRATE, RED, 2]),
            13 => $f([13, POULPIRATE, RED, 2]),
            14 => $f([14, POULPIRATE, RED, 3]),
            15 => $f([15, POULPIRATE, GREEN, 1]),
            16 => $f([16, POULPIRATE, GREEN, 1]),
            17 => $f([17, POULPIRATE, GREEN, 1]),
            18 => $f([18, POULPIRATE, GREEN, 2]),
            19 => $f([19, POULPIRATE, GREEN, 2]),
            20 => $f([20, POULPIRATE, GREEN, 3]),
            21 => $f([21, POULPIRATE, PURPLE, 1]),
            22 => $f([22, POULPIRATE, PURPLE, 1]),
            23 => $f([23, POULPIRATE, PURPLE, 1]),
            24 => $f([24, POULPIRATE, PURPLE, 2]),
            25 => $f([25, POULPIRATE, PURPLE, 2]),
            26 => $f([26, POULPIRATE, PURPLE, 3]),
            27 => $f([27, POULPIRATE, BROWN, 1]),
            28 => $f([28, POULPIRATE, BROWN, 1]),
            29 => $f([29, POULPIRATE, BROWN, 1]),
            30 => $f([30, POULPIRATE, BROWN, 2]),
            31 => $f([31, POULPIRATE, BROWN, 2]),
            32 => $f([32, POULPIRATE, BROWN, 3]),
            33 => $f([33, POULPIRATE, BLUE, 1]),
            34 => $f([34, POULPIRATE, BLUE, 1]),
            35 => $f([35, POULPIRATE, BLUE, 1]),
            36 => $f([36, POULPIRATE, BLUE, 2]),
            37 => $f([37, POULPIRATE, BLUE, 2]),
            38 => $f([38, POULPIRATE, BLUE, 3]),
            39 => $f([39, POULPIRATE, YELLOW, 4]),
            40 => $f([40, POULPIRATE, YELLOW, 4]),
            41 => $f([41, POULPIRATE, YELLOW, 4]),
            42 => $f([42, POULPIRATE, YELLOW, 4]),
            43 => $f([43, POULPIRATE, YELLOW, 4]),
            44 => $f([44, POULPIRATE, YELLOW, 6]),
            45 => $f([45, CHAMOURAI, RED, 1]),
            46 => $f([46, CHAMOURAI, RED, 1]),
            47 => $f([47, CHAMOURAI, RED, 1]),
            48 => $f([48, CHAMOURAI, RED, 2]),
            49 => $f([49, CHAMOURAI, RED, 2]),
            50 => $f([50, CHAMOURAI, RED, 3]),
            51 => $f([51, CHAMOURAI, GREEN, 1]),
            52 => $f([52, CHAMOURAI, GREEN, 1]),
            53 => $f([53, CHAMOURAI, GREEN, 1]),
            54 => $f([54, CHAMOURAI, GREEN, 2]),
            55 => $f([55, CHAMOURAI, GREEN, 2]),
            56 => $f([56, CHAMOURAI, GREEN, 3]),
            57 => $f([57, CHAMOURAI, PURPLE, 1]),
            58 => $f([58, CHAMOURAI, PURPLE, 1]),
            59 => $f([59, CHAMOURAI, PURPLE, 1]),
            60 => $f([60, CHAMOURAI, PURPLE, 2]),
            61 => $f([61, CHAMOURAI, PURPLE, 2]),
            62 => $f([62, CHAMOURAI, PURPLE, 3]),
            63 => $f([63, CHAMOURAI, BROWN, 1]),
            64 => $f([64, CHAMOURAI, BROWN, 1]),
            65 => $f([65, CHAMOURAI, BROWN, 1]),
            66 => $f([66, CHAMOURAI, BROWN, 2]),
            67 => $f([67, CHAMOURAI, BROWN, 2]),
            68 => $f([68, CHAMOURAI, BROWN, 3]),
            69 => $f([69, CHAMOURAI, BLUE, 1]),
            70 => $f([70, CHAMOURAI, BLUE, 1]),
            71 => $f([71, CHAMOURAI, BLUE, 1]),
            72 => $f([72, CHAMOURAI, BLUE, 2]),
            73 => $f([73, CHAMOURAI, BLUE, 2]),
            74 => $f([74, CHAMOURAI, BLUE, 3]),
            75 => $f([75, CHAMOURAI, YELLOW, 4]),
            76 => $f([76, CHAMOURAI, YELLOW, 4]),
            77 => $f([77, CHAMOURAI, YELLOW, 4]),
            78 => $f([78, CHAMOURAI, YELLOW, 4]),
            79 => $f([79, CHAMOURAI, YELLOW, 4]),
            80 => $f([80, CHAMOURAI, YELLOW, 6]),
            81 => $f([81, POULPIRATE, BALLOON, 1]),
            82 => $f([82, POULPIRATE, BALLOON, 2]),
            83 => $f([83, POULPIRATE, BALLOON, 3]),
            84 => $f([84, CHAMOURAI, BALLOON, 1]),
            85 => $f([85, CHAMOURAI, BALLOON, 2]),
            86 => $f([86, CHAMOURAI, BALLOON, 3]),
        ];
    }
}
