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

namespace BlitzPHP\Tasks\Commands;

use BlitzPHP\Cli\Console\Command;

/**
 * Fonctionnalité de base pour activer/désactiver.
 */
abstract class TaskCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected $group = 'Tasks';

    /**
     * emplacement pour sauvegarder.
     */
    protected string $path = STORAGE_PATH . 'tasks';
}
