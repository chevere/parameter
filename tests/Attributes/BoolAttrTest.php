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

namespace Chevere\Tests\Parameter\Attributes;

use Chevere\Parameter\Attributes\BoolAttr;
use PHPUnit\Framework\TestCase;
use function Chevere\Parameter\bool;

final class BoolAttrTest extends TestCase
{
    public function testConstruct(): void
    {
        $parameter = bool(default: false);
        $attr = new BoolAttr(
            default: false
        );
        $parameter = $attr->parameter();
        $this->assertEquals($parameter, $attr->parameter());
    }
}
