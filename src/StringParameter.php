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

use Chevere\Parameter\Interfaces\StringParameterInterface;
use Chevere\Parameter\Interfaces\TypeInterface;
use Chevere\Parameter\Traits\ParameterTrait;
use Chevere\Regex\Interfaces\RegexInterface;
use Chevere\Regex\Regex;
use InvalidArgumentException;
use Stringable;
use function Chevere\Message\message;

final class StringParameter implements StringParameterInterface
{
    use ParameterTrait;

    private RegexInterface $regex;

    private ?string $default = null;

    public function __invoke(string|Stringable $value): string
    {
        $value = strval($value);
        if ($this->regex->match($value) !== []) {
            return $value;
        }

        throw new InvalidArgumentException(
            (string) message(
                "Argument value provided `%provided%` doesn't match the regex `%regex%`",
                provided: $value,
                regex: strval($this->regex),
            )
        );
    }

    public function setUp(): void
    {
        $this->regex = new Regex(self::PATTERN_DEFAULT);
    }

    public function withRegex(RegexInterface $regex): StringParameterInterface
    {
        $new = clone $this;
        $new->regex = $regex;

        return $new;
    }

    public function withDefault(string $value): StringParameterInterface
    {
        $this($value);
        $new = clone $this;
        $new->default = $value;

        return $new;
    }

    public function regex(): RegexInterface
    {
        return $this->regex;
    }

    public function default(): ?string
    {
        return $this->default;
    }

    public function schema(): array
    {
        return [
            'type' => $this->type()->primitive(),
            'description' => $this->description(),
            'default' => $this->default(),
            'regex' => $this->regex()->noDelimiters(),
        ];
    }

    public function assertCompatible(StringParameterInterface $parameter): void
    {
        if ($this->regex->__toString() === $parameter->regex()->__toString()) {
            return;
        }

        throw new InvalidArgumentException(
            (string) message(
                'Expected regex `%expected%`, provided `%provided%`',
                expected: $this->regex->__toString(),
                provided: $parameter->regex()->__toString()
            )
        );
    }

    private function typeName(): string
    {
        return TypeInterface::STRING;
    }
}
