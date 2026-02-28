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
use Chevere\Parameter\Interfaces\MixedParameterInterface;
use Chevere\Parameter\Interfaces\ObjectParameterInterface;
use Chevere\Parameter\Interfaces\StringParameterInterface;
use Chevere\Parameter\Interfaces\UnionParameterInterface;
use Chevere\Parameter\ReflectionParameterTyped;
use Chevere\Tests\src\Depends;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionObject;
use ReflectionParameter;
use ReflectionProperty;
use stdClass;
use TypeError;
use function Chevere\Parameter\reflectionPropertyToParameter;

final class ReflectionParameterTypedTest extends TestCase
{
    public function testUseNoneGoesMixed(): void
    {
        $parameter = $this->getReflection('useNone');
        $reflection = new ReflectionParameterTyped($parameter);
        $reflected = $reflection->parameter();
        $this->assertInstanceOf(MixedParameterInterface::class, $reflected);
        $this->assertSame('default', $reflected->default());
    }

    public function testParameterObject(): void
    {
        $parameter = $this->getReflection('useObject');
        $reflection = new ReflectionParameterTyped($parameter);
        /** @var ObjectParameterInterface $reflected */
        $reflected = $reflection->parameter();
        $this->assertInstanceOf(ObjectParameterInterface::class, $reflected);
        $this->assertSame(null, $reflected->default());
        $this->assertSame(stdClass::class, $reflected->className());
    }

    public function testParameterDefault(): void
    {
        $parameter = $this->getReflection('useString');
        $reflection = new ReflectionParameterTyped($parameter);
        /** @var StringParameterInterface $reflected */
        $reflected = $reflection->parameter();
        $this->assertInstanceOf(StringParameterInterface::class, $reflected);
        $this->assertSame('/^[a-z]+$/', $reflected->regex()->__toString());
        $this->assertSame('default', $reflected->default());
        $this->assertSame('A string', $reflected->description());
    }

    public function testUnion(): void
    {
        $parameter = $this->getReflection('useUnion');
        $reflection = new ReflectionParameterTyped($parameter);
        $reflected = $reflection->parameter();
        $this->assertInstanceOf(UnionParameterInterface::class, $reflected);
    }

    public function testUnionAttributeError(): void
    {
        $parameter = $this->getReflection('useWrongTUnion');
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Parameter \$union of type int|string is not compatible with Chevere\Parameter\UnionParameter attribute
            PLAIN
        );
        new ReflectionParameterTyped($parameter);
    }

    public function testIntersection(): void
    {
        $parameter = $this->getReflection('useIntersection');
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('$intersection of type intersection is not supported');
        new ReflectionParameterTyped($parameter);
    }

    public function testMixed(): void
    {
        $parameter = $this->getReflection('useMixed');
        $reflection = new ReflectionParameterTyped($parameter);
        $reflected = $reflection->parameter();
        $this->assertInstanceOf(MixedParameterInterface::class, $reflected);
    }

    public function testInvalidAttribute(): void
    {
        $parameter = $this->getReflection('useInvalidAttribute');
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Parameter $int of type int is not compatible with Chevere\Parameter\StringParameter attribute');
        new ReflectionParameterTyped($parameter);
    }

    public function testAttributeInheritsDefault(): void
    {
        $function = function (#[_int(min: 1)] int $param = 100) {};
        $reflection = new ReflectionParameterTyped(new ReflectionParameter($function, 'param'));
        $this->assertSame(100, $reflection->parameter()->default());
    }

    public function testReflectionPropertyToParameter(): void
    {
        $class = new class() {
            public int $id = 100;
        };
        $reflection = new ReflectionProperty($class, 'id');
        $parameter = reflectionPropertyToParameter($reflection);
        $this->assertSame(100, $parameter->default());
    }

    public function testReflectionPropertyToParameterWithAttribute(): void
    {
        $object = new class() {
            #[_int(min: 200)]
            public int $id = 100;
        };
        $reflection = (new ReflectionObject($object))->getProperty('id');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided `100` is less than `200`
            PLAIN
        );
        new ReflectionParameterTyped($reflection);
    }

    public function testPromotedReflectionPropertyToParameter(): void
    {
        $class = new class() {
            public function __construct(
                public int $id = 100
            ) {
            }
        };
        $reflection = new ReflectionProperty($class, 'id');
        $parameter = (new ReflectionParameterTyped($reflection))->parameter();
        $this->assertSame(100, $parameter->default());
    }

    private function getReflection(string $method, int $pos = 0): ReflectionParameter
    {
        $reflection = new ReflectionMethod(Depends::class, $method);

        return $reflection->getParameters()[$pos];
    }
}
