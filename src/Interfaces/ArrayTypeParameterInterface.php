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

use Iterator;
use IteratorAggregate;

/**
 * Describes the component in charge of defining an array type parameter to be
 * used as a base for array-shape parameters.
 *
 * @extends IteratorAggregate<ParameterInterface>
 */
interface ArrayTypeParameterInterface extends ParameterInterface, ParametersAccessInterface, IteratorAggregate
{
    /**
     * Provides access to the default value (if any).
     *
     * @return array<mixed, mixed>
     */
    public function default(): ?array;

    public function typeSchema(): string;

    public function isList(): bool;

    public function isMap(): bool;

    /**
     * @return Traversable<string, ParameterInterface>
     * @phpstan-return Iterator<string, ParameterInterface>
     */
    public function getIterator(): Iterator;
}
