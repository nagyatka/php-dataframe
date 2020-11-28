<?php


namespace PHPDataFrame;


use InvalidArgumentException;
use PHPDataFrame\Exception\UnsupportedOperationException;

/**
 * Concatenates indices to a string. It is a helper method to select multiple columns from a Dataframe.
 *
 * @param array $indices List of indices.
 * @return string
 */
function inds($indices) {
    return cols($indices);
}

/**
 * Returns with the array of indices which included in the input string.
 *
 * @param string $indices_str String of column names.
 * @return array
 */
function get_inds($indices_str) {
    return get_cols($indices_str);
}

/**
 * Determines whether the input string is a indices string or not.
 *
 * @param string $str Input string.
 * @return bool
 */
function is_inds_str($str) {
    return is_cols_str($str);
}


class IndexLocation implements \ArrayAccess
{
    /**
     * @var DataFrame
     */
    private $df;

    /**
     * IndexLocation constructor.
     * @param DataFrame $df
     */
    public function __construct(DataFrame $df)
    {
        $this->df = $df;
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
        if(is_inds_str($offset)) {
            $indices = get_inds($offset);
            return count($indices) === count(array_intersect($indices, $this->df->getIndices()));
        }
        if(is_int($offset)) {
            return count($this->df->getIndices()) >= intval($offset);
        }
        return in_array($offset, $this->df->getIndices());
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
        if(is_inds_str($offset)) {
            $indices = get_inds($offset);
            if(is_int($indices[0])) {
                return $this->getRows(array_map(function ($x) {return intval($x);}, $indices));
            }
            else {
                return $this->getRows($indices);
            }
        }
        elseif (is_int($offset)) {
            $index = intval($offset);
            return Series::fromRow($this->df->values[$index], $index, $this->df->getColumnNames());
        }
        elseif (is_string($offset)) {
            $idx = array_search($offset, $this->df->getIndices());
            if($idx == false) {
                throw new InvalidArgumentException("Unknown index: " . $offset);
            }
            return Series::fromRow($this->df->values[$idx], $offset,  $this->df->getColumnNames());
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
     * @throws UnsupportedOperationException
     */
    public function offsetSet($offset, $value)
    {
        throw new UnsupportedOperationException("Series are immutable objects. Use drop method instead!");
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
        throw new UnsupportedOperationException("Series are immutable objects. Use drop method instead!");
    }

    public function getRows($indices) {
        $result = [];
        foreach ($indices as $index) {
            if(!is_int($index)) {
                $idx = array_search($index, $this->df->getIndices());
            }
            else {
                $idx = $index;
            }

            if($idx == false) {
                throw new InvalidArgumentException("Unknown index: " . $index);
            }
            $result[] = $this->df->values[$idx];
        }
        return $result;
    }
}