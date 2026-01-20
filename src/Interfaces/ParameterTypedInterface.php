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

namespace Chevere\Parameter\Interfaces;

/**
 * Describes the component providing type-safe access to a parameter.
 */
interface ParameterTypedInterface
{
    public function array(): ArrayParameterInterface;

    public function bool(): BoolParameterInterface;

    public function float(): FloatParameterInterface;

    public function int(): IntParameterInterface;

    public function object(): ObjectParameterInterface;

    public function null(): NullParameterInterface;

    public function union(): UnionParameterInterface;

    public function iterable(): IterableParameterInterface;

    public function string(): StringParameterInterface;
}
