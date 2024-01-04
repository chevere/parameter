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

use Throwable;
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
use function PHPUnit\Framework\assertSame;

final class NoUsesAttr
{
    public function __construct(
        string $name = 'Test',
        int $age = 12,
        array $cols = [
            'id' => 1,
        ],
        iterable $tags = ['Chevere', 'Chevere', 'Chevere', 'Uh'],
        bool $flag = false,
        float $amount = 0,
        mixed $null = null,
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
        try {
            $name = stringAttr('name')($name);
        } catch (Throwable $e) {
            assertSame('No attribute for parameter `name`', $e->getMessage());
        }
        // $age = intAttr('age')($age);
        // $cols = arrayAttr('cols')($cols);
        // $id = arrayArguments('cols')->required('id')->int();
        // $tags = iteratorAttr('tags')($tags);
        // $flag = boolAttr('flag')($flag);
        // $amount = floatAttr('amount')($amount);
        // $null = nullAttr('null')($null);
        // $enum = enumAttr('enum')($enum);
        // Validate return attr
        // returnAttr()($id);
    }

    public function run(): int
    {
        return returnAttr()(1);
    }

    public static function return(): int
    {
        return 120;
    }
}
