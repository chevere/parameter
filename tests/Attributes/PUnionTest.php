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
use Chevere\Parameter\Attributes\PNull;
use Chevere\Parameter\Attributes\PUnion;
use PHPUnit\Framework\TestCase;
use function Chevere\Parameter\int;
use function Chevere\Parameter\null;
use function Chevere\Parameter\union;

final class PUnionTest extends TestCase
{
    public function testConstruct(): void
    {
        $parameter = union(
            int(),
            null()
        );
        $attr = new PUnion(
            new PInt(),
            new PNull()
        );
        $this->assertEquals($parameter, $attr->parameter());
        $attr->__invoke(null);
        $attr->__invoke(1);
    }
}
