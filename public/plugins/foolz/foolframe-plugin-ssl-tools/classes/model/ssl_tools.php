<?php

namespace Foolz\Foolframe\Plugins\SslTools\Model;

use Foolz\Foolframe\Model\Context;
use Foolz\Foolframe\Model\Model;
use Foolz\Foolframe\Model\Preferences;
use Foolz\Foolframe\Model\Uri;
use Symfony\Component\HttpFoundation\Request;

class SslTools extends Model
{
    public static function nav(Context $context, Request $request, $position, $result)
    {
        $nav = $result->getParam('nav');

        /** @var Preferences $preferences */
        $preferences = $context->getService('preferences');
        /** @var Uri $uri */
        $uri = $context->getService('uri');

        if ($preferences->get('foolframe.plugins.ssl_tools.enable_'.$position.'_link') && (!$request->isSecure())) {
            $nav[] = array('href' => 'https'.substr($uri->base(), 4), 'text' => '<i class="icon-lock"></i> SSL');
        }

        $result->setParam('nav', $nav)->set($nav);
    }
}
