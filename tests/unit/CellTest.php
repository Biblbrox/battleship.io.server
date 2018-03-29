<?php
declare(strict_types=1);
use Battleship\App\Cell;
use Battleship\Helper\OccupationType;

class CellTest extends \PHPUnit\Framework\TestCase
{

    private $cell;

    protected function setUp()
    {
        $this->cell = new Cell(1, 1);
    }

    protected function tearDown()
    {
        $this->cell = null;
    }

    public function testIsEmptyAfterCreate()
    {
        $this->assertEquals(OccupationType::EMPTY, $this->cell->occupationType);
    }
}