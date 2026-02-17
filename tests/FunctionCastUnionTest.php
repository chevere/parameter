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

use Chevere\Parameter\Interfaces\TypeInterface;
use Chevere\Parameter\Interfaces\UnionParameterInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function Chevere\Parameter\castUnion;
use function Chevere\Parameter\float;
use function Chevere\Parameter\getType;
use function Chevere\Parameter\int;
use function Chevere\Parameter\string;
use function Chevere\Parameter\union;

final class FunctionCastUnionTest extends TestCase
{
    #[DataProvider('dataProviderCastUnion')]
    public function testCastUnion(
        string $type,
        mixed $expected,
        mixed $value,
        UnionParameterInterface $union,
    ): void {
        $castUnion = castUnion($union, $value);
        $this->assertSame($expected, $castUnion);
        $this->assertSame($type, getType($castUnion));
    }

    public static function dataProviderCastUnion(): array
    {
        return [
            [
                TypeInterface::STRING,
                'bar',
                'bar',
                union(string(), int(), float()),
            ],
            [
                TypeInterface::INT,
                123,
                '123',
                union(string(), int(), float()),
            ],
            [
                TypeInterface::FLOAT,
                12.3,
                12.3,
                union(string(), int(), float()),
            ],
            [
                TypeInterface::FLOAT,
                12.3,
                '12.3',
                union(string(), int(), float()),
            ],
            [
                TypeInterface::STRING,
                'bar',
                'bar',
                union(string(), float()),
            ],
            [
                TypeInterface::FLOAT,
                123.0,
                123,
                union(string(), float()),
            ],
            [
                TypeInterface::FLOAT,
                12.3,
                12.3,
                union(string(), float()),
            ],
            [
                TypeInterface::FLOAT,
                12.3,
                '12.3',
                union(string(), float()),
            ],
            [
                TypeInterface::STRING,
                'bar',
                'bar',
                union(string(), int()),
            ],
            [
                TypeInterface::INT,
                123,
                '123',
                union(string(), int()),
            ],
            [
                TypeInterface::STRING,
                '12.3',
                12.3,
                union(string(), int()),
            ],
            [
                TypeInterface::STRING,
                '12.3',
                '12.3',
                union(string(), int()),
            ],
        ];
    }
}
