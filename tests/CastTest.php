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

use ArrayObject;
use Chevere\Parameter\Cast;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CastTest extends TestCase
{
    public static function dataProviderCast(): array
    {
        return [
            [null, 'mixed'],
            [1, 'int'],
            [1.1, 'float'],
            [true, 'bool'],
            ['string', 'string'],
            [[], 'array'],
            [new Cast(''), 'object'],
            [
                fn () => null,
                'callable',
            ],
            [[], 'iterable'],
            [1, 'nullInt'],
            [null, 'nullInt'],
            [1.1, 'nullFloat'],
            [null, 'nullFloat'],
            [true, 'nullBool'],
            [null, 'nullBool'],
            ['string', 'nullString'],
            [null, 'nullString'],
            [[], 'nullArray'],
            [null, 'nullArray'],
            [new Cast(''), 'nullObject'],
            [null, 'nullObject'],
            [
                fn () => null,
                'nullCallable',
            ],
            [
                null,
                'nullCallable',
            ],
            [[], 'nullIterable'],
            [null, 'nullIterable'],
        ];
    }

    #[DataProvider('dataProviderCast')]
    public function testCast($expected, string $method): void
    {
        $cast = new Cast($expected);
        $this->assertSame($expected, $cast->{$method}());
    }

    public function testArrayAccess(): void
    {
        $input = ['foo'];
        $value = new ArrayObject($input);
        $cast = new Cast($value);
        $this->assertSame($input, $cast->array());
    }
}
