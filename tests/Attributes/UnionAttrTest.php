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

use Chevere\Parameter\Attributes\IntAttr;
use Chevere\Parameter\Attributes\NullAttr;
use Chevere\Parameter\Attributes\UnionAttr;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function Chevere\Parameter\int;
use function Chevere\Parameter\null;
use function Chevere\Parameter\union;

final class UnionAttrTest extends TestCase
{
    public function testConstruct(): void
    {
        $parameter = union(
            int(min: 1),
            null()
        );
        $attr = new UnionAttr(
            new IntAttr(min: 1),
            new NullAttr()
        );
        $parameter = $attr->parameter();
        $this->assertEquals($parameter, $attr->parameter());
        $attr->__invoke(null);
        $attr->__invoke(1);
        $this->expectException(InvalidArgumentException::class);
        $attr->__invoke(0);
    }
}
