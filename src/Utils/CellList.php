<?php

namespace Battleship\Utils;

use Battleship\App\Cell;

/**
 * Class CellList
 * @package Battleship\Utils
 */
class CellList extends ArrayCollection
{
    /**
    * @param $row
    * @param $column
    * @return Cell
    */
    public function at($row, $column) : Cell
    {
        $return = null;
        foreach ($this->data as $key => $cell) {
            if ($cell->coordinates->row == $row
                && $cell->coordinates->column == $column) {
                $return = &$this->data[$key];
            }
        }

        return $return;
    }

    /**
     * @param $startRow
     * @param $startColumn
     * @param $endRow
     * @param $endColumn
     * @return CellList
     */
    public function range($startRow, $startColumn, $endRow, $endColumn) : CellList
    {
        $ranged = new CellList();
        foreach ($this->data as $key => $cell) {
            if ($cell->coordinates->row >= $startRow
                && $cell->coordinates->column >= $startColumn
                && $cell->coordinates->row <= $endRow
                && $cell->coordinates->column <= $endColumn) {
                $ranged[] = &$this->data[$key];
            }
        }

        return $ranged;
    }
}