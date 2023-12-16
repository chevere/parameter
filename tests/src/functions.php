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

use Chevere\Parameter\Attributes\ArrayAttr;
use Chevere\Parameter\Attributes\BoolAttr;
use Chevere\Parameter\Attributes\GenericAttr;
use Chevere\Parameter\Attributes\IntAttr;
use Chevere\Parameter\Attributes\ReturnAttr;
use Chevere\Parameter\Attributes\StringAttr;
use function Chevere\Parameter\Attributes\valid;

#[ReturnAttr(
    new BoolAttr()
)]
function myArray(
    #[ArrayAttr(
        id: new IntAttr(min: 1),
        role: new ArrayAttr(
            mask: new IntAttr(min: 64),
            name: new StringAttr(),
            tenants: new GenericAttr(
                new IntAttr(min: 1, max: 5)
            )
        ),
    )]
    array $spooky
): bool {
    valid('spooky');

    return true;
}
