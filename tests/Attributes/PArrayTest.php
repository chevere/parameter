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

use Chevere\Parameter\Attributes\PArray;
use Chevere\Parameter\Attributes\PInt;
use PHPUnit\Framework\TestCase;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\int;

final class PArrayTest extends TestCase
{
    public function testConstruct(): void
    {
        $parameter = arrayp(
            key: int()
        );
        $attr = new PArray(
            key: new PInt()
        );
        $this->assertEquals($parameter, $attr->parameter());
        $attr->__invoke([
            'key' => 0,
        ]);
    }
}
