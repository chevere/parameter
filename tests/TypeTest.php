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

use Chevere\Parameter\Interfaces\TypeInterface;
use Chevere\Parameter\Type;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

final class TypeTest extends TestCase
{
    public function testInvalidArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Type('TypeSome');
    }

    #[DataProvider('typesProvider')]
    public function testTypes(
        string $primitive,
        array $constructorArgs,
        string $typeHinting,
        bool $isScalar
    ): void {
        $type = new Type(...$constructorArgs);

        $this->assertSame($primitive, $type->primitive());
        $this->assertSame($typeHinting, $type->typeHinting());
        $this->assertSame($isScalar, $type->isScalar());
    }

    public static function typesProvider(): array
    {
        return [
            'bool' => [Type::BOOL, ['bool'], 'bool', true],
            'int' => [Type::INT, ['int'], 'int', true],
            'float' => [Type::FLOAT, ['float'], 'float', true],
            'string' => [Type::STRING, ['string'], 'string', true],
            'array' => [Type::ARRAY, ['array'], 'array', false],
            'object' => [Type::OBJECT, ['object'], 'object', false],
            'className' => [Type::PRIMITIVE_CLASS_NAME, [stdClass::class], 'stdClass', false],
            'callable' => [Type::CALLABLE, ['callable'], 'callable', false],
            'iterable' => [Type::ITERABLE, ['iterable'], 'iterable', false],
            'null' => [Type::NULL, ['null'], 'null', false],
            'resource' => [Type::RESOURCE, ['resource'], 'resource', false],
            'union' => [Type::UNION, ['union', Type::INT, Type::STRING], 'int|string', false],
        ];
    }

    public function testClassName(): void
    {
        $type = new Type(Exception::class);
        $this->assertSame(Type::PRIMITIVE_CLASS_NAME, $type->primitive());
        $this->assertSame(Exception::class, $type->typeHinting());
        $this->assertFalse($type->isScalar());
    }

    public function testInterfaceName(): void
    {
        $type = new Type(TypeInterface::class);
        $this->assertSame(Type::PRIMITIVE_INTERFACE_NAME, $type->primitive());
        $this->assertSame(TypeInterface::class, $type->typeHinting());
    }
}
