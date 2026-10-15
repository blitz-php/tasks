<?php

/**
 * This file is part of BlitzPHP Tasks.
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use BlitzPHP\Initializer\Boot;

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// S'assure qu'il reconnaît que nous testons.
$_SERVER['ENVIRONMENT'] = 'testing';
define('ENVIRONMENT', 'testing');
define('BLITZ_DEBUG', true);

// Souvent, ces constantes sont prédéfinies, mais interroger la structure actuelle du répertoire comme un repli
defined('HOME_PATH')   || define('HOME_PATH', realpath(rtrim(getcwd(), '\\/ ')) . DIRECTORY_SEPARATOR);
defined('VENDOR_PATH') || define('VENDOR_PATH', realpath(HOME_PATH . 'vendor') . DIRECTORY_SEPARATOR);

if (! is_file($autoload_file = realpath(VENDOR_PATH . 'autoload.php')) ?: '') {
    echo 'Votre fichier autoload de Composer ne semble pas être défini correctement. ';
    echo 'Veuillez ouvrir le fichier suivant et pour corriger: "' . __FILE__ . '"';

    exit(3); // EXIT_CONFIG
}

define('SYST_PATH', VENDOR_PATH . 'blitz-php/framework/src/');
define('APPLICATION_PATH', __DIR__ . '/_support/');
define('TEST_PATH', __DIR__ . '/');
define('WEBROOT', APPLICATION_PATH);

require_once $autoload_file;
require_once SYST_PATH . 'Initializer' . DIRECTORY_SEPARATOR . 'Boot.php';

$paths = ['app' => APPLICATION_PATH, 'storage' => APPLICATION_PATH, 'test' => TEST_PATH, 'composer' => VENDOR_PATH];
Boot::test($paths, __FILE__);

helper('path');

config()->load('parametres', VENDOR_PATH . 'blitz-php/parametres/src/Config/parametres.php');
config()->set('parametres.json.file', storage_path('.parametres.json'));
config()->set('parametres.handlers', ['json']);
config()->load('tasks', __DIR__ . '/../src/Config/tasks.php');
config()->ghost('cache', ['handler' => 'file']);
