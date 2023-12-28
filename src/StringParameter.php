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

    private ?string $contains = null;

    private ?int $minLength = null;

    private ?int $maxLength = null;

    private ?int $length = null;

    public function __invoke(string|Stringable $value): string
    {
        // $value = strval($value);
        // if ($this->regex->match($value) !== []) {
        //     return $value;
        // }

        // throw new InvalidArgumentException(
        //     (string) message(
        //         "Argument value provided `%provided%` doesn't match the regex `%regex%`",
        //         provided: $value,
        //         regex: strval($this->regex),
        //     )
        // );

        return (string) $value;
    }

    public function schema(): array
    {
        return [
            'type' => $this->type()->primitive(),
            'description' => $this->description(),
            'default' => $this->default(),
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

    public function withContains(string $string): StringParameterInterface
    {
        $strlen = mb_strlen($string);
        $this->assertRuleLength('length', $strlen);
        $this->assertRuleLength('maxLength', $strlen);
        $new = clone $this;
        $new->contains = $string;

        return $new;
    }

    public function contains(): ?string
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
        $this->assertRuleLength('startsWith', $int);
        $this->assertRuleLength('endsWith', $int);
        $this->assertRuleLength('contains', $int);
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
        $this->assertRuleLength('startsWith', $int);
        $this->assertRuleLength('endsWith', $int);
        $this->assertRuleLength('contains', $int);
        $new = clone $this;
        $new->length = $int;

        return $new;
    }

    public function length(): ?int
    {
        return $this->length;
    }

    private function assertRuleLength(string $rule, int $length): void
    {
        if ($this->{$rule} === null) {
            return;
        }
        $ruleLength = match (is_int($this->{$rule})) {
            true => $this->{$rule},
            default => mb_strlen($this->{$rule}),
        };
        if ($length > $ruleLength) {
            throw new LogicException(
                (string) message(
                    'Argument value provided conflicts with `%rule%` rule length `%value%`',
                    rule: $rule,
                    value: $ruleLength,
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
