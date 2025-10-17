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

use Chevere\Parameter\Interfaces\ArgumentsInterface;
use Chevere\Parameter\Interfaces\CastInterface;
use Chevere\Parameter\Interfaces\ParametersAccessInterface;
use Chevere\Parameter\Traits\ArgumentsTrait;
use Chevere\Parameter\Traits\ExceptionErrorMessageTrait;
use InvalidArgumentException;
use OutOfBoundsException;
use TypeError;
use function Chevere\Message\message;

final class Arguments implements ArgumentsInterface
{
    use ExceptionErrorMessageTrait;

    /**
     * @template-use ArgumentsTrait<mixed>
     */
    use ArgumentsTrait;

    // @phpstan-ignore-next-line
    public function toArrayFill(mixed $fill): array
    {
        $filler = array_fill_keys($this->null, $fill);

        return array_merge($filler, $this->arguments);
    }

    /**
     * @throws OutOfBoundsException
     * @throws TypeError
     * @throws InvalidArgumentException
     */
    public function withPut(string $name, mixed $value): ArgumentsInterface
    {
        $new = clone $this;
        $new->assertSetArgument($name, $value);
        $new->arguments[$name] = $value;

        return $new;
    }

    public function get(string $name): mixed
    {
        $this->parameters()->assertHas($name);
        if (! array_key_exists($name, $this->arguments)) {
            $name = array_search($name, $this->parameters()->keys(), true);
        }

        return $this->arguments[$name] ?? null;
    }

    public function required(string $name): CastInterface
    {
        if ($this->parameters()->optionalKeys()->contains($name)) {
            throw new InvalidArgumentException(
                (string) message(
                    'Argument `%name%` is optional',
                    name: $name
                )
            );
        }

        return new Cast($this->arguments[$name]);
    }

    public function optional(string $name): ?CastInterface
    {
        if ($this->parameters()->has($name)
            && ! $this->parameters()->optionalKeys()->contains($name)
        ) {
            throw new InvalidArgumentException(
                (string) message(
                    'Argument `%name%` is required',
                    name: $name
                )
            );
        }

        if ($this->has($name)) {
            return new Cast($this->arguments[$name]);
        }

        return null;
    }

    public function nested(string $key, string ...$lookup): ArgumentsInterface
    {
        $this->parameters()->assertHas($key);
        $currentKey = $key;
        $currentParameter = $this->parameters()->get($currentKey);
        $currentArgument = $this->arguments[$currentKey] ?? null;
        foreach ($lookup as $nestedKey) {
            if (! ($currentParameter instanceof ParametersAccessInterface)) {
                throw new TypeError(
                    (string) message(
                        'Parameter `%name%` is not a nested set of parameters',
                        name: $currentKey
                    )
                );
            }
            $parameters = $currentParameter->parameters();
            if (! $parameters->has($nestedKey)) {
                throw new InvalidArgumentException(
                    (string) message(
                        'Key `%key%` not found in nested parameter `%parent%`',
                        key: $nestedKey,
                        parent: $currentKey
                    )
                );
            }
            if (! is_array($currentArgument) || ! array_key_exists($nestedKey, $currentArgument)) {
                throw new InvalidArgumentException(
                    (string) message(
                        'Key `%key%` not found in nested argument `%parent%`',
                        key: $nestedKey,
                        parent: $currentKey
                    )
                );
            }
            $currentKey = $nestedKey;
            $currentParameter = $parameters->get($nestedKey);
            $currentArgument = $currentArgument[$nestedKey];
        }
        if (! is_array($currentArgument)) {
            throw new TypeError(
                (string) message(
                    'Argument `%name%` is not a nested set of arguments',
                    name: $currentKey
                )
            );
        }
        if ($currentParameter instanceof ParametersAccessInterface) {
            return new self(
                $currentParameter->parameters(),
                $currentArgument
            );
        }

        throw new TypeError(
            (string) message(
                'Parameter `%name%` is not a nested set of parameters',
                name: $currentKey
            )
        );
    }
}
