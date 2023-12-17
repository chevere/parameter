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
use Chevere\Parameter\Attributes\CallableAttr;
use Chevere\Parameter\Attributes\GenericAttr;
use Chevere\Parameter\Attributes\IntAttr;
use Chevere\Parameter\Attributes\ReturnAttr;
use Chevere\Parameter\Attributes\StringAttr;
use Chevere\Parameter\Interfaces\ParameterInterface;
use function Chevere\Parameter\Attributes\arrayArguments;
use function Chevere\Parameter\Attributes\arrayAttr;
use function Chevere\Parameter\Attributes\genericAttr;
use function Chevere\Parameter\Attributes\intAttr;
use function Chevere\Parameter\Attributes\stringAttr;
use function Chevere\Parameter\Attributes\valid;
use function Chevere\Parameter\Attributes\validReturn;
use function Chevere\Parameter\int;

final class UsesParameterAttributes
{
    #[ReturnAttr(
        new CallableAttr(__CLASS__ . '::return')
    )]
    public function __construct(
        #[StringAttr('/^[A-Za-z]+$/')]
        string $name = 'Test',
        #[IntAttr(min: 1, max: 100)]
        int $age = 12,
        #[ArrayAttr(
            id: new CallableAttr(__CLASS__ . '::callable'),
        )]
        array $cols = [],
        #[GenericAttr(
            new StringAttr('/^[A-Za-z]+$/'),
        )]
        iterable $tags = [],
    ) {
        valid('name');
        valid('age');
        valid('cols');
        valid('tags');
        valid();
        $name = stringAttr('name')($name);
        $age = intAttr('age')($age);
        $cols = arrayAttr('cols')($cols);
        $id = arrayArguments('cols')->required('id')->int();
        $tags = genericAttr('tags')($tags);
        validReturn($id);
    }

    public static function callable(): ParameterInterface
    {
        return int(min: 1);
    }

    public static function return(): ParameterInterface
    {
        return int();
    }

    #[ReturnAttr(
        new IntAttr(min: 0, max: 5)
    )]
    public function run(int $int): int
    {
        return validReturn($int);
    }
}
