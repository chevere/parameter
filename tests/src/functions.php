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

use Chevere\Parameter\Attributes\PArray;
use Chevere\Parameter\Attributes\PBool;
use Chevere\Parameter\Attributes\PEnum;
use Chevere\Parameter\Attributes\PInt;
use Chevere\Parameter\Attributes\PIterable;
use Chevere\Parameter\Attributes\PReturn;
use Chevere\Parameter\Attributes\PString;
use SensitiveParameter;
use function Chevere\Parameter\Attributes\arrayArguments;
use function Chevere\Parameter\Attributes\assertArguments;
use function Chevere\Parameter\Attributes\assertReturn;
use function Chevere\Parameter\Attributes\PArray;
use function PHPUnit\Framework\assertSame;

#[PReturn(
    new PBool()
)]
function usesAttr(
    #[PArray(
        id: new PInt(min: 1),
        role: new PArray(
            mask: new PInt(min: 64),
            name: new PString(),
            tenants: new PIterable(
                new PInt(min: 1, max: 5)
            )
        ),
    )]
    array $spooky
): bool {
    assertArguments();
    assertArguments('spooky');
    PArray('spooky')($spooky);
    assertSame(
        $spooky['id'],
        arrayArguments('spooky')->required('id')->int()
    );

    return assertReturn(true);
}

function noUsesAttr(
    array $spooky
): bool {
    assertArguments('spooky');

    return assertReturn(true);
}

function withDefaultError(
    #[PInt(min: 2)]
    int $int = 1
): void {
}

#[PReturn(
    new PInt(min: 100, max: 200)
)]
function validates(
    #[PInt(min: 1, max: 100)]
    int $base,
    #[PInt(min: 1, max: 5)]
    int $times = 1,
    string $name = '',
): int {
    return $base * $times;
}

function usesSensitiveParameterAttr(
    #[SensitiveParameter]
    #[PInt(min: 1000)]
    int $code,
    #[SensitiveParameter]
    #[PEnum('super', 'safe')]
    string $password
): void {
    assertArguments();
}
