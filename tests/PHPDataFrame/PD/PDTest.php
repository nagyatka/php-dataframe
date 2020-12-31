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

    public function testSqlException()
    {
        $this->expectException(RuntimeException::class);
        $stmtMock = $this->createMock(\PDOStatement::class);
        $pdoMock = $this->createMock(\PDO::class);

        $stmtMock->method('execute')
            ->willReturn(false);
        $pdoMock->method('prepare')
            ->willReturn($stmtMock);

        $df = PD::read_sql("SELECT * FROM table_name", $pdoMock);
    }

    public function testSqlWithResults()
    {
        $stmtMock = $this->createMock(\PDOStatement::class);
        $pdoMock = $this->createMock(\PDO::class);

        $stmtMock->method('execute')
            ->willReturn(true);
        $stmtMock->method('fetchAll')
            ->willReturn([
                ["a" => 0, "b" => 1, "c" => 2],
                ["a" => 3, "b" => 4, "c" => 5],
                ["a" => 6, "b" => 7, "c" => 8],
            ]);
        $pdoMock->method('prepare')
            ->willReturn($stmtMock);

        $df = PD::read_sql("SELECT * FROM table_name", $pdoMock);

        $this->assertEquals($df->shape, [3, 3]);
        $this->assertEquals($df->at(0,0), 0);
        $this->assertEquals($df->at(1,1), 4);
        $this->assertEquals($df->at(2,2), 8);
    }

    public function testXlsxWithoutHeader()
    {
        try {
            $df = PD::read_xlsx("test_xls.xlsx");
            $this->assertEquals($df->shape, [3, 4]);
            $this->assertEquals($df->at(0,0), "e");
            $this->assertEquals($df->at(1,1), 4);
            $this->assertEquals($df->at(2,2), 8);
            $this->assertEquals(["idx", "a", "b", "c"],  $df->getColumnNames());
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            print($e->getTraceAsString());
        }
    }

    public function testXlsxWithoutHeaderIndexCol()
    {
        try {
            $df = PD::read_xlsx("test_xls.xlsx", null, 0);
            $this->assertEquals($df->shape, [3, 3]);
            $this->assertEquals($df->at(0,0), 1);
            $this->assertEquals($df->at(1,1), 5);
            $this->assertEquals($df->at(2,2), 9);
            $this->assertEquals(["a", "b", "c"],  $df->getColumnNames());
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            print($e->getTraceAsString());
        }
    }

    public function testXlsxWithHeaderIndexCol()
    {
        try {
            $df = PD::read_xlsx("test_xls.xlsx", ["x", "y", "z"], 0);
            $this->assertEquals($df->shape, [4, 3]);
            $this->assertEquals($df->at(0,0), "a");
            $this->assertEquals($df->at(1,1), 2);
            $this->assertEquals($df->at(2,2), 6);
            $this->assertEquals(["x", "y", "z"],  $df->getColumnNames());
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            print($e->getTraceAsString());
        }
    }
}
