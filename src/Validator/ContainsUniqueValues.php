<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Attribute passing values to validator checking uniqueness of the values
 * within iterable data structure.
 * 
 * @author Rastislav Bostik <rastislav.bostik@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ContainsUniqueValues extends Constraint
{
     /**
     * Whether to compare items of iterable data structure
     * using identity comparison taking into consideration
     * types of item values.
     * (by using serialize() to fetch the typed-value hash)
     */
    public const MODE_STRICT = 'strict';
    /**
     * Whether to compare items of iterable data structure
     * through casting them to string values.
     * (by using array_unique($iterableAsArray, SORT_STRING))
     */
    public const MODE_LOOSE  = 'loose';

    public const MODES = [
        self::MODE_STRICT,
        self::MODE_LOOSE,
    ];

    /**
     * Mode of iterable items uniqueness checking
     * @var string
     */
    public string $mode    = self::MODE_STRICT;
    /**
     * Error message value
     * @var string
     */
    public string $message = 'The collection contains duplicate values.';

    public function __construct(
        ?string $mode    = null,
        ?string $message = null,
        ?array $groups   = null,
        mixed $payload   = null,
    ) {
        if (null !== $mode && !\in_array($mode, self::MODES, true)) {
            throw new InvalidArgumentException('The "mode" parameter value is not valid.');
        }

        parent::__construct([], $groups, $payload);

        $this->mode    = $mode ?? $this->mode;
        $this->message = $message ?? $this->message;
    }
}