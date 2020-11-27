<?php


namespace PHPDataFrame;



use InvalidArgumentException;
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
    return (strlen($string) > $length) ? substr($string, 0, $length - strlen($dots)) . $dots : $string;
}



class DataFrame implements \ArrayAccess
{
    /**
     * @var array
     */
    public $values;

    /**
     * @var array
     */
    public $columns;

    /**
     * @var array
     */
    public $shape;

    /**
     * @var array
     */
    private $indices;

    /**
     * PHPDataFrame constructor.
     *
     * @param array $values
     * @param array $columns
     * @param array $indices
     */
    public function __construct(array $values, array $columns=null, array $indices=null)
    {
        if($indices == null) {
            $indices = array_keys($values);
        }
        else {
            $values = array_values($values);
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
        }

        $this->values = $values;
        $this->columns = $columns;
        $this->indices = $indices;
        $this->shape = [count($columns), count($indices)];
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
        return new Series(array_column($this->values, $column_name));
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


    public function __toString()
    {
        // TODO: finish print (basic version)
        // TODO: Add iterators (Iterator interface, itterows, itercols)
        $min_length = 6;
        $max_length = 10;
        $length = max(array_map(function($x)use($min_length){max($min_length, strlen($x));}, $this->columns));
        $result_str = "\n";

        $header_str = "|";

        return $result_str;
    }
}