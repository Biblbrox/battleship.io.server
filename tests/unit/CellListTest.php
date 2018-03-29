<?php

use Battleship\App\Cell;
use Battleship\Utils\CellList;

class CellListTest extends \Codeception\Test\Unit
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

    public function testGetCellWithWrongCoordinates()
    {
        $cellList = new CellList();
        $cellList[] = new Cell(1, 1);

        $found = $cellList->at(2, 1);
        $this->assertEquals(null, $found);
    }

    public function testGetCellWithOutOfRangeCoordinates()
    {
        $cellList = new CellList();
        $cellList[] = new Cell(1, 1);

        $found = $cellList->at(200, 1);
        $this->assertEquals(null, $found);
    }
}