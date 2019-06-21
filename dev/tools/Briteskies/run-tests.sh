#!/usr/bin/env bash
set -e
composer install --no-suggest -o --ignore-platform-reqs --prefer-dist
composer run-script test
php -d memory_limit=2G ./bin/magento setup:install --db-host=127.0.0.1 --db-name=magento2 --db-user=magento2 --db-password=magento2 --base-url=http://localhost/ --admin-firstname=Test --admin-lastname=User --admin-email=test.user@example.org --admin-user=test --admin-password=magento2
