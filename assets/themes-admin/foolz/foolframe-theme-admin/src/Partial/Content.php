<?php

namespace Foolz\FoolFrame\Theme\Admin\Partial;

class Content extends \Foolz\FoolFrame\View\View
{
    public function toString()
    {
        echo $this->getParamManager()->getParam('content');
    }
}
