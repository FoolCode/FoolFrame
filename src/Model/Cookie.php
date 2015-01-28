<?php

namespace Foolz\FoolFrame\Model;

class Cookie extends \Symfony\Component\HttpFoundation\Cookie
{
    public function __construct(Context $context, $name, $value = null, $expire = 0, $path = '/', $domain = null, $secure = false, $httpOnly = false)
    {
        $config = $context->getService('config');
        parent::__construct(
            $config->get('foolz/foolframe', 'config', 'config.cookie_prefix').$name,
            $value,
            time() + $expire,
            $path,
            $domain,
            $secure,
            $httpOnly
        );
    }
}
