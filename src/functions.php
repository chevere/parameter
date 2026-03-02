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
use Chevere\Parameter\Attributes\_return;
use Chevere\Parameter\Exceptions\AttributeNotFoundException;
use Chevere\Parameter\Exceptions\ParameterException;
use Chevere\Parameter\Exceptions\ReturnException;
use Chevere\Parameter\Interfaces\ArgumentsInterface;
use Chevere\Parameter\Interfaces\ArrayParameterInterface;
use Chevere\Parameter\Interfaces\BoolParameterInterface;
use Chevere\Parameter\Interfaces\FloatParameterInterface;
use Chevere\Parameter\Interfaces\IntParameterInterface;
use Chevere\Parameter\Interfaces\IterableParameterInterface;
use Chevere\Parameter\Interfaces\MixedParameterInterface;
use Chevere\Parameter\Interfaces\NullParameterInterface;
use Chevere\Parameter\Interfaces\ObjectParameterInterface;
use Chevere\Parameter\Interfaces\ParameterAttributeInterface;
use Chevere\Parameter\Interfaces\ParameterInterface;
use Chevere\Parameter\Interfaces\ParametersAccessInterface;
use Chevere\Parameter\Interfaces\ParametersInterface;
use Chevere\Parameter\Interfaces\TypedInterface;
use Chevere\Parameter\Interfaces\TypeInterface;
use Chevere\Parameter\Interfaces\UnionParameterInterface;
use InvalidArgumentException;
use Iterator;
use LogicException;
use ReflectionAttribute;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use SebastianBergmann\Type\Parameter;
use SensitiveParameter;
use Throwable;
use function Chevere\Message\message;

/**
 * Type-safe access for a variable.
 *
 * @param mixed $variable The variable to type-safe access.
 * @param string|int ...$key The key to access in the array (array reduce)
 */
function typed(mixed $variable, string|int ...$key): TypedInterface
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

    return new Typed($variable);
}

function null(
    string $description = '',
    bool $sensitive = false,
    string $label = '',
): NullParameterInterface {
    $parameter = new NullParameter($description, $sensitive);
    if ($label !== '') {
        $parameter = $parameter->withLabel($label);
    }

    return $parameter;
}

function mixed(
    string $description = '',
    bool $sensitive = false,
    string $label = '',
): MixedParameterInterface {
    $parameter = new MixedParameter($description, $sensitive);
    if ($label !== '') {
        $parameter = $parameter->withLabel($label);
    }

    return $parameter;
}

/**
 * @param class-string $className
 */
function object(
    string $className,
    string $description = '',
    bool $sensitive = false,
    string $label = '',
): ObjectParameterInterface {
    $parameter = new ObjectParameter($description, $sensitive);
    if ($label !== '') {
        $parameter = $parameter->withLabel($label);
    }

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
    string $label = '',
): IterableParameterInterface {
    $K ??= int();
    $parameter = new IterableParameter($V, $K, $description);
    if ($sensitive) {
        $parameter = $parameter->withIsSensitive($sensitive);
    }
    if ($label !== '') {
        $parameter = $parameter->withLabel($label);
    }

    return $parameter;
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
 * @param array<int|string, mixed> $arguments
 */
function arguments(
    ParametersInterface|ParametersAccessInterface $parameters,
    array $arguments
): ArgumentsInterface {
    $parameters = getParameters($parameters);

    return new Arguments($parameters, $arguments);
}

/**
 * Same as `arguments()` but casting the provided arguments to the defined
 * parameter types
 *
 * @param array<int|string, mixed> $arguments
 */
function castArguments(
    ParametersInterface|ParametersAccessInterface $parameters,
    array $arguments
): ArgumentsInterface {
    return arguments($parameters, castValues($parameters, $arguments));
}

/**
 * Returns the provided values casted to the defined parameter types.
 *
 * @param array<int|string, mixed> $values
 * @return array<int|string, mixed>
 */
function castValues(
    ParametersInterface|ParametersAccessInterface $parameters,
    array $values
): array {
    $return = [];
    $parameters = getParameters($parameters);
    $isVariadic = $parameters->isVariadic();
    foreach ($values as $key => $value) {
        $key = (string) $key;
        if ($parameters->has($key) === false) {
            if ($isVariadic) {
                $return[$key] = $value;

                continue;
            }

            throw new InvalidArgumentException("Unknown parameter: {$key}");
        }
        $parameter = $parameters->get($key);
        $return[$key] = match (true) {
            $parameter instanceof BoolParameterInterface => (bool) $value,
            $parameter instanceof IntParameterInterface => (int) $value, // @phpstan-ignore-line
            $parameter instanceof FloatParameterInterface => (float) $value, // @phpstan-ignore-line
            $parameter instanceof UnionParameterInterface => castUnion($parameter, $value),
            default => $value,
        };
    }

    return $return;
}

/**
 * Attempt to cast a value to the most appropriate member type declared
 * in a union parameter.
 *
 * - For string inputs that "look like" numbers, prefer numeric casts
 *   (integer-like strings → int, non-integer numeric strings → float)
 *   when those primitives are present in the union. If numeric members
 *   are not present, `string` is used if available.
 * - For non-string inputs, the value's runtime type is matched against
 *   the union members and cast to the first matching primitive if found.
 * - If no matching primitive is present in the union, the original
 *   value is returned unchanged.
 *
 * This allows `castValues()` to convert e.g. `"123"` → `123` or
 * `"12.3"` → `12.3` when the union contains `int`/`float` accordingly.
 *
 * @param UnionParameterInterface $union The union parameter definition
 * @param mixed $value The value to cast
 * @return mixed Cast value or original value if no suitable member found
 */
function castUnion(
    UnionParameterInterface $union,
    mixed $value
): mixed {
    $available = [];
    foreach ($union->parameters() as $parameter) {
        $available[] = $parameter->type()->primitive();
    }
    $available = array_unique($available);
    $candidates = [];
    if (is_string($value)) {
        $candidates = match (true) {
            preg_match('/^-?\d+$/', $value) === 1 => [TypeInterface::INT, TypeInterface::FLOAT, TypeInterface::STRING],
            is_numeric($value) => [TypeInterface::FLOAT, TypeInterface::STRING, TypeInterface::INT],
            default => [TypeInterface::STRING],
        };
    } else {
        $runtime = getType($value);
        $candidates = [$runtime];
        if ($runtime === TypeInterface::INT && in_array(TypeInterface::FLOAT, $available, true)) {
            $candidates[] = TypeInterface::FLOAT;
        }
        if (in_array(TypeInterface::STRING, $available, true)) {
            $candidates[] = TypeInterface::STRING;
        }
    }
    foreach ($candidates as $candidate) {
        if (in_array($candidate, $available, true)) {
            return match ($candidate) {
                TypeInterface::INT => (int) $value, // @phpstan-ignore-line
                TypeInterface::FLOAT => (float) $value, // @phpstan-ignore-line
                TypeInterface::BOOL => (bool) $value,
                TypeInterface::STRING => (string) $value, // @phpstan-ignore-line
                default => $value,
            };
        }
    }

    return $value;
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

function takeOne(
    ParametersAccessInterface|ParametersInterface $parameter,
    string|int $name
): ParameterInterface {
    return getParameters($parameter)->get(strval($name));
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

function parameterAttribute(
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

function reflectionToParameters(
    ReflectionFunction|ReflectionMethod $reflection
): ParametersInterface {
    $parameters = parameters();
    foreach ($reflection->getParameters() as $reflectionParameter) {
        $parameter = reflectionToParameter($reflectionParameter);
        $withMethod = match ($reflectionParameter->isOptional()) {
            true => 'withOptional',
            default => 'withRequired',
        };
        $parameters = $parameters->{$withMethod}(
            $reflectionParameter->getName(),
            $parameter
        );
        if ($reflectionParameter->isVariadic()) {
            $parameters = $parameters->withIsVariadic(true);
        }
    }

    return $parameters;
}

function reflectionToReturn(
    ReflectionFunction|ReflectionMethod $reflection
): ParameterInterface {
    $attributes = $reflection->getAttributes(_return::class);
    if ($attributes === []) {
        $returnType = (string) $reflection->getReturnType();

        return match ($returnType) {
            '' => mixed(),
            'void' => null(),
            default => toParameter($returnType),
        };
    }
    /** @var ReflectionAttribute<_return> $attribute */
    $attribute = $attributes[0];

    return $attribute->newInstance()->parameter();
}

function reflectionToParameter(
    ReflectionProperty|ReflectionParameter $reflection
): ParameterInterface {
    return (new ReflectionParameterTyped($reflection))->parameter();
}

function reflectedParameterAttribute(
    ReflectionParameter|ReflectionProperty $reflection,
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
