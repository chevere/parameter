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
use Chevere\Parameter\Attributes\_unionNull;
use PHPUnit\Framework\TestCase;
use function Chevere\Parameter\int;
use function Chevere\Parameter\unionNull;

final class _unionNullTest extends TestCase
{
    public function testConstruct(): void
    {
        $parameter = unionNull(
            int(),
        );
        $attr = new _unionNull(
            new _int(),
        );
        $this->assertEquals($parameter, $attr->parameter());
        $attr->__invoke(null);
        $attr->__invoke(1);
    }
}
