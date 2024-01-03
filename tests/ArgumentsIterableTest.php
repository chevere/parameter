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

use Chevere\Parameter\Arguments;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function Chevere\Parameter\arrayp;
use function Chevere\Parameter\int;
use function Chevere\Parameter\iterable;
use function Chevere\Parameter\parameters;
use function Chevere\Parameter\string;

final class ArgumentsIterableTest extends TestCase
{
    public function iterableArrayProvider(): array
    {
        return [
            [
                [
                    'a' => 'foo',
                    'b' => 'bar',
                ],
            ],
        ];
    }

    public function iterableArrayPropertyProvider(): array
    {
        return [
            [
                [
                    'top' => [
                        1 => 'one',
                        2 => 'two',
                    ],
                ],
            ],
        ];
    }

    public function iterableArrayNestedPropertyProvider(): array
    {
        return [
            [
                [
                    'nested' => [
                        1 => [
                            'foo' => 1,
                            'bar' => 2,
                        ],
                        2 => [
                            'wea' => 3,
                            'baz' => 4,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function iterableProvider(): array
    {
        return [
            [
                [
                    'test' => [
                        'one' => 123,
                        'two' => 456,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider iterableProvider
     */
    public function testIterable(array $args): void
    {
        $parameters = parameters(
            test: arrayp(
                one: int(),
                two: int(),
            )
        );
        $this->expectNotToPerformAssertions();
        new Arguments($parameters, $args);
    }

    /**
     * @dataProvider iterableProvider
     */
    public function testIterableConflict(array $args): void
    {
        $parameters = parameters(
            test: arrayp(
                one: int(max: 1),
                two: int(),
            )
        );
        $this->expectException(InvalidArgumentException::class);
        new Arguments($parameters, $args);
    }

    /**
     * @dataProvider iterableArrayPropertyProvider
     */
    public function testIterableArrayProperty(array $args): void
    {
        $parameters = parameters(
            top: iterable(
                K: int(),
                V: string()
            )
        );
        $this->expectNotToPerformAssertions();
        new Arguments($parameters, $args);
    }

    /**
     * @dataProvider iterableArrayPropertyProvider
     */
    public function testIterableArrayPropertyConflict(array $args): void
    {
        $parameters = parameters(
            top: iterable(
                K: int(),
                V: string('/^one$/')
            )
        );
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument value provided');
        $this->expectExceptionMessage("doesn't match the regex `/^one$/`");
        new Arguments($parameters, $args);
    }

    /**
     * @dataProvider iterableArrayNestedPropertyProvider
     */
    public function testIterableArrayNestedProperty(array $args): void
    {
        $parameters = parameters(
            nested: iterable(
                K: int(),
                V: iterable(
                    K: string(),
                    V: int()
                )
            )
        );
        $this->expectNotToPerformAssertions();
        new Arguments($parameters, $args);
    }

    /**
     * @dataProvider iterableArrayNestedPropertyProvider
     */
    public function testIterableArrayNestedPropertyConflict(array $args): void
    {
        $parameters = parameters(
            nested: iterable(
                K: int(),
                V: iterable(
                    K: string(),
                    V: string()
                )
            )
        );
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be of type Stringable|string, int given');
        new Arguments($parameters, $args);
    }

    /**
     * @dataProvider iterableArrayProvider
     */
    public function testIterableArray(array $args): void
    {
        $parameter = iterable(
            V: string(),
            K: string()
        );
        $this->expectNotToPerformAssertions();
        $parameter($args);
    }

    /**
     * @dataProvider iterableArrayProvider
     */
    public function testIterableArrayConflict(array $args): void
    {
        $parameter = iterable(
            V: int(),
            K: string()
        );
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/^\[_V \*iterable\]\:.*/');
        $parameter($args);
    }
}
