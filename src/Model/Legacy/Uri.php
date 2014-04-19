<?php

namespace Foolz\Foolframe\Model\Legacy;

use Symfony\Component\HttpFoundation\Request;

class Uri
{

    /**
     * @var null|Request
     */
    public static $request = null;

    public static function setRequest(Request $request)
    {
        static::$request = $request;
    }

    public static function base()
    {
        return static::$request->getUriForPath('/');
    }

    public static function create($uri)
    {
        return static::$request->getUriForPath('/'.(is_array($uri) ? implode('/', $uri) : trim($uri, '/'))).'/';
    }

    public static function main()
    {
        return static::$request->getBasePath() . static::$request->getPathInfo();
    }

    public static function string()
    {
        return static::$request->getUri();
    }

    public static function uri_to_assoc($uri, $index = 0, $allowed = null)
    {
        if (is_string($uri)) {
            $uri = explode('/', $uri);
        }

        for ($i = 0; $i < $index; $i++) {
            array_shift($uri);
        }

        // reorder the keys
        $uri = array_values($uri);
        $result = array();

        foreach ($uri as $key => $item) {
            if ($key % 2) {
                $result[$temp] = $item;
            } else {
                $temp = $item;
            }
        }

        if ($allowed !== null) {
            foreach ($allowed as $item) {
                $filtered[$item] = isset($result[$item]) ? $result[$item] : null;
            }

            $result = $filtered;
        }

        return $result;
    }
}
