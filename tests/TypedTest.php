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

use Chevere\Parameter\Typed;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TypedTest extends TestCase
{
    public static function dataProviderTyped(): array
    {
        return [
            [null, 'mixed'],
            [1, 'int'],
            [1.1, 'float'],
            [true, 'bool'],
            ['string', 'string'],
            [[], 'array'],
            [new Typed(''), 'object'],
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
            [new Typed(''), 'nullObject'],
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

    #[DataProvider('dataProviderTyped')]
    public function testTyped($expected, string $method): void
    {
        $typed = new Typed($expected);
        $this->assertSame($expected, $typed->{$method}());
    }
}
