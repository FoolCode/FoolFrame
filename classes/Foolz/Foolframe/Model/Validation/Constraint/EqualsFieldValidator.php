<?php

namespace Foolz\Foolframe\Model\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EqualsFieldValidator extends ConstraintValidator {

    public function validate($value, Constraint $constraint) {
        if ($constraint->field_value !== $value) {
            $this->context->addViolation($constraint->message, [
                '{{ field }}' => $constraint->field_label
            ]);
        }
    }
}