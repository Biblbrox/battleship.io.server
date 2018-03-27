<?php

namespace Battleship\App\Ship;

/**
 * Class Ship
 * @package Battleship\App\Ship
 */
abstract class Ship
{
    /**
     * @var string $name
     */
    public $name;
    /**
     * @var int $width
     */
    public $width;
    /**
     * @var int $hits
     */
    public $hits;
    /**
     * @var string $occupationType
     */
    public $occupationType;

    /**
     * @var array Coordinates
     */
    public $coordinates = [];
    /**
     * @return bool
     */
    public function isDead() : bool
    {
        return $this->hits >= $this->width;
    }
}