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

namespace Chevere\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function Chevere\Tests\src\usesVariadic;

final class FunctionAssertArgumentsTest extends TestCase
{
    public function testVariadic(): void
    {
        usesVariadic(1, 'hugo', 'paco', 'luis');
        $this->expectException(InvalidArgumentException::class);
        usesVariadic(1, 'hugo', '-');
    }
}
