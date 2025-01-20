<?php

/**
 * This file is part of BlitzPHP Tasks.
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use BlitzPHP\Tasks\Task;
use BlitzPHP\Tasks\TaskLog;
use BlitzPHP\Utilities\Date;

use function Kahlan\expect;

describe('TaskLog', function () {
    it('Test la duree', function () {
        $data = [
            [
                '2021-01-21 12:00:00',
                '2021-01-21 12:00:00',
                '0.00',
                ['first item', 'second item'],
            ],
            [
                '2021-01-21 12:00:00',
                '2021-01-21 12:00:01',
                '1.00',
                true,
            ],
            [
                '2021-01-21 12:00:00',
                '2021-01-21 12:05:12',
                '312.00',
                null,
            ],
        ];

        foreach ($data as [$start, $end, $expected, $output]) {
            $start = new Date($start);
            $end   = new Date($end);

            $log = new TaskLog([
                'task'     => new Task('closure', static function () {}),
                'output'   => $output,
                'runStart' => $start,
                'runEnd'   => $end,
                'error'    => null,
            ]);

            expect($log->duration())->toBe($expected);
            expect($log->runStart)->toBe($start);
        }
    });
});
