#!/usr/bin/env bash

BASEPATH=$(cd `dirname $0`; cd ../src/; pwd)
REPOS=$(ls $BASEPATH)

echo "|Repository|Version|Downloads|"
echo "|--|--|--|"

for REPO in ${REPOS}; do
    echo "|[${REPO}](https://github.com/friendsofhyperf/${REPO})|[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/${REPO})](https://packagist.org/packages/friendsofhyperf/${REPO})|[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/${REPO})](https://packagist.org/packages/friendsofhyperf/${REPO})|"
done