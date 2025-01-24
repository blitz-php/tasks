<?php

/**
 * This file is part of BlitzPHP Tasks.
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use BlitzPHP\Container\Container;
use BlitzPHP\Tasks\Scheduler;
use BlitzPHP\Tasks\Task;

use function Kahlan\expect;

describe('Scheduler', function () {
    beforeEach(function () {
        $this->scheduler = new Scheduler();
    });

    it('Peut sauvegarder une closure', function () {
        $function = static fn () => 'Hello';

        $task = $this->scheduler->call($function);

        expect($function)->toBeAnInstanceOf(Closure::class);
        expect($task)->toBeAnInstanceOf(Task::class);
        expect($function)->toBe($task->getAction());
        expect('Hello')->toBe($task->getAction()());
    });

    it('Peut sauvegarder un callable', function () {
        $class = new class () {
            public function execute()
            {
                return 'Hello';
            }
        };

        $task = $this->scheduler->call([$class, 'execute']);

        expect($task)->toBeAnInstanceOf(Task::class);
        expect('Hello')->toBe($task->getAction()());
    });

    it('Peut sauvegarder une classe invokable', function () {
        $class = new class () {
            public function __invoke()
            {
                return 'Hello';
            }
        };

        $task = $this->scheduler->call($class);

        expect($task)->toBeAnInstanceOf(Task::class);
        expect('Hello')->toBe($task->getAction()());
    });

    it("Peut faire de l'injection de dependance dans un callable", function () {
        $class = new class () {
            public function __invoke(Container $container)
            {
                $container->set('foo', 'bar');

                return $container;
            }
        };

        $task = $this->scheduler->call($class);

        expect($task)->toBeAnInstanceOf(Task::class);

        $container = $task->run();

        expect($container)->toBeAnInstanceOf(Container::class);
        expect($container->get('foo'))->toBe('bar');
    });

    it('Peut sauvegarder une commande klinge', function () {
        $task = $this->scheduler->command('foo:bar');

        expect($task)->toBeAnInstanceOf(Task::class);
        expect('foo:bar')->toBe($task->getAction());
    });

    it('Peut sauvegarder une commande shell', function () {
        $task = $this->scheduler->shell('foo:bar');

        expect($task)->toBeAnInstanceOf(Task::class);
        expect('foo:bar')->toBe($task->getAction());
        expect('shell')->toBe($task->getType());
    });

    it('Peut sauvegarder un evenement', function () {
        $task = $this->scheduler->event('foo.bar');

        expect($task)->toBeAnInstanceOf(Task::class);
        expect('foo.bar')->toBe($task->getAction());
        expect('event')->toBe($task->getType());
    });

    it('Peut sauvegarder un appel d\'URL', function () {
        $task = $this->scheduler->url('http://localhost');

        expect($task)->toBeAnInstanceOf(Task::class);
        expect('http://localhost')->toBe($task->getAction());
        expect('url')->toBe($task->getType());
    });
});
