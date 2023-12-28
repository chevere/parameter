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

use Chevere\Parameter\Interfaces\RegexParameterInterface;
use Chevere\Parameter\Interfaces\StringParameterInterface;
use Chevere\Regex\Regex;

/**
 * @param array<string> $contains
 * @param array<string> $reject
 */
function string(
    string $description = '',
    ?string $startsWith = null,
    ?string $endsWith = null,
    array $contains = [],
    array $reject = [],
    ?int $length = null,
    ?int $minLength = null,
    ?int $maxLength = null,
    ?string $default = null,
): StringParameterInterface {
    $parameter = new StringParameter($description);
    if ($startsWith !== null) {
        $parameter = $parameter->withStartsWith($startsWith);
    }
    if ($endsWith !== null) {
        $parameter = $parameter->withEndsWith($endsWith);
    }
    if ($contains === []) {
        $parameter = $parameter->withContains(...$contains);
    }
    if ($reject !== []) {
        $parameter = $parameter->withReject(...$reject);
    }
    if ($length !== null) {
        $parameter = $parameter->withLength($length);
    }
    if ($minLength !== null) {
        $parameter = $parameter->withMinLength($minLength);
    }
    if ($maxLength !== null) {
        $parameter = $parameter->withMaxLength($maxLength);
    }

    if ($default !== null) {
        $parameter = $parameter->withDefault($default);
    }

    return $parameter;
}

function regex(
    string $pattern = '',
    string $description = '',
    ?string $default = null,
): RegexParameterInterface {
    $parameter = new RegexParameter($description);
    if ($pattern !== '') {
        $parameter = $parameter
            ->withRegex(
                new Regex($pattern)
            );
    }
    if ($default !== null) {
        $parameter = $parameter->withDefault($default);
    }

    return $parameter;
}

function intString(
    string $description = '',
    ?string $default = null,
): RegexParameterInterface {
    return regex(
        pattern: '/^\d+$/',
        description: $description,
        default: $default
    );
}

function enum(string $string, string ...$strings): RegexParameterInterface
{
    array_unshift($strings, $string);
    $cases = implode('|', $strings);
    $regex = "/\b({$cases})\b/";

    return regex($regex);
}

/**
 * Parameter for `YYYY-MM-DD` strings.
 */
function date(
    string $description = 'YYYY-MM-DD',
    ?string $default = null
): RegexParameterInterface {
    $regex = '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/';

    return regex($regex, $description, $default);
}

/**
 * Parameter for `hh:mm:ss` strings.
 */
function time(
    string $description = 'hh:mm:ss',
    ?string $default = null
): RegexParameterInterface {
    $regex = '/^\d{2,3}:[0-5][0-9]:[0-5][0-9]$/';

    return regex($regex, $description, $default);
}

/**
 * Parameter for `YYYY-MM-DD hh:mm:ss` strings.
 */
function datetime(
    string $description = 'YYYY-MM-DD hh:mm:ss',
    ?string $default = null
): RegexParameterInterface {
    $regex = '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])\s{1}\d{2,3}:[0-5][0-9]:[0-5][0-9]$/';

    return regex($regex, $description, $default);
}
