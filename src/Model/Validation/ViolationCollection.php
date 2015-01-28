<?php

namespace Foolz\FoolFrame\Model\Validation;

use Foolz\FoolFrame\Model\Validation\Violation;

class ViolationCollection {

    /**
     * @var Violation[]
     */
    protected $violations;

    /**
     * @param Violation[] $violations
     */
    public function __construct($violations)
    {
        $this->violations = $violations;
    }

    /**
     * @return Violation[]
     */
    public function getArray()
    {
        return $this->violations;
    }

    public function getString()
    {
        $array = [];
        foreach ($this->violations as $violation) {
            $array[] = $violation->getLabel().': '.$violation->getViolationsString();
        }

        return $array;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->violations);
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        return implode('<br>', $this->getString());
    }

    /**
     * @return string
     */
    public function getText()
    {
        return implode("\n", $this->getString());
    }
}
