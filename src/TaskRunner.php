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

use Ahc\Cli\Output\Writer;
use BlitzPHP\Utilities\Date;
use Throwable;

/**
 * @credit <a href="https://tasks.codeigniter.com">CodeIgniter4 - CodeIgniter\Tasks\TaskRunner</a>
 */
class TaskRunner
{
    protected Scheduler $scheduler;
    protected ?string $testTime = null;

    /**
     * Stocke les alias des tâches à exécuter
     * Si vide, toutes les tâches seront exécutées selon leur planification.
     */
    protected array $only = [];

	/**
     * Instance de la sortie de la console
     */
	protected static Writer $writter;

    public function __construct(?Scheduler $scheduler = null)
    {
        $this->scheduler = $scheduler ?? service('scheduler');
    }

    /**
     * Point d'entrée principal pour l'exécution des tâches au sein du système.
     * Il s'occupe également de la collecte des données de sortie et de l'envoi de notifications si nécessaire.
     */
    public function run()
    {
        $tasks = $this->scheduler->getTasks();

        if ($tasks === []) {
            return;
        }

        foreach ($tasks as $task) {
            // Si des tâches spécifiques ont été choisies, sauter l'exécution des tâches restantes.
            if (! empty($this->only) && ! in_array($task->name, $this->only, true)) {
                continue;
            }

            if (! $task->shouldRun($this->testTime) && empty($this->only)) {
                continue;
            }

            $error  = null;
            $start  = Date::now();
            $output = null;

            $this->cliWrite('Traitement: ' . ($task->name ?: 'Task'), 'green');

            try {
                $output = $task->run();

                $this->cliWrite('Exécuté: ' . ($task->name ?: 'Task'), 'cyan');
            } catch (Throwable $e) {
                $this->cliWrite('Échoué: ' . ($task->name ?: 'Task'), 'red');

                logger()->error($e->getMessage(), $e->getTrace());
                $error = $e;
            } finally {
                // Sauvegarde des informations sur les performances
                $taskLog = new TaskLog([
                    'task'     => $task,
                    'output'   => $output,
                    'runStart' => $start,
                    'runEnd'   => Date::now(),
                    'error'    => $error,
                ]);

                $this->updateLogs($taskLog);
            }
        }
    }

    /**
     * Spécifier les tâches à exécuter
     */
    public function only(array $tasks = []): TaskRunner
    {
        $this->only = $tasks;

        return $this;
    }

    /**
     * Définit une heure qui sera utilisée.
     * Permet de définir une heure spécifique par rapport à laquelle le test sera effectué.
     * Doit être dans un format compatible avec DateTime.
     */
    public function withTestTime(string $time): TaskRunner
    {
        $this->testTime = $time;

        return $this;
    }

    /**
     * Ecrire une ligne dans l'interface de ligne de commande
     */
    protected function cliWrite(string $text, ?string $foreground = null)
    {
        // Sauter l'écriture pour cli dans les tests
        if (on_test()) {
            return;
        }

        if (! is_cli()) {
            return;
        }

		if (static::$writter === null) {
            static::$writter = new Writer();
        }

		static::$writter->write(
			text: static::$writter->colorizer()->line('[' . date('Y-m-d H:i:s') . '] ' . $text, ['fg' => $foreground]),
			eol: true,
		);
    }

    /**
     * Ajoute le journal des performances
     */
    protected function updateLogs(TaskLog $taskLog)
    {
        if (parametre('tasks.log_performance') === false) {
            return;
        }

        // un nom « unique » sera renvoyé s'il n'a pas été défini
        $name = $taskLog->task->name;

        $data = [
            'task'     => $name,
            'type'     => $taskLog->task->getType(),
            'start'    => $taskLog->runStart->format('Y-m-d H:i:s'),
            'duration' => $taskLog->duration(),
            'output'   => $taskLog->output ?? null,
            'error'    => serialize($taskLog->error ?? null),
        ];

        // Obtenir les logs existants
        $logs = parametre("tasks.log-{$name}");
        if (empty($logs)) {
            $logs = [];
        }

        // Assurez-vous que nous avons de la place pour un de plus
        /** @var int $maxLogsPerTask */
        $maxLogsPerTask = parametre('tasks.max_logs_per_task');
        if ((is_countable($logs) ? count($logs) : 0) > $maxLogsPerTask) {
            array_pop($logs);
        }

        // Add the log to the top of the array
        array_unshift($logs, $data);

        parametre("tasks.log-{$name}", $logs);
    }
}
