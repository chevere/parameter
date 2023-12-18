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

use Chevere\Parameter\Interfaces\MixedParameterInterface;
use Chevere\Parameter\Interfaces\TypeInterface;
use Chevere\Parameter\Traits\ParameterTrait;
use Chevere\Parameter\Traits\SchemaTrait;

final class MixedParameter implements MixedParameterInterface
{
    use ParameterTrait;
    use SchemaTrait;

    private mixed $default = null;

    public function __invoke(mixed $value): mixed
    {
        return $value;
    }

    /**
     * @codeCoverageIgnore
     */
    public function assertCompatible(MixedParameterInterface $parameter): void
    {
    }

    public function withDefault(mixed $default): self
    {
        $new = clone $this;
        $new->default = $default;

        return $new;
    }

    public function default(): mixed
    {
        return $this->default;
    }

    private function getType(): TypeInterface
    {
        return new Type(Type::MIXED);
    }
}
