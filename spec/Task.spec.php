<?php

/**
 * This file is part of BlitzPHP Tasks.
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use BlitzPHP\Spec\CliOutputHelper as COH;
use BlitzPHP\Spec\ReflectionHelper;
use BlitzPHP\Tasks\Task;
use BlitzPHP\Utilities\Date;

use function Kahlan\expect;

describe('Task', function () {
    beforeAll(function (): void {
        COH::setUpBeforeClass();
    });

    afterAll(function (): void {
        COH::tearDownAfterClass();
    });

    beforeEach(function (): void {
        COH::setUp();
    });

    afterEach(function (): void {
        COH::tearDown();
    });

    it('Test des tâches nommées', function () {
        $task = new Task('command', 'foo:bar');

        // Un nom aléatoire a été donné
        expect(str_starts_with($task->name, 'command_'))->toBeTruthy();

        $task = (new Task('command', 'foo:bar'))->named('foo');

        expect($task->name)->toBe('foo');
    });

    it("Teste si le constructeur sauvegarde l'action", function () {
        $task = new Task('command', 'foo:bar');

        $result = ReflectionHelper::getPrivateProperty($task, 'action');

        expect($result)->toBe('foo:bar');
    });

    it('getAction', function () {
        $task = new Task('command', 'foo:bar');

        expect($task->getAction())->toBe('foo:bar');
    });

    it('getType', function () {
        $task = new Task('command', 'foo:bar');

        expect($task->getType())->toBe('command');
    });

    it("Execution d'une commande", function () {
        $task = new Task('command', 'tasks:test');

        $task->run();

        expect(COH::buffer())->toMatch(
            static fn ($actual) => str_contains($actual, 'La commande peut produire du texte.')
        );
    });

    it('shouldRun', function () {
        $task = (new Task('command', 'tasks:test'))->hourly();

        expect($task->shouldRun('12:05am'))->toBeFalsy();
        expect($task->shouldRun('12:00am'))->toBeTruthy();
    });

    it("Peut s'executer dans un environnement donné", function () {
        $originalEnv = environment();

        $task = (new Task('command', 'tasks:test'))->environments('development');

        config()->set('app.environment', 'development');
        expect($task->shouldRun('12:00am'))->toBeTruthy();

        config()->set('app.environment', 'production');
        expect($task->shouldRun('12:00am'))->toBeFalsy();

        config()->set('app.environment', $originalEnv);
    });

    it('lastRun', function () {
        parametre('tasks.log_performance', true);

        $task = new Task('closure', static fn () => 1);
        $task->named('foo');

        // Doit être un tiret lorsqu'il n'est pas executé
        expect($task->lastRun())->toBe('--');

        $date = date('Y-m-d H:i:s');

        // Insérer un élément de performance dans la bd
        parametre("tasks.log-{$task->name}", [[
            'task'     => $task->name,
            'type'     => $task->getType(),
            'start'    => $date,
            'duration' => '11.3s',
            'output'   => null,
            'error'    => null,
        ]]);

        // Doit renvoyer l'heure actuelle
        expect($task->lastRun())->toBeAnInstanceOf(Date::class); // @phpstan-ignore-line
        expect($task->lastRun()->format('Y-m-d H:i:s'))->toBe($date);
    });
});
