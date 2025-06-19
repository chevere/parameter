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

    public function __construct(
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
    public function type(): TypeInterface
    {
        // @phpstan-ignore-next-line
        return $this->type ??= new Type($this->typeName());
    }

    public function description(): string
    {
        return $this->description;
    }

    public function withDescription(string $description): static
    {
        $new = clone $this;
        $new->description = $description;

        return $new;
    }

    public function withIsSensitive(bool $isSensitive = true): static
    {
        $new = clone $this;
        $new->isSensitive = $isSensitive;

        return $new;
    }

    public function isSensitive(): bool
    {
        return $this->isSensitive;
    }

    abstract private function typeName(): string;
}
