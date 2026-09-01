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

use Chevere\Parameter\Interfaces\ParameterInterface;
use Chevere\Parameter\Interfaces\ParametersInterface;
use Chevere\Parameter\Interfaces\TypeInterface;
use Chevere\Parameter\Interfaces\UnionParameterInterface;
use Chevere\Parameter\Traits\ArrayParameterTrait;
use Chevere\Parameter\Traits\ExceptionErrorMessageTrait;
use Chevere\Parameter\Traits\ParameterAssertArrayTypeTrait;
use Chevere\Parameter\Traits\ParameterTrait;
use InvalidArgumentException;
use LogicException;
use Throwable;
use TypeError;
use function Chevere\Message\message;

final class UnionParameter implements UnionParameterInterface
{
    use ParameterTrait;
    use ArrayParameterTrait;
    use ParameterAssertArrayTypeTrait;
    use ExceptionErrorMessageTrait;

    private mixed $default = null;

    public function __construct(
        private ParametersInterface $parameters,
        private string $description = '',
    ) {
        if ($parameters->count() < 2) {
            throw new LogicException(
                (string) message(
                    'Must pass at least two parameters for union'
                )
            );
        }
        $types = array_map(
            fn (ParameterInterface $parameter): string => $parameter->type()
                ->typeHinting(),
            iterator_to_array($this->parameters)
        );
        $this->type = new Type(TypeInterface::UNION, ...$types);
        $this->parameters = $parameters;
    }

    public function __invoke(mixed $value): mixed
    {
        $messages = [];
        foreach ($this->parameters() as $name => $parameter) {
            try {
                return $parameter->__invoke($value);
            } catch (Throwable $e) {
                $messages[] = $this->getParameterError($parameter, $name, $e);
            }
        }
        $message = implode('; ', $messages);

        throw new InvalidArgumentException(
            (string) message(
                "Argument provided doesn't match union: %message%",
                message: $message,
            )
        );
    }

    public function withDefault(mixed $default): UnionParameterInterface
    {
        $this($default);
        $new = clone $this;
        $new->default = $default;

        return $new;
    }

    public function default(): mixed
    {
        return $this->default;
    }

    public function withAdded(ParameterInterface ...$parameter): UnionParameterInterface
    {
        $new = clone $this;
        foreach ($parameter as $name => $item) {
            $name = strval($name);
            $new->parameters = $new->parameters
                ->withRequired($name, $item);
        }

        return $new;
    }

    public function assertCompatible(UnionParameterInterface $parameter): void
    {
        $types = [];
        foreach ($this->parameters() as $item) {
            $types[$item->type()->typeHinting()] = $item;
        }

        $provided = [];
        foreach ($parameter->parameters() as $item) {
            $provided[$item->type()->typeHinting()] = $item;
        }

        $missing = array_diff_key($types, $provided);
        $extra = array_diff_key($provided, $types);
        if ($missing !== [] || $extra !== []) {
            $expected = implode('|', array_keys($types));
            $providedTypes = implode('|', array_keys($provided));

            throw new InvalidArgumentException(
                (string) message(
                    'Union types `%expected%` not compatible with `%provided%`',
                    expected: $expected,
                    provided: $providedTypes,
                )
            );
        }

        foreach ($types as $type => $item) {
            try {
                $item->assertCompatible($provided[$type]);
            } catch (TypeError) {
                throw new InvalidArgumentException(
                    (string) message(
                        'Union type `%type%` is not compatible with provided type `%provided%`',
                        type: $type,
                        provided: $provided[$type]::class,
                    )
                );
            }
        }
    }

    public function typeSchema(): string
    {
        return $this->type->primitive();
    }

    private function getParameterError(
        ParameterInterface $parameter,
        string $name,
        Throwable $e
    ): string {
        $type = $parameter::class;
        $message = $this->getExceptionMessage($e);

        return <<<PLAIN
        Parameter `{$name}` <{$type}>: {$message}
        PLAIN;
    }

    private function typeName(): string
    {
        return TypeInterface::UNION;
    }
}
