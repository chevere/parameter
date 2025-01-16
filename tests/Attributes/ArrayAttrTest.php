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

use Chevere\Parameter\Attributes\ArrayAttr;
use Chevere\Parameter\Attributes\IntAttr;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\int;

final class ArrayAttrTest extends TestCase
{
    public function testConstruct(): void
    {
        $parameter = arrayp(
            key: int(min: 1)
        );
        $attr = new ArrayAttr(
            key: new IntAttr(min: 1)
        );
        $parameter = $attr->parameter();
        $this->assertEquals($parameter, $attr->parameter());
        $this->expectException(InvalidArgumentException::class);
        $attr->__invoke([
            'key' => 0,
        ]);
    }
}
