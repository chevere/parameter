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
use Chevere\Parameter\Interfaces\StringParameterInterface;
use Chevere\Regex\Regex;
use InvalidArgumentException;
use Stringable;

function string(
    string|Stringable|BackedEnum $regex = '',
    string $description = '',
    ?string $default = null,
    bool $sensitive = false,
    string $label = '',
): StringParameterInterface {
    $parameter = new StringParameter($description, $sensitive);
    if ($regex !== '') {
        $regex = match (true) {
            is_string($regex) => $regex,
            $regex instanceof Stringable => (string) $regex,
            is_string($regex->value) => $regex->value,
            default => throw new InvalidArgumentException(
                'Regex must be a `string`, `Stringable`, or `BackedEnum` with string value.'
            )
        };
        $parameter = $parameter
            ->withRegex(
                new Regex($regex)
            );
    }
    if ($default !== null) {
        $parameter = $parameter->withDefault($default);
    }
    if ($label !== '') {
        $parameter = $parameter->withLabel($label);
    }

    return $parameter;
}

function intString(
    string $description = '',
    ?string $default = null,
    bool $sensitive = false,
    string $label = '',
): StringParameterInterface {
    return string(
        regex: '/^\d+$/',
        description: $description,
        default: $default,
        sensitive: $sensitive,
        label: $label,
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
    bool $sensitive = false,
    string $label = '',
): StringParameterInterface {
    $regex = '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/';

    return string(...get_defined_vars());
}

/**
 * Parameter for `hh:mm:ss` strings.
 */
function time(
    string $description = 'hh:mm:ss',
    ?string $default = null,
    bool $sensitive = false,
    string $label = '',
): StringParameterInterface {
    $regex = '/^\d{2,3}:[0-5][0-9]:[0-5][0-9]$/';

    return string(...get_defined_vars());
}

/**
 * Parameter for `YYYY-MM-DD hh:mm:ss.precision` strings.
 */
function datetime(
    string $description = 'YYYY-MM-DD hh:mm:ss',
    ?string $default = null,
    bool $sensitive = false,
    int $precision = 0,
    string $label = '',
): StringParameterInterface {
    $regex = '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])\s{1}\d{2,3}:[0-5][0-9]:[0-5][0-9]$/';
    if ($precision > 0) {
        $regex = str_replace('$/', '(\.\d{1,' . $precision . '})?$/', $regex);
    }
    unset($precision);

    return string(...get_defined_vars());
}
