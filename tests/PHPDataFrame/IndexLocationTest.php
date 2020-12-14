<?php


use PHPDataFrame\DataFrame;
use function PHPDataFrame\inds;
use PHPDataFrame\Series;
use PHPDataFrame\Util;
use PHPUnit\Framework\TestCase;

class IndexLocationTest extends TestCase
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

    public function testUndefinedIndexLabel() {
        $this->expectException(InvalidArgumentException::class);
        $this->df->iloc["b"];
    }

    public function testUndefinedIndexNum() {
        $this->expectException(InvalidArgumentException::class);
        $this->df->iloc[10];
    }

    public function testGetSingleLabel() {
        /** @var Series $result */
        $result = $this->df->iloc["f"];
        $this->assertTrue(Util::isSeries($result));
        $this->assertTrue($result->getValues() === ["a" => 4, "b" => 5, "c" => 6]);
    }

    public function testGetSingleIndex() {
        /** @var Series $result */
        $result = $this->df->iloc[1];
        $this->assertTrue(Util::isSeries($result));
        $this->assertTrue($result->getValues() === ["a" => 4, "b" => 5, "c" => 6]);
    }

    public function testGetMultipleLabel() {
        /** @var DataFrame $result */
        $result = $this->df->iloc[inds(["e", "g"])];
        $this->assertTrue(Util::isDataFrame($result));

        $this->assertEquals(2, $result->shape[0]);
        $this->assertEquals(3, $result->shape[1]);
        $this->assertTrue($result->getIndices() === ["e", "g"]);
        $this->assertTrue($result->getColumnNames() === ["a", "b", "c"]);

        $this->assertTrue($result->values[0] === ["a" => 1, "b" => 2, "c" => 3]);
        $this->assertTrue($result->values[1] === ["a" => 7, "b" => 8, "c" => 9]);
    }

    public function testGetMultipleIndices() {
        /** @var DataFrame $result */
        $result = $this->df->iloc[inds([0, 2])];
        $this->assertTrue(Util::isDataFrame($result));

        $this->assertEquals(2, $result->shape[0]);
        $this->assertEquals(3, $result->shape[1]);
        $this->assertTrue($result->getIndices() === ["e", "g"]);
        $this->assertTrue($result->getColumnNames() === ["a", "b", "c"]);

        $this->assertTrue($result->values[0] === ["a" => 1, "b" => 2, "c" => 3]);
        $this->assertTrue($result->values[1] === ["a" => 7, "b" => 8, "c" => 9]);
    }
}