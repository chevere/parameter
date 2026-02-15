<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Parameter\Interfaces;

use Chevere\Parameter\ArrayParameter;
use Chevere\Parameter\BoolParameter;
use Chevere\Parameter\FloatParameter;
use Chevere\Parameter\IntParameter;
use Chevere\Parameter\IterableParameter;
use Chevere\Parameter\MixedParameter;
use Chevere\Parameter\NullParameter;
use Chevere\Parameter\ObjectParameter;
use Chevere\Parameter\StringParameter;

/**
 * Describes the component in charge of dynamic type validation.
 */
interface TypeInterface
{
    public const BOOL = 'bool';

    public const INT = 'int';

    public const FLOAT = 'float';

    public const STRING = 'string';

    public const ARRAY = 'array';

    public const OBJECT = 'object';

    public const CALLABLE = 'callable';

    public const ITERABLE = 'iterable';

    public const RESOURCE = 'resource';

    public const NULL = 'null';

    public const MIXED = 'mixed';

    public const UNION = 'union';

    public const PRIMITIVE_CLASS_NAME = 'className';

    public const PRIMITIVE_INTERFACE_NAME = 'interfaceName';

    /**
     * Type arguments accepted.
     */
    public const TYPE_ARGUMENTS = [
        self::ARRAY,
        self::BOOL,
        self::CALLABLE,
        self::FLOAT,
        self::INT,
        self::ITERABLE,
        self::NULL,
        self::MIXED,
        self::OBJECT,
        self::RESOURCE,
        self::STRING,
        self::UNION,
    ];

    /**
     * @var array<string, string>
     */
    public const TYPE_TO_PARAMETER = [
        'array' => ArrayParameter::class,
        'bool' => BoolParameter::class,
        'float' => FloatParameter::class,
        'int' => IntParameter::class,
        'iterable' => IterableParameter::class,
        'string' => StringParameter::class,
        'object' => ObjectParameter::class,
        'null' => NullParameter::class,
        'mixed' => MixedParameter::class,
        'integer' => IntParameter::class,
        'boolean' => BoolParameter::class,
        'double' => FloatParameter::class,
        'NULL' => NullParameter::class,
    ];

    /**
     * Returns the type primitive (array, bool, object, ..., className, interfaceName).
     */
    public function primitive(): string;

    /**
     * Returns the type hinting.
     *
     * It will return either the class name, interface, or the primitive.
     */
    public function typeHinting(): string;

    /**
     * Indicates if type is scalar.
     */
    public function isScalar(): bool;
}
