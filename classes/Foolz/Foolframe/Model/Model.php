<?php

namespace Foolz\Foolframe\Model;

use Foolz\Foolframe\Model\Context;

class Model implements ModelInterface
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
}