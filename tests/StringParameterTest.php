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
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;

final class StringParameterTest extends TestCase
{
    public function testConstruct(): void
    {
        $string = new StringParameter();
        $string('');
        $this->assertSame('', $string->description());
        $this->assertSame(null, $string->default());
        $this->assertSame(null, $string->startsWith());
        $this->assertSame(null, $string->endsWith());
        $this->assertSame([], $string->contains());
        $this->assertSame(null, $string->minLength());
        $this->assertSame(null, $string->maxLength());
        $this->assertSame(null, $string->length());
        $expect = [
            'type' => $string->type()->primitive(),
            'description' => $string->description(),
            'default' => $string->default(),
            'startsWith' => $string->startsWith(),
            'endsWith' => $string->endsWith(),
            'contains' => $string->contains(),
            'reject' => $string->reject(),
            'minLength' => $string->minLength(),
            'maxLength' => $string->maxLength(),
            'length' => $string->length(),
        ];
        $this->assertSame($expect, $string->schema());
    }

    public function dataProviderPassIntValues(): array
    {
        return [
            ['withMinLength'],
            ['withMaxLength'],
            ['withLength'],
        ];
    }

    /**
     * @dataProvider dataProviderPassIntValues
     */
    public function testPassIntValues(string $method): void
    {
        $string = new StringParameter();
        $string->{$method}(0);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided `-1` is less than `0`
            PLAIN
        );
        $string->{$method}(-1);
    }

    public function testWithDefault(): void
    {
        $string = new StringParameter();
        $with = $string->withDefault('foo');
        $with('');
        $this->assertNotSame($string, $with);
        $this->assertSame('foo', $with->default());
    }

    public function testWithLength(): void
    {
        $string = new StringParameter();
        $with = $string->withLength(5);
        $this->assertNotSame($string, $with);
        $this->assertSame(5, $with->length());
        $with('12345');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument `123456` length (6) is different from 5
            PLAIN
        );
        $with('123456');
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
        $with = $string->withMinLength(2);
        $this->assertNotSame($string, $with);
        $this->assertSame(2, $with->minLength());
        $with('12');
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument `1` length (1) is less than 2
            PLAIN
        );
        $with('1');
    }

    public function testWithMinLengthWithLength(): void
    {
        $string = new StringParameter();
        $with = $string->withMinLength(2);
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
        $with = $string->withMaxLength(3);
        $this->assertNotSame($string, $with);
        $this->assertSame(3, $with->maxLength());
        $with('123');
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument `1234` length (4) is greater than 3
            PLAIN
        );
        $with('1234');
    }

    public function testWithMaxLengthWithLength(): void
    {
        $string = new StringParameter();
        $with = $string->withMaxLength(123);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Unable to set `length` rule when `minLength|maxLength` rule is already set
            PLAIN
        );
        $with->withLength(123);
    }

    public function testWithMaxLengthWithMinLength(): void
    {
        $string = new StringParameter();
        $with = $string->withMaxLength(0);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided conflicts with `maxLength` rule
            PLAIN
        );
        $with->withMinLength(1);
    }

    public function testWithStartsWith(): void
    {
        $string = new StringParameter();
        $with = $string->withStartsWith('foo');
        $with('foobar');
        $this->assertNotSame($string, $with);
        $this->assertSame('foo', $with->startsWith());
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument `barfoo` doesn't start with `foo`
            PLAIN
        );
        $with('barfoo');
    }

    public function testWithStartsWithLength(): void
    {
        $string = new StringParameter();
        $with = $string
            ->withStartsWith('🥁foo')
            ->withLength(4);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided conflicts with `startsWith` rule
            PLAIN
        );
        $with->withLength(2);
    }

    public function testWithStartsWithMaxLength(): void
    {
        $string = new StringParameter();
        $with = $string
            ->withStartsWith('foo')
            ->withMaxLength(3);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided conflicts with `startsWith` rule
            PLAIN
        );
        $with->withMaxLength(2);
    }

    public function testLengthWithStartsWith(): void
    {
        $string = new StringParameter();
        $with = $string
            ->withLength(3)
            ->withStartsWith('foo');
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided conflicts with `length` rule
            PLAIN
        );
        $with->withStartsWith('foobar');
    }

    public function testMaxLengthWithStartsWith(): void
    {
        $string = new StringParameter();
        $with = $string
            ->withMaxLength(4)
            ->withStartsWith('🥁foo');
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided conflicts with `maxLength` rule
            PLAIN
        );
        $with->withStartsWith('foobar');
    }

    public function testWithEnds(): void
    {
        $string = new StringParameter();
        $with = $string->withEndsWith('bar');
        $with('foobar');
        $this->assertNotSame($string, $with);
        $this->assertSame('bar', $with->endsWith());
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument `barfoo` doesn't ends with `bar`
            PLAIN
        );
        $with('barfoo');
    }

    public function testWithEndsWithLength(): void
    {
        $string = new StringParameter();
        $with = $string
            ->withEndsWith('🥁bar')
            ->withLength(4);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided conflicts with `endsWith` rule
            PLAIN
        );
        $with->withLength(2);
    }

    public function testWithEndsWithMaxLength(): void
    {
        $string = new StringParameter();
        $with = $string
            ->withEndsWith('bar')
            ->withMaxLength(3);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided conflicts with `endsWith` rule
            PLAIN
        );
        $with->withMaxLength(2);
    }

    public function testLengthWithEndsWith(): void
    {
        $string = new StringParameter();
        $with = $string
            ->withLength(3)
            ->withEndsWith('bar');
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided conflicts with `length` rule
            PLAIN
        );
        $with->withEndsWith('foobar');
    }

    public function testMaxLengthWithEndsWith(): void
    {
        $string = new StringParameter();
        $with = $string
            ->withMaxLength(4)
            ->withEndsWith('🥁foo');
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided conflicts with `maxLength` rule
            PLAIN
        );
        $with->withEndsWith('foobar');
    }

    public function testWithContains(): void
    {
        $string = new StringParameter();
        $with = $string->withContains('foo');
        $with('el foobar este');
        $this->assertNotSame($string, $with);
        $this->assertSame(['foo'], $with->contains());
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument `no chance` doesn't contain `foo`
            PLAIN
        );
        $with('no chance');
    }

    public function testWithContainsWithLength(): void
    {
        $string = new StringParameter();
        $with = $string
            ->withContains('🥁foo')
            ->withLength(4);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided conflicts with `contains` rule
            PLAIN
        );
        $with->withLength(2);
    }

    public function testWithContainsWithMaxLength(): void
    {
        $string = new StringParameter();
        $with = $string
            ->withContains('foo')
            ->withMaxLength(3);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided conflicts with `contains` rule
            PLAIN
        );
        $with->withMaxLength(2);
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
            ->withMaxLength(4)
            ->withContains('🥁foo');
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided conflicts with `maxLength` rule
            PLAIN
        );
        $with->withContains('foobar');
    }

    public function testWithReject(): void
    {
        $string = new StringParameter();
        $with = $string->withReject('foo');
        $with('bar');
        $this->assertNotSame($string, $with);
        $this->assertSame(['foo'], $with->reject());
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument `foo` contains rejected value `foo`
            PLAIN
        );
        $with('foo');
    }

    public function testWithRejectWithLength(): void
    {
        $string = new StringParameter();
        $with = $string
            ->withReject('🥁foo')
            ->withLength(4);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided conflicts with `reject` rule
            PLAIN
        );
        $with->withLength(2);
    }

    public function testWithRejectWithMaxLength(): void
    {
        $string = new StringParameter();
        $with = $string
            ->withReject('foo')
            ->withMaxLength(3);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided conflicts with `reject` rule
            PLAIN
        );
        $with->withMaxLength(2);
    }

    public function testLengthWithReject(): void
    {
        $string = new StringParameter();
        $with = $string
            ->withLength(3)
            ->withReject('foo');
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided conflicts with `length` rule
            PLAIN
        );
        $with->withReject('foobar');
    }

    public function testMaxLengthWithReject(): void
    {
        $string = new StringParameter();
        $with = $string
            ->withMaxLength(4)
            ->withReject('🥁foo');
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument value provided conflicts with `maxLength` rule
            PLAIN
        );
        $with->withReject('foobar');
    }
}
