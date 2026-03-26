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
use Chevere\Parameter\Interfaces\FloatParameterInterface;
use Chevere\Parameter\Interfaces\ParameterAttributeInterface;
use Chevere\Parameter\Traits\AttrTrait;
use function Chevere\Parameter\float;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS_CONSTANT)]
class _float implements ParameterAttributeInterface
{
    use AttrTrait;

    private FloatParameterInterface $parameter;

    /**
     * @param float[] $accept
     * @param float[] $reject
     */
    public function __construct(
        string $description = '',
        ?float $min = null,
        ?float $max = null,
        array $accept = [],
        array $reject = [],
        bool $sensitive = false,
        string $label = '',
    ) {
        $this->parameter = float(...get_defined_vars());
    }

    public function __invoke(float $float): float
    {
        return $this->parameter->__invoke($float);
    }
}
