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

use BlitzPHP\Tasks\Exceptions\TasksException;
use BlitzPHP\Utilities\Date;
use Exception;

/**
 * @credit <a href="https://tasks.codeigniter.com">CodeIgniter4 - CodeIgniter\Tasks\CronExpression</a>
 */
class CronExpression
{
    /**
     * Le fuseau horaire dans lequel cela doit être pris en compte.
     */
    protected string $timezone;

    /**
     * Le fuseau horaire global à utiliser.
     */
    private string $globalTimezone;

    /**
     * La date/heure actuelle. Utilisée pour les tests.
     */
    protected ?Date $testTime = null;

    /**
     * La chaîne d'expression Cron actuelle à traiter
     */
    private ?string $currentExpression = null;

    /**
     * Nous permet de définir le fuseau horaire global pour toutes les tâches de construction
     *
     * @param string $timezone Le fuseau horaire global pour toutes les tâches
     *
     * @return void
     */
    public function __construct(?string $timezone = null)
    {
        if (null === $globalTimezone = config('tasks.timezone')) {
            $globalTimezone = config('app.timezone');
        }

        $this->globalTimezone = $globalTimezone;

        $this->setTimezone($timezone);
    }

    /**
     * Définit le fuseau horaire global pour toutes les tâches de construction.
     */
    public function setTimezone(?string $timezone = null): self
    {
        $this->timezone = $timezone ?? $this->globalTimezone;

        return $this;
    }

    /**
     * Vérifie si l'expression cron doit être exécutée. Permet d'utiliser un fuseau horaire personnalisé pour une tâche spécifique
     *
     * @param string $expression L'expression Cron à évaluer
     */
    public function shouldRun(string $expression): bool
    {
        $this->setTime();

        $this->currentExpression = $expression;

        // Diviser l'expression en parties distinctes
        [
            $min,
            $hour,
            $monthDay,
            $month,
            $weekDay,
        ] = explode(' ', $expression);

        return $this->checkMinute($min)
            && $this->checkHour($hour)
            && $this->checkMonthDay($monthDay)
            && $this->checkMonth($month)
            && $this->checkWeekDay($weekDay);
    }

    /**
     * Renvoie une instance Date représentant la prochaine date/heure à laquelle cette expression serait exécutée.
     */
    public function nextRun(string $expression): Date
    {
        $this->setTime();

        return (new RunResolver())->nextRun($expression, clone $this->testTime);
    }

    /**
     * Renvoie une instance Date représentant la dernière date/heure à laquelle cette expression aurait été exécutée.
     */
    public function lastRun(string $expression): Date
    {
        return new Date();
    }

    /**
     * Définit une date/heure qui sera utilisée à la place de l'heure actuelle pour faciliter les tests.
     *
     * @throws Exception
     */
    public function testTime(string $dateTime): self
    {
        $this->testTime = Date::parse($dateTime, $this->timezone);

        return $this;
    }

    private function checkMinute(string $time): bool
    {
        return $this->checkTime($time, 'i');
    }

    private function checkHour(string $time): bool
    {
        return $this->checkTime($time, 'G');
    }

    private function checkMonthDay(string $time): bool
    {
        return $this->checkTime($time, 'j');
    }

    private function checkMonth(string $time): bool
    {
        return $this->checkTime($time, 'n');
    }

    private function checkWeekDay(string $time): bool
    {
        return $this->checkTime($time, 'w');
    }

    private function checkTime(string $time, string $format): bool
    {
        if ($time === '*') {
            return true;
        }

        $currentTime = $this->testTime->format($format);
        assert(ctype_digit($currentTime));

        // Gérer les temps répétitifs (c'est-à-dire /5 ou */5 pour toutes les 5 minutes)
        if (str_contains($time, '/')) {
            $period = substr($time, strpos($time, '/') + 1) ?: '';

            if ($period === '' || ! ctype_digit($period)) {
                throw TasksException::invalidCronExpression($this->currentExpression);
            }

            return ($currentTime % $period) === 0;
        }

        // Gere les plages (1-5)
        if (str_contains($time, '-')) {
            $items         = [];
            [$start, $end] = explode('-', $time);

            for ($i = $start; $i <= $end; $i++) {
                $items[] = $i;
            }
        }
        // Gérer plusieurs jours
        else {
            $items = explode(',', $time);
        }

        return in_array($currentTime, $items, false);
    }

    /**
     * Définit l'heure actuelle si elle n'a pas déjà été définie.
     *
     * @throws Exception
     */
    private function setTime(): void
    {
        // Définir notre heure actuelle
        if ($this->testTime instanceof Date) {
            return;
        }

        $this->testTime = Date::now($this->timezone);
    }
}
