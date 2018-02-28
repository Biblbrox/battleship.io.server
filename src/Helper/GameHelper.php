<?php

namespace Battleship\Helper;

use Battleship\App\Cell;
use Battleship\App\GameRoom;
use Battleship\App\Player;
use Battleship\App\Ship\Ship;
use Battleship\Utils\ArrayCollection;
use Battleship\Utils\CellList;

/**
 * Class GameHelper
 * @package Battleship\Helper
 */
class GameHelper
{
    /**
     * This method generating board for battleship game
     * and returns it as a array.
     * @param Player $player
     */
    public static function generateBoard($player)
    {
        /**
         * @var Ship $ship
         */
        foreach ($player->ships as $ship) {

            $isOpen = true;
            while($isOpen) {
                $startColumn = mt_rand(0, 9);
                $startRow = mt_rand(0, 9);

                $endRow = $startRow;
                $endColumn = $startColumn;

                $orientation = mt_rand(1, 100) % 2;

                if ($orientation == 0) { // vertical
                    for ($i = 0; $i < $ship->width - 1; $i++) {
                        $endRow++;
                    }
                } else { // horizontal
                    for ($i = 0; $i < $ship->width - 1; $i++) {
                        $endColumn++;
                    }
                }

                if ($endRow > 9 || $endColumn > 9) {
                    $isOpen = true;
                    continue;
                }

                $affectedCells = $player->board->cells->range($startRow,
                    $startColumn, $endRow, $endColumn);

                $hasMovement = false;
                /**
                 * Check cell $cell on collision.
                 * @var Cell $cell
                 */
                foreach ($affectedCells as $cell) {
                    if ($cell->isOccupied()
                        || self::checkCollision($cell, $player->board->cells, $ship->occupationType, $hasMovement, $orientation)) {
                        $isOpen = true;
                        continue 2;
                    }
                    $hasMovement = true;
                }

                foreach ($affectedCells as $cell) {
                    $cell->occupationType = $ship->occupationType;
                    $ship->coordinates[]  = $cell->coordinates;
                }

                $isOpen = false;
            }
        }

//        self::printBoards($player);
    }

//    public static function printBoards($player)
//    {
//         printf("Own Board:                          Firing Board:\n");
//
//        for ($i = 0; $i < 10; $i++) {
//            for($j = 0; $j < 10; $j++) {
//                $cell = $player->board->cells->at($i, $j);
//                if ($cell->occupationType != OccupationType::EMPTY) {
//                    printf("\033[01;31m{$cell->occupationType}\033[0m");
//                } else {
//                    printf("$cell->occupationType");
//                }
//            }
//
//            printf("\t\t\t    ");
//
//            for($j = 0; $j < 10; $j++) {
//                $cell = $player->firingBoard->cells->at($i, $j);
//                if ($cell->occupationType == OccupationType::HIT) {
//                    printf("\033[01;31m{$cell->occupationType}\033[0m");
//                } else {
//                    printf("$cell->occupationType");
//                }
//            }
//
//            printf("\n");
//        }
//    }

    /**
     * Check area like
     * _______
     * |1|2|3|
     * -------
     * |4|5|6|
     * -------
     * |7|8|9|
     * -------
     * where 5 is $cell on collision with other ships.
     * @param Cell $cell
     * @param CellList $cells
     * @param OccupationType $occupationType
     * @param bool $hasMovement
     * @param integer $orientation
     * @return bool
     */
    public static function checkCollision($cell, $cells, $occupationType, $hasMovement, $orientation)
    {
        $checkCells = new CellList();

        $topLeft = $checkCells->push($cells->at($cell->coordinates->row - 1, $cell->coordinates->column - 1));
        $checkCells->push($cells->at($cell->coordinates->row - 1, $cell->coordinates->column));
        $topRight = $checkCells->push($cells->at($cell->coordinates->row - 1, $cell->coordinates->column + 1));
        $checkCells->push($cells->at($cell->coordinates->row, $cell->coordinates->column - 1));
        $checkCells->push($cells->at($cell->coordinates->row, $cell->coordinates->column + 1));
        $lowLeft = $checkCells->push($cells->at($cell->coordinates->row + 1, $cell->coordinates->column - 1));
        $checkCells->push($cells->at($cell->coordinates->row + 1, $cell->coordinates->column));
        $lowRight = $checkCells->push($cells->at($cell->coordinates->row + 1, $cell->coordinates->column + 1));

        /**
         * Check on collision with other type ships on all cells
         * @var Cell $item
         */
        foreach ($checkCells as $item) {
            if (isset($item)
                && $item->isOccupied()
                && ($item->occupationType != $occupationType)) {
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

        if (!$hasMovement) {
            foreach ($checkCells as $item) {
                if (isset($item) && !$item->isEmpty()) {
                    return true;
                }
            }
        } else {
            foreach ($checkCells as $item) {
                if (isset($item) && $orientation == 0 /* vertical */
                    && ($item->coordinates->row == $cell->coordinates->row - 1)
                    && ($item->coordinates->column == $cell->coordinates->column)
                    && ($item->isOccupied())
                    && ($item->status() != $occupationType)) {
                    return true;
                } else if (isset($item) && $orientation == 1 /* horizontal */
                    && ($item->coordinates->row    == $cell->coordinates->row)
                    && ($item->coordinates->column == $cell->coordinates->column - 1)
                    && ($item->isOccupied())
                    && ($item->status() != $occupationType)) {
                    return true;
                } else if (isset($item) && $orientation == 1 /* Horizontal. Checking on collision with same type ship on top of movement */
                    && ($item->coordinates->row    == $cell->coordinates->row)
                    && ($item->coordinates->column == $cell->coordinates->column + 1)
                    && ($item->isOccupied())
                    && ($item->status() == $occupationType)) {
                    return true;
                } else if (isset($item) && $orientation == 0 /* Vertical. Checking on collision with same type ship on right of movement */
                    && ($item->coordinates->row == $cell->coordinates->row + 1)
                    && ($item->coordinates->column == $cell->coordinates->column)
                    && ($item->isOccupied())
                    && ($item->status() == $occupationType)) {
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
    public static function findGameRoom($rooms)
    {
        $result = null;
        /**
         * @var GameRoom $room
         */
        foreach ($rooms as $room) {
            if (!$room->isFull()) {
                $result = $room;
                break;
            }
        }

        return $result;
    }
}
