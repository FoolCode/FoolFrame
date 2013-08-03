<?php

namespace Foolz\Foolframe\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Common
{
    public function before(Request $request)
    {
        if (!\Foolz\Foolframe\Model\Legacy\Config::get('foolz/foolframe', 'config', 'install.installed')) {
            throw new NotFoundHttpException;
        }
    }
}
