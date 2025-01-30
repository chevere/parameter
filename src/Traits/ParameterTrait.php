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

namespace Chevere\Parameter\Traits;

use Chevere\Parameter\Interfaces\TypeInterface;
use Chevere\Parameter\Type;

trait ParameterTrait
{
    private TypeInterface $type;

    private bool $isSensitive = false;

    final public function __construct(
        private string $description = '',
        bool $isSensitive = false,
    ) {
        $this->setUp();
        $this->type = $this->type();
        $this->isSensitive = $isSensitive;
    }

    public function setUp(): void
    {
        // Nothing to do
    }

    /**
     * @infection-ignore-all
     */
    final public function type(): TypeInterface
    {
        return $this->type ??= new Type($this->typeName());
    }

    final public function description(): string
    {
        return $this->description;
    }

    final public function withDescription(string $description): static
    {
        $new = clone $this;
        $new->description = $description;

        return $new;
    }

    final public function withIsSensitive(bool $isSensitive): static
    {
        $new = clone $this;
        $new->isSensitive = $isSensitive;

        return $new;
    }

    final public function isSensitive(): bool
    {
        return $this->isSensitive;
    }

    abstract private function typeName(): string;
}
