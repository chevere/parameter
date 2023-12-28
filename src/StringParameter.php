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
use Chevere\Parameter\Interfaces\StringParameterInterface;
use Chevere\Parameter\Interfaces\TypeInterface;
use Chevere\Parameter\Traits\ParameterTrait;
use InvalidArgumentException;
use LogicException;
use Stringable;
use function Chevere\Message\message;

final class StringParameter implements StringParameterInterface
{
    use ParameterTrait;

    private ?string $default = null;

    private ?string $startsWith = null;

    private ?string $endsWith = null;

    private ?int $minLength = null;

    private ?int $maxLength = null;

    private ?int $length = null;

    /**
     * @var array<string>
     */
    private array $contains = [];

    /**
     * @var array<string>
     */
    private array $reject = [];

    public function __invoke(string|Stringable $value): string
    {
        $value = (string) $value;
        if ($this->startsWith && ! str_starts_with($value, $this->startsWith)) {
            throw new InvalidArgumentException(
                (string) message(
                    "Argument `%value%` doesn't start with `%startsWith%`",
                    value: $value,
                    startsWith: $this->startsWith,
                )
            );
        }
        if ($this->endsWith && ! str_ends_with($value, $this->endsWith)) {
            throw new InvalidArgumentException(
                (string) message(
                    "Argument `%value%` doesn't ends with `%endsWith%`",
                    value: $value,
                    endsWith: $this->endsWith,
                )
            );
        }
        $strlen = mb_strlen($value);
        if ($this->minLength !== null && $strlen < $this->minLength) {
            throw new InvalidArgumentException(
                (string) message(
                    'Argument `%value%` length (%strlen%) is less than %minLength%',
                    value: $value,
                    strlen: $strlen,
                    minLength: $this->minLength,
                )
            );
        }
        if ($this->maxLength !== null && $strlen > $this->maxLength) {
            throw new InvalidArgumentException(
                (string) message(
                    'Argument `%value%` length (%strlen%) is greater than %maxLength%',
                    value: $value,
                    strlen: $strlen,
                    maxLength: $this->maxLength,
                )
            );
        }
        if ($this->length !== null && $strlen !== $this->length) {
            throw new InvalidArgumentException(
                (string) message(
                    'Argument `%value%` length (%strlen%) is different from %length%',
                    value: $value,
                    strlen: $strlen,
                    length: $this->length,
                )
            );
        }
        foreach ($this->contains as $string) {
            if (! str_contains($value, $string)) {
                throw new InvalidArgumentException(
                    (string) message(
                        "Argument `%value%` doesn't contain `%contains%`",
                        value: $value,
                        contains: $string,
                    )
                );
            }
        }
        foreach ($this->reject as $string) {
            if (str_contains($value, $string)) {
                throw new InvalidArgumentException(
                    (string) message(
                        'Argument `%value%` contains rejected value `%reject%`',
                        value: $value,
                        reject: $string,
                    )
                );
            }
        }

        return $value;
    }

    public function schema(): array
    {
        return [
            'type' => $this->type()->primitive(),
            'description' => $this->description(),
            'default' => $this->default(),
            'startsWith' => $this->startsWith(),
            'endsWith' => $this->endsWith(),
            'contains' => $this->contains(),
            'reject' => $this->reject(),
            'minLength' => $this->minLength(),
            'maxLength' => $this->maxLength(),
            'length' => $this->length(),
        ];
    }

    public function assertCompatible(StringParameterInterface $parameter): void
    {
    }

    public function withDefault(string $value): StringParameterInterface
    {
        $new = clone $this;
        $new->default = $new($value);

        return $new;
    }

    public function default(): ?string
    {
        return $this->default;
    }

    public function withStartsWith(string $string): StringParameterInterface
    {
        $strlen = mb_strlen($string);
        $this->assertRuleLength('length', $strlen);
        $this->assertRuleLength('maxLength', $strlen);
        $new = clone $this;
        $new->startsWith = $string;

        return $new;
    }

    public function startsWith(): ?string
    {
        return $this->startsWith;
    }

    public function withEndsWith(string $string): StringParameterInterface
    {
        $strlen = mb_strlen($string);
        $this->assertRuleLength('length', $strlen);
        $this->assertRuleLength('maxLength', $strlen);
        $new = clone $this;
        $new->endsWith = $string;

        return $new;
    }

    public function endsWith(): ?string
    {
        return $this->endsWith;
    }

    public function withContains(string ...$strings): StringParameterInterface
    {
        $new = clone $this;
        foreach ($strings as $string) {
            $strlen = mb_strlen($string);
            $this->assertRuleLength('length', $strlen);
            $this->assertRuleLength('maxLength', $strlen);
        }
        $new->contains = $strings;

        return $new;
    }

    public function contains(): array
    {
        return $this->contains;
    }

    public function withMinLength(int $int): StringParameterInterface
    {
        $this->assertInt($int);
        if ($this->length !== null) {
            throw new BadMethodCallException(
                (string) message(
                    'Unable to set `%setRule%` rule when `%rule%` rule is already set',
                    setRule: 'minLength',
                    rule: 'length',
                )
            );
        }
        $this->assertRuleLength('maxLength', $int);
        $new = clone $this;
        $new->minLength = $int;

        return $new;
    }

    public function minLength(): ?int
    {
        return $this->minLength;
    }

    public function withMaxLength(int $int): StringParameterInterface
    {
        $this->assertInt($int);
        if ($this->length !== null) {
            throw new LogicException(
                (string) message(
                    'Unable to set `%setRule%` rule when `%rule%` rule is already set',
                    setRule: 'maxLength',
                    rule: 'length',
                )
            );
        }
        $this->assertRuleLength(
            rule: 'startsWith',
            try: $int,
            operand: '<'
        );
        $this->assertRuleLength(
            rule: 'endsWith',
            try: $int,
            operand: '<'
        );
        $this->assertRuleLength(
            rule: 'contains',
            try: $int,
            operand: '<'
        );
        $this->assertRuleLength(
            rule: 'reject',
            try: $int,
            operand: '<'
        );
        $new = clone $this;
        $new->maxLength = $int;

        return $new;
    }

    public function maxLength(): ?int
    {
        return $this->maxLength;
    }

    public function withLength(int $int): StringParameterInterface
    {
        $this->assertInt($int);
        if ($this->minLength !== null || $this->maxLength !== null) {
            throw new LogicException(
                (string) message(
                    'Unable to set `%setRule%` rule when `%rule%` rule is already set',
                    setRule: 'length',
                    rule: 'minLength|maxLength',
                )
            );
        }
        $this->assertRuleLength(
            rule: 'startsWith',
            try: $int,
            operand: '<'
        );
        $this->assertRuleLength(
            rule: 'endsWith',
            try: $int,
            operand: '<'
        );
        $this->assertRuleLength(
            rule: 'contains',
            try: $int,
            operand: '<'
        );
        $this->assertRuleLength(
            rule: 'reject',
            try: $int,
            operand: '<'
        );
        $new = clone $this;
        $new->length = $int;

        return $new;
    }

    public function length(): ?int
    {
        return $this->length;
    }

    public function withReject(string ...$strings): StringParameterInterface
    {
        foreach ($strings as $string) {
            $strlen = mb_strlen($string);
            $this->assertRuleLength('length', $strlen);
            $this->assertRuleLength('maxLength', $strlen);
        }
        $new = clone $this;
        $new->reject = $strings;

        return $new;
    }

    public function reject(): array
    {
        return $this->reject;
    }

    // @phpstan-ignore-next-line
    private function assertRuleLength(
        string $rule,
        int $try,
        null|array|int|string $length = null,
        string $operand = '>'
    ): void {
        if ($this->{$rule} === null) {
            return;
        }
        $length = $length ?? $this->{$rule};
        if (is_array($length)) {
            foreach ($length as $string) {
                $this->assertRuleLength($rule, $try, $string, $operand);
            }

            return;
        }
        if (is_string($length)) {
            $length = mb_strlen($length);
        }
        // @infection-ignore-all
        $result = match (true) {
            $operand === '>' => $try > $length,
            $operand === '>=' => $try >= $length,
            $operand === '<' => $try < $length,
            $operand === '<=' => $try <= $length,
            default => $try > $length,
        };
        if ($result) {
            throw new LogicException(
                (string) message(
                    'Argument value provided conflicts with `%rule%` rule length `%ruleLength%`',
                    rule: $rule,
                    ruleLength: $length,
                )
            );
        }
    }

    private function assertInt(int $value): void
    {
        int(min: 0)($value);
    }

    private function typeName(): string
    {
        return TypeInterface::STRING;
    }
}
