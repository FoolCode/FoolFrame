<?php

namespace Foolz\Foolframe\Model;

use Symfony\Component\Validator\Validation as SymfonyValidation;
use Symfony\Component\Validator\Constraints as Assert;

class Validation
{
    public static function validateValues(Array $data, $constraints, $labels) {
        $validator = SymfonyValidation::createValidator();
        $violations_arr = [];

        foreach ($constraints as $key => $constraint) {
            if (isset($data[$key])) {
                $violations = $validator->validateValue($data[$key], $constraint);
                if ($violations->count() > 0) {
                    $violations_arr[$key] = new Violation($violations, $key, isset($labels[$key]) ? $labels[$key] : '');
                }
            }
        }

        return new ViolationCollection($violations_arr);
    }

    /**
     * Checks the form for and returns either a compiled array of values or
     * the error
     *
     * @param $form array
     * @param $alternate array name/value pairs to use instead of the POST array
     * @return array
     */
    public static function form_validate($form, $alternate = null)
    {
        // this gets a bit complex because we want to show all errors at the same
        // time, which means we have to run both core validation and custom, then
        // merge the result.

        $input = !is_null($alternate) ? $alternate : \Input::post();

        foreach ($form as $name => $item) {
            if (isset($item['sub'])) {
                // flatten the form
                $form = array_merge($form, $item['sub']);
            }

            if (isset($item['sub_inverse'])) {
                // flatten the form
                $form = array_merge($form, $item['sub_inverse']);
            }

            if (isset($item['checkboxes'])) {
                // flatten the form
                $form_temp = array();

                foreach ($item['checkboxes'] as $checkbox) {
                    $form_temp[$name . ',' . $checkbox['array_key'] . ''] = $checkbox;
                }

                $form = array_merge($form, $form_temp);
            }
        }

        $validator = SymfonyValidation::createValidator();
        $constraint_arr = [];
        foreach ($form as $name => $item) {
            if (isset($item['validation'])) {
                // set the rules and add [] to the name if array
                $constraint_arr[$name . ((isset($item['array']) && $item['array']) ? '[]' : '')] = $item['validation'];
            }
        }

        $constraint = new Assert\Collection($constraint_arr);
        $constraint->allowExtraFields = true;

        // we need to run both validation and closures
        $violations = $validator->validateValue($input, $constraint);

        $validation_func = array();
        // we run this after form_validation in case form_validation edited the POST data
        foreach ($form as $name => $item) {
            // the "required" MUST be handled with the standard form_validation
            // or we'll never get in here
            if (isset($item['validation_func']) && isset($input[$name])) {
                // contains TRUE for success and in array with ['error'] in case
                $validation_func[$name] = $item['validation_func']($input, $form);

                // critical errors don't allow the continuation of the validation.
                // this allows less checks for functions that come after the critical ones.
                // criticals are usually the IDs in the hidden fields.
                if (isset($validation_func[$name]['critical']) && $validation_func[$name]['critical'] == true) {
                    break;
                }

                if (isset($validation_func[$name]['push']) && is_array($validation_func[$name]['push'] == true)) {
                    // overwrite the $input array
                    foreach ($validation_func[$name]['push'] as $n => $i) {
                        $input[$n] = $i;
                    }
                }
            }
        }

        // filter results, since the closures return ['success'] = TRUE on success
        $validation_func_errors = array();
        $validation_func_warnings = array();
        foreach ($validation_func as $item) {
            // we want only the errors
            if (isset($item['success'])) {
                continue;
            }

            if (isset($item['warning'])) {
                // we want only the human readable error
                $validation_func_warnings[] = $item['warning'];
            }

            if (isset($item['error'])) {
                // we want only the human readable error
                $validation_func_errors[] = $item['error'];
            }
        }

        if ($violations->count() || count($validation_func_errors)) {
            $errors = [];

            foreach ($violations as $violation) {
                $errors[] = $form[substr($violation->getPropertyPath(), 1, -1)]['label'].': '.$violation->getMessage().' ';
            }

            $errors += $validation_func_errors;

            return array('error' => implode(' ', $errors));
        } else {
            // get rid of all the uninteresting inputs and simplify
            $result = array();

            foreach ($form as $name => $item) {
                // not interested in data that is not related to database
                if ($item['type'] != 'checkbox_array' &&
                    (!isset($item['database']) || $item['database'] !== TRUE) &&
                    (!isset($item['preferences']) || $item['preferences'] === FALSE)
                ) {
                    continue;
                }

                if ($item['type'] == 'checkbox_array') {
                    foreach ($item['checkboxes'] as $checkbox_key => $checkbox) {
                        if (isset($input[$name][$checkbox['array_key']]) && $input[$name][$checkbox['array_key']] == 1) {
                            $result[$name][$checkbox['array_key']] = 1;
                        } else {
                            $result[$name][$checkbox['array_key']] = 0;
                        }
                    }
                } else if ($item['type'] == 'checkbox') {
                    if (isset($input[$name]) && $input[$name] == 1) {
                        $result[$name] = 1;
                    } else {
                        $result[$name] = 0;
                    }
                } else {
                    if (isset($input[$name]) && $input[$name] !== FALSE) {
                        $result[$name] = $input[$name];
                    }
                }
            }

            if (count($validation_func_warnings) > 0) {
                return array('success' => $result, 'warning' => implode(' ', $validation_func_warnings));
            }
            // returning a form with the new values
            return array('success' => $result);
        }
    }
}
