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

namespace Chevere\Tests\Attributes;

use Chevere\Parameter\Attributes\CallableAttr;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function Chevere\Parameter\int;

final class CallableAttrTest extends TestCase
{
    public function testConstruct(): void
    {
        $callable = fn () => int(min: 1);
        $attr = new CallableAttr($callable);
        $parameter = $attr->parameter();
        $this->assertEquals($parameter, $attr->parameter());
        $this->expectException(InvalidArgumentException::class);
        $attr->__invoke(0);
    }
}
