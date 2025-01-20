<?php

/**
 * This file is part of BlitzPHP Tasks.
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use BlitzPHP\Tasks\CronExpression;
use BlitzPHP\Tasks\Exceptions\TasksException;
use BlitzPHP\Utilities\Date;

use function Kahlan\expect;

describe('CronExpression', function () {
    beforeEach(function () {
        $this->cron = new CronExpression();
    });

    it('Test des minutes', function () {
        expect($this->cron->shouldRun('* * * * *'))->toBeTruthy();

        $this->cron->testTime('2020-05-01 10:04 am');

        expect($this->cron->shouldRun('10 * * * *'))->toBeFalsy();
        expect($this->cron->shouldRun('4 * * * *'))->toBeTruthy();
        expect($this->cron->shouldRun('04 * * * *'))->toBeTruthy();
        expect($this->cron->shouldRun('4,8 * * * *'))->toBeTruthy();
        expect($this->cron->shouldRun('1,2,4 * * * *'))->toBeTruthy();
        expect($this->cron->shouldRun('5-15 * * * *'))->toBeFalsy();
        expect($this->cron->shouldRun('1-5 * * * *'))->toBeTruthy();
        expect($this->cron->shouldRun('/4 * * * *'))->toBeTruthy();
        expect($this->cron->shouldRun('/2 * * * *'))->toBeTruthy();
        expect($this->cron->shouldRun('/5 * * * *'))->toBeFalsy();
    });

    it('Test des minutes avec un nombre invalide', function () {
        $this->cron->testTime('2020-05-01 10:04 am');

        expect(fn () => $this->cron->shouldRun('/ * * * *'))
            ->toThrow(new TasksException('"/ * * * *" is not a valid cron expression.'));
    });

    it('Test des minutes avec une donnée non numerique', function () {
        $this->cron->testTime('2020-05-01 10:04 am');

        expect(fn () => $this->cron->shouldRun('/a * * * *'))
            ->toThrow(new TasksException('"/a * * * *" is not a valid cron expression.'));
    });

    it('Test des heures', function () {
        $this->cron->testTime('2020-05-01 10:04 am');

        expect($this->cron->shouldRun('* * * * *'))->toBeTruthy();
        expect($this->cron->shouldRun('* 10 * * *'))->toBeTruthy();
        expect($this->cron->shouldRun('* 20 * * *'))->toBeFalsy();
        expect($this->cron->shouldRun('4 10 * * *'))->toBeTruthy();
        expect($this->cron->shouldRun('10 10 * * *'))->toBeFalsy();
        expect($this->cron->shouldRun('* 10,11 * * *'))->toBeTruthy();
        expect($this->cron->shouldRun('* 9,11,10 * * *'))->toBeTruthy();
        expect($this->cron->shouldRun('* 9,11,12 * * *'))->toBeFalsy();
        expect($this->cron->shouldRun('* 8-11 * * *'))->toBeTruthy();
        expect($this->cron->shouldRun('* 7-9 * * *'))->toBeFalsy();
        expect($this->cron->shouldRun('* /2 * * *'))->toBeTruthy();
        expect($this->cron->shouldRun('* /5 * * *'))->toBeTruthy();
        expect($this->cron->shouldRun('* /3 * * *'))->toBeFalsy();
    });

    it('Test des jours du mois', function () {
        $this->cron->testTime('2020-05-01 10:04 am');

        expect($this->cron->shouldRun('* * 1 * *'))->toBeTruthy();
        expect($this->cron->shouldRun('* * 01 * *'))->toBeTruthy();
        expect($this->cron->shouldRun('* * 02 * *'))->toBeFalsy();
        expect($this->cron->shouldRun('04 10 1 * *'))->toBeTruthy();
        expect($this->cron->shouldRun('05 10 1 * *'))->toBeFalsy();
        expect($this->cron->shouldRun('04 11 1 * *'))->toBeFalsy();
        expect($this->cron->shouldRun('* * 1,2 * *'))->toBeTruthy();
        expect($this->cron->shouldRun('* * 3,2 * *'))->toBeFalsy();
        expect($this->cron->shouldRun('* * 1-3 * *'))->toBeTruthy();
        expect($this->cron->shouldRun('* * 3-5 * *'))->toBeFalsy();
        expect($this->cron->shouldRun('* * /1 * *'))->toBeTruthy();
        expect($this->cron->shouldRun('* * /2 * *'))->toBeFalsy();
    });

    it('Test des mois', function () {
        $this->cron->testTime('2020-05-01 10:04 am');

        expect($this->cron->shouldRun('* * * 5 *'))->toBeTruthy();
        expect($this->cron->shouldRun('* * * 6 *'))->toBeFalsy();
        expect($this->cron->shouldRun('* * * 5,6 *'))->toBeTruthy();
        expect($this->cron->shouldRun('* * * 4,6 *'))->toBeFalsy();
        expect($this->cron->shouldRun('* * * 4-6 *'))->toBeTruthy();
        expect($this->cron->shouldRun('* * * 6-8 *'))->toBeFalsy();
        expect($this->cron->shouldRun('* * * /5 *'))->toBeTruthy();
        expect($this->cron->shouldRun('* * * /2 *'))->toBeFalsy();
    });

    it('Test des jours de la semaine', function () {
        // Le 1er mai est une vendredi
        $this->cron->testTime('2020-05-01 10:04 am');

        expect($this->cron->shouldRun('* * * * 5'))->toBeTruthy();
        expect($this->cron->shouldRun('* * * * 6'))->toBeFalsy();
        expect($this->cron->shouldRun('* * * * 5,6'))->toBeTruthy();
        expect($this->cron->shouldRun('* * * * 4,6'))->toBeFalsy();
        expect($this->cron->shouldRun('* * * * 1-3'))->toBeFalsy();
        expect($this->cron->shouldRun('* * * * 4-6'))->toBeTruthy();
        expect($this->cron->shouldRun('* * * * /5'))->toBeTruthy();
        expect($this->cron->shouldRun('* * * * /2'))->toBeFalsy();
    });

    it('Test des heures et minutes', function () {
        $this->cron->testTime('6:30 PM');
        expect($this->cron->shouldRun('30 18 * * *'))->toBeTruthy();
    });

    it('Test de chaque heures', function () {
        $hours24 = array_map(static fn ($h) => [
            $h . ':00',
            $h . ':10',
        ], range(0, 23));
        $hoursAM = array_map(static fn ($h) => [
            $h . ':00 AM',
            $h . ':10 AM',
        ], range(1, 12));
        $hoursPM = array_map(static fn ($h) => [
            $h . ':00 PM',
            $h . ':10 PM',
        ], range(1, 12));

        $iterable = [...$hours24, ...$hoursAM, ...$hoursPM];

        foreach ($iterable as [$hourTrue, $hourFalse]) {
            $this->cron->testTime($hourTrue);
            expect($this->cron->shouldRun('00 * * * *'))->toBeTruthy();

            $this->cron->testTime($hourFalse);
            expect($this->cron->shouldRun('00 * * * *'))->toBeFalsy();
        }
    });

    it('testQuarterPastHour', function () {
        $this->cron->testTime('6:15 PM');
        expect($this->cron->shouldRun('15 * * * *'))->toBeTruthy();

        $this->cron->testTime('6:30 PM');
        expect($this->cron->shouldRun('15 * * * *'))->toBeFalsy();
    });

    it('test des dates specifiques', function () {
        $this->cron->testTime('January 1, 2020 12:30 am');
        expect($this->cron->shouldRun('30 0 1 1,6,12 *'))->toBeTruthy();

        $this->cron->testTime('June 1, 2020 12:30 am');
        expect($this->cron->shouldRun('30 0 1 1,6,12 *'))->toBeTruthy();

        $this->cron->testTime('December 1, 2020 12:30 am');
        expect($this->cron->shouldRun('30 0 1 1,6,12 *'))->toBeTruthy();

        $this->cron->testTime('February 1, 2020 12:30 am');
        expect($this->cron->shouldRun('30 0 1 1,6,12 *'))->toBeFalsy();
    });

    it('Test de tous les jours de la semaine dans un mois', function () {
        // Le 5 octobre 2020 est un lundi
        $this->cron->testTime('October 5, 2020 8:00 pm');
        expect($this->cron->shouldRun('0 20 * 10 1-5'))->toBeTruthy();

        $this->cron->testTime('October 4, 2020 8:00 pm');
        expect($this->cron->shouldRun('0 20 * 10 1-5'))->toBeFalsy();

        // Un autre lundi
        $this->cron->testTime('November 2, 2020 8:00 pm');
        expect($this->cron->shouldRun('0 20 * 10 1-5'))->toBeFalsy();
    });

    it('Test de la prochaine date d\'execution', function () {
        $iterable = [
            ['* * * * *', 'October 5, 2020 8:01 pm'],
            ['4 * * * *', 'October 5, 2020 8:04 pm'],
            ['5-10 * * * *', 'October 5, 2020 8:05 pm'],
            ['57,3,8,10 * * * *', 'October 5, 2020 8:03 pm'],
            ['*/5 * * * *', 'October 5, 2020 8:05 pm'],
            ['30/5 * * * *', 'October 5, 2020 8:30 pm'],
            ['* 6 * * *', 'October 6, 2020 6:00 am'],
            ['* 12-14 * * *', 'October 6, 2020 12:00 pm'],
            ['* 5,6 * * *', 'October 6, 2020 5:00 am'],
            ['* 2/4 * * *', 'October 5, 2020 10:00 pm'],
            ['5 10 * * *', 'October 6, 2020 10:05 am'],
            ['* * 10 * *', 'October 10, 2020 8:00 pm'],
            ['5 4 10 * *', 'October 10, 2020 4:05 am'],
            ['* * * 3 *', 'March 5, 2021 8:00 pm'],
            ['* 4/5 12 3 *', 'March 12, 2021 4:00 am'],
            ['* * * * Wed', 'October 7, 2020 8:00 pm'],
            ['* * * * 3', 'October 7, 2020 8:00 pm'],
            ['* * * * 6,0', 'October 10, 2020 8:00 pm'],
        ];

        foreach ($iterable as [$exp, $expected]) {
            $this->cron->testTime('October 5, 2020 8:00 pm');

            $next = $this->cron->nextRun($exp);

            expect($next)->toBeAnInstanceOf(Date::class);
            expect($next->format('F j, Y g:i a'))->toBe($expected);
        }
    });
});
