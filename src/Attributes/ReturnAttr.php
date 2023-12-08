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

namespace Chevere\Parameter\Attributes;

use Attribute;
use Chevere\Parameter\Interfaces\ParameterAttributeInterface;
use Chevere\Parameter\Interfaces\ParameterInterface;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION)]
class ReturnAttr implements ParameterAttributeInterface
{
    public readonly ParameterInterface $parameter;

    public function __construct(ParameterAttributeInterface $attribute)
    {
        $this->parameter = $attribute->parameter();
    }

    public function __invoke(mixed $mixed): mixed
    {
        return $this->parameter->__invoke($mixed);
    }

    public function parameter(): ParameterInterface
    {
        return $this->parameter;
    }
}
