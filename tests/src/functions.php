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
use Chevere\Parameter\Attributes\IntAttr;
use Chevere\Parameter\Attributes\IterableAttr;
use Chevere\Parameter\Attributes\ReturnAttr;
use Chevere\Parameter\Attributes\StringAttr;
use function Chevere\Parameter\Attributes\arrayArguments;
use function Chevere\Parameter\Attributes\arrayAttr;
use function Chevere\Parameter\Attributes\returnAttr;
use function Chevere\Parameter\Attributes\valid;
use function PHPUnit\Framework\assertSame;

#[ReturnAttr(
    new BoolAttr()
)]
function usesAttr(
    #[ArrayAttr(
        id: new IntAttr(min: 1),
        role: new ArrayAttr(
            mask: new IntAttr(min: 64),
            name: new StringAttr(),
            tenants: new IterableAttr(
                new IntAttr(min: 1, max: 5)
            )
        ),
    )]
    array $spooky
): bool {
    valid('spooky');
    assertSame(
        $spooky['id'],
        arrayArguments('spooky')->required('id')->int()
    );

    return returnAttr()(true);
}

function noUsesAttr(
    array $spooky
): bool {
    valid('spooky');

    return returnAttr()(true);
}
