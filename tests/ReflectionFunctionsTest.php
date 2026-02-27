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

use Chevere\Parameter\Attributes\_int;
use Chevere\Parameter\Attributes\_return;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;
use ReflectionMethod;
use function Chevere\Parameter\arguments;
use function Chevere\Parameter\bool;
use function Chevere\Parameter\reflectionToParameters;
use function Chevere\Parameter\reflectionToReturn;

final class ReflectionFunctionsTest extends TestCase
{
    public function testAnonClassReturn(): void
    {
        $class = new class() {
            #[_return(
                new _int(min: 100)
            )]
            public function wea(int $base): int
            {
                return 10 * $base;
            }
        };
        $reflection = new ReflectionMethod($class, 'wea');
        $return = reflectionToReturn($reflection);
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
                #[_int(accept: [1, 10, 100])]
                int $base = 1
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
            #[_return(
                new _int(min: 1000)
            )]
            function (int $base): int {
                return 10 * $base;
            };
        $reflection = new ReflectionFunction($function);
        $return = reflectionToReturn($reflection);
        $function(10);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument value provided `100` is less than `1000`');
        $return($function(10));
    }

    public static function dataProviderFunctionToReturnUses(): array
    {
        return [
            ['Chevere\Tests\src\usesAttr'],
            ['Chevere\Tests\src\noUsesAttr'],
        ];
    }

    #[DataProvider('dataProviderFunctionToReturnUses')]
    public function testFunctionToReturnUses(string $function): void
    {
        $this->expectNotToPerformAssertions();
        $reflection = new ReflectionFunction($function);
        $return = reflectionToReturn($reflection);
        $return->assertCompatible(bool());
    }

    public function testWithDefaultError(): void
    {
        $function = function (
            #[_int(min: 2)]
            int $int = 1
        ): void {
        };
        $reflection = new ReflectionFunction($function);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided `1` is less than `2`
            PLAIN
        );
        reflectionToParameters($reflection);
    }
}
