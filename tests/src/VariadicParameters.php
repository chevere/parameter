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

namespace Chevere\Tests\src;

final class VariadicParameters
{
    public function main(
        string $_task,
        int $_priority = 0,
        int $_maxRetries = 3,
        string ...$argument,
    ): void {
    }
}
