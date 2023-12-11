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
use LogicException;
use ReflectionAttribute;
use ReflectionFunction;
use ReflectionMethod;
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

function floatAttr(string $name): FloatAttribute
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

function genericAttr(string $name): GenericAttr
{
    $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];

    // @phpstan-ignore-next-line
    return parameterAttr($name, $caller);
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
        $arguments[$named] = $args[$pos];
    }

    // @phpstan-ignore-next-line
    return $array->parameters()->__invoke(...$arguments[$name]);
}

/**
 * Validates argument `$name` against attribute rules.
 */
function validate(?string $name = null): void
{
    $caller = debug_backtrace(0, 2)[1];
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
        $arguments[$named] = $args[$pos];
    }
    if ($name === null) {
        $parameters(...$arguments);

        return;
    }
    $parameters->get($name)->__invoke($arguments[$name]);
}

/**
 * Validates `$var` against the return attribute.
 *
 * @return mixed The validated `$var`.
 */
function returnAttr(mixed $var): mixed
{
    $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
    $class = $caller['class'] ?? null;
    $method = $caller['function'];
    $reflection = $class
        ? new ReflectionMethod($class, $method)
        : new ReflectionFunction($method);
    /** @var ReflectionAttribute<ReturnAttr> $attribute */
    $attribute = $reflection->getAttributes(ReturnAttr::class)[0]
        ?? throw new LogicException('No return attribute found');

    return $attribute->newInstance()->__invoke($var);
}
