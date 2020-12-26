<?php


use PHPDataFrame\DataFrame;
use function PHPDataFrame\inds;
use PHPDataFrame\Series;
use PHPDataFrame\Util;
use PHPUnit\Framework\TestCase;

class DataFrameAppendTest extends TestCase
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

    public function testArrayType() {
        $this->expectException(InvalidArgumentException::class);
        $this->df1->append([1,2,3]);
    }

    public function testIntType() {
        $this->expectException(InvalidArgumentException::class);
        $this->df1->append(1);
    }

    public function testStrType() {
        $this->expectException(InvalidArgumentException::class);
        $this->df1->append("asd");
    }

    public function testBoolType() {
        $this->expectException(InvalidArgumentException::class);
        $this->df1->append(true);
    }

    public function testDifferentIndex() {
        $this->df2->setColumnNames(["a", "b", "c"]);
        $this->df1->append($this->df2);
        $this->assertEquals($this->df1->shape, [6, 3]);

        $this->assertEquals($this->df1->at(0,0), 1);
        $this->assertEquals($this->df1->at(1,1), 5);
        $this->assertEquals($this->df1->at(2,2), 9);
        $this->assertEquals($this->df1->at(3,0), 1);
        $this->assertEquals($this->df1->at(4,1), 5);
        $this->assertEquals($this->df1->at(5,2), 9);
    }

    public function testSameIndices() {
        $this->df1->append($this->df1);
        $this->assertEquals($this->df1->shape, [3, 3]);

        $this->assertEquals($this->df1->at(0,0), 1);
        $this->assertEquals($this->df1->at(1,1), 5);
        $this->assertEquals($this->df1->at(2,2), 9);
    }

    public function testIgnoredIndices() {
        $this->df1->append($this->df1, true);
        $this->assertEquals($this->df1->shape, [6, 3]);

        $this->assertEquals($this->df1->at(0,0), 1);
        $this->assertEquals($this->df1->at(1,1), 5);
        $this->assertEquals($this->df1->at(2,2), 9);
        $this->assertEquals($this->df1->at(3,0), 1);
        $this->assertEquals($this->df1->at(4,1), 5);
        $this->assertEquals($this->df1->at(5,2), 9);
    }
}