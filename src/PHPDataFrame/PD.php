<?php


namespace PHPDataFrame;

use PDO;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class PD
{
    /**
     * Loads the content of the csv file in a DataFrame object.
     *
     * @param string $filepath_or_buffer Path to csv file.
     * @param string $sep Value separator in the csv file.
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

    /**
     * Loads the content of the xls file in a DataFrame object.
     *
     * @param string $filepath_or_buffer Path to xls file.
     * @param array|null $header_names Array of column names.
     * @param int|bool $index_col Number of the index column. Set to false if the csv file does not contain index column.
     * @return DataFrame
     * @throws Exception
     */
    public static function read_xls($filepath_or_buffer, $header_names=null, $index_col=false) {
        $reader = new Xls();
        return self::processObj($reader->load($filepath_or_buffer), $header_names, $index_col);
    }

    /**
     * Loads the content of the xlsx file in a DataFrame object.
     *
     * @param $filepath_or_buffer
     * @param array|null $header_names
     * @param int|bool $index_col Number of the index column. Set to false if the csv file does not contain index column.
     * @return DataFrame
     * @throws Exception
     */
    public static function read_xlsx($filepath_or_buffer, $header_names=null, $index_col=false) {
        $reader = new Xlsx();
        return self::processObj($reader->load($filepath_or_buffer), $header_names, $index_col);
    }

    /**
     * @param string $sql
     * @param PDO $pdo
     * @param array $parameters
     * @param array|null $header_names
     * @param int|bool $index_col Number of the index column. Set to false if the csv file does not contain index column.
     * @return DataFrame
     */
    public static function read_sql($sql, $pdo, $parameters = [], $header_names=null, $index_col=false) {
        $stmt = $pdo->prepare($sql);
        foreach ($parameters as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        if (!$stmt->execute()) {
            throw new \RuntimeException("Error occurred during the execution of sql query. PDO error code: ".$pdo->errorCode());
        }

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $columns = $header_names == null ? array_keys($data[0]) : $header_names;

        if($index_col !== false) {
            $indices = [];
        }

        return new DataFrame($data, $columns);
    }

    /**
     * Processes the input Spreadsheet object and converts it into a DataFrame object.
     *
     * @param Spreadsheet $objWorksheet
     * @param array|null $header_names
     * @param int|bool $index_col Number of the index column. Set to false if the csv file does not contain index column.
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
            $columns = array_filter($columns, function ($values){return !is_null($values);});
            $start_row = 2;
        }

        if($index_col !== false && $header_names === null) {
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
                $row_values = array_filter($row_values, function ($values){return !is_null($values);});
                if($index_col !== false) {
                    $indices[] = $row_values[$index_col];
                    array_splice($row_values, $index_col, 1);
                }
                $data[] = array_combine($columns, $row_values);
            }
        }
        return new DataFrame($data, $columns, $indices);
    }

    /**
     * @param DataFrame $df
     * @param string $mode
     * @param null $path_to_file
     * @param bool $index
     * @param bool $header
     * @param string $sheet_name
     * @param array $styles
     * @param array $formats
     * @param string $sep
     * @return bool|string
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public static function df_to_file($df, $mode, $path_to_file=null, $index=true, $header=true,
                                      $sheet_name=null, $styles=[], $formats=[], $sep=",") {
        $xlsArray = $df->values;

        if($index) {
            $columns = array_merge(["idx"], $df->getColumnNames());
            $indices = $df->getIndices();
            for($i = 0; $i < count($xlsArray); $i++) {
                $xlsArray[$i] = array_merge(["idx" => $indices[0]], $xlsArray[$i]);
            }
        }
        else {
            $columns = $df->getColumnNames();
        }

        if($header) {
            array_unshift($xlsArray, $columns);
        }

        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->setTitle($sheet_name == '' ? 'Sheet 1' : $sheet_name);

        $worksheet->fromArray($xlsArray);

        foreach ($styles as $selectedCells => $styleFormatArray) {
            $worksheet->getStyle($selectedCells)->applyFromArray($styleFormatArray);
        }

        foreach ($formats as $selectedCells => $formatCode) {
            $worksheet->getStyle($selectedCells)->getNumberFormat()->setFormatCode($formatCode);
        }

        switch ($mode) {
            case "xls":
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
                break;
            case "xlsx":
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                break;
            case "csv":
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
                $writer->setSheetIndex(0);
                $writer->setDelimiter($sep);
                break;
            default:
                throw new \InvalidArgumentException("Unknown mode: " . $mode);
        }
        if($path_to_file == null) {
            ob_start();
            $writer->save('php://output');
            $output = ob_get_clean();
            return $output;
        }
        else {
            $writer->save($path_to_file);
            return true;
        }
    }
}