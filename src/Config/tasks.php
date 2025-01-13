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

use BlitzPHP\Tasks\Scheduler;

return [
    /**
     * --------------------------------------------------------------------------
     * Les mesures de performance doivent-elles être enregistrées ?
     * --------------------------------------------------------------------------
     *
     * Si c'est vrai, le temps nécessaire à l'exécution de chaque tâche sera enregistré.
     * Nécessite que la table des paramètres ait été créée au préalable.
     */
    'log_performance' => false,

    /**
     * --------------------------------------------------------------------------
     * Logs de performances maximum
     * --------------------------------------------------------------------------
     *
     * Le nombre maximum de journaux qui doivent être enregistrés par tâche.
     * Des nombres inférieurs réduisent la quantité de base de données requise pour stocker les journaux.
     */
    'max_logs_per_task' => 10,

    /**
     * Enregistrez toutes les tâches dans cette méthode pour l'application.
     * Appelé par TaskRunner.
     */
    'init' => function (Scheduler $schedule) {
        $schedule->command('foo:bar')->daily();

        $schedule->shell('cp foo bar')->daily('11:00 pm');

        //        $schedule->call(static function () {
        //            // do something....
        //        })->mondays()->named('foo');
    },
];
