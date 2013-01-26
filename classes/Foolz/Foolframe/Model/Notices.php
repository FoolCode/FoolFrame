<?php

namespace Foolz\Foolframe\Model;

class Notices
{
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
	public static function set($level, $message)
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
		$array = \Session::get_flash('notices');

		return is_array($array) ? $array : [];
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
		\Session::set_flash('notices', static::$flash_notices);
	}
}