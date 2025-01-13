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

use BlitzPHP\Tasks\Exceptions\TasksException;
use BlitzPHP\Tasks\Task;

/**
 * Classe de test qui empêche l'appel des actions.
 *
 * @credit <a href="https://tasks.codeigniter.com">CodeIgniter4 - CodeIgniter\Tasks\Test\MockTask</a>
 */
class MockTask extends Task
{
    /**
     * Prétend exécuter l'action de cette tâche.
     *
     * @return array
     *
     * @throws TasksException
     */
    public function run()
    {
        $method = 'run' . ucfirst($this->type);
        if (! method_exists($this, $method)) {
            throw TasksException::invalidTaskType($this->type);
        }

        $_SESSION['tasks_cache'] = [$this->type, $this->action];

        return [
            'command' => 'success',
            'shell'   => [],
            'closure' => 42,
            'event'   => true,
            'url'     => 'body',
        ][$this->type];
    }
}
