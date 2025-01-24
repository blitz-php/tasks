<?php

namespace BlitzPHP\Tasks\Commands;

use BlitzPHP\Utilities\Date;
use Symfony\Component\Process\Process;

class Work extends TaskCommand
{
    /**
     * {@inheritDoc}
     */
    protected $name = 'tasks:work';

    /**
     * {@inheritDoc}
     */
    protected $options = [
        '--run-output-file' => 'Le fichier vers lequel diriger la sortie de `tasks:run`.',
    ];

    /**
     * {@inheritDoc}
     */
    protected $description = 'Démarre le planificateur de tâche et l\'exécute localement.';

    /**
     * {@inheritDoc}
     */
    protected $required = [
        'symfony/process:^7.2',
    ];

    /** 
     * {@inheritDoc}
     */
    public function execute(array $params)
    {
        $this->info('Exéctutions des tâches programmées.');

        [$lastExecutionStartedAt, $executions] = [Date::now()->subMinutes(10), []];

        $command = sprintf(
            '%s %s %s', 
            escapeshellarg(PHP_BINARY), 
            escapeshellarg(base_path('klinge')), 
            'tasks:run'
        );

        if ($this->option('run-output-file')) {
            $command .= ' >> ' . escapeshellarg($this->option('run-output-file')) . ' 2>&1';
        }

        while (true) {
            usleep(100 * 1000);

            if (intval(Date::now()->getSecond()) === 0 && ! Date::now()->startOfMinute()->equalTo($lastExecutionStartedAt)) {
                $executions[] = $execution = Process::fromShellCommandline($command);

                $execution->start();

                $lastExecutionStartedAt = Date::now()->startOfMinute();
            }

            foreach ($executions as $key => $execution) {
                $output = $execution->getIncrementalOutput(). $execution->getIncrementalErrorOutput();

                $this->write(ltrim($output, "\n"))->eol();

                if (! $execution->isRunning()) {
                    unset($executions[$key]);
                }
            }
        }
    }
}
