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
 * Describes the component in charge of defining a parameter of type mixed.
 */
interface MixedParameterInterface extends ParameterInterface
{
    /**
     * Asserts the given `$value` is valid.
     */
    public function __invoke(mixed $value): mixed;

    public function assertCompatible(self $parameter): void;
}
