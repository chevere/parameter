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

use BadMethodCallException;
use Chevere\Parameter\Exceptions\ParameterException;
use Chevere\Parameter\Exceptions\ReturnException;
use Chevere\Parameter\Interfaces\ParametersAccessInterface;
use Chevere\Parameter\Interfaces\ParametersInterface;
use InvalidArgumentException;
use LogicException;
use OutOfBoundsException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use TypeError;
use function Chevere\Parameter\arguments;
use function Chevere\Parameter\arrayFrom;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\assertArray;
use function Chevere\Parameter\assertNamedArgument;
use function Chevere\Parameter\bool;
use function Chevere\Parameter\castArguments;
use function Chevere\Parameter\float;
use function Chevere\Parameter\getType;
use function Chevere\Parameter\int;
use function Chevere\Parameter\iterable;
use function Chevere\Parameter\mixed;
use function Chevere\Parameter\null;
use function Chevere\Parameter\object;
use function Chevere\Parameter\parameterAttribute;
use function Chevere\Parameter\parameters;
use function Chevere\Parameter\parametersFrom;
use function Chevere\Parameter\string;
use function Chevere\Parameter\takeFrom;
use function Chevere\Parameter\takeKeys;
use function Chevere\Parameter\typed;
use function Chevere\Parameter\validated;
use function Chevere\Parameter\valMd;

final class FunctionsTest extends TestCase
{
    public function testParameters(): void
    {
        $parameters = parameters();
        $this->assertCount(0, $parameters);
        $parameters = parameters(
            foo: string()
        );
        $this->assertCount(1, $parameters);
        $this->assertTrue($parameters->requiredKeys()->contains('foo'));
    }

    public function testArguments(): void
    {
        $parameters = parameters(
            foo: string()
        );
        $args = [
            'foo' => 'bar',
        ];
        $arguments = arguments($parameters, $args);
        $this->assertSame($args, $arguments->toArray());
    }

    public function testArrayParameter(): void
    {
        $parameter = arrayp();
        $this->assertSame('', $parameter->description());
        $this->assertSame(null, $parameter->default());
        $parameter([]);
    }

    public function testBoolParameter(): void
    {
        $parameter = bool();
        $parameter(true);
        $this->assertSame('', $parameter->description());
        $this->assertSame(null, $parameter->default());
        $parameter = bool(
            description: 'name',
            default: true
        );
        $this->assertSame('name', $parameter->description());
        $this->assertSame(true, $parameter->default());
        $this->expectException(TypeError::class);
        $parameter(null);
    }

    public function testNullParameter(): void
    {
        $parameter = null();
        $parameter(null);
        $this->assertSame('', $parameter->description());
        $this->assertSame(null, $parameter->default());
        $this->expectException(TypeError::class);
        $parameter(1);
    }

    public function testIntParameter(): void
    {
        $parameter = int();
        $parameter(1);
        $this->assertSame('', $parameter->description());
        $this->assertSame(null, $parameter->default());
        $parameter = int(
            default: 10
        );
        $this->assertSame(10, $parameter->default());
        $this->expectException(TypeError::class);
        $parameter('');
    }

    public function testFunctionObjectParameter(): void
    {
        $parameter = object(stdClass::class);
        $parameter(new stdClass());
        $this->assertSame('', $parameter->description());
        $this->assertSame(stdClass::class, $parameter->className());
        $parameter = object(stdClass::class, 'foo');
        $this->assertSame('foo', $parameter->description());
        $this->expectException(TypeError::class);
        $parameter(parameters());
    }

    public function testFunctionStringParameter(): void
    {
        $description = 'some description';
        $default = 'abcd';
        $regex = '/^[a-z]+$/';
        $parameter = string(
            description: $description,
            default: $default,
            regex: $regex,
        );
        $parameter($default);
        $this->assertSame($description, $parameter->description());
        $this->assertSame($default, $parameter->default());
        $this->assertSame($regex, $parameter->regex()->__toString());
        $this->expectException(TypeError::class);
        $parameter(123);
    }

    public function testFunctionArrayParameter(): void
    {
        $parameter = arrayp(
            one: string(),
            two: int(default: 222)
        );
        $array = [
            'one' => 'foo',
        ];
        $expected = array_merge($array, [
            'two' => 222,
        ]);
        $assert = assertArray($parameter, $array);
        $this->assertSame($assert, $expected);
        $this->assertCount(2, $parameter->parameters());
        $this->expectException(TypeError::class);
        $parameter(1);
    }

    public function testFunctionArrayParameterNested(): void
    {
        $parameter = arrayp(
            one: string(),
            two: int(default: 222),
            nestTwice: arrayp(
                first: arrayp(
                    twice: int(default: 1)
                )
            ),
            nest: arrayp(
                nestOne: int(default: 1),
                nestTwo: int(default: 2),
            )
        );
        $array = [
            'one' => 'foo',
            'nest' => [],
        ];
        $expected = [
            'one' => 'foo',
            'nest' => [
                'nestOne' => 1,
                'nestTwo' => 2,
            ],
            'two' => 222,
            'nestTwice' => [
                'first' => [
                    'twice' => 1,
                ],
            ],
        ];
        $assert = assertArray($parameter, $array);
        $this->assertSame($expected, $assert);
        $this->assertCount(4, $parameter->parameters());
        $this->expectException(TypeError::class);
        $parameter(1);
    }

    public function testFunctionAssertArgument(): void
    {
        assertNamedArgument('test', int(), 123);
        $this->expectException(InvalidArgumentException::class);
        assertNamedArgument('fail', string(), 13.13);
    }

    public function testFunctionIterableParameter(): void
    {
        $parameter = iterable(
            V: string()
        );
        $this->assertSame('', $parameter->description());
        $parameter = iterable(
            K: string(),
            V: string(),
            description: 'foo'
        );
        $this->assertSame('foo', $parameter->description());
    }

    public function testAssertArrayIgnoreExtraArguments(): void
    {
        $parameter = arrayp(
            OK: string(),
        );
        $this->expectNotToPerformAssertions();
        assertArray($parameter, [
            'OK' => 'abc',
            'ERROR' => 123,
        ]);
    }

    public function testAssertArrayErrorType(): void
    {
        $parameter = arrayp(
            OK: string(),
        );
        $this->expectException(InvalidArgumentException::class);
        assertArray($parameter, [
            'OK' => 123,
        ]);
    }

    public function testAssertArrayErrorNull(): void
    {
        $parameter = arrayp(
            OK: string(),
        );
        $this->expectException(InvalidArgumentException::class);
        assertArray($parameter, [
            'OK' => null,
        ]);
    }

    public function testWithParametersFrom(): void
    {
        $foo = string(default: 'foo');
        $bar = int(default: 1);
        $parameters = parameters()
            ->withRequired('foo', $foo)
            ->withOptional('bar', $bar);
        $from = parametersFrom($parameters, 'foo', 'bar');
        $array = arrayp()
            ->withRequired(foo: $foo)
            ->withOptional(bar: $bar);
        $fromArray = parametersFrom($array, 'foo', 'bar');
        $this->assertEquals($from, $fromArray);
        $this->assertNotEquals($parameters, $from);
        $this->assertTrue($from->has('foo', 'bar'));
        $this->assertTrue($from->requiredKeys()->contains('foo', 'bar'));
        $this->assertFalse($from->optionalKeys()->contains('foo', 'bar'));
        $from = parametersFrom($parameters, 'bar');
        $this->assertNotEquals($parameters, $from);
        $this->assertTrue($from->has('bar'));
        $this->assertFalse($from->has('foo'));
        $this->assertTrue($from->requiredKeys()->contains('bar'));
    }

    public function testTakeKeys(): void
    {
        $parameters = parameters(foo: string())
            ->withOptional('bar', int());
        $this->assertSame(['foo', 'bar'], takeKeys($parameters));
    }

    public function testTakeFrom(): void
    {
        $foo = string(default: 'foo');
        $bar = int(default: 1);
        $parameters = parameters()
            ->withRequired('foo', $foo)
            ->withOptional('bar', $bar);
        $take = takeFrom($parameters, 'foo', 'bar');
        $takeArray = iterator_to_array($take);
        $this->assertSame(
            iterator_to_array($parameters),
            $takeArray
        );
        $this->assertSame(
            [
                'foo' => $foo,
                'bar' => $bar,
            ],
            $takeArray
        );
        $take = takeFrom($parameters, 'foo');
        $this->assertSame(
            [
                'foo' => $foo,
            ],
            iterator_to_array($take)
        );
        $take = takeFrom($parameters, 'bar');
        $this->assertSame(
            [
                'bar' => $bar,
            ],
            iterator_to_array($take)
        );
        $take = takeFrom($parameters, 'bar', 'foo');
        $this->assertSame(
            [
                'bar' => $bar,
                'foo' => $foo,
            ],
            iterator_to_array($take)
        );
        $this->expectException(OutOfBoundsException::class);
        iterator_to_array(takeFrom($parameters, '404'));
    }

    public function testArrayFrom(): void
    {
        $foo = int();
        $bar = string();
        $baz = float();
        $parameter = arrayp(
            foo: $foo,
            bar: $bar,
            baz: $baz,
        );
        $arrayFrom = arrayFrom($parameter, 'foo', 'baz');
        $this->assertSame(
            [
                'foo' => $foo,
                'baz' => $baz,
            ],
            iterator_to_array(
                $arrayFrom->parameters()
            )
        );
    }

    public function testGetType(): void
    {
        $table = [
            'object' => $this,
            'float' => 10.10,
            'null' => null,
            'int' => 1,
            'string' => 'foo',
            'array' => [],
            'bool' => true,
        ];
        foreach ($table as $type => $value) {
            $this->assertSame($type, getType($value));
        }
    }

    public function testTyped(): void
    {
        $value = 'string';
        $typed = typed($value);
        $this->assertSame($value, $typed->string());
    }

    public function testTypedArray(): void
    {
        $value = [
            'foo' => 'bar',
        ];
        $typed = typed($value, 'foo');
        $this->assertSame('bar', $typed->string());
    }

    public function testTypedNested(): void
    {
        $value = [
            'super' => [
                'taldo' => null,
            ],
            3 => 'co',
            'agac',
        ];
        $typed = typed($value);
        $this->assertSame($value, $typed->array());
        $typed = typed($value, 'super');
        $this->assertSame([
            'taldo' => null,
        ], $typed->array());
        $typed = typed($value, 'super', 'taldo');
        $this->assertSame(null, $typed->mixed());
        $typed = typed($value, 3);
        $this->assertSame('co', $typed->string());
        $typed = typed($value, 4);
        $this->assertSame('agac', $typed->string());
    }

    public static function dataProviderTypedNestedError(): array
    {
        return [
            [1, BadMethodCallException::class],
            [[], InvalidArgumentException::class],
        ];
    }

    #[DataProvider('dataProviderTypedNestedError')]
    public function testTypedNestedError(mixed $value, string $exception): void
    {
        $this->expectException($exception);
        typed($value, 'foo');
    }

    public function testParameterAttr(): void
    {
        $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("Parameter `foo` doesn't exists");
        parameterAttribute('foo', $caller['function'], $caller['class'] ?? '');
    }

    public function testValidatedParameterError(): void
    {
        $function = 'Chevere\Tests\src\validates';
        $base = 0;
        $times = 1;
        $name = 'Test';
        $this->expectException(ParameterException::class);
        $this->expectExceptionMessage("`{$function}` InvalidArgumentException → [base]: Argument value provided `0` is less than `1`");
        validated($function, $base, $times, $name);
    }

    public function testValidatedReturnError(): void
    {
        $function = 'Chevere\Tests\src\validates';
        $base = 99;
        $times = 1;
        $name = 'Test';
        $this->expectException(ReturnException::class);
        $this->expectExceptionMessage("`{$function}` InvalidArgumentException → Argument value provided `99` is less than `100`");
        validated($function, $base, $times, $name);
    }

    public function testValidatedFunction(): void
    {
        $function = 'Chevere\Tests\src\validates';
        $this->expectNotToPerformAssertions();
        validated($function, 100, 1, 'Test'); // base, times, name
    }

    #[DataProvider('dataProviderValMd')]
    public function testValMd(mixed $value, string $expects): void
    {
        $this->assertSame($expects, valMd($value));
    }

    public static function dataProviderValMd(): array
    {
        return [
            [null, ' `null`'],
            [1, ' `1`'],
            ['foo', ' `foo`'],
            ['', ''],
            [
                [0, 1, 2],
                ' `array ( 0 => 0, 1 => 1, 2 => 2, )`',
            ],
        ];
    }

    #[DataProvider('dataProviderCastArguments')]
    public function testCastArguments(
        array $expected,
        array $arguments,
        ParametersInterface|ParametersAccessInterface $parameters,
    ): void {
        $cast = castArguments($parameters, $arguments)->toArray();
        $this->assertSame($expected, $cast);
    }

    public static function dataProviderCastArguments(): array
    {
        return [
            [
                [
                    'foo' => 123,
                    'bar' => '456',
                    'baz' => 78.9,
                ],
                [
                    'foo' => '123',
                    'bar' => '456',
                    'baz' => '78.9',
                ],
                parameters(
                    foo: int(),
                    bar: string(),
                    baz: float()
                ),
            ],
            [
                [
                    'a' => '1',
                    'b' => 2,
                    'c' => 3.0,
                ],
                [
                    'a' => '1',
                    'b' => 2,
                    'c' => 3.0,
                ],
                parameters()->withIsVariadic(true),
            ],
            [
                [
                    'set' => 42,
                    'extra' => 'value',
                    'number' => '3.14',
                ],
                [
                    'set' => '42',
                    'extra' => 'value',
                    'number' => '3.14',
                ],
                parameters(
                    set: int(),
                    pairs: mixed(),
                )->withIsVariadic(true),
            ],
        ];
    }
}
