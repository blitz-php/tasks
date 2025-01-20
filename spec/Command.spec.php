<?php

/**
 * This file is part of BlitzPHP Tasks.
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use function Kahlan\expect;

describe('Task / Command', function () {
    it('Les commandes `tasks:disable` et `tasks:enable` fonctionnent', function () {
        expect(parametre('tasks.enabled'))->toBeNull();

        command('tasks:disable');
        expect(parametre('tasks.enabled'))->toBeFalsy();

        command('tasks:enable');
        expect(parametre('tasks.enabled'))->toBeTruthy();
    });

    it('Publisher', function () {
        config()->ghost('publisher')->set('publisher.restrictions', [ROOTPATH => '*']);

        $path = CONFIG_PATH . 'tasks.php';

        expect(file_exists($path))->toBeFalsy();

        // conserver les fichiers originaux car a la fin, on suppimera tous les fichiers publiés
        $original_files = array_map(fn ($f) => $f->getRelativePathname(), service('fs')->files(CONFIG_PATH));

        command('publish');
        // command('publish --namespace=BlitzPHP\\\\Tasks');

        expect(file_exists($path))->toBeTruthy();

        $content = file_get_contents($path);
        expect(str_contains($content, '\'init\' => function (Scheduler $schedule) {'))->toBeTruthy();

        foreach (service('fs')->files(CONFIG_PATH) as $f) {
            if (! in_array($f->getRelativePathname(), $original_files, true)) {
                @unlink($f->getPathname());
            }
        }

        expect(file_exists($path))->toBeFalsy();
    });
});
