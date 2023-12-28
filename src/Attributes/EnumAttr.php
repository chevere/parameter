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
use Chevere\Parameter\Interfaces\RegexParameterInterface;
use function Chevere\Parameter\enum;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS_CONSTANT)]
class EnumAttr implements ParameterAttributeInterface
{
    public readonly RegexParameterInterface $parameter;

    public function __construct(
        string $string,
        string ...$strings,
    ) {
        $this->parameter = enum($string, ...$strings);
    }

    public function __invoke(string $string): string
    {
        return $this->parameter->__invoke($string);
    }

    public function parameter(): ParameterInterface
    {
        return $this->parameter;
    }
}
