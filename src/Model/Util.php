<?php

namespace Foolz\FoolFrame\Model;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Util
{
    /**
     * Checks if an array is associative
     * From http://stackoverflow.com/a/4254008/644504
     *
     * @param $array
     * @return bool
     */
    public static function isAssoc($array)
    {
        return (bool) count(array_filter(array_keys($array), 'is_string'));
    }

    /**
     * Returns a random string
     * From http://stackoverflow.com/a/4356295/644504
     *
     * @param int $length
     * @return string
     */
    public static function randomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    /**
     * Delete a file/recursively delete a directory
     *
     * NOTE: Be very careful with the path you pass to this!
     *
     * From: http://davidhancock.co/2012/11/useful-php-functions-for-dealing-with-the-file-system/
     *
     * @param string $path The path to the file/directory to delete
     * @return void
     */
    public static function delete($path)
    {
        if (is_dir($path)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    rmdir($file->getPathname());
                } else {
                    unlink($file->getPathname());
                }
            }

            rmdir($path);
        } else {
            unlink($path);
        }
    }

    /**
     * Copy a file or recursively copy a directories contents
     *
     * From: http://davidhancock.co/2012/11/useful-php-functions-for-dealing-with-the-file-system/
     *
     * @param string $source The path to the source file/directory
     * @param string $dest The path to the destination directory
     * @return void
     */
    public static function copy($source, $dest)
    {
        if (is_dir($source)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    mkdir($dest.DIRECTORY_SEPARATOR.$iterator->getSubPathName());
                } else {
                    copy($file, $dest.DIRECTORY_SEPARATOR.$iterator->getSubPathName());
                }
            }
        } else {
            copy($source, $dest);
        }
    }

    /**
     * Return the size of a file or a directory and its contents in bytes
     *
     * NOTE: This function may return unexpected results for files larger than
     *       2GB on 32bit hosts due to PHP's integer type being 32bit signed.
     *
     * From: http://davidhancock.co/2012/11/useful-php-functions-for-dealing-with-the-file-system/
     *
     * @param string $path The path to the file/directory to calculate the size of
     * @return int
     */
    public static function getSize($path)
    {
        $size = 0;
        if (is_dir($path)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

            foreach ($iterator as $file) {
                $size += $file->getSize();
            }
        } else {
            $size = filesize($path);
        }

        return $size;
    }
}
