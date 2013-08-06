<?php

namespace Foolz\Foolframe\Theme\Admin\Partial;

class Content extends \Foolz\Foolframe\View\View
{
    public function toString()
    {
        echo $this->getParamManager()->getParam('content');
    }
}
