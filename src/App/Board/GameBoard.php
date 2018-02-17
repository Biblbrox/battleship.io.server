<?php


namespace Battleship\App\Board;


use Battleship\App\Cell;
use Battleship\CellList;

class GameBoard
{
    /**
     * @var CellList $cells
     */
    public $cells;

    /**
     * GameBoard constructor.
     */
    public function __construct()
    {
        $this->cells = new CellList();
        foreach (range(0, 9) as $i) {
            foreach (range(0, 9) as $j) {
                $this->cells->push(new Cell($i, $j));
            }
        }
    }

    /**
     * Return the game board cells as a string.
     */
    public function toArray()
    {
        $board_array = [];

        foreach ($this->cells as $cell) {
            $board_array[] = $cell->occupationType;
        }

        return $board_array;
    }

}