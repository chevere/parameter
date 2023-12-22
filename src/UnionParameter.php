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
use Chevere\Parameter\Traits\ParameterTrait;
use Throwable;
use TypeError;
use function Chevere\Message\message;

final class UnionParameter implements UnionParameterInterface
{
    use ParameterTrait;
    use ArrayParameterTrait;
    use ParameterAssertArrayTypeTrait;

    /**
     * @var array<mixed, mixed>|null
     */
    private ?array $default = null;

    final public function __construct(
        private ParametersInterface $parameters,
        private string $description = '',
    ) {
        $this->type = $this->type();
        $this->parameters = $parameters;
    }

    public function __invoke(mixed $value): mixed
    {
        $types = [];
        $errors = [];
        foreach ($this->parameters() as $item) {
            try {
                assertNamedArgument('', $item, $value);

                return $value;
            } catch (Throwable $e) {
                $types[] = $item::class;
                $errors[] = $e->getMessage();
            }
        }
        $types = implode('|', $types);
        $errors = implode('; ', $errors);

        throw new TypeError(
            (string) message(
                "Argument provided doesn't match the union type `%types%`. Error(s): %errors%",
                types: $types,
                errors: $errors,
            )
        );
    }

    public function withAdded(ParameterInterface ...$parameter): static
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

    private function getType(): TypeInterface
    {
        return new Type(Type::UNION);
    }
}
