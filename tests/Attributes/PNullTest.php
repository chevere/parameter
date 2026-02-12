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

use Chevere\Parameter\Attributes\PNull;
use PHPUnit\Framework\TestCase;
use TypeError;
use function Chevere\Parameter\null;

final class PNullTest extends TestCase
{
    public function testConstruct(): void
    {
        $parameter = null();
        $attr = new PNull();
        $this->assertEquals($parameter, $attr->parameter());
        $this->expectException(TypeError::class);
        $attr->__invoke(true);
    }
}
