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
 * Describes the component in charge of defining a parameter of type object (typed).
 */
interface ObjectParameterInterface extends ParameterInterface
{
    /**
     * Asserts the given `$value` is valid.
     */
    public function __invoke(object $value): object;

    /**
     * @return class-string
     */
    public function className(): string;

    /**
     * Return an instance with the specified class name.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified class name.
     *
     * @param class-string $className
     */
    public function withClassName(string $className): self;

    public function withDefault(object $default): self;

    public function assertCompatible(self $parameter): void;
}
