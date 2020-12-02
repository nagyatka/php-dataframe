<?php


use function PHPDataFrame\cols;
use PHPDataFrame\DataFrame;
use PHPDataFrame\Series;
use PHPDataFrame\Util;
use PHPUnit\Framework\TestCase;

class DataFrameTest extends TestCase
{
    public function testInitEmpty()
    {
        $this->expectException(InvalidArgumentException::class);
        $df = new DataFrame([]);
    }

    public function testInitEmptyWithIndices()
    {
        $this->expectException(InvalidArgumentException::class);
        $df = new DataFrame([], null, [0,1,2]);
    }

    public function testInitEmptyWithColumns()
    {
        $df = new DataFrame([], ["a", "b", "c"]);
        $this->assertEquals(0, $df->shape[0]);
        $this->assertEquals(3, $df->shape[1]);
    }

    public function testInitWithoutColumns()
    {
        $df = new DataFrame([
            [1,2,3],
            [4,5,6]
        ]);
        $this->assertEquals(2, $df->shape[0]);
        $this->assertEquals(3, $df->shape[1]);
    }

    public function testInitWithColumns()
    {
        $df = new DataFrame([
            [1,2,3],
            [4,5,6]
        ], ["a", "b", "c"]);
        $this->assertEquals(2, $df->shape[0]);
        $this->assertEquals(3, $df->shape[1]);
    }

    public function testInitWithColumnsAndIndices()
    {
        $df = new DataFrame([
            [1,2,3],
            [4,5,6]
        ], ["a", "b", "c"], ["e", "f"]);
        $this->assertEquals(2, $df->shape[0]);
        $this->assertEquals(3, $df->shape[1]);
        $this->assertTrue($df->getIndices() === ["e", "f"]);
        $this->assertTrue($df->getColumnNames() === ["a", "b", "c"]);
    }

    public function testOffsetExists()
    {
        $df = new DataFrame([
            [1,2,3],
            [4,5,6]
        ], ["a", "b", "c"], ["e", "f"]);
        $this->assertTrue(isset($df["a"]));
        $this->assertTrue(isset($df["b"]));
        $this->assertTrue(isset($df["c"]));

        $this->assertTrue(isset($df[cols(["a"])]));
        $this->assertTrue(isset($df[cols(["a", "b"])]));

        $this->assertFalse(isset($df["e"]));
        $this->assertFalse(isset($df["f"]));

        $this->assertFalse(isset($df[cols(["a", "b", "e"])]));

        $this->assertFalse(isset($df[cols(["e", "g"])]));
    }

    public function testOffsetGetOneElement() {
        $df = new DataFrame([
            [1,2,3],
            [4,5,6]
        ], ["a", "b", "c"], ["e", "f"]);

        /** @var Series $col */
        $col = $df["a"];
        $this->assertTrue(Util::isSeries($col));
        $this->assertTrue($col->getValues() === ["e" => 1, "f" => 4]);

        $col = $df[1];
        $this->assertTrue(Util::isSeries($col));
        $this->assertTrue($col->getValues() === ["e" => 2, "f" => 5]);
    }

    public function testOffsetGetMoreElements() {
        $df = new DataFrame([
            [1,2,3],
            [4,5,6]
        ], ["a", "b", "c"], ["e", "f"]);

        /** @var Series $col */
        $sub_df = $df[cols(["a", "b"])];
        $this->assertTrue(Util::isDataFrame($sub_df));

        $this->assertEquals(2, $sub_df->shape[0]);
        $this->assertEquals(2, $sub_df->shape[1]);
        $this->assertTrue($sub_df->getIndices() === ["e", "f"]);
        $this->assertTrue($sub_df->getColumnNames() === ["a", "b"]);

        $this->assertTrue($sub_df->values[0] === ["a" => 1, "b" => 2]);
        $this->assertTrue($sub_df->values[1] === ["a" => 4, "b" => 5]);

    }
}
