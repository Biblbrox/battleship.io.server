<?php

declare(strict_types=1);

namespace Battleship\Helper;

use Battleship\App\Cell;
use Battleship\App\GameRoom;
use Battleship\App\Player;
use Battleship\Utils\ArrayCollection;
use Battleship\Utils\CellList;
use Closure;
use Workerman\Connection\TcpConnection;

/**
 * Class GameHelper
 * @package Battleship\Helper
 */
class GameHelper
{
    /**
     * This method generating board for battleship game
     * and returns it as an array.
     * @param TcpConnection $connection
     * @return \Battleship\App\Player
     */
    public static function generateUser($connection) : Player
    {
        $player = new Player($connection->id);
        $player->connection = $connection;

        foreach ($player->ships as $ship) {
            $placeNotFound = true;
            while($placeNotFound) {
                $startColumn = mt_rand(0, 9);
                $startRow = mt_rand(0, 9);

                $endRow = $startRow;
                $endColumn = $startColumn;

                $orientation = mt_rand(1, 100) % 2;

                if ($orientation == 0) { // vertical
                    $endRow += $ship->width - 1;
                } else { // horizontal
                    $endColumn += $ship->width - 1;
                }

                if ($endRow > 9 || $endColumn > 9) {
                    $placeNotFound = true;
                    continue;
                }

                $affectedCells = $player->board->cells->range($startRow,
                    $startColumn, $endRow, $endColumn);

                $hasMovement = false;
                foreach ($affectedCells as $cell) {
                    if ($cell->isOccupied()
                        || self::checkCollision($cell, $player->board->cells, $ship->occupationType, $hasMovement, $orientation)) {
                        $placeNotFound = true;
                        continue 2;
                    }
                    $hasMovement = true;
                }

                foreach ($affectedCells as $cell) {
                    $cell->occupationType = $ship->occupationType;
                    $ship->coordinates[]  = $cell->coordinates;
                }

                $placeNotFound = false;
            }
        }

        return $player;
    }

    /**
     * Check area like
     * _______
     * |1|2|3|
     * -------
     * |4|x|6|
     * -------
     * |7|8|9|
     * -------
     * where x is $cell which we are checking
     * @param Cell $cell
     * @param CellList $cells
     * @param OccupationType $occupationType
     * @param bool $hasMovement
     * @param integer $orientation
     * @return bool
     */
    public static function checkCollision($cell, $cells, $occupationType, $hasMovement, $orientation) : bool
    {
        $checkCells = new CellList();

        $checkCells[] = $cells->at($cell->coordinates->row - 1, $cell->coordinates->column);
        $checkCells[] = $cells->at($cell->coordinates->row, $cell->coordinates->column - 1);
        $checkCells[] = $cells->at($cell->coordinates->row, $cell->coordinates->column + 1);
        $checkCells[] = $cells->at($cell->coordinates->row + 1, $cell->coordinates->column);

        $checkCells[] = $cells->at($cell->coordinates->row - 1, $cell->coordinates->column - 1);
        $topLeft = $checkCells->at($cell->coordinates->row - 1, $cell->coordinates->column - 1);

        $checkCells[] = $cells->at($cell->coordinates->row - 1, $cell->coordinates->column + 1);
        $topRight = $checkCells->at($cell->coordinates->row - 1, $cell->coordinates->column + 1);

        $checkCells[] = $cells->at($cell->coordinates->row + 1, $cell->coordinates->column - 1);
        $lowLeft = $checkCells->at($cell->coordinates->row + 1, $cell->coordinates->column - 1);

        $checkCells[] = $cells->at($cell->coordinates->row + 1, $cell->coordinates->column + 1);
        $lowRight = $checkCells->at($cell->coordinates->row + 1, $cell->coordinates->column + 1);

        /**
         * Check on collision with other type ships on all cells
         * @var Cell $cell
         */
        foreach ($checkCells as $cell) {
            if (isset($cell)
                && $cell->isOccupied()
                && ($cell->occupationType != $occupationType)) {
                return true;
            }
        }

        /**
         * Check on collision with all ships on edges. Like 1,3,7,9
         */
        if (   (isset($topLeft)  && !$topLeft->isEmpty())
            || (isset($topRight) && !$topRight->isEmpty())
            || (isset($lowLeft)  && !$lowLeft->isEmpty())
            || (isset($lowRight) && !$lowRight->isEmpty())) {
            return true;
        }

        /**
         * Check if we're already have checked one or more cells of this ship.
         */
        if (!$hasMovement) {
            foreach ($checkCells as $cell) {
                if (isset($cell) && !$cell->isEmpty()) {
                    return true;
                }
            }
        } else {
            foreach ($checkCells as $cell) {
                if (isset($cell) && $orientation == 0 /* vertical */
                    && ($cell->coordinates->row == $cell->coordinates->row - 1)
                    && ($cell->coordinates->column == $cell->coordinates->column)
                    && ($cell->isOccupied())
                    && ($cell->status() != $occupationType)) {
                    return true;
                } else if (isset($cell) && $orientation == 1 /* horizontal */
                    && ($cell->coordinates->row    == $cell->coordinates->row)
                    && ($cell->coordinates->column == $cell->coordinates->column - 1)
                    && ($cell->isOccupied())
                    && ($cell->status() != $occupationType)) {
                    return true;
                } else if (isset($cell) && $orientation == 1 /* Horizontal. Checking on collision with same type ship on top of movement */
                    && ($cell->coordinates->row    == $cell->coordinates->row)
                    && ($cell->coordinates->column == $cell->coordinates->column + 1)
                    && ($cell->isOccupied())
                    && ($cell->status() == $occupationType)) {
                    return true;
                } else if (isset($cell) && $orientation == 0 /* Vertical. Checking on collision with same type ship on right of movement */
                    && ($cell->coordinates->row == $cell->coordinates->row + 1)
                    && ($cell->coordinates->column == $cell->coordinates->column)
                    && ($cell->isOccupied())
                    && ($cell->status() == $occupationType)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param ArrayCollection $rooms
     * @return GameRoom | null
     */
    public static function findGameRoom($rooms) : ?GameRoom
    {
        $result = null;
        foreach ($rooms as $room) {
            if (!$room->isFull()) {
                $result = $room;
                break;
            }
        }

        return $result;
    }
}
