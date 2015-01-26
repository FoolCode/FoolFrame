<?php

namespace Foolz\FoolFrame\Model\Validation;

use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class Violation {

    /**
     * @var ConstraintViolationList
     */
    protected $violations;

    /**
     * @var string
     */
    protected $field_name;

    /**
     * @var string
     */
    protected $label;

    /**
     * @param ConstraintViolationList $violations
     * @param string $field_name
     * @param string $label
     */
    public function __construct(ConstraintViolationList $violations, $field_name, $label) {
        $this->violations = $violations;
        $this->field_name = $field_name;
        $this->label = $label;
    }

    /**
     * @return ConstraintViolationList
     */
    public function getViolations() {
        return $this->violations;
    }

    /**
     * @return string
     */
    public function getViolationsString() {
        $array = [];
        foreach ($this->violations as $violation) {
            /** @var $violation ConstraintViolation */
            $array[] = $violation->getMessage();
        }

        return implode(' ', $array);
    }

    /**
     * @return string
     */
    public function getFieldName() {
        return $this->field_name;
    }

    /**
     * @return string
     */
    public function getLabel() {
        return $this->label;
    }
}
