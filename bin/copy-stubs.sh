#!/usr/bin/env bash

set -e

BASEPATH=$(cd `dirname $0`; cd ../src/; pwd)

REPOS=$(ls $BASEPATH)

for REPO in $REPOS
do
    [ ! -d "src/$REPO/.github" ] && cp -rf bin/stubs/.github src/$REPO
    [ ! -f "src/$REPO/LICENSE" ] && cp -rf LICENSE src/$REPO
done
