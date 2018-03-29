<?php

namespace Battleship\App\Ship;

use Battleship\Helper\OccupationType;

/**
 * Class Battleship
 * @package Battleship\App\Ship
 */
class Battleship extends Ship
{
    /**
     * Battleship constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->name = "Battleship";
        $this->width = 4;
        $this->occupationType = OccupationType::BATTLESHIP;
    }
}