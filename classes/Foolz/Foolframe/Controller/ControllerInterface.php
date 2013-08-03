<?php

namespace Foolz\Foolframe\Controller;

use Foolz\Foolframe\Model\Context;
use Symfony\Component\HttpFoundation\Request;

interface ControllerInterface
{

    public function setContext(Context $context);

    public function getContext();

    public function setRequest(Request $request);

    public function getRequest();

}