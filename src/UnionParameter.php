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
use Chevere\Parameter\Traits\ParameterAssertArrayTypeTrait;
use Chevere\Parameter\Traits\ParameterErrorMessageTrait;
use Chevere\Parameter\Traits\ParameterTrait;
use InvalidArgumentException;
use LogicException;
use Throwable;
use function Chevere\Message\message;

final class UnionParameter implements UnionParameterInterface
{
    use ParameterTrait;
    use ArrayParameterTrait;
    use ParameterAssertArrayTypeTrait;
    use ParameterErrorMessageTrait;

    private mixed $default = null;

    final public function __construct(
        private ParametersInterface $parameters,
        private string $description = '',
    ) {
        $this->type = $this->type();
        if ($parameters->count() < 2) {
            throw new LogicException(
                (string) message(
                    'Must pass at least two parameters for union'
                )
            );
        }

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
        $this->assertArrayType($parameter);
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
        $message = $e->getMessage();
        $strstr = strstr($message, '::__invoke():', false);
        if (! is_string($strstr)) {
            $message = $message; // @codeCoverageIgnore
        } else {
            $message = substr($strstr, 14);
        }
        $calledIn = strpos($message, ', called in');
        $message = $calledIn
            ? substr($message, 0, $calledIn)
            : $message;

        return <<<PLAIN
        Parameter `{$name}` <{$type}>: {$message}
        PLAIN;
    }

    private function typeName(): string
    {
        return TypeInterface::UNION;
    }
}
