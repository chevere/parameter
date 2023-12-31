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

#[\AllowDynamicProperties]
final class ArrayAccessMixed extends ArrayAccess
{
    public function __construct(
        public string $string,
        protected int $int,
        private bool $bool,
    ) {
        $this->dynamic = '123abc';
    }
}
