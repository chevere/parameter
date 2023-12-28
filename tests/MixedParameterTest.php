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

use Chevere\Parameter\MixedParameter;
use PHPUnit\Framework\TestCase;

final class MixedParameterTest extends TestCase
{
    public function testConstruct(): void
    {
        $parameter = new MixedParameter();
        $this->assertSame(null, $parameter->default());
        $compatible = new MixedParameter();
        $parameter->assertCompatible($compatible);
        $this->assertSame([
            'type' => 'mixed',
            'description' => '',
            'default' => null,
        ], $parameter->schema());
        $parameter(null);
        $parameter(1);
    }

    public function testWithDefault(): void
    {
        $parameter = new MixedParameter();
        $with = $parameter->withDefault('default');
        $this->assertNotSame($parameter, $with);
        $this->assertSame('default', $with->default());
    }
}
