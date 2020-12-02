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
}