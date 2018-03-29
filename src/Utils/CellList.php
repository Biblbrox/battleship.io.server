<?php
declare(strict_types=1);
namespace Battleship\Utils;

use Battleship\App\Cell;
use InvalidArgumentException;

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
    public function at(int $row, int $column) : ?Cell
    {
        $this->data = array_filter($this->data);
        if (!in_array($row, range(0, 9))
            || !in_array($column, range(0, 9))) {
            return null;
        }

        $return = null;
        foreach ($this->data as $key => $cell) {
            if ($cell->coordinates->row === $row
                && $cell->coordinates->column === $column) {
                $return = $this->data[$key];
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
    public function range(int $startRow, int $startColumn, int $endRow, int $endColumn) : CellList
    {
        if ($startRow < 0    || $startColumn < 0
            || $startRow > 9 || $startColumn > 9
            || $endRow < 0   || $endColumn < 0
            || $endRow > 9   || $endColumn > 9) {
            throw new InvalidArgumentException("Row coordinates must be in range(0..9)");
        }

        $this->data = array_filter($this->data);
        $ranged = new CellList();
        foreach ($this->data as $key => $cell) {
            if ($cell->coordinates->row >= $startRow
                && $cell->coordinates->column >= $startColumn
                && $cell->coordinates->row <= $endRow
                && $cell->coordinates->column <= $endColumn) {
                $ranged[] = $this->data[$key];
            }
        }

        return $ranged;
    }
}