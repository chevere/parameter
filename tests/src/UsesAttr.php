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
use Chevere\Parameter\Attributes\_callable;
use Chevere\Parameter\Attributes\_enum;
use Chevere\Parameter\Attributes\_float;
use Chevere\Parameter\Attributes\_int;
use Chevere\Parameter\Attributes\_iterable;
use Chevere\Parameter\Attributes\_null;
use Chevere\Parameter\Attributes\_return;
use Chevere\Parameter\Attributes\_string;
use Chevere\Parameter\Attributes\_union;
use Chevere\Parameter\Interfaces\ParameterInterface;
use function Chevere\Parameter\Attributes\arrayArguments;
use function Chevere\Parameter\Attributes\assertArguments;
use function Chevere\Parameter\Attributes\assertReturn;
use function Chevere\Parameter\int;

final class UsesAttr
{
    #[_return(
        new _callable(__CLASS__ . '::return')
    )]
    public function __construct(
        #[_string('/^[A-Za-z]+$/')]
        string $name = 'Test',
        #[_int(min: 1, max: 100)]
        int $age = 12,
        #[_arrayp(
            id: new _callable(__CLASS__ . '::callable'),
        )]
        array $cols = [
            'id' => 1,
        ],
        #[_iterable(
            new _string('/^[A-Za-z]+$/'),
        )]
        iterable $tags = ['Chevere', 'Chevere', 'Chevere', 'Uh'],
        #[_bool()]
        bool $flag = false,
        #[_float(min: 0)]
        float $amount = 0,
        #[_null()]
        mixed $null = null,
        #[_enum('test', 'value')]
        string $enum = 'value',
        #[_union(
            new _int(min: 1),
            new _string('/^[A-Za-z]+$/'),
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
        $id = arrayArguments('cols')->required('id')->int();
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

    #[_return(
        new _int(min: 0, max: 5)
    )]
    public function run(int $int): int
    {
        return assertReturn($int);
    }
}
