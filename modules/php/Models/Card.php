<?php

namespace GNC\Models;

use GNC\Managers\Players;

/*
 * Card
 */

class Card extends \GNC\Helpers\DB_Model
{
    protected $table = 'cards';
    protected $primary = 'card_id';
    protected $attributes = [
        'id' => ['card_id', 'int'],
        'location' => 'card_location',
        'state' => ['card_state', 'int'],
        'extraDatas' => ['extra_datas', 'obj'],
        'flipped' => ['flipped', 'int'],
        'playerId' => ['player_id', 'int'],
    ];

    protected $staticAttributes = [
        'deck',
        'type',
        ['value', 'int'],
    ];

    public function __construct($row, $datas)
    {
        parent::__construct($row);
        foreach ($datas as $attribute => $value) {
            $this->$attribute = $value;
        }
    }

    public function getColumnId()
    {
        return explode('_', $this->getLocation())[1];
    }

    public function isSupported($players, $options)
    {
        return true; // Useful for expansion/ban list/ etc...
    }
}
