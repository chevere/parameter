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

use Chevere\Parameter\Interfaces\BoolParameterInterface;
use Chevere\Parameter\Interfaces\IntParameterInterface;
use Chevere\Parameter\Interfaces\StringParameterInterface;

function bool(
    string $description = '',
    ?bool $default = null,
    bool $sensitive = false,
): BoolParameterInterface {
    $parameter = new BoolParameter($description, $sensitive);
    if ($default !== null) {
        $parameter = $parameter->withDefault($default);
    }

    return $parameter;
}

function boolInt(
    string $description = '',
    ?int $default = null,
    bool $sensitive = false,
): IntParameterInterface {
    return int(
        description: $description,
        default: $default,
        accept: [0, 1],
        sensitive: $sensitive
    );
}

function boolString(
    string $description = '',
    ?string $default = null,
    bool $sensitive = false,
): StringParameterInterface {
    return string(
        regex: '/^[01]$/',
        description: $description,
        default: $default,
        sensitive: $sensitive
    );
}
