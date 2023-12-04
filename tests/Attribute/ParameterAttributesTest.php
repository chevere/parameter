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

namespace Chevere\Tests\Attribute;

use Chevere\Tests\src\UsesParameterAttributes;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ParameterAttributesTest extends TestCase
{
    public function dataProviderWillSuccess(): array
    {
        return [
            [
                'name' => 'Rodolfo',
                'age' => 25,
                'cols' => [
                    'id' => 1,
                ],
                'tags' => ['Chevere', 'Chevere', 'Chevere', 'Uh'],
            ],
        ];
    }

    public function dataProviderWillFail(): array
    {
        return [
            [
                'name' => 'Peoples Hernandez',
                'age' => 66,
                'cols' => [
                    'id' => 1,
                ],
                'tags' => ['people'],
                'error' => "Argument value provided `Peoples Hernandez` doesn't match the regex `/^[A-Za-z]+$/`",
            ],
            [
                'name' => 'zerothehero',
                'age' => 0,
                'cols' => [
                    'id' => 1,
                ],
                'tags' => ['zero'],
                'error' => 'Argument value provided `0` is less than `1`',
            ],
            [
                'name' => 'SergioDalmata',
                'age' => 101,
                'cols' => [
                    'id' => 1,
                ],
                'tags' => ['dalmata'],
                'error' => 'Argument value provided `101` is greater than `100`',
            ],
            [
                'name' => 'DonZeroId',
                'age' => 42,
                'cols' => [
                    'id' => 0,
                ],
                'tags' => ['zeroid'],
                'error' => '[id]: Argument value provided `0` is less than `1`',
            ],
            [
                'name' => 'iterableNull',
                'age' => 24,
                'cols' => [
                    'id' => 42,
                ],
                'tags' => [123],
                'error' => 'Argument #1 ($value) must be of type Stringable|string, int given',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderWillFail
     */
    public function testWillFail(
        string $name,
        int $age,
        array $array,
        iterable $iterable,
        string $error
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($error);

        new UsesParameterAttributes($name, $age, $array, $iterable);
    }

    /**
     * @dataProvider dataProviderWillSuccess
     */
    public function testWillSuccess(
        string $name,
        int $age,
        array $array,
        iterable $iterable
    ): void {
        $this->expectNotToPerformAssertions();

        new UsesParameterAttributes($name, $age, $array, $iterable);
    }

    public function testWea(): void
    {
        $arguments = $this->dataProviderWillSuccess()[0];
        $object = new UsesParameterAttributes(...$arguments);
        $object->run(5);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument value provided `6` is greater than `5`');
        $object->run(6);
    }
}
