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

namespace Chevere\Tests\Attributes;

use Chevere\Parameter\Attributes\_callable;
use Chevere\Parameter\Interfaces\StringParameterInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function Chevere\Parameter\string;

final class _callableTest extends TestCase
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
        new _callable($callable);
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
        new _callable($callable);
    }

    public function testConstruct(): void
    {
        $parameter = string('/^foo/');
        $callable = function () use ($parameter): StringParameterInterface {
            return $parameter;
        };
        $attr = new _callable($callable);
        $this->assertSame($parameter, $attr->parameter());
        $attr('foo');
    }
}
