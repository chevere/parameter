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

use Chevere\Parameter\Interfaces\UnionParameterInterface;
use function Chevere\Parameter\bool;
use function Chevere\Parameter\float;
use function Chevere\Parameter\int;
use function Chevere\Parameter\object;
use function Chevere\Parameter\string;
use function Chevere\Parameter\unionNull;

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
): UnionParameterInterface {
    return unionNull(
        float(...get_defined_vars())
    );
}

function nullBool(
    string $description = '',
    ?bool $default = null,
    bool $sensitive = false,
): UnionParameterInterface {
    return unionNull(
        bool(...get_defined_vars())
    );
}

function nullString(
    string $regex = '',
    string $description = '',
    ?string $default = null,
    bool $sensitive = false
): UnionParameterInterface {
    return unionNull(
        string(...get_defined_vars())
    );
}

function nullObject(
    string $className,
    string $description = '',
    bool $sensitive = false,
): UnionParameterInterface {
    return unionNull(
        object(...get_defined_vars())
    );
}
