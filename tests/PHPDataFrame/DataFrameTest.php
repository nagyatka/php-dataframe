<?php


use function PHPDataFrame\cols;
use PHPDataFrame\DataFrame;
use PHPUnit\Framework\TestCase;

class DataFrameTest extends TestCase
{

    public function testOffsetGet()
    {
        $df = new DataFrame([
            ["a" => "b", "b" => "c"],
            ["a" => "d", "b" => "e"],
        ]);

        print($df);

        $sub_df = $df[cols(["a", "b"])];

    }
}
