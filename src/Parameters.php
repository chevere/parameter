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

namespace Chevere\Parameter;

use BadMethodCallException;
use Chevere\DataStructure\Interfaces\VectorInterface;
use Chevere\DataStructure\Map;
use Chevere\DataStructure\Traits\MapTrait;
use Chevere\DataStructure\Vector;
use Chevere\Parameter\Interfaces\ArgumentsInterface;
use Chevere\Parameter\Interfaces\ParameterInterface;
use Chevere\Parameter\Interfaces\ParametersInterface;
use Chevere\Parameter\Interfaces\ParameterTypedInterface;
use InvalidArgumentException;
use Iterator;
use OverflowException;
use function Chevere\Message\message;

final class Parameters implements ParametersInterface
{
    /**
     * @template-use MapTrait<ParameterInterface>
     */
    use MapTrait;

    /**
     * @var Map<ParameterInterface>
     */
    private Map $map;

    /**
     * @var VectorInterface<string>
     */
    private VectorInterface $requiredKeys;

    /**
     * @var VectorInterface<string>
     */
    private VectorInterface $optionalKeys;

    private int $optionalMinimum = 0;

    private bool $isVariadic = false;

    /**
     * @var VectorInterface<string>
     */
    private VectorInterface $index;

    /**
     * @param ParameterInterface $parameter Required parameters
     */
    public function __construct(ParameterInterface ...$parameter)
    {
        $this->map = new Map();
        $this->index = new Vector();
        $this->requiredKeys = new Vector();
        $this->optionalKeys = new Vector();
        foreach ($parameter as $name => $item) {
            $name = strval($name);
            $this->addProperty('requiredKeys', $name, $item);
        }
    }

    public function __invoke(mixed ...$argument): ArgumentsInterface
    {
        return new Arguments($this, $argument);
    }

    public function keys(): array
    {
        return $this->index->toArray();
    }

    public function getIterator(): Iterator
    {
        foreach ($this->index as $key) {
            yield $key => $this->map->get($key);
        }
    }

    public function withIsVariadic(bool $flag = true): ParametersInterface
    {
        $new = clone $this;
        $new->isVariadic = $flag;

        return $new;
    }

    public function isVariadic(): bool
    {
        return $this->isVariadic;
    }

    public function withRequired(string $name, ParameterInterface $parameter): ParametersInterface
    {
        $new = clone $this;
        $new->addProperty('requiredKeys', $name, $parameter);

        return $new;
    }

    public function withOptional(string $name, ParameterInterface $parameter): ParametersInterface
    {
        $new = clone $this;
        $new->addProperty('optionalKeys', $name, $parameter);

        return $new;
    }

    public function withMakeOptional(string ...$name): ParametersInterface
    {
        $new = clone $this;
        if ($name === []) {
            $name = $this->requiredKeys->toArray();
        }
        $index = $new->index;
        foreach ($name as $key) {
            if (! $new->requiredKeys->contains($key)) {
                throw new InvalidArgumentException(
                    (string) message(
                        'Parameter `%name%` is not required',
                        name: $key
                    )
                );
            }
            $parameter = $new->get($key);
            $new->remove($key);
            $new->addProperty('optionalKeys', $key, $parameter);
        }
        $new->index = $index;

        return $new;
    }

    public function withMakeRequired(string ...$name): ParametersInterface
    {
        $new = clone $this;
        if ($name === []) {
            $name = $this->optionalKeys->toArray();
        }
        $index = $new->index;
        foreach ($name as $key) {
            if (! $new->optionalKeys()->contains($key)) {
                throw new InvalidArgumentException(
                    (string) message(
                        'Parameter `%name%` is not optional',
                        name: $key
                    )
                );
            }
            $parameter = $new->get($key);
            $new->remove($key);
            $new->addProperty('requiredKeys', $key, $parameter);
        }
        $new->index = $index;

        return $new;
    }

    public function without(string ...$name): ParametersInterface
    {
        $new = clone $this;
        $new->remove(...$name);

        return $new;
    }

    public function withMerge(ParametersInterface $parameters): ParametersInterface
    {
        $new = clone $this;
        foreach ($parameters as $name => $parameter) {
            $container = match ($parameters->requiredKeys()->contains($name)) {
                true => 'requiredKeys',
                default => 'optionalKeys',
            };
            $new->addProperty($container, $name, $parameter);
        }

        return $new;
    }

    public function withOptionalMinimum(int $count): ParametersInterface
    {
        match (true) {
            $count < 0 => throw new InvalidArgumentException(
                (string) message('Count must be greater or equal to 0')
            ),
            $this->optionalKeys()->count() === 0 => throw new BadMethodCallException(
                (string) message('No optional parameters found')
            ),
            default => null,
        };
        $new = clone $this;
        $new->optionalMinimum = $count;
        $new->assertMinimumOptional();

        return $new;
    }

    public function requiredKeys(): VectorInterface
    {
        return $this->requiredKeys;
    }

    public function optionalKeys(): VectorInterface
    {
        return $this->optionalKeys;
    }

    public function optionalMinimum(): int
    {
        return $this->optionalMinimum;
    }

    public function assertHas(string ...$name): void
    {
        $this->map->assertHas(...$name);
    }

    public function has(string ...$name): bool
    {
        return $this->map->has(...$name);
    }

    public function get(string $name): ParameterInterface
    {
        return $this->map->get($name);
    }

    public function required(string $name): ParameterTypedInterface
    {
        $parameter = $this->get($name);
        if ($this->optionalKeys()->contains($name)) {
            throw new InvalidArgumentException(
                (string) message(
                    'Parameter `%name%` is optional',
                    name: $name
                )
            );
        }

        return new ParameterTyped($parameter);
    }

    public function optional(string $name): ParameterTypedInterface
    {
        $parameter = $this->get($name);
        if (! $this->optionalKeys()->contains($name)) {
            throw new InvalidArgumentException(
                (string) message(
                    'Parameter `%name%` is required',
                    name: $name
                )
            );
        }

        return new ParameterTyped($parameter);
    }

    private function remove(string ...$name): void
    {
        if ($name === []) {
            return;
        }
        $this->map = $this->map->without(...$name);
        $requiredDiff = array_diff($this->requiredKeys->toArray(), $name);
        $optionalDiff = array_diff($this->optionalKeys->toArray(), $name);
        $this->requiredKeys = new Vector(...$requiredDiff);
        $this->optionalKeys = new Vector(...$optionalDiff);
        $this->assertMinimumOptional();
        $positions = [];
        foreach ($name as $key) {
            $pos = $this->index->find($key);
            if ($pos !== null) {
                $positions[] = $pos;
            }
        }
        $this->index = $this->index->withRemove(...$positions);
    }

    private function assertMinimumOptional(): void
    {
        if ($this->optionalMinimum > $this->optionalKeys()->count()) {
            throw new InvalidArgumentException(
                (string) message(
                    'Count must be less or equal to `%optional%`',
                    optional: strval($this->optionalMinimum)
                )
            );
        }
    }

    private function addProperty(string $property, string $name, ParameterInterface $parameter): void
    {
        if ($this->has($name)) {
            throw new OverflowException(
                (string) message(
                    'Parameter `%name%` has been already added',
                    name: $name
                )
            );
        }
        $this->{$property} = $this->{$property}->withPush($name);
        $this->map = $this->map->withPut($name, $parameter);
        $this->index = $this->index->withPush($name);
    }
}
