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

use Chevere\Parameter\IterableParameter;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function Chevere\Parameter\arguments;
use function Chevere\Parameter\int;
use function Chevere\Parameter\iterable;
use function Chevere\Parameter\string;
use function Chevere\Parameter\union;

final class IterableParameterTest extends TestCase
{
    public function testConstruct(): void
    {
        $value = string();
        $key = string();
        $description = 'test';
        $parameter = new IterableParameter(
            $value,
            $key,
            $description
        );
        $this->assertSame($value, $parameter->value());
        $this->assertSame($key, $parameter->key());
        $this->assertSame(null, $parameter->default());
        $this->assertSame($description, $parameter->description());
        $this->assertSame([
            'type' => 'iterable',
            'description' => $description,
            'default' => null,
            'parameters' => [
                'K' => [
                    'required' => true,
                ] + $key->schema(),
                'V' => [
                    'required' => true,
                ] + $value->schema(),
            ],
        ], $parameter->schema());
        $parameters = $parameter->parameters();
        $this->assertSame($parameters, $parameter->parameters());
        $this->assertEquals($value, $parameters->get('V'));
        $this->assertEquals($key, $parameters->get('K'));
    }

    public function testAssertCompatible(): void
    {
        $this->expectNotToPerformAssertions();
        $key = string();
        $value = int(description: 'compatible');
        $keyAlt = string(description: 'compatible');
        $valueAlt = int();
        $parameter = new IterableParameter($value, $key);
        $compatible = new IterableParameter($valueAlt, $keyAlt, 'compatible');
        $parameter->assertCompatible($compatible);
    }

    public function testAssertCompatibleConflictValue(): void
    {
        $key = string();
        $value = int();
        $valueAlt = int(min: 1);
        $parameter = new IterableParameter($value, $key);
        $notCompatible = new IterableParameter($valueAlt, $key);
        $this->expectException(InvalidArgumentException::class);
        $parameter->assertCompatible($notCompatible);
    }

    public function testAssertCompatibleConflictKey(): void
    {
        $key = string();
        $value = int();
        $keyAlt = string('/^[a-z]+&/');
        $parameter = new IterableParameter($value, $key);
        $notCompatible = new IterableParameter($value, $keyAlt);
        $this->expectException(InvalidArgumentException::class);
        $parameter->assertCompatible($notCompatible);
    }

    public function testNestedIterable(): void
    {
        $this->expectNotToPerformAssertions();
        $parameter = iterable(
            V: string(),
            K: string()
        );
        $argument = [
            'a' => 'A',
        ];
        $parameter($argument);
        $parameter = iterable(
            V: $parameter,
        );
        $argument = [
            [
                'b' => 'B',
            ],
            [
                'c' => 'C',
            ],
        ];
        $parameter($argument);
    }

    public function testIterableArguments(): void
    {
        $parameter = iterable(
            V: string(),
            K: int()
        );
        $array = [
            0 => 'foo',
            1 => 'bar',
            2 => 'baz',
        ];
        $arguments = arguments($parameter, $array);
        $this->assertSame($array['0'], $arguments->required('0')->string());
        $parameter = iterable(
            V: iterable(
                string()
            ),
            K: string()
        );
        $array = [
            '0' => ['foo', 'oof'],
            '1' => ['bar'],
            '2' => ['baz', 'bar'],
        ];
        $arguments = arguments($parameter, $array);
        $this->assertSame($array['0'], $arguments->required('0')->array());
    }

    public function testInvoke(): void
    {
        $value = [10, '10'];
        $parameter = iterable(union(int(), string()));
        $parameter($value);
        $this->expectException(InvalidArgumentException::class);
        $parameter([]);
    }

    public function testKeyError(): void
    {
        $parameter = iterable(K: int(), V: string());
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            [K *iterable]: Argument #1 (\$value) must be of type int, string given
            PLAIN
        );
        $parameter([
            'key' => 'foo',
        ]);
    }

    public function testValueError(): void
    {
        $parameter = iterable(K: int(), V: string());
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            [V *iterable]: Argument #1 (\$value) must be of type Stringable|string, int given
            PLAIN
        );
        $parameter([
            100 => 100,
        ]);
    }

    public function testWithDefault(): void
    {
        $value = [10, '10'];
        $parameter = iterable(union(int(), string()));
        $with = $parameter->withDefault($value);
        $this->assertNotSame($parameter, $with);
        $this->assertSame($value, $with->default());
        $this->expectException(InvalidArgumentException::class);
        $parameter->withDefault([null, false]);
    }
}
