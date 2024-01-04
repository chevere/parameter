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

namespace Chevere\Parameter\Attributes;

use Chevere\Parameter\Interfaces\ArgumentsInterface;
use Chevere\Parameter\Interfaces\ParameterInterface;
use LogicException;
use ReflectionFunction;
use ReflectionMethod;
use Throwable;
use function Chevere\Message\message;
use function Chevere\Parameter\parameterAttr;
use function Chevere\Parameter\reflectionToParameters;

function stringAttr(string $name): StringAttr
{
    $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];

    // @phpstan-ignore-next-line
    return parameterAttr($name, $caller);
}

function enumAttr(string $name): EnumAttr
{
    $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];

    // @phpstan-ignore-next-line
    return parameterAttr($name, $caller);
}

function intAttr(string $name): IntAttr
{
    $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];

    // @phpstan-ignore-next-line
    return parameterAttr($name, $caller);
}

function floatAttr(string $name): FloatAttr
{
    $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];

    // @phpstan-ignore-next-line
    return parameterAttr($name, $caller);
}

function boolAttr(string $name): BoolAttr
{
    $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];

    // @phpstan-ignore-next-line
    return parameterAttr($name, $caller);
}

function nullAttr(string $name): NullAttr
{
    $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];

    // @phpstan-ignore-next-line
    return parameterAttr($name, $caller);
}

function arrayAttr(string $name): ArrayAttr
{
    $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];

    // @phpstan-ignore-next-line
    return parameterAttr($name, $caller);
}

function iteratorAttr(string $name): IterableAttr
{
    $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];

    // @phpstan-ignore-next-line
    return parameterAttr($name, $caller);
}

function returnAttr(): ReturnAttr
{
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    $caller = $trace[1];
    $class = $caller['class'] ?? null;
    $method = $caller['function'];
    $convention = "{$class}::return";
    $reflection = $class
        ? new ReflectionMethod($class, $method)
        : new ReflectionFunction($method);
    $attribute = $reflection->getAttributes(ReturnAttr::class)[0] ?? null;
    if ($attribute === null) {
        if (! is_callable($convention)) {
            throw new LogicException(
                (string) message(
                    'No applicable return rules to validate',
                )
            );
        }
        $parameter = $convention();
        if (! $parameter instanceof ParameterInterface) {
            throw new LogicException(
                (string) message(
                    'Callable `%callable%` must return a `%type%` instance',
                    callable: $convention,
                    type: ParameterInterface::class
                )
            );
        }
    } else {
        $attribute = $attribute->newInstance();
    }

    /** @var ReturnAttr $attribute */
    return $attribute;
}

/**
 * Get Arguments for an array parameter.
 */
function arrayArguments(string $name): ArgumentsInterface
{
    $caller = debug_backtrace(0, 2)[1];
    $class = $caller['class'] ?? false;
    $method = $caller['function'];
    $args = $caller['args'] ?? [];
    $reflection = $class
        ? new ReflectionMethod($class, $method)
        : new ReflectionFunction($method);
    $parameters = reflectionToParameters($reflection);
    $parameters->assertHas($name);
    $array = match ($parameters->optionalKeys()->contains($name)) {
        true => $parameters->optional($name)->array(),
        default => $parameters->required($name)->array(),
    };
    $pos = -1;
    $arguments = [];
    foreach ($parameters->keys() as $named) {
        $pos++;
        $arguments[$named] = match (true) {
            array_key_exists($pos, $args) => $args[$pos],
            default => $parameters->get($named)->default(),
        };
    }

    return $array->parameters()->__invoke(...$arguments[$name]);
}

/**
 * Validates argument `$name` against parameter attribute rules.
 *
 * @param ?string $name Argument name or `null` to validate all arguments.
 */
function valid(?string $name = null): void
{
    $trace = debug_backtrace(0, 2);
    $caller = $trace[1];
    $class = $caller['class'] ?? false;
    $method = $caller['function'];
    $args = $caller['args'] ?? [];
    $reflection = $class
        ? new ReflectionMethod($class, $method)
        : new ReflectionFunction($method);
    $parameters = reflectionToParameters($reflection);
    $pos = -1;
    $arguments = [];
    foreach ($parameters->keys() as $named) {
        $pos++;
        if (! isset($args[$pos])) {
            continue;
        }
        $arguments[$named] = $args[$pos];
    }
    if ($name === null) {
        $parameters(...$arguments);

        return;
    }

    try {
        $parameter = $parameters->get($name);
        if ($parameters->optionalKeys()->contains($name) && ! array_key_exists($name, $arguments)) {
            return;
        }
        $parameter->__invoke($arguments[$name]);
    } catch (Throwable $e) {
        $invoker = $trace[0];

        throw new $e(
            (string) message(
                '%message% → %invokedAt%',
                message: $e->getMessage(),
                // @phpstan-ignore-next-line
                invokedAt: $invoker['file'] . ':' . $invoker['line'],
            )
        );
    }
}
