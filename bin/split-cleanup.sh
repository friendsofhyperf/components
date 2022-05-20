#!/usr/bin/env bash

SOMETHINGS="src/*/.git src/*/.vscode src/*/.github src/*/vendor/ src/*/.gitignore src/*/.php-cs-fixer.php src/*/.phpunit* src/*/composer.lock src/*/phpunit.* src/*/phpstan* src/*/.phpstorm.meta*"

for i in $SOMETHINGS; do
    echo "Removing $i"
    rm -rf $i
done