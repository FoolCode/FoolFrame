<?php

namespace Foolz\Foolframe\Model;

use Doctrine\DBAL\Logging\SQLLogger;
use Foolz\Profiler\Profiler;

class DoctrineLogger extends Model implements SQLLogger
{
    /**
     * @var Profiler
     */
    protected $profiler;

    protected $benchmark = null;

    /**
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);

        $this->profiler = $context->getService('profiler');
    }

    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->profiler->logStart('Doctrine: '.$sql, [$sql]);
    }

    public function stopQuery()
    {
        $this->profiler->logStop('Doctrine');
    }
}
