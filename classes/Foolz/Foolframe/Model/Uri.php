<?php

namespace Foolz\Foolframe\Model;

use Symfony\Component\HttpFoundation\Request;

class Uri extends Model {

    /**
     * @var Request
     */
    public $request;

    public function __construct(Context $context, Request $request)
    {
        parent::__construct($context);
        $this->request = $request;
    }

    public function base()
    {
        return $this->request->getUriForPath('/');
    }

    public function create($uri)
    {
        return $this->request->getUriForPath('/'.(is_array($uri) ? implode('/', $uri) : trim($uri, '/'))).'/';
    }

    public function main()
    {
        return $this->request->getBasePath();
    }

    public function string()
    {
        return $this->request->getUri();
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

        $temp = ''; // this should never be used before it's actually set
        foreach ($uri as $key => $item) {
            if ($key % 2) {
                $result[$temp] = $item;
            } else {
                $temp = $item;
            }
        }

        if ($allowed !== null) {
            $filtered = [];
            foreach ($allowed as $item) {
                $filtered[$item] = isset($result[$item]) ? $result[$item] : null;
            }

            $result = $filtered;
        }

        return $result;
    }

}