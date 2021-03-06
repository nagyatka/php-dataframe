<?php


namespace PHPDataFrame;


use ArrayAccess;
use InvalidArgumentException;
use Iterator;
use PHPDataFrame\Exception\UnsupportedOperationException;

/**
 * Class Series
 *
 * TODO: testing, add new operations.
 *
 * @package PHPDataFrame
 */
class Series implements ArrayAccess, Iterator
{
    const ROW_DATA = 0;
    const COLUMN_DATA = 1;

    const PRINT_MAX_LEN = 10;

    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $columns;

    /**
     * @var null
     */
    private $indices;

    /**
     * @var int
     */
    private $axis;

    /**
     * @var int
     */
    private $cursor = 0;

    /**
     * Series constructor.
     * @param array $data
     * @param null $columns
     * @param null $indices
     * @param int $axis
     */
    public function __construct(array $data, $axis=0, $columns = null, $indices = null)
    {
        // Presetting default values
        if($axis == Series::ROW_DATA && $columns == null) {
            if(count($data) < 1) {
                $columns = [];
            }
            else {
                $columns = range(0, count($data) - 1);
            }
        }
        if($axis == Series::ROW_DATA && $indices == null) {
            $indices = [];
        }

        if($axis == Series::COLUMN_DATA && $indices == null) {
            $indices = range(0, count($data) - 1);
        }
        if($axis == Series::COLUMN_DATA && $columns == null) {
            $indices = [];
        }


        // Checking provided values
        if($axis == Series::ROW_DATA && count($data) != count($columns)) {
            throw new InvalidArgumentException("Length of data and columns does not match: ".
                count($data) ."!=". count($columns));
        }

        if($axis == Series::COLUMN_DATA && count($data) != count($indices)) {
            throw new InvalidArgumentException("Length of data and indices does not match: ".
                count($data) ."!=". count($indices));
        }
        if(!in_array($axis, [Series::ROW_DATA, Series::COLUMN_DATA])) {
            throw new InvalidArgumentException("Axis must be 0 or 1.");
        }

        $this->data = $data;
        $this->columns = $columns;
        $this->indices = $indices;
        $this->axis = $axis;
    }

    public function offsetExists($offset)
    {
        if($this->axis == Series::ROW_DATA) {
            $idx = array_search($offset, $this->columns);
        }
        else {
            $idx = array_search($offset, $this->indices);
        }
        return isset($this->data[$idx]);
    }

    private static function getOffsetValue($needle, $haystack) {
        if(is_string($needle)) {
            $idx = array_search($needle, $haystack);
            if($idx === false) {
                throw new InvalidArgumentException("Unknown offset: " . $needle);
            }
            return $idx;
        }
        elseif (is_int($needle)) {
            return $needle;
        }
        else {
            throw new InvalidArgumentException("Unknown index type.");
        }

    }

    public function offsetGet($offset)
    {
        if($this->axis == Series::ROW_DATA) {
            $idx = self::getOffsetValue($offset, $this->columns);
        }
        else {
            $idx = self::getOffsetValue($offset, $this->indices);
        }
        return $this->data[$idx];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws UnsupportedOperationException
     */
    public function offsetSet($offset, $value)
    {
       throw new UnsupportedOperationException("Series are immutable objects. Use drop method instead!");
    }

    /**
     * @param mixed $offset
     * @throws UnsupportedOperationException
     */
    public function offsetUnset($offset)
    {
        throw new UnsupportedOperationException("Series are immutable objects. Use drop method instead!");
    }

    public static function fromArray(array $data) {
        return new Series(array_values($data), Series::ROW_DATA);
    }

    public static function fromColumn(array $data, $column_name, $indices) {
        return new Series(array_values($data), Series::COLUMN_DATA, [$column_name], $indices);
    }

    public static function fromRow(array $data, $index, $columns) {
        return new Series(array_values($data), Series::ROW_DATA, $columns, [$index]);
    }

    private static function  __getDataStr($keys, $data) {
        $len = count($data);
        $data_elements = [];
        for ($i = 0; $i < min(Series::PRINT_MAX_LEN, $len); ++$i) {
            $data_elements[] = "\t" . $keys[$i] . ": " . $data[$i] . ",\n";
        }

        if($len > Series::PRINT_MAX_LEN) {
            $data_elements[] = "...\n";
            $data_elements [] = "\t" . end($keys) . ": " . end($data) . ",\n";
        }
        return implode("", $data_elements);
    }

    public function __toString()
    {
        $len = count($this->data);
        if($this->axis == Series::ROW_DATA) {
            $data_str = self::__getDataStr($this->columns, $this->data);
            if(count($this->indices) < 1) {
                $index = "null";
            }
            else {
                $index = $this->indices[0];
            }
            return "Series(Index=".$index.", Length=".$len."){[\n$data_str]}";
        }
        else {
            $data_str = self::__getDataStr($this->indices, $this->data);
            return "Series(Name=".$this->columns[0].", length=".$len."){[\n$data_str]}";
        }
    }

    public function getValues() {
        return $this->axis == Series::ROW_DATA ?
            array_combine($this->columns, $this->data):
            array_combine($this->indices, $this->data);
    }

    public function getIndices() {
        return $this->axis == Series::ROW_DATA ? $this->columns : $this->indices;
    }

    public function getName() {
        if($this->axis == Series::ROW_DATA) {
            if(count($this->indices) < 1) {
                return null;
            }
            return  $this->indices[0];
        }
        else {
            if(count($this->columns) < 1) {
                return null;
            }
            return  $this->columns[0];
        }
    }

    /**
     * Return the current element
     * @link https://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return $this->data[$this->cursor];
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
        if($this->axis == Series::ROW_DATA) {
            return $this->columns[$this->cursor];
        }
        else {
            return $this->indices[$this->cursor];
        }
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
        return $this->cursor >= 0 && $this->cursor < count($this->data);
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
}









