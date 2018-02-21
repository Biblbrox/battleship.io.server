<?php


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
    public function __construct($row, $column)
    {
        $this->coordinates = new Coordinates($row, $column);
        $this->occupationType = OccupationType::EMPTY;
    }

    /**
     * @return string
     */
    public function status()
    {
        return $this->occupationType;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return ($this->occupationType == OccupationType::EMPTY);
    }

    /**
     * @return bool
     */
    public function isOccupied()
    {
        return $this->occupationType === OccupationType::BATTLESHIP
            || $this->occupationType === OccupationType::CRUISER
            || $this->occupationType === OccupationType::DESTROYER
            || $this->occupationType === OccupationType::SUBMARINE;
    }
}