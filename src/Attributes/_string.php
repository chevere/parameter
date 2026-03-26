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
use BackedEnum;
use Chevere\Parameter\Interfaces\ParameterAttributeInterface;
use Chevere\Parameter\Interfaces\StringParameterInterface;
use Chevere\Parameter\Traits\AttrTrait;
use Stringable;
use function Chevere\Parameter\string;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS_CONSTANT)]
class _string implements ParameterAttributeInterface
{
    use AttrTrait;

    private StringParameterInterface $parameter;

    public function __construct(
        string|Stringable|BackedEnum $regex = '',
        string $description = '',
        bool $sensitive = false,
        string $label = '',
    ) {
        $this->parameter = string(...get_defined_vars());
    }

    public function __invoke(string $string): string
    {
        return $this->parameter->__invoke($string);
    }
}
