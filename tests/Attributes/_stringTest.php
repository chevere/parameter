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

use Chevere\Parameter\Attributes\_string;
use PHPUnit\Framework\TestCase;
use function Chevere\Parameter\string;

final class _stringTest extends TestCase
{
    public function testConstruct(): void
    {
        $parameter = string();
        $attr = new _string();
        $this->assertEquals($parameter, $attr->parameter());
        $attr->__invoke('2');
    }
}
