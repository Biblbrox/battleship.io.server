<?php
declare(strict_types=1);
namespace Battleship\App\Ship;

use Battleship\Utils\ArrayCollection;

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
     * @var ArrayCollection Coordinates
     */
    public $coordinates;

    public function __construct()
    {
        $this->coordinates = new ArrayCollection();
    }

    /**
     * @return bool
     */
    public function isDead() : bool
    {
        return $this->hits >= $this->width;
    }
}