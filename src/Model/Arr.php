<?php

namespace Foolz\Foolframe\Model;


class Arr {

    /**
     * Checks if an array is associative
     *
     * @param $array
     * @return bool
     */
    public static function is_assoc($array) {
        return (bool) count(array_filter(array_keys($array), 'is_string'));
    }

}
