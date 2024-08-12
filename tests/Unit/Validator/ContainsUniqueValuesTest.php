<?php

namespace App\Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use App\Validator\ContainsUniqueValues;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Loader\AttributeLoader;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

class ContainsUniqueValuesTest extends TestCase
{    
    public function testConstructorModeNull(): void
    {
        $subject = new ContainsUniqueValues(mode: null);

        // implicit internal value MODE_STRICT expected
        $this->assertEquals(ContainsUniqueValues::MODE_STRICT, $subject->mode);
    }

    public function testConstructorModeStrict(): void
    {
        $subject = new ContainsUniqueValues(mode: ContainsUniqueValues::MODE_STRICT);

        $this->assertEquals(ContainsUniqueValues::MODE_STRICT, $subject->mode);
    }
    
    public function testConstructorModeLoose(): void
    {
        $subject = new ContainsUniqueValues(mode: ContainsUniqueValues::MODE_LOOSE);

        $this->assertEquals(ContainsUniqueValues::MODE_LOOSE, $subject->mode);
    }

    public function testConstructorModeUnknownTriggeringException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "mode" parameter value is not valid.');
        new ContainsUniqueValues(mode: 'Unknown Mode');
    }

    public function testConstructorMessageNull(): void
    {
        $subject = new ContainsUniqueValues(message: null);

        // implicit internal value of message is available
        $this->assertEquals('The collection contains duplicate values.', $subject->message);
    }

    public function testConstructorMessageCustom(): void
    {
        $subject = new ContainsUniqueValues(message: 'Custom message.');

        $this->assertEquals('Custom message.', $subject->message);
    }

    public function testAttribute(): void
    {
        $metadata = new ClassMetadata(ContainsUniqueValuesTestDummy::class);
        (new AttributeLoader())->loadClassMetadata($metadata);

        [$aConstraint] = $metadata->properties['a']->constraints;
        self::assertSame(ContainsUniqueValues::MODE_STRICT,           $aConstraint->mode);
        self::assertSame('The collection contains duplicate values.', $aConstraint->message);

        [$bConstraint] = $metadata->properties['b']->constraints;
        self::assertSame(ContainsUniqueValues::MODE_LOOSE,            $bConstraint->mode);
        self::assertSame('Custom message.',                           $bConstraint->message);

        [$cConstraint] = $metadata->properties['c']->getConstraints();
        self::assertSame(ContainsUniqueValues::MODE_STRICT,           $cConstraint->mode);
        self::assertSame('The collection contains duplicate values.', $cConstraint->message);
    }
}

class ContainsUniqueValuesTestDummy
{
    #[ContainsUniqueValues]
    protected $a;

    #[ContainsUniqueValues(
        mode: ContainsUniqueValues::MODE_LOOSE,
        message: 'Custom message.', 
    )]
    private $b;

    #[ContainsUniqueValues(
        mode: ContainsUniqueValues::MODE_STRICT,
    )]
    private $c;
}