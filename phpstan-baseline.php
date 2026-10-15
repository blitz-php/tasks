<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$name of function service expects class\\-string\\<cronExpression\\>, string given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/src/Commands/Lister.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$name of function service expects class\\-string\\<scheduler\\>, string given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/src/Commands/Lister.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$name of function service expects class\\-string\\<scheduler\\>, string given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/src/Commands/Run.php',
];
$ignoreErrors[] = [
	'message' => '#^While loop condition is always true\\.$#',
	'identifier' => 'while.alwaysTrue',
	'count' => 1,
	'path' => __DIR__ . '/src/Commands/Work.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to constant class on an unknown class BlitzPHP\\\\Queue\\\\Job\\.$#',
	'identifier' => 'class.notFound',
	'count' => 1,
	'path' => __DIR__ . '/src/Scheduler.php',
];
$ignoreErrors[] = [
	'message' => '#^Access to property \\$driver on an unknown class BlitzPHP\\\\Queue\\\\Job\\.$#',
	'identifier' => 'class.notFound',
	'count' => 1,
	'path' => __DIR__ . '/src/Scheduler.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to method execute\\(\\) on an unknown class BlitzPHP\\\\Queue\\\\Job\\.$#',
	'identifier' => 'class.notFound',
	'count' => 1,
	'path' => __DIR__ . '/src/Scheduler.php',
];
$ignoreErrors[] = [
	'message' => '#^Call to method queue\\(\\) on an unknown class BlitzPHP\\\\Queue\\\\Job\\.$#',
	'identifier' => 'class.notFound',
	'count' => 1,
	'path' => __DIR__ . '/src/Scheduler.php',
];
$ignoreErrors[] = [
	'message' => '#^PHPDoc tag @var for variable \\$job contains unknown class BlitzPHP\\\\Queue\\\\Job\\.$#',
	'identifier' => 'class.notFound',
	'count' => 1,
	'path' => __DIR__ . '/src/Scheduler.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$name of function service expects class\\-string\\<container\\>, string given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/src/Scheduler.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$name of function service expects class\\-string\\<queue\\>, string given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/src/Scheduler.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\$job of method BlitzPHP\\\\Tasks\\\\Scheduler\\:\\:job\\(\\) has invalid type BlitzPHP\\\\Queue\\\\Job\\.$#',
	'identifier' => 'class.notFound',
	'count' => 2,
	'path' => __DIR__ . '/src/Scheduler.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$name of function service expects class\\-string\\<container\\>, string given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/src/Task.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$name of function service expects class\\-string\\<cronExpression\\>, string given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/src/Task.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$name of function service expects class\\-string\\<httpclient\\>, string given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/src/Task.php',
];
$ignoreErrors[] = [
	'message' => '#^Parameter \\#1 \\$name of function service expects class\\-string\\<scheduler\\>, string given\\.$#',
	'identifier' => 'argument.type',
	'count' => 1,
	'path' => __DIR__ . '/src/TaskRunner.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
