<?php


use PHPDataFrame\DataFrame;
use PHPDataFrame\Series;
use PHPDataFrame\Util;
use PHPUnit\Framework\TestCase;

class DataFrameIteratorTest  extends TestCase
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

    public function testIteratorInterface() {
        $data = [
            [1,2,3],
            [4,5,6],
            [7,8,9]
        ];
        $indices = ["e", "f", "g"];

        $cntr = 1;
        /** @var Series $row */
        foreach ($this->df as $row) {
            $this->assertTrue(Util::isSeries($row));
            $series = Series::fromRow($data[$cntr - 1], $indices[$cntr - 1], ["a", "b", "c"]);
            $this->assertTrue($row->getValues() === $series->getValues());
            $cntr++;
        }
    }

    public function testIterrows() {
        $data = [
            [1,2,3],
            [4,5,6],
            [7,8,9]
        ];
        $indices = ["e", "f", "g"];

        $cntr = 1;
        /** @var Series $row */
        foreach ($this->df->iterrows() as $row) {
            $this->assertTrue(Util::isSeries($row));
            $series = Series::fromRow($data[$cntr - 1], $indices[$cntr - 1], ["a", "b", "c"]);
            $this->assertTrue($row->getValues() === $series->getValues());
            $cntr++;
        }
    }

    public function testItercols() {
        $data = [
            [1,2,3],
            [4,5,6],
            [7,8,9]
        ];
        $indices = ["e", "f", "g"];
        $columns = ["a", "b", "c"];

        $cntr = 1;
        /** @var Series $row */
        foreach ($this->df->itercols() as $row) {
            $this->assertTrue(Util::isSeries($row));
            $series = Series::fromColumn(array_column($data, $cntr - 1), $columns[$cntr - 1], $indices);
            $this->assertTrue($row->getValues() === $series->getValues());
            $cntr++;
        }
    }
}