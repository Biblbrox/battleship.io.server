<?php


class ShipTest extends \PHPUnit\Framework\TestCase
{
    public function testDeadAfterShots()
    {
        $battleship = new Battleship\App\Ship\Battleship();
        $cruiser    = new \Battleship\App\Ship\Cruiser();
        $destroyer  = new \Battleship\App\Ship\Destroyer();
        $submarine  = new \Battleship\App\Ship\Submarine();

        foreach (range(0, 3) as $i) {
            $battleship->hits++;
            if ($i < 3) {
                $cruiser->hits++;
            }
            if ($i < 2) {
                $destroyer->hits++;
            }
            if ($i < 1) {
                $submarine->hits++;
            }
        }

        $this->assertEquals($battleship->isDead(), true);
        $this->assertEquals($cruiser->isDead(), true);
        $this->assertEquals($destroyer->isDead(), true);
        $this->assertEquals($submarine->isDead(), true);
    }
}