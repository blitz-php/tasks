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

use BlitzPHP\Utilities\Date;
use Exception;
use Throwable;

/**
 * @property ?Throwable $error
 * @property ?string    $output
 * @property Date       $runStart
 * @property Task       $task
 *
 * @credit <a href="https://tasks.codeigniter.com">CodeIgniter4 - CodeIgniter\Tasks\TaskLog</a>
 */
class TaskLog
{
    protected Task $task;
    protected ?string $output = null;
    protected Date $runStart;
    protected Date $runEnd;

    /**
     * L'exception levée pendant l'exécution, le cas échéant.
     */
    protected ?Throwable $error = null;

    /**
     * Constructeur TaskLog.
     *
     * @param array<string,mixed> $data
     */
    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Renvoie la durée de la tâche en secondes et fractions de seconde.
     *
     * @throws Exception
     */
    public function duration(): string
    {
        return number_format((float) $this->runEnd->format('U.u') - (float) $this->runStart->format('U.u'), 2);
    }

    /**
     * Getter magique.
     *
     * @return mixed
     */
    public function __get(string $key)
    {
        if (property_exists($this, $key)) {
            return $this->{$key};
        }
    }
}
