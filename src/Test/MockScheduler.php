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

namespace BlitzPHP\Tasks\Test;

use BlitzPHP\Tasks\Scheduler;
use BlitzPHP\Tasks\Task;

/**
 * Une classe wrapper pour tester le renvoi de MockTasks au lieu de Tasks.
 *
 * @credit <a href="https://tasks.codeigniter.com">CodeIgniter4 - CodeIgniter\Tasks\Test\MockScheduler</a>
 */
class MockScheduler extends Scheduler
{
    /**
     * @return MockTask
     */
    protected function createTask(string $type, mixed $action): Task
    {
        $task          = new MockTask($type, $action);
        $this->tasks[] = $task;

        return $task;
    }
}
