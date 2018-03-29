<?php

use Battleship\App\GameRoom;
use Battleship\App\Player;
use Battleship\Helper\GameHelper;
use Battleship\Utils\ArrayCollection;

class GameHelperTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    
    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testRoomNullWhenRoomsListIsEmpty()
    {
        $room = GameHelper::findGameRoom([]);
        $this->assertEquals(null, $room);
    }

    public function testFindRoom()
    {
        $player1 = new Player(1);
        $player2 = new Player(2);
        $player3 = new Player(3);

        $room1 = new GameRoom($player1);
        $room2 = new GameRoom($player2);

        // Room 1 is full
        $room1->addUser($player3);

        $roomList = new ArrayCollection();
        $roomList[] = $room1;
        $roomList[] = $room2;

        $found = GameHelper::findGameRoom($roomList);
        $this->assertEquals($player2->id, $found->createdBy->id);
    }
}