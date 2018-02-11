<?php


namespace Battleship\App\Ship;


use Battleship\Helper\OccupationType;

class Destroyer extends Ship
{
    /**
     * Destroyer constructor.
     */
    public function __construct()
    {
        $this->name = "Destroyer";
        $this->width = 2;
        $this->occupationType = OccupationType::DESTROYER;
    }
}