<?php


namespace Battleship\App\Ship;


use Battleship\Helper\OccupationType;

class Battleship extends Ship
{
    /**
     * Battleship constructor.
     */
    public function __construct()
    {
        $this->name = "Battleship";
        $this->width = 4;
        $this->occupationType = OccupationType::BATTLESHIP;
    }
}