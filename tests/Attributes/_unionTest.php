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

use Chevere\Parameter\Attributes\_int;
use Chevere\Parameter\Attributes\_null;
use Chevere\Parameter\Attributes\_union;
use PHPUnit\Framework\TestCase;
use function Chevere\Parameter\int;
use function Chevere\Parameter\null;
use function Chevere\Parameter\union;

final class _unionTest extends TestCase
{
    public function testConstruct(): void
    {
        $parameter = union(
            int(),
            null()
        );
        $attr = new _union(
            new _int(),
            new _null()
        );
        $this->assertEquals($parameter, $attr->parameter());
        $attr->__invoke(null);
        $attr->__invoke(1);
    }
}
