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

use Chevere\Parameter\Exceptions\ParameterException;
use Chevere\Parameter\Interfaces\ArgumentsInterface;
use LogicException;
use ReflectionFunction;
use ReflectionMethod;
use Throwable;
use function Chevere\Message\message;
use function Chevere\Parameter\reflectionToParameters;

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

    return $array->parameters()->__invoke(
        // @phpstan-ignore-next-line
        ...$arguments[$name]
    );
}

/**
 * Assert argument(s) against parameter attribute rules.
 *
 * @param string ...$name Argument name(s) or empty to validate all arguments.
 */
function assertArguments(string ...$name): void
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
    if ($name === []) {
        $parameters(...$arguments);

        return;
    }
    foreach ($name as $item) {
        if ($parameters->optionalKeys()->contains($item)
            && ! array_key_exists($item, $arguments)
        ) {
            continue;
        }

        try {
            if (! $parameters->has($item)) {
                throw new LogicException(
                    (string) message(
                        'Parameter `%name%` not found',
                        name: $item,
                    )
                );
            }
            $parameter = $parameters->get($item);
            $parameter->__invoke($arguments[$item]);
        } catch (Throwable $e) {
            $invoker = $trace[0];
            $file = $invoker['file'] ?? 'na';
            $line = $invoker['line'] ?? 0;

            throw new ParameterException($e->getMessage(), $e, $file, $line);
        }
    }
}

/**
 * Assert return value against _return rules.
 *
 * @param mixed $value Return value to assert
 */
function assertReturn(mixed $value = null): mixed
{
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    $caller = $trace[1];
    $class = $caller['class'] ?? null;
    $method = $caller['function'];
    $reflection = $class
        ? new ReflectionMethod($class, $method)
        : new ReflectionFunction($method);
    $attribute = $reflection->getAttributes(_return::class)[0]
        ?? null;
    $return = $attribute?->newInstance()
        ?? new _return(new _mixed());

    return $return($value);
}
