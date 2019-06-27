<?php

namespace Deployer;

require 'recipe/common.php';

// Project name
set('application', 'MemorialBracelets');

// Project repository
set('repository', 'git@github.com:InteractOne/MemorialBracelets.git');

inventory('hosts.yml');

// Configuration

set('shared_files', [
    'app/etc/env.php',
    'var/.maintenance.ip',
]);
set('shared_dirs', [
    'var/log',
    'var/backups',
    'pub/media',
]);
set('writable_dirs', [
    'var',
    'pub/static',
    'pub/media',
]);
set('clear_paths', [
    'generation/*',
    'var/cache/*',
]);

// Tasks
desc('Compile magento di');
task('magento:compile', function () {
    run("{{bin/php}} {{release_path}}/bin/magento setup:di:compile");
    run('cd {{release_path}} && {{bin/composer}} dump-autoload -o');
});
desc('Deploy assets');
task('magento:deploy:assets', function () {
    run("{{bin/php}} {{release_path}}/bin/magento setup:static-content:deploy -j 1");
    //run("{{bin/php}} {{release_path}}/bin/magento sampledata:deploy");
});
desc('Enable maintenance mode');
task('magento:maintenance:enable', function () {
    run("if [ -d $(echo {{deploy_path}}/current) ]; then {{bin/php}} {{deploy_path}}/current/bin/magento maintenance:enable; fi");
});
desc('Disable maintenance mode');
task('magento:maintenance:disable', function () {
    run("if [ -d $(echo {{deploy_path}}/current) ]; then {{bin/php}} {{deploy_path}}/current/bin/magento maintenance:disable; fi");
});
desc('Upgrade magento database');
task('magento:upgrade:db', function () {
    run("{{bin/php}} {{release_path}}/bin/magento setup:upgrade --keep-generated");
});
desc('Flush Magento Cache');
task('magento:cache:flush', function () {
    run("{{bin/php}} {{release_path}}/bin/magento cache:flush");
});
//Had to do an ugly hack to get composer to work since alpha host's composer binary is just a shell script that runs php composer
desc('Composer install override');
task('deploy:vendors_override', function () {
    run("cd {{release_path}} && {{bin/php}} -d allow_url_fopen=on $(which composer) {{composer_options}}");
});
desc('Magento2 deployment operations');
task('deploy:magento', [
    'magento:cache:flush',
    'magento:compile',
    'magento:deploy:assets',
    'magento:maintenance:enable',
    'magento:upgrade:db',
    'magento:maintenance:disable'
]);

desc('Deploy your project');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
//    Deploy vendors is broken on alpha hosting. Had to override this.
//    'deploy:vendors',
    'deploy:vendors_override',
    'deploy:writable',
    'deploy:clear_paths',
    'deploy:magento',
    'deploy:symlink',
//    'deploy:apc_cache_clear',
//    'deploy:server_cache_clear',
    'deploy:unlock',
    'cleanup',
    'success'
]);
after('deploy:failed', 'magento:maintenance:disable');
require 'vendor/deployer/recipes/recipe/slack.php';
set('slack_webhook', 'https://hooks.slack.com/services/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');
before('deploy', 'slack:notify');
after('success', 'slack:notify:success');
after('deploy:failed', 'slack:notify:failure');
