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

use function Kahlan\expect;

describe('HookTrait', function () {
    it('testAfter et testBefore', function () {
        expect(file_exists($path = __DIR__ . '/test-hook.txt'))->toBeFalsy();

        $task = new Task('closure', fn () => 'Hello');
        $task->before(function () use ($path) {
            file_put_contents($path, 'before');
        });

        $task->after(function () use ($path) {
            file_put_contents($path, 'after', FILE_APPEND);
        });

        $result = $task->run();

        expect(file_exists($path))->toBeTruthy();
        $content = file_get_contents($path);

        expect(str_contains($content, 'before'))->toBeTruthy();
        expect(str_contains($content, 'after'))->toBeTruthy();
        expect($result)->toBe('Hello');

        unlink($path);
    });

    it('sendOutputTo', function () {
        $task = new Task('closure', function () {
            echo 'Hello';

            return 'world';
        });

        $task->sendOutputTo($path = __DIR__ . '/output.txt');

        $result = $task->run();

        expect(file_exists($path))->toBeTruthy();
        expect(file_get_contents($path))->toBe('Hello');
        expect($result)->toBe('world');

        unlink($path);
    });

    it('appendOutputTo', function () {
        $task = new Task('closure', function () {
            echo 'Hello';
        });

        $task->appendOutputTo($path = __DIR__ . '/output.txt');

        file_put_contents($path, 'World');

        $task->run();

        expect(file_exists($path))->toBeTruthy();
        expect(file_get_contents($path))->toBe('WorldHello');

        unlink($path);
    });

    it('onSuccess', function () {
        $task = new Task('closure', fn () => 'Hello');

        expect(file_exists($path = __DIR__ . '/test-hook.txt'))->toBeFalsy();

        $task->onSuccess(function () use ($path) {
            file_put_contents($path, 'Success!');
        });

        $task->run();

        expect(file_exists($path))->toBeTruthy();
        expect(file_get_contents($path))->toBe('Success!');

        unlink($path);
    });

    it('onFailure', function () {
        $task = new Task('closure', function () {
            throw new Exception('Error');
        });

        expect(file_exists($path = __DIR__ . '/test-hook.txt'))->toBeFalsy();

        $task->onFailure(function () use ($path) {
            file_put_contents($path, 'Failure!');
        });

        $task->run();

        expect(file_exists($path))->toBeTruthy();
        expect(file_get_contents($path))->toBe('Failure!');

        unlink($path);
    });

    it('onFailure lorsqu\'il y\'a pas exception mais on renvoie un code d\'erreur', function () {
        $task = new Task('closure', fn () => EXIT_ERROR);

        expect(file_exists($path = __DIR__ . '/test-hook.txt'))->toBeFalsy();

        $task->onFailure(function () use ($path) {
            file_put_contents($path, 'Failure!');
        });

        $task->run();

        expect(file_exists($path))->toBeTruthy();
        expect(file_get_contents($path))->toBe('Failure!');

        unlink($path);
    });

    it('onFailure avec recuperation de l\'exception', function () {
        $task = new Task('closure', function () {
            throw new Exception('Error');
        });

        expect(file_exists($path = __DIR__ . '/test-hook.txt'))->toBeFalsy();

        $task->onFailure(function (Throwable $e) use ($path) {
            file_put_contents($path, $e->getMessage());
        });

        $task->run();

        expect(file_exists($path))->toBeTruthy();
        expect(file_get_contents($path))->toBe('Error');

        unlink($path);
    });
});
