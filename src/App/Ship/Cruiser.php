<?php


namespace Battleship\App\Ship;


use Battleship\Helper\OccupationType;

/**
 * Class Cruiser
 * @package Battleship\App\Ship
 */
class Cruiser extends Ship
{
    /**
     * Cruiser constructor.
     */
    public function __construct()
    {
        $this->name = "Cruiser";
        $this->width = 3;
        $this->occupationType = OccupationType::CRUISER;
    }
}