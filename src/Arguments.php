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

use ArgumentCountError;
use ArrayAccess;
use Chevere\Parameter\Interfaces\ArgumentsInterface;
use Chevere\Parameter\Interfaces\CastInterface;
use Chevere\Parameter\Interfaces\ParametersInterface;
use Chevere\Parameter\Traits\ExceptionErrorMessageTrait;
use InvalidArgumentException;
use OutOfBoundsException;
use ReflectionClass;
use Throwable;
use TypeError;
use function Chevere\Message\message;

final class Arguments implements ArgumentsInterface
{
    use ExceptionErrorMessageTrait;

    private ParametersInterface $iterable;

    /**
     * @var array<int|string, mixed>
     */
    private array $arguments = [];

    /**
     * @var array<string>
     */
    private array $null = [];

    /**
     * @var array<string>
     */
    private array $reflected = [];

    /**
     * @var string[]
     */
    private array $errors = [];

    // @phpstan-ignore-next-line
    public function __construct(
        private ParametersInterface $parameters,
        array|ArrayAccess $arguments
    ) {
        if ($arguments instanceof ArrayAccess) {
            $arguments = $this->getArrayAccessArray($arguments);
        }
        if (array_is_list($arguments) && count($arguments) === count($parameters)) {
            $arguments = array_combine($parameters->keys(), $arguments);
        }
        $this->setArguments($arguments);
        if ($parameters->keys() === ['K', 'V']) {
            $pairs = [];
            foreach (array_keys($arguments) as $key) {
                $key = strval($key);
                $pairs[$key] = $parameters->get('V');
            }
            $this->iterable = new Parameters(...$pairs);
        }
        $this->assertNoArgumentsOverflow();
        $this->handleDefaults();
        $this->assertRequired();
        $this->assertMinimumOptional();
        $this->handleParameters();
        if ($this->errors !== []) {
            throw new InvalidArgumentException(
                implode('; ', $this->errors)
            );
        }
    }

    public function parameters(): ParametersInterface
    {
        return $this->iterable ?? $this->parameters;
    }

    // @phpstan-ignore-next-line
    public function toArray(): array
    {
        return $this->arguments;
    }

    // @phpstan-ignore-next-line
    public function toArrayFill(mixed $fill): array
    {
        $filler = array_fill_keys($this->null, $fill);
        /**
         * @infection-ignore-all (false positive)
         */
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
        $new->assertArgument($name, $value);
        $new->arguments[$name] = $value;

        return $new;
    }

    public function has(string ...$name): bool
    {
        foreach ($name as $key) {
            if (! array_key_exists($key, $this->arguments)) {
                return false;
            }
        }

        return true;
    }

    public function get(string $name): mixed
    {
        $this->parameters()->assertHas($name);

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
        if (! $this->parameters()->optionalKeys()->contains($name)) {
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

    private function assertNoArgumentsOverflow(): void
    {
        $overflow = array_diff(
            array_keys($this->arguments),
            $this->parameters()->keys()
        );
        if ($overflow !== []) {
            throw new ArgumentCountError(
                (string) message(
                    'Invalid argument(s) provided: `%extra%`',
                    extra: implode(', ', $overflow)
                )
            );
        }
    }

    private function handleDefaults(): void
    {
        foreach ($this->parameters() as $name => $parameter) {
            if ($this->has($name)) {
                continue;
            }
            if ($parameter->default() === null) {
                $this->null[] = $name;

                continue;
            }
            $this->arguments[$name] = $parameter->default();
        }
    }

    private function assertRequired(): void
    {
        $values = array_keys($this->arguments);
        $missing = array_diff(
            $this->parameters()->requiredKeys()->toArray(),
            $values,
        );
        if ($missing !== []) {
            throw new ArgumentCountError(
                (string) message(
                    'Missing required argument(s): `%missing%`',
                    missing: implode(', ', $missing)
                )
            );
        }
    }

    private function assertMinimumOptional(): void
    {
        $optional = $this->parameters()->optionalKeys()->toArray();
        $providedOptionals = array_intersect(
            $optional,
            array_keys($this->arguments)
        );
        $countProvided = count($providedOptionals);
        if ($countProvided < $this->parameters()->optionalMinimum()) {
            throw new ArgumentCountError(
                (string) message(
                    'Requires minimum **%minimum%** optional argument(s), **%provided%** provided',
                    minimum: strval($this->parameters()->optionalMinimum()),
                    provided: strval($countProvided)
                )
            );
        }
    }

    /**
     * @infection-ignore-all
     */
    private function assertArgument(string $name, mixed $argument): void
    {
        $parameter = $this->parameters()->get($name);

        try {
            $this->arguments[$name] = $parameter->__invoke($argument);
        } catch (TypeError $e) {
            throw new TypeError(
                $this->getExceptionPropertyMessage($name, $e)
            );
        } catch (Throwable $e) {
            throw new InvalidArgumentException(
                $this->getExceptionPropertyMessage($name, $e)
            );
        }
    }

    private function getExceptionPropertyMessage(string $property, Throwable $e): string
    {
        $message = $this->getExceptionMessage($e);

        return "[{$property}]: {$message}";
    }

    private function handleParameters(): void
    {
        foreach ($this->parameters()->keys() as $name) {
            if ($this->isSkipOptional($name)) {
                continue;
            }

            try {
                $this->assertArgument($name, $this->get($name));
            } catch (Throwable $e) {
                $this->errors[] = $e->getMessage();
            }
        }
    }

    private function isSkipOptional(string $name): bool
    {
        return $this->parameters()->optionalKeys()->contains($name)
            && ! $this->has($name);
    }

    /**
     * @param array<int|string, mixed> $arguments
     */
    private function setArguments(array $arguments): void
    {
        $keys = array_keys($arguments);
        foreach ($keys as $name) {
            $name = strval($name);
            $this->arguments[$name] = $arguments[$name];
        }
    }

    /**
     * @param ArrayAccess<int|string, mixed> $arguments
     * @return array<int|string, mixed>
     */
    private function getArrayAccessArray(ArrayAccess $arguments): array
    {
        $array = [];
        $cast = (array) $arguments;
        $reflector = new ReflectionClass($arguments);
        $properties = $reflector->getProperties();
        foreach ($properties as $property) {
            $name = $property->getName();
            $array[$name] = $property->getValue($arguments);
            $this->reflected[] = $name;
        }
        $this->fixScopeArrayCast($array, $cast);

        return $array;
    }

    /**
     * @param array<int|string, mixed> $array
     * @param array<int|string, mixed> $cast
     */
    private function fixScopeArrayCast(array &$array, array $cast): void
    {
        foreach ($cast as $key => $value) {
            $key = strval($key);
            if (str_contains($key, "\x00")
                || in_array($key, $this->reflected, true)) {
                continue;
            }
            $array[$key] = $value;
        }
    }
}
