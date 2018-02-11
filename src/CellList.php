<?php


namespace Battleship;


use Battleship\App\Cell;

class CellList implements \Iterator
{
    /**
     * @var $cells
     */
    private $cells = [];

    /**
     * @param $row
     * @param $column
     * @return Cell
     */
    public function at($row, $column)
    {
        $return = null;
        foreach ($this->cells as $key => $cell) {
            if ($cell->coordinates->row == $row
                && $cell->coordinates->column == $column) {
                $return = &$this->cells[$key];
            }
        }

        return $return;
    }

    /**
     * @param $_cell
     * @return Cell
     */
    public function push($_cell)
    {
        $this->cells[] = $_cell;

        return $_cell;
    }

    public function range($startRow, $startColumn, $endRow, $endColumn)
    {
        $ranged = [];
        foreach ($this->cells as $key => $cell) {
            if ($cell->coordinates->row >= $startRow
                && $cell->coordinates->column >= $startColumn
                && $cell->coordinates->row <= $endRow
                && $cell->coordinates->column <= $endColumn) {
                $ranged[] = &$this->cells[$key];
            }
        }

        return $ranged;
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return current($this->cells);
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        next($this->cells);
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return key($this->cells);
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return null !== key($this->cells);
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        reset($this->cells);
    }
}