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

use Stringable;

/**
 * Describes the component in charge of defining a parameter of type string.
 */
interface StringParameterInterface extends ParameterInterface
{
    /**
     * Asserts the given `$value` is valid.
     */
    public function __invoke(Stringable|string $value): string;

    /**
     * Return an instance with the specified `$default` value.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified `$default` value.
     */
    public function withDefault(string $value): self;

    /**
     * Provides access to the default value (if any).
     */
    public function default(): ?string;

    public function assertCompatible(self $parameter): void;

    public function withStartsWith(string $string): self;

    public function startsWith(): ?string;

    public function withEndsWith(string $string): self;

    public function endsWith(): ?string;

    public function withContains(string ...$strings): self;

    /**
     * @return array<string>
     */
    public function contains(): array;

    public function withReject(string ...$strings): self;

    /**
     * @return array<string>
     */
    public function reject(): array;

    public function withMinLength(int $int): self;

    public function minLength(): ?int;

    public function withMaxLength(int $int): self;

    public function maxLength(): ?int;

    public function withLength(int $int): self;

    public function length(): ?int;
}
