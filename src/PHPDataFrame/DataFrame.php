<?php


namespace PHPDataFrame;



use InvalidArgumentException;

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
     * @param array $values
     * @param array $columns
     * @param array $indices
     */
    public function __construct(array $values, array $columns=null, array $indices=null)
    {
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
        // TODO: Implement offsetExists() method.
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
        // TODO: how to pass list in offset???
        if(is_array($offset)) {
            if(is_string($offset[0])) {
                // TODO: handling string list offset
            }
            else if(is_int($offset[0])) {
                // TODO: handling int list offset
            }
            else {
                throw new InvalidArgumentException("Only column names or column indices are allowed in column selection. The given value was: $offset");
            }
        }
        elseif (is_int($offset)) {
            $column_name = $this->columns[$offset];
            return Series::fromColumn(array_column($this->values, $column_name), $column_name, $this->indices);
        }
        elseif (is_string($offset)) {
            // TODO: handling int list offset
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
        // TODO: Implement offsetSet() method. ALLOWED!
    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }

    /**
     * Returns with values of the selected column.
     *
     * @param string $column_name
     * @return Series
     */
    private function getColumn($column_name) {
        return new Series(array_column($this->values, $column_name));
    }
}