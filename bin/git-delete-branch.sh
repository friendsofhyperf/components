#!/usr/bin/env bash

set -e

NOW=$(date +%s)
BASEPATH=$(cd `dirname $0`; cd ../src/; pwd)

REMOTES=$(ls $BASEPATH)

# Tag Components
for REMOTE in $REMOTES
do
    echo ""
    echo ""
    echo "Cloning $REMOTE";

    TMP_DIR="/tmp/friendsofhyperf-split"
    REMOTE_URL="git@github.com:friendsofhyperf/$REMOTE.git"

    rm -rf $TMP_DIR;
    mkdir $TMP_DIR;

    (
        cd $TMP_DIR;

        git clone $REMOTE_URL .

        # git checkout main

        # gh repo edit --default-branch main

        # git push --delete origin '2.x' || true
        # git push --delete origin '3.x' || true
        # git push --delete origin 'HEAD' || true
    )
done

TIME=$(echo "$(date +%s) - $NOW" | bc)

printf "Execution time: %f seconds" $TIME