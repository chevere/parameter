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

use Chevere\Parameter\Arguments;
use Chevere\Parameter\IntParameter;
use Chevere\Parameter\Parameters;
use InvalidArgumentException;
use OverflowException;
use PHPUnit\Framework\TestCase;

final class IntParameterTest extends TestCase
{
    public function testConstruct(): void
    {
        $parameter = new IntParameter();
        $this->assertSame(null, $parameter->default());
        $this->assertSame(null, $parameter->min());
        $this->assertSame(null, $parameter->max());
        $this->assertSame([], $parameter->accept());
        $this->assertSame([], $parameter->reject());
        $default = 1234;
        $parameterWithDefault = $parameter->withDefault($default);
        (new ParameterHelper())->testWithParameterDefault(
            primitive: 'int',
            parameter: $parameter,
            default: $default,
            parameterWithDefault: $parameterWithDefault
        );
        $this->assertSame([
            'type' => 'int',
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
        $accept = [3, 2, 1];
        $sorted = [1, 2, 3];
        $parameter = new IntParameter();
        $with = $parameter->withAccept(...$accept);
        $this->assertNotSame($parameter, $with);
        $this->assertSame(null, $with->max());
        $this->assertSame(null, $with->min());
        $this->assertSame($sorted, $with->accept());
        $this->assertSame([
            'type' => 'int',
            'description' => '',
            'default' => null,
            'min' => null,
            'max' => null,
            'accept' => $sorted,
            'reject' => [],
        ], $with->schema());
    }

    public function testWithReject(): void
    {
        $reject = [3, 2, 1];
        $sorted = [1, 2, 3];
        $parameter = new IntParameter();
        $with = $parameter->withReject(...$reject);
        $this->assertNotSame($parameter, $with);
        $this->assertSame($sorted, $with->reject());
        $this->assertSame(null, $with->max());
        $this->assertSame(null, $with->min());
        $this->assertSame([
            'type' => 'int',
            'description' => '',
            'default' => null,
            'min' => null,
            'max' => null,
            'accept' => [],
            'reject' => $sorted,
        ], $with->schema());
    }

    public function testWithAcceptRejectConflict(): void
    {
        $conflict = [1, 2, 3];
        $parameter = new IntParameter();
        $with = $parameter->withAccept(...$conflict);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Accept list `[1, 2, 3]` intersects with reject list `[1, 2, 3]`');
        $with->withReject(...$conflict);
    }

    public function testWithRejectAcceptConflict(): void
    {
        $conflict = [1, 2, 3];
        $parameter = new IntParameter();
        $with = $parameter->withReject(...$conflict);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Accept list `[1, 2, 3]` intersects with reject list `[1, 2, 3]`');
        $with->withAccept(...$conflict);
    }

    public function testWithAcceptOnArguments(): void
    {
        $accept = [1, 2, 3];
        $expect = [
            'test' => 1,
        ];
        $parameter = (new IntParameter())->withAccept(...$accept);
        $parameters = new Parameters(test: $parameter);
        $arguments = new Arguments($parameters, $expect);
        $this->assertSame($expect, $arguments->toArray());
        $this->expectException(InvalidArgumentException::class);
        new Arguments($parameters, [
            'test' => 0,
        ]);
    }

    public function testWithRejectOnArguments(): void
    {
        $reject = [1, 2, 3];
        $expect = [
            'test' => 0,
        ];
        $parameter = (new IntParameter())->withReject(...$reject);
        $parameters = new Parameters(test: $parameter);
        $arguments = new Arguments($parameters, $expect);
        $this->assertSame($expect, $arguments->toArray());
        $this->expectException(InvalidArgumentException::class);
        new Arguments($parameters, [
            'test' => 1,
        ]);
    }

    public function testWithMinAccept(): void
    {
        $parameter = new IntParameter();
        $with = $parameter->withMin(1);
        $this->assertNotSame($parameter, $with);
        $this->assertSame(1, $with->min());
        $with = $parameter->withAccept(3, 2, 1);
        $this->expectException(OverflowException::class);
        $with->withMin(0);
    }

    public function testWithMinReject(): void
    {
        $parameter = new IntParameter();
        $with = $parameter->withMin(1);
        $this->assertNotSame($parameter, $with);
        $this->assertSame(1, $with->min());
        $with = $parameter->withReject(3, 2, 1);
        $this->expectException(OverflowException::class);
        $with->withMin(0);
    }

    public function testWithMinOnArguments(): void
    {
        $expect = [
            'test' => 1,
        ];
        $parameter = (new IntParameter())->withMin(1);
        $parameters = new Parameters(test: $parameter);
        $arguments = new Arguments($parameters, $expect);
        $this->assertSame($expect, $arguments->toArray());
        $this->expectException(InvalidArgumentException::class);
        new Arguments($parameters, [
            'test' => -1,
        ]);
    }

    public function testWithMax(): void
    {
        $parameter = new IntParameter();
        $withmax = $parameter->withMax(1);
        $this->assertNotSame($parameter, $withmax);
        $this->assertSame(1, $withmax->max());
        $withValue = $parameter->withAccept(1, 2, 3);
        $this->expectException(OverflowException::class);
        $withValue->withMax(0);
    }

    public function testWithMaxOnArguments(): void
    {
        $expect = [
            'test' => 1,
        ];
        $parameter = (new IntParameter())->withMax(1);
        $parameters = new Parameters(test: $parameter);
        $arguments = new Arguments($parameters, $expect);
        $this->assertSame($expect, $arguments->toArray());
        $this->expectException(InvalidArgumentException::class);
        new Arguments($parameters, [
            'test' => 2,
        ]);
    }

    public function testWithMaxAccept(): void
    {
        $parameter = new IntParameter();
        $with = $parameter->withMax(1);
        $this->assertNotSame($parameter, $with);
        $this->assertSame(1, $with->max());
        $with = $parameter->withReject(3, 2, 1);
        $this->expectException(OverflowException::class);
        $with->withMax(0);
    }

    public function testWithMaxReject(): void
    {
        $parameter = new IntParameter();
        $with = $parameter->withMax(1);
        $this->assertNotSame($parameter, $with);
        $this->assertSame(1, $with->max());
        $with = $parameter->withReject(3, 2, 1);
        $this->expectException(OverflowException::class);
        $with->withMax(0);
    }

    public function testWithMinMax(): void
    {
        $parameter = new IntParameter();
        $with = $parameter
            ->withMin(1)
            ->withMax(2);
        $this->expectException(InvalidArgumentException::class);
        $with->withMin(2);
    }

    public function testWithMaxMin(): void
    {
        $parameter = new IntParameter();
        $with = $parameter
            ->withMin(1)
            ->withMax(2);
        $this->expectException(InvalidArgumentException::class);
        $with->withMax(1);
    }

    public function testAssertCompatibleMin(): void
    {
        $value = 1;
        $parameter = (new IntParameter())->withMin($value);
        $compatible = (new IntParameter())->withMin($value);
        $parameter->assertCompatible($compatible);
        $compatible->assertCompatible($parameter);
        $provided = $value * 2;
        $notCompatible = (new IntParameter())->withMin($provided);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<STRING
            Expected min value `{$value}`, provided `{$provided}`
            STRING
        );
        $parameter->assertCompatible($notCompatible);
    }

    public function testAssertCompatibleMax(): void
    {
        $value = 1;
        $compatible = (new IntParameter())->withMax($value);
        $parameter = (new IntParameter())->withMax($value);
        $parameter->assertCompatible($compatible);
        $compatible->assertCompatible($parameter);
        $provided = $value * 2;
        $notCompatible = (new IntParameter())->withMax($provided);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<STRING
            Expected max value `{$value}`, provided `{$provided}`
            STRING
        );
        $parameter->assertCompatible($notCompatible);
    }

    public function testAssertCompatibleAccept(): void
    {
        $parameter = (new IntParameter())->withAccept(1, 2, 3, 4);
        $compatible = (new IntParameter())->withAccept(4, 3, 2, 1);
        $parameter->assertCompatible($compatible);
        $compatible->assertCompatible($parameter);
        $notCompatible = (new IntParameter())->withAccept(1, 4);
        $expected = implode(', ', $parameter->accept());
        $this->expectException(InvalidArgumentException::class);
        $this->getExpectedExceptionMessage(<<<PLAIN
        Expected accept values in `[{$expected}]`, provided `[]`
        PLAIN);
        $parameter->assertCompatible($notCompatible);
    }

    public function testAssertCompatibleReject(): void
    {
        $parameter = (new IntParameter())->withReject(1, 2, 3, 4);
        $compatible = (new IntParameter())->withReject(4, 3, 2, 1);
        $parameter->assertCompatible($compatible);
        $compatible->assertCompatible($parameter);
        $notCompatible = (new IntParameter())->withReject(1, 4);
        $expected = implode(', ', $parameter->reject());
        $this->expectException(InvalidArgumentException::class);
        $this->getExpectedExceptionMessage(<<<PLAIN
        Expected reject values in `[{$expected}]`, provided `[]`
        PLAIN);
        $parameter->assertCompatible($notCompatible);
    }

    public function testAssertCompatibleAcceptMin(): void
    {
        $parameter = (new IntParameter())->withAccept(1, 2, 3, 4);
        $notCompatible = (new IntParameter())->withMin(0);
        $this->expectException(InvalidArgumentException::class);
        $this->getExpectedExceptionMessage('value null');
        $parameter->assertCompatible($notCompatible);
    }

    public function testWithDefaultConflictMin(): void
    {
        $parameter = (new IntParameter())
            ->withMin(1)
            ->withDefault(1);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<STRING
            Default value `0` can't be less than min value `1`
            STRING
        );
        $parameter->withDefault(0);
    }

    public function testWithDefaultConflictMax(): void
    {
        $parameter = (new IntParameter())
            ->withMax(1)
            ->withDefault(1);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<STRING
            Default value `2` can't be greater than max value `1`
            STRING
        );
        $parameter->withDefault(2);
    }

    public function testWithDefaultConflictAccept(): void
    {
        $parameter = (new IntParameter())->withAccept(1, 2, 3);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<STRING
            Default value `5` must be in accept list `[1, 2, 3]`
            STRING
        );
        $parameter->withDefault(5);
    }

    public function testWithDefaultConflictReject(): void
    {
        $parameter = (new IntParameter())->withReject(1, 2, 3);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<STRING
            Default value `1` must not be in reject list `[1, 2, 3]`
            STRING
        );
        $parameter->withDefault(1);
    }

    public function testInvoke(): void
    {
        $value = 10;
        $parameter = new IntParameter();
        $this->assertSame($value, $parameter($value));
    }
}
