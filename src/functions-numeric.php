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
    if ($accept !== []) {
        $parameter = $parameter->withAccept(...$accept);
    }
    if ($reject !== []) {
        $parameter = $parameter->withReject(...$reject);
    }
    if ($min !== null) {
        $parameter = $parameter->withMin($min);
    }
    if ($max !== null) {
        $parameter = $parameter->withMax($max);
    }
    if ($default !== null) {
        $parameter = $parameter->withDefault($default);
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
    if ($min !== null) {
        $parameter = $parameter->withMin($min);
    }
    if ($max !== null) {
        $parameter = $parameter->withMax($max);
    }
    if ($default !== null) {
        $parameter = $parameter->withDefault($default);
    }

    return $parameter;
}
