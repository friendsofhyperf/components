#!/usr/bin/env bash

BASEPATH=$(cd `dirname $0`; cd ../src/; pwd)
REPOS=$(ls $BASEPATH)

echo "|Repository|Stable Version|Unstable Version|Downloads|"
echo "|--|--|--|--|"

for REPO in ${REPOS}; do
    echo "|[${REPO}](https://github.com/friendsofhyperf/${REPO})|[![Latest Stable Version](https://poser.pugx.org/friendsofhyperf/${REPO}/v/stable.svg)](https://packagist.org/packages/friendsofhyperf/${REPO})|[![Latest Unstable Version](https://poser.pugx.org/friendsofhyperf/components/v/unstable.svg)](https://packagist.org/packages/friendsofhyperf/${REPO})|[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/${REPO})](https://packagist.org/packages/friendsofhyperf/${REPO})|"
done