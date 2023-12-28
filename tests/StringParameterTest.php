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

use Chevere\Parameter\StringParameter;
use LogicException;
use PHPUnit\Framework\TestCase;

final class StringParameterTest extends TestCase
{
    public function testConstruct(): void
    {
        $string = new StringParameter();
        $this->assertSame('', $string->description());
        $this->assertSame(null, $string->default());
        $this->assertSame(null, $string->starts());
        $this->assertSame(null, $string->ends());
        $this->assertSame(null, $string->contains());
        $this->assertSame(null, $string->minLength());
        $this->assertSame(null, $string->maxLength());
        $this->assertSame(null, $string->length());
    }

    public function testWithDefault(): void
    {
        $string = new StringParameter();
        $with = $string->withDefault('foo');
        $this->assertNotSame($string, $with);
        $this->assertSame('foo', $with->default());
    }

    public function testWithLength(): void
    {
        $string = new StringParameter();
        $with = $string->withLength(123);
        $this->assertNotSame($string, $with);
        $this->assertSame(123, $with->length());
    }

    public function testWithLengthWithMinLength(): void
    {
        $string = new StringParameter();
        $with = $string->withLength(123);
        $this->expectExceptionMessage(
            <<<PLAIN
            Unable to set `minLength` rule when `length` rule is already set
            PLAIN
        );
        $with->withMinLength(123);
    }

    public function testWithLengthWithMaxLength(): void
    {
        $string = new StringParameter();
        $with = $string->withLength(123);
        $this->expectExceptionMessage(
            <<<PLAIN
            Unable to set `maxLength` rule when `length` rule is already set
            PLAIN
        );
        $with->withMaxLength(123);
    }

    public function testWithMinLength(): void
    {
        $string = new StringParameter();
        $with = $string->withMinLength(123);
        $this->assertNotSame($string, $with);
        $this->assertSame(123, $with->minLength());
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Unable to set `length` rule when `minLength|maxLength` rule is already set
            PLAIN
        );
        $with->withLength(123);
    }

    public function testWithMaxLength(): void
    {
        $string = new StringParameter();
        $with = $string->withMaxLength(123);
        $this->assertNotSame($string, $with);
        $this->assertSame(123, $with->maxLength());
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Unable to set `length` rule when `minLength|maxLength` rule is already set
            PLAIN
        );
        $with->withLength(123);
    }

    public function testWithStartsWith(): void
    {
        $string = new StringParameter();
        $with = $string->withStarts('foo');
        $this->assertNotSame($string, $with);
        $this->assertSame('foo', $with->starts());
    }

    public function testWithStartsWithLength(): void
    {
        $string = new StringParameter();
        $with = $string
            ->withStarts('foo')
            ->withLength(3);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided conflicts with `startsWith` rule
            PLAIN
        );
        $with->withLength(2);
    }

    public function testLengthWithStartsWith(): void
    {
        $string = new StringParameter();
        $with = $string
            ->withLength(3)
            ->withStarts('foo');
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided conflicts with `length` rule
            PLAIN
        );
        $with->withStarts('foobar');
    }

    public function testMaxLengthWithStartsWith(): void
    {
        $string = new StringParameter();
        $with = $string
            ->withMaxLength(3)
            ->withStarts('foo');
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided conflicts with `maxLength` rule
            PLAIN
        );
        $with->withStarts('foobar');
    }

    public function testWithEnds(): void
    {
        $string = new StringParameter();
        $with = $string->withEnds('bar');
        $this->assertNotSame($string, $with);
        $this->assertSame('bar', $with->ends());
    }

    public function testWithEndsWithLength(): void
    {
        $string = new StringParameter();
        $with = $string
            ->withEnds('bar')
            ->withLength(3);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided conflicts with `endsWith` rule
            PLAIN
        );
        $with->withLength(2);
    }

    public function testLengthWithEndsWith(): void
    {
        $string = new StringParameter();
        $with = $string
            ->withLength(3)
            ->withEnds('bar');
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided conflicts with `length` rule
            PLAIN
        );
        $with->withEnds('foobar');
    }

    public function testMaxLengthWithEndsWith(): void
    {
        $string = new StringParameter();
        $with = $string
            ->withMaxLength(3)
            ->withEnds('foo');
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided conflicts with `maxLength` rule
            PLAIN
        );
        $with->withEnds('foobar');
    }

    public function testWithContains(): void
    {
        $string = new StringParameter();
        $with = $string->withContains('foo');
        $this->assertNotSame($string, $with);
        $this->assertSame('foo', $with->contains());
    }

    public function testWithContainsWithLength(): void
    {
        $string = new StringParameter();
        $with = $string
            ->withContains('foo')
            ->withLength(3);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided conflicts with `contains` rule
            PLAIN
        );
        $with->withLength(2);
    }

    public function testLengthWithContains(): void
    {
        $string = new StringParameter();
        $with = $string
            ->withLength(3)
            ->withContains('foo');
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided conflicts with `length` rule
            PLAIN
        );
        $with->withContains('foobar');
    }

    public function testMaxLengthWithContains(): void
    {
        $string = new StringParameter();
        $with = $string
            ->withMaxLength(3)
            ->withContains('foo');
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided conflicts with `maxLength` rule
            PLAIN
        );
        $with->withContains('foobar');
    }
}
