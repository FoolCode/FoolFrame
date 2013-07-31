<?php

namespace Foolz\Foolframe\Model\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

class EqualsField extends Constraint {

    public $message = '';

    public $field = '';

    public function __construct($field_label, $field_value) {
        $this->field_label = $field_label;
        $this->field_value = $field_value;
        $this->message = _i('The field should match the content of {{ field }}.');
    }

}