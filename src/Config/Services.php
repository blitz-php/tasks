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

namespace BlitzPHP\Tasks\Config;

use BlitzPHP\Container\Services as BaseServices;
use BlitzPHP\Tasks\CronExpression;
use BlitzPHP\Tasks\Scheduler;

class Services extends BaseServices
{
    /**
     * Renvoie le planificateur de tâches
     */
    public static function scheduler(bool $shared = true): Scheduler
    {
        if (true === $shared && isset(static::$instances[Scheduler::class])) {
            return static::$instances[Scheduler::class];
        }

        return static::$instances[Scheduler::class] = new Scheduler();
    }

    /**
     * Renvoie la classe CronExpression.
     */
    public static function cronExpression(bool $shared = true): CronExpression
    {
        if (true === $shared && isset(static::$instances[CronExpression::class])) {
            return static::$instances[CronExpression::class];
        }

        return static::$instances[CronExpression::class] = new CronExpression();
    }
}
