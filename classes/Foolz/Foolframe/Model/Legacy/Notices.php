<?php

namespace Foolz\Foolframe\Model\Legacy;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Notices
{
    /**
     * @var Session
     */
    protected static $session;

    /**
     * Notices for the next page load
     *
     * @var  array
     */
    protected static $flash_notices = [];

    /**
     * Notices for the current page load
     *
     * @var  array
     */
    protected static $notices = [];

    /**
     * Sets the session
     */
    public static function init(SessionInterface $session)
    {
        static::$session = $session;
    }

    /**
     * Returns the notices that have been set during this load
     *
     * @return  array  The notices, can be empty
     */
    public static function get()
    {
        return static::$notices;
    }

    /**
     * Set notices to be displayed during this load
     *
     * @param  string  $level    The level of the message: success, warning, danger
     * @param  string  $message  The message
     */
    public static function set($level, $message, $escape = false)
    {
        static::$notices[] = ['level' => $level, 'message' => $message];
    }

    /**
     * Get the flash notices
     *
     * @return  array  The flash notices, can be empty
     */
    public static function getFlash()
    {
        return $array = static::$session->getFlashBag()->get('notice', []);
    }

    /**
     * Sets a flash notice
     *
     * @param  string  $level    The level of the message: success, warning, danger
     * @param  string  $message  The message
     */
    public static function setFlash($level, $message)
    {
        static::$flash_notices[] = ['level' => $level, 'message' => $message];
        static::$session->getFlashBag()->set('notice', static::$flash_notices);
    }
}
