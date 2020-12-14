<?php


namespace PHPDataFrame;



use ArrayAccess;
use InvalidArgumentException;
use Iterator;
use PHPDataFrame\Exception\UnsupportedOperationException;

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
 * TODO list:
 *  - append rows (update shape)
 *  - update one value
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
            if(is_int($columns[0])) {
                return $this->getColumns(array_map(function ($x) {return intval($x);}, $columns));
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
        }
        else {
            $a_val = $value;
            $value = array_fill(0, count($this->values), $a_val);
        }

        for ($i = 0; $i < count($this->values); $i++) {
            $this->values[$offset] = $value[$i];
        }
        $this->columns[] = $offset;

        $this->shape = [count($this->indices), count($this->columns)];
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
    public function getIndices(): array
    {
        return $this->indices;
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
    public function setColumnNames(array $columns): void
    {
        if(count($columns) != count($this->columns)) {
            throw new InvalidArgumentException("Length of column names array is worng. ". count($columns).
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
}