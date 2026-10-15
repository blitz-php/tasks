<?php

/**
 * This file is part of BlitzPHP Tasks.
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Commands;

use BlitzPHP\Cli\Console\Command;

/**
 * @internal
 */
final class TasksTest extends Command
{
    protected string $group       = 'Testing';
    protected string $name        = 'tasks:test';
    protected string $description = 'Tests Tasks';

    public function handle()
    {
        $this->write('La commande peut produire du texte.');
    }
}
