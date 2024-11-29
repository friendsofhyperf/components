#!/usr/bin/env bash

set -e
set -x

CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)

function split()
{
    SHA1=`./bin/splitsh-lite-linux --prefix=$1`
    git push $2 "$SHA1:refs/heads/$CURRENT_BRANCH" -f
}

function remote()
{
    git remote add $1 $2 || true
}

git pull origin $CURRENT_BRANCH

remote "docs" git@github.com:friendsofhyperf/friendsofhyperf.github.io.git
split "docs" "git@github.com:friendsofhyperf/friendsofhyperf.github.io.git"
