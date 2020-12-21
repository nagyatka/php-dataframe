<?php


use PHPDataFrame\DataFrame;
use function PHPDataFrame\inds;
use PHPDataFrame\Series;
use PHPDataFrame\Util;
use PHPUnit\Framework\TestCase;

class DataFrameUpdateCellTest extends TestCase
{
    /**
     * @var DataFrame
     */
    private $df;

    protected function setUp(): void
    {
        $this->df = new DataFrame([
            [1,2,3],
            [4,5,6],
            [7,8,9]
        ], ["a", "b", "c"], ["e", "f", "g"]);
    }

    public function testGetByLabels() {
        $this->assertEquals(1, $this->df->at("e", "a"));
        $this->assertEquals(2, $this->df->at("e", "b"));
        $this->assertEquals(6, $this->df->at("f", "c"));
        $this->assertEquals(8, $this->df->at("g", "b"));
    }

    public function testGetByColIndices() {
        $this->assertEquals(1, $this->df->at("e", 0));
        $this->assertEquals(2, $this->df->at("e", 1));
        $this->assertEquals(6, $this->df->at("f", 2));
        $this->assertEquals(8, $this->df->at("g", 1));

        $this->assertEquals(1, $this->df->at(0, 0));
        $this->assertEquals(2, $this->df->at(0, 1));
        $this->assertEquals(6, $this->df->at(1, 2));
        $this->assertEquals(8, $this->df->at(2, 1));
    }

    public function testSetValue() {
        $this->df->at("e", "a", 10);
        $this->df->at(2, 2, 15);
        $this->assertEquals(15, $this->df->values[2]["c"]);
    }
}