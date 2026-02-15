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

namespace Chevere\Parameter;

use Chevere\Parameter\Interfaces\TypeInterface;
use InvalidArgumentException;
use function Chevere\Message\message;

final class Type implements TypeInterface
{
    public const CLASS_TYPES = [
        self::PRIMITIVE_CLASS_NAME,
        self::PRIMITIVE_INTERFACE_NAME,
    ];

    private string $primitive = '';

    private string $typeHinting = '';

    /**
     * @param string $type A debug type
     */
    public function __construct(
        private string $type,
        string ...$more
    ) {
        $this->setPrimitive();
        $this->assertHasPrimitive();
        $this->typeHinting = $this->primitive;
        if (in_array($this->primitive, self::CLASS_TYPES, true)) {
            $this->typeHinting = $this->type;
        }
        if ($type === self::UNION) {
            $this->typeHinting = implode('|', array_map(
                static fn (string $type) => (new Type($type))->typeHinting(),
                $more
            ));
        }
    }

    public function primitive(): string
    {
        return $this->primitive;
    }

    public function typeHinting(): string
    {
        return $this->typeHinting;
    }

    public function isScalar(): bool
    {
        return in_array($this->primitive, ['bool', 'int', 'float', 'string'], true);
    }

    private function setPrimitive(): void
    {
        $this->primitive = match (true) {
            in_array($this->type, self::TYPE_ARGUMENTS, true) => $this->type,
            class_exists($this->type) => self::PRIMITIVE_CLASS_NAME,
            interface_exists($this->type) => self::PRIMITIVE_INTERFACE_NAME,
            default => '',
        };
    }

    private function assertHasPrimitive(): void
    {
        if ($this->primitive === '') {
            throw new InvalidArgumentException(
                (string) message(
                    "Type `%type%` doesn't exists",
                    type: $this->type
                )
            );
        }
    }
}
