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
 * Fournit les méthodes permettant d'attribuer des fréquences à des tâches individuelles.
 *
 * @credit <a href="https://tasks.codeigniter.com">CodeIgniter4 - CodeIgniter\Tasks\FrequenciesTrait</a>
 */
trait FrequenciesTrait
{
    /**
     * L'expression cron générée
     *
     * @var array<int|string, int|string>
     */
    protected array $expression = [
        'min'        => '*',
        'hour'       => '*',
        'dayOfMonth' => '*',
        'month'      => '*',
        'dayOfWeek'  => '*',
    ];

    /**
     * Si cette option est répertoriée, elle sera limitée à l'exécution dans ces environnements uniquement.
     *
     * @var list<string>
     */
    protected $allowedEnvironments;

    /**
     * Planifie la tâche via une chaîne d'expression crontab brute.
     */
    public function cron(string $expression): self
    {
        $this->expression = explode(' ', $expression);

        return $this;
    }

    /**
     * Renvoie l'expression générée.
     */
    public function getExpression(): string
    {
        return implode(' ', array_values($this->expression));
    }

    /**
     * S'exécute tous les jours à l'heure indiquée
     */
    public function at(string $time): self
    {
        return $this->daily($time);
    }

	/**
     * S'exécute tous les jours à minuit, sauf si une chaîne d'heure est transmise (comme 04:08pm)
     */
    public function daily(?string $time = null): self
    {
        $min = $hour = 0;

        if (! empty($time)) {
            [$min, $hour] = $this->parseTime($time);
        }

        $this->expression['min']  = $min;
        $this->expression['hour'] = $hour;

        return $this;
    }

	/**
     * S'execute entre une temps de debut et de fin
     */
    public function between(string $startTime, string $endTime): self
    {
		[$minStart, $hourStart] = array_map('intval', $this->parseTime($startTime));
		[$minEnd, $hourEnd]     = array_map('intval', $this->parseTime($endTime));

		$this->betweenHours($hourStart, $hourEnd);
		$this->betweenMinutes($minStart, $minEnd);

        return $this;
    }

    /**
     * S'execute à une heure précise de la journée
     */
    public function time(string $time): self
    {
        [$min, $hour] = $this->parseTime($time);

        $this->expression['min']  = $min;
        $this->expression['hour'] = $hour;

        return $this;
    }

    /**
     * S'exécute au début de chaque heure à une minute précise.
     */
    public function hourly(?int $minute = null): self
    {
        return $this->everyHour(1, $minute);
    }

    /**
     * S'exécute toutes les heures ou toutes les x heures
     *
     * @param int|string|null $minute
     */
    public function everyHour(int $hour = 1, $minute = null): self
    {
        $this->expression['min']  = $minute ?? '0';
        $this->expression['hour'] = ($hour === 1) ? '*' : '*/' . $hour;

        return $this;
    }

	/**
     * S'exécute toutes les 2 heures
     *
     * @param int|string|null $minute
     */
    public function everyTwoHours($minute = null): self
    {
        return $this->everyHour(2, $minute);
    }

	/**
     * S'exécute toutes les 3 heures
     *
     * @param int|string|null $minute
     */
    public function everyThreeHours($minute = null): self
    {
        return $this->everyHour(3, $minute);
    }

	/**
     * S'exécute toutes les 4 heures
     *
     * @param int|string|null $minute
     */
    public function everyFourHours($minute = null): self
    {
        return $this->everyHour(4, $minute);
    }

	/**
     * S'exécute toutes les 6 heures
     *
     * @param int|string|null $minute
     */
    public function everySixHours($minute = null): self
    {
        return $this->everyHour(6, $minute);
    }

    /**
     * S'execute toutes les heures impaires
     */
    public function everyOddHour($minute = null): self
    {
		$this->expression['min']  = $minute ?? '0';
        $this->expression['hour'] = '1-23/2';

        return $this;
    }

    /**
     * S'execute dans une plage horaire spécifique
     */
    public function betweenHours(int $fromHour, int $toHour): self
    {
        $this->expression['hour'] = $fromHour . '-' . $toHour;

        return $this;
    }

    /**
     * Fonctionne à des heures spécifiques choisies
     *
     * @param int|list<int> $hours
     */
    public function hours($hours = []): self
    {
        if (! is_array($hours)) {
            $hours = [$hours];
        }

        $this->expression['hour'] = implode(',', $hours);

        return $this;
    }

    /**
     * Définissez le temps d'exécution sur toutes les minutes ou toutes les x minutes.
     *
     * @param int|string|null $minute Lorsqu'il est défini, spécifie que le travail sera exécuté toutes les $minute minutes
     */
    public function everyMinute($minute = null): self
    {
        $this->expression['min'] = null === $minute ? '*' : '*/' . $minute;

        return $this;
    }

    /**
     * S'execute toutes les 2 minutes
     */
    public function everyTwoMinutes(): self
    {
        return $this->everyMinute(2);
    }

    /**
     * S'execute toutes les 3 minutes
     */
    public function everyThreeMinutes()
    {
        return $this->everyMinute(3);
    }

    /**
     * S'execute toutes les 4 minutes
     */
    public function everyFourMinutes()
    {
        return $this->everyMinute(4);
    }

    /**
     * S'execute toutes les 5 minutes
     */
    public function everyFiveMinutes(): self
    {
        return $this->everyMinute(5);
    }

    /**
     * S'execute toutes les 10 minutes
     */
    public function everyTenMinutes(): self
    {
		return $this->everyMinute(10);
    }

    /**
     * S'execute toutes les 15 minutes
     */
    public function everyFifteenMinutes(): self
    {
        return $this->everyMinute(15);
    }

    /**
     * S'execute toutes les 30 minutes
     */
    public function everyThirtyMinutes(): self
    {
        return $this->everyMinute(30);
    }

    /**
     * S'execute dans une plage de minutes spécifiée
     */
    public function betweenMinutes(int $fromMinute, int $toMinute): self
    {
        $this->expression['min'] = $fromMinute . '-' . $toMinute;

        return $this;
    }

    /**
     * S'execute sur un nombre de minutes spécifique choisi
     *
     * @param int|list<int> $minutes
     */
    public function minutes($minutes = []): self
    {
        if (! is_array($minutes)) {
            $minutes = [$minutes];
        }

        $this->expression['min'] = implode(',', $minutes);

        return $this;
    }

    /**
     * S'execute à des jours précis
     *
     * @param int|list<int> $days [0 : Dimanche - 6 : Samedi]
     */
    public function days($days): self
    {
        if (! is_array($days)) {
            $days = [$days];
        }

        $this->expression['dayOfWeek'] = implode(',', $days);

        return $this;
    }

    /**
     * S'execute tous les dimanches à minuit, sauf si l'heure est transmise.
     */
    public function sundays(?string $time = null): self
    {
        return $this->setDayOfWeek(Scheduler::SUNDAY, $time);
    }

    /**
     * S'execute tous les lundi à minuit, sauf si l'heure est transmise.
     */
    public function mondays(?string $time = null): self
    {
        return $this->setDayOfWeek(Scheduler::MONDAY, $time);
    }

    /**
     * S'execute tous les mardi à minuit, sauf si l'heure est transmise.
     */
    public function tuesdays(?string $time = null): self
    {
        return $this->setDayOfWeek(Scheduler::TUESDAY, $time);
    }

    /**
     * S'execute tous les mercredi à minuit, sauf si l'heure est transmise.
     */
    public function wednesdays(?string $time = null): self
    {
        return $this->setDayOfWeek(Scheduler::WEDNESDAY, $time);
    }

    /**
     * S'execute tous les jeudi à minuit, sauf si l'heure est transmise.
     */
    public function thursdays(?string $time = null): self
    {
        return $this->setDayOfWeek(Scheduler::THURSDAY, $time);
    }

    /**
     * S'execute tous les vendredi à minuit, sauf si l'heure est transmise.
     */
    public function fridays(?string $time = null): self
    {
        return $this->setDayOfWeek(Scheduler::FRIDAY, $time);
    }

    /**
     * S'execute tous les samedi à minuit, sauf si l'heure est transmise.
     */
    public function saturdays(?string $time = null): self
    {
        return $this->setDayOfWeek(Scheduler::SATURDAY, $time);
    }

    /**
     * Devrait être exécuté le premier jour de chaque mois.
     */
    public function monthly(?string $time = null): self
    {
        return $this->monthlyOn(1, $time);
    }

	/**
     * S'execute mensuellement à un jour et une heure donnés.
     *
     * @param  int<1, 31>  $dayOfMonth
     */
    public function monthlyOn(int $dayOfMonth = 1, ?string $time = null): self
    {
        $min = $hour = 0;

        if (! empty($time)) {
            [$min, $hour] = $this->parseTime($time);
        }

        $this->expression['min']        = $min;
        $this->expression['hour']       = $hour;
        $this->expression['dayOfMonth'] = $dayOfMonth;

        return $this;
    }

    /**
     * S'execute à des jours précis du mois
     *
     * @param int|list<int> $days [1-31]
     */
    public function daysOfMonth($days): self
    {
        if (! is_array($days)) {
            $days = [$days];
        }

        $this->expression['dayOfMonth'] = implode(',', $days);

        return $this;
    }

    /**
     * S'execute le dernier jours du mois
     */
    public function lastDayOfMonth(?string $time = null)
    {
        $this->daily($time);

		return $this->daysOfMonth(Date::now()->endOfMonth()->getDay());
    }

    /**
     * Définissez le temps d'exécution sur tous les mois ou tous les x mois.
     *
     * @param int|string|null $month Lorsqu'il est défini, spécifie que le travail sera exécuté tous les $month mois
     */
    public function everyMonth($month = null): self
    {
        $this->expression['month'] = null === $month ? '*' : '*/' . $month;

        return $this;
    }

    /**
     * S'execute sur une plage de mois spécifique
     *
     * @param int $from Mois [1-12]
     * @param int $to   Mois [1-12]
     */
    public function betweenMonths(int $from, int $to): self
    {
        $this->expression['month'] = $from . '-' . $to;

        return $this;
    }

    /**
     * S'execute a des mois specifiques
     *
     * @param list<int> $months [1-12]
     */
    public function months(array $months = []): self
    {
        $this->expression['month'] = implode(',', $months);

        return $this;
    }

    /**
     * Devrait s'executer le premier jour de chaque trimestre,
     * exp. Jan 1, Apr 1, July 1, Oct 1
     */
    public function quarterly(?string $time = null): self
    {
        return $this->quarterlyOn(1, $time);
    }

	/**
     * S'execute tous les trimestres à un jour et une heure donnés.
     */
    public function quarterlyOn(int $dayOfQuarter = 1, ?string $time = null)
	{
        $min = $hour = 0;

        if (! empty($time)) {
            [$min, $hour] = $this->parseTime($time);
        }

        $this->expression['min']        = $min;
        $this->expression['hour']       = $hour;
        $this->expression['dayOfMonth'] = $dayOfQuarter;

        $this->everyMonth(3);

        return $this;
    }

    /**
     * Devrait s'executer le premier jour de l'annee.
     */
    public function yearly(?string $time = null): self
    {
        return $this->yearlyOn(1, 1, $time);
    }

	/**
     * S'execute chaque année à un mois, un jour et une heure donnés.
     *
     * @param  int<1, 31>  $dayOfMonth
     */
    public function yearlyOn(int $month = 1, int $dayOfMonth = 1, ?string $time = null): self
    {
        $min = $hour = 0;

        if (! empty($time)) {
            [$min, $hour] = $this->parseTime($time);
        }

        $this->expression['min']        = $min;
        $this->expression['hour']       = $hour;
        $this->expression['dayOfMonth'] = $dayOfMonth;
        $this->expression['month']      = $month;

        return $this;
    }

    /**
     * S'execute en semainde (du lundi au vendredi).
     */
    public function weekdays(?string $time = null): self
    {
        $min = $hour = 0;

        if (! empty($time)) {
            [$min, $hour] = $this->parseTime($time);
        }

        $this->expression['min']       = $min;
        $this->expression['hour']      = $hour;
        $this->expression['dayOfWeek'] = '1-5';

        return $this;
    }

    /**
     * S'execute les weekends (samedi et dimanche)
     */
    public function weekends(?string $time = null): self
    {
        $min = $hour = 0;

        if (! empty($time)) {
            [$min, $hour] = $this->parseTime($time);
        }

        $this->expression['min']       = $min;
        $this->expression['hour']      = $hour;
        $this->expression['dayOfWeek'] = '6-7';

        return $this;
    }

    /**
     * Fonction interne utilisée par les fonctions mondays(), etc.
     */
    protected function setDayOfWeek(int $day, ?string $time = null): self
    {
        $min = $hour = '*';

        if (! empty($time)) {
            [$min, $hour] = $this->parseTime($time);
        }

        $this->expression['min']       = $min;
        $this->expression['hour']      = $hour;
        $this->expression['dayOfWeek'] = $day;

        return $this;
    }

    /**
     * Analyse une chaîne de temps (comme 04:08pm) en minutes et en heures
     *
     * @return list<string>
     */
    protected function parseTime(string $time): array
    {
        $time = strtotime($time);

        return [
            date('i', $time), // mins
            date('G', $time),
        ];
    }
}
