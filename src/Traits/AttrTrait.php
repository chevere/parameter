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

trait AttrTrait
{
    public function withIsSensitive(bool $isSensitive = true): static
    {
        $new = clone $this;
        // @phpstan-ignore-next-line
        $new->parameter = $new->parameter->withIsSensitive($isSensitive);

        return $new;
    }
}
