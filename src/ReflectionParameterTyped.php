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

use Chevere\Parameter\Interfaces\ParameterInterface;
use Chevere\Parameter\Interfaces\ReflectionParameterTypedInterface;
use LogicException;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Throwable;
use TypeError;
use function Chevere\Message\message;

final class ReflectionParameterTyped implements ReflectionParameterTypedInterface
{
    private ?ReflectionNamedType $type;

    private ParameterInterface $parameter;

    public function __construct(
        private ReflectionParameter $reflection
    ) {
        $this->type = $this->getType();
        $parameter = toParameter($this->type?->getName() ?? 'mixed');

        try {
            $attribute = reflectedParameterAttribute($reflection);
        } catch (Throwable) {
            // do nothing
        }
        if (isset($attribute, $this->type)) {
            $typeHint = $this->type->getName();
            $attrHint = $attribute->parameter()->type()->typeHinting();
            if ($typeHint !== $attrHint) {
                throw new TypeError(
                    (string) message(
                        'Parameter %name% of type %type% is not compatible with %attr% attribute',
                        name: $reflection->getName(),
                        type: $typeHint,
                        attr: $attribute->parameter()::class
                    )
                );
            }
            $parameter = $attribute->parameter();
        }
        if ($this->reflection->isDefaultValueAvailable()
            && method_exists($parameter, 'withDefault')
            && $this->reflection->getDefaultValue() !== null
        ) {
            /** @var ParameterInterface $parameter */
            $parameter = $parameter
                ->withDefault(
                    $this->reflection->getDefaultValue()
                );
        }
        $this->parameter = $parameter;
    }

    public function parameter(): ParameterInterface
    {
        return $this->parameter;
    }

    private function getType(): ?ReflectionNamedType
    {
        $reflection = $this->reflection->getType();
        if ($reflection === null) {
            return null;
        }
        if ($reflection instanceof ReflectionNamedType) {
            return $reflection;
        }
        $name = '$' . $this->reflection->getName();
        $type = $this->getReflectionType($reflection);

        throw new LogicException(
            (string) message(
                'Parameter %name% of type %type% is not supported',
                name: $name,
                type: $type
            )
        );
    }

    /**
     * @infection-ignore-all
     */
    private function getReflectionType(mixed $reflectionType): string
    {
        return match (true) {
            $reflectionType instanceof ReflectionUnionType => 'union',
            $reflectionType instanceof ReflectionIntersectionType => 'intersection',
            default => 'unknown',
        };
    }
}
