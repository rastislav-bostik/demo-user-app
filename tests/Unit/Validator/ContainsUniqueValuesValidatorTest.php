<?php

namespace App\Tests\Unit\Validator;

use App\Validator\ContainsUniqueValues;
use App\Validator\ContainsUniqueValuesValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

class ContainsUniqueValuesValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): ContainsUniqueValuesValidator
    {
        return new ContainsUniqueValuesValidator();
    }
    
    public function testWrongConstraintClassTriggerException(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(sprintf(
            'Expected argument of type "%s", "%s" given',
            ContainsUniqueValues::class,
            get_debug_type(new NonContainsUniqueValuesConstraint())
        ));
        $this->validator->validate([], new NonContainsUniqueValuesConstraint());
    }

    public function testUnknownModeTriggerException(): void
    {
        $constraint = new ContainsUniqueValues();
        $constraint->mode = 'Unknown';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Unsupported constraint %s::mode parameter value "%s".',
            get_debug_type($constraint),
            'Unknown'
        ));
        
        $this->validator->validate([], $constraint);
    }

    public function testEmptyMessageTriggerException(): void
    {
        $constraint = new ContainsUniqueValues();
        $constraint->message = '';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Empty constraint %s::message parameter value.',
            get_debug_type($constraint)
        ));
        
        $this->validator->validate([], $constraint);
    }

    public function testEmptyStringIsValid(): void
    {
        $this->validator->validate('', new ContainsUniqueValues());

        $this->assertNoViolation();
    }
    
    public function testExpectsIterableCompatibleType(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(sprintf(
            'Expected argument of type "%s", "%s" given',
            'iterable',
            get_debug_type(new \stdClass)
        ));
        $this->validator->validate(new \stdClass(), new ContainsUniqueValues());
    }
    
    /**
     * @dataProvider \App\Tests\Data\Providers\IterableDataProvider::getEmptyIterables()
     */
    public function testEmptyIterableIsValidInStrictMode(iterable $emptyIterableData): void
    {
        $this->validator->validate(
            $emptyIterableData, // <-- [] --
            new ContainsUniqueValues(
                mode: ContainsUniqueValues::MODE_STRICT
            )
        );

        $this->assertNoViolation();
    }

    /**
     * @dataProvider \App\Tests\Data\Providers\IterableDataProvider::getEmptyIterables()
     */
    public function testEmptyIterableIsValidInLooseMode(iterable $emptyIterableData): void
    {
        $this->validator->validate(
            $emptyIterableData, // <-- [] --
            new ContainsUniqueValues(
                mode: ContainsUniqueValues::MODE_LOOSE
            )
        );

        $this->assertNoViolation();
    }


    /**
     * @dataProvider \App\Tests\Data\Providers\IterableDataProvider::getUniqueStringValuesContainingIterables()
     */
    public function testUniqueStringValuesIterableIsValidInStrictMode(iterable $uniqueStringValuesIterableData): void
    {
        $this->validator->validate(
            $uniqueStringValuesIterableData, // <-- ['a', 'b', 'c'] --
            new ContainsUniqueValues(
                mode: ContainsUniqueValues::MODE_STRICT
            )
        );

        $this->assertNoViolation();
    }

    /**
     * @dataProvider \App\Tests\Data\Providers\IterableDataProvider::getUniqueStringValuesContainingIterables()
     */
    public function testUniqueStringValuesIterableIsValidInLooseMode(iterable $uniqueStringValuesIterableData): void
    {
        $this->validator->validate(
            $uniqueStringValuesIterableData, // <-- ['a', 'b', 'c'] --
            new ContainsUniqueValues(
                mode: ContainsUniqueValues::MODE_LOOSE
            )
        );

        $this->assertNoViolation();
    }

    /**
     * @dataProvider \App\Tests\Data\Providers\IterableDataProvider::getUniqueTypeLooseValuesContainingIterables()
     */
    public function testUniqueTypeLooseValuesIterableIsValidInStrictMode(iterable $uniqueTypeLooseValuesIterableData): void
    {
        $this->validator->validate(
            $uniqueTypeLooseValuesIterableData, // <-- [true, 'false', 123] --
            new ContainsUniqueValues(
                mode: ContainsUniqueValues::MODE_STRICT
            )
        );

        $this->assertNoViolation();
    }

    
    /**
     * @dataProvider \App\Tests\Data\Providers\IterableDataProvider::getUniqueTypeLooseValuesContainingIterables()
     */
    public function testUniqueTypeLooseValuesIterableIsValidInLooseMode(iterable $uniqueTypeLooseValuesIterableData): void
    {
        $this->validator->validate(
            $uniqueTypeLooseValuesIterableData, // <-- [true, 'false', 123] --
            new ContainsUniqueValues(
                mode: ContainsUniqueValues::MODE_LOOSE
            )
        );

        $this->assertNoViolation();
    }

    /**
     * @dataProvider \App\Tests\Data\Providers\IterableDataProvider::getUniqueTypeStrictValuesContainingIterables()
     */
    public function testUniqueTypeStrictValuesIterableIsValidInStrictMode(iterable $uniqueTypeStrictValuesIterableData): void
    {
        $this->validator->validate(
            $uniqueTypeStrictValuesIterableData, // <-- [true, 'false', 1] --
            new ContainsUniqueValues(
                mode: ContainsUniqueValues::MODE_STRICT
            )
        );

        // type strict mode evaluates type-differing loose-duplicate values as unique
        // (e.g. bool(true) and int(1) are unique from type strict perspective)
        $this->assertNoViolation();
    }

    /**
     * @dataProvider \App\Tests\Data\Providers\IterableDataProvider::getUniqueTypeStrictValuesContainingIterables()
     */
    public function testUniqueTypeStrictValuesIterableIsInvalidInLooseMode(iterable $uniqueTypeStrictValuesIterableData): void
    {
        $this->validator->validate(
            $uniqueTypeStrictValuesIterableData, // <-- [true, 'false', 1] --
            new ContainsUniqueValues(
                mode: ContainsUniqueValues::MODE_LOOSE
            )
        );

        $this->buildViolation((new ContainsUniqueValues())->message)
            ->assertRaised();
    }

    /**
     * @dataProvider \App\Tests\Data\Providers\IterableDataProvider::getUniqueTypeStrictValuesContainingIterables()
     */
    public function testUniqueTypeStrictValuesIterableIsInvalidInLooseModeWithCustomMessage(iterable $uniqueTypeStrictValuesIterableData): void
    {
        $this->validator->validate(
            $uniqueTypeStrictValuesIterableData, // <-- [true, 'false', 1] --
            new ContainsUniqueValues(
                mode:    ContainsUniqueValues::MODE_LOOSE,
                message: 'Custom message.'
            )
        );

        $this->buildViolation('Custom message.')
            ->assertRaised();
    }

    /**
     * @dataProvider \App\Tests\Data\Providers\IterableDataProvider::getUniqueObjectsContainingIterables()
     */
    public function testUniqueObjectsIterableIsValidInStrictMode(iterable $uniqueObjectsIterableData): void
    {
        $this->validator->validate(
            $uniqueObjectsIterableData, // <-- [{'attributeX': 'valueA'}, {'attributeX': 'valueB'}, {'attributeX': 'valueC'}] --
            new ContainsUniqueValues(
                mode: ContainsUniqueValues::MODE_STRICT
            )
        );

        $this->assertNoViolation();
    }

        /**
     * @dataProvider \App\Tests\Data\Providers\IterableDataProvider::getUniqueObjectsContainingIterables()
     */
    public function testUniqueObjectsIterableIsValidInLooseMode(iterable $uniqueObjectsIterableData): void
    {
        $this->validator->validate(
            $uniqueObjectsIterableData, // <-- [{'attributeX': 'valueA'}, {'attributeX': 'valueB'}, {'attributeX': 'valueC'}] --
            new ContainsUniqueValues(
                mode: ContainsUniqueValues::MODE_LOOSE
            )
        );

        $this->assertNoViolation();
    }

    /**
     * @dataProvider \App\Tests\Data\Providers\IterableDataProvider::getDuplicateStringValuesContainingIterables()
     */
    public function testDuplicateStringValuesIterableIsValidInStrictMode(iterable $duplicateStringValuesIterableData): void
    {
        $this->validator->validate(
            $duplicateStringValuesIterableData, // <-- ['a', 'b', 'a'] --
            new ContainsUniqueValues(
                mode:    ContainsUniqueValues::MODE_STRICT,
            )
        );

        $this->buildViolation((new ContainsUniqueValues())->message)
            ->assertRaised();
    }

    /**
     * @dataProvider \App\Tests\Data\Providers\IterableDataProvider::getDuplicateStringValuesContainingIterables()
     */
    public function testDuplicateStringValuesIterableIsValidInStrictModeWithCustomMessage(iterable $duplicateStringValuesIterableData): void
    {
        $this->validator->validate(
            $duplicateStringValuesIterableData, // <-- ['a', 'b', 'a'] --
            new ContainsUniqueValues(
                mode:    ContainsUniqueValues::MODE_STRICT,
                message: 'Custom message.'
            )
        );

        $this->buildViolation('Custom message.')
            ->assertRaised();
    }


    /**
     * @dataProvider \App\Tests\Data\Providers\IterableDataProvider::getDuplicateStringValuesContainingIterables()
     */
    public function testDuplicateStringValuesIterableIsValidInLooseMode(iterable $duplicateStringValuesIterableData): void
    {
        $this->validator->validate(
            $duplicateStringValuesIterableData, // <-- ['a', 'b', 'a'] --
            new ContainsUniqueValues(
                mode:    ContainsUniqueValues::MODE_LOOSE,
            )
        );

        $this->buildViolation((new ContainsUniqueValues())->message)
            ->assertRaised();
    }

    /**
     * @dataProvider \App\Tests\Data\Providers\IterableDataProvider::getDuplicateStringValuesContainingIterables()
     */
    public function testDuplicateStringValuesIterableIsValidInLooseModeWithCustomMessage(iterable $duplicateStringValuesIterableData): void
    {
        $this->validator->validate(
            $duplicateStringValuesIterableData, // <-- ['a', 'b', 'a'] --
            new ContainsUniqueValues(
                mode:    ContainsUniqueValues::MODE_LOOSE,
                message: 'Custom message.'
            )
        );

        $this->buildViolation('Custom message.')
            ->assertRaised();
    }

    /**
     * @dataProvider \App\Tests\Data\Providers\IterableDataProvider::getDuplicateTypeLooseValuesContainingIterables()
     */
    public function testDuplicateTypeLooseValuesIterableIsValidInStrictMode(iterable $duplicateTypeLooseValuesIterableData): void
    {
        $this->validator->validate(
            $duplicateTypeLooseValuesIterableData, // <-- [true, 'false', 1] --
            new ContainsUniqueValues(
                mode: ContainsUniqueValues::MODE_STRICT
            )
        );

        // type strict mode evaluates type-differing loose-duplicate values as unique
        // (e.g. bool(true) and int(1) are unique from type strict perspective)
        $this->assertNoViolation();
    }

    /**
     * @dataProvider \App\Tests\Data\Providers\IterableDataProvider::getDuplicateTypeLooseValuesContainingIterables()
     */
    public function testDuplicateTypeLooseValuesIterableIsValidInLooseMode(iterable $duplicateTypeLooseValuesIterableData): void
    {
        $this->validator->validate(
            $duplicateTypeLooseValuesIterableData, // <-- [true, 'false', 1] --
            new ContainsUniqueValues(
                mode: ContainsUniqueValues::MODE_LOOSE
            )
        );

        $this->buildViolation((new ContainsUniqueValues())->message)
            ->assertRaised();
    }


    /**
     * @dataProvider \App\Tests\Data\Providers\IterableDataProvider::getDuplicateTypeLooseValuesContainingIterables()
     */
    public function testDuplicateTypeLooseValuesIterableIsValidInLooseModeWithCustomMessage(iterable $duplicateTypeLooseValuesIterableData): void
    {
        $this->validator->validate(
            $duplicateTypeLooseValuesIterableData, // <-- [true, 'false', 1] --
            new ContainsUniqueValues(
                mode:     ContainsUniqueValues::MODE_LOOSE,
                message: 'Custom message.'
            )
        );

        $this->buildViolation('Custom message.')
            ->assertRaised();
    }

    /**
     * @dataProvider \App\Tests\Data\Providers\IterableDataProvider::getDuplicateTypeStrictValuesContainingIterables()
     */
    public function testDuplicateTypeStrictValuesIterableIsInvalidInStrictMode(iterable $duplicateTypeStrictValuesIterableData): void
    {
        $this->validator->validate(
            $duplicateTypeStrictValuesIterableData, // <-- [true, 'false', true] --
            new ContainsUniqueValues(
                mode: ContainsUniqueValues::MODE_STRICT
            )
        );

        $this->buildViolation((new ContainsUniqueValues())->message)
            ->assertRaised();
    }

    /**
     * @dataProvider \App\Tests\Data\Providers\IterableDataProvider::getDuplicateTypeStrictValuesContainingIterables()
     */
    public function testDuplicateTypeStrictValuesIterableIsInvalidInStrictModeWithCustomMessage(iterable $duplicateTypeStrictValuesIterableData): void
    {
        $this->validator->validate(
            $duplicateTypeStrictValuesIterableData, // <-- [true, 'false', true] --
            new ContainsUniqueValues(
                mode:     ContainsUniqueValues::MODE_STRICT,
                message: 'Custom message.'
            )
        );

        $this->buildViolation('Custom message.')
            ->assertRaised();
    }

    /**
     * @dataProvider \App\Tests\Data\Providers\IterableDataProvider::getDuplicateTypeStrictValuesContainingIterables()
     */
    public function testDuplicateTypeStrictValuesIterableIsInvalidInLooseMode(iterable $duplicateTypeStrictValuesIterableData): void
    {
        $this->validator->validate(
            $duplicateTypeStrictValuesIterableData, // <-- [true, 'false', true] --
            new ContainsUniqueValues(
                mode: ContainsUniqueValues::MODE_LOOSE
            )
        );

        $this->buildViolation((new ContainsUniqueValues())->message)
            ->assertRaised();
    }

    /**
     * @dataProvider \App\Tests\Data\Providers\IterableDataProvider::getDuplicateTypeStrictValuesContainingIterables()
     */
    public function testDuplicateTypeStrictValuesIterableIsInvalidInLooseModeWithCustomMessage(iterable $duplicateTypeStrictValuesIterableData): void
    {
        $this->validator->validate(
            $duplicateTypeStrictValuesIterableData, // <-- [true, 'false', true] --
            new ContainsUniqueValues(
                mode:     ContainsUniqueValues::MODE_LOOSE,
                message: 'Custom message.'
            )
        );

        $this->buildViolation('Custom message.')
            ->assertRaised();
    }

    /**
     * @dataProvider \App\Tests\Data\Providers\IterableDataProvider::getDuplicateObjectsContainingIterables()
     */
    public function testDuplicateObjectsIterableIsValidInStrictMode(iterable $duplicateObjectsIterableData): void
    {
        $this->validator->validate(
            $duplicateObjectsIterableData, // <-- [{'attributeX': 'valueA'}, {'attributeX': 'valueB'}, {'attributeX': 'valueA'}] --
            new ContainsUniqueValues(
                mode: ContainsUniqueValues::MODE_STRICT
            )
        );

        $this->buildViolation((new ContainsUniqueValues())->message)
            ->assertRaised();
    }

    /**
     * @dataProvider \App\Tests\Data\Providers\IterableDataProvider::getDuplicateObjectsContainingIterables()
     */
    public function testDuplicateObjectsIterableIsValidInStrictModeWithCustomMessage(iterable $duplicateObjectsIterableData): void
    {
        $this->validator->validate(
            $duplicateObjectsIterableData, // <-- [{'attributeX': 'valueA'}, {'attributeX': 'valueB'}, {'attributeX': 'valueA'}] --
            new ContainsUniqueValues(
                mode:    ContainsUniqueValues::MODE_STRICT,
                message: 'Custom message.'
            )
        );

        $this->buildViolation('Custom message.')
            ->assertRaised();
    }

        /**
     * @dataProvider \App\Tests\Data\Providers\IterableDataProvider::getDuplicateObjectsContainingIterables()
     */
    public function testDuplicateObjectsIterableIsValidInLooseMode(iterable $duplicateObjectsIterableData): void
    {
        $this->validator->validate(
            $duplicateObjectsIterableData, // <-- [{'attributeX': 'valueA'}, {'attributeX': 'valueB'}, {'attributeX': 'valueA'}] --
            new ContainsUniqueValues(
                mode: ContainsUniqueValues::MODE_LOOSE
            )
        );

        $this->buildViolation((new ContainsUniqueValues())->message)
            ->assertRaised();
    }

    /**
     * @dataProvider \App\Tests\Data\Providers\IterableDataProvider::getDuplicateObjectsContainingIterables()
     */
    public function testDuplicateObjectsIterableIsValidInLooseModeWithCustomMessage(iterable $duplicateObjectsIterableData): void
    {
        $this->validator->validate(
            $duplicateObjectsIterableData, // <-- [{'attributeX': 'valueA'}, {'attributeX': 'valueB'}, {'attributeX': 'valueA'}] --
            new ContainsUniqueValues(
                mode:    ContainsUniqueValues::MODE_LOOSE,
                message: 'Custom message.'
            )
        );

        $this->buildViolation('Custom message.')
            ->assertRaised();
    }
}

class NonContainsUniqueValuesConstraint extends Constraint {
    
}