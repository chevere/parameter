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

function string(
    string $description = '',
    string $startsWith = '',
    string $endsWith = '',
    string $contains = '',
    string $notStartsWith = '',
    string $notEndsWith = '',
    string $notContains = '',
    int $minLength = 0,
    int $maxLength = 0,
    int $length = 0,
    ?string $default = null,
): StringParameterInterface {
    $parameter = new StringParameter($description);
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
