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

use Chevere\Parameter\Attributes\BoolAttr;
use PHPUnit\Framework\TestCase;
use function Chevere\Parameter\bool;

final class BoolAttrTest extends TestCase
{
    public function testConstruct(): void
    {
        $parameter = bool();
        $attr = new BoolAttr();
        $this->assertEquals($parameter, $attr->parameter());
    }

    public function testWithSensitive(): void
    {
        $parameter = bool(sensitive: true);
        $attr = new BoolAttr();
        $with = $attr->withIsSensitive();
        $this->assertNotEquals($attr, $with);
        $this->assertEquals($parameter, $with->parameter());
    }
}
