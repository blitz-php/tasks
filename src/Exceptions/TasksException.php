<?php

declare(strict_types=1);

/**
 * This file is part of BlitzPHP Tasks.
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace BlitzPHP\Tasks\Exceptions;

use RuntimeException;

/**
 * @credit <a href="https://tasks.codeigniter.com">CodeIgniter4 - CodeIgniter\Tasks\Exceptions\TasksException</a>
 */
final class TasksException extends RuntimeException
{
    public static function invalidTaskType(string $type): self
    {
        return new self(lang('Tasks.invalidTaskType', [$type]));
    }

    public static function invalidCronExpression(string $string): self
    {
        return new self(lang('Tasks.invalidCronExpression', [$string]));
    }
}
