<?php

namespace Foolz\Foolframe\Model\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

class EqualsField extends Constraint
{
    public $message;
    public $field;
    public $value;

    public function __construct($options = null)
    {
        if (!isset($options['field'])) {
            throw new ConstraintDefinitionException(sprintf(
                'The %s constraint requires the "field" option to be set.',
                get_class($this)
            ));
        }

        if (!isset($options['value'])) {
            throw new ConstraintDefinitionException(sprintf(
                'The %s constraint requires the "value" option to be set.',
                get_class($this)
            ));
        }

        $this->message = _i('This field should match the contents of {{ field }}.');

        parent::__construct($options);
    }
}
