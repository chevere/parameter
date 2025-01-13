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

use Chevere\Parameter\Attributes\StringAttr;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function Chevere\Parameter\string;

final class StringAttrTest extends TestCase
{
    public function testConstruct(): void
    {
        $parameter = string('/[a-b]+/');
        $attr = new StringAttr('/[a-b]+/');
        $parameter = $attr->parameter();
        $this->assertEquals($parameter, $attr->parameter());
        $this->expectException(InvalidArgumentException::class);
        $attr->__invoke('2');
    }
}
