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

use Chevere\Parameter\Interfaces\StringParameterInterface;
use Chevere\Regex\Regex;

function string(
    string $regex = '',
    string $description = '',
    ?string $default = null,
    bool $sensitive = false
): StringParameterInterface {
    $parameter = new StringParameter($description, $sensitive);
    if ($regex !== '') {
        $parameter = $parameter
            ->withRegex(
                new Regex($regex)
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
    bool $sensitive = false
): StringParameterInterface {
    return string(
        regex: '/^\d+$/',
        description: $description,
        default: $default,
        sensitive: $sensitive
    );
}

function enum(string $string, string ...$strings): StringParameterInterface
{
    array_unshift($strings, $string);
    $cases = implode('|', $strings);
    $regex = "#^{$cases}$#";

    return string($regex);
}

/**
 * Parameter for `YYYY-MM-DD` strings.
 */
function date(
    string $description = 'YYYY-MM-DD',
    ?string $default = null,
    bool $sensitive = false
): StringParameterInterface {
    $regex = '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/';

    return string($regex, $description, $default, $sensitive);
}

/**
 * Parameter for `hh:mm:ss` strings.
 */
function time(
    string $description = 'hh:mm:ss',
    ?string $default = null,
    bool $sensitive = false
): StringParameterInterface {
    $regex = '/^\d{2,3}:[0-5][0-9]:[0-5][0-9]$/';

    return string($regex, $description, $default, $sensitive);
}

/**
 * Parameter for `YYYY-MM-DD hh:mm:ss.precision` strings.
 */
function datetime(
    string $description = 'YYYY-MM-DD hh:mm:ss',
    ?string $default = null,
    bool $sensitive = false,
    int $precision = 0,
): StringParameterInterface {
    $regex = '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])\s{1}\d{2,3}:[0-5][0-9]:[0-5][0-9]$/';
    if ($precision > 0) {
        $regex = str_replace('$/', '(\.\d{1,' . $precision . '})?$/', $regex);
    }

    return string($regex, $description, $default, $sensitive);
}
