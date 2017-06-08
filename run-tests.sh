#!/usr/bin/env bash

cd /data
composer install --dev

# If that failed, exit.
rc=$?; if [[ $rc != 0 ]]; then exit $rc; fi

cd tests/
../vendor/bin/phpunit -v .