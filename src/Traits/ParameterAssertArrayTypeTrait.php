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

use Chevere\Parameter\Interfaces\ParametersAccessInterface;
use Chevere\Parameter\Interfaces\ParametersInterface;
use InvalidArgumentException;
use OutOfBoundsException;
use TypeError;

trait ParameterAssertArrayTypeTrait
{
    private ParametersInterface $parameters;

    private function assertArrayType(ParametersAccessInterface $parameter): void
    {
        $parametersCount = $this->parameters->count();
        $providedCount = $parameter->parameters()
            ->count();
        if ($parametersCount === 0 || $providedCount === 0) {
            return;
        }
        foreach ($this->parameters as $name => $item) {
            try {
                $tryParameter = $parameter->parameters()
                    ->get($name);
            } catch (OutOfBoundsException) {
                throw new OutOfBoundsException(
                    sprintf(
                        'Parameter `%s` not found',
                        $name
                    )
                );
            }

            try {
                $item->assertCompatible($tryParameter);
            } catch (TypeError) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Parameter `%s` of type `%s` is not compatible with type `%s`',
                        $name,
                        $item::class,
                        $tryParameter::class,
                    )
                );
            }
        }
    }
}
