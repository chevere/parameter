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
use ReflectionFunction;
use ReflectionMethod;
use function Chevere\Parameter\arguments;
use function Chevere\Parameter\parameterAttr;
use function Chevere\Parameter\reflectedParameterAttribute;

/**
 * Validates argument `$name` against attribute rules.
 * @throws LogicException
 */
function validate(string $name): void
{
    $caller = debug_backtrace(0, 2)[1];
    $class = $caller['class'] ?? null;
    $method = $caller['function'];
    $reflection = $class
        ? new ReflectionMethod($class, $method)
        : new ReflectionFunction($method);
    $parameters = $reflection->getParameters();
    foreach ($parameters as $parameter) {
        if ($parameter->getName() !== $name) {
            continue;
        }
        $pos = $parameter->getPosition();
        // @phpstan-ignore-next-line
        reflectedParameterAttribute($name, $parameter)($caller['args'][$pos]);

        return;
    }

    // throw new LogicException('No parameter attribute found');
}

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

function arrayArguments(string $name): ArgumentsInterface
{
    $caller = debug_backtrace(0, 2)[1];
    $class = $caller['class'] ?? null;
    $method = $caller['function'];
    $reflection = $class
        ? new ReflectionMethod($class, $method)
        : new ReflectionFunction($method);
    $parameters = $reflection->getParameters();
    foreach ($parameters as $parameter) {
        if ($parameter->getName() !== $name) {
            continue;
        }
        $pos = $parameter->getPosition();
        // @phpstan-ignore-next-line
        $array = reflectedParameterAttribute(
            $name,
            $parameter,
            ArrayAttr::class
        )->parameter;

        // @phpstan-ignore-next-line
        return arguments($array, $caller['args'][$pos]);
    }

    throw new LogicException('No parameter attribute for ' . $name);
}
