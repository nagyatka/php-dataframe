<?php


use PHPDataFrame\DataFrame;
use PHPUnit\Framework\TestCase;

class DataFrameInitTest extends TestCase
{
    public function testInitEmpty()
    {
        $this->expectException(InvalidArgumentException::class);
        $df = new DataFrame([]);
    }

    public function testInitEmptyWithColumns()
    {
        $df = new DataFrame([], ["a", "b", "c"]);
        $this->assertEquals(0, $df->shape[0]);
        $this->assertEquals(3, $df->shape[1]);
    }

    public function testInitEmptyWithIndices()
    {
        // TODO: how should I handle empty columns
        $this->assertEquals(0, 0);
    }


}
