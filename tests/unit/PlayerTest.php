<?php
declare(strict_types=1);

use Battleship\App\Coordinates;
use Battleship\App\Player;

class PlayerTest extends \Codeception\Test\Unit
{

    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testHasLostOnAllShipsDied()
    {
        $player = new Player(1);
        foreach ($player->ships as $ship) {
            foreach (range(0, 3) as $i) {
                $ship->hits++;
            }
        }

        $this->assertEquals($player->hasLost(), true);
    }

    public function testShipAtCoordExist()
    {
        $player = new Player(1);
        $player->ships[0]->coordinates[] = new Coordinates(1, 1);

        $found = $player->shipAt(1, 1);

        $this->assertEquals($found, $player->ships[0]);
    }

    public function testShipAtWrongCoords()
    {
        $player = new Player(1);
        $player->ships[0]->coordinates[] = new Coordinates(1, 1);

        $found = $player->shipAt(0, 1);

        $this->assertEquals(null, $found);
    }

    public function testShipWithOutOfRangeCoords()
    {
        $player = new Player(1);
        $player->ships[0]->coordinates[] = new Coordinates(1, 1);

        $this->tester->expectException(
            new InvalidArgumentException("Ships coordinates must be in range(0..9)"), function () use ($player) {
            $found = $player->shipAt(10, 1);
        });
    }
}