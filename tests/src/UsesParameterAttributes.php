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
use Chevere\Parameter\Attributes\GenericAttr;
use Chevere\Parameter\Attributes\IntAttr;
use Chevere\Parameter\Attributes\ReturnAttr;
use Chevere\Parameter\Attributes\StringAttr;
use function Chevere\Parameter\Attributes\arrayArguments;
use function Chevere\Parameter\Attributes\arrayAttr;
use function Chevere\Parameter\Attributes\genericAttr;
use function Chevere\Parameter\Attributes\intAttr;
use function Chevere\Parameter\Attributes\stringAttr;
use function Chevere\Parameter\Attributes\validate;
use function Chevere\Parameter\returnAttr;

final class UsesParameterAttributes
{
    public function __construct(
        #[StringAttr('/^[A-Za-z]+$/')]
        string $name = '',
        #[IntAttr(min: 1, max: 100)]
        int $age = 12,
        #[ArrayAttr(
            id: new IntAttr(min: 1),
        )]
        array $cols = [],
        #[GenericAttr(
            new StringAttr('/^[A-Za-z]+$/'),
        )]
        iterable $tags = [],
    ) {
        validate('name');
        validate('age');
        validate('cols');
        validate('tags');
        $name = stringAttr('name')($name);
        $age = intAttr('age')($age);
        $cols = arrayAttr('cols')($cols);
        $id = arrayArguments('cols')->required('id')->int();
        $tags = genericAttr('tags')($tags);
    }

    #[ReturnAttr(
        new IntAttr(min: 0, max: 5)
    )]
    public function run(int $int): int
    {
        return returnAttr($int);
    }
}
