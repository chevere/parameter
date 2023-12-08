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

use Chevere\Parameter\Attributes\IntAttr;
use Chevere\Parameter\Attributes\ReturnAttr;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;
use ReflectionMethod;
use function Chevere\Parameter\arguments;
use function Chevere\Parameter\reflectionToParameters;
use function Chevere\Parameter\reflectionToReturnParameter;
use function Chevere\Tests\src\myArray;

final class TypeFunctionsTest extends TestCase
{
    public function testBodyValidate(): void
    {
        $value = [
            'id' => 1,
            'role' => [
                'mask' => 64,
                'name' => 'admin',
                'tenants' => [1, 2, 3, 4, 5],
            ],
        ];
        myArray($value);
        $value = [
            'id' => 1,
            'role' => [
                'mask' => 64,
                'name' => 'admin',
                'tenants' => [6, 7, 8],
            ],
        ];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('[role]: [tenants]: [_V *generic]: Argument value provided `6` is greater than `5`');
        myArray($value);
    }

    public function testAnonClassReturn(): void
    {
        $class = new class() {
            #[ReturnAttr(
                new IntAttr(min: 100)
            )]
            public function wea(int $base): int
            {
                return 10 * $base;
            }
        };
        $reflection = new ReflectionMethod($class, 'wea');
        $return = reflectionToReturnParameter($reflection);
        $object = new $class();
        $object->wea(1);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument value provided `10` is less than `100`');
        $return($object->wea(1));
    }

    public function testAnonClassParameters(): void
    {
        $class = new class() {
            public function wea(
                #[IntAttr(accept: [1, 10, 100])]
                int $base
            ): void {
            }
        };
        $reflection = new ReflectionMethod($class, 'wea');
        $parameters = reflectionToParameters($reflection);
        $object = new $class();
        $object->wea(1);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('[base]: Argument value provided `0` is not an accepted value in `[1,10,100]`');
        arguments($parameters, [
            'base' => 0,
        ]);
    }

    public function testAnonFunctionReturn(): void
    {
        $function =
            #[ReturnAttr(
                new IntAttr(min: 1000)
            )]
            function (int $base): int {
                return 10 * $base;
            };
        $reflection = new ReflectionFunction($function);
        $return = reflectionToReturnParameter($reflection);
        $function(10);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument value provided `100` is less than `1000`');
        $return($function(10));
    }
}
