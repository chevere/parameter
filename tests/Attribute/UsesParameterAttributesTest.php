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

use Chevere\Tests\src\NoUsesAttr;
use Chevere\Tests\src\UsesAttr;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;

final class UsesParameterAttributesTest extends TestCase
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
                'flag' => false,
                'amount' => 0,
                'null' => null,
                'enum' => 'test',
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
                'flag' => false,
                'amount' => 0,
                'null' => null,
                'enum' => 'test',
                'error' => "Argument value provided `Peoples Hernandez` doesn't match the regex `/^[A-Za-z]+$/`",
            ],
            [
                'name' => 'zerothehero',
                'age' => 0,
                'cols' => [
                    'id' => 1,
                ],
                'tags' => ['zero'],
                'flag' => false,
                'amount' => 0,
                'null' => null,
                'enum' => 'test',
                'error' => 'Argument value provided `0` is less than `1`',
            ],
            [
                'name' => 'SergioDalmata',
                'age' => 101,
                'cols' => [
                    'id' => 1,
                ],
                'tags' => ['dalmata'],
                'flag' => false,
                'amount' => 0,
                'null' => null,
                'enum' => 'test',
                'error' => 'Argument value provided `101` is greater than `100`',
            ],
            [
                'name' => 'DonZeroId',
                'age' => 42,
                'cols' => [
                    'id' => 0,
                ],
                'tags' => ['zeroid'],
                'flag' => false,
                'amount' => 0,
                'null' => null,
                'enum' => 'test',
                'error' => '[id]: Argument value provided `0` is less than `1`',
            ],
            [
                'name' => 'iterableNull',
                'age' => 24,
                'cols' => [
                    'id' => 42,
                ],
                'tags' => [123],
                'flag' => false,
                'amount' => 0,
                'null' => null,
                'enum' => 'test',
                'error' => 'Argument #1 ($value) must be of type Stringable|string, int given',
            ],
            [
                'name' => 'negativeAmount',
                'age' => 24,
                'cols' => [
                    'id' => 42,
                ],
                'tags' => ['test'],
                'flag' => false,
                'amount' => -10.5,
                'null' => null,
                'enum' => 'test',
                'error' => 'Argument value provided `-10.5` is less than `0`',
            ],
            [
                'name' => 'wrongEnum',
                'age' => 24,
                'cols' => [
                    'id' => 42,
                ],
                'tags' => ['test'],
                'flag' => false,
                'amount' => 100.5,
                'null' => null,
                'enum' => 'try',
                'error' => "Argument value provided `try` doesn't match the regex `/\b(test|value)\b/`",
            ],
        ];
    }

    /**
     * @dataProvider dataProviderWillFail
     */
    public function testWillFail(
        string $name,
        int $age,
        array $cols,
        iterable $tags,
        bool $flag,
        float $amount,
        mixed $null,
        string $enum,
        string $error
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($error);
        $args = func_get_args();
        array_pop($args);
        new UsesAttr(...$args);
    }

    /**
     * @dataProvider dataProviderWillSuccess
     */
    public function testWillSuccess(
        string $name,
        int $age,
        array $cols,
        iterable $tags,
        bool $flag,
        float $amount,
        mixed $null,
        string $enum,
    ): void {
        $this->expectNotToPerformAssertions();

        new UsesAttr(...func_get_args());
    }

    public function testReturnAttr(): void
    {
        $arguments = $this->dataProviderWillSuccess()[0];
        $object = new UsesAttr(...$arguments);
        $object->run(5);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument value provided `6` is greater than `5`');
        $object->run(6);
    }

    public function testNoReturnRule(): void
    {
        $object = new NoUsesAttr();
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Callable `Chevere\Tests\src\NoUsesAttr::return` must return a `Chevere\Parameter\Interfaces\ParameterInterface` instance');
        $object->run();
    }
}
