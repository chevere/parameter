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

use BackedEnum;
use Chevere\Parameter\Interfaces\ParameterInterface;
use Chevere\Parameter\Interfaces\StringParameterInterface;
use Chevere\Parameter\Interfaces\UnionParameterInterface;
use InvalidArgumentException;
use Stringable;

/**
 * @param int[] $accept
 * @param int[] $reject
 */
function nullInt(
    string $description = '',
    ?int $default = null,
    ?int $min = null,
    ?int $max = null,
    array $accept = [],
    array $reject = [],
    bool $sensitive = false,
    string $label = '',
): UnionParameterInterface {
    return unionNull(
        int(...get_defined_vars())
    );
}

/**
 * @param float[] $accept
 * @param float[] $reject
 */
function nullFloat(
    string $description = '',
    ?float $default = null,
    ?float $min = null,
    ?float $max = null,
    array $accept = [],
    array $reject = [],
    bool $sensitive = false,
    string $label = '',
): UnionParameterInterface {
    return unionNull(
        float(...get_defined_vars())
    );
}

function nullBool(
    string $description = '',
    ?bool $default = null,
    bool $sensitive = false,
    string $label = '',
): UnionParameterInterface {
    return unionNull(
        bool(...get_defined_vars())
    );
}

function nullString(
    string|Stringable|BackedEnum $regex = '',
    string $description = '',
    ?string $default = null,
    bool $sensitive = false,
    string $label = '',
): UnionParameterInterface {
    $regex = match (true) {
        is_string($regex) => $regex,
        $regex instanceof Stringable => (string) $regex,
        is_string($regex->value) => $regex->value,
        default => throw new InvalidArgumentException(
            'Regex must be a `string`, `Stringable`, or `BackedEnum` with string value.'
        )
    };

    return unionNull(
        string(...get_defined_vars())
    );
}

function nullArray(
    ParameterInterface ...$required
): UnionParameterInterface {
    return unionNull(
        arrayp(...$required)
    );
}

function nullArrayString(
    StringParameterInterface ...$required
): UnionParameterInterface {
    return unionNull(
        arrayString(...$required)
    );
}

/**
 * @param class-string $className
 */
function nullObject(
    string $className,
    string $description = '',
    bool $sensitive = false,
    string $label = '',
): UnionParameterInterface {
    return unionNull(
        object(...get_defined_vars())
    );
}
