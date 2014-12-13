<?php

namespace Foolz\Foolframe\Model;


class Config extends Model
{
    /**
     * Array of information for each package with configurations
     *
     * @var  array
     */
    public $packages = [];

    /**
     * Changes the values of the configuration
     *
     * @param  string $package_name  The name of the package (use vendor/package format)
     * @param  string $file          The filename where the config is located (without extension)
     * @param  string $key           The dotted key, each token is an array key
     * @param  string $value         The value to set
     *
     * @throws  \OutOfBoundsException  In case the package wasn't set
     */
    public function set($package_name, $file, $key, $value)
    {
        $this->load($package_name, $file);

        static::arrSet($this->packages[$package_name]['data'][$file], $key, $value);
    }

    /**
     * Set an array item (dot-notated) to the value.
     *
     * From FuelPHP codebase app/core/classes/arr.php
     *
     * @param   array $array  The array to insert it into
     * @param   mixed $key    The dot-notated key to set or array of keys
     * @param   mixed $value  The value
     * @return  void
     */
    public static function arrSet(&$array, $key, $value = null)
    {
        if (is_null($key)) {
            $array = $value;
            return;
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                static::arrSet($array, $k, $v);
            }
        } else {
            $keys = explode('.', $key);

            while (count($keys) > 1) {
                $key = array_shift($keys);

                if (!isset($array[$key]) or !is_array($array[$key])) {
                    $array[$key] = array();
                }

                $array =& $array[$key];
            }

            $array[array_shift($keys)] = $value;
        }
    }

    /**
     * Save the configuration array (it will be saved in the environment folder)
     *
     * @param  string $package_name  The name of the package (vendor/package format)
     * @param  string $file          The filename where the config is located (without extension)
     *
     * @throws  \OutOfBoundsException  In case the package wasn't set
     */
    public function save($package_name, $file)
    {
        $this->load($package_name, $file);

        $path = VAPPPATH . $package_name . '/' . $this->packages[$package_name]['config_dir'] . '/';

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        static::saveArrayToFile($path . $file . '.php', $this->packages[$package_name]['data'][$file]);
    }

    /**
     * Saves an array to a PHP file with a return statement
     *
     * @param   string $path   The target path
     * @param   array $array  The array to save
     */
    public static function saveArrayToFile($path, $array)
    {
        $content = "<?php \n" .
            "return " . var_export($array, true) . ';';

        file_put_contents($path, $content);
    }

    /**
     * Remove a package from the array of available configs
     *
     * @param  string $package_name  The name of the package (use vendor/package format)
     *
     * @return  \Foolz\Foolframe\Model\Config  The current object
     */
    public function removePackage($package_name)
    {
        unset($this->packages[$package_name]);
    }

    /**
     * Get an element from the config file
     *
     * @param  string $package_name  The name of the package (use vendor/package format)
     * @param  string $file          The filename where the config is located (without extension)
     * @param  string $key           The dotted key, each token is an array key
     * @param  string $fallback      The value returned if not found
     *
     * @return  mixed  The element, or $fallback
     * @throws  \OutOfBoundsException  If the $package_name doesn't exist
     */
    public function get($package_name, $file, $key = '', $fallback = null)
    {
        $this->load($package_name, $file);

        return static::dottedConfig($this->packages[$package_name]['data'][$file], $key, $fallback);
    }

    /**
     * Loads the config file. It's not necessary to do this as other methods call it if necessary.
     * It will look down for other
     *
     * @param  string $package_name  The name of the package (use vendor/package format)
     * @param  string $file          The filename where the config is located (without extension)
     *
     * @throws  \OutOfBoundsException  In case the package wasn't set
     */
    public function load($package_name, $file)
    {
        if (!isset($this->packages[$package_name])) {
            // try using Composer format, the config could be somewhere down here!
            if (file_exists(VENDPATH . $package_name . '/config')) {
                $this->addPackage($package_name, VENDPATH . $package_name);
            } else {
                throw new \OutOfBoundsException('Package "' . $package_name . '" not found.');
            }
        }

        if (!isset($this->packages[$package_name]['data'][$file])) {
            $upper_level = VAPPPATH . $package_name . '/' . $this->packages[$package_name]['config_dir']
                . '/' . $file . '.php';

            $lower_level = $this->packages[$package_name]['dir'] . $this->packages[$package_name]['config_dir']
                . $file . '.php';

            if (file_exists($upper_level)) {
                $this->packages[$package_name]['data'][$file] = require $upper_level;
            } else {
                $this->packages[$package_name]['data'][$file] = require $lower_level;
            }
        }
    }

    /**
     * Add a package to the array of available configs
     *
     * @param  string $package_name  The name of the package (use vendor/package format)
     * @param  string $dir           The directory of the package
     * @param  string $config_dir    The actual location of the config. Normally the "config/" dir relative to $dir
     */
    public function addPackage($package_name, $dir, $config_dir = 'config/')
    {
        $this->packages[$package_name] = [
            'dir' => rtrim($dir, '/') . '/',
            'config_dir' => rtrim($config_dir, '/') . '/',
            'data' => null
        ];
    }

    /**
     * Returns the value of a deep associative array by using a dotted notation for the keys
     *
     * @param   array $config    The config file to fetch the value from
     * @param   string $section   The dotted keys: akey.anotherkey.key
     * @param   mixed $fallback  The fallback value
     *
     * @return  mixed
     */
    public static function dottedConfig($config, $section = '', $fallback = null)
    {
        if ($section === '') {
            return $config;
        }

        // get the section with the dot separated string
        $sections = explode('.', $section);
        $current = $config;
        foreach ($sections as $key) {
            if (isset($current[$key])) {
                $current = $current[$key];
            } else {
                return $fallback;
            }
        }

        return $current;
    }
}
