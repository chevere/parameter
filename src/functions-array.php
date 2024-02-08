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
use Chevere\Parameter\Interfaces\ArrayParameterInterface;
use Chevere\Parameter\Interfaces\ArrayStringParameterInterface;
use Chevere\Parameter\Interfaces\ArrayTypeParameterInterface;
use Chevere\Parameter\Interfaces\IntParameterInterface;
use Chevere\Parameter\Interfaces\ParameterInterface;
use Chevere\Parameter\Interfaces\StringParameterInterface;

function arrayp(
    ParameterInterface ...$required
): ArrayParameterInterface {
    $array = new ArrayParameter();

    return $required
        ? $array->withRequired(...$required)
        : $array;
}

function arrayString(
    StringParameterInterface ...$required
): ArrayStringParameterInterface {
    $array = new ArrayStringParameter();

    return $required
        ? $array->withRequired(...$required)
        : $array;
}

function file(
    ?IntParameterInterface $error = null,
    ?StringParameterInterface $name = null,
    ?StringParameterInterface $type = null,
    ?StringParameterInterface $tmp_name = null,
    ?IntParameterInterface $size = null,
    ?StringParameterInterface $contents = null,
): ArrayParameterInterface {
    $array = arrayp(
        error: $error ?? int(accept: [UPLOAD_ERR_OK]),
        name: $name ?? string(),
        size: $size ?? int(),
        type: $type ?? string(),
        tmp_name: $tmp_name ?? string(),
    );
    if ($contents !== null) {
        $array = $array->withOptional(
            contents: $contents,
        );
    }

    return $array;
}

/**
 * @param array<int|string, mixed>|ArrayAccess<int|string, mixed> $argument
 * @return array<int|string, mixed> Asserted array, with fixed optional values.
 */
function assertArray(
    ArrayTypeParameterInterface $parameter,
    array|ArrayAccess $argument,
): array {
    if ($parameter->parameters()->count() === 0) {
        return (array) $argument;
    }

    return arguments($parameter->parameters(), $argument)->toArray();
}

/**
 * @param array<int|string, string> $argument
 * @return array<int|string, string> Asserted array, with fixed optional values.
 */
function assertArrayString(
    ArrayStringParameterInterface $parameter,
    array $argument,
): array {
    /** @var array<int|string, string> */
    return assertArray($parameter, $argument);
}
