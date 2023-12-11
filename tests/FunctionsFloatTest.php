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

use PHPUnit\Framework\TestCase;
use function Chevere\Parameter\float;

final class FunctionsFloatTest extends TestCase
{
    public function testFloat(): void
    {
        $parameter = float();
        $this->assertSame('', $parameter->description());
        $this->assertSame(null, $parameter->default());
        $this->assertSame(null, $parameter->min());
        $this->assertSame(null, $parameter->max());
        $this->assertSame([], $parameter->accept());
        $this->assertSame([], $parameter->reject());
    }

    public function testFloatOptions(): void
    {
        $description = 'test';
        $default = 5.0;
        $parameter = float(
            description: $description,
            default: $default,
            min: -100,
            max: 100,
        );
        $this->assertSame($description, $parameter->description());
        $this->assertSame($default, $parameter->default());
        $this->assertSame(-100.0, $parameter->min());
        $this->assertSame(100.0, $parameter->max());
        $parameter = float(accept: [0, 1]);
        $this->assertSame([0.0, 1.0], $parameter->accept());
        $parameter = float(reject: [0, 1]);
        $this->assertSame([0.0, 1.0], $parameter->reject());
    }

    public function testAssertFloat(): void
    {
        $parameter = float();
        $this->assertSame(0.0, $parameter(0));
        $this->assertSame(0.0, $parameter(0.0));
    }
}
