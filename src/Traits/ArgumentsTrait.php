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

use ArgumentCountError;
use Chevere\Parameter\Interfaces\ArrayTypeParameterInterface;
use Chevere\Parameter\Interfaces\ParametersAccessInterface;
use Chevere\Parameter\Interfaces\ParametersInterface;
use Chevere\Parameter\Parameters;
use InvalidArgumentException;
use SensitiveParameterValue;
use Throwable;
use TypeError;
use function Chevere\Message\message;
use function Chevere\Parameter\getParameters;

/**
 * @template-covariant TValue
 */
trait ArgumentsTrait
{
    private ParametersInterface $parameters;

    private ParametersInterface $iterable;

    /**
     * @var array<int|string, TValue>
     */
    private array $arguments = [];

    /**
     * @var array<string>
     */
    private array $null = [];

    /**
     * @var string[]
     */
    private array $errors = [];

    private bool $isPositional;

    private bool $isIterable;

    private int $count;

    // @phpstan-ignore-next-line
    public function __construct(
        ParametersInterface|ParametersAccessInterface $parameters,
        array $arguments
    ) {
        $this->parameters = getParameters($parameters);
        $this->isIterable = $this->parameters->keys() === ['K', 'V'];
        $this->isPositional = array_is_list($arguments) && ! $this->isIterable;
        if ($this->isIterable) {
            $this->iterable = new Parameters();
        }
        $this->arguments = $arguments;
        $this->excludeExtraArguments();
        $this->handleDefaults();
        $this->count = count($this->arguments);
        $this->assertArgumentCount();
        $this->assertMinimumOptional();
        $this->assertValues();
        if ($this->errors !== []) {
            throw new InvalidArgumentException(
                implode("\n", $this->errors)
            );
        }
    }

    public function parameters(): ParametersInterface
    {
        return $this->iterable ?? $this->parameters;
    }

    /**
     * @return array<int|string, TValue>
     */
    public function toArray(): array
    {
        return $this->arguments;
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

    private function excludeExtraArguments(): void
    {
        if ($this->parameters->isVariadic()
            || $this->isIterable
            || count($this->parameters) === 0
        ) {
            return;
        }
        if ($this->isPositional) {
            $this->arguments = array_slice(
                $this->arguments,
                0,
                $this->parameters->count()
            );

            return;
        }
        $count = 0;
        foreach (array_keys($this->arguments) as $key) {
            if ($count >= $this->parameters->count()) {
                unset($this->arguments[$key]);

                continue;
            }
            if (is_string($key)) {
                $name = $key;
            } else {
                $name = $this->parameters->keys()[$key] ?? null;
            }
            if ($name && $this->parameters->has($name)) {
                $count++;

                continue;
            }

            unset($this->arguments[$key]);
        }
    }

    /**
     * When adding default values it will always name the default argument.
     */
    private function handleDefaults(): void
    {
        foreach ($this->parameters as $id => $parameter) {
            if ($parameter instanceof ArrayTypeParameterInterface) {
                $hasStock = array_key_exists($id, $this->arguments);
                $this->handleDefaultNested($parameter, $id);
                if (! $hasStock
                    && ($this->arguments[$id] ?? []) === []
                ) {
                    unset($this->arguments[$id]);
                }

                continue;
            }
            if (! array_key_exists($id, $this->arguments)) {
                $namedPos = array_search($id, $this->parameters->keys());
                $name = $id;
                $id = strval($namedPos);
            }
            if ($this->has($id)) {
                continue;
            }
            if ($parameter->default() === null) {
                $this->null[] = $name ?? $id;

                continue;
            }
            // @phpstan-ignore-next-line
            $this->arguments[$name ?? $id] = $parameter->default();
        }
    }

    private function handleDefaultNested(
        ParametersAccessInterface $access,
        string ...$id
    ): void {
        if ($access->parameters()->keys() === ['K', 'V']) {
            return;
        }
        // If union?
        foreach ($access->parameters() as $name => $parameter) {
            if ($parameter instanceof ArrayTypeParameterInterface) {
                $args = $id;
                $args[] = $name;
                $this->handleDefaultNested($parameter, ...$args);

                continue;
            }
            /** @var array<int|string, mixed> $current */
            $current = &$this->arguments;
            foreach ($id as $key) {
                if (! array_key_exists($key, $current ?? [])) {
                    $current[$key] = [];
                }
                $current = &$current[$key];
            }
            if (! is_array($current)) {
                continue;
            }
            if (array_key_exists($name, $current)) {
                unset($current);

                continue;
            }
            if ($parameter->default() !== null) {
                $current[$name] = $parameter->default();
            }
        }
    }

    private function assertArgumentCount(): void
    {
        $requiredKeys = $this->parameters()->requiredKeys()->toArray();
        if (count($requiredKeys) <= $this->count) {
            return;
        }
        $argumentKeys = array_keys($this->arguments);
        if ($this->isPositional) {
            $missing = array_diff(
                array_keys($requiredKeys),
                $argumentKeys
            );
            $missing = array_map(
                fn (int $key) => $requiredKeys[$key],
                $missing
            );
        } else {
            $missing = array_diff($requiredKeys, $argumentKeys);
        }

        throw new ArgumentCountError(
            (string) message(
                'Missing required argument(s): `%missing%`',
                missing: implode(', ', $missing)
            )
        );
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
    private function assertSetArgument(string $name, mixed $argument, null|int|string $key = null): void
    {
        $parameter = $this->parameters()->get($name);
        $property = $name;
        if ($key !== null) {
            $property = $key . '...' . $name;
        }
        if (
            version_compare(PHP_VERSION, '8.2.0', '>=')
            && class_exists('SensitiveParameterValue')
            && $argument instanceof SensitiveParameterValue
        ) {
            $argument = $argument->getValue();
        }

        try {
            /** @var TValue $argument */
            $argument = $parameter->__invoke($argument);
            if (isset($key)) {
                $this->arguments[$key] = $argument;
            } elseif (array_key_exists($name, $this->arguments)) {
                $this->arguments[$name] = $argument;
            }
        } catch (TypeError $e) {
            throw new $e(
                $this->getExceptionPropertyMessage($property, $e)
            );
        } catch (Throwable $e) {
            throw new InvalidArgumentException(
                $this->getExceptionPropertyMessage($property, $e)
            );
        }
    }

    private function getExceptionPropertyMessage(string $name, Throwable $e): string
    {
        $message = $this->getExceptionMessage($e);

        return "[{$name}]: {$message}";
    }

    private function assertValues(): void
    {
        $lastPos = array_key_last($this->parameters()->keys());
        $argumentsKeys = array_keys($this->arguments);
        foreach ($this->parameters()->keys() as $pos => $name) {
            if ($pos === $lastPos && $this->parameters->isVariadic()) {
                if ($this->isPositional) {
                    $variadicKeys = array_slice($argumentsKeys, $pos);
                } else {
                    $variadicKeys = array_filter(
                        $argumentsKeys,
                        fn (string|int $key) => ! in_array($key, $this->parameters->keys(), true)
                            && ! (is_int($key) && $key < $pos)
                    );
                }

                foreach ($variadicKeys as $id) {
                    try {
                        $this->assertSetArgument($name, $this->arguments[$id], $id);
                    } catch (Throwable $e) {
                        $this->errors[] = $e->getMessage();
                    }
                }

                break;
            }
            if ($this->isSkipOptional($name)) {
                continue;
            }

            try {
                $this->assertSetArgument($name, $this->get($name));
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
}
