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

namespace BlitzPHP\Tasks;

use Closure;

/**
 * @credit <a href="https://tasks.codeigniter.com">CodeIgniter4 - CodeIgniter\Tasks\Scheduler</a>
 */
class Scheduler
{
    /**
     * @var list<Task>
     */
    protected array $tasks = [];

    /**
     * Renvoie les tâches créées.
     *
     * @return list<Task>
     */
    public function getTasks(): array
    {
        return $this->tasks;
    }

    /**
     * Planifie l'execution d'une closure.
     */
    public function call(callable $func): Task
    {
        return $this->createTask('closure', $func);
    }

    /**
     * Planifie l'execution d'une commande.
     */
    public function command(string $command, array $parameters = []): Task
    {
        return $this->createTask('command', $command, $parameters);
    }

    /**
     * Planifie l'exécution d'une commande systeme
     */
    public function shell(string $command): Task
    {
        return $this->createTask('shell', $command);
    }

    /**
     * Planifie le declenchement d'un evenement.
     *
     * @param string $name Nom de l'evenement a declencher
     */
    public function event(string $name): Task
    {
        return $this->createTask('event', $name);
    }

    /**
     * Planifie une commande cURL vers une URL distante
     */
    public function url(string $url): Task
    {
        return $this->createTask('url', $url);
    }

    /**
     * @param mixed $action
     */
    protected function createTask(string $type, $action, array $parameters = []): Task
    {
        $task          = new Task($type, $action, $parameters);
        $this->tasks[] = $task;

        return $task;
    }
}
