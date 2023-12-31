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
 * Describes the component in charge of defining a parameter.
 * @method void assertCompatible(self $parameter) Asserts parameter compatibility against the provided `$parameter`.
 * @method mixed __invoke($value) Asserts the given `$value` is valid.
 * @method mixed withDefault(mixed $default) Return an instance with the specified `$default` value.
 */
interface ParameterInterface
{
    /**
     * This method runs before the `__construct` method.
     */
    public function setUp(): void;

    /**
     * Provides access to the type instance.
     */
    public function type(): TypeInterface;

    /**
     * Provides access to the default value.
     */
    public function default(): mixed;

    /**
     * @return array<string, mixed>
     */
    public function schema(): array;

    /**
     * Provides access to the description.
     */
    public function description(): string;

    /**
     * Return an instance with the specified description.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified description.
     */
    public function withDescription(string $description): self;
}
