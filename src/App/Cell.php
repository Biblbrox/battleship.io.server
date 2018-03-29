<?php
declare(strict_types=1);
namespace Battleship\App;

use Battleship\Helper\OccupationType;

/**
 * Class Cell
 * @package Battleship\App
 */
class Cell
{
    /**
     * May take a values from BoardField constants.
     * @var $occupationType
     */
    public $occupationType;

    /**
     * @var $coordinates
     */
    public $coordinates;

    /**
     * Cell constructor.
     * @param $row
     * @param $column
     */
    public function __construct(int $row, int $column)
    {
        if (!in_array($row, range(0, 9))
            || !in_array($column, range(0, 9))) {
            throw new \InvalidArgumentException("Ships coordinates must be in range(0..9)");
        }

        $this->coordinates = new Coordinates($row, $column);
        $this->occupationType = OccupationType::EMPTY;
    }

    /**
     * @return string
     */
    public function status() : string
    {
        return $this->occupationType;
    }

    /**
     * @return bool
     */
    public function isEmpty() : bool
    {
        return ($this->occupationType === OccupationType::EMPTY);
    }

    /**
     * @return bool
     */
    public function isOccupied() : bool
    {
        return $this->occupationType === OccupationType::BATTLESHIP
            || $this->occupationType === OccupationType::CRUISER
            || $this->occupationType === OccupationType::DESTROYER
            || $this->occupationType === OccupationType::SUBMARINE;
    }
}