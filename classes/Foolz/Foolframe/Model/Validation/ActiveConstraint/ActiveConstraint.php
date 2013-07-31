<?php

namespace Foolz\Foolframe\Model\Validation\ActiveConstraint;


interface ActiveConstraint {

    /**
     * @param mixed $data The data to be processed
     * @return mixed The processed data
     */
    public function run($data);

}