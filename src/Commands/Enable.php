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

/**
 * Activer l'exécution des tâches.
 */
class Enable extends TaskCommand
{
    /**
     * {@inheritDoc}
     */
    protected $name = 'tasks:enable';

    /**
     * {@inheritDoc}
     */
    protected $description = 'Active l\'exécuteur de tâches.';

    /**
     * {@inheritDoc}
     */
    public function execute(array $params)
    {
        helper('preference');

        preference('tasks.enabled', true);

        $this->writer->ok('Les tâches ont été activées.');
    }
}
