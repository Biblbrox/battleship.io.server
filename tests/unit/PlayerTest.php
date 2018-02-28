<?php

use Battleship\App\Player;

class PlayerTest extends \PHPUnit\Framework\TestCase
{
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
}