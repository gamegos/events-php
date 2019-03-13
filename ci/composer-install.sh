#!/bin/bash

rm -f composer.lock

if [[ ! "$GITHUB_TOKEN" = "" ]]; then
    composer config --global --auth github-oauth.github.com ${GITHUB_TOKEN}
    composer install --no-interaction
else
    composer install --no-interaction --prefer-source
fi
