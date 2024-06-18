#!/bin/bash

php bin/console --env=test doctrine:fixtures:load --no-interaction
XDEBUG_MODE=coverage php bin/phpunit --coverage-html tests/coverage