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
 * Describes the component in charge of defining array modifications.
 */
interface ArrayParameterModifyInterface
{
    /**
     * Return an instance with the specified now optional parameter(s).
     *
     * If no parameter is specified, all parameters are made optional.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified now optional parameter(s).
     */
    public function withMakeOptional(string ...$name): static;

    /**
     * Return an instance with the specified now required parameter(s).
     *
     * If no parameter is specified, all parameters are made required.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified now required parameter(s).
     */
    public function withMakeRequired(string ...$name): static;

    /**
     * Return an instance with removed parameters.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified added parameters.
     */
    public function without(string ...$name): static;

    /**
     * Return an instance requiring at least `$count` of optional arguments.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified optional parameters.
     */
    public function withOptionalMinimum(int $count): static;
}
