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

use Chevere\Parameter\Attributes\_null;
use PHPUnit\Framework\TestCase;
use TypeError;
use function Chevere\Parameter\null;

final class _nullTest extends TestCase
{
    public function testConstruct(): void
    {
        $parameter = null();
        $attr = new _null();
        $this->assertEquals($parameter, $attr->parameter());
        $this->expectException(TypeError::class);
        $attr->__invoke(true);
    }
}
