<?php

namespace App\Commands;

use BlitzPHP\Cli\Console\Command;

/**
 * @internal
 */
final class TasksTest extends Command
{
    protected $group       = 'Testing';
    protected $name        = 'tasks:test';
    protected $description = 'Tests Tasks';

    public function execute(array $params)
	{
		$this->write('La commande peut produire du texte.');
    }
}
