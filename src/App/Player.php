<?php

namespace Battleship\App;

use Battleship\App\Board\GameBoard;
use Battleship\App\Ship\Battleship;
use Battleship\App\Ship\Cruiser;
use Battleship\App\Ship\Destroyer;
use Battleship\App\Ship\Ship;
use Battleship\App\Ship\Submarine;
use Workerman\Connection\TcpConnection;

/**
 * Class Player
 * @package Battleship\App
 */
class Player
{
    /**
     * @var int $id
     */
    public $id;

    /**
     * @var $enemy
     */
    public $enemy;

    /**
     * @var GameBoard $board
     */
    public $board;

    /**
     * @var TcpConnection $connection
     */
    public $connection;

    /**
     * The board that contains players shots and misses
     * on enemy board.
     * @var GameBoard $firingBoard
     */
    public $firingBoard;

    /**
     * @var array $ships
     */
    public $ships = [];

    /**
     * Player constructor.
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->id = $id;
        $this->board = new GameBoard();
        $this->firingBoard = new GameBoard();
        $this->enemy = false;
        $this->ships = [
            new Battleship(),
            new Cruiser(),
            new Cruiser(),
            new Destroyer(),
            new Destroyer(),
            new Destroyer(),
            new Submarine(),
            new Submarine(),
            new Submarine(),
            new Submarine()
        ];
    }

    /**
     * @param $row
     * @param $column
     * @return Ship|null
     */
    public function shipAt(int $row, int $column) : Ship
    {
        $result = null;
        foreach ($this->ships as $ship) {
            foreach ($ship->coordinates as $item) {
                if ($item->row == $row && $item->column == $column) {
                    $result = $ship;
                }
            }
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function hasLost() : bool
    {
        $lost = true;
        foreach ($this->ships as $ship) {
           if (!$ship->isDead()) {
               $lost = false;
           }
        }

        return $lost;
    }
}