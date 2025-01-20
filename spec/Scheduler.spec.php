<?php

/**
 * This file is part of BlitzPHP Tasks.
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use BlitzPHP\Tasks\Scheduler;
use BlitzPHP\Tasks\Task;

use function Kahlan\expect;

describe("Scheduler", function() {
	beforeEach(function() {
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

	it('Peut sauvegarder une commande klinge', function () {
        $task = $this->scheduler->command('foo:bar');

		expect($task)->toBeAnInstanceOf(Task::class);
		expect('foo:bar')->toBe($task->getAction());
	});

	it('Peut sauvegarder une commande shell', function () {
        $task = $this->scheduler->shell('foo:bar');

		expect($task)->toBeAnInstanceOf(Task::class);
		expect('foo:bar')->toBe($task->getAction());
	});
});
