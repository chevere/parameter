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

use BadMethodCallException;
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
use ReflectionParameter;
use SensitiveParameter;
use Throwable;
use function Chevere\Message\message;

/**
 * Cast a variable to a CastInterface instance.
 *
 * @param mixed $variable The variable to cast.
 * @param string|int ...$key The key to access in the array (array reduce)
 */
function cast(mixed $variable, string|int ...$key): CastInterface
{
    if ($key !== []) {
        if (! is_array($variable)) {
            throw new BadMethodCallException(
                (string) message(
                    'Argument must be array-accessible, %type% provided',
                    type: gettype($variable)
                )
            );
        }
        $fn = function ($carry, $item) {
            if (array_key_exists($item, $carry)) {
                return $carry[$item];
            }

            throw new InvalidArgumentException(
                (string) message(
                    'Key `%key%` not found in array',
                    key: $item
                )
            );
        };
        $variable = array_reduce($key, $fn, $variable);
    }

    return new Cast($variable);
}

function null(
    string $description = '',
): NullParameterInterface {
    return new NullParameter($description);
}

function mixed(
    string $description = '',
    bool $sensitive = false,
): MixedParameterInterface {
    return new MixedParameter($description, $sensitive);
}

function object(
    string $className,
    string $description = '',
    bool $sensitive = false,
): ObjectParameterInterface {
    $parameter = new ObjectParameter($description, $sensitive);

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
    bool $sensitive = false,
): IterableParameterInterface {
    $K ??= int();

    return (new IterableParameter($V, $K, $description))->withIsSensitive($sensitive);
}

function union(
    ParameterInterface $one,
    ParameterInterface $two,
    ParameterInterface ...$more
): UnionParameterInterface {
    $parameters = parameters($one, $two, ...$more);

    return new UnionParameter($parameters);
}

/**
 * Same as `union()` but with a null parameter already included.
 */
function unionNull(
    ParameterInterface ...$more
): UnionParameterInterface {
    $parameters = parameters(null(), ...$more);

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
    array $arguments
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
        $message = $e->getMessage();
        if (! str_ends_with($name, '*iterable')) {
            $needle = "[{$name}]: ";
            $pos = strpos($message, $needle);
            if ($pos !== false) {
                $message = substr_replace($message, '', $pos, strlen($needle));
            }
        }

        throw new InvalidArgumentException(
            (string) message(
                'Argument [%name%]: %message%',
                name: $name,
                message: $message,
            )
        );
    }
}

function toUnionParameter(string ...$types): UnionParameterInterface
{
    $parameters = [];
    foreach ($types as $type) {
        $parameters[] = toParameter($type);
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
    string|int ...$name
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
    string|int ...$name
): Iterator {
    $parameters = getParameters($parameter);
    foreach ($name as $item) {
        $item = strval($item);
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
    $hasVariadic = false;
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
                /** @var ParameterInterface $push */
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
        if ($reflectionParameter->isVariadic()) {
            $parameters = $parameters->withIsVariadic(true);
        }
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
    $isSensitive = $reflection->getAttributes(SensitiveParameter::class) !== [];
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

    return $attribute->newInstance()->withIsSensitive($isSensitive);
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
        $return($result); // @phpstan-ignore-line
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

/**
 * Returns an string representation of a user provided value.
 *
 * Will return " `value`" with leading space and wrap in backtick.
 * If the value is empty or sensitive, will return an empty string.
 *
 * @return string A markdown formatted string.
 */
function valMd(mixed $value, bool $isSensitive = false): string
{
    if ($isSensitive) {
        return '';
    }
    if ($value === null) {
        $value = 'null';
    }
    if (is_scalar($value)) {
        $value = strval($value);
    } else {
        $value = var_export($value, true);
        $value = preg_replace('/\s+/', ' ', $value);
    }

    return $value === ''
        ? ''
        : " `{$value}`";
}
