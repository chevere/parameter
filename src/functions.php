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

namespace Chevere\Parameter;

use ArrayAccess;
use Chevere\Parameter\Attributes\ReturnAttr;
use Chevere\Parameter\Exceptions\AttributeNotFoundException;
use Chevere\Parameter\Exceptions\ParameterException;
use Chevere\Parameter\Exceptions\ReturnException;
use Chevere\Parameter\Interfaces\ArgumentsInterface;
use Chevere\Parameter\Interfaces\ArrayParameterInterface;
use Chevere\Parameter\Interfaces\CastInterface;
use Chevere\Parameter\Interfaces\IterableParameterInterface;
use Chevere\Parameter\Interfaces\MixedParameterInterface;
use Chevere\Parameter\Interfaces\NullParameterInterface;
use Chevere\Parameter\Interfaces\ObjectParameterInterface;
use Chevere\Parameter\Interfaces\ParameterAttributeInterface;
use Chevere\Parameter\Interfaces\ParameterInterface;
use Chevere\Parameter\Interfaces\ParametersAccessInterface;
use Chevere\Parameter\Interfaces\ParametersInterface;
use Chevere\Parameter\Interfaces\TypeInterface;
use Chevere\Parameter\Interfaces\UnionParameterInterface;
use InvalidArgumentException;
use Iterator;
use LogicException;
use ReflectionAttribute;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use Throwable;
use function Chevere\Message\message;

function cast(mixed $argument): CastInterface
{
    return new Cast($argument);
}

function null(
    string $description = '',
): NullParameterInterface {
    return new NullParameter($description);
}

function mixed(
    string $description = '',
): MixedParameterInterface {
    return new MixedParameter($description);
}

function object(
    string $className,
    string $description = '',
): ObjectParameterInterface {
    $parameter = new ObjectParameter($description);

    return $parameter->withClassName($className);
}

/**
 * @param ParameterInterface $V Iterable value parameter
 * @param ParameterInterface|null $K Iterable key parameter
 */
function iterable(
    ParameterInterface $V,
    ?ParameterInterface $K = null,
    string $description = '',
): IterableParameterInterface {
    $K ??= int();

    return new IterableParameter($V, $K, $description);
}

function union(
    ParameterInterface $one,
    ParameterInterface $two,
    ParameterInterface ...$more
): UnionParameterInterface {
    $parameters = parameters($one, $two, ...$more);

    return new UnionParameter($parameters);
}

function parameters(
    ParameterInterface ...$required,
): ParametersInterface {
    return new Parameters(...$required);
}

/**
 * @phpstan-ignore-next-line
 */
function arguments(
    ParametersInterface|ParametersAccessInterface $parameters,
    array|ArrayAccess $arguments
): ArgumentsInterface {
    $parameters = getParameters($parameters);

    return new Arguments($parameters, $arguments);
}

function assertNamedArgument(
    string $name,
    ParameterInterface $parameter,
    mixed $argument
): ArgumentsInterface {
    $parameters = parameters(
        ...[
            $name => $parameter,
        ]
    );
    $arguments = [
        $name => $argument,
    ];

    try {
        return arguments($parameters, $arguments);
    } catch (Throwable $e) {
        throw new InvalidArgumentException(
            (string) message(
                'Argument [%name%]: %message%',
                name: $name,
                message: $e->getMessage(),
            )
        );
    }
}

function toUnionParameter(array $types): UnionParameterInterface
{
    $parameters = [];

    /** @var ReflectionNamedType $type */
    foreach ($types as $type) {
        $parameters[] = toParameter($type->getName());
    }

    $parameters = parameters(...$parameters);

    return new UnionParameter($parameters);
}

function toParameter(string $type): ParameterInterface
{
    $class = TypeInterface::TYPE_TO_PARAMETER[$type]
        ?? null;
    if ($class === null) {
        $class = TypeInterface::TYPE_TO_PARAMETER['object'];
        $className = $type;
    }
    $arguments = [];
    if ($class === IterableParameter::class) {
        $parameter = iterable(mixed());
    } else {
        $parameter = new $class(...$arguments);
    }
    if (isset($className)) {
        // @phpstan-ignore-next-line
        $parameter = $parameter->withClassName($className);
    }

    return $parameter;
}

function arrayFrom(
    ParametersAccessInterface|ParametersInterface $parameter,
    string ...$name
): ArrayParameterInterface {
    return arrayp(
        ...takeFrom($parameter, ...$name)
    );
}

/**
 * @return array<string>
 */
function takeKeys(
    ParametersAccessInterface|ParametersInterface $parameter,
): array {
    return getParameters($parameter)->keys();
}

/**
 * @return Iterator<string, ParameterInterface>
 */
function takeFrom(
    ParametersAccessInterface|ParametersInterface $parameter,
    string ...$name
): Iterator {
    $parameters = getParameters($parameter);
    foreach ($name as $item) {
        yield $item => $parameters->get($item);
    }
}

function parametersFrom(
    ParametersAccessInterface|ParametersInterface $parameter,
    string ...$name
): ParametersInterface {
    $parameters = getParameters($parameter);

    return parameters(
        ...takeFrom($parameters, ...$name)
    );
}

function getParameters(
    ParametersAccessInterface|ParametersInterface $parameter
): ParametersInterface {
    return $parameter instanceof ParametersAccessInterface
        ? $parameter->parameters()
        : $parameter;
}

/**
 * Retrieves the type of a variable as defined by this library.
 */
function getType(mixed $variable): string
{
    $type = \gettype($variable);

    return match ($type) {
        'integer' => 'int',
        'boolean' => 'bool',
        'double' => 'float',
        'NULL' => 'null',
        default => $type,
    };
}

/**
 * Retrieves a Parameter attribute instance from a function or method parameter.
 */
function parameterAttr(
    string $parameter,
    string $function,
    string $class = ''
): ParameterAttributeInterface {
    $reflection = $class !== ''
        ? new ReflectionMethod($class, $function)
        : new ReflectionFunction($function);
    $parameters = $reflection->getParameters();
    foreach ($parameters as $parameterReflection) {
        if ($parameterReflection->getName() === $parameter) {
            return reflectedParameterAttribute($parameterReflection);
        }
    }

    throw new LogicException(
        (string) message(
            "Parameter `%name%` doesn't exists",
            name: $parameter
        )
    );
}

/**
 * Get Parameters from a function or method reflection.
 */
function reflectionToParameters(
    ReflectionFunction|ReflectionMethod $reflection
): ParametersInterface {
    $parameters = parameters();
    foreach ($reflection->getParameters() as $reflectionParameter) {
        try {
            $push = reflectedParameterAttribute($reflectionParameter);
        } catch (AttributeNotFoundException) {
            $push = new ReflectionParameterTyped($reflectionParameter);
        }
        $push = $push->parameter();
        if ($reflectionParameter->isDefaultValueAvailable()
            && $reflectionParameter->getDefaultValue() !== null
            && $push->default() === null
        ) {
            try {
                $push = $push->withDefault($reflectionParameter->getDefaultValue());
            } catch (Throwable $e) {
                $name = $reflectionParameter->getName();
                $class = $reflectionParameter->getDeclaringClass()?->getName() ?? null;
                $function = $reflectionParameter->getDeclaringFunction()->getName();
                $caller = match (true) {
                    $class === null => $function,
                    default => $class . '::' . $function,
                };

                throw new InvalidArgumentException(
                    (string) message(
                        'Unable to use default value for parameter `%name%` in `%caller%`: %message%',
                        name: $name,
                        caller: $caller,
                        message: $e->getMessage(),
                    )
                );
            }
        }
        $withMethod = match ($reflectionParameter->isOptional()) {
            true => 'withOptional',
            default => 'withRequired',
        };

        $parameters = $parameters->{$withMethod}(
            $reflectionParameter->getName(),
            $push
        );
    }

    return $parameters;
}

/**
 * Get a return Parameter from a function or method reflection.
 */
function reflectionToReturn(
    ReflectionFunction|ReflectionMethod $reflection
): ParameterInterface {
    $attributes = $reflection->getAttributes(ReturnAttr::class);
    if ($attributes === []) {
        $returnType = (string) $reflection->getReturnType();

        return toParameter($returnType);
    }
    /** @var ReflectionAttribute<ReturnAttr> $attribute */
    $attribute = $attributes[0];

    return $attribute->newInstance()->parameter();
}

function reflectedParameterAttribute(
    ReflectionParameter $reflection,
): ParameterAttributeInterface {
    $attributes = $reflection->getAttributes(
        ParameterAttributeInterface::class,
        ReflectionAttribute::IS_INSTANCEOF
    );
    if ($attributes === []) {
        throw new AttributeNotFoundException(
            (string) message(
                'No `%type%` attribute for parameter `%name%`',
                type: ParameterAttributeInterface::class,
                name: $reflection->getName()
            )
        );
    }
    /** @var ReflectionAttribute<ParameterAttributeInterface> $attribute */
    $attribute = $attributes[0];

    return $attribute->newInstance();
}

function validated(callable $callable, mixed ...$args): mixed
{
    // @phpstan-ignore-next-line
    $reflection = new ReflectionFunction($callable);

    try {
        $parameters = reflectionToParameters($reflection);
        $return = reflectionToReturn($reflection);
        $parameters(...$args);
    } catch (Throwable $e) {
        // // @infection-ignore-all
        throw new ParameterException(
            ...getExceptionArguments($e, $reflection),
        );
    }
    $result = $callable(...$args);

    try {
        /** @var callable $return */
        $return($result);
    } catch (Throwable $e) {
        // @infection-ignore-all
        throw new ReturnException(
            ...getExceptionArguments($e, $reflection),
        );
    }

    return $return;
}

/**
 * @return array{0: string, 1: Throwable, 2: string, 3: int}
 */
function getExceptionArguments(Throwable $e, ReflectionFunction $reflection): array
{
    // @infection-ignore-all
    $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
    $function = $reflection->getName();
    $message = (string) message(
        '`%actor%` %exception% → %message%',
        exception: $e::class,
        actor: $function,
        message: $e->getMessage(),
    );

    // @infection-ignore-all
    return [
        $message,
        $e,
        $caller['file'] ?? 'na',
        $caller['line'] ?? 0,
    ];
}
