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

use Chevere\Parameter\Exceptions\ParameterException;
use Chevere\Parameter\Interfaces\ParameterAttributeInterface;
use Throwable;
use function Chevere\Parameter\Attributes\arrayArguments;
use function Chevere\Parameter\Attributes\assertArguments;
use function Chevere\Parameter\Attributes\assertReturn;
use function Chevere\Parameter\Attributes\PString;
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
        assertArguments();
        // Pick validation
        assertArguments('name');
        assertArguments('age');
        assertArguments('cols');
        assertArguments('tags');
        assertArguments('flag');
        assertArguments('amount');

        try {
            assertArguments('404');
        } catch (ParameterException $e) {
            assertSame(
                'Parameter `404` not found',
                $e->getMessage()
            );
        }

        // Get attribute, validate and return
        try {
            $name = PString('name')($name);
        } catch (Throwable $e) {
            assertSame(
                'No `' . ParameterAttributeInterface::class . '` attribute for parameter `name`',
                $e->getMessage()
            );
        }
        // $age = PInt('age')($age);
        // $cols = PArray('cols')($cols);
        // $id = arrayArguments('cols')->required('id')->int();
        // $tags = PIterator('tags')($tags);
        // $flag = PBool('flag')($flag);
        // $amount = PFloat('amount')($amount);
        // $null = PNull('null')($null);
        // $enum = PEnum('enum')($enum);
        // Assert return attr
        // assertReturn($id);
    }

    public function run(): int
    {
        return assertReturn(1);
    }

    public static function return(): int
    {
        return 120;
    }
}
