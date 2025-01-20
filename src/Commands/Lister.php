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

use BlitzPHP\Tasks\CronExpression;
use BlitzPHP\Tasks\Scheduler;
use BlitzPHP\Utilities\Date;

/**
 * Répertorie les tâches actuellement planifiées.
 */
class Lister extends TaskCommand
{
    /**
     * {@inheritDoc}
     */
    protected $name = 'tasks:list';

    /**
     * {@inheritDoc}
     */
    protected $description = 'Répertorie les tâches actuellement configurées pour être exécutées.';

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    public function execute(array $params)
    {
        helper('preference');

        if (parametre('tasks.enabled') === false) {
            $this->writer->error('WARNING: L\'exécution de la tâche est actuellement désactivée.', true);
            $this->writer->write("Pour réactiver les tâches, exécutez\u{a0}: `tasks:enable`", true);
            $this->newLine();
        }

        /** @var Scheduler */
        $scheduler = service('scheduler');

        call_user_func(config('tasks.init'), $scheduler);

        $tasks = [];

        foreach ($scheduler->getTasks() as $task) {
            /** @var CronExpression */
            $cron = service('cronExpression');

            $nextRun = $cron->nextRun($task->getExpression());
            $lastRun = $task->lastRun();

            $tasks[] = [
                'Nom'                 => $task->name ?: $task->getAction(),
                'Type'                => $task->getType(),
                'Schedule'            => $task->getExpression(),
                'Derniere exécution'  => $lastRun instanceof Date ? $lastRun->toDateTimeString() : $lastRun,
                'Prochaine exécution' => $nextRun,
                // 'runs_in'  => $nextRun->getDate(),
            ];
        }

        usort($tasks, static fn ($a, $b) => ($a['Prochaine exécution'] < $b['Prochaine exécution']) ? -1 : 1);

        $this->table($tasks);
    }
}
