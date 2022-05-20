#!/usr/bin/env bash

for i in src/*/.git src/*/.vscode src/*/.github src/*/vendor/ src/*/.gitignore src/*/.php-cs-fixer.php src/*/.phpunit* src/*/composer.lock src/*/phpunit.* src/*/phpstan*; do
    rm -rf $i
done