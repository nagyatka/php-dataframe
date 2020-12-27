<?php


use PHPDataFrame\DataFrame;
use PHPDataFrame\Series;
use PHPUnit\Framework\TestCase;

class DataFrameApplyTest extends TestCase
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

    public function testApply()
    {
        $this->df["d"] = $this->df->apply(function($row) {
            /** @var $row Series*/
            return array_sum($row->getValues());
        });
        $this->assertEquals(["e" => 6, "f" => 15, "g" => 24], $this->df["d"]->getValues());
    }
}
