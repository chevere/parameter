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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UsesParameterAttributesTest extends TestCase
{
    public const DEFAULT_ARGUMENTS = [
        'name' => 'Test',
        'age' => 12,
        'cols' => [
            'id' => 1,
        ],
        'tags' => ['Chevere', 'Chevere', 'Chevere', 'Uh'],
        'flag' => false,
        'amount' => 0,
        'null' => null,
        'enum' => 'value',
        'union' => 1,
    ];

    public static function dataProviderWillSuccess(): array
    {
        return [
            static::DEFAULT_ARGUMENTS,
        ];
    }

    public static function dataProviderWillFail(): array
    {
        return [
            array_merge(static::DEFAULT_ARGUMENTS, [
                'name' => 'Peoples Hernandez',
                'error' => "[name]: Argument value provided `Peoples Hernandez` doesn't match the regex `/^[A-Za-z]+$/`",
            ]),
            array_merge(static::DEFAULT_ARGUMENTS, [
                'age' => 0,
                'error' => '[age]: Argument value provided `0` is less than `1`',
            ]),
            array_merge(static::DEFAULT_ARGUMENTS, [
                'age' => 101,
                'error' => '[age]: Argument value provided `101` is greater than `100`',
            ]),
            array_merge(static::DEFAULT_ARGUMENTS, [
                'cols' => [
                    'id' => 0,
                ],
                'error' => '[cols]: [id]: Argument value provided `0` is less than `1`',
            ]),
            array_merge(static::DEFAULT_ARGUMENTS, [
                'tags' => [123],
                'error' => '[tags]: [V *iterable]: Argument must be of type Stringable|string, int given',
            ]),
            array_merge(static::DEFAULT_ARGUMENTS, [
                'amount' => -10.5,
                'error' => '[amount]: Argument value provided `-10.5` is less than `0`',
            ]),
            array_merge(static::DEFAULT_ARGUMENTS, [
                'enum' => 'try',
                'error' => "[enum]: Argument value provided `try` doesn't match the regex `#^test|value$#`",
            ]),
            array_merge(static::DEFAULT_ARGUMENTS, [
                'union' => 0,
                'error' => <<<PLAIN
                [union]: Argument provided doesn't match union: Parameter `0` <Chevere\Parameter\IntParameter>: Argument value provided `0` is less than `1`; Parameter `1` <Chevere\Parameter\StringParameter>: Argument must be of type Stringable|string, int given
                PLAIN,
            ]),
        ];
    }

    #[DataProvider('dataProviderWillFail')]
    public function testWillFail(
        string $name,
        int $age,
        array $cols,
        iterable $tags,
        bool $flag,
        float $amount,
        mixed $null,
        string $enum,
        int|string $union,
        string $error
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($error);
        $args = func_get_args();
        array_pop($args);
        new UsesAttr(...$args);
    }

    #[DataProvider('dataProviderWillSuccess')]
    public function testWillSuccess(
        string $name,
        int $age,
        array $cols,
        iterable $tags,
        bool $flag,
        float $amount,
        mixed $null,
        string $enum,
        int|string $union
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

    public function testNoReturnRuleFallbackMixed(): void
    {
        $object = new NoUsesAttr();
        $object->run();
    }
}
