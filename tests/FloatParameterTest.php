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

use Chevere\Parameter\FloatParameter;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TypeError;
use function Chevere\Parameter\float;

final class FloatParameterTest extends TestCase
{
    public function testConstruct(): void
    {
        $parameter = new FloatParameter();
        $parameter(1.0);
        $this->assertEquals($parameter, float());
        $this->assertSame(null, $parameter->default());
        $this->assertSame(null, $parameter->min());
        $this->assertSame(null, $parameter->max());
    }

    public function testWithDefault(): void
    {
        $parameter = new FloatParameter();
        $default = 12.34;
        $parameterWithDefault = $parameter->withDefault($default);
        (new ParameterHelper())->testWithParameterDefault(
            primitive: 'float',
            parameter: $parameter,
            default: $default,
            parameterWithDefault: $parameterWithDefault
        );
        $this->assertSame([
            'type' => 'float',
            'description' => '',
            'default' => $default,
            'min' => null,
            'max' => null,
            'accept' => [],
            'reject' => [],
        ], $parameterWithDefault->schema());
    }

    public function testWithAccept(): void
    {
        $values = [1.1, 2.2, 3.3];
        $parameter = new FloatParameter();
        $with = $parameter->withAccept(...$values);
        $this->assertNotSame($parameter, $with);
        $this->assertSame($values, $with->accept());
        foreach ($values as $value) {
            $with($value);
        }
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<STRING
            Argument value provided `4.4` is not an accepted value in `[1.1,2.2,3.3]`
            STRING
        );
        $with(4.4);
    }

    public function testWithAcceptWithDefault(): void
    {
        $values = [1.1, 2.2, 3.3];
        $parameter = new FloatParameter();
        $with = $parameter->withAccept(...$values);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<STRING
            Argument value provided `4.4` is not an accepted value in `[1.1,2.2,3.3]`
            STRING
        );
        $with->withDefault(4.4);
    }

    public function testWithReject(): void
    {
        $values = [1.1, 2.2, 3.3];
        $parameter = new FloatParameter();
        $with = $parameter->withReject(...$values);
        $this->assertNotSame($parameter, $with);
        $this->assertSame($values, $with->reject());
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<STRING
            Argument value provided `2.2` is on rejected list `[1.1,2.2,3.3]`
            STRING
        );
        $with(2.2);
    }

    public function testWithMin(): void
    {
        $parameter = new FloatParameter();
        $value = 1.0;
        $with = $parameter->withMin($value);
        $this->assertNotSame($parameter, $with);
        $this->assertSame($value, $with->min());
        $with(1.0);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<STRING
            Argument value provided `0.9` is less than `1`
            STRING
        );
        $with(0.9);
    }

    public function testWithMax(): void
    {
        $parameter = new FloatParameter();
        $value = 1.0;
        $with = $parameter->withMax($value);
        $this->assertNotSame($parameter, $with);
        $this->assertSame($value, $with->max());
        $with(1.0);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<STRING
            Argument value provided `1.1` is greater than `1`
            STRING
        );
        $with(1.1);
    }

    public function testAssertCompatible(): void
    {
        $parameter = (new FloatParameter())->withDefault(12.34);
        $compatible = new FloatParameter();
        $this->expectNotToPerformAssertions();
        $parameter->assertCompatible($compatible);
        $compatible->assertCompatible($parameter);
    }

    public function testAssertNotCompatible(): void
    {
        $value = 12.34;
        $provided = 56.78;
        $parameter = (new FloatParameter())->withAccept($value);
        $notCompatible = (new FloatParameter())->withAccept($provided);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<STRING
            Expected accept values in `[{$value}]`, provided `[{$provided}]`
            STRING
        );
        $parameter->assertCompatible($notCompatible);
    }

    public function testInvoke(): void
    {
        $value = 10.0;
        $parameter = new FloatParameter();
        $parameter(10.0);
        $this->expectException(TypeError::class);
        $parameter('value');
    }
}
