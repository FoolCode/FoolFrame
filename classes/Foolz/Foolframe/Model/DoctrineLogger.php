<?php

namespace Foolz\Foolframe\Model;

class DoctrineLogger implements \Doctrine\DBAL\Logging\SQLLogger
{
	protected $benchmark = null;

	public function startQuery($sql, array $params = null, array $types = null)
	{
		$this->benchmark = \Profiler::start('DBAL', $sql);
	}

	public function stopQuery()
	{
		\Profiler::stop($this->benchmark);
	}
}

