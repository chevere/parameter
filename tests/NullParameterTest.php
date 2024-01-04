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

use Chevere\Parameter\NullParameter;
use PHPUnit\Framework\TestCase;
use TypeError;

final class NullParameterTest extends TestCase
{
    public function testConstruct(): void
    {
        $parameter = new NullParameter();
        $this->assertSame(null, $parameter->default());
        $this->assertSame([
            'type' => 'null',
            'description' => '',
            'default' => null,
        ], $parameter->schema());
        $parameter(null);
    }

    public function testWithDefault(): void
    {
        $parameter = new NullParameter();
        $with = $parameter->withDefault(null);
        $this->assertNotSame($parameter, $with);
        $parameter->assertCompatible($with);
        $with(null);
    }

    public function testCompatible(): void
    {
        $this->expectNotToPerformAssertions();
        $parameter = new NullParameter();
        $compatible = new NullParameter();
        $parameter->assertCompatible($compatible);
    }

    public function testError(): void
    {
        $parameter = new NullParameter();
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided is not of type null
            PLAIN
        );
        $parameter(1);
    }
}
