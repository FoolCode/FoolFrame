<?php

namespace Foolz\FoolFrame\Model;

class Autoloader extends Model
{
    /**
     * @var array
     */
    protected $classmap = [];

    /**
     * Registers the autoloader to spl_autoload_register
     */
    public function register()
    {
        spl_autoload_register([$this, 'classLoader'], true, true);
    }

    /**
     * Unregisters the autoloader from spl_autoload_register
     */
    public function unregister()
    {
        spl_autoload_unregister([$this, 'classLoader']);
    }

    /**
     * Loads the class (for spl_autoload_register)
     *
     * @param $class
     * @return bool
     */
    public function classLoader($class)
    {
        if (!isset($this->classmap[$class])) {
            return false;
        }

        require_once $this->classmap[$class];
        return true;
    }

    /**
     * Add the path to a single class
     *
     * @param $class
     * @param $path
     */
    public function addClass($class, $path)
    {
        $this->classmap[$class] = ltrim($path, '\\');
    }

    /**
     * Add a classmap
     *
     * @param $arr
     */
    public function addClassMap($arr)
    {
        foreach($arr as $class => $path) {
            $this->addClass($class, $path);
        }
    }
}
