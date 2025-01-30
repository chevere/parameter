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
use Chevere\Parameter\Interfaces\IterableParameterInterface;
use Chevere\Parameter\Interfaces\ParameterAttributeInterface;
use Chevere\Parameter\Interfaces\ParameterInterface;
use Chevere\Parameter\Traits\AttrTrait;
use function Chevere\Parameter\iterable;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS_CONSTANT)]
class IterableAttr implements ParameterAttributeInterface
{
    use AttrTrait;

    private IterableParameterInterface $parameter;

    public function __construct(
        ParameterAttributeInterface $V,
        ?ParameterAttributeInterface $K = null,
        string $description = '',
        bool $sensitive = false
    ) {
        $this->parameter = iterable(
            V: $V->parameter(),
            K: $K?->parameter(),
            description: $description,
            sensitive: $sensitive
        );
    }

    // @phpstan-ignore-next-line
    public function __invoke(iterable $array): iterable
    {
        return $this->parameter->__invoke($array);
    }

    public function parameter(): ParameterInterface
    {
        return $this->parameter;
    }
}
