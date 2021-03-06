<?php

declare(strict_types=1);

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
     * @var $context
     */

    /**
     * BattleshipServer constructor.
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->rooms = new ArrayCollection();

        $this->initWebSocket();
    }

    private function initWebSocket() : void
    {
        $ws_worker = new Worker("websocket://0.0.0.0:2346");

        $ws_worker->onConnect = function($connection) use ($ws_worker)
        {
            $connection->onWebSocketConnect = function(TcpConnection $connection) use ($ws_worker)
            {
                echo "New connection\n";
                $user = GameHelper::generateUser($connection);
                $this->users[$connection->id] = $user;
                $connection->send(json_encode([
                    'msg'   => "onConnection",
                    'board' => $user->board->toArray(),
                    'id'    => $user->id
                ]));
            };
        };

        $ws_worker->onMessage = function(TcpConnection $connection, $data) use ($ws_worker)
        {
            $msg = json_decode($data);

            if (!isset($msg)) {
                return;
            }

            switch ($msg->msg)
            {
                case ServerMessage::FIND_ROOM:
                    $user = $this->users[$connection->id];
                    foreach ($this->rooms as $room) {
                        if ($room->containsUser($user->id)) {
                            break 2;
                        }
                    }
                    $gameRoom = GameHelper::findGameRoom($this->rooms);

                    $onFull = function () use ($gameRoom) {
                        foreach ($gameRoom->users as $user) {
                            $user->connection->send(json_encode([
                                'msg' => ServerMessage::ENEMY_FOUND,
                                'enemyId' => $gameRoom->user1->id === $user->id ? $gameRoom->user2->id : $gameRoom->user1->id,
                                'walkingUserId' => $gameRoom->createdBy->id
                            ]));
                        }
                    };

                    if (isset($gameRoom)) {
                        $gameRoom->onFull = $onFull;
                        $gameRoom->addUser($user);
                    } else {
                        $gameRoom = new GameRoom($user);
                        $this->rooms[] = $gameRoom;
                        $gameRoom->onFull = $onFull;
                    }
                    break;
                case ServerMessage::HIT:
                    $row    = $msg->row ?? null;
                    $column = $msg->column ?? null;
                    $userId = $msg->userId ?? null;

                    if (!isset($row) || !isset($column)) {
                        $connection->send(json_encode([ 'msg' => "You must set row and column" ]));
                        break;
                    }

                    if (!isset($userId)) {
                        $connection->send(json_encode([ 'msg' => 'You must set userId' ]));
                        break;
                    }

                    $user = $this->users[$userId];

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
                    $this->rooms->forEach(function($room, $key) use (&$gameRoom, &$keyRoom, $user, $connection) {
                        if ($room->containsUser($user->id) && $room->containsUser($connection->id)) {
                            $keyRoom = $key;
                            $gameRoom = $room;
                        }
                    });

                    if (!isset($gameRoom)) {
                        $connection->send(json_encode([ 'msg' => "Room with such players was not found" ]));
                        break;
                    }

                    if ($gameRoom->walkingUser->id !== $connection->id) {
                        $connection->send(json_encode([ 'msg' => "It's not your action" ]));
                        break;
                    }

                    $firedCell = $this->users[$connection->id]->firingBoard->cells->at($row, $column);

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
            $this->rooms->forEach(function($room, $key) use ($connection) {
                if ($room->containsUser($connection->id)) {

                    $user1 = $room->user1;
                    $user2 = $room->user2;

                    if (isset($user1) && $user1->id !== $connection->id) {
                        $user1->connection->send(json_encode([
                            'msg' => ServerMessage::ENEMY_DISCONNECT
                        ]));
                    }
                    if (isset($user2) && $user2->id !== $connection->id) {
                        $user2->connection->send(json_encode([
                            'msg' => ServerMessage::ENEMY_DISCONNECT
                        ]));
                    }

                    foreach ($room->users as $user) {
                        $user->connection->close();
                        unset($this->users[$user->id]);
                    }

                    unset($this->rooms[$key]);
                }
            });
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
     * @throws \ReflectionException
     */
    private function hitCell($connection, $userUnderAttack, $row, $column, $firedCell, $keyRoom, $gameRoom = null) : bool
    {
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
            $attackedShip->coordinates->forEach(function($item) use($row, $column, $attackedShip) {
                if ($item->row == $row && $item->column == $column) {
                    $attackedShip->hits++;
                }
            });

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
                unset($this->rooms[$keyRoom]);
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
    public function run() : void
    {
        Worker::runAll();
    }
}