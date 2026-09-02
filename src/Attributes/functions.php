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

use Chevere\Parameter\Arguments;
use Chevere\Parameter\Interfaces\ArgumentsInterface;
use InvalidArgumentException;
use LogicException;
use ReflectionFunction;
use ReflectionMethod;
use Throwable;
use function Chevere\Message\message;
use function Chevere\Parameter\arguments;
use function Chevere\Parameter\parameters;
use function Chevere\Parameter\reflectionToParameters;
use function Chevere\Parameter\string;

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
        true => $parameters->optional($name)
            ->array(),
        default => $parameters->required($name)
            ->array(),
    };
    $pos = -1;
    $arguments = [];
    foreach ($parameters->keys() as $named) {
        $pos++;
        $arguments[$named] = match (true) {
            array_key_exists($pos, $args) => $args[$pos],
            default => $parameters->get($named)
                ->default(),
        };
    }

    return $array->parameters()
        ->__invoke(
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
    foreach ($reflection->getParameters() as $parameter) {
        if (! array_key_exists($parameter->getPosition(), $args)
                && $parameter->isOptional()
                && $parameter->isDefaultValueAvailable()
        ) {
            $args[$parameter->getPosition()] = $parameter->getDefaultValue();
        }
    }
    if ($name === []) {
        $parameters(...$args);

        return;
    }
    $namedKeys = $parameters->keys();
    $lastIndex = $parameters->count() - 1;
    $lastName = $namedKeys[$lastIndex];
    $errors = [];
    foreach ($name as $named) {
        if (! $parameters->has($named)) {
            throw new LogicException(
                (string) message(
                    'Parameter `%name%` not found',
                    name: $named,
                )
            );
        }
        $isVariadicItem = $named === $lastName && $parameters->isVariadic();

        try {
            if ($isVariadicItem) {
                parameters(
                    ...[
                        $named => $parameters->get($named),
                    ]
                )->withIsVariadic(true)
                    ->__invoke(...array_slice($args, $lastIndex, preserve_keys: true));
            } else {
                $parameters->get($named)
                    ->__invoke($args[array_search($named, $namedKeys, true)]);
            }
        } catch (Throwable $e) {
            $message = $e->getMessage();
            if ($isVariadicItem) {
                $message = preg_replace_callback(
                    '/\[(\d+)\.\.\.' . preg_quote($named, '/') . '\]/',
                    fn (array $matches): string => '['
                        . ((int) $matches[1] + $lastIndex)
                        . '...'
                        . $named
                        . ']',
                    $message,
                ) ?? $message;
            }
            $errors[] = $message;
        }
    }
    if ($errors !== []) {
        throw new InvalidArgumentException(implode("\n", $errors));
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
