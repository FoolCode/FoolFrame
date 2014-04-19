<?php

namespace Foolz\Foolframe\Model\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EqualsFieldValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint) {
        if (!$this->compareValues($value, $constraint->value, $constraint)) {
            $this->context->addViolation($constraint->message, [
                '{{ field }}' => $constraint->field
            ]);
        }
    }

    protected function compareValues($value1, $value2)
    {
        return $value1 === $value2;
    }
}
