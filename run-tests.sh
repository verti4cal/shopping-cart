#!/bin/bash
vendor/bin/phpstan analyse src tests
php bin/console --env=test doctrine:database:create --if-not-exists
php bin/console --env=test doctrine:migrations:migrate --no-interaction
php bin/console --env=test doctrine:fixtures:load --no-interaction
XDEBUG_MODE=coverage php bin/phpunit --coverage-html tests/coverage