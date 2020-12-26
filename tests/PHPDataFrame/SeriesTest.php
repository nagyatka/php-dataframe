<?php


use PHPDataFrame\Series;
use PHPUnit\Framework\TestCase;

class SeriesTest extends TestCase
{
    public function testFromArray()
    {
        $series = Series::fromArray([0,1,2]);
        $this->assertEquals([0,1,2], $series->getValues());
        $this->assertEquals(null, $series->getName());
    }

    public function testFromRow()
    {
        $series = Series::fromRow([0,1,2], 1, ["a", "b", "c"]);
        $this->assertEquals(["a" => 0, "b" => 1, "c" => 2], $series->getValues());
        $this->assertEquals(1, $series->getName());

        $series = Series::fromRow([0,1,2], null, ["a", "b", "c"]);
        $this->assertEquals(["a" => 0, "b" => 1, "c" => 2], $series->getValues());
        $this->assertEquals(null, $series->getName());
    }

    public function testFromCol()
    {
        $series = Series::fromColumn([0,1,2], "x", ["a", "b", "c"]);
        $this->assertEquals(["a" => 0, "b" => 1, "c" => 2], $series->getValues());
        $this->assertEquals("x", $series->getName());

        $series = Series::fromColumn([0,1,2], "x", null);
        $this->assertEquals([0 => 0, 1 => 1, 2 => 2], $series->getValues());
        $this->assertEquals("x", $series->getName());
    }

    public function testGet()
    {
        $series = Series::fromRow([0,1,2], 1, ["a", "b", "c"]);
        $this->assertEquals(0, $series["a"]);
        $this->assertEquals(1, $series["b"]);
        $this->assertEquals(2, $series["c"]);
        $this->assertEquals(0, $series[0]);
        $this->assertEquals(1, $series[1]);
        $this->assertEquals(2, $series[2]);
    }

    public function testIteration() {
        $cols = ["a", "b", "c"];
        $series = Series::fromRow([0,1,2], 1, $cols);
        foreach ($series as $key => $item) {
            $this->assertEquals($cols[$item], $key);
        }
    }
}
