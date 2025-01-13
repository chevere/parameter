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

use Chevere\Parameter\Attributes\BoolAttr;
use Chevere\Parameter\Attributes\NullAttr;
use Chevere\Parameter\Attributes\StringAttr;
use Chevere\Parameter\Attributes\UnionAttr;
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
        #[StringAttr('/^[a-z]+$/', description: 'A string')]
        string $string = 'default'
    ) {
    }

    public function useUnion(string|int $union)
    {
    }

    public function useMixed(mixed $mixed)
    {
    }

    public function useWrongUnionAttr(
        #[UnionAttr(
            new BoolAttr(),
            new NullAttr(),
        )]
        string|int $union
    ) {
    }

    public function useIntersection(stdClass&Depends $intersection)
    {
    }

    public function useInvalidAttribute(
        #[StringAttr()]
        int $int
    ) {
    }
}
