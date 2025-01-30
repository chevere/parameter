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
use Chevere\Parameter\Interfaces\BoolParameterInterface;
use Chevere\Parameter\Interfaces\ParameterAttributeInterface;
use Chevere\Parameter\Interfaces\ParameterInterface;
use Chevere\Parameter\Traits\AttrTrait;
use function Chevere\Parameter\bool;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS_CONSTANT)]
class BoolAttr implements ParameterAttributeInterface
{
    use AttrTrait;

    private BoolParameterInterface $parameter;

    public function __construct(
        string $description = '',
        ?bool $default = null,
        bool $sensitive = false
    ) {
        $this->parameter = bool(
            description: $description,
            default: $default,
            sensitive: $sensitive
        );
    }

    public function __invoke(bool $bool): bool
    {
        return $this->parameter->__invoke($bool);
    }

    public function parameter(): ParameterInterface
    {
        return $this->parameter;
    }
}
