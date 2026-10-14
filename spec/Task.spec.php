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
use BlitzPHP\Tasks\Exceptions\TasksException;
use BlitzPHP\Tasks\Task;
use BlitzPHP\Utilities\Date;
use BlitzPHP\Utilities\Helpers;

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
        expect($task->type)->toBe('command');
    });

    it('__set', function () {
        $task = new Task('command', 'foo:bar');

        $task->fake = 'foo:bar';

        $attributes = ReflectionHelper::getPrivateProperty($task, 'attributes');

        expect($attributes)->toBe(['fake' => 'foo:bar']);
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

        $task = (new Task('command', 'tasks:test'))->hourly()->environments('production');

        expect($task->shouldRun('12:00am'))->toBeFalsy();
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

    it('Execute une tache unique', function () {
        $task = new Task('command', 'foo:bar');

        expect($task->singleInstance)->toBeFalsy();

        $result = $task->singleInstance();
        expect($task->singleInstance)->toBeTruthy();
        expect($task->singleInstanceTTL)->toBeNull();
        expect($result)->toBe($task);

        // Test avec un TTL personnalisé
        $task->singleInstance(3600);
        expect($task->singleInstance)->toBeTruthy();
        expect($task->singleInstanceTTL)->toBe(3600);
    });

    it('Recuperation de la cle de verouillage', function () {
        $task = new Task('command', 'foo:bar');
        $task->named('test_task');

        $method   = ReflectionHelper::getPrivateMethodInvoker($task, 'getLockKey');
        $expected = 'task_lock_test_task';

        expect($method())->toBe($expected);

        // Test avec une tache sans nom - doit utiliser le nom dynamique
        $task   = new Task('command', 'foo:bar');
        $method = ReflectionHelper::getPrivateMethodInvoker($task, 'getLockKey');

        // Doit utiliser le nom de la tache depuis le getter magique
        $expected = "task_lock_{$task->name}";
        expect($method())->toBe($expected);
    });

    it('Cohérence de la clé de verrouillage pour les tâches nommées', function () {
        // Créer deux tâches closure différentes avec le même nom
        $closure1 = static fn () => 'test1';

        $closure2 = static function () {
            return 'test2'; // Different functionality
        };

        $task1 = new Task('closure', $closure1);
        $task2 = new Task('closure', $closure2);

        // Si elles ont le même nom, elles devraient avoir la même clé de verrouillage
        $task1->named('same_name');
        $task2->named('same_name');

        $getLockKey1 = ReflectionHelper::getPrivateMethodInvoker($task1, 'getLockKey');
        $getLockKey2 = ReflectionHelper::getPrivateMethodInvoker($task2, 'getLockKey');

        expect($getLockKey1())->toBe($getLockKey2());

        // Des noms différents devraient produire des clés différentes
        $task3 = new Task('closure', $closure1);
        $task3->named('different_name');

        $getLockKey3 = ReflectionHelper::getPrivateMethodInvoker($task3, 'getLockKey');

        expect($getLockKey1())->not->toBe($getLockKey3());
    });

    it('shouldRun avec singleInstance', function () {
        $task = (new Task('command', 'foo:bar'))
            ->named('test_should_run')
            ->hourly()
            ->singleInstance();

        // Devrait s'exécuter à l'heure correcte sans verrou existant
        expect($task->shouldRun('12:00am'))->toBeTruthy();

        // Créer un verrou
        $lockKey = ReflectionHelper::getPrivateMethodInvoker($task, 'getLockKey')();
        cache()->set($lockKey, [], 3600);

        // Should not run if a lock exists
        expect($task->shouldRun('12:00am'))->toBeFalsy();

        cache()->delete($lockKey);
    });

    it('run avec singleInstance', function () {
        $task = new Task('closure', static fn () => 'task executed');
        $task->named('test_run_single');
        $task->singleInstance();

        $result = $task->run();
        expect($result)->toBe('task executed');
        $lockKey = ReflectionHelper::getPrivateMethodInvoker($task, 'getLockKey')();
        expect(cache()->get($lockKey))->toBeNull();
    });

    it('verrou libéré après une exception', function () {
        $task = new Task('command', 'invalid:command');
        $task->named('test_exception');
        $task->singleInstance();

        $reflection = new ReflectionClass($task);
        $property   = $reflection->getProperty('type');
        $property->setValue($task, 'invalid_type');

        $lockKey = ReflectionHelper::getPrivateMethodInvoker($task, 'getLockKey')();

        try {
            $task->run();
            expect(false)->toBe(true); // Should not reach here
        } catch (Exception $e) {
            expect($e)->toBeAnInstanceOf(TasksException::class);
        }

        expect(cache()->get($lockKey))->toBeNull();
    });

    it('singleInstance avec TTL personnalisé', function () {
        $task = new Task('closure', static fn () => 'done');
        $task->named('test_ttl');

        $task->singleInstance(60);

        expect(ReflectionHelper::getPrivateProperty($task, 'singleInstanceTTL'))->toBe(60);

        $task2 = new Task('closure', static fn () => 'done');
        $task2->named('test_no_ttl');
        $task2->singleInstance();

        expect(ReflectionHelper::getPrivateProperty($task2, 'singleInstanceTTL'))->toBeNull();
    });

    it('Peut exécuter une commande shell', function () {
        expect(file_exists($path = __DIR__ . '/test.php'))->toBeFalsy();

        $task = new Task('shell', 'cp ' . __FILE__ . ' ' . $path);
        $task->run();

        expect(file_exists($path))->toBeTruthy();

        $task = new Task('shell', 'rm ' . $path);
        $task->run();

        expect(file_exists($path))->toBeFalsy();
    });

    it('Peut executer un evenement', function () {
        expect(file_exists($path = __DIR__ . '/test.txt'))->toBeFalsy();

        service('event')->on($event = 'test.event', function () use ($path) {
            file_put_contents($path, 'event.txt');
        });

        $task = new Task('event', $event);
        $task->run();

        expect(file_exists($path))->toBeTruthy();
        expect(file_get_contents($path))->toBe('event.txt');

        unlink($path);
    });

    it('Peut executer une URL', function () {
        skipIf(! Helpers::isConnected());

        $task   = new Task('url', 'https://raw.githubusercontent.com/blitz-php/tasks/refs/heads/main/composer.json');
        $result = $task->run();
        $result = json_decode($result, true);

        expect($result)->toContainKey('name');
        expect($result['name'])->toBe('blitz-php/tasks');
    });
});
