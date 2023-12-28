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

namespace Chevere\Tests\Attribute;

use Chevere\Parameter\Attributes\CallableAttr;
use Chevere\Parameter\Interfaces\StringParameterInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function Chevere\Parameter\string;

final class CallableAttrTest extends TestCase
{
    public function testCallableNoReturn(): void
    {
        $callable = function (): void {
        };
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Callable must return a `Chevere\Parameter\Interfaces\ParameterInterface` instance
            PLAIN
        );
        new CallableAttr($callable);
    }

    public function testCallableWrongReturn(): void
    {
        $callable = function (): string {
            return 'test';
        };
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Callable must return a `Chevere\Parameter\Interfaces\ParameterInterface` instance
            PLAIN
        );
        new CallableAttr($callable);
    }

    public function testConstruct(): void
    {
        $parameter = string('/^foo/');
        $callable = function () use ($parameter): StringParameterInterface {
            return $parameter;
        };
        $attr = new CallableAttr($callable);
        $this->assertSame($parameter, $attr->parameter());
        $attr('foo');
    }
}
