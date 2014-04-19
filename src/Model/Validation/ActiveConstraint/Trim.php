<?php

namespace Foolz\Foolframe\Model\Validation\ActiveConstraint;

use Foolz\Foolframe\Model\Validation\ActiveConstraint\ActiveConstraint;

class Trim implements ActiveConstraint {

    /**
     * @param mixed $data The data to be processed
     * @return mixed The processed data
     */
    public function run($data)
    {
        return trim($data);
    }
}