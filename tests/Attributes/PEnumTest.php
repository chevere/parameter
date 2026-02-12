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

use Chevere\Parameter\Attributes\PEnum;
use PHPUnit\Framework\TestCase;
use function Chevere\Parameter\enum;

final class PEnumTest extends TestCase
{
    public function testConstruct(): void
    {
        $parameter = enum('super');
        $attr = new PEnum('super');
        $this->assertEquals($parameter, $attr->parameter());
        $attr->__invoke('super');
    }
}
