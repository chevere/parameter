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

use Chevere\Regex\Interfaces\RegexInterface;
use Stringable;

/**
 * Describes the component in charge of defining a parameter of type string.
 */
interface StringParameterInterface extends ParameterInterface
{
    public const REGEX_DEFAULT = '/^.*$/';

    /**
     * Asserts the given `$value` is valid, returning the value if so.
     */
    public function __invoke(Stringable|string $value): string;

    /**
     * Provides access to the regex instance.
     */
    public function regex(): RegexInterface;

    /**
     * Return an instance with the specified `$regex`.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified `$regex`.
     */
    public function withRegex(RegexInterface $regex): self;

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
}
