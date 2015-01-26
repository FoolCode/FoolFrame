<?php

namespace Foolz\FoolFrame\Model;

use Foolz\FoolFrame\Model\Context;

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
