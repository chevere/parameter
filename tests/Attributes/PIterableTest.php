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

use Chevere\Parameter\Attributes\PInt;
use Chevere\Parameter\Attributes\PIterable;
use PHPUnit\Framework\TestCase;
use function Chevere\Parameter\int;
use function Chevere\Parameter\iterable;

final class PIterableTest extends TestCase
{
    public function testConstruct(): void
    {
        $parameter = iterable(
            int()
        );
        $attr = new PIterable(
            new PInt()
        );
        $this->assertEquals($parameter, $attr->parameter());
        $attr->__invoke([0]);
    }
}
