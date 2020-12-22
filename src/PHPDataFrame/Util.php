<?php


namespace PHPDataFrame;

/**
 * Class Util
 *
 * @package PHPDataFrame
 */
class Util
{
    public static function isTypeArray(array $input, string $type_str) {
        foreach ($input as $item) {
            if(!gettype($item) === $type_str) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns true, if elements from the array are integer.
     *
     * @param array $input
     * @return bool
     */
    public static function isIntArray(array $input) {
        return self::isTypeArray($input, "integer");
    }

    /**
     * Returns true, if elements from the array are string.
     *
     * @param array $input
     * @return bool
     */
    public static function isStringArray(array $input) {
        return self::isTypeArray($input, "string");
    }

    /**
     * Returns true, if elements from the array are boolean.
     *
     * @param array $input
     * @return bool
     */
    public static function isBooleanArray(array $input) {
        return self::isTypeArray($input, "boolean");
    }

    /**
     * Returns true, if the input object is a Series.
     *
     * @param $input
     * @return bool
     */
    public static function isSeries($input) {
        return get_class($input) === Series::class;
    }

    /**
     * Returns true, if the input object is a DataFrame.
     *
     * @param $input
     * @return bool
     */
    public static function isDataFrame($input) {
        return get_class($input) === DataFrame::class;
    }

    /**
     * Compares the values of the arrays. Returns true, if the two arrays have same element values. Method does not
     * take into account the ordering of the elements.
     *
     * @param array $arr1 Array 1
     * @param array $arr2 Array 2
     * @return bool True, if the two input arrays are equals. False, otherwise.
     */
    public static function arraysEqual($arr1, $arr2) {
        $x = array_values($arr1);
        $y = array_values($arr2);

        sort($x);
        sort($y);

        return $x === $y;
    }
}