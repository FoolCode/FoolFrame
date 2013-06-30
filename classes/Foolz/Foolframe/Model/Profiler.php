<?php

namespace Foolz\Foolframe\Model;

use Foolz\Profiler\Profiler as P;

class Profiler
{
    /**
     * @var \Foolz\Profiler\Profiler
     */
    protected static $profiler;

    public static function forge(P $profiler)
    {
        static::$profiler = $profiler;
    }

    public static function mark($label)
    {
        static::$profiler->log($label);
    }

    public static function mark_memory($var = false, $name = 'PHP')
    {
        static::$profiler->logMem($name, $var);
    }

    public static function start($dbname, $sql)
    {
        static::$profiler->logStart($dbname, ['sql' => $sql]);
    }

    public static function stop($text)
    {
        static::$profiler->logStop($text);
    }
}
