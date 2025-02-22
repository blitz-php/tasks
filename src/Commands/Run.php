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

use BlitzPHP\Tasks\Task;
use BlitzPHP\Tasks\TaskRunner;

/**
 * Exécute les tâches en cours.
 */
class Run extends TaskCommand
{
    /**
     * {@inheritDoc}
     */
    protected $name = 'tasks:run';

    /**
     * {@inheritDoc}
     */
    protected $options = [
        '--task' => 'Exécuter une tâche spécifique par son alias.',
    ];

    /**
     * {@inheritDoc}
     */
    protected $description = 'Exécute des tâches en fonction de la planification, doit être configuré comme une tâche cron pour s\'exécuter toutes les minutes.';

    /**
     * {@inheritDoc}
     *
     * @return int
     */
    public function execute(array $params)
    {
        if (parametre('tasks.enabled') === false) {
            $this->writer->error('WARNING: L\'exécution de la tâche est actuellement désactivée.', true);
            $this->writer->write("Pour réactiver les tâches, exécutez\u{a0}: `tasks:enable`", true);
            $this->newLine();
        }

        $this->task('Exécution de tâches...')->eol();

        call_user_func(config('tasks.init'), $scheduler = service('scheduler'));

        $only = $this->option('task');

        $tasks = collect($scheduler->getTasks())
            ->filter(fn (Task $task) => $task->shouldRun())
            ->filter(fn (Task $task) => $only === null ? true : $task->name === $only);

        if ($tasks->isEmpty()) {
            $this->writer->error('Aucune tâche à exécuter.');

            return EXIT_ERROR;
        }

        $runner = new TaskRunner($scheduler);

        if ($only) {
            $runner->only([$only]);
        }

        $runner->run();

        $this->eol()->border();
        $this->writer->ok('Tâches en cours d\'exécution terminées');

        return EXIT_SUCCESS;
    }
}
