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

use Chevere\Parameter\Interfaces\TypeInterface;
use Chevere\Parameter\UnionParameter;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use function Chevere\Parameter\arguments;
use function Chevere\Parameter\float;
use function Chevere\Parameter\int;
use function Chevere\Parameter\null;
use function Chevere\Parameter\parameters;
use function Chevere\Parameter\string;
use function Chevere\Parameter\union;

final class UnionParameterTest extends TestCase
{
    public function testConstructError(): void
    {
        $parameters = parameters(
            foo: string(),
        );
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Must pass at least two parameters for union');
        new UnionParameter($parameters);
    }

    public function testConstruct(): void
    {
        $parameters = parameters(
            foo: string(),
            bar: int()
        );
        $parameter = new UnionParameter($parameters);
        $this->assertSame(
            TypeInterface::UNION,
            $parameter->type()->primitive()
        );
        $this->assertCount(2, $parameter->parameters());
        $this->assertSame(TypeInterface::UNION, $parameter->typeSchema());
        $parameter('string');
        $parameter(123);
    }

    public function testWithAdded(): void
    {
        $foo = string();
        $bar = int();
        $baz = float();
        $pun = null();
        $parameters = parameters(
            foo: $foo,
            bar: $bar
        );
        $parameter = new UnionParameter($parameters);
        $with = $parameter->withAdded(
            baz: $baz,
            pun: $pun
        );
        $this->assertNotSame($parameter, $with);
        $this->assertCount(4, $with->parameters());
        $this->assertSame($foo, $with->parameters()->get('foo'));
        $this->assertSame($bar, $with->parameters()->get('bar'));
        $this->assertSame($baz, $with->parameters()->get('baz'));
        $this->assertSame($pun, $with->parameters()->get('pun'));
    }

    public function testAssertCompatible(): void
    {
        $parameters = parameters(
            int: int(),
            string: string(),
        );
        $parametersAlt = parameters(
            string: string(description: 'one'),
            int: int(),
        );
        $parameter = new UnionParameter($parameters);
        $compatible = new UnionParameter($parametersAlt);
        $this->expectNotToPerformAssertions();
        $parameter->assertCompatible($compatible);
    }

    public function testAssertNotCompatible(): void
    {
        $parameters = parameters(
            string(),
            int(),
        );
        $parametersAlt = parameters(
            int(),
            string(),
        );
        $parameter = new UnionParameter($parameters);
        $compatible = new UnionParameter($parametersAlt);
        $this->expectException(InvalidArgumentException::class);
        $parameter->assertCompatible($compatible);
    }

    public function testUnionArguments(): void
    {
        $parameter = union(
            string(),
            int()
        );
        $array = [
            0 => 'foo',
            1 => 1,
        ];
        $arguments = arguments($parameter, $array);
        $this->assertSame($array['0'], $arguments->required('0')->string());
        $this->assertSame($array['1'], $arguments->required('1')->int());
    }

    public function testInvoke(): void
    {
        $parameter = union(
            string(),
            int()
        );
        $parameter(10);
        $parameter('10');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument provided doesn't match union: Parameter `0` <Chevere\Parameter\StringParameter>: Argument #1 (\$value) must be of type Stringable|string, float given; Parameter `1` <Chevere\Parameter\IntParameter>: Argument #1 (\$value) must be of type int, float given
            PLAIN
        );
        $parameter(1.1);
    }

    public function testWithDefault(): void
    {
        $parameter = union(
            string(),
            int()
        );
        $this->assertSame(null, $parameter->default());
        $with = $parameter->withDefault(10);
        $this->assertNotSame($parameter, $with);
        $this->assertSame(10, $with->default());
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument provided doesn't match union: Parameter `0` <Chevere\Parameter\StringParameter>: Argument #1 (\$value) must be of type Stringable|string, array given; Parameter `1` <Chevere\Parameter\IntParameter>: Argument #1 (\$value) must be of type int, array given
            PLAIN
        );
        $parameter->withDefault([]);
    }
}
