<?php

namespace Foolz\Foolframe\Plugins\SslTools\Model;

class SslTools
{
    public static function check()
    {
        if (!isset($_SERVER['HTTPS']) || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'off')) {
            if (\Preferences::get('foolframe.plugins.ssl_tools.force_everyone')
                || (\Preferences::get('foolframe.plugins.ssl_tools.force_for_logged') && \Auth::has_access('maccess.user'))
                || (\Preferences::get('foolframe.plugins.ssl_tools.sticky') && \Input::cookie('ff_sticky_ssl')))
            {
                // redirect to itself
                \Response::redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            }
        } else {
            if (\Preferences::get('foolframe.plugins.ssl_tools.sticky') && !\Input::cookie('ff_sticky_ssl')) {
                \Cookie::set('foolframe.plugins.ssl_tools.sticky', '1', 30);
            }
        }
    }

    public static function nav_top($result)
    {
        return static::nav('top', $result);
    }

    public static function nav_bottom($result)
    {
        return static::nav('bottom', $result);
    }

    public static function nav($position, $result)
    {
        $nav = $result->getParam('nav');

        if (\Preferences::get('foolframe.plugins.ssl_tools.enable_'.$position.'_link') && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off')) {
            $nav[] = array('href' => 'https' . substr(\Uri::current(), 4), 'text' => '<i class="icon-lock"></i> SSL');
        }

        $result->setParam('nav', $nav)->set($nav);
    }
}
