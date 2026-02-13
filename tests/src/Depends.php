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

use Chevere\Parameter\Attributes\_bool;
use Chevere\Parameter\Attributes\_null;
use Chevere\Parameter\Attributes\_string;
use Chevere\Parameter\Attributes\_union;
use stdClass;

final class Depends
{
    public function useNone($file = 'default')
    {
    }

    // public function useNull(null $file = null)
    // {
    // }

    public function useObject(stdClass $file)
    {
    }

    public function useString(
        #[_string('/^[a-z]+$/', description: 'A string')]
        string $string = 'default'
    ) {
    }

    public function useUnion(string|int $union)
    {
    }

    public function useMixed(mixed $mixed)
    {
    }

    public function useWrongTUnion(
        #[_union(
            new _bool(),
            new _null(),
        )]
        string|int $union
    ) {
    }

    public function useIntersection(stdClass&Depends $intersection)
    {
    }

    public function useInvalidAttribute(
        #[_string()]
        int $int
    ) {
    }
}
