<?php


namespace Battleship\App;


use Battleship\App\Board\FIringBoard;
use Battleship\App\Board\GameBoard;
use Battleship\App\Ship\Battleship;
use Battleship\App\Ship\Cruiser;
use Battleship\App\Ship\Destroyer;
use Battleship\App\Ship\Ship;
use Battleship\App\Ship\Submarine;
use Workerman\Connection\TcpConnection;

class Player
{
    /**
     * @var $enemy
     */
    public $enemy;

    /**
     * @var GameBoard $board
     */
    public $board;

    /**
     * True means that player send message to some player
     * about a game and answer was right.
     * @var bool $inGame
     */
    public $inGame;

    /**
     * @var TcpConnection $connection
     */
    public $connection;

    /**
     * The board that contains players shots and misses
     * on enemy board.
     * @var $firingBoard
     */
    public $firingBoard;

    /**
     * @var array $ships
     */
    public $ships = [];

    /**
     * Player constructor.
     */
    public function __construct()
    {
        $this->board = new GameBoard();
        $this->firingBoard = new GameBoard();
        $this->inGame = false;
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
    public function shipAt($row, $column)
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
    public function hasLost()
    {
        $lost = true;
        foreach ($this->ships as $ship) {
           if (!$ship->isDead()) {
               $lost = false;
           }
        }

        return $lost;
    }

    /**
     * @return bool
     */
    public function inGame()
    {
        return $this->inGame;
    }
}