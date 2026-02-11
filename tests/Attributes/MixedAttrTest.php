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

use Chevere\Parameter\Attributes\MixedAttr;
use PHPUnit\Framework\TestCase;
use function Chevere\Parameter\mixed;

final class MixedAttrTest extends TestCase
{
    public function testConstruct(): void
    {
        $parameter = mixed();
        $attr = new MixedAttr();
        $this->assertEquals($parameter, $attr->parameter());
    }
}
