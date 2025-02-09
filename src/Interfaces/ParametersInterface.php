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

use Chevere\DataStructure\Interfaces\StringMappedInterface;
use Chevere\DataStructure\Interfaces\VectorInterface;

/**
 * Describes the component in charge of collecting objects implementing `ParameterInterface`.
 *
 * @extends StringMappedInterface<ParameterInterface>
 */
interface ParametersInterface extends StringMappedInterface
{
    /**
     * Asserts the given named arguments are valid.
     */
    public function __invoke(mixed ...$argument): ArgumentsInterface;

    /**
     * Return an instance with the specified required parameter added.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified required parameter added.
     */
    public function withRequired(string $name, ParameterInterface $parameter): self;

    /**
     * Return an instance with the specified optional parameter(s) added.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified optional parameter(s) added.
     */
    public function withOptional(string $name, ParameterInterface $parameter): self;

    /**
     * Return an instance with the specified now optional parameter(s).
     *
     * If no parameter is specified, all parameters are made optional.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified now optional parameter(s).
     */
    public function withMakeOptional(string ...$name): self;

    /**
     * Return an instance with the specified now required parameter(s).
     *
     * If no parameter is specified, all parameters are made required.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified now required parameter(s).
     */
    public function withMakeRequired(string ...$name): self;

    /**
     * Return an instance with the specified parameter(s) removed.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified parameter(s) removed.
     */
    public function without(string ...$name): self;

    /**
     * Return an instance with the specified parameter(s) merged.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified parameter(s) merged.
     */
    public function withMerge(self $parameters): self;

    /**
     * Return an instance requiring at least `$count` of optional arguments.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified optional parameters.
     */
    public function withOptionalMinimum(int $count): self;

    /**
     * Asserts whether the instance has a parameter by name(s).
     */
    public function assertHas(string ...$name): void;

    /**
     * Indicates whether the instance has a parameter by name(s).
     */
    public function has(string ...$name): bool;

    /**
     * @return VectorInterface<string>
     */
    public function requiredKeys(): VectorInterface;

    /**
     * @return VectorInterface<string>
     */
    public function optionalKeys(): VectorInterface;

    /**
     * Indicates the minimum number of optional parameters required.
     */
    public function optionalMinimum(): int;

    /**
     * Provides parameter access by name.
     *
     * ```php
     * // @var StringParameterInterface
     * $parameters->get('name');
     * ```
     */
    public function get(string $name): ParameterInterface;

    /**
     * Provides cast access to the required parameter by name.
     *
     * ```php
     * $parameters->required('name')->string();
     * ```
     */
    public function required(string $name): ParameterCastInterface;

    /**
     * Provides cast access to the optional parameter by name.
     *
     * ```php
     * $parameters->optional('name')->string();
     * ```
     */
    public function optional(string $name): ParameterCastInterface;

    /**
     * Return an instance with the specified $isVariadic.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified $isVariadic.
     */
    public function withIsVariadic(bool $isVariadic = true): self;

    public function isVariadic(): bool;
}
