#!/usr/bin/env bash

for file in $(find ./src -name composer.json); do
    echo "Normalizing $file"
    composer normalize --no-update-lock $file
done