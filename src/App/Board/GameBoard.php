<?php
declare(strict_types=1);
namespace Battleship\App\Board;

use Battleship\App\Cell;
use Battleship\Utils\CellList;

/**
 * Class GameBoard
 * @package Battleship\App\Board
 */
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
                $this->cells[] = new Cell($i, $j);
            }
        }
    }


    /**
     * Return the game board cells as a string.
     * @return array
     */
    public function toArray() : array
    {
        $board_array = [];

        foreach ($this->cells as $cell) {
            $board_array[] = $cell->occupationType;
        }

        return $board_array;
    }

}