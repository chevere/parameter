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
use Chevere\Parameter\Interfaces\MixedParameterInterface;
use Chevere\Parameter\Interfaces\ParameterAttributeInterface;
use Chevere\Parameter\Interfaces\ParameterInterface;
use Chevere\Parameter\Traits\AttrTrait;
use function Chevere\Parameter\mixed;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS_CONSTANT)]
class MixedAttr implements ParameterAttributeInterface
{
    use AttrTrait;

    private MixedParameterInterface $parameter;

    public function __construct(
        string $description = '',
        bool $sensitive = false
    ) {
        $this->parameter = mixed(
            description: $description,
            sensitive: $sensitive
        );
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
