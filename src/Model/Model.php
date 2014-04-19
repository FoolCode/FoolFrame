<?php

namespace Foolz\Foolframe\Model;

use Foolz\Foolframe\Model\Context;

class Model
{
    /**
     * @var Context
     */
    protected $context;

    public function __construct(Context $context) {
        $this->context = $context;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return Auth
     */
    public function getAuth()
    {
        return $this->context->getService('auth');
    }
}