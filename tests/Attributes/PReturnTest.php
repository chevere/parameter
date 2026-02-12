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
use Chevere\Parameter\Attributes\PReturn;
use PHPUnit\Framework\TestCase;
use function Chevere\Parameter\int;

final class PReturnTest extends TestCase
{
    public function testConstruct(): void
    {
        $parameter = int();
        $attr = new PReturn(
            new PInt()
        );
        $this->assertEquals($parameter, $attr->parameter());
    }
}
