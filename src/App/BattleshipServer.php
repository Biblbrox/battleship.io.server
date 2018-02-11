<?php
namespace Battleship\App;

use Battleship\CellList;
use Battleship\Helper\GameHelper;
use Battleship\Helper\OccupationType;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;

class BattleshipServer
{
    /**
     * @var $users
     */
    private $users = [];

    /**
     * BattleshipServer constructor.
     */
    public function __construct()
    {
        $ws_worker = new Worker("websocket://0.0.0.0:2346");

        // 4 processes
        $ws_worker->count = 4;

        // Emitted when new connection come
        $ws_worker->onConnect = function($connection)
        {
            // Emitted when websocket handshake done
            $connection->onWebSocketConnect = function(TcpConnection $connection)
            {
                echo "New connection\n";
                $user = new Player();
                GameHelper::generateBoard($user);
                $user->connection = $connection;
                $this->users[$connection->id] = $user;
            };
        };

        // Emitted when data is received
        $ws_worker->onMessage = function(TcpConnection $connection, $data)
        {
            $msg = json_decode($data);
            switch ($msg->msg)
            {
                case "findEnemy":
                    $user = $this->users[$connection->id];
                    if (!$user->enemy && !$user->inGame) {
                        if (GameHelper::findEnemy($this->users, $user)) {
                            $user->inGame = true;
                        }
                    }
                    break;
                case "hit":

                    $row = $msg->row;
                    $column = $msg->column;
                    $userId = $msg->userId;

                    if (!isset($row) || !isset($column)) {
                        $connection->send("You must set row and column");
                        break;
                    }

                    if (!isset($userId)) {
                        $connection->send("You must set $userId");
                    }

                    $user = $this->users[$userId];

                    if (!isset($user)) {
                        $connection->send("User with id: $userId was not found");
                    }

                    $firedCell = $this->users[$connection->id]->firingBoard->cells->at(
                        $row,
                        $column);

                    if(!$this->hitCell($connection, $user, $row, $column, $firedCell)) {
                        break;
                    }

                    break;
                default:
                    $connection->send("Action $data is't supported");
                    break;
            }

            // Send hello $data
            $connection->send('hello ' . $data);
            /**
             * @var Player $user
             */
            foreach ($this->users as $user) {
                $enemyId = $user->enemy;
                $id = $user->connection->id;
                printf("User {$user->connection->id}: Id = $id; enemyId = $enemyId\n");
            }

            if (!empty($this->users)) {
                GameHelper::printBoards($this->users[$connection->id]);
            }
        };

        // Emitted when connection closed
        $ws_worker->onClose = function($connection)
        {
            echo "Connection closed";
            unset($this->users[$connection->id]);
            $this->users = array_filter($this->users, function ($item) {
                return isset($item);
            });
        };
    }



    /**
     * *********************************
     * Messages of the attacker:
     * {
     *     msg: "hit",
     *     row: ...,
     *     column: ....,
     *     userId: ....
     * },
     * *********************************
     * Messages to the attacker:
     * {
     *     msg: "win"
     * },
     * {
     *     msg: "enemyInjured",
     *     row: ....,
     *     column: ....
     * },
     * {
     *     msg: "youFall",
     *     row: ....,
     *     column: ....
     * }
     * *********************************
     * Messages to the attacked:
     * {
     *     msg: "lost"
     * },
     * {
     *     msg: "youInjured",
     *     row: ....,
     *     column: ....
     * },
     * {
     *     msg: "enemyFall",
     *     row: ....,
     *     column: ....
     * }
     * *********************************
     * @param TcpConnection $connection
     * @param Player $userUnderAttack
     * @param integer $row
     * @param integer $column
     * @param Cell $firedCell
     * @return bool
     */
    private function hitCell($connection, $userUnderAttack, $row, $column, $firedCell)
    {
        /**
         * @var CellList $cells
         */
        $cells = $userUnderAttack->board->cells;
        $cell = $cells->at($row, $column);

        if (!isset($cell)) {
            $connection->send("Cell at $row, $column was not found on enemy board");
            return false;
        }

        if ($cell->isOccupied()) {
            $firedCell->occupationType = OccupationType::HIT;
            $userUnderAttack->connection->send(json_encode([
                'msg' => "youInjured",
                'row' => $row,
                'column' => $column
            ]));
            $connection->send(json_encode([
                'msg' => 'enemyInjured',
                'row' => $row,
                'column' => $column
            ]));
            $attackedShip = $userUnderAttack->shipAt($row, $column);
            /**
             * @var Coordinates $item
             */
            foreach ($attackedShip->coordinates as $item) {
                if ($item->row == $row && $item->column == $column) {
                    $attackedShip->hits++;
                }
            }
            $firedCell->occupationType = OccupationType::HIT;
            if ($userUnderAttack->hasLost()) {
                $userUnderAttack->connection->send(json_encode([
                    'msg' => 'lost'
                ]));
                $connection->send(json_encode([
                    'msg' => 'win'
                ]));
                $connection->close();
                $userUnderAttack->connection->close();
            }
        } else {
            $connection->send(json_encode([
                'msg' => 'youFall',
                'row' => $row,
                'column' => $column
            ]));
            $firedCell->occupationType = OccupationType::MISS;
            $userUnderAttack->connection->send(json_encode([
                'msg' => 'enemyFall',
                'row' => $row,
                'column' => $column
            ]));
        }
        return true;
    }

    /**
     * Run the Battleship websocket server
     */
    public function run()
    {
        Worker::runAll();
    }
}