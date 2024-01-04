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

namespace Chevere\Parameter\Exceptions;

use Exception;
use Throwable;

/**
 * Exception thrown when validating return.
 */
final class ReturnException extends Exception
{
    public function __construct(string $message, Throwable $previous, string $file, int $line)
    {
        parent::__construct(message: $message, previous: $previous);
        $this->file = $file;
        $this->line = $line;
    }
}
