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
use Chevere\Parameter\Interfaces\UnionParameterInterface;
use Chevere\Parameter\Parameters;
use Chevere\Parameter\Traits\AttrTrait;
use Chevere\Parameter\UnionParameter;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS_CONSTANT)]
class UnionAttr implements ParameterAttributeInterface
{
    use AttrTrait;

    private UnionParameterInterface $parameter;

    public function __construct(
        ParameterAttributeInterface ...$parameterAttribute,
    ) {
        $parameters = new Parameters();
        foreach ($parameterAttribute as $name => $attribute) {
            $name = (string) $name;
            $parameters = $parameters
                ->withRequired($name, $attribute->parameter());
        }
        $this->parameter = new UnionParameter($parameters);
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
