#!/usr/bin/env bash
set -e
# Checkout (done by pipelines)
# Install Dependencies
composer install --no-suggest -o --ignore-platform-reqs --prefer-dist
# Clear Composer Install modifications that are in VCS
git checkout -- .
# Setup Magento
cp dev/tools/deployment/config.local.php app/etc/config.local.php
# Deploy our specific static files
php bin/magento setup:static-content:deploy -t Briteskies/memorial -t Memorial/backend en_US
# Compile generated files
php bin/magento setup:di:compile
# Fix the translation issue in the quote module
php bin/magento i18n:pack app/design/frontend/Briteskies/memorial/i18n/en_US.csv -d en_US