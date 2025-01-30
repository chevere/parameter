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
use Chevere\Parameter\Interfaces\NullParameterInterface;
use Chevere\Parameter\Interfaces\ParameterAttributeInterface;
use Chevere\Parameter\Interfaces\ParameterInterface;
use Chevere\Parameter\Traits\AttrTrait;
use function Chevere\Parameter\null;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS_CONSTANT)]
class NullAttr implements ParameterAttributeInterface
{
    use AttrTrait;

    private NullParameterInterface $parameter;

    public function __construct(
        string $description = '',
    ) {
        $this->parameter = null(
            description: $description,
        );
    }

    public function __invoke(mixed $null): mixed
    {
        return $this->parameter->__invoke($null);
    }

    public function parameter(): ParameterInterface
    {
        return $this->parameter;
    }
}
