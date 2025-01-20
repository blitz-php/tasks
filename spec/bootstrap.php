<?php

/**
 * This file is part of BlitzPHP Tasks.
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use BlitzPHP\Tasks\Config\Services;

defined('HOME_PATH')   || define('HOME_PATH', realpath(rtrim(getcwd(), '\\/ ')) . DIRECTORY_SEPARATOR);
defined('VENDOR_PATH') || define('VENDOR_PATH', realpath(HOME_PATH . 'vendor') . DIRECTORY_SEPARATOR);

define('APP_NAMESPACE', 'App');
define('APP_PATH', __DIR__ . '/_support/');
define('WEBROOT', APP_PATH);
define('STORAGE_PATH', APP_PATH);
define('SYST_PATH', VENDOR_PATH . 'blitz-php/framework/src/');

require_once SYST_PATH . 'Constants/constants.php';
require_once SYST_PATH . 'Helpers/common.php';
require_once SYST_PATH . 'Helpers/path.php';

Services::autoloader()->initialize()->register();
Services::container()->initialize();

config()->load('parametres', VENDOR_PATH . 'blitz-php/parametres/src/Config/parametres.php');
config()->set('parametres.file.path', storage_path('.parametres.json'));
config()->load('tasks', __DIR__ . '/../src/Config/tasks.php');
