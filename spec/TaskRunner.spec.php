cd<?php

/**
 * This file is part of BlitzPHP Tasks.
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use BlitzPHP\Spec\ReflectionHelper;
use BlitzPHP\Tasks\Task;
use BlitzPHP\Tasks\TaskRunner;

use function Kahlan\expect;

describe('TaskRunner', function () {
    beforeAll(function () {
        $this->getRunner = function (array $tasks = []) {
            $scheduler = service('scheduler');

            ReflectionHelper::setPrivateProperty($scheduler, 'tasks', $tasks);

            return new TaskRunner($scheduler);
        };

        $this->seeInFile = function (array $where) {
            $data = json_decode(file_get_contents(storage_path('.parametres.json')), true) ?: [];
            $data = collect($data);

            foreach ($where as $k => $v) {
                if ($v === null) {
                    $data = $data->whereNull($k);
                } else {
                    $data = $data->where($k, '=', $v);
                }
            }

            return $data->isNotEmpty();
        };
    });

    beforeEach(function () {
        parametre('tasks.log_performance', true);
    });

    afterAll(function () {
        @unlink(storage_path('.parametres.json'));
    });

    it("S'execute avec aucune tache", function () {
        expect($this->getRunner()->run())->toBeNull();
    });

    it("S'execute normalement avec succes", function () {
        $task1 = (new Task('closure', static function () {
            echo 'Task 1';
        }))->daily('12:05am')->named('task1');

        $task2 = (new Task('closure', static function () {
            echo 'Task 2';
        }))->daily('12:00am')->named('task2');

        $runner = $this->getRunner([$task1, $task2]);

        ob_start();
        $runner->withTestTime('12:00am')->run();
        $output = ob_get_clean();

        // Seulement la tache 2 doit avoir été exécutée
        expect($output)->toBe('Task 2');

        // doit avoir les stats de log
        $expected = [
            [
                'task'     => 'task2',
                'type'     => 'closure',
                'start'    => date('Y-m-d H:i:s'),
                'duration' => '0.00',
                'output'   => null,
                'error'    => serialize(null),
            ],
        ];

        expect($this->seeInFile([
            'file'  => 'tasks',
            'key'   => 'log-task2',
            'value' => serialize($expected),
        ]))->toBeTruthy();

        expect($this->seeInFile([
            'key' => 'log-task1',
        ]))->toBeFalsy();
    });
});
