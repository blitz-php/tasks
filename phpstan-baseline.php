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
	'message' => '#^Cannot call method startOfMinute\\(\\) on BlitzPHP\\\\Utilities\\\\Date\\|string\\.$#',
	'identifier' => 'method.nonObject',
	'count' => 2,
	'path' => __DIR__ . '/src/Commands/Work.php',
];
$ignoreErrors[] = [
	'message' => '#^While loop condition is always true\\.$#',
	'identifier' => 'while.alwaysTrue',
	'count' => 1,
	'path' => __DIR__ . '/src/Commands/Work.php',
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
