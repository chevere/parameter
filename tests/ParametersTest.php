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

use ArgumentCountError;
use BadMethodCallException;
use Chevere\Parameter\ArrayParameter;
use Chevere\Parameter\BoolParameter;
use Chevere\Parameter\FloatParameter;
use Chevere\Parameter\Interfaces\ParameterInterface;
use Chevere\Parameter\IntParameter;
use Chevere\Parameter\IterableParameter;
use Chevere\Parameter\NullParameter;
use Chevere\Parameter\ObjectParameter;
use Chevere\Parameter\Parameters;
use Chevere\Parameter\StringParameter;
use Chevere\Parameter\UnionParameter;
use Chevere\Tests\src\VariadicParameters;
use InvalidArgumentException;
use OutOfBoundsException;
use OverflowException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use function Chevere\Parameter\arguments;
use function Chevere\Parameter\int;
use function Chevere\Parameter\reflectionToParameters;
use function Chevere\Parameter\string;

final class ParametersTest extends TestCase
{
    public function testConstructEmpty(): void
    {
        $name = 'name';
        $parameters = new Parameters();
        $this->assertCount(0, $parameters);
        $this->assertCount(0, $parameters->optionalKeys());
        $this->assertCount(0, $parameters->requiredKeys());
        $this->assertFalse($parameters->has($name));
        $this->assertFalse($parameters->isVariadic());
        $this->expectException(OutOfBoundsException::class);
        $parameters->get($name);
    }

    public function testAssertEmpty(): void
    {
        $name = 'name';
        $parameters = new Parameters();
        $this->expectException(OutOfBoundsException::class);
        $parameters->assertHas($name);
    }

    public function testConstruct(): void
    {
        $name = 'name';
        $parameter = new StringParameter();
        $parameters = new Parameters(...[
            $name => $parameter,
        ]);
        $this->assertCount(1, $parameters);
        $this->assertCount(0, $parameters->optionalKeys());
        $this->assertCount(1, $parameters->requiredKeys());
        $parameters->assertHas($name);
        $this->assertTrue($parameters->has($name));
        $this->assertTrue($parameters->requiredKeys()->contains($name));
        $this->assertSame($parameter, $parameters->get($name));
        $this->expectException(OverflowException::class);
        $parameters->withRequired(
            $name,
            $parameter,
        );
    }

    public function testConstructPositional(): void
    {
        $foo = string();
        $bar = int();
        $parameters = new Parameters($foo, $bar);
        $this->assertCount(2, $parameters);
        $this->assertTrue($parameters->has('0'));
        $this->assertTrue($parameters->has('1'));
        $this->assertSame($foo, $parameters->get('0'));
        $this->assertSame($bar, $parameters->get('1'));
    }

    public function testRequiredMissing(): void
    {
        $parameters = new Parameters();
        $this->assertFalse($parameters->has('foo'));
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Key `foo` not found');
        $parameters->required('foo');
    }

    public function testOptionalMissing(): void
    {
        $parameters = new Parameters();
        $this->assertFalse($parameters->has('foo'));
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Key `foo` not found');
        $parameters->optional('foo');
    }

    public function testRequiredMissingNoOptional(): void
    {
        $parameters = (new Parameters(foo: string()))
            ->withOptional('bar', string());
        $this->expectException(ArgumentCountError::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Missing required argument(s): `foo`
            PLAIN
        );
        $parameters();
    }

    public function testRequiredTyped(): void
    {
        $parameter = string();
        $parameters = new Parameters(foo: $parameter);
        $this->assertTrue($parameters->has('foo'));
        $this->assertSame($parameter, $parameters->required('foo')->string());
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter `foo` is required');
        $parameters->optional('foo');
    }

    public function testRequiredTypedPositional(): void
    {
        $parameter = string();
        $parameters = new Parameters($parameter);
        $this->assertTrue($parameters->has('0'));
        $this->assertSame($parameter, $parameters->required('0')->string());
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter `0` is required');
        $parameters->optional('0');
    }

    public function testOptionalTyped(): void
    {
        $parameter = string();
        $parameters = (new Parameters())
            ->withOptional('foo', $parameter);
        $this->assertSame($parameter, $parameters->optional('foo')->string());
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter `foo` is optional');
        $parameters->required('foo');
    }

    public function testWithRequiredOverflow(): void
    {
        $name = 'name';
        $parameter = new StringParameter();
        $parameters = new Parameters(
            ...[
                $name => $parameter,
            ]
        );
        $this->assertCount(1, $parameters);
        $this->assertCount(0, $parameters->optionalKeys());
        $this->assertCount(1, $parameters->requiredKeys());
        $parameters->assertHas($name);
        $this->assertTrue($parameters->has($name));
        $this->assertTrue($parameters->requiredKeys()->contains($name));
        $this->assertSame($parameter, $parameters->get($name));
        $parametersWith = $parameters->withRequired('test', $parameter);
        $this->assertNotSame($parameters, $parametersWith);
        $this->expectException(OverflowException::class);
        $parameters->withRequired(
            $name,
            $parameter,
        );
    }

    public function testWithout(): void
    {
        $parameters = (new Parameters())
            ->withRequired('a', string())
            ->withRequired('b', string())
            ->withRequired('c', string())
            ->withOptional('x', string())
            ->withOptional('y', string())
            ->withOptional('z', string());
        $index = $parameters->keys();
        $requiredKeys = $parameters->requiredKeys();
        $optionalKeys = $parameters->optionalKeys();
        $without = $parameters->without();
        $this->assertNotSame($parameters, $without);
        $this->assertSame($index, $without->keys());
        $this->assertSame($requiredKeys, $without->requiredKeys());
        $this->assertSame($optionalKeys, $without->optionalKeys());
        $parametersWith = $parameters->without('a', 'y');
        $this->assertNotSame($parameters, $parametersWith);
        $this->assertCount(4, $parametersWith);
        $this->assertSame(['b', 'c'], $parametersWith->requiredKeys()->toArray());
        $this->assertSame(['x', 'z'], $parametersWith->optionalKeys()->toArray());
        $this->assertSame(['b', 'c', 'x', 'z'], $parametersWith->keys());
    }

    public function testWithRequiredOptional(): void
    {
        $name = 'name';
        $parameter = new StringParameter();
        $parameters = new Parameters();
        $this->assertSame(false, $parameters->isVariadic());
        $parametersWith = $parameters->withOptional($name, $parameter);
        $this->assertNotSame($parameters, $parametersWith);
        $this->assertCount(1, $parametersWith);
        $this->assertCount(1, $parametersWith->optionalKeys());
        $this->assertCount(0, $parametersWith->requiredKeys());
        $this->assertTrue($parametersWith->has($name));
        $this->assertTrue($parametersWith->optionalKeys()->contains($name));
        $this->assertFalse($parametersWith->requiredKeys()->contains($name));
        $this->assertSame($parameter, $parametersWith->get($name));
        $this->expectException(OverflowException::class);
        $parametersWith->withOptional($name, $parameter);
    }

    public function testWithVariadicParameters(): void
    {
        $reflector = new ReflectionMethod(VariadicParameters::class, 'main');
        $parameters = reflectionToParameters($reflector);
        $this->assertTrue($parameters->isVariadic());
        $return = $parameters(
            _task: 'test',
            _priority: 2,
            _maxRetries: 3,
            foo: 'bar',
            bar: 'baz',
        );
        $this->assertSame(
            [
                '_task' => 'test',
                '_priority' => 2,
                '_maxRetries' => 3,
                'foo' => 'bar',
                'bar' => 'baz',
            ],
            $return->toArray()
        );
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            [foo...argument]: Argument must be of type Stringable|string, true given
            PLAIN
        );
        $this->expectExceptionMessage(
            <<<PLAIN
            [bar...argument]: Argument must be of type Stringable|string, int given
            PLAIN
        );
        $parameters(
            _task: 'test',
            _priority: 0,
            _maxRetries: 0,
            foo: true,
            bar: 123,
        );
    }

    public static function dataProviderGetTyped(): array
    {
        return [
            [new StringParameter(), 'string'],
            [new IntParameter(), 'int'],
            [new FloatParameter(), 'float'],
            [new BoolParameter(), 'bool'],
            [new ArrayParameter(), 'array'],
            [new ObjectParameter(), 'object'],
            [new NullParameter(), 'null', 'int'],
        ];
    }

    #[DataProvider('dataProviderGetTyped')]
    public function testGetTyped(
        ParameterInterface $parameter,
        string $type,
        string $error = 'null'
    ): void {
        $name = 'test';
        $parameters = new Parameters(...[
            $name => $parameter,
        ]);
        $this->assertSame(
            $parameter,
            $parameters->required($name)->{$type}()
        );
        $this->expectException(\TypeError::class);
        $parameters->required($name)->{$error}();
    }

    public function testGetUnion(): void
    {
        $name = 'test';
        $type1 = new StringParameter();
        $type2 = new IntParameter();
        $parameters = new Parameters($type1, $type2);
        $parameter = new UnionParameter($parameters);
        $parameters = new Parameters(...[
            $name => $parameter,
        ]);
        $this->assertSame(
            $parameter,
            $parameters->required($name)->union()
        );
        $this->expectException(\TypeError::class);
        $parameters->required($name)->null();
    }

    public function testGetIterable(): void
    {
        $name = 'test';
        $parameter = new IterableParameter(
            value: string(),
            key: int(),
        );
        $parameters = new Parameters(...[
            $name => $parameter,
        ]);
        $this->assertSame(
            $parameter,
            $parameters->required($name)->iterable()
        );
        $this->expectException(\TypeError::class);
        $parameters->required($name)->null();
    }

    public function testWithOptionalMinimum(): void
    {
        $parameters = (new Parameters())->withOptional('a', string());
        $parametersWith = $parameters->withOptionalMinimum(1);
        $this->assertNotSame($parameters, $parametersWith);
        $this->assertSame(1, $parametersWith->optionalMinimum());
    }

    public function testWithOptionalMinimumBadMethodCall(): void
    {
        $parameters = new Parameters();
        $this->expectException(BadMethodCallException::class);
        $parameters->withOptionalMinimum(1);
    }

    public function testWithOptionalMinimumInvalidArgument(): void
    {
        $parameters = (new Parameters())->withOptional('foo', string());
        $this->expectException(InvalidArgumentException::class);
        $parameters->withOptionalMinimum(2);
    }

    public function testWithOptionalMinimumInvalidArgumentNumber(): void
    {
        $parameters = (new Parameters())->withOptional('foo', string());
        $this->expectException(InvalidArgumentException::class);
        $parameters->withOptionalMinimum(-1);
    }

    public function testWithOptionalMinimumWithout(): void
    {
        $parameters = (new Parameters())
            ->withOptional('foo', string())
            ->withOptional('bar', string());
        $parametersWith = $parameters->withOptionalMinimum(1);
        $parametersWith = $parametersWith->without('foo');
        $parametersWith = $parametersWith->withOptionalMinimum(0);
        $this->expectNotToPerformAssertions();
        $parametersWith->without('bar');
    }

    public function testWithOptionalMinimumWithoutInvalidArgument(): void
    {
        $parameters = (new Parameters())->withOptional('foo', string());
        $parametersWith = $parameters->withOptionalMinimum(1);
        $this->expectException(InvalidArgumentException::class);
        $parametersWith->without('foo');
    }

    public function testWithMakeOptional(): void
    {
        $parameters = new Parameters(
            foo: string(),
            bar: int()
        );
        $with = $parameters->withMakeOptional('foo');
        $this->assertNotSame($parameters, $with);
        $this->assertCount(2, $with);
        $this->assertSame(['foo'], $with->optionalKeys()->toArray());
        $this->assertSame(['bar'], $with->requiredKeys()->toArray());
        $this->assertSame(['foo', 'bar'], $with->keys());
        $this->assertSame(['foo', 'bar'], array_keys(iterator_to_array($with)));
        $this->expectException(InvalidArgumentException::class);
        $with->withMakeOptional('foo');
    }

    public function testWithMakeRequired(): void
    {
        $parameters = (new Parameters())
            ->withOptional('foo', string())
            ->withOptional('bar', int());
        $with = $parameters->withMakeRequired('bar');
        $this->assertNotSame($parameters, $with);
        $this->assertCount(2, $with);
        $this->assertSame(['foo'], $with->optionalKeys()->toArray());
        $this->assertSame(['bar'], $with->requiredKeys()->toArray());
        $this->assertSame(['foo', 'bar'], $with->keys());
        $this->assertSame(['foo', 'bar'], array_keys(iterator_to_array($with)));
        $this->expectException(InvalidArgumentException::class);
        $with->withMakeRequired('bar');
    }

    public function testWithMerge(): void
    {
        $foo = string();
        $bar = int();
        $parametersFoo = new Parameters(foo: $foo);
        $parametersBar = (new Parameters())->withOptional('bar', $bar);
        $parameters = (new Parameters(foo: $foo))->withOptional('bar', $bar);
        $fooWithMerge = $parametersFoo->withMerge($parametersBar);
        $this->assertNotSame($parametersFoo, $fooWithMerge);
        $this->assertEquals($parameters, $fooWithMerge);
        $this->assertSame(['foo'], $fooWithMerge->requiredKeys()->toArray());
        $this->assertSame(['bar'], $fooWithMerge->optionalKeys()->toArray());
        $barWithMerge = $parametersBar->withMerge($parametersFoo);
        $this->assertSame(['foo'], $barWithMerge->requiredKeys()->toArray());
        $this->assertSame(['bar'], $barWithMerge->optionalKeys()->toArray());
    }

    public function testWithIsVariadic(): void
    {
        $parameters = new Parameters();
        $with = $parameters->withIsVariadic();
        $this->assertNotEquals($with, $parameters);
        $this->assertTrue($with->isVariadic());
    }

    public function testReadme(): void
    {
        $parameters = (new Parameters(
            id: int(min: 1),
            name: string('/^[A-Z]{1}\w+$/'),
        ))
            ->withOptional(
                'email',
                string(),
            );
        $data = [
            'id' => 1,
            'name' => 'Pepe',
        ];
        $arguments = arguments($parameters, $data);
        $this->assertTrue($arguments->has('id'));
        $this->assertFalse($arguments->has('poto'));
        $this->assertSame(1, $arguments->get('id'));
        $this->assertSame(1, $arguments->required('id')->int());
        $this->assertNull($arguments->optional('email')?->string());
    }

    public function testIterable(): void
    {
        $parameters = new Parameters(
            K: int(),
            V: string(),
        );
        $this->assertTrue($parameters->isIterable());
    }
}
