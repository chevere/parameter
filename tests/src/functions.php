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

use Chevere\Parameter\Attributes\_arrayp;
use Chevere\Parameter\Attributes\_bool;
use Chevere\Parameter\Attributes\_enum;
use Chevere\Parameter\Attributes\_int;
use Chevere\Parameter\Attributes\_iterable;
use Chevere\Parameter\Attributes\_return;
use Chevere\Parameter\Attributes\_string;
use SensitiveParameter;
use function Chevere\Parameter\Attributes\arrayArguments;
use function Chevere\Parameter\Attributes\assertArguments;
use function Chevere\Parameter\Attributes\assertReturn;
use function PHPUnit\Framework\assertSame;

#[_return(
    new _bool()
)]
function usesAttr(
    #[_arrayp(
        id: new _int(min: 1),
        role: new _arrayp(
            mask: new _int(min: 64),
            name: new _string(),
            tenants: new _iterable(
                new _int(min: 1, max: 5)
            )
        ),
    )]
    array $spooky
): bool {
    assertArguments();
    assertArguments('spooky');
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

#[_return(
    new _int(min: 100, max: 200)
)]
function validates(
    #[_int(min: 1, max: 100)]
    int $base,
    #[_int(min: 1, max: 5)]
    int $times = 1,
    string $name = '',
): int {
    return $base * $times;
}

function usesSensitiveParameterAttr(
    #[SensitiveParameter]
    #[_int(min: 1000)]
    int $code,
    #[SensitiveParameter]
    #[_enum('super', 'safe')]
    string $password
): void {
    assertArguments();
}
