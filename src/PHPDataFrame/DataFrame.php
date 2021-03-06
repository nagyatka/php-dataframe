<?php


namespace PHPDataFrame;



use ArrayAccess;
use InvalidArgumentException;
use Iterator;
use PHPDataFrame\Exception\UnsupportedOperationException;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use function PHPSTORM_META\type;

/**
 * Concatenates column names to a string. It is a helper method to select multiple columns from a Dataframe.
 *
 * @param array $column_names List of column names.
 * @return string
 */
function cols($column_names) {
    return "[" . implode(";", $column_names) . "]";
}

/**
 * Returns with the array of column names which included in the input string.
 *
 * @param string $column_names_str String of column names.
 * @return array
 */
function get_cols($column_names_str) {
    return explode(";", substr($column_names_str, 1, strlen($column_names_str) - 2));
}

/**
 * Determines whether the input string is a column names string or not.
 *
 * @param string $str Input string.
 * @return bool
 */
function is_cols_str($str) {
    return substr($str, 0, 1) === "[" and substr($str, strlen($str) - 1, 1) === "]";
}

/**
 * @param $string
 * @param $length
 * @param string $dots
 * @return string
 */
function truncate($string, $length, $dots = "...") {
    return (strlen($string) > $length) ? substr($string, 0, $length - strlen($dots)) . $dots : str_pad($string, $length);
}

/**
 * Class DataFrame
 * @package PHPDataFrame
 */
class DataFrame implements ArrayAccess, Iterator
{
    /**
     * @var array
     */
    public $values;

    /**
     * @var array
     */
    public $shape;

    /**
     * @var IndexLocation
     */
    public $iloc;

    /**
     * @var array
     */
    private $columns;

    /**
     * @var array
     */
    private $indices;

    /**
     * @var int
     */
    private $cursor = 0;

    /**
     * PHPDataFrame constructor.
     *
     * @param array $values
     * @param array $columns
     * @param array $indices
     */
    public function __construct(array $values, array $columns=null, array $indices=null)
    {
        // Type checks
        if((count($values) < 1 || $values == null) && $columns == null) {
            throw new InvalidArgumentException("At least columns must be set if the values is empty.");
        }
        if($columns != null && !(Util::isIntArray($columns) || Util::isStringArray($columns))) {
            throw new InvalidArgumentException("The columns must be array of column name strings or integers.");
        }
        if($indices != null && !(Util::isIntArray($columns) || Util::isStringArray($columns))) {
            throw new InvalidArgumentException("The indices must be array of strings or integers.");
        }

        // If values empty, it will be initiated as an empty array
        if($values == null) {
            $values = [];
        }

        // Empty indices means that, the indices come from the input $values.
        if($indices == null) {
            $indices = array_keys($values);
        }

        $values = array_values($values);

        if(count($values) > 0 && $columns != null && count($columns) != count($values[0])) {
            throw new InvalidArgumentException("The length of column array has not the same length as input values: ".count($columns)."!=".count($values[0]));
        }

        if($columns == null) {
            $idx = array_keys($indices)[0];
            $columns = array_keys($values[$idx]);
        }
        else {
            $new_values = [];
            foreach ($values as $row) {
                $new_values[] = array_combine($columns, $row);
            }
            $values = $new_values;
        }

        if(count($indices) != count($values)) {
            throw new InvalidArgumentException("The length of index array has not the same length as input values: ".count($indices)."!=".count($values));
        }

        $this->values = $values;
        $this->columns = $columns;
        $this->indices = $indices;
        $this->shape = [count($indices), count($columns)];
        $this->iloc = new IndexLocation($this);
    }

    private function updateShape() {
        $this->shape = [count($this->indices), count($this->columns)];
    }

    private function updateIloc() {
        $this->iloc = new IndexLocation($this);
    }

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        if(is_cols_str($offset)) {
            $columns = get_cols($offset);
            return count($columns) === count(array_intersect($columns, $this->columns));
        }
        if(is_int($offset)) {
            return count($this->columns) >= intval($offset);
        }
        return in_array($offset, $this->columns);
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        if(is_cols_str($offset)) {
            $columns = get_cols($offset);
            if(is_numeric($columns[0])) {
                return $this->getColumns(array_map(function ($x) {return $this->columns[intval($x)];}, $columns));
            }
            else {
                return $this->getColumns($columns);
            }
        }
        elseif (is_int($offset)) {
            $column_name = $this->columns[$offset];
            return Series::fromColumn(array_column($this->values, $column_name), $column_name, $this->indices);
        }
        elseif (is_string($offset)) {
            if(!in_array($offset, $this->columns)) {
                throw new InvalidArgumentException("Missing column: $offset");
            }
            return Series::fromColumn(array_column($this->values, $offset), $offset, $this->indices);
        }
        else {
            throw new InvalidArgumentException("Unsupported key type");
        }
    }

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        if(is_array($value)) {
            if(count($value) != count($this->values)) {
                throw new InvalidArgumentException("The length of DataFrame and the length of new column are 
                not equal: " . count($value) . "!=" . count($this->values));
            }
            if(Util::isAssocArray($value)) {
                $value = array_values($value);
            }
        }
        else {
            $a_val = $value;
            $value = array_fill(0, count($this->values), $a_val);
        }

        for ($i = 0; $i < count($this->values); $i++) {
            $this->values[$i][$offset] = $value[$i];
        }
        $this->columns[] = $offset;

        $this->updateShape();
    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     * @throws UnsupportedOperationException
     */
    public function offsetUnset($offset)
    {
        throw new UnsupportedOperationException("DataFrames are immutable objects. Use drop method instead!");
    }

    /**
     * Returns with values of the selected column.
     *
     * @param string $column_name
     * @return Series
     */
    public function getColumn($column_name) {
        return Series::fromColumn(array_column($this->values, $column_name), $column_name, $this->indices);
    }

    /**
     * Returns with the sub DataFrame which contains only the selected columns.
     *
     * @param array $column_names
     * @return DataFrame
     */
    public function getColumns($column_names) {
        $columns_data = [];
        foreach ($column_names as $column_name) {
            $columns_data[$column_name] = array_column($this->values, $column_name);
        }

        $values = [];
        for ($i = 0; $i < count($columns_data[$column_names[0]]); $i++) {
            $row = [];
            foreach ($columns_data as $column_name => $data_col) {
                $row[$column_name] = $data_col[$i];
            }
            $values[] = $row;
        }

        return new DataFrame($values, $column_names, $this->indices);
    }

    /**
     * @return array
     */
    public function getIndices()
    {
        return $this->indices;
    }

    /**
     * @param array $indices
     */
    public function setIndices($indices) {
        if(count($indices) != count($this->indices)) {
            throw new InvalidArgumentException("Length of index array is wrong. ". count($indices).
                "!=".count($this->indices));
        }
        $this->indices = $indices;
        $this->updateIloc();
    }

    /**
     * Returns with the column names' array.
     *
     * @return array
     */
    public function getColumnNames() {
        return $this->columns;
    }

    /**
     *
     *
     * @param array $columns
     */
    public function setColumnNames(array $columns)
    {
        if(count($columns) != count($this->columns)) {
            throw new InvalidArgumentException("Length of column names array is wrong. ". count($columns).
                "!=".count($this->columns));
        }
        $this->columns = $columns;
        $this->updateIloc();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $result_str = "\n";

        # Determining length of one header element
        $min_length = 6;
        $max_length = 10;
        $length = max(max(array_map(function($x)use($min_length){max($min_length, strlen($x));}, $this->columns)), $max_length);
        $index_len = $length / 2;

        # Assembling header
        $header_str = str_repeat(" ", $index_len)."|".implode("|", array_map(function($x)use($length) {
            return truncate(strval($x), $length);}, $this->columns)) . "|\n";
        $result_str .= $header_str . str_repeat("=", strlen($header_str) - 1) . "\n";

        # Assembling data rows
        $vals = array_values($this->values);
        for($i = 0; $i < count($this->indices); $i++) {
            $result_str .= truncate(strval($this->indices[$i]), $index_len)."|".implode("|", array_map(function ($x) use($length) {
                return truncate(strval($x), $length);
            }, $vals[$i]))."|\n";
        }

        $result_str .= "Shape: " . $this->shape[0] . "x" . $this->shape[1] . "\n";
        return $result_str;
    }

    /**
     * Return the current element
     * @link https://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return Series::fromRow($this->values[$this->cursor], $this->indices[$this->cursor], $this->columns);
    }

    /**
     * Move forward to next element
     * @link https://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        ++$this->cursor;
    }

    /**
     * Return the key of the current element
     * @link https://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->indices[$this->cursor];
    }

    /**
     * Checks if current position is valid
     * @link https://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return $this->cursor >= 0 && $this->cursor < count($this->values);
    }

    /**
     * Rewind the Iterator to the first element
     * @link https://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->cursor = 0;
    }

    /**
     * Yields a {row index => row values: Series} generator.
     */
    public function iterrows() {
        for($i = 0; $i < count($this->values); $i++) {
            yield $this->indices[$i] => Series::fromRow($this->values[$i], $this->indices[$i], $this->columns);
        }
    }

    /**
     * Yields a {column name => column values: Series} generator.
     */
    public function itercols() {
        for($i = 0; $i < count($this->values); $i++) {
            yield $this->columns[$i] => $this->getColumn($this->columns[$i]);
        }
    }

    /**
     * Access a single value for a row/column label/index pair. If value parameter is null the function returns the
     * current value of the selected cell, otherwise the value passed in the parameter will be set at the cell.
     *
     * Notes:
     *  - In case of label duplication, always the first occurrence is used.
     *  - Numeric strings are treated as labels.
     *
     *
     * @param string|integer $row_id Row label or index
     * @param string|integer $col_id Column label or index
     * @param mixed|null $value The value to set.
     *
     * @return mixed|null
     */
    public function at($row_id, $col_id, $value = null) {
        if(is_string($row_id)) {
            $row_idx = array_search($row_id, $this->getIndices());
            if($row_idx === false) {
                throw new InvalidArgumentException("Unknown row label: " . $row_id);
            }
        }
        elseif (is_int($row_id)) {
            if($row_id >= count($this->getIndices())) {
                throw new InvalidArgumentException("Too big row index: " . $row_id);
            }
            $row_idx = $row_id;
        }
        else {
            throw new InvalidArgumentException("Unknown type of row");
        }

        if(is_string($col_id)) {
            if(!Util::isStringArray($this->getColumnNames())){
                throw new InvalidArgumentException("DataFrame has integer column ids, string label has given.");
            }

            if(!in_array($col_id, $this->getColumnNames())) {
                throw new InvalidArgumentException("Unknown column label: " . $col_id);
            }
            $col_idx = $col_id;
        }
        elseif (is_int($col_id)) {
            if($col_id >= count($this->getColumnNames())) {
                throw new InvalidArgumentException("Too big column index: " . $col_id);
            }
            $col_idx = $this->getColumnNames()[$col_id];
        }
        else {
            throw new InvalidArgumentException("Unknown type of column index: " . $col_id);
        }

        if($value == null) {
            return $this->values[$row_idx][$col_idx];
        }
        else {
            $this->values[$row_idx][$col_idx] = $value;
            return null;
        }
    }

    /**
     * Appends the input object to the DataFrame. Input object must be a DataFrame or a Series.
     *
     * @param DataFrame|Series $other
     * @param bool $ignore_index
     */
    public function append($other, $ignore_index = false) {
        if(is_array($other) || is_int($other) || is_string($other) || is_bool($other)) {
            throw new InvalidArgumentException("The input other object must be a DataFrame or a Series, array given.");
        }
        if(!Util::isDataFrame($other) && !Util::isSeries($other)) {
            throw new InvalidArgumentException("The input other object must be a DataFrame or a Series.");
        }

        if(Util::isSeries($other)) {
            /** @var Series $other */
            $other = new DataFrame($other->getValues(), $other->getIndices(), [$other->getName()]);
        }


        if(!Util::arraysEqual($this->getColumnNames(), $other->getColumnNames())) {
            throw new InvalidArgumentException("The input other object has different columns.");
        }

        if($ignore_index) {
            $this->values = array_merge($this->values, $other->values);
            $this->indices = array_merge($this->getIndices(), $other->getIndices());
            $this->updateShape();
            $this->updateIloc();
        }
        else {
            $same_indices = array_intersect($this->getIndices(), $other->getIndices());
            foreach ($same_indices as $same_index) {
                // Replacing for all positions
                $idx_positions = array_keys($this->getIndices(), $same_index);
                foreach ($idx_positions as $idx_position) {
                    $this->values[$idx_position] = $other->iloc[$same_index];
                }
            }

            $new_indices = array_diff($other->getIndices(), $this->getIndices());
            foreach ($new_indices as $new_index) {
                $this->values[] = $other->iloc[$new_index]->getValues();
            }

            $this->indices = array_merge($this->getIndices(), $new_indices);
            $this->updateShape();
            $this->updateIloc();
        }

    }

    /**
     * Applies the input callable on the rows or columns of the DataFrame depending on the axis parameter.
     * Axis 0 is the row and axis 1 is the column selector.
     *
     * @param $callable Callable function.
     * @param int $axis The selected axis. axis=0 row, axis=1 col.
     * @return array
     */
    public function apply($callable, $axis = 0) {
        if(!is_callable($callable)) {
            throw new InvalidArgumentException("Apply function's first parameter must be a callable.");
        }
        $result = [];
        if($axis === 0) {
            foreach ($this->iterrows() as $index => $row) {
                $result[$index] = $callable($row);
            }
        }
        else {
            foreach ($this->itercols() as $column_name => $col) {
                $result[$column_name] = $callable($col);
            }
        }
        return $result;
    }

    /**
     * Writes the DataFrame object to a csv file.
     *
     * @param string|null $path_to_file Destination file path. If it is null, it returns with the file content as a string.
     * @param bool $index If it is true, the index column is attached as the first column. Default: true
     * @param bool $header If it is true, it writes the column names. Default: true
     * @param string $sep Separator character.
     * @return bool|string
     * @throws Exception
     */
    public function to_csv($path_to_file=null, $index=true, $header=true, $sep=",") {
        return PD::df_to_file($this, "csv", $path_to_file, $index, $header, null, [], [], $sep);
    }

    /**
     * Writes the DataFrame object to a xls file.
     *
     * @param string|null $path_to_file Destination file path. If it is null, it returns with the file content as a string.
     * @param bool $index If it is true, the index column is attached as the first column. Default: true
     * @param bool $header If it is true, it writes the column names. Default: true
     * @param string $sheet_name Name of the sheet. Default: null
     * @param array $styles Styling array. Default: []; help: https://phpspreadsheet.readthedocs.io/en/latest/topics/recipes/#styles
     * @param array $formats Number formatting settings. Default: []; https://phpspreadsheet.readthedocs.io/en/latest/topics/recipes/#styles
     * @throws Exception
     */
    public function to_xls($path_to_file=null, $index=true, $header=true, $sheet_name=null, $styles=[], $formats=[]) {
        PD::df_to_file($this, "xls", $path_to_file, $index, $header, $sheet_name, $styles, $formats);
    }

    /**
     * Writes the DataFrame object to a xlsx file.
     *
     * @param string|null $path_to_file Destination file path. If it is null, it returns with the file content as a string.
     * @param bool $index If it is true, the index column is attached as the first column. Default: true
     * @param bool $header If it is true, it writes the column names. Default: true
     * @param string $sheet_name Name of the sheet. Default: null
     * @param array $styles Styling array. Default: []; help: https://phpspreadsheet.readthedocs.io/en/latest/topics/recipes/#styles
     * @param array $formats Number formatting settings. Default: []; https://phpspreadsheet.readthedocs.io/en/latest/topics/recipes/#styles
     * @throws Exception
     */
    public function to_xlsx($path_to_file=null, $index=true, $header=true, $sheet_name=null, $styles=[], $formats=[]) {
        PD::df_to_file($this, "xlsx", $path_to_file, $index, $header, $sheet_name, $styles, $formats);
    }
}