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

/**
 * @credit <a href="https://tasks.codeigniter.com">CodeIgniter4 - CodeIgniter\Tasks\RunResolver</a>
 */
class RunResolver
{
    /**
     * Le nombre maximal de fois à parcourir lors de la recherche de la prochaine date d'exécution.
     */
    protected int $maxIterations = 1000;

    /**
     * Prend une expression cron, par exemple '* * * * 4',
     * et renvoie une instance Dte qui représente la prochaine fois que cette expression s'exécutera.
     */
    public function nextRun(string $expression, Date $next): Date
    {
        // Diviser l'expression en parties distinctes
        [
            $minute,
            $hour,
            $monthDay,
            $month,
            $weekDay,
        ] = explode(' ', $expression);

        $cron = [
            'minute'   => $minute,
            'hour'     => $hour,
            'monthDay' => $monthDay,
            'month'    => $month,
            'weekDay'  => $weekDay,
        ];

        // Nous n'avons pas besoin de satisfaire les valeurs '*', donc supprimez-les pour avoir moins de boucles.
        $cron = array_filter($cron, static fn ($item) => $item !== '*');

        // S'il ne reste plus rien, c'est toutes les minutes, alors réglez-le sur une minute à partir de maintenant.
        if ($cron === []) {
            return $next->addMinutes(1)->setSecond(0);
        }

        // Bouclez sur chacun des éléments $cron restants jusqu'à ce que nous parvenions à les satisfaire tous
        for ($i = 1; $i <= $this->maxIterations; $i++) {
            foreach ($cron as $position => $value) {
                $satisfied = false;

                // La méthode à utiliser sur l'instance Date
                $method = 'get' . ucfirst($position);

                // monthDay et weekDay nécessitent des méthodes personnalisées
                if ($position === 'monthDay') {
                    $method = 'getDay';
                }
                if ($position === 'weekDay') {
                    $method = 'getDayOfWeek';

                    $value = $this->convertDOWToNumbers($value);
                }
                $nextValue = $next->{$method}();

                // S'il s'agit d'une valeur unique
                if ($nextValue === $value) {
                    $satisfied = true;
                }
                // Si la valeur est une liste
                elseif (str_contains($value, ',')) {
                    if ($this->isInList($nextValue, $value)) {
                        $satisfied = true;
                    }
                }
                // Si la valeur est une plage
                elseif (str_contains($value, '-')) {
                    if ($this->isInRange($nextValue, $value)) {
                        $satisfied = true;
                    }
                }
                // Si la valeur est un incrément
                elseif (str_contains($value, '/')) {
                    if ($this->isInIncrement($nextValue, $value)) {
                        $satisfied = true;
                    }
                }

                // Si nous ne le faisons pas correspondre, recommencez les itérations
                if (! $satisfied) {
                    $next = $this->increment($next, $position);

                    continue 2;
                }
            }
        }

        return $next;
    }

    /**
     * Incrémente la partie du cron à la suivante appropriée.
     *
     * Remarque : il s'agit d'une méthode assez brutale pour le faire.
     * Nous pourrions certainement le rendre plus intelligent à l'avenir pour réduire le nombre d'itérations nécessaires.
     */
    protected function increment(Date $next, string $position): Date
    {
        switch ($position) {
            case 'minute':
                $next = $next->addMinutes(1);
                break;

            case 'hour':
                $next = $next->addHours(1);
                break;

            case 'monthDay':
            case 'weekDay':
                $next = $next->addDays(1);
                break;

            case 'month':
                $next = $next->addMonths(1);
                break;
        }

        return $next;
    }

    /**
     * Détermine si la valeur donnée est dans la plage spécifiée.
     *
     * @param int|string $value
     */
    protected function isInRange($value, string $range): bool
    {
        [$start, $end] = explode('-', $range);

        return $value >= $start && $value <= $end;
    }

    /**
     * Détermine si la valeur donnée est dans la liste de valeurs spécifiée.
     *
     * @param int|string $value
     */
    protected function isInList($value, string $list): bool
    {
        $list = explode(',', $list);

        return in_array(trim($value), $list, true);
    }

    /**
     * Détermine si la valeur $value est l'un des incréments.
     *
     * @param int|string $value
     */
    protected function isInIncrement($value, string $increment): bool
    {
        [$start, $increment] = explode('/', $increment);

        // Autoriser les valeurs de départ vides
        if ($start === '' || $start === '*') {
            $start = 0;
        }

        // L'intervalle $start doit être le premier à tester
        if ($value === $start) {
            return true;
        }

        return ($value - $start) > 0
               && (($value - $start) % $increment) === 0;
    }

    /**
     * Étant donné un paramètre cron pour le jour de la semaine,
     * il convertira les paramètres avec les jours de la semaine en texte (lun, mar, etc.)
     * en valeurs numériques pour une gestion plus facile.
     */
    protected function convertDOWToNumbers(string $origValue): string
    {
        $origValue = strtolower(trim($origValue));

        // S'il ne contient aucune lettre, renvoyez-le simplement.
        preg_match('/\w/', $origValue, $matches);

        if ($matches === []) {
            return $origValue;
        }

        $days = [
            'sun' => 0,
            'mon' => 1,
            'tue' => 2,
            'wed' => 3,
            'thu' => 4,
            'fri' => 5,
            'sat' => 6,
        ];

        return str_replace(array_keys($days), array_values($days), $origValue);
    }
}
