<?php


namespace PHPDataFrame;


use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class PD
{
    /**
     * Loads the content of the csv file in a DataFrame object.
     *
     * @param string $filepath_or_buffer Path to csv file
     * @param string $sep Value separator in the csv file
     * @param array|null $header_names Array of column names.
     * @param int|bool $index_col Number of the index column. Set to false if the csv file does not contain index column.
     * @return DataFrame
     * @throws Exception
     */
    public static function read_csv($filepath_or_buffer, $sep=",", $header_names=null, $index_col=false) {
        $reader = new Csv();
        $reader->setDelimiter($sep);
        $reader->setSheetIndex(0);
        return self::processObj($reader->load($filepath_or_buffer), $header_names, $index_col);
    }

    public static function read_xls($file_path) {

    }

    public static function read_sql($file_path) {

    }

    /**
     * Processes the input Spreadsheet object and converts it into a DataFrame object.
     *
     * @param Spreadsheet $objWorksheet
     * @param array|null $header_names
     * @param bool $index_col
     * @return DataFrame
     * @throws Exception
     */
    private static function processObj($objWorksheet, $header_names=null, $index_col=false) {
        $objWorksheet   = $objWorksheet->setActiveSheetIndex(0);
        $highestRow     = $objWorksheet->getHighestRow();
        $highestColumn  = $objWorksheet->getHighestColumn();

        if($header_names != null) {
            $columns = $header_names;
            $start_row = 1;
        } else {
            $columns  = array_values($objWorksheet->rangeToArray(
                'A1:'.$highestColumn.'1', null, true,
                true, true)[1]);
            $start_row = 2;
        }

        if($index_col !== false) {
            array_splice($columns, $index_col, 1);
        }

        // The numbering of the rows starts from 1 (not 0).
        $data = [];
        $indices = [];
        for ($row = $start_row; $row <= $highestRow; ++$row) {
            $dataRow = $objWorksheet->rangeToArray('A'.$row.':'.$highestColumn.$row,null,
                true, true, true);
            if ((isset($dataRow[$row]['A'])) && ($dataRow[$row]['A'] > '')) {
                $row_values =  array_values($dataRow[$row]);
                if($index_col !== false) {
                    $indices[] = $row_values[$index_col];
                    array_splice($row_values, $index_col, 1);
                }
                $data[] = array_combine($columns, $row_values);
            }
        }
        // TODO: indices
        return new DataFrame($data, $columns, $indices);
    }
}