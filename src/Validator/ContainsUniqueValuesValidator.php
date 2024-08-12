<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Validator checking uniqueness of the values inside the iterable data structure.
 * 
 * @author Rastislav Bostik <rastislav.bostik@gmail.com>
 */
class ContainsUniqueValuesValidator extends ConstraintValidator
{
    /**
     * Check whether provided value is an array
     * containing unique values
     * 
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     * @throws \Symfony\Component\Validator\Exception\UnexpectedTypeException
     * @throws \Symfony\Component\Validator\Exception\UnexpectedValueException
     * @throws \Symfony\Component\Validator\Exception\InvalidArgumentException
     * @return void
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ContainsUniqueValues) {
            throw new UnexpectedTypeException($constraint, ContainsUniqueValues::class);
        }

        if (!in_array($constraint->mode, ContainsUniqueValues::MODES, true)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported constraint %s::mode parameter value "%s".', 
                get_debug_type($constraint), 
                $constraint->mode
            ));
        }

        if (empty(mb_trim($constraint->message))) {
            throw new InvalidArgumentException(sprintf(
                'Empty constraint %s::message parameter value.', 
                get_debug_type($constraint)
            ));
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) to take care of that
        if (null === $value || '' === $value) {
            return;
        }

        // check whether provided input value is iterable
        // and throw an exception if not
        if (!is_iterable($value)) {
            throw new UnexpectedValueException($value, 'iterable');
        }

        // initialize the double found indicator
        $doubleFound = false;
        // check provided iterable structure for duplicit values
        if (ContainsUniqueValues::MODE_STRICT === $constraint->mode) {
            $valuesMap = [];
            foreach ($value as $itemValue) {
                // exclude handling of "resource" typed values
                if (is_resource($itemValue)) {
                    throw new UnexpectedValueException('Values of type "resource" are not supported.','object|array|string|float|int|bool');
                }

                // get item value hash
                $itemValueHash = serialize($itemValue);

                // check whether hash has been already found
                // mark it down if so and quit the loop
                if (array_key_exists($itemValueHash, $valuesMap)) {
                    $doubleFound = true;
                    break;
                }

                // store the item value hash to the map
                $valuesMap[$itemValueHash] = true;
            }
        } else { // ContainsUniqueValues::MODE_LOOSE === $constraint->mode
            // convert value to array
            $value = !is_array($value)
                ? iterator_to_array($value)
                : $value;

            // convert eeach objects into string hash
            // simplifying uniqing
            array_walk(
                $value,
                function(&$value, $key) {
                    if(is_object($value)) {
                        if($value)
                        $value = serialize($value);
                    }
                }
            );

            // check whether sizes of original and uniqued arrays are equal
            $doubleFound = count($value) !== count(array_unique($value, SORT_STRING));
        }

        // return without any complaints
        // when no duble found in array
        if (!$doubleFound) {
            return;
        }

        // build the violation otherwise
        $this->context
            ->buildViolation($constraint->message)
            ->addViolation();
    }
}