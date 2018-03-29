<?php

namespace Battleship\App\Ship;

use Battleship\Helper\OccupationType;

/**
 * Class Destroyer
 * @package Battleship\App\Ship
 */
class Destroyer extends Ship
{
    /**
     * Destroyer constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->name = "Destroyer";
        $this->width = 2;
        $this->occupationType = OccupationType::DESTROYER;
    }
}