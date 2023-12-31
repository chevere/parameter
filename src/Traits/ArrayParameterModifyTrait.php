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

namespace Chevere\Parameter\Traits;

use Chevere\Parameter\Interfaces\ParameterInterface;
use Chevere\Parameter\Interfaces\ParametersInterface;

trait ArrayParameterModifyTrait
{
    private ParametersInterface $parameters;

    private bool $isList = false;

    public function withDefault(array $value): static
    {
        // @phpstan-ignore-next-line
        $this($value);
        $new = clone $this;
        $new->default = $value;

        return $new;
    }

    public function without(string ...$name): static
    {
        $new = clone $this;
        $new->parameters = $new->parameters
            ->without(...$name);

        return $new;
    }

    public function withMakeOptional(string ...$name): static
    {
        $new = clone $this;
        $new->parameters = $new->parameters
            ->withMakeOptional(...$name);

        return $new;
    }

    public function withMakeRequired(string ...$name): static
    {
        $new = clone $this;
        $new->parameters = $new->parameters
            ->withMakeRequired(...$name);

        return $new;
    }

    public function withModify(ParameterInterface ...$parameter): static
    {
        $new = clone $this;
        $keys = array_keys($parameter);
        $keys = array_map(fn ($key) => strval($key), $keys);
        $new->parameters->assertHas(...$keys);
        foreach ($parameter as $name => $item) {
            $name = strval($name);
            $method = match (true) {
                $new->parameters->optionalKeys()->contains($name) => 'withOptional',
                default => 'withRequired',
            };
            $new->parameters = $new->parameters->without($name);
            $new->parameters = $new->parameters->{$method}($name, $item);
        }

        return $new;
    }

    private function put(string $method, ParameterInterface ...$parameter): void
    {
        $this->removeConflictKeys(...$parameter);
        foreach ($parameter as $name => $item) {
            $name = strval($name);
            $this->parameters = $this->parameters->{$method}($name, $item);
        }
        $keys = $this->parameters->keys();
        /** @var array<string> $fillKeys */
        $fillKeys = array_fill_keys($keys, null);
        $this->isList = array_is_list($fillKeys);
    }

    private function removeConflictKeys(ParameterInterface ...$parameter): void
    {
        $keys = array_keys($parameter);
        /** @var string[] $diff */
        $diff = array_intersect($keys, $this->parameters->keys());
        $this->parameters = $this->parameters->without(...$diff);
    }
}
