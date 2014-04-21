<?php

namespace Foolz\Foolframe\Model;


class Util {

    /**
     * Checks if an array is associative
     * From http://stackoverflow.com/a/4254008/644504
     *
     * @param $array
     * @return bool
     */
    public static function isAssoc($array) {
        return (bool) count(array_filter(array_keys($array), 'is_string'));
    }

    /**
     * Returns a random string
     * From http://stackoverflow.com/a/4356295/644504
     *
     * @param int $length
     * @return string
     */
    public static function randomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
}
