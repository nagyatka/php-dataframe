<?php


use PHPDataFrame\PD;
use PHPDataFrame\Series;
use PHPUnit\Framework\TestCase;

class PDTest extends TestCase
{
    public function testCsvWithHeader()
    {
        try {
            $df = PD::read_csv('test_with_header.csv');
            $this->assertEquals($df->shape, [3, 3]);
            $this->assertEquals($df->at(0,0), 1);
            $this->assertEquals($df->at(1,1), 5);
            $this->assertEquals($df->at(2,2), 9);
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            print($e->getTraceAsString());
        }
    }

    public function testCsvWithHeaderIndices()
    {
        try {
            $df = PD::read_csv('test_with_header_indices.csv', ",", null, 0);
            $this->assertEquals($df->shape, [3, 3]);
            $this->assertEquals($df->at(0,0), 1);
            $this->assertEquals($df->at(1,1), 5);
            $this->assertEquals($df->at(2,2), 9);
            $this->assertEquals(["e", "f", "g"],  $df->getIndices());
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            print($e->getTraceAsString());
        }
    }

    public function testCsvWithoutHeader()
    {
        try {
            $df = PD::read_csv("test_without_header.csv", ",", ["x", "y", "z"]);
            $this->assertEquals($df->shape, [3, 3]);
            $this->assertEquals($df->at(0,0), 1);
            $this->assertEquals($df->at(1,1), 5);
            $this->assertEquals($df->at(2,2), 9);
            $this->assertEquals(["x", "y", "z"],  $df->getColumnNames());
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            print($e->getTraceAsString());
        }
    }
}
