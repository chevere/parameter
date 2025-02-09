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

use Chevere\Parameter\BoolParameter;
use PHPUnit\Framework\TestCase;
use function Chevere\Parameter\bool;

final class BoolParameterTest extends TestCase
{
    public function testConstruct(): void
    {
        $parameter = new BoolParameter();
        $this->assertSame(null, $parameter->default());
        $default = true;
        $parameterWithDefault = $parameter->withDefault($default);
        (new ParameterHelper())->testWithParameterDefault(
            primitive: 'bool',
            parameter: $parameter,
            default: $default,
            parameterWithDefault: $parameterWithDefault
        );
        $this->assertSame([
            'type' => 'bool',
            'description' => '',
            'default' => $default,
        ], $parameterWithDefault->schema());
    }

    public function testWithSensitive(): void
    {
        $parameter = bool();
        $with = $parameter->withIsSensitive();
        $this->assertNotEquals($parameter, $with);
        $this->assertTrue($with->isSensitive());
    }
}
