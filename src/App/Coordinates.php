<?php

namespace Battleship\App;

/**
 * Class Coordinates
 * @package Battleship\App
 */
class Coordinates
{
    /**
     * The row coordinate.
     * @var $row
     */
    public $row;
    /**
     * The column coordinate.
     * @var $column
     */
    public $column;

    /**
     * Coordinates constructor.
     * @param $row
     * @param $column
     */
    public function __construct($row, $column)
    {
        $this->row = $row;
        $this->column = $column;
    }

}