<?php

namespace Foolz\FoolFrame\Model;

use Foolz\FoolFrame\Model\Context;

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
