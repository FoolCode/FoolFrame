<?php

namespace Foolz\Foolframe\Model;

use Doctrine\DBAL\Logging\SQLLogger;

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
        $this->profiler->start('Doctrine', $sql);
    }

    public function stopQuery()
    {
        $this->profiler->stop('Doctrine');
    }
}
