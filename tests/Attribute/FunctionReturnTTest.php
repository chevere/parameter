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
use PHPUnit\Framework\TestCase;
use function Chevere\Tests\src\noUsesAttr;
use function Chevere\Tests\src\usesAttr;
use function Chevere\Tests\src\usesSensitiveParameterAttr;

final class FunctionReturnTTest extends TestCase
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
        $this->expectExceptionMessage(
            <<<PLAIN
            [role]: [tenants]: [V *iterable]: Argument value provided `6` is greater than `5`
            PLAIN
        );
        usesAttr($value);
    }

    public function testNoUsesAttrFallbackMixed(): void
    {
        $this->expectNotToPerformAssertions();
        noUsesAttr([]);
    }

    public function testUsesSensitiveParameterAttr(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            [code]: Argument value provided is less than `1000`
            [password]: Argument value provided doesn't match the regex `#^super|safe$#`
            PLAIN
        );
        usesSensitiveParameterAttr(999, 'password');
    }
}
