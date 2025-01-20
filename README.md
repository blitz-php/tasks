Blitz PHP - Tasks
==============
### Planificateur de tâches pour BlitzPHP

[![Tests](https://github.com/blitz-php/tasks/actions/workflows/run-tests.yml/badge.svg)](https://github.com/blitz-php/tasks/actions/workflows/run-tests.yml)
[![Code Coverage](https://scrutinizer-ci.com/g/blitz-php/tasks/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/blitz-php/tasks/?branch=main)
[![Coding Standards](https://github.com/blitz-php/tasks/actions/workflows/test-coding-standards.yml/badge.svg)](https://github.com/blitz-php/tasks/actions/workflows/test-coding-standards.yml)
[![Build Status](https://scrutinizer-ci.com/g/blitz-php/tasks/badges/build.png?b=main)](https://scrutinizer-ci.com/g/blitz-php/tasks/build-status/main)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/blitz-php/tasks/badges/code-intelligence.svg?b=main)](https://scrutinizer-ci.com/code-intelligence)
[![Quality Score](https://img.shields.io/scrutinizer/g/blitz-php/tasks.svg?style=flat-square)](https://scrutinizer-ci.com/g/blitz-php/tasks)
[![PHPStan](https://github.com/blitz-php/tasks/actions/workflows/test-phpstan.yml/badge.svg)](https://github.com/blitz-php/tasks/actions/workflows/test-phpstan.yml)
[![PHPStan level](https://img.shields.io/badge/PHPStan-level%206-brightgreen)](phpstan.neon.dist)

[![Total Downloads](https://poser.pugx.org/blitz-php/tasks/downloads)](https://packagist.org/packages/blitz-php/tasks)
[![Latest Version](https://img.shields.io/packagist/v/blitz-php/tasks.svg?style=flat-square)](https://packagist.org/packages/blitz-php/tasks)
![PHP](https://img.shields.io/badge/PHP-%5E8.1-blue)
![BlitzPHP](https://img.shields.io/badge/BlitzPHP-%5E0.11.3-yellow)
[![Software License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

<br>

Introduction
------------

## Installation

Vous pouvez installer le package via composer :

```bash
composer require blitz-php/tasks
```

## Configuration

Publier le fichier de configuration : 

```php 
php klinge publish
``` 

## Définition des tâches 

Définissez vos tâches dans la fonction de la clé `init()` :

```php 
// app/Config/tasks.php

<?php 

use BlitzPHP\Tasks\Scheduler;

return [
    // ...
	// ...

    /**
     * Enregistrez toutes les tâches dans cette méthode pour l'application.
     */
    'init' => function (Scheduler $schedule) {
        $schedule->command('demo:refresh --all')->mondays('11:00 pm');
    },
];

``` 

## Documentation 

Lire la documentation complète : http://blitz-php.byethost14.com 

## Contribuer 

Nous acceptons et encourageons les contributions de la communauté sous n'importe quelle forme. Peu importe que vous sachiez coder, écrire de la documentation ou aider à trouver des bogues, toutes les contributions sont les bienvenues. 

Veuillez consulter [CONTRIBUTING.md](CONTRIBUTING.md) pour plus de détails.

## Credits

Ce package est une réadaptation du package <a href="https://github.com/codeigniter4/tasks" target="_blank">CodeIgniter/Tasks</a> pour pouvoir avoir le même fonctionnement avec BlitzPHP. De ce fait tout le mérite revient à <a href="https://github.com/codeigniter4/tasks/graphs/contributors" target="_blank">tous les contributeurs de ce projet</a> que nous remercions sincerement pour ce qu'ils font pour l'évolution du développement web

Pour la réadaptation, nous disons merci à : 
- [Dimitri Sitchet Tomkeu](http://github.com/dimtrovich)
- [Tous les Contributeurs](../../contributors)

## Licence

**tasks** est un package open source publié sous licence MIT. Veuillez consulter [le fichier de licence](LICENSE.md) pour plus d'informations.
