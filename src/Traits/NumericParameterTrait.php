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
use function Chevere\Parameter\valMd;

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

    private bool $isSensitive = false;

    private function errorOverflow(string $property, string $topic): string
    {
        return sprintf(
            'Cannot set %s value when %s range is set',
            $property,
            $topic,
        );
    }

    private function errorMinMaxArgument(string $target, string $conflict): string
    {
        return sprintf(
            'Cannot set %s value greater or equal than %s value',
            $target,
            $conflict,
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
                sprintf(
                    'Accept list `%s` intersects with reject list `%s`',
                    "[{$accept}]",
                    "[{$reject}]",
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
                sprintf(
                    'Expected %s values in `%s`, provided `%s`',
                    $property,
                    "[{$value}]",
                    "[{$provided}]",
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
                sprintf(
                    'Expected %s value `%s`, provided `%s`',
                    $property,
                    $value,
                    $provided,
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
                sprintf(
                    'Argument value provided%s is not an accepted value in `%s`',
                    valMd($argument, $this->isSensitive),
                    "[{$values}]"
                )
            );
        }
        if ($this->reject !== []) {
            if (! in_array($argument, $this->reject, true)) {
                return $argument;
            }
            $values = implode(',', $this->reject);

            throw new InvalidArgumentException(
                sprintf(
                    'Argument value provided%s is on rejected list `%s`',
                    valMd($argument, $this->isSensitive),
                    "[{$values}]"
                )
            );
        }
        if ($this->min() !== null && $argument < $this->min()) {
            throw new InvalidArgumentException(
                sprintf(
                    'Argument value provided%s is less than `%s`',
                    valMd($argument, $this->isSensitive),
                    strval($this->min())
                )
            );
        }
        if ($this->max !== null && $argument > $this->max) {
            throw new InvalidArgumentException(
                sprintf(
                    'Argument value provided%s is greater than `%s`',
                    valMd($argument, $this->isSensitive),
                    strval($this->max)
                )
            );
        }

        return $argument;
    }
}
