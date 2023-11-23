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
}
