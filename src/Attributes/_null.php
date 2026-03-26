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
use Chevere\Parameter\Traits\AttrTrait;
use function Chevere\Parameter\null;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS_CONSTANT)]
class _null implements ParameterAttributeInterface
{
    use AttrTrait;

    private NullParameterInterface $parameter;

    public function __construct(
        string $description = '',
        string $label = '',
    ) {
        $this->parameter = null(...get_defined_vars());
    }

    public function __invoke(mixed $null): mixed
    {
        return $this->parameter->__invoke($null);
    }
}
