<?php

namespace Battleship\App;

use Battleship\Helper\GameHelper;
use Battleship\Helper\OccupationType;
use Battleship\Helper\ServerMessage;
use Battleship\Utils\ArrayCollection;
use Battleship\Utils\CellList;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;

/**
 * Class BattleshipServer
 * @package Battleship\App
 */
class BattleshipServer
{
    /**
     * @var $users
     */
    private $users;

    /**
     * @var ArrayCollection $rooms
     */
    private $rooms;

    /**
     * BattleshipServer constructor.
     */
    public function __construct()
    {
        $context = [
            'ssl' => [
                'local_cert'  => '/home/staralex/test/localhost.cert',
                'local_pk'    => '/home/staralex/test/localhost.key',
                'verify_peer' => false,
            ]
        ];
        $ws_worker = new Worker("websocket://0.0.0.0:2346"/*, $context*/);

//        $ws_worker->transport = "ssl";

        $this->users = new ArrayCollection();
        $this->rooms = new ArrayCollection();

        $ws_worker->onConnect = function($connection) use ($ws_worker)
        {
            $connection->onWebSocketConnect = function(TcpConnection $connection) use ($ws_worker)
            {
                echo "New connection\n";
                $user = new Player($connection->id);
                GameHelper::generateBoard($user);
                $user->connection = $connection;
                $this->users->push($user, $connection->id);
                $connection->send(json_encode([
                    'msg' => "onConnection",
                    'board' => $user->board->toArray(),
                    'id' => $user->id
                ]));
            };
        };

        $ws_worker->onMessage = function(TcpConnection $connection, $data) use ($ws_worker)
        {
            $msg = json_decode($data);

            switch ($msg->msg)
            {
                case "findRoom":
                    $user = $this->users->get($connection->id);
                    /**
                     * @var GameRoom $room
                     */
                    foreach ($this->rooms as $room) {
                        if ($room->containsUser($user->id)) {
                            break 2;
                        }
                    }
                    $gameRoom = GameHelper::findGameRoom($this->rooms);

                    $onFull = function () use ($gameRoom) {
                        foreach ($gameRoom->users as $_user) {
                            $_user->connection->send(json_encode([
                                'msg' => ServerMessage::ENEMY_FOUND,
                                'enemyId' => $gameRoom->user1->id === $_user->id ? $gameRoom->user2->id : $gameRoom->user1->id,
                                'walkingUserId' => $gameRoom->createdBy->id
                            ]));
                        }
                    };

                    if (isset($gameRoom)) {
                        $gameRoom->onFull = $onFull;
                        $gameRoom->addUser($user);
                    } else {
                        $gameRoom = new GameRoom($user);
                        $this->rooms->push($gameRoom);
                        $gameRoom->onFull = $onFull;
                    }
                    break;
                case "hit":
                    $row    = isset($msg->row)    ? $msg->row    : null;
                    $column = isset($msg->column) ? $msg->column : null;
                    $userId = isset($msg->userId) ? $msg->userId : null;

                    if (!isset($row) || !isset($column)) {
                        $connection->send(json_encode([ 'msg' => "You must set row and column" ]));
                        break;
                    }

                    if (!isset($userId)) {
                        $connection->send(json_encode([ 'msg' => 'You must set userId' ]));
                        break;
                    }

                    /**
                     * @var Player $user
                     */
                    $user = $this->users->get($userId);

                    if (!isset($user)) {
                        $connection->send(json_encode([ 'msg' => "User with id: $userId was not found" ]));
                        break;
                    }

                    if ($user->id === $connection->id) {
                        $connection->send(json_encode([ 'msg' => 'You can not hit yourself' ]));
                        break;
                    }

                    $gameRoom = null;
                    $keyRoom  = null;
                    /**
                     * @var GameRoom $room
                     */
                    foreach ($this->rooms as $key => $room) {
                        if ($room->containsUser($user->id) && $room->containsUser($connection->id)) {
                            $keyRoom = $key;
                            $gameRoom = $room;
                        }
                    }

                    if (!isset($gameRoom)) {
                        $connection->send(json_encode([ 'msg' => "Room with such players was not found" ]));
                        break;
                    }

                    if ($gameRoom->walkingUser->id !== $connection->id) {
                        $connection->send(json_encode([ 'msg' => "It's not your action" ]));
                        break;
                    }

                    $firedCell = $this->users->get($connection->id)->firingBoard->cells->at($row, $column);

                    if (!$this->hitCell($connection, $user, $row, $column, $firedCell, $keyRoom, $gameRoom)) {
                        break;
                    }

                    break;
                default:
                    $connection->send(json_encode([ 'msg' => "Action $data is not t supported" ]));
                    break;
            }
        };

        $ws_worker->onClose = function($connection)
        {
            echo "Connection closed";
            /**
             * @var GameRoom $room
             */
            foreach ($this->rooms as $key => $room) {
                if ($room->containsUser($connection->id)) {

                    foreach ($room->users as $user) {
                        $user->connection->close();
                        $this->users->remove($user->id);
                    }

                    $this->rooms->remove($key);
                }
            }
        };
    }

    /**
     * @param TcpConnection $connection
     * @param Player $userUnderAttack
     * @param integer $row
     * @param integer $column
     * @param Cell $firedCell
     * @param integer $keyRoom
     * @param GameRoom $gameRoom
     * @return bool
     */
    private function hitCell($connection, $userUnderAttack, $row, $column, $firedCell, $keyRoom, $gameRoom = null)
    {
        /**
         * @var CellList $cells
         */
        $cells = $userUnderAttack->board->cells;
        $cell  = $cells->at($row, $column);

        if (!isset($cell)) {
            $connection->send(json_encode([ 'msg' => "Cell at $row, $column was not found on enemy board" ]));
            return false;
        }

        if ($cell->isOccupied()) {
            $firedCell->occupationType = OccupationType::HIT;
            $userUnderAttack->connection->send(json_encode([
                'msg'    => ServerMessage::YOU_INJURED,
                'row'    => $row,
                'column' => $column
            ]));
            $connection->send(json_encode([
                'msg'    => ServerMessage::ENEMY_INJURED,
                'row'    => $row,
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
                    'msg' => ServerMessage::LOST
                ]));
                $connection->send(json_encode([
                    'msg' => ServerMessage::WIN
                ]));
                $connection->close();
                $userUnderAttack->connection->close();
                $this->rooms->remove($keyRoom);
            }
        } else {
            $connection->send(json_encode([
                'msg'    => ServerMessage::YOU_FALL,
                'row'    => $row,
                'column' => $column
            ]));
            $firedCell->occupationType = OccupationType::MISS;
            $userUnderAttack->connection->send(json_encode([
                'msg'    => ServerMessage::ENEMY_FALL,
                'row'    => $row,
                'column' => $column
            ]));
            $gameRoom->walkingUser = $userUnderAttack;
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