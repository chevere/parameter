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

namespace Chevere\Tests;

use Chevere\Parameter\Arguments;
use PHPUnit\Framework\TestCase;
use function Chevere\Parameter\parameters;
use function Chevere\Parameter\string;

final class ArgumentsTraitTest extends TestCase
{
    public function testManyExtraArgumentsExceedingParameterCount(): void
    {
        $parameters = parameters(
            a: string(),
            b: string(),
            c: string(),
        );
        $input = [
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
            'd' => 'D', // extra
            'e' => 'E', // extra
            'f' => 'F', // extra
        ];
        $arguments = new Arguments($parameters, $input);
        $this->assertSame([
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
        ], $arguments->toArray());
    }

    public function testNumericIndexAfterAllParametersIsExcluded(): void
    {
        $parameters = parameters(
            a: string(),
            b: string(),
            c: string(),
        );
        // Provide named parameters first so $count reaches parameters->count()
        // then supply a numeric key that would map to an existing parameter index.
        $input = [
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
            0 => 'X-duplicate',
        ];
        $arguments = new Arguments($parameters, $input);
        // With the correct operator (`>=`) the numeric entry must be removed.
        $this->assertSame([
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
        ], $arguments->toArray());
    }
}
