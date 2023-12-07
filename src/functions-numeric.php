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

use Chevere\Parameter\Interfaces\FloatParameterInterface;
use Chevere\Parameter\Interfaces\IntParameterInterface;
use InvalidArgumentException;
use function Chevere\Message\message;

/**
 * @param float[] $accept
 * @param float[] $reject
 */
function float(
    string $description = '',
    ?float $default = null,
    ?float $min = null,
    ?float $max = null,
    array $accept = [],
    array $reject = [],
): FloatParameterInterface {
    $parameter = new FloatParameter($description);
    if ($default !== null) {
        $parameter = $parameter->withDefault($default);
    }
    if ($min !== null) {
        $parameter = $parameter->withMin($min);
    }
    if ($max !== null) {
        $parameter = $parameter->withMax($max);
    }
    if ($accept !== []) {
        $parameter = $parameter->withAccept(...$accept);
    }
    if ($reject !== []) {
        $parameter = $parameter->withReject(...$reject);
    }

    return $parameter;
}

/**
 * @param int[] $accept
 * @param int[] $reject
 */
function int(
    string $description = '',
    ?int $default = null,
    ?int $min = null,
    ?int $max = null,
    array $accept = [],
    array $reject = [],
): IntParameterInterface {
    $parameter = new IntParameter($description);
    if ($accept !== []) {
        $parameter = $parameter->withAccept(...$accept);
    }
    if ($reject !== []) {
        $parameter = $parameter->withReject(...$reject);
    }
    if ($default !== null) {
        $parameter = $parameter->withDefault($default);
    }
    if ($min !== null) {
        $parameter = $parameter->withMin($min);
    }
    if ($max !== null) {
        $parameter = $parameter->withMax($max);
    }

    return $parameter;
}

function assertNumeric(
    IntParameterInterface|FloatParameterInterface $parameter,
    int|float $argument,
): int|float {
    if ($parameter->accept() !== []) {
        if (in_array($argument, $parameter->accept(), true)) {
            return $argument;
        }
        $values = implode(',', $parameter->accept());

        throw new InvalidArgumentException(
            (string) message(
                'Argument value provided `%provided%` is not an accepted value in `%value%`',
                provided: strval($argument),
                value: "[{$values}]"
            )
        );
    }
    if ($parameter->reject() !== []) {
        if (! in_array($argument, $parameter->reject(), true)) {
            return $argument;
        }
        $values = implode(',', $parameter->reject());

        throw new InvalidArgumentException(
            (string) message(
                'Argument value provided `%provided%` is on rejected list `%value%`',
                provided: strval($argument),
                value: "[{$values}]"
            )
        );
    }
    $min = $parameter->min();
    if ($min !== null && $argument < $min) {
        throw new InvalidArgumentException(
            (string) message(
                'Argument value provided `%provided%` is less than `%min%`',
                provided: strval($argument),
                min: strval($min)
            )
        );
    }
    $max = $parameter->max();
    if ($max !== null && $argument > $max) {
        throw new InvalidArgumentException(
            (string) message(
                'Argument value provided `%provided%` is greater than `%max%`',
                provided: strval($argument),
                max: strval($max)
            )
        );
    }

    return $argument;
}

function assertInt(
    IntParameterInterface $parameter,
    int $argument,
): int {
    assertNumeric($parameter, $argument);

    return $argument;
}

function assertFloat(
    FloatParameterInterface $parameter,
    float $argument
): float {
    assertNumeric($parameter, $argument);

    return $argument;
}
