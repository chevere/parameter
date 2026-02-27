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
use ReflectionProperty;
use ReflectionUnionType;
use SensitiveParameter;
use Throwable;
use TypeError;
use function Chevere\Message\message;

final class ReflectionParameterTyped implements ReflectionParameterTypedInterface
{
    private ReflectionUnionType|ReflectionNamedType|null $type;

    private ParameterInterface $parameter;

    private bool $hasDefault;

    private mixed $defaultValue;

    public function __construct(
        private ReflectionParameter|ReflectionProperty $reflection
    ) {
        $this->type = $this->getType();
        $parameter = $this->getParameter();

        try {
            $attribute = reflectedParameterAttribute($reflection);
        } catch (Throwable) {
            // do nothing
        }
        if (isset($attribute, $this->type)) {
            $typeHint = $this->getTypeHint($this->type);
            $attrHint = $attribute->parameter()->type()->typeHinting();
            $typeHintParts = explode('|', $typeHint);
            $attrHintParts = explode('|', $attrHint);
            sort($typeHintParts);
            sort($attrHintParts);
            $typeHint = implode('|', $typeHintParts);
            $attrHint = implode('|', $attrHintParts);
            if ($typeHint !== $attrHint) {
                throw new TypeError(
                    (string) message(
                        'Parameter $%name% of type %type% is not compatible with %attr% attribute',
                        name: $reflection->getName(),
                        type: $typeHint,
                        attr: $attribute->parameter()::class
                    )
                );
            }
            $parameter = $attribute->parameter();
        }
        $this->handleDefaultValue();
        if ($this->hasDefault && $this->defaultValue !== null) {
            /** @var ParameterInterface $parameter */
            $parameter = $parameter->withDefault($this->defaultValue);
        }
        $isSensitive = $reflection->getAttributes(SensitiveParameter::class) !== [];
        if ($isSensitive) {
            $parameter = $parameter->withIsSensitive($isSensitive);
        }
        $this->parameter = $parameter;
    }

    public function parameter(): ParameterInterface
    {
        return $this->parameter;
    }

    private function getParameter(): ParameterInterface
    {
        if ($this->type instanceof ReflectionUnionType) {
            $types = [];
            foreach ($this->type->getTypes() as $type) {
                if ($type instanceof ReflectionIntersectionType) {
                    continue;
                }
                $types[] = $type->getName();
            }

            return toUnionParameter(...$types);
        } elseif ($this->type !== null) {
            if ($this->type->allowsNull() && $this->type->__toString() !== 'mixed') {
                return toUnionParameter(
                    $this->getTypeHint($this->type),
                    'null'
                );
            }

            return toParameter($this->getTypeHint($this->type));
        }

        return toParameter('mixed');
    }

    private function getType(): ReflectionNamedType|ReflectionUnionType|null
    {
        $reflection = $this->reflection->getType();
        if ($reflection === null) {
            return null;
        }
        if ($reflection instanceof ReflectionNamedType || $reflection instanceof ReflectionUnionType) {
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

    private function getTypeHint(object $reflection): string
    {
        if (method_exists($reflection, 'getName')) {
            return $reflection->getName();
        }
        if ($reflection instanceof ReflectionUnionType) {
            $types = [];
            foreach ($reflection->getTypes() as $type) {
                $types[] = $this->getTypeHint($type);
            }

            return implode('|', $types);
        }

        return 'mixed'; // @codeCoverageIgnore
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

    private function handleDefaultValue(): void
    {
        $this->hasDefault = false;
        $this->defaultValue = null;
        if (! ($this->reflection instanceof ReflectionProperty)) {
            $this->hasDefault = $this->reflection->isDefaultValueAvailable();
            $this->defaultValue = $this->hasDefault
                ? $this->reflection->getDefaultValue()
                : $this->defaultValue;

            return;
        }
        if (! $this->reflection->isPromoted()) {
            $this->hasDefault = $this->reflection->hasDefaultValue();
            $this->defaultValue = $this->hasDefault
                ? $this->reflection->getDefaultValue()
                : $this->defaultValue;

            return;
        }
        $constructor = $this->reflection->getDeclaringClass()->getConstructor();
        if ($constructor === null) {
            return;
        }
        foreach ($constructor->getParameters() as $reflectionParameter) {
            if ($reflectionParameter->getName() === $this->reflection->getName()) {
                if ($reflectionParameter->isDefaultValueAvailable()) {
                    $this->hasDefault = true;
                    $this->defaultValue = $reflectionParameter->getDefaultValue();
                }

                break;
            }
        }
    }
}
