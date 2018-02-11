<?php


namespace Battleship\App\Ship;


use Battleship\Helper\OccupationType;

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