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

use Chevere\Parameter\Interfaces\NullParameterInterface;
use Chevere\Parameter\Interfaces\TypeInterface;
use Chevere\Parameter\Traits\ParameterDefaultNullTrait;
use Chevere\Parameter\Traits\ParameterTrait;
use Chevere\Parameter\Traits\SchemaTrait;
use TypeError;
use function Chevere\Message\message;

final class NullParameter implements NullParameterInterface
{
    use ParameterTrait;
    use ParameterDefaultNullTrait;
    use SchemaTrait;

    public function __invoke(mixed $value): mixed
    {
        if ($value === null) {
            return $value;
        }

        throw new TypeError(
            (string) message('Argument value provided is not of type null')
        );
    }

    /**
     * @codeCoverageIgnore
     */
    public function assertCompatible(NullParameterInterface $parameter): void
    {
    }

    private function typeName(): string
    {
        return TypeInterface::NULL;
    }
}
