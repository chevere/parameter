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
use stdClass;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\arrayString;
use function Chevere\Parameter\bool;
use function Chevere\Parameter\float;
use function Chevere\Parameter\int;
use function Chevere\Parameter\nullArray;
use function Chevere\Parameter\nullArrayString;
use function Chevere\Parameter\nullBool;
use function Chevere\Parameter\nullFloat;
use function Chevere\Parameter\nullInt;
use function Chevere\Parameter\nullObject;
use function Chevere\Parameter\nullString;
use function Chevere\Parameter\object;
use function Chevere\Parameter\string;
use function Chevere\Parameter\unionNull;

final class FunctionsNullTest extends TestCase
{
    public function testNullInt(): void
    {
        $this->assertEquals(
            unionNull(int()),
            nullInt()
        );
    }

    public function testNullFloat(): void
    {
        $this->assertEquals(
            unionNull(float()),
            nullFloat()
        );
    }

    public function testNullBool(): void
    {
        $this->assertEquals(
            unionNull(bool()),
            nullBool()
        );
    }

    public function testNullString(): void
    {
        $this->assertEquals(
            unionNull(string()),
            nullString()
        );
    }

    public function testNullArray(): void
    {
        $this->assertEquals(
            unionNull(arrayp()),
            nullArray()
        );
        $this->assertEquals(
            unionNull(arrayp(a: string())),
            nullArray(a: string())
        );
    }

    public function testNullArrayString(): void
    {
        $this->assertEquals(
            unionNull(arrayString()),
            nullArrayString()
        );
    }

    public function testNullObject(): void
    {
        $this->assertEquals(
            unionNull(object(stdClass::class)),
            nullObject(stdClass::class)
        );
    }
}
