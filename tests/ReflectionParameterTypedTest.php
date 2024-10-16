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

use Chevere\Parameter\Interfaces\MixedParameterInterface;
use Chevere\Parameter\Interfaces\NullParameterInterface;
use Chevere\Parameter\Interfaces\ObjectParameterInterface;
use Chevere\Parameter\Interfaces\StringParameterInterface;
use Chevere\Parameter\ReflectionParameterTyped;
use Chevere\Tests\src\Depends;
use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionParameter;
use stdClass;
use TypeError;

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

    // public function testUseNull(): void
    // {
    //     $parameter = $this->getReflection('useNull');
    //     $reflection = new ReflectionParameterTyped($parameter);
    //     $reflected = $reflection->parameter();
    //     $this->assertInstanceOf(NullParameterInterface::class, $reflected);
    //     $this->assertSame(null, $reflected->default());
    // }

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
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('$union of type union is not supported');
        new ReflectionParameterTyped($parameter);
    }

    public function testIntersection(): void
    {
        $parameter = $this->getReflection('useIntersection');
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('$intersection of type intersection is not supported');
        new ReflectionParameterTyped($parameter);
    }

    public function testInvalidAttribute(): void
    {
        $parameter = $this->getReflection('useInvalidAttribute');
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Parameter int of type int is not compatible with Chevere\Parameter\StringParameter attribute');
        new ReflectionParameterTyped($parameter);
    }

    private function getReflection(string $method, int $pos = 0): ReflectionParameter
    {
        $reflection = new ReflectionMethod(Depends::class, $method);

        return $reflection->getParameters()[$pos];
    }
}
