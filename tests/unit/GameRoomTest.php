<?php
declare(strict_types=1);
use Battleship\App\GameRoom;

class GameRoomTest extends \PHPUnit\Framework\TestCase
{
    public function testCannotAddUserWhenIsFull()
    {
        $user1 = new \Battleship\App\Player(1);
        $user2 = new \Battleship\App\Player(2);

        $gameRoom = new GameRoom($user1);
        $gameRoom->addUser($user2);

        $anotherUser = new \Battleship\App\Player(3);
        $gameRoom->addUser($anotherUser);

        $this->assertNotEquals($gameRoom->user2->id, $anotherUser->id);
    }

    public function testUser2EqualWithAdded()
    {
        $user1 = new \Battleship\App\Player(1);
        $user2 = new \Battleship\App\Player(2);

        $gameRoom = new GameRoom($user1);
        $gameRoom->addUser($user2);

        $this->assertEquals($gameRoom->user2->id, $user2->id);
    }

    public function testCreatedByEqualWithUser()
    {
         $user1 = new \Battleship\App\Player(1);

         $gameRoom = new GameRoom($user1);

         $this->assertEquals($gameRoom->createdBy->id, $user1->id);
    }

    public function testUserCreatorIsWalking()
    {
        $user1 = new \Battleship\App\Player(1);
        $user2 = new \Battleship\App\Player(2);

        $gameRoom = new GameRoom($user1);
        $gameRoom->addUser($user2);

        $this->assertEquals($gameRoom->walkingUser->id, $user1->id);
    }

    public function testRoomIsFullWhenTwoPlayers()
    {
        $user1 = new \Battleship\App\Player(1);
        $user2 = new \Battleship\App\Player(2);

        $gameRoom = new GameRoom($user1);
        $gameRoom->addUser($user2);

        $this->assertEquals(true, $gameRoom->isFull());
    }
}