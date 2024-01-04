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
use Chevere\Parameter\Attributes\CallableAttr;
use Chevere\Parameter\Attributes\EnumAttr;
use Chevere\Parameter\Attributes\FloatAttr;
use Chevere\Parameter\Attributes\IntAttr;
use Chevere\Parameter\Attributes\IterableAttr;
use Chevere\Parameter\Attributes\NullAttr;
use Chevere\Parameter\Attributes\ReturnAttr;
use Chevere\Parameter\Attributes\StringAttr;
use Chevere\Parameter\Interfaces\ParameterInterface;
use function Chevere\Parameter\Attributes\arrayArguments;
use function Chevere\Parameter\Attributes\arrayAttr;
use function Chevere\Parameter\Attributes\boolAttr;
use function Chevere\Parameter\Attributes\enumAttr;
use function Chevere\Parameter\Attributes\floatAttr;
use function Chevere\Parameter\Attributes\intAttr;
use function Chevere\Parameter\Attributes\iteratorAttr;
use function Chevere\Parameter\Attributes\nullAttr;
use function Chevere\Parameter\Attributes\returnAttr;
use function Chevere\Parameter\Attributes\stringAttr;
use function Chevere\Parameter\Attributes\valid;
use function Chevere\Parameter\int;

final class UsesAttr
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
        array $cols = [
            'id' => 1,
        ],
        #[IterableAttr(
            new StringAttr('/^[A-Za-z]+$/'),
        )]
        iterable $tags = ['Chevere', 'Chevere', 'Chevere', 'Uh'],
        #[BoolAttr()]
        bool $flag = false,
        #[FloatAttr(min: 0)]
        float $amount = 0,
        #[NullAttr()]
        mixed $null = null,
        #[EnumAttr('test', 'value')]
        string $enum = 'value',
    ) {
        // Validate all
        valid();
        // Pick validation
        valid('name');
        valid('age');
        valid('cols');
        valid('tags');
        valid('flag');
        valid('amount');
        // Get attribute, validate and return
        $name = stringAttr('name')($name);
        $age = intAttr('age')($age);
        $cols = arrayAttr('cols')($cols);
        $id = arrayArguments('cols')->required('id')->int();
        $tags = iteratorAttr('tags')($tags);
        $flag = boolAttr('flag')($flag);
        $amount = floatAttr('amount')($amount);
        $null = nullAttr('null')($null);
        $enum = enumAttr('enum')($enum);
        // Validate return attr
        returnAttr()($id);
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
        return returnAttr()($int);
    }
}
