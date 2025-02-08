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
use function Chevere\Parameter\reflectionToParameters;

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
            'bar' => '',
        ];
        $arguments = ['super'];
        $fn = function (string $foo, string $bar = ''): array {
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
            $expect,
            $parameters(...$arguments)->toArray()
        );
    }

    public function testVariadicNoNames(): void
    {
        $expect = [
            0 => 'super',
            1 => 'taldo',
        ];
        $arguments = [
            'super',
            'taldo',
        ];
        $fn = function (mixed ...$mixed): array {
            return $mixed;
        };
        $parameters = reflectionToParameters(
            new ReflectionFunction($fn)
        );
        $this->assertSame(
            $expect,
            $fn(...$arguments)
        );
        $this->assertSame(
            $expect,
            $parameters(...$arguments)->toArray()
        );
    }

    public function testVariadicSomeNames(): void
    {
        $expect = [
            0 => 1,
            1 => 'super',
            'bar' => 'taldo',
        ];
        $arguments = [
            1,
            'super',
            'bar' => 'taldo',
        ];
        $fn = function (mixed ...$mixed): array {
            return $mixed;
        };
        $parameters = reflectionToParameters(
            new ReflectionFunction($fn)
        );
        $this->assertSame(
            $expect,
            $fn(...$arguments)
        );
        $this->assertSame(
            $expect,
            $parameters(...$arguments)->toArray()
        );
    }

    public function testVariadicNames(): void
    {
        $expect = [
            'foo' => 'super',
            'bar' => 'taldo',
        ];
        $arguments = [
            'foo' => 'super',
            'bar' => 'taldo',
        ];
        $fn = function (mixed ...$mixed): array {
            return $mixed;
        };
        $parameters = reflectionToParameters(
            new ReflectionFunction($fn)
        );
        $this->assertSame(
            $expect,
            $fn(...$arguments)
        );
        $this->assertSame(
            $expect,
            $parameters(...$arguments)->toArray()
        );
    }
}
