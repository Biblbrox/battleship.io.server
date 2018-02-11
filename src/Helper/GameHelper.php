<?php

namespace Battleship\Helper;

use Battleship\App\Cell;
use Battleship\App\Player;
use Battleship\App\Ship\Ship;
use Battleship\CellList;

class GameHelper
{
    /**
     * This method generating board for battleship game
     * and returns it as a array.
     * @param Player $player
     * @return string
     */
    public static function generateBoard($player)
    {
        /**
         * @var Ship $ship
         */
        foreach ($player->ships as $ship) {

            $isOpen = true;
            while($isOpen) {
                $startColumn = rand(0, 9);
                $startRow = rand(0, 9);

                $endRow = $startRow;
                $endColumn = $startColumn;

                $orientation = rand(1, 100) % 2;

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
                        || self::hasCollision($cell, $player->board->cells, $ship->occupationType, $hasMovement, $orientation)) {
                        $isOpen = true;
                        continue 2;
                    }
                    $hasMovement = true;
                }

                foreach ($affectedCells as $cell) {
                    $cell->occupationType = $ship->occupationType;
                    $ship->coordinates[] = $cell->coordinates;
                }

                $isOpen = false;
            }
        }

        self::printBoards($player);

        return "Board";
    }

    public static function printBoards($player)
    {
         printf("Own Board:                          Firing Board:\n");

        for ($i = 0; $i < 10; $i++) {
            for($j = 0; $j < 10; $j++) {
                $cell = $player->board->cells->at($i, $j);
                if ($cell->occupationType != OccupationType::EMPTY) {
                    printf("\033[01;31m{$cell->occupationType}\033[0m");
                } else {
                    printf("$cell->occupationType");
                }
            }

            printf("\t\t\t    ");

            for($j = 0; $j < 10; $j++) {
                $cell = $player->firingBoard->cells->at($i, $j);
                if ($cell->occupationType == OccupationType::HIT) {
                    printf("\033[01;31m{$cell->occupationType}\033[0m");
                } else {
                    printf("$cell->occupationType");
                }
            }

            printf("\n");
        }
    }

    /**
     * Hit ship on $board with coordinates $x and $y
     * @param $board
     * @param $x
     * @param $y
     */
    public static function hit($board, $x, $y)
    {

    }

    /**
     * @param array $users
     * @param $player
     * @return string
     */
    public static function findEnemy($users, Player &$player)
    {
        foreach ($users as $user) {
            if (!$user->enemy && !$user->inGame && $user != $player) {
                $user->enemy = $player->connection->id;
                $player->enemy = $user->connection->id;

                return true;
            }
        }

        return false;
    }

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
    public static function hasCollision($cell, $cells, $occupationType, $hasMovement, $orientation)
    {
        $checkCells = new CellList();

        $topLeft = $checkCells->push($cells->at($cell->coordinates->row - 1, $cell->coordinates->column - 1));
        $topTop = $checkCells->push($cells->at($cell->coordinates->row - 1, $cell->coordinates->column));
        $topRight = $checkCells->push($cells->at($cell->coordinates->row - 1, $cell->coordinates->column + 1));
        $middleLeft = $checkCells->push($cells->at($cell->coordinates->row, $cell->coordinates->column - 1));
        $middleRight = $checkCells->push($cells->at($cell->coordinates->row, $cell->coordinates->column + 1));
        $lowLeft = $checkCells->push($cells->at($cell->coordinates->row + 1, $cell->coordinates->column - 1));
        $lowMiddle = $checkCells->push($cells->at($cell->coordinates->row + 1, $cell->coordinates->column));
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
        if ((isset($topLeft)     && !$topLeft->isEmpty())
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
                    && ($item->occupationType != $occupationType)) {
                    return true;
                } else if (isset($item) && $orientation == 1 /* horizontal */
                    && ($item->coordinates->row == $cell->coordinates->row)
                    && ($item->coordinates->column == $cell->coordinates->column - 1)
                    && ($item->isOccupied())
                    && ($item->occupationType != $occupationType)) {
                    return true;
                } else if (isset($item) && $orientation == 1 /* Horizontal. Checking on collision with same type ship on top of movement */
                    && ($item->coordinates->row == $cell->coordinates->row)
                    && ($item->coordinates->column == $cell->coordinates->column + 1)
                    && ($item->isOccupied())
                    && ($item->occupationType == $occupationType)) {
                    return true;
                } else if (isset($item) && $orientation == 0 /* Vertical. Checking on collision with same type ship on right of movement */
                    && ($item->coordinates->row == $cell->coordinates->row + 1)
                    && ($item->coordinates->column == $cell->coordinates->column)
                    && ($item->isOccupied())
                    && ($item->occupationType == $occupationType)) {
                    return true;
                }
            }
        }

        return false;
    }
}


//
//namespace Battleship\Helper;
//
//use Battleship\App\Cell;
//use Battleship\App\Player;
//
//class GameHelper
//{
//    /**
//     * This method generating board for battleship game
//     * and returns it as a array.
//     * @param Player $player
//     * @return string
//     */
//    public static function generateBoard($player)
//    {
//        foreach ($player->ships as $ship) {
//            $count = 4 - $ship->width + 1;
//
//            for ($k = 0; $k < $count; $k++) {
//
//                $isOpen = true;
//                while($isOpen) {
//                    $startColumn = rand(0, 9);
//                    $startRow = rand(0, 9);
//
//                    $endRow = $startRow;
//                    $endColumn = $startColumn;
//
//                    $orientation = rand(1, 100) % 2;
//
//                    if ($orientation == 0) { // vertical
//                        for ($i = 0; $i < $ship->width - 1; $i++) {
//                            $endRow++;
//                        }
//                    } else { // horizontal
//                        for ($i = 0; $i < $ship->width - 1; $i++) {
//                            $endColumn++;
//                        }
//                    }
//
//                    if ($endRow > 9 || $endColumn > 9) {
//                        $isOpen = true;
//                        continue;
//                    }
//
//                    $affectedCells = self::range($player->board->cells, $startRow,
//                        $startColumn, $endRow, $endColumn);
//
//                    $hasMovement = false;
//                    /**
//                     * Check cell $cell on collision.
//                     * @var Cell $cell
//                     */
//                    foreach ($affectedCells as $cell) {
//                        if ($cell->isOccupied()
//                            || self::hasCollision($cell, $player->board->cells, $ship->occupationType, $hasMovement, $orientation)) {
//                            $isOpen = true;
//                            continue 2;
//                        }
//                        $hasMovement = true;
//                    }
//
//                    foreach ($affectedCells as $cell) {
//                        $cell->occupationType = $ship->occupationType;
//                    }
//
//                    $isOpen = false;
//                }
//            }
//        }
//
//        printf("Own Board:                          Firing Board:\n");
//
//        for ($i = 0; $i < 10; $i++) {
//            for($j = 0; $j < 10; $j++) {
//                $cell = self::at($player->board->cells, $i, $j);
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
//                $cell = self::at($player->board->cells, $i, $j);
//                printf("$cell->occupationType");
//            }
//
//            printf("\n");
//        }
//
//        return "Board";
//    }
//
//    /**
//     * @param $cells
//     * @param $startRow
//     * @param $startColumn
//     * @param $endRow
//     * @param $endColumn
//     * @return array
//     */
//    public static function range(array $cells, $startRow, $startColumn, $endRow, $endColumn)
//    {
//        $ranged = [];
//        foreach ($cells as $key => $cell) {
//            if ($cell->coordinates->row >= $startRow
//                && $cell->coordinates->column >= $startColumn
//                && $cell->coordinates->row <= $endRow
//                && $cell->coordinates->column <= $endColumn) {
//                $ranged[] = &$cells[$key];
//            }
//        }
//
//        return $ranged;
//    }
//
//    /**
//     * @param $cells
//     * @param $row
//     * @param $column
//     * @return Cell
//     */
//    public static function at($cells, $row, $column)
//    {
//        $ranged = null;
//        foreach ($cells as $key => $cell) {
//            if ($cell->coordinates->row == $row
//                && $cell->coordinates->column == $column) {
//                $ranged = &$cells[$key];
//            }
//        }
//
//        return $ranged;
//    }
//
//    /**
//     * Hit ship on $board with coordinates $x and $y
//     * @param $board
//     * @param $x
//     * @param $y
//     */
//    public static function hit($board, $x, $y)
//    {
//
//    }
//
//    /**
//     * @param array $users
//     * @param $player
//     * @return string
//     */
//    public static function findEnemy($users, Player $player)
//    {
//        foreach ($users as $user) {
//            if (!$user->enemy && !$user->inGame && $user != $player) {
//                $user->enemy = $player->connection->id;
//                $player->enemy = $user->connection->id;
//
//                return true;
//            }
//        }
//
//        return false;
//    }
//
//    /**
//     * Check area like
//     * _______
//     * |1|2|3|
//     * -------
//     * |4|5|6|
//     * -------
//     * |7|8|9|
//     * -------
//     * where 5 is $cell on collision with other ships.
//     * @param Cell $cell
//     * @param array $cells
//     * @param OccupationType $occupationType
//     * @param bool $hasMovement
//     * @param integer $orientation
//     * @return bool
//     */
//    public static function hasCollision($cell, array $cells = [], $occupationType, $hasMovement, $orientation)
//    {
//        $checkCells = [];
//
//        $checkCells[] = $topLeft = self::at($cells, $cell->coordinates->row - 1, $cell->coordinates->column - 1);
//        $checkCells[] = $topTop = self::at($cells, $cell->coordinates->row - 1, $cell->coordinates->column);
//        $checkCells[] = $topRight = self::at($cells, $cell->coordinates->row - 1, $cell->coordinates->column + 1);
//        $checkCells[] = $middleLeft = self::at($cells, $cell->coordinates->row, $cell->coordinates->column - 1);
//        $checkCells[] = $middleRight = self::at($cells, $cell->coordinates->row, $cell->coordinates->column + 1);
//        $checkCells[] = $lowLeft = self::at($cells, $cell->coordinates->row + 1, $cell->coordinates->column - 1);
//        $checkCells[] = $lowMiddle = self::at($cells, $cell->coordinates->row + 1, $cell->coordinates->column);
//        $checkCells[] = $lowRight = self::at($cells, $cell->coordinates->row + 1, $cell->coordinates->column + 1);
//
//        /**
//         * Check on collision with other type ships on all cells
//         * @var Cell $item
//         */
//        foreach ($checkCells as $item) {
//            if (isset($item)
//                && self::occupationByShip($item)
//                && ($item->occupationType != $occupationType)) {
//                return true;
//            }
//        }
//
//        /**
//         * Check on collision with all ships on edges. Like 1,3,7,9
//         */
//        if ((isset($topLeft) &&     !$topLeft->isEmpty())
//            || (isset($topRight) && !$topRight->isEmpty())
//            || (isset($lowLeft) &&  !$lowLeft->isEmpty())
//            || (isset($lowRight) && !$lowRight->isEmpty())) {
//            return true;
//        }
//
//        if (!$hasMovement) {
//            foreach ($checkCells as $item) {
//                if (isset($item) && !$item->isEmpty()) {
//                    return true;
//                }
//            }
//        } else {
//            foreach ($checkCells as $item) {
//                if (isset($item) && $orientation == 0 /* vertical */
//                    && ($item->coordinates->row == $cell->coordinates->row - 1)
//                    && ($item->coordinates->column == $cell->coordinates->column)
//                    && ($item->occupationByShip())
//                    && ($item->occupationType != $occupationType)) {
//                    return true;
//                } else if (isset($item) && $orientation == 1 /* horizontal */
//                    && ($item->coordinates->row == $cell->coordinates->row)
//                    && ($item->coordinates->column == $cell->coordinates->column - 1)
//                    && ($item->occupationByShip())
//                    && ($item->occupationType != $occupationType)) {
//                    return true;
//                } else if (isset($item) && $orientation == 1 /* Horizontal. Checking on collision with same type ship on top of movement */
//                    && ($item->coordinates->row == $cell->coordinates->row)
//                    && ($item->coordinates->column == $cell->coordinates->column + 1)
//                    && ($item->occupationByShip())
//                    && ($item->occupationType == $occupationType)) {
//                    return true;
//                } else if (isset($item) && $orientation == 0 /* Vertical. Checking on collision with same type ship on right of movement */
//                    && ($item->coordinates->row == $cell->coordinates->row + 1)
//                    && ($item->coordinates->column == $cell->coordinates->column)
//                    && ($item->occupationByShip())
//                    && ($item->occupationType == $occupationType)) {
//                    return true;
//                }
//            }
//        }
//
//        return false;
//    }
//
//    /**
//     * @param $cell
//     * @return bool
//     */
//    public static function occupationByShip($cell)
//    {
//        return ($cell->occupationType == OccupationType::SUBMARINE)
//            ||($cell->occupationType == OccupationType::BATTLESHIP)
//            || ($cell->occupationType == OccupationType::CRUISER)
//            || ($cell->occupationType == OccupationType::DESTROYER);
//    }
//}