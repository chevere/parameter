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
use Chevere\Parameter\Attributes\PCallable;
use Chevere\Parameter\Attributes\PEnum;
use Chevere\Parameter\Attributes\PFloat;
use Chevere\Parameter\Attributes\PInt;
use Chevere\Parameter\Attributes\PIterable;
use Chevere\Parameter\Attributes\PNull;
use Chevere\Parameter\Attributes\PReturn;
use Chevere\Parameter\Attributes\PString;
use Chevere\Parameter\Attributes\PUnion;
use Chevere\Parameter\Interfaces\ParameterInterface;
use function Chevere\Parameter\Attributes\arrayArguments;
use function Chevere\Parameter\Attributes\assertArguments;
use function Chevere\Parameter\Attributes\assertReturn;
use function Chevere\Parameter\Attributes\PArray;
use function Chevere\Parameter\Attributes\PBool;
use function Chevere\Parameter\Attributes\PEnum;
use function Chevere\Parameter\Attributes\PFloat;
use function Chevere\Parameter\Attributes\PInt;
use function Chevere\Parameter\Attributes\PIterator;
use function Chevere\Parameter\Attributes\PNull;
use function Chevere\Parameter\Attributes\PString;
use function Chevere\Parameter\Attributes\PUnion;
use function Chevere\Parameter\int;

final class UsesAttr
{
    #[PReturn(
        new PCallable(__CLASS__ . '::return')
    )]
    public function __construct(
        #[PString('/^[A-Za-z]+$/')]
        string $name = 'Test',
        #[PInt(min: 1, max: 100)]
        int $age = 12,
        #[PArray(
            id: new PCallable(__CLASS__ . '::callable'),
        )]
        array $cols = [
            'id' => 1,
        ],
        #[PIterable(
            new PString('/^[A-Za-z]+$/'),
        )]
        iterable $tags = ['Chevere', 'Chevere', 'Chevere', 'Uh'],
        #[PBool()]
        bool $flag = false,
        #[PFloat(min: 0)]
        float $amount = 0,
        #[PNull()]
        mixed $null = null,
        #[PEnum('test', 'value')]
        string $enum = 'value',
        #[PUnion(
            new PInt(min: 1),
            new PString('/^[A-Za-z]+$/'),
        )]
        int|string $union = 1,
    ) {
        // Validate all
        assertArguments();
        // Pick single
        assertArguments('name');
        assertArguments('age');
        assertArguments('cols');
        // Pick many
        assertArguments('tags', 'flag', 'amount');
        // Get attribute, validate and return
        $name = PString('name')($name);
        $age = PInt('age')($age);
        $cols = PArray('cols')($cols);
        $id = arrayArguments('cols')->required('id')->int();
        $tags = PIterator('tags')($tags);
        $flag = PBool('flag')($flag);
        $amount = PFloat('amount')($amount);
        $null = PNull('null')($null);
        $enum = PEnum('enum')($enum);
        $union = PUnion('union')($union);
        // Assert return
        assertReturn($id);
    }

    public static function callable(): ParameterInterface
    {
        return int(min: 1);
    }

    public static function return(): ParameterInterface
    {
        return int();
    }

    #[PReturn(
        new PInt(min: 0, max: 5)
    )]
    public function run(int $int): int
    {
        return assertReturn($int);
    }
}
