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
        try {
            return Cache::item('foolframe.model.markdown.parse.md5.'.md5($text))->get();
        } catch (\OutOfBoundsException $e) {
            $parsed = M::defaultTransform($text);
            Cache::item('foolframe.model.markdown.parse.md5.'.md5($text))->set($parsed, 900);
            return $parsed;
        }
    }
}
