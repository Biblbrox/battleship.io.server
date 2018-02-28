<?php


namespace Battleship\App\Ship;

/**
 * Class Ship
 * @package Battleship\App\Ship
 */
abstract class Ship
{
    /**
     * @var
     */
    public $name;
    /**
     * @var
     */
    public $width;
    /**
     * @var
     */
    public $hits;
    /**
     * @var
     */
    public $occupationType;

    /**
     * @var
     */
    public $coordinates = [];
    /**
     * @return bool
     */
    public function isDead()
    {
        return $this->hits >= $this->width;
    }
}