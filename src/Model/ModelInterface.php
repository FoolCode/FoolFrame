<?php

namespace Foolz\Foolframe\Model;

use Foolz\Foolframe\Model\Context;

interface ModelInterface
{
    /**
     * Pass to the constructor the Context
     *
     * @param Context $context
     */
    public function __construct(Context $context);

    /**
     * Return the Context
     *
     * @return Context
     */
    public function getContext();
}