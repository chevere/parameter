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

namespace Chevere\Tests\Attribute;

use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use function Chevere\Tests\src\noUsesAttr;
use function Chevere\Tests\src\usesAttr;

final class FunctionReturnAttrTest extends TestCase
{
    public function testUsesAttr(): void
    {
        $value = [
            'id' => 1,
            'role' => [
                'mask' => 64,
                'name' => 'admin',
                'tenants' => [1, 2, 3, 4, 5],
            ],
        ];
        usesAttr($value);
        $value = [
            'id' => 1,
            'role' => [
                'mask' => 64,
                'name' => 'admin',
                'tenants' => [6, 7, 8],
            ],
        ];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('[role]: [tenants]: [_V *iterable]: Argument value provided `6` is greater than `5`');
        usesAttr($value);
    }

    public function testNoUsesAttr(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('No applicable return rules to validate');
        noUsesAttr([]);
    }
}
