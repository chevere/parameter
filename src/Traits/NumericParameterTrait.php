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

use Chevere\Parameter\Interfaces\FloatParameterInterface;
use Chevere\Parameter\Interfaces\IntParameterInterface;
use InvalidArgumentException;
use OverflowException;
use function Chevere\Message\message;

/**
 * @template-covariant TValue
 */
trait NumericParameterTrait
{
    /**
     * @var array<TValue>
     */
    private array $accept = [];

    /**
     * @var array<TValue>
     */
    private array $reject = [];

    private function errorOverflow(string $property, string $topic): string
    {
        return (string) message(
            'Cannot set %property% value when %topic% range is set',
            property: $property,
            topic: $topic,
        );
    }

    private function errorMinMaxArgument(string $target, string $conflict): string
    {
        return (string) message(
            'Cannot set %target% value greater or equal than %conflict% value',
            target: $target,
            conflict: $conflict,
        );
    }

    private function assertAcceptEmpty(string $message): void
    {
        if ($this->accept !== []) {
            throw new OverflowException($message);
        }
    }

    private function assertRejectEmpty(string $message): void
    {
        if ($this->reject !== []) {
            throw new OverflowException($message);
        }
    }

    private function setMin(int|float $value, int|float $max): void
    {
        $this->assertAcceptEmpty(
            $this->errorOverflow('min', 'accept')
        );
        $this->assertRejectEmpty(
            $this->errorOverflow('min', 'reject')
        );
        if ($value >= ($this->max ?? $max)) {
            throw new InvalidArgumentException(
                $this->errorMinMaxArgument('min', 'max')
            );
        }
        // @phpstan-ignore-next-line
        $this->min = $value;
    }

    private function setMax(int|float $value, int|float $min): void
    {
        $this->assertAcceptEmpty(
            $this->errorOverflow('max', 'accept')
        );
        $this->assertRejectEmpty(
            $this->errorOverflow('min', 'reject')
        );
        if ($value <= ($this->min ?? $min)) {
            throw new InvalidArgumentException(
                $this->errorMinMaxArgument('max', 'min')
            );
        }
        // @phpstan-ignore-next-line
        $this->max = $value;
    }

    private function setAccept(int|float ...$value): void
    {
        sort($value);
        // @phpstan-ignore-next-line
        $this->accept = $value;
        $this->min = null;
        $this->max = null;
        $this->assertAcceptReject();
    }

    private function assertAcceptReject(): void
    {
        $intersect = array_intersect($this->accept, $this->reject);
        if ($intersect !== []) {
            $accept = implode(', ', $this->accept);
            $reject = implode(', ', $this->reject);

            throw new InvalidArgumentException(
                (string) message(
                    'Accept list `%accept%` intersects with reject list `%reject%`',
                    accept: "[{$accept}]",
                    reject: "[{$reject}]",
                )
            );
        }
    }

    private function setReject(int|float ...$value): void
    {
        sort($value);
        // @phpstan-ignore-next-line
        $this->reject = $value;
        $this->min = null;
        $this->max = null;
        $this->assertAcceptReject();
    }

    private function assertNumericCompatible(
        IntParameterInterface|FloatParameterInterface $parameter
    ): void {
        $this->assertNumericList($parameter, 'accept');
        $this->assertNumericList($parameter, 'reject');
        $this->assertNumericLimit($parameter, 'min');
        $this->assertNumericLimit($parameter, 'max');
    }

    private function assertNumericList(
        IntParameterInterface|FloatParameterInterface $parameter,
        string $property
    ): void {
        // @infection-ignore-all
        $diff = array_merge(
            array_diff($this->{$property}, $parameter->{$property}()),
            array_diff($parameter->{$property}(), $this->{$property})
        );
        if ($diff !== []) {
            $value = implode(', ', $this->{$property});
            $provided = implode(', ', $parameter->{$property}());

            throw new InvalidArgumentException(
                (string) message(
                    'Expected %topic% values in `%expect%`, provided `%provided%`',
                    topic: $property,
                    expect: "[{$value}]",
                    provided: "[{$provided}]",
                )
            );
        }
    }

    private function assertNumericLimit(
        IntParameterInterface|FloatParameterInterface $parameter,
        string $property
    ): void {
        if ($this->{$property} !== $parameter->{$property}()) {
            $value = strval($this->{$property} ?? 'null');
            $provided = strval($parameter->{$property}() ?? 'null');

            throw new InvalidArgumentException(
                (string) message(
                    'Expected %topic% value `%value%`, provided `%provided%`',
                    topic: $property,
                    value: $value,
                    provided: $provided,
                )
            );
        }
    }

    /**
     * @return TValue
     */
    private function assert(
        int|float $argument
    ): int|float {
        if ($this->accept !== []) {
            if (in_array($argument, $this->accept, true)) {
                return $argument;
            }
            $values = implode(',', $this->accept);

            throw new InvalidArgumentException(
                (string) message(
                    'Argument value provided `%provided%` is not an accepted value in `%value%`',
                    provided: strval($argument),
                    value: "[{$values}]"
                )
            );
        }
        if ($this->reject !== []) {
            if (! in_array($argument, $this->reject, true)) {
                return $argument;
            }
            $values = implode(',', $this->reject);

            throw new InvalidArgumentException(
                (string) message(
                    'Argument value provided `%provided%` is on rejected list `%value%`',
                    provided: strval($argument),
                    value: "[{$values}]"
                )
            );
        }
        if ($this->min() !== null && $argument < $this->min()) {
            throw new InvalidArgumentException(
                (string) message(
                    'Argument value provided `%provided%` is less than `%min%`',
                    provided: strval($argument),
                    min: strval($this->min())
                )
            );
        }
        if ($this->max !== null && $argument > $this->max) {
            throw new InvalidArgumentException(
                (string) message(
                    'Argument value provided `%provided%` is greater than `%max%`',
                    provided: strval($argument),
                    max: strval($this->max)
                )
            );
        }

        return $argument;
    }
}
