<?php

namespace Vladimino\Discoverist\Core;

/**
 * Class Utils
 * @package Vladimino\Discoverist\Core
 */
class Utils
{
    /**
     * To sort array by specific key
     * @todo: rework and simplify this!
     *
     * @param array  $array
     * @param string $subKey
     * @param bool   $sortAscending
     */
    public static function subKeySort(&$array, $subKey = "id", $sortAscending = false)
    {
        $tempArray = [];
        if (count($array)) {
            $tempArray[key($array)] = array_shift($array);
        }

        foreach ($array as $key => $val) {
            $offset = 0;
            $found  = false;
            foreach ($tempArray as $tmp_key => $tmp_val) {
                if (!$found and strtolower($val[$subKey]) > strtolower($tmp_val[$subKey])) {
                    $tempArray = array_merge(
                        (array)array_slice($tempArray, 0, $offset),
                        [$key => $val],
                        array_slice($tempArray, $offset)
                    );
                    $found     = true;
                }
                $offset++;
            }
            if (!$found) {
                $tempArray = array_merge($tempArray, [$key => $val]);
            }
        }

        if ($sortAscending) {
            $array = array_reverse($tempArray);
        } else {
            $array = $tempArray;
        }
    }
}
