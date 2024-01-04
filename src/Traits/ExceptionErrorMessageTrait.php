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

namespace Chevere\Parameter\Traits;

use Throwable;

trait ExceptionErrorMessageTrait
{
    private function getExceptionMessage(
        Throwable $e,
        string $needle = '::__invoke(): ',
    ): string {
        $message = $e->getMessage();
        $strstr = strstr($message, $needle, false);
        if (! is_string($strstr)) {
            $strstr = $message; // @codeCoverageIgnore
        } else {
            $strstr = substr($strstr, strlen($needle));
        }
        $calledIn = strpos($strstr, ', called in');

        return $calledIn
            ? substr($strstr, 0, $calledIn)
            : $strstr;
    }
}
