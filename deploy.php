<?php

namespace Deployer;

use Dotenv\Dotenv;

/*
 * Zero-downtime deploys with Deployer (https://deployer.org): `vendor/bin/dep deploy`.
 *
 * The server comes from the DEPLOY_* settings in .env, so this file stays the same for every install.
 * The server needs PHP, Composer and git; the assets are built here and uploaded, so it does not need Node.
 */

require 'recipe/laravel.php';

Dotenv::createImmutable(__DIR__)->safeLoad();

/**
 * A DEPLOY_* setting from .env, or the default when it is empty.
 */
function deploySetting(string $key, ?string $default = null): ?string
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;

    return is_string($value) && $value !== '' ? $value : $default;
}

set('application', 'bricks');
set('repository', deploySetting('DEPLOY_REPOSITORY', trim((string) shell_exec('git config --get remote.origin.url'))));
set('branch', deploySetting('DEPLOY_BRANCH', 'main'));
set('keep_releases', (int) deploySetting('DEPLOY_KEEP_RELEASES', '5'));

// The server clones the repository with its own deploy key, it does not get to use your SSH agent.
set('forward_agent', false);

// Shared between releases, next to .env and storage: the database when the server uses SQLite.
add('shared_files', ['database/database.sqlite']);

if (($hostname = deploySetting('DEPLOY_HOST')) !== null) {
    $host = host($hostname)
        ->set('remote_user', deploySetting('DEPLOY_USER', 'deploy'))
        ->set('port', (int) deploySetting('DEPLOY_PORT', '22'))
        ->set('deploy_path', deploySetting('DEPLOY_PATH', '~/{{application}}'));

    if (($php = deploySetting('DEPLOY_PHP')) !== null) {
        $host->set('bin/php', $php);
    }
}

desc('Builds the assets here and uploads them to the release');
task('deploy:assets', function () {
    runLocally('npm ci && npm run build', ['timeout' => 600]);

    upload('public/build/', '{{release_path}}/public/build/');
});

after('deploy:vendors', 'deploy:assets');

after('deploy:failed', 'deploy:unlock');
