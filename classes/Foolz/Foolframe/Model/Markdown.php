<?php
namespace Foolz\Foolframe\Model;

use Michelf\Markdown as M;
use Foolz\Cache\Cache;

class Markdown
{
	/**
	 * Adapter for Markdown that caches the result
	 *
	 * @param string $text The content to parse from MarkDown to HTML
	 *
	 * @return string The HTML
	 */
	public static function parse($text)
	{
		try
		{
			// using crc32 should be safe enough for some human written text, and supposedly it's lighter than md5
			// this of course appends an integer to the string
			return Cache::item('Foolz\Foolframe\Model\Markdown::parse.crc32.'.crc32($text))->get();
		}
		catch (\OutOfBoundsException $e)
		{
			$parsed = M::defaultTransform($text);
			Cache::item('Foolz\Foolframe\Model\Markdown::parse.crc32.'.crc32($text))->set($parsed, 900);
			return $parsed;
		}
	}
}