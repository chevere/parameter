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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;
use function Chevere\Parameter\int;
use function Chevere\Parameter\null;
use function Chevere\Parameter\reflectionToParameters;
use function Chevere\Parameter\union;

final class ArgumentsSignatureTest extends TestCase
{
    #[DataProvider('dataProviderRequired')]
    public function testRequired(array $expect): void
    {
        $fn = function (string $foo, string $bar): array {
            return func_get_args();
        };
        $parameters = reflectionToParameters(
            new ReflectionFunction($fn)
        );
        $this->assertSame(
            $expect,
            $parameters(...$expect)->toArray()
        );
    }

    public static function dataProviderRequired(): array
    {
        return [
            [
                [
                    0 => 'super',
                    'bar' => 'taldo',
                ],
            ],
            [
                [
                    'foo' => 'super',
                    'bar' => 'taldo',
                ],
            ],
        ];
    }

    public function testOptional(): void
    {
        $expect = [
            'foo' => 'super',
            'bar' => 'baz',
        ];
        $arguments = ['super'];
        $fn = function (string $foo, string $bar = 'baz'): array {
            return get_defined_vars();
        };
        $parameters = reflectionToParameters(
            new ReflectionFunction($fn)
        );
        $this->assertSame(
            $expect,
            $fn(...$arguments)
        );
        $this->assertSame(
            [
                0 => 'super',
                'bar' => 'baz',
            ],
            $parameters(...$arguments)->toArray()
        );
    }

    #[DataProvider('dataProviderVariadic')]
    public function testVariadic(array $expect): void
    {
        $fn = function (mixed ...$mixed): array {
            return $mixed;
        };
        $parameters = reflectionToParameters(
            new ReflectionFunction($fn)
        );
        $this->assertSame(
            $expect,
            $fn(...$expect)
        );
        $this->assertSame(
            $expect,
            $parameters(...$expect)->toArray()
        );
    }

    public static function dataProviderVariadic(): array
    {
        return [
            [
                [
                    'foo' => 'super',
                    'bar' => 'taldo',
                ],
            ],
            [
                [
                    1,
                    'super',
                    'bar' => 'taldo',
                ],
            ],
            [
                [
                    0 => 'super',
                    1 => 'taldo',
                ],
            ],
        ];
    }

    public function testAllowsNull(): void
    {
        $fn = function (?int $number): ?int {
            return $number;
        };
        $parameters = reflectionToParameters(
            new ReflectionFunction($fn)
        );
        $this->assertEquals(
            $parameters->get('number'),
            union(int(), null())
        );
    }
}
