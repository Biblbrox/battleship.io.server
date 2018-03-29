<?php

namespace Battleship\App\Ship;

use Battleship\Helper\OccupationType;

/**
 * Class Submarine
 * @package Battleship\App\Ship
 */
class Submarine extends Ship
{
    /**
     * Submarine constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->name = "Submarine";
        $this->width = 1;
        $this->occupationType = OccupationType::SUBMARINE;
    }
}