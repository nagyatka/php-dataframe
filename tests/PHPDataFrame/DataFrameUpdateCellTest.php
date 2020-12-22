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
    private $df1;

    /**
     * @var DataFrame
     */
    private $df2;



    protected function setUp(): void
    {
        $this->df1 = new DataFrame([
            [1,2,3],
            [4,5,6],
            [7,8,9]
        ], ["a", "b", "c"], ["e", "f", "g"]);

        $this->df2 = new DataFrame([
            [1,2,3],
            [4,5,6],
            [7,8,9]
        ]);
    }

    public function testGetByLabels() {
        $this->assertEquals(1, $this->df1->at("e", "a"));
        $this->assertEquals(2, $this->df1->at("e", "b"));
        $this->assertEquals(6, $this->df1->at("f", "c"));
        $this->assertEquals(8, $this->df1->at("g", "b"));
    }

    public function testGetByIndices() {

        $this->assertEquals(1, $this->df1->at(0, "a"));
        $this->assertEquals(2, $this->df1->at(0, "b"));
        $this->assertEquals(6, $this->df1->at(1, "c"));
        $this->assertEquals(8, $this->df1->at(2, "b"));

        $this->assertEquals(1, $this->df1->at("e", 0));
        $this->assertEquals(2, $this->df1->at("e", 1));
        $this->assertEquals(6, $this->df1->at("f", 2));
        $this->assertEquals(8, $this->df1->at("g", 1));

        $this->assertEquals(1, $this->df1->at(0, 0));
        $this->assertEquals(2, $this->df1->at(0, 1));
        $this->assertEquals(6, $this->df1->at(1, 2));
        $this->assertEquals(8, $this->df1->at(2, 1));

        $this->assertEquals(1, $this->df2->at(0, 0));
        $this->assertEquals(2, $this->df2->at(0, 1));
        $this->assertEquals(6, $this->df2->at(1, 2));
        $this->assertEquals(8, $this->df2->at(2, 1));
    }

    public function testSetValue() {
        $this->df1->at("e", "a", 10);
        $this->df1->at(2, 2, 15);
        $this->assertEquals(15, $this->df1->values[2]["c"]);
    }

    public function testMissingRowLabel() {
        $this->expectException(InvalidArgumentException::class);
        $this->df1->at("t", "a");
    }

    public function testMissingColumnLabel() {
        $this->expectException(InvalidArgumentException::class);
        $this->df1->at("e", "t");
    }

    public function testMissingRowIndex() {
        $this->expectException(InvalidArgumentException::class);
        $this->df1->at(4, "t");
    }

    public function testMissingColumnIndex() {
        $this->expectException(InvalidArgumentException::class);
        $this->df1->at("e", 10);
    }

    public function testWrongColumnType() {
        $this->expectException(InvalidArgumentException::class);
        $this->df1->at("e", true);
    }

    public function testWrongRowType() {
        $this->expectException(InvalidArgumentException::class);
        $this->df1->at([1,2], "a");
    }
}