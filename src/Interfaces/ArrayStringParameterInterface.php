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

use ArrayAccess;

/**
 * Describes the component in charge of defining a parameter of type array with string members.
 */
interface ArrayStringParameterInterface extends ArrayTypeParameterInterface, ArrayParameterModifyInterface
{
    /**
     * Asserts the given `$value` is valid.
     *
     * @param array<string, mixed> $array
     * @return array<string, mixed>
     *
     * @phpstan-ignore-next-line
     */
    public function __invoke(array|ArrayAccess $array): array|ArrayAccess;

    /**
     * Return an instance with the specified default value.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified default value.
     *
     * @param array<mixed, string> $value
     */
    public function withDefault(array $value): self;

    /**
     * Return an instance with required parameters.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified required parameters.
     */
    public function withRequired(StringParameterInterface ...$parameter): self;

    /**
     * Return an instance with optional parameters.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified optional parameters.
     */
    public function withOptional(StringParameterInterface ...$parameter): self;

    public function assertCompatible(self $parameter): void;
}
