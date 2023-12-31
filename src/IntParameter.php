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

use Chevere\Parameter\Interfaces\IntParameterInterface;
use Chevere\Parameter\Interfaces\TypeInterface;
use Chevere\Parameter\Traits\NumericParameterTrait;
use Chevere\Parameter\Traits\ParameterTrait;

final class IntParameter implements IntParameterInterface
{
    use ParameterTrait;

    /**
     * @template-use NumericParameterTrait<int>
     */
    use NumericParameterTrait;

    private ?int $default = null;

    private ?int $min = null;

    private ?int $max = null;

    /**
     * @var int[]
     */
    private array $accept = [];

    public function __invoke(int $value): int
    {
        $this->assert($value);

        return $value;
    }

    public function withDefault(int $value): IntParameterInterface
    {
        $this($value);
        $new = clone $this;
        $new->default = $value;

        return $new;
    }

    public function withMin(int $value): IntParameterInterface
    {
        $new = clone $this;
        $new->setMin($value, self::MAX);

        return $new;
    }

    public function withMax(int $value): IntParameterInterface
    {
        $new = clone $this;
        $new->setMax($value, self::MIN);

        return $new;
    }

    public function withAccept(int ...$value): IntParameterInterface
    {
        $new = clone $this;
        $new->setAccept(...$value);

        return $new;
    }

    public function withReject(int ...$value): IntParameterInterface
    {
        $new = clone $this;
        $new->setReject(...$value);

        return $new;
    }

    public function default(): ?int
    {
        return $this->default;
    }

    public function min(): ?int
    {
        return $this->min;
    }

    public function max(): ?int
    {
        return $this->max;
    }

    public function accept(): array
    {
        return $this->accept;
    }

    public function reject(): array
    {
        return $this->reject;
    }

    public function schema(): array
    {
        return [
            'type' => $this->type()->primitive(),
            'description' => $this->description,
            'default' => $this->default,
            'min' => $this->min,
            'max' => $this->max,
            'accept' => $this->accept,
            'reject' => $this->reject,
        ];
    }

    public function assertCompatible(IntParameterInterface $parameter): void
    {
        $this->assertNumericCompatible($parameter);
    }

    private function typeName(): string
    {
        return TypeInterface::INT;
    }
}
