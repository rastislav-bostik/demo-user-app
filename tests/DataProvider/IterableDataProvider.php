<?php

namespace App\Tests\DataProvider;

/**
 * Source of various iterable data sets for testing
 * usable as PHPUnit @dataProvider.
 * (empty, unique & duplicit values cotaining, ...)
 */
class IterableDataProvider
{
    private const EMPTY_DATASET = [];

    private const STRING_UNIQUE_DATASET    = ['a', 'b', 'c'];
    private const STRING_DUPLICATE_DATASET = ['a', 'b', 'a'];

    private const TYPE_LOOSE_UNIQUE_DATASET    = [true, 'false', 123];
    private const TYPE_LOOSE_DUPLICATE_DATASET = [true, 'false', 1];

    private const TYPE_STRICT_UNIQUE_DATASET    = self::TYPE_LOOSE_DUPLICATE_DATASET;
    private const TYPE_STRICT_DUPLICATE_DATASET = [true, 'false', true];

    /**
     * Set of empty iterable values of various kind
     * (array, \Generator, \Iterator, \IteratorAggregate, ...)
     * 
     * @return iterable[][]
     */
    public static function getEmptyIterables(): array
    {
        return [
            'empty-array'              => [self::EMPTY_DATASET],
            'empty-generator'          => [self::getGenerator(self::EMPTY_DATASET)],
            'empty-iterator'           => [self::getIterator(self::EMPTY_DATASET)],
            'empty-iterator-aggregate' => [self::getIteratorAggreagte(self::EMPTY_DATASET)]
        ];
    }

    /**
     * Set of iterables of various kind providing unique string value
     * (array, \Generator, \Iterator, \IteratorAggregate, ...)
     * 
     * @return iterable[][]
     */
    public static function getUniqueStringValuesContainingIterables(): array
    {
        return [
            'empty-array'              => [self::STRING_UNIQUE_DATASET],
            'empty-generator'          => [self::getGenerator(self::STRING_UNIQUE_DATASET)],
            'empty-iterator'           => [self::getIterator(self::STRING_UNIQUE_DATASET)],
            'empty-iterator-aggregate' => [self::getIteratorAggreagte(self::STRING_UNIQUE_DATASET)]
        ];
    }

    /**
     * Set of iterables of various kind providing type loose comparison unique mixed value
     * (array, \Generator, \Iterator, \IteratorAggregate, ...)
     * 
     * @return iterable[][]
     */
    public static function getUniqueTypeLooseValuesContainingIterables(): array
    {
        return [
            'empty-array'              => [self::TYPE_LOOSE_UNIQUE_DATASET],
            'empty-generator'          => [self::getGenerator(self::TYPE_LOOSE_UNIQUE_DATASET)],
            'empty-iterator'           => [self::getIterator(self::TYPE_LOOSE_UNIQUE_DATASET)],
            'empty-iterator-aggregate' => [self::getIteratorAggreagte(self::TYPE_LOOSE_UNIQUE_DATASET)]
        ];
    }

    /**
     * Set of iterables of various kind providing type strict comparison unique mixed value
     * (array, \Generator, \Iterator, \IteratorAggregate, ...)
     * 
     * @return iterable[][]
     */
    public static function getUniqueTypeStrictValuesContainingIterables(): array
    {
        return [
            'empty-array'              => [self::TYPE_STRICT_UNIQUE_DATASET],
            'empty-generator'          => [self::getGenerator(self::TYPE_STRICT_UNIQUE_DATASET)],
            'empty-iterator'           => [self::getIterator(self::TYPE_STRICT_UNIQUE_DATASET)],
            'empty-iterator-aggregate' => [self::getIteratorAggreagte(self::TYPE_STRICT_UNIQUE_DATASET)]
        ];
    }

    /**
     * Set of iterables containing set of unique objects each
     * (array, \Generator, \Iterator, \IteratorAggregate, ...)
     * 
     * @return iterable[][]
     */
    public static function getUniqueObjectsContainingIterables(): array
    {
        $objects = [
            (object) ['attributeX' => 'valueA'],
            (object) ['attributeX' => 'valueB'],
            (object) ['attributeX' => 'valueC'],
        ];

        return [
            'empty-array'              => [$objects],
            'empty-generator'          => [self::getGenerator($objects)],
            'empty-iterator'           => [self::getIterator($objects)],
            'empty-iterator-aggregate' => [self::getIteratorAggreagte($objects)]
        ];
    }

    /**
     * Set of iterables of various kind providing duplicated string values
     * (array, \Generator, \Iterator, \IteratorAggregate, ...)
     * 
     * @return iterable[][]
     */
    public static function getDuplicateStringValuesContainingIterables(): array
    {
        return [
            'empty-array'              => [self::STRING_DUPLICATE_DATASET],
            'empty-generator'          => [self::getGenerator(self::STRING_DUPLICATE_DATASET)],
            'empty-iterator'           => [self::getIterator(self::STRING_DUPLICATE_DATASET)],
            'empty-iterator-aggregate' => [self::getIteratorAggreagte(self::STRING_DUPLICATE_DATASET)]
        ];
    }
    
    /**
     * Set of iterables of various kind providing type loose comparison duplicated mixed value
     * (array, \Generator, \Iterator, \IteratorAggregate, ...)
     * 
     * @return iterable[][]
     */
    public static function getDuplicateTypeLooseValuesContainingIterables(): array
    {
        return [
            'empty-array'              => [self::TYPE_LOOSE_DUPLICATE_DATASET],
            'empty-generator'          => [self::getGenerator(self::TYPE_LOOSE_DUPLICATE_DATASET)],
            'empty-iterator'           => [self::getIterator(self::TYPE_LOOSE_DUPLICATE_DATASET)],
            'empty-iterator-aggregate' => [self::getIteratorAggreagte(self::TYPE_LOOSE_DUPLICATE_DATASET)]
        ];
    }
    
    /**
     * Set of iterables of various kind providing type strict comparison duplicated mixed value
     * (array, \Generator, \Iterator, \IteratorAggregate, ...)
     * 
     * @return iterable[][]
     */
    public static function getDuplicateTypeStrictValuesContainingIterables(): array
    {
        return [
            'empty-array'              => [self::TYPE_STRICT_DUPLICATE_DATASET],
            'empty-generator'          => [self::getGenerator(self::TYPE_STRICT_DUPLICATE_DATASET)],
            'empty-iterator'           => [self::getIterator(self::TYPE_STRICT_DUPLICATE_DATASET)],
            'empty-iterator-aggregate' => [self::getIteratorAggreagte(self::TYPE_STRICT_DUPLICATE_DATASET)]
        ];
    }

    /**
     * Set of iterables containing set of duplicate objects each
     * (array, \Generator, \Iterator, \IteratorAggregate, ...)
     * 
     * @return iterable[][]
     */
    public static function getDuplicateObjectsContainingIterables(): array
    {
        $objects = [
            (object) ['attributeX' => 'valueA'],
            (object) ['attributeX' => 'valueB'],
            (object) ['attributeX' => 'valueA'],
        ];

        return [
            'empty-array'              => [$objects],
            'empty-generator'          => [self::getGenerator($objects)],
            'empty-iterator'           => [self::getIterator($objects)],
            'empty-iterator-aggregate' => [self::getIteratorAggreagte($objects)]
        ];
    }

    /**
     * Create \Generator instance from provided array data
     * 
     * @return \Generator
     */
    protected static function getGenerator(array $sourceData): \Generator
    {
        yield from $sourceData;
    }

    /**
     * Create \Iterator instance from provided array data
     * 
     * @param array $sourceData 
     * @return \Iterator
     */
    protected static function getIterator(array $sourceData): \Iterator
    {
        return new \ArrayIterator($sourceData);
    }

    /**
     * Create \IteratorAggregate instance from provided array data
     * 
     * @param array $sourceData 
     * @return \IteratorAggregate
     */
    protected static function getIteratorAggreagte(array $sourceData): \IteratorAggregate
    {
        return new class ($sourceData) implements \IteratorAggregate {
            private array $sourceData;

            public function __construct(array $sourceData)
            {
                $this->sourceData = $sourceData;
            }

            public function getIterator(): \Traversable
            {
                return new \ArrayIterator($this->sourceData);
            }
        };
    }
}
